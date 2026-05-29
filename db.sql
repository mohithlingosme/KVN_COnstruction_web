-- KVN Construction Platform Database Schema
-- Complete database structure with all tables

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
START TRANSACTION;
SET time_zone = '+00:00';

CREATE TABLE users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','client','employee') NOT NULL DEFAULT 'client',
  profile_image VARCHAR(255) DEFAULT NULL,
  status ENUM('active','inactive','blocked') NOT NULL DEFAULT 'active',
  last_login TIMESTAMP NULL DEFAULT NULL,
  email_verified_at TIMESTAMP NULL DEFAULT NULL,
  remember_token VARCHAR(255) DEFAULT NULL,
  failed_attempts INT DEFAULT 0,
  failed_login_attempts INT DEFAULT 0,
  locked_until DATETIME DEFAULT NULL,
  last_login_ip VARCHAR(45) DEFAULT NULL,
  last_login_user_agent VARCHAR(255) DEFAULT NULL,
  last_password_change DATETIME DEFAULT NULL,
  password_changed_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY email (email),
  KEY idx_users_phone (phone),
  KEY idx_users_role_status (role,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE user_sessions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  session_token VARCHAR(255) NOT NULL,
  remember_token VARCHAR(255) DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  device_name VARCHAR(100) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  revoked_at DATETIME DEFAULT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_user_sessions_user_token (user_id,session_token),
  KEY idx_user_sessions_active (user_id,is_active,revoked_at),
  KEY idx_user_sessions_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE roles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  role_key VARCHAR(100) NOT NULL UNIQUE,
  role_name VARCHAR(150) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE permissions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  permission_key VARCHAR(150) NOT NULL UNIQUE,
  permission_name VARCHAR(150) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE role_permissions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_role_permission (role_id,permission_id),
  CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE user_roles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_role (user_id,role_id),
  CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE email_verification_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  verified_at DATETIME DEFAULT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_email_verification_token_hash (token_hash),
  KEY idx_email_verification_user_id (user_id),
  CONSTRAINT fk_email_verification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE password_resets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) DEFAULT NULL,
  token VARCHAR(255) DEFAULT NULL,
  expires_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE password_reset_otps (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  otp_code VARCHAR(10) NOT NULL,
  expires_at DATETIME NOT NULL,
  is_used TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE otps (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  otp_code VARCHAR(255) NOT NULL,
  otp_type VARCHAR(50) NOT NULL DEFAULT 'login',
  attempts INT DEFAULT 0,
  resend_count INT DEFAULT 0,
  is_used TINYINT(1) DEFAULT 0,
  verified TINYINT(1) DEFAULT 0,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_sent_at TIMESTAMP NULL DEFAULT NULL,
  used_at DATETIME DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  KEY idx_otps_user_phone (user_id,phone),
  KEY idx_otps_expires (expires_at),
  KEY idx_otps_type (otp_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE security_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  event_type VARCHAR(100) NOT NULL,
  event_level ENUM('info','warning','critical') DEFAULT 'info',
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  details JSON DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_security_logs_user_event (user_id,event_type,created_at),
  KEY idx_security_logs_level_created (event_level,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE login_activity (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  device_name VARCHAR(100) DEFAULT NULL,
  location VARCHAR(255) DEFAULT NULL,
  login_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT login_activity_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  action_type VARCHAR(100) NOT NULL,
  entity_type VARCHAR(100) DEFAULT NULL,
  entity_id BIGINT UNSIGNED DEFAULT NULL,
  description TEXT DEFAULT NULL,
  old_values JSON DEFAULT NULL,
  new_values JSON DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT audit_logs_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  title VARCHAR(255) DEFAULT NULL,
  message TEXT DEFAULT NULL,
  notification_type VARCHAR(50) DEFAULT 'general',
  link VARCHAR(255) DEFAULT NULL,
  is_read TINYINT(1) DEFAULT 0,
  read_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT notifications_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE site_settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(150) DEFAULT NULL,
  setting_value LONGTEXT DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE lead_statuses (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) DEFAULT NULL,
  color VARCHAR(20) DEFAULT NULL,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE leads (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(255) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  location VARCHAR(255) DEFAULT NULL,
  plot_size VARCHAR(100) DEFAULT NULL,
  budget VARCHAR(100) DEFAULT NULL,
  service_required VARCHAR(255) DEFAULT NULL,
  source VARCHAR(100) DEFAULT NULL,
  message TEXT DEFAULT NULL,
  status_id INT DEFAULT 1,
  assigned_to BIGINT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_leads_phone (phone),
  KEY idx_leads_status (status_id),
  KEY idx_leads_assigned (assigned_to),
  CONSTRAINT leads_ibfk_1 FOREIGN KEY (status_id) REFERENCES lead_statuses(id) ON DELETE SET NULL,
  CONSTRAINT leads_ibfk_2 FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE lead_followups (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lead_id BIGINT UNSIGNED NOT NULL,
  followup_type VARCHAR(50) DEFAULT 'call',
  notes TEXT DEFAULT NULL,
  next_followup_date DATE DEFAULT NULL,
  created_by BIGINT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY lead_id (lead_id),
  KEY created_by (created_by),
  CONSTRAINT lead_followups_ibfk_1 FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
  CONSTRAINT lead_followups_ibfk_2 FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE otps (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  otp_code VARCHAR(255) NOT NULL,
  otp_type VARCHAR(50) NOT NULL DEFAULT 'login',
  attempts INT DEFAULT 0,
  resend_count INT DEFAULT 0,
  is_used TINYINT(1) DEFAULT 0,
  verified TINYINT(1) DEFAULT 0,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_sent_at TIMESTAMP NULL DEFAULT NULL,
  used_at DATETIME DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  KEY idx_otps_user_phone (user_id,phone),
  KEY idx_otps_expires (expires_at),
  KEY idx_otps_type (otp_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE security_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  event_type VARCHAR(100) NOT NULL,
  event_level ENUM('info','warning','critical') DEFAULT 'info',
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  details JSON DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_security_logs_user_event (user_id,event_type,created_at),
  KEY idx_security_logs_level_created (event_level,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE login_activity (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  device_name VARCHAR(100) DEFAULT NULL,
  location VARCHAR(255) DEFAULT NULL,
  login_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT login_activity_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  action_type VARCHAR(100) NOT NULL,
  entity_type VARCHAR(100) DEFAULT NULL,
  entity_id BIGINT UNSIGNED DEFAULT NULL,
  description TEXT DEFAULT NULL,
  old_values JSON DEFAULT NULL,
  new_values JSON DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT audit_logs_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  title VARCHAR(255) DEFAULT NULL,
  message TEXT DEFAULT NULL,
  notification_type VARCHAR(50) DEFAULT 'general',
  link VARCHAR(255) DEFAULT NULL,
  is_read TINYINT(1) DEFAULT 0,
  read_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT notifications_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE site_settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(150) DEFAULT NULL,
  setting_value LONGTEXT DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE lead_statuses (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) DEFAULT NULL,
  color VARCHAR(20) DEFAULT NULL,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE leads (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(255) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  location VARCHAR(255) DEFAULT NULL,
  plot_size VARCHAR(100) DEFAULT NULL,
  budget VARCHAR(100) DEFAULT NULL,
  service_required VARCHAR(255) DEFAULT NULL,
  source VARCHAR(100) DEFAULT NULL,
  message TEXT DEFAULT NULL,
  status_id INT DEFAULT 1,
  assigned_to BIGINT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_leads_phone (phone),
  KEY idx_leads_status (status_id),
  KEY idx_leads_assigned (assigned_to),
  CONSTRAINT leads_ibfk_1 FOREIGN KEY (status_id) REFERENCES lead_statuses(id) ON DELETE SET NULL,
  CONSTRAINT leads_ibfk_2 FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE lead_followups (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lead_id BIGINT UNSIGNED NOT NULL,
  followup_type VARCHAR(50) DEFAULT 'call',
  notes TEXT DEFAULT NULL,
  next_followup_date DATE DEFAULT NULL,
  created_by BIGINT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY lead_id (lead_id),
  KEY created_by (created_by),
  CONSTRAINT lead_followups_ibfk_1 FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
  CONSTRAINT lead_followups_ibfk_2 FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE appointments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lead_id BIGINT UNSIGNED DEFAULT NULL,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  appointment_date DATETIME NOT NULL,
  duration_minutes INT DEFAULT 60,
  meeting_type VARCHAR(50) DEFAULT 'site_visit',
  location VARCHAR(255) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  status ENUM('scheduled','confirmed','completed','cancelled') DEFAULT 'scheduled',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY lead_id (lead_id),
  KEY user_id (user_id),
  CONSTRAINT appointments_ibfk_1 FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
  CONSTRAINT appointments_ibfk_2 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE construction_packages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  package_name VARCHAR(255) DEFAULT NULL,
  slug VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  base_price DECIMAL(15,2) DEFAULT NULL,
  price_per_sqft DECIMAL(10,2) DEFAULT NULL,
  includes_gst TINYINT(1) DEFAULT 1,
  features TEXT DEFAULT NULL,
  delivery_time_months INT DEFAULT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE package_features (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  package_id BIGINT UNSIGNED NOT NULL,
  feature_name VARCHAR(255) DEFAULT NULL,
  KEY package_id (package_id),
  CONSTRAINT package_features_ibfk_1 FOREIGN KEY (package_id) REFERENCES construction_packages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE location_zones (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  zone_name VARCHAR(150) DEFAULT NULL,
  multiplier DECIMAL(5,2) DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE estimator_requests (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  lead_id BIGINT UNSIGNED DEFAULT NULL,
  package_id BIGINT UNSIGNED DEFAULT NULL,
  location_zone_id BIGINT UNSIGNED DEFAULT NULL,
  full_name VARCHAR(255) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  plot_area DECIMAL(10,2) DEFAULT NULL,
  floors INT DEFAULT 1,
  estimated_cost DECIMAL(15,2) DEFAULT NULL,
  status ENUM('pending','reviewed','quoted') DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY user_id (user_id),
  KEY lead_id (lead_id),
  KEY package_id (package_id),
  KEY location_zone_id (location_zone_id),
  CONSTRAINT estimator_requests_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT estimator_requests_ibfk_2 FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
  CONSTRAINT estimator_requests_ibfk_3 FOREIGN KEY (package_id) REFERENCES construction_packages(id) ON DELETE SET NULL,
  CONSTRAINT estimator_requests_ibfk_4 FOREIGN KEY (location_zone_id) REFERENCES location_zones(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE project_statuses (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE projects (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  project_name VARCHAR(255) DEFAULT NULL,
  client_id BIGINT UNSIGNED DEFAULT NULL,
  lead_id BIGINT UNSIGNED DEFAULT NULL,
  package_id BIGINT UNSIGNED DEFAULT NULL,
  project_manager_id BIGINT UNSIGNED DEFAULT NULL,
  location VARCHAR(255) DEFAULT NULL,
  plot_size DECIMAL(10,2) DEFAULT NULL,
  budget DECIMAL(15,2) DEFAULT NULL,
  start_date DATE DEFAULT NULL,
  expected_end_date DATE DEFAULT NULL,
  status_id INT DEFAULT 1,
  description TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY lead_id (lead_id),
  KEY package_id (package_id),
  KEY project_manager_id (project_manager_id),
  KEY idx_projects_status (status_id),
  KEY idx_projects_client (client_id),
  CONSTRAINT projects_ibfk_1 FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT projects_ibfk_2 FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
  CONSTRAINT projects_ibfk_3 FOREIGN KEY (package_id) REFERENCES construction_packages(id) ON DELETE SET NULL,
  CONSTRAINT projects_ibfk_4 FOREIGN KEY (project_manager_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT projects_ibfk_5 FOREIGN KEY (status_id) REFERENCES project_statuses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE project_milestones (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  project_id BIGINT UNSIGNED NOT NULL,
  milestone_name VARCHAR(255) DEFAULT NULL,
  progress_percent INT DEFAULT 0,
  payment_due DECIMAL(15,2) DEFAULT 0.00,
  deadline DATE DEFAULT NULL,
  completed_at DATETIME DEFAULT NULL,
  status ENUM('pending','in_progress','completed') DEFAULT 'pending',
  KEY project_id (project_id),
  CONSTRAINT project_milestones_ibfk_1 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE project_payments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  project_id BIGINT UNSIGNED NOT NULL,
  milestone_id BIGINT UNSIGNED DEFAULT NULL,
  amount DECIMAL(15,2) DEFAULT NULL,
  payment_mode ENUM('cash','bank_transfer','upi','cheque') DEFAULT 'bank_transfer',
  transaction_id VARCHAR(255) DEFAULT NULL,
  receipt_file VARCHAR(255) DEFAULT NULL,
  status ENUM('pending','paid','failed') DEFAULT 'pending',
  paid_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY project_id (project_id),
  KEY milestone_id (milestone_id),
  CONSTRAINT project_payments_ibfk_1 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT project_payments_ibfk_2 FOREIGN KEY (milestone_id) REFERENCES project_milestones(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE project_updates (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  project_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) DEFAULT NULL,
  update_notes TEXT DEFAULT NULL,
  media_file VARCHAR(255) DEFAULT NULL,
  visible_to_client TINYINT(1) DEFAULT 1,
  uploaded_by BIGINT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY project_id (project_id),
  KEY uploaded_by (uploaded_by),
  CONSTRAINT project_updates_ibfk_1 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT project_updates_ibfk_2 FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE quotations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  quotation_number VARCHAR(100) DEFAULT NULL,
  lead_id BIGINT UNSIGNED DEFAULT NULL,
  project_id BIGINT UNSIGNED DEFAULT NULL,
  subtotal DECIMAL(15,2) DEFAULT NULL,
  gst DECIMAL(15,2) DEFAULT NULL,
  discount DECIMAL(15,2) DEFAULT NULL,
  total DECIMAL(15,2) DEFAULT NULL,
  status ENUM('draft','sent','approved','rejected') DEFAULT 'draft',
  valid_until DATE DEFAULT NULL,
  created_by BIGINT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY quotation_number (quotation_number),
  KEY lead_id (lead_id),
  KEY project_id (project_id),
  KEY created_by (created_by),
  CONSTRAINT quotations_ibfk_1 FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
  CONSTRAINT quotations_ibfk_2 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
  CONSTRAINT quotations_ibfk_3 FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE quotation_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  quotation_id BIGINT UNSIGNED NOT NULL,
  item_name VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  quantity DECIMAL(10,2) DEFAULT NULL,
  rate DECIMAL(15,2) DEFAULT NULL,
  amount DECIMAL(15,2) DEFAULT NULL,
  KEY quotation_id (quotation_id),
  CONSTRAINT quotation_items_ibfk_1 FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE media_library (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  file_name VARCHAR(255) DEFAULT NULL,
  file_path VARCHAR(255) DEFAULT NULL,
  file_type VARCHAR(100) DEFAULT NULL,
  file_size BIGINT DEFAULT NULL,
  uploaded_by BIGINT UNSIGNED DEFAULT NULL,
  project_id BIGINT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY uploaded_by (uploaded_by),
  KEY project_id (project_id),
  CONSTRAINT media_library_ibfk_1 FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT media_library_ibfk_2 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE media_derivatives (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  media_id BIGINT UNSIGNED NOT NULL,
  derivative_type VARCHAR(100) NOT NULL,
  derivative_path VARCHAR(255) NOT NULL,
  width INT DEFAULT NULL,
  height INT DEFAULT NULL,
  file_size BIGINT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY media_id (media_id),
  CONSTRAINT fk_media_derivatives_media FOREIGN KEY (media_id) REFERENCES media_library(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE portfolio_projects (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) DEFAULT NULL,
  slug VARCHAR(255) DEFAULT NULL,
  location VARCHAR(255) DEFAULT NULL,
  project_type ENUM('residential','commercial','interior','renovation') DEFAULT NULL,
  area_sqft DECIMAL(10,2) DEFAULT NULL,
  budget DECIMAL(15,2) DEFAULT NULL,
  duration_months INT DEFAULT NULL,
  featured_image VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  testimonial TEXT DEFAULT NULL,
  client_name VARCHAR(150) DEFAULT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY slug (slug),
  KEY idx_portfolio_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE blog_categories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(150) DEFAULT NULL,
  slug VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE blog_posts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) DEFAULT NULL,
  slug VARCHAR(255) DEFAULT NULL,
  excerpt TEXT DEFAULT NULL,
  content LONGTEXT DEFAULT NULL,
  category_id BIGINT UNSIGNED DEFAULT NULL,
  featured_image VARCHAR(255) DEFAULT NULL,
  author VARCHAR(255) DEFAULT NULL,
  created_by BIGINT UNSIGNED DEFAULT NULL,
  published_at TIMESTAMP NULL DEFAULT NULL,
  status ENUM('draft','published') DEFAULT 'draft',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY slug (slug),
  KEY category_id (category_id),
  KEY created_by (created_by),
  KEY idx_blog_slug (slug),
  CONSTRAINT blog_posts_ibfk_1 FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
  CONSTRAINT blog_posts_ibfk_2 FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE about_page (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  hero_title VARCHAR(255) DEFAULT NULL,
  hero_description TEXT DEFAULT NULL,
  hero_image VARCHAR(255) DEFAULT NULL,
  vision_title VARCHAR(255) DEFAULT NULL,
  vision_description LONGTEXT DEFAULT NULL,
  cta_title VARCHAR(255) DEFAULT NULL,
  cta_description TEXT DEFAULT NULL,
  cta_button_text VARCHAR(100) DEFAULT NULL,
  cta_button_link VARCHAR(255) DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE about_advantages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  icon VARCHAR(100) DEFAULT NULL,
  sort_order INT DEFAULT 0,
  status ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE about_process_steps (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  step_title VARCHAR(255) DEFAULT NULL,
  step_description TEXT DEFAULT NULL,
  sort_order INT DEFAULT 0,
  status ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE about_specifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  spec_title VARCHAR(255) DEFAULT NULL,
  spec_value VARCHAR(255) DEFAULT NULL,
  sort_order INT DEFAULT 0,
  status ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE homepage_slides (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) DEFAULT NULL,
  subtitle TEXT DEFAULT NULL,
  image_path VARCHAR(255) DEFAULT NULL,
  primary_button_text VARCHAR(100) DEFAULT NULL,
  primary_button_link VARCHAR(255) DEFAULT NULL,
  secondary_button_text VARCHAR(100) DEFAULT NULL,
  secondary_button_link VARCHAR(255) DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  status ENUM('draft','published') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE homepage_sections (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  section_key VARCHAR(100) NOT NULL UNIQUE,
  section_title VARCHAR(255) DEFAULT NULL,
  section_content LONGTEXT DEFAULT NULL,
  section_payload LONGTEXT DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  status ENUM('draft','published') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE cta_blocks (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  block_key VARCHAR(100) NOT NULL UNIQUE,
  title VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  button_text VARCHAR(100) DEFAULT NULL,
  button_link VARCHAR(255) DEFAULT NULL,
  secondary_text VARCHAR(100) DEFAULT NULL,
  secondary_link VARCHAR(255) DEFAULT NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE route_seo_meta (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  route_key VARCHAR(150) NOT NULL UNIQUE,
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description TEXT DEFAULT NULL,
  canonical_url VARCHAR(255) DEFAULT NULL,
  og_image VARCHAR(255) DEFAULT NULL,
  schema_json LONGTEXT DEFAULT NULL,
  robots_directive VARCHAR(100) DEFAULT 'index,follow',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE faqs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  question TEXT DEFAULT NULL,
  answer TEXT DEFAULT NULL,
  category VARCHAR(100) DEFAULT NULL,
  sort_order INT DEFAULT 0,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE testimonials (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  client_name VARCHAR(150) DEFAULT NULL,
  client_location VARCHAR(150) DEFAULT NULL,
  review TEXT DEFAULT NULL,
  rating INT DEFAULT 5,
  project_type VARCHAR(100) DEFAULT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE services (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  service_name VARCHAR(255) DEFAULT NULL,
  slug VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  icon VARCHAR(100) DEFAULT NULL,
  short_description VARCHAR(255) DEFAULT NULL,
  featured TINYINT(1) DEFAULT 0,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- SEED DATA
-- --------------------------------------------------------
INSERT INTO roles (role_key, role_name) VALUES
('admin', 'Administrator'),
('client', 'Client'),
('employee', 'Employee');
INSERT INTO permissions (permission_key, permission_name) VALUES
('dashboard.view', 'View Dashboard'),
('leads.view', 'View Leads'),
('leads.create', 'Create Leads'),
('leads.edit', 'Edit Leads'),
('leads.delete', 'Delete Leads'),
('projects.view', 'View Projects'),
('projects.create', 'Create Projects'),
('projects.edit', 'Edit Projects'),
('projects.delete', 'Delete Projects'),
('quotations.view', 'View Quotations'),
('quotations.create', 'Create Quotations'),
('quotations.edit', 'Edit Quotations'),
('cms.view', 'View CMS'),
('cms.edit', 'Edit CMS'),
('media.view', 'View Media'),
('media.upload', 'Upload Media'),
('media.delete', 'Delete Media'),
('users.view', 'View Users'),
('users.create', 'Create Users'),
('users.edit', 'Edit Users'),
('settings.view', 'View Settings'),
('settings.edit', 'Edit Settings'),
('reports.view', 'View Reports'),
('security.view', 'View Security Logs');
INSERT INTO role_permissions (role_id, permission_id) SELECT 1, id FROM permissions;
INSERT INTO lead_statuses (id, name, color, sort_order) VALUES
(1, 'New', '#2196F3', 1),
(2, 'Contacted', '#FF9800', 2),
(3, 'Qualified', '#9C27B0', 3),
(4, 'Proposal Sent', '#00BCD4', 4),
(5, 'Negotiation', '#FFC107', 5),
(6, 'Won', '#4CAF50', 6),
(7, 'Lost', '#F44336', 7);
INSERT INTO project_statuses (id, name) VALUES
(1, 'Planning'),
(2, 'Foundation'),
(3, 'Structure'),
(4, 'Finishing'),
(5, 'Completed'),
(6, 'On Hold');
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
('linkedin_url', 'https://linkedin.com/company/kvnconstruction');
INSERT INTO users (full_name, email, phone, password, role, status, email_verified_at, created_at) VALUES
('Admin User', 'admin@kvnconstruction.com', '+919876543210', '.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NOW(), NOW());
INSERT INTO user_roles (user_id, role_id) SELECT 1, 1;
INSERT INTO location_zones (zone_name, multiplier) VALUES
('Premium Areas', 1.20),
('Urban Areas', 1.10),
('Suburban Areas', 1.00),
('Semi-Urban Areas', 0.95),
('Rural Areas', 0.90),
('Industrial Areas', 1.15);

INSERT INTO construction_packages (package_name, slug, description, base_price, price_per_sqft, includes_gst, delivery_time_months, status) VALUES
('Basic Package', 'basic-package', 'Essential construction services with quality materials.', 500000.00, 1500.00, 1, 8, 'active'),
('Standard Package', 'standard-package', 'Comprehensive construction with premium finishes.', 800000.00, 1800.00, 1, 10, 'active'),
('Premium Package', 'premium-package', 'Luxury construction with high-end materials.', 1200000.00, 2200.00, 1, 12, 'active');
INSERT INTO package_features (package_id, feature_name) VALUES
(1, 'Structural Design'), (1, 'Basic Materials'), (1, 'Standard Flooring'), (1, 'Basic Plumbing'), (1, 'Standard Electrical'),
(2, 'Structural Design'), (2, 'Premium Materials'), (2, 'Vitrified Flooring'), (2, 'Premium Plumbing'), (2, 'Modular Electrical'), (2, 'Painting'), (2, 'Fixtures'),
(3, 'Structural Design'), (3, 'Luxury Materials'), (3, 'Italian Flooring'), (3, 'Smart Plumbing'), (3, 'Smart Electrical'), (3, 'Premium Painting'), (3, 'Premium Fixtures'), (3, 'Smart Home Integration');
INSERT INTO about_page (hero_title, hero_description, hero_image, vision_title, vision_description, cta_title, cta_description, cta_button_text, cta_button_link) VALUES
('About KVN Constructions', 'KVN Constructions is a one-stop solution for home construction.', 'assets/images/about/about-hero.jpg', 'Our Core Strength and Vision', 'We pride ourselves on delivering uncompromising quality.', 'Lets Build Your Dream Home', 'Partner with KVN Constructions for premium construction.', 'Contact Us', 'contact.php');
INSERT INTO about_advantages (title, description, icon, sort_order, status) VALUES
('Expert Architecture', 'We house a dedicated team of experts.', 'bi bi-building', 1, 'active'),
('Precision Design Validation', 'Structural engineers ensure all designs comply.', 'bi bi-check-circle', 2, 'active'),
('Dedicated Construction Oversight', 'Every project is assigned a dedicated site engineer.', 'bi bi-person-workspace', 3, 'active'),
('Reliable Key Handover', 'We strictly follow construction timelines.', 'bi bi-house-check', 4, 'active');
INSERT INTO about_process_steps (step_title, step_description, sort_order, status) VALUES
('Stage 1 - Client Requirements', 'We begin by understanding client requirements.', 1, 'active'),
('Stage 2 - Design Specifications', 'Our architectural team creates conceptual plans.', 2, 'active'),
('Stage 3 - Transparent Agreement', 'We finalize project costing.', 3, 'active'),
('Stage 4 - Construction Execution', 'Our site engineer oversees construction.', 4, 'active'),
('Stage 5 - Quality Checks', 'Comprehensive quality checks at each milestone.', 5, 'active'),
('Stage 6 - Final Handover', 'After final inspection, we hand over keys.', 6, 'active');
INSERT INTO about_specifications (spec_title, spec_value, sort_order, status) VALUES
('Years of Experience', '15+', 1, 'active'),
('Projects Completed', '200+', 2, 'active'),
('Happy Clients', '180+', 3, 'active'),
('Team Members', '50+', 4, 'active'),
('Areas Served', '25+', 5, 'active'),
('Customer Rating', '4.8/5', 6, 'active');

INSERT INTO services (service_name, slug, description, icon, short_description, featured, status) VALUES
('Residential Construction', 'residential-construction', 'Complete home construction services.', 'bi bi-house-door', 'Expert residential construction', 1, 'active'),
('Architectural Design', 'architectural-design', 'Professional architectural services.', 'bi bi-pen', 'Creative architectural designs', 1, 'active'),
('Commercial Construction', 'commercial-construction', 'State-of-the-art commercial spaces.', 'bi bi-building', 'Reliable commercial solutions', 0, 'active'),
('Interior Design', 'interior-design', 'Transform your spaces.', 'bi bi-lamp', 'Elegant interior design', 0, 'active'),
('Renovation Services', 'renovation-services', 'Upgrade and modernize.', 'bi bi-tools', 'Complete renovation services', 0, 'active'),
('Project Management', 'project-management', 'End-to-end project management.', 'bi bi-clipboard-check', 'Professional oversight', 1, 'active');
INSERT INTO faqs (question, answer, category, sort_order, status) VALUES
('What is the typical timeline for home construction?', 'A standard residential project takes 8-12 months.', 'Construction', 1, 'active'),
('Do you provide free estimates?', 'Yes, we offer free consultations.', 'Estimates', 2, 'active'),
('What payment options do you offer?', 'We offer flexible payment schedules.', 'Payment', 3, 'active'),
('Do you handle vastu compliance?', 'Yes, all our designs ensure vastu compliance.', 'Design', 4, 'active'),
('Do you provide warranties?', 'Yes, we provide comprehensive warranties.', 'Warranty', 5, 'active'),
('Can I customize my home design?', 'Absolutely! We encourage customization.', 'Design', 6, 'active');
INSERT INTO testimonials (client_name, client_location, review, rating, project_type, status) VALUES
('Rajesh Kumar', 'Whitefield, Bangalore', 'KVN Construction delivered our dream home exactly on time.', 5, 'Premium Residential', 'active'),
('Priya Sharma', 'HSR Layout, Bangalore', 'From design to handover, KVN exceeded our expectations.', 5, 'Standard Residential', 'active'),
('Anand Reddy', 'Electronic City, Bangalore', 'They delivered a beautiful home within our budget.', 5, 'Basic Residential', 'active'),
('Meera Nair', 'Marathahalli, Bangalore', 'Professional, reliable, and quality-focused.', 5, 'Premium Residential', 'active');
COMMIT;
