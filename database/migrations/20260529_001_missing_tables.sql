-- KVN Construction Platform
-- Missing Tables Migration
-- Date: 2026-05-29
-- Description: Creates tables referenced in code but missing from main schema

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
START TRANSACTION;

-- ============================================================
-- RATE LIMITS TABLE
-- Referenced by helpers/rateLimiter.php
-- ============================================================

CREATE TABLE IF NOT EXISTS rate_limits (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    route_name VARCHAR(255) DEFAULT NULL,
    attempts INT UNSIGNED DEFAULT 0,
    blocked_until DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_rate_limits_identifier_action (identifier, action_type),
    KEY idx_rate_limits_route (route_name),
    KEY idx_rate_limits_blocked (blocked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- USER DEVICES TABLE
-- Referenced by helpers/session.php for device tracking
-- ============================================================

CREATE TABLE IF NOT EXISTS user_devices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    device_name VARCHAR(255) DEFAULT NULL,
    device_hash VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    last_used_at DATETIME DEFAULT NULL,
    is_trusted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user_devices_user (user_id),
    KEY idx_user_devices_hash (device_hash),
    CONSTRAINT fk_user_devices_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CLIENT MESSAGES TABLE
-- Referenced by public/client/dashboard.php
-- ============================================================

CREATE TABLE IF NOT EXISTS client_messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at DATETIME DEFAULT NULL,
    replied_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_client_messages_client (client_id),
    KEY idx_client_messages_read (is_read),
    CONSTRAINT fk_client_messages_user FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ADD MISSING COLUMNS TO EXISTING TABLES
-- ============================================================

-- Add missing columns to user_sessions
ALTER TABLE user_sessions
ADD COLUMN IF NOT EXISTS fingerprint_hash VARCHAR(255) DEFAULT NULL AFTER remember_token,
ADD COLUMN IF NOT EXISTS last_activity DATETIME DEFAULT NULL AFTER expires_at,
ADD COLUMN IF NOT EXISTS logout_reason VARCHAR(100) DEFAULT NULL AFTER revoked_at;

-- Add missing columns to users table
ALTER TABLE users
ADD COLUMN IF NOT EXISTS last_activity_at DATETIME DEFAULT NULL AFTER last_login,
ADD COLUMN IF NOT EXISTS last_ip VARCHAR(45) DEFAULT NULL AFTER last_login_user_agent,
ADD COLUMN IF NOT EXISTS last_user_agent VARCHAR(255) DEFAULT NULL AFTER last_ip;

-- Add missing columns to leads table
ALTER TABLE leads
ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL AFTER updated_at,
ADD COLUMN IF NOT EXISTS deleted_by BIGINT UNSIGNED DEFAULT NULL AFTER deleted_at,
ADD COLUMN IF NOT EXISTS source_detail VARCHAR(255) DEFAULT NULL AFTER source;

-- ============================================================
-- SOFT DELETE TRIGGER FOR LEADS
-- ============================================================

DELIMITER //
CREATE TRIGGER IF NOT EXISTS leads_soft_delete BEFORE DELETE ON leads
FOR EACH ROW
BEGIN
    -- Archive to lead_history would go here
    -- For now, we just prevent actual deletion via application logic
END//
DELIMITER ;

-- ============================================================
-- COMPLETE CONSTRUCTION_PACKAGES TABLE STRUCTURE
-- (Schema has base table, this adds missing specifications)
-- ============================================================

CREATE TABLE IF NOT EXISTS package_specifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    package_id BIGINT UNSIGNED NOT NULL,
    category VARCHAR(100) NOT NULL,
    specification_name VARCHAR(255) NOT NULL,
    specification_value VARCHAR(255) DEFAULT NULL,
    is_included TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_package_specs_package (package_id),
    KEY idx_package_specs_category (category),
    CONSTRAINT fk_package_specs_package FOREIGN KEY (package_id) REFERENCES construction_packages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- MATERIAL PRICING TABLE
-- For dynamic material cost calculations
-- ============================================================

CREATE TABLE IF NOT EXISTS material_pricing (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    material_name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    quality_grade ENUM('basic', 'standard', 'premium', 'luxury') DEFAULT 'standard',
    supplier VARCHAR(255) DEFAULT NULL,
    last_updated DATETIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_material_pricing_category (category),
    KEY idx_material_pricing_grade (quality_grade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- LABOR PRICING TABLE
-- For labor cost calculations per sqft
-- ============================================================

CREATE TABLE IF NOT EXISTS labor_pricing (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    work_type VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    rate_per_sqft DECIMAL(10,2) NOT NULL,
    min_area_sqft DECIMAL(10,2) DEFAULT 0,
    quality_grade ENUM('basic', 'standard', 'premium', 'luxury') DEFAULT 'standard',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_labor_pricing_work_type (work_type),
    KEY idx_labor_pricing_grade (quality_grade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ESTIMATOR CALCULATION LOG
-- Track all estimator calculations for analytics
-- ============================================================

CREATE TABLE IF NOT EXISTS estimator_calculation_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    request_id BIGINT UNSIGNED DEFAULT NULL,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    package_id BIGINT UNSIGNED DEFAULT NULL,
    plot_area DECIMAL(10,2) NOT NULL,
    floors INT NOT NULL,
    base_cost DECIMAL(15,2) NOT NULL,
    labor_cost DECIMAL(15,2) DEFAULT 0,
    material_cost DECIMAL(15,2) DEFAULT 0,
    location_multiplier DECIMAL(5,2) DEFAULT 1.00,
    subtotal DECIMAL(15,2) NOT NULL,
    gst_amount DECIMAL(15,2) DEFAULT 0,
    total_cost DECIMAL(15,2) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_estimator_log_request (request_id),
    KEY idx_estimator_log_package (package_id),
    KEY idx_estimator_log_created (created_at),
    CONSTRAINT fk_estimator_log_request FOREIGN KEY (request_id) REFERENCES estimator_requests(id) ON DELETE SET NULL,
    CONSTRAINT fk_estimator_log_package FOREIGN KEY (package_id) REFERENCES construction_packages(id) ON DELETE SET NULL,
    CONSTRAINT fk_estimator_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED DATA FOR LOCATION ZONES
-- ============================================================

INSERT INTO location_zones (zone_name, multiplier) VALUES
('Premium Areas', 1.20),
('Urban Areas', 1.10),
('Suburban Areas', 1.00),
('Semi-Urban Areas', 0.95),
('Rural Areas', 0.90),
('Industrial Areas', 1.15)
ON DUPLICATE KEY UPDATE multiplier = VALUES(multiplier);

-- ============================================================
-- SEED DATA FOR CONSTRUCTION PACKAGES
-- ============================================================

INSERT INTO construction_packages (package_name, slug, description, base_price, price_per_sqft, includes_gst, delivery_time_months, status) VALUES
('Basic Package', 'basic-package', 'Essential construction services with quality materials.', 500000.00, 1500.00, 1, 8, 'active'),
('Standard Package', 'standard-package', 'Comprehensive construction with premium finishes.', 800000.00, 1800.00, 1, 10, 'active'),
('Premium Package', 'premium-package', 'Luxury construction with high-end materials.', 1200000.00, 2200.00, 1, 12, 'active')
ON DUPLICATE KEY UPDATE base_price = VALUES(base_price);

-- ============================================================
-- SEED DATA FOR PACKAGE FEATURES
-- ============================================================

INSERT INTO package_features (package_id, feature_name) VALUES
(1, 'Structural Design'), (1, 'Basic Materials'), (1, 'Standard Flooring'), (1, 'Basic Plumbing'), (1, 'Standard Electrical'),
(2, 'Structural Design'), (2, 'Premium Materials'), (2, 'Vitrified Flooring'), (2, 'Premium Plumbing'), (2, 'Modular Electrical'), (2, 'Painting'), (2, 'Fixtures'),
(3, 'Structural Design'), (3, 'Luxury Materials'), (3, 'Italian Flooring'), (3, 'Smart Plumbing'), (3, 'Smart Electrical'), (3, 'Premium Painting'), (3, 'Premium Fixtures'), (3, 'Smart Home Integration')
ON DUPLICATE KEY UPDATE feature_name = VALUES(feature_name);

-- ============================================================
-- SEED DATA FOR LEAD STATUSES
-- ============================================================

INSERT INTO lead_statuses (id, name, color, sort_order) VALUES
(1, 'New', '#2196F3', 1),
(2, 'Contacted', '#FF9800', 2),
(3, 'Qualified', '#9C27B0', 3),
(4, 'Proposal Sent', '#00BCD4', 4),
(5, 'Negotiation', '#FFC107', 5),
(6, 'Won', '#4CAF50', 6),
(7, 'Lost', '#F44336', 7)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ============================================================
-- SEED DATA FOR PROJECT STATUSES
-- ============================================================

INSERT INTO project_statuses (id, name) VALUES
(1, 'Planning'),
(2, 'Foundation'),
(3, 'Structure'),
(4, 'Finishing'),
(5, 'Completed'),
(6, 'On Hold')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ============================================================
-- SEED DATA FOR SITE SETTINGS
-- ============================================================

INSERT INTO site_settings (setting_key, setting_value) VALUES
('company_name', 'KVN Construction'),
('company_tagline', 'Building Dreams, Delivering Excellence'),
('company_phone', '+91 9876543210'),
('company_email', 'info@kvnconstruction.com'),
('company_address', '123 Construction Lane, Bangalore, Karnataka'),
('currency', 'INR'),
('gst_percentage', '18'),
('facebook_url', 'https://facebook.com/kvnconstruction'),
('instagram_url', 'https://instagram.com/kvnconstruction'),
('linkedin_url', 'https://linkedin.com/company/kvnconstruction')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ============================================================
-- SEED DATA FOR MATERIAL PRICING
-- ============================================================

INSERT INTO material_pricing (material_name, category, unit, unit_price, quality_grade) VALUES
('Cement', 'Structural', 'Bag (50kg)', 380.00, 'standard'),
('Steel (Fe500)', 'Structural', 'Quintal', 5200.00, 'standard'),
('Sand (River)', 'Masonry', 'Cubic Meter', 2800.00, 'standard'),
('Aggregates', 'Structural', 'Cubic Meter', 1600.00, 'standard'),
('Bricks (Clay)', 'Masonry', 'Per 1000', 8500.00, 'standard'),
('Tiles (Vitrified)', 'Flooring', 'Sq Ft', 45.00, 'standard'),
('Tiles (Premium)', 'Flooring', 'Sq Ft', 85.00, 'premium'),
('Tiles (Italian)', 'Flooring', 'Sq Ft', 150.00, 'luxury'),
('Paint (Premium)', 'Finishing', 'Liter', 250.00, 'premium'),
('Paint (Standard)', 'Finishing', 'Liter', 150.00, 'standard'),
('CPVC Pipes', 'Plumbing', 'Running Ft', 45.00, 'standard'),
('PVC Pipes', 'Plumbing', 'Running Ft', 35.00, 'standard'),
('Electrical Wiring', 'Electrical', 'Running Ft', 25.00, 'standard'),
('Switches (Premium)', 'Electrical', 'Per Piece', 85.00, 'premium')
ON DUPLICATE KEY UPDATE unit_price = VALUES(unit_price);

-- ============================================================
-- SEED DATA FOR LABOR PRICING
-- ============================================================

INSERT INTO labor_pricing (work_type, description, rate_per_sqft, quality_grade) VALUES
('Foundation', 'Excavation and foundation work', 150.00, 'standard'),
('Structural', 'RCC work, columns, beams, slab', 350.00, 'standard'),
('Masonry', 'Brickwork, blockwork', 180.00, 'standard'),
('Plastering', 'Internal and external plaster', 120.00, 'standard'),
('Flooring', 'Floor preparation and tile laying', 140.00, 'standard'),
('Plumbing', 'Pipe fitting, bathroom fixtures', 160.00, 'standard'),
('Electrical', 'Wiring, switchboard installation', 130.00, 'standard'),
('Painting', 'Priming, putty, paint application', 100.00, 'standard'),
('Carpentry', 'Door frame, shutter installation', 120.00, 'standard'),
('Smart Home', 'Automation wiring and setup', 200.00, 'premium')
ON DUPLICATE KEY UPDATE rate_per_sqft = VALUES(rate_per_sqft);

COMMIT;