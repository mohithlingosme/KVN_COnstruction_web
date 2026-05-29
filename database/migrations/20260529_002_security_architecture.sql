-- KVN Construction Platform
-- Complete Security Architecture Migration
-- Version: 1.0
-- Date: 2026-05-29
-- Description: Complete security and authentication infrastructure migration
-- Status: PRODUCTION-READY

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

/*
|--------------------------------------------------------------------------
| PART 1: CORE SECURITY TABLES
|--------------------------------------------------------------------------
| These tables form the foundation of the security architecture.
| All tables use InnoDB engine with utf8mb4 charset for full Unicode support.
|--------------------------------------------------------------------------
*/

-- ============================================================
-- RATE LIMITS TABLE
-- Purpose: Track and enforce rate limits for all actions
-- Referenced by: helpers/rateLimiter.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `identifier` VARCHAR(255) NOT NULL COMMENT 'Hashed identifier (IP + User-Agent + action)',
    `action_type` VARCHAR(100) NOT NULL COMMENT 'Action being limited (login, otp, etc)',
    `route_name` VARCHAR(255) DEFAULT NULL COMMENT 'Route being accessed',
    `attempts` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Current attempt count',
    `blocked_until` DATETIME DEFAULT NULL COMMENT 'Until when this identifier is blocked',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_rate_limits_id` (`id`),
    KEY `idx_rate_limits_identifier_action` (`identifier`(100), `action_type`),
    KEY `idx_rate_limits_route` (`route_name`),
    KEY `idx_rate_limits_blocked` (`blocked_until`),
    KEY `idx_rate_limits_updated` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Rate limiting tracking for brute force protection';

-- ============================================================
-- USER DEVICES TABLE
-- Purpose: Track user devices for security monitoring
-- Referenced by: helpers/session.php (trackUserDevice)
-- ============================================================

CREATE TABLE IF NOT EXISTS `user_devices` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Reference to users table',
    `device_name` VARCHAR(255) DEFAULT NULL COMMENT 'Browser/device identifier',
    `device_hash` VARCHAR(255) NOT NULL COMMENT 'SHA-256 hash of device characteristics',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Last known IP address',
    `last_used_at` DATETIME DEFAULT NULL COMMENT 'Last time this device was used',
    `is_trusted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'User marked this device as trusted',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_devices_id` (`id`),
    KEY `idx_user_devices_user` (`user_id`),
    KEY `idx_user_devices_hash` (`device_hash`(100)),
    KEY `idx_user_devices_trusted` (`user_id`, `is_trusted`),
    CONSTRAINT `fk_user_devices_user` FOREIGN KEY (`user_id`)
        REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='User device tracking for login security';

-- ============================================================
-- SECURITY LOGS TABLE
-- Purpose: Centralized security event logging
-- Referenced by: helpers/security.php (logSecurityEvent)
-- ============================================================

CREATE TABLE IF NOT EXISTS `security_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'User ID if authenticated',
    `event_type` VARCHAR(100) NOT NULL COMMENT 'Type of security event',
    `event_level` ENUM('info', 'warning', 'critical') NOT NULL DEFAULT 'info' COMMENT 'Severity level',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Client IP address',
    `user_agent` VARCHAR(255) DEFAULT NULL COMMENT 'Browser user agent',
    `event_details` TEXT DEFAULT NULL COMMENT 'JSON or text details of the event',
    `request_uri` VARCHAR(255) DEFAULT NULL COMMENT 'Requested URI',
    `request_method` VARCHAR(10) DEFAULT NULL COMMENT 'HTTP method',
    `created_by_system` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'System-generated event',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_security_logs_id` (`id`),
    KEY `idx_security_logs_user_event` (`user_id`, `event_type`, `created_at`),
    KEY `idx_security_logs_level_created` (`event_level`, `created_at`),
    KEY `idx_security_logs_ip` (`ip_address`, `created_at`),
    KEY `idx_security_logs_type` (`event_type`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Security event logging for audit and monitoring';

-- ============================================================
-- AUDIT LOGS TABLE
-- Purpose: Track user actions for compliance and audit
-- Referenced by: helpers/security.php (logAdminAction)
-- ============================================================

CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'User who performed action',
    `action_type` VARCHAR(100) NOT NULL COMMENT 'Action performed',
    `description` TEXT DEFAULT NULL COMMENT 'Human-readable description',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Client IP address',
    `entity_type` VARCHAR(100) DEFAULT NULL COMMENT 'Entity type affected',
    `entity_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Entity ID affected',
    `old_values` TEXT DEFAULT NULL COMMENT 'Previous values (JSON)',
    `new_values` TEXT DEFAULT NULL COMMENT 'New values (JSON)',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_audit_logs_id` (`id`),
    KEY `idx_audit_logs_user_action` (`user_id`, `action_type`, `created_at`),
    KEY `idx_audit_logs_entity` (`entity_type`, `entity_id`),
    KEY `idx_audit_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Audit trail for user actions and changes';

-- ============================================================
-- OTP STORAGE TABLE
-- Purpose: Store OTP codes for authentication
-- Referenced by: helpers/otp.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `otps` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'User ID if registered',
    `phone` VARCHAR(20) DEFAULT NULL COMMENT 'Phone number for OTP',
    `email` VARCHAR(150) DEFAULT NULL COMMENT 'Email for OTP',
    `otp_code` VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed OTP',
    `otp_type` VARCHAR(50) NOT NULL COMMENT 'Type: login, password_reset, phone_verification',
    `attempts` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Failed verification attempts',
    `resend_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of resends',
    `is_used` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'OTP has been used',
    `expires_at` DATETIME NOT NULL COMMENT 'Expiration timestamp',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_sent_at` DATETIME DEFAULT NULL COMMENT 'Last time OTP was sent',
    `verified_at` DATETIME DEFAULT NULL COMMENT 'When OTP was verified',
    `used_at` DATETIME DEFAULT NULL COMMENT 'When OTP was used',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP address that requested OTP',
    `user_agent` VARCHAR(255) DEFAULT NULL COMMENT 'Browser that requested OTP',
    `verified` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Verification status',
    UNIQUE KEY `uk_otps_id` (`id`),
    KEY `idx_otps_user_type` (`user_id`, `otp_type`, `is_used`),
    KEY `idx_otps_phone_type` (`phone`, `otp_type`, `is_used`),
    KEY `idx_otps_email_type` (`email`, `otp_type`, `is_used`),
    KEY `idx_otps_expires` (`expires_at`),
    KEY `idx_otps_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='One-time password storage for authentication';

-- ============================================================
-- LOGIN ATTEMPTS TABLE
-- Purpose: Track login attempts for security monitoring
-- Referenced by: User model for account lockout
-- ============================================================

CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(150) DEFAULT NULL COMMENT 'Email used for login',
    `phone` VARCHAR(20) DEFAULT NULL COMMENT 'Phone used for login',
    `ip_address` VARCHAR(45) NOT NULL COMMENT 'IP address of attempt',
    `user_agent` VARCHAR(255) DEFAULT NULL COMMENT 'Browser user agent',
    `success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Whether login succeeded',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_login_attempts_id` (`id`),
    KEY `idx_login_attempts_ip` (`ip_address`, `created_at`),
    KEY `idx_login_attempts_email` (`email`, `created_at`),
    KEY `idx_login_attempts_phone` (`phone`, `created_at`),
    KEY `idx_login_attempts_success` (`success`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Login attempt tracking for security monitoring';

-- ============================================================
-- REMEMBER TOKENS TABLE
-- Purpose: Store remember me tokens for persistent sessions
-- Referenced by: helpers/session.php (remember me functionality)
-- ============================================================

CREATE TABLE IF NOT EXISTS `remember_tokens` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'User ID',
    `token_hash` VARCHAR(255) NOT NULL COMMENT 'SHA-256 hash of token',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP address when created',
    `user_agent` VARCHAR(255) DEFAULT NULL COMMENT 'Browser when created',
    `expires_at` DATETIME NOT NULL COMMENT 'Token expiration',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_remember_tokens_id` (`id`),
    KEY `idx_remember_tokens_user` (`user_id`),
    KEY `idx_remember_tokens_hash` (`token_hash`(100)),
    KEY `idx_remember_tokens_expires` (`expires_at`),
    CONSTRAINT `fk_remember_tokens_user` FOREIGN KEY (`user_id`)
        REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Remember me token storage for persistent login';

-- ============================================================
-- EMAIL VERIFICATION TOKENS TABLE
-- Purpose: Store email verification tokens
-- Referenced by: AuthService, public/verify-email.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `email_verification_tokens` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'User ID',
    `token_hash` VARCHAR(255) NOT NULL COMMENT 'SHA-256 hash of token',
    `verified_at` DATETIME DEFAULT NULL COMMENT 'When token was verified',
    `expires_at` DATETIME NOT NULL COMMENT 'Token expiration',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_email_verification_tokens_hash` (`token_hash`),
    KEY `idx_email_verification_user` (`user_id`),
    KEY `idx_email_verification_expires` (`expires_at`),
    CONSTRAINT `fk_email_verification_user` FOREIGN KEY (`user_id`)
        REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Email verification token storage';

-- ============================================================
-- MAIL LOGS TABLE
-- Purpose: Track email delivery for audit
-- Referenced by: helpers/mail.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `mail_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `recipient` VARCHAR(150) NOT NULL COMMENT 'Email recipient',
    `subject` VARCHAR(255) NOT NULL COMMENT 'Email subject',
    `status` ENUM('success', 'failed', 'pending') NOT NULL DEFAULT 'pending' COMMENT 'Delivery status',
    `error_message` TEXT DEFAULT NULL COMMENT 'Error details if failed',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Request IP address',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_mail_logs_id` (`id`),
    KEY `idx_mail_logs_recipient` (`recipient`),
    KEY `idx_mail_logs_status_created` (`status`, `created_at`),
    KEY `idx_mail_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Email delivery logging for audit';

-- ============================================================
-- SMS LOGS TABLE
-- Purpose: Track SMS delivery for audit
-- Referenced by: helpers/sms.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `sms_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `phone` VARCHAR(20) NOT NULL COMMENT 'Phone number',
    `message` TEXT NOT NULL COMMENT 'SMS message content',
    `status` ENUM('success', 'failed') NOT NULL COMMENT 'Delivery status',
    `provider_response` TEXT DEFAULT NULL COMMENT 'API response details',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_sms_logs_id` (`id`),
    KEY `idx_sms_logs_phone` (`phone`),
    KEY `idx_sms_logs_created` (`created_at`),
    KEY `idx_sms_logs_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='SMS delivery logging for audit';

-- ============================================================
-- CLIENT MESSAGES TABLE
-- Purpose: Messages between clients and admin
-- Referenced by: public/client/dashboard.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `client_messages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `client_id` BIGINT UNSIGNED NOT NULL COMMENT 'Client user ID',
    `subject` VARCHAR(255) DEFAULT NULL COMMENT 'Message subject',
    `message` TEXT DEFAULT NULL COMMENT 'Message content',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Read status',
    `read_at` DATETIME DEFAULT NULL COMMENT 'When message was read',
    `replied_at` DATETIME DEFAULT NULL COMMENT 'When message was replied',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_client_messages_id` (`id`),
    KEY `idx_client_messages_client` (`client_id`),
    KEY `idx_client_messages_read` (`is_read`, `created_at`),
    CONSTRAINT `fk_client_messages_user` FOREIGN KEY (`client_id`)
        REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Client support messages';

-- ============================================================
-- SUSPICIOUS ACTIVITY LOG TABLE
-- Purpose: Dedicated table for suspicious activity tracking
-- Referenced by: helpers/security.php (suspiciousActivity)
-- ============================================================

CREATE TABLE IF NOT EXISTS `suspicious_activity` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'User ID if available',
    `ip_address` VARCHAR(45) NOT NULL COMMENT 'IP address',
    `activity_type` VARCHAR(100) NOT NULL COMMENT 'Type of suspicious activity',
    `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium' COMMENT 'Severity level',
    `details` TEXT DEFAULT NULL COMMENT 'JSON details',
    `resolved` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Whether investigated',
    `resolved_at` DATETIME DEFAULT NULL COMMENT 'When resolved',
    `resolved_by` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Admin who resolved',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_suspicious_activity_id` (`id`),
    KEY `idx_suspicious_activity_user` (`user_id`),
    KEY `idx_suspicious_activity_ip` (`ip_address`, `created_at`),
    KEY `idx_suspicious_activity_type` (`activity_type`, `created_at`),
    KEY `idx_suspicious_activity_severity` (`severity`, `created_at`),
    KEY `idx_suspicious_activity_unresolved` (`resolved`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Dedicated suspicious activity tracking';

-- ============================================================
-- SESSION HISTORY TABLE
-- Purpose: Historical session data for audit
-- Referenced by: cleanup processes
-- ============================================================

CREATE TABLE IF NOT EXISTS `session_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   _id` BIGINT UNSIGNED NOT NULL COMMENT 'User ID',
    `session_token_hash` VARCHAR(255) NOT NULL COMMENT 'Hashed session token',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP address',
    `user_agent` VARCHAR(255) DEFAULT NULL COMMENT 'Browser',
    `started_at` DATETIME NOT NULL COMMENT 'Session start',
    `ended_at` DATETIME DEFAULT NULL COMMENT 'Session end',
    `end_reason` VARCHAR(100) DEFAULT NULL COMMENT 'Reason for ending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_session_history_id` (`id`),
    KEY `idx_session_history_user` (`user_id`),
    KEY `idx_session_history_started` (`started_at`),
    KEY `idx_session_history_ended` (`ended_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historical session data for audit';

-- ============================================================
-- ADMIN ACTION LOGS TABLE
-- Purpose: Specific admin action audit trail
-- Referenced by: middleware/admin.php
-- ============================================================

CREATE TABLE IF NOT EXISTS `admin_action_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `admin_id` BIGINT UNSIGNED NOT NULL COMMENT 'Admin user ID',
    `action` VARCHAR(100) NOT NULL COMMENT 'Action performed',
    `entity_type` VARCHAR(100) DEFAULT NULL COMMENT 'Entity type affected',
    `entity_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Entity ID affected',
    `old_data` TEXT DEFAULT NULL COMMENT 'Previous data',
    `new_data` TEXT DEFAULT NULL COMMENT 'New data',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP address',
    `user_agent` VARCHAR(255) DEFAULT NULL COMMENT 'Browser',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_admin_action_logs_id` (`id`),
    KEY `idx_admin_action_logs_admin` (`admin_id`, `created_at`),
    KEY `idx_admin_action_logs_entity` (`entity_type`, `entity_id`),
    KEY `idx_admin_action_logs_action` (`action`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Admin action audit trail';

-- ============================================================
-- PASSWORD HISTORY TABLE
-- Purpose: Track password history to prevent reuse
-- Referenced by: User model
-- ============================================================

CREATE TABLE IF NOT EXISTS `password_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'User ID',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'Old password hash',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_password_history_id` (`id`),
    KEY `idx_password_history_user` (`user_id`, `created_at`),
    CONSTRAINT `fk_password_history_user` FOREIGN KEY (`user_id`)
        REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Password change history for reuse prevention';

/*
|--------------------------------------------------------------------------
| PART 2: ALTER EXISTING TABLES
|--------------------------------------------------------------------------
| Add missing columns to existing tables for security features
|--------------------------------------------------------------------------
*/

-- ============================================================
-- ALTER USER_SESSIONS TABLE
-- ============================================================

ALTER TABLE `user_sessions`
    ADD COLUMN IF NOT EXISTS `fingerprint_hash` VARCHAR(255) DEFAULT NULL
        AFTER `remember_token`,
    ADD COLUMN IF NOT EXISTS `last_activity` DATETIME DEFAULT NULL
        AFTER `expires_at`,
    ADD COLUMN IF NOT EXISTS `logout_reason` VARCHAR(100) DEFAULT NULL
        AFTER `revoked_at`,
    ADD INDEX IF NOT EXISTS `idx_user_sessions_active`
        (`user_id`, `is_active`, `revoked_at`),
    ADD INDEX IF NOT EXISTS `idx_user_sessions_remember_token`
        (`remember_token`),
    ADD INDEX IF NOT EXISTS `idx_user_sessions_expires_at`
        (`expires_at`);

-- ============================================================
-- ALTER USERS TABLE
-- ============================================================

ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `last_activity_at` DATETIME DEFAULT NULL
        AFTER `last_login`,
    ADD COLUMN IF NOT EXISTS `last_ip` VARCHAR(45) DEFAULT NULL
        AFTER `last_login_user_agent`,
    ADD COLUMN IF NOT EXISTS `last_user_agent` VARCHAR(255) DEFAULT NULL
        AFTER `last_ip`,
    ADD COLUMN IF NOT EXISTS `failed_login_attempts` INT UNSIGNED DEFAULT 0
        AFTER `failed_attempts`,
    ADD COLUMN IF NOT EXISTS `locked_until` DATETIME DEFAULT NULL
        AFTER `failed_login_attempts`,
    ADD COLUMN IF NOT EXISTS `password_changed_at` DATETIME DEFAULT NULL
        AFTER `password`,
    ADD COLUMN IF NOT EXISTS `last_password_change` DATETIME DEFAULT NULL
        AFTER `password_changed_at`,
    ADD COLUMN IF NOT EXISTS `email_verified` TINYINT(1) DEFAULT 0
        AFTER `email_verified_at`,
    ADD COLUMN IF NOT EXISTS `phone_verified` TINYINT(1) DEFAULT 0
        AFTER `email_verified`,
    ADD COLUMN IF NOT EXISTS `email_verified_at` DATETIME DEFAULT NULL
        AFTER `phone_verified`,
    ADD COLUMN IF NOT EXISTS `phone_verified_at` DATETIME DEFAULT NULL
        AFTER `email_verified_at`;

-- ============================================================
-- ALTER SECURITY_LOGS TABLE
-- ============================================================

ALTER TABLE `security_logs`
    ADD INDEX IF NOT EXISTS `idx_security_logs_user_event`
        (`user_id`, `event_type`, `created_at`),
    ADD INDEX IF NOT EXISTS `idx_security_logs_level_created`
        (`event_level`, `created_at`);

-- ============================================================
-- ALTER LEADS TABLE
-- ============================================================

ALTER TABLE `leads`
    ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME DEFAULT NULL
        AFTER `updated_at`,
    ADD COLUMN IF NOT EXISTS `deleted_by` BIGINT UNSIGNED DEFAULT NULL
        AFTER `deleted_at`,
    ADD COLUMN IF NOT EXISTS `source_detail` VARCHAR(255) DEFAULT NULL
        AFTER `source`;

-- ============================================================
-- ALTER OTP_ATTEMPTS TABLE (if exists)
-- ============================================================

-- Note: This table may not exist in older schemas
-- CREATE TABLE IF NOT EXISTS `otp_attempts` similar to `login_attempts`

/*
|--------------------------------------------------------------------------
| PART 3: SEED DATA
|--------------------------------------------------------------------------
| Insert required seed data for the security system
|--------------------------------------------------------------------------
*/

-- ============================================================
-- RATE LIMIT CONFIGURATIONS SEED
-- ============================================================

-- Note: rate_limits table is populated dynamically
-- These are reference values for configuration

INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
    ('security.login_rate_limit', '5'),
    ('security.login_rate_window', '300'),
    ('security.admin_login_rate_limit', '3'),
    ('security.admin_login_rate_window', '600'),
    ('security.otp_rate_limit', '3'),
    ('security.otp_rate_window', '600'),
    ('security.max_otp_attempts', '5'),
    ('security.otp_expiry_minutes', '5'),
    ('security.session_timeout', '3600'),
    ('security.admin_session_timeout', '1800'),
    ('security.remember_me_days', '30'),
    ('security.max_login_attempts', '5'),
    ('security.account_lock_duration', '15'),
    ('security.csrf_token_expiry', '1800')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

/*
|--------------------------------------------------------------------------
| PART 4: VIEWS FOR MONITORING
|--------------------------------------------------------------------------
| Create views for security monitoring dashboards
|--------------------------------------------------------------------------
*/

-- ============================================================
-- SECURITY OVERVIEW VIEW
-- ============================================================

CREATE OR REPLACE VIEW `security_overview` AS
SELECT
    COUNT(DISTINCT CASE WHEN event_level = 'critical' THEN id END) AS critical_events,
    COUNT(DISTINCT CASE WHEN event_level = 'warning' THEN id END) AS warning_events,
    COUNT(DISTINCT CASE WHEN event_level = 'info' THEN id END) AS info_events,
    COUNT(DISTINCT CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN id END) AS events_today,
    COUNT(DISTINCT CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND event_level = 'critical' THEN id END) AS critical_last_hour
FROM security_logs
WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY);

-- ============================================================
-- ACTIVE SESSIONS VIEW
-- ============================================================

CREATE OR REPLACE VIEW `active_sessions_view` AS
SELECT
    u.id AS user_id,
    u.full_name,
    u.email,
    u.role,
    us.session_token,
    us.ip_address,
    us.user_agent,
    us.last_activity,
    us.created_at AS session_started,
    TIMESTAMPDIFF(MINUTE, us.last_activity, NOW()) AS minutes_inactive
FROM user_sessions us
INNER JOIN users u ON u.id = us.user_id
WHERE us.is_active = 1
    AND us.revoked_at IS NULL
    AND (us.expires_at IS NULL OR us.expires_at > NOW())
ORDER BY us.last_activity DESC;

-- ============================================================
-- FAILED LOGIN ATTEMPTS VIEW
-- ============================================================

CREATE OR REPLACE VIEW `failed_login_attempts_view` AS
SELECT
    ip_address,
    email,
    COUNT(*) AS attempt_count,
    MAX(created_at) AS last_attempt,
    GROUP_CONCAT(DISTINCT user_agent SEPARATOR ' | ') AS user_agents
FROM login_attempts
WHERE success = 0
    AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY ip_address, email
HAVING COUNT(*) >= 3
ORDER BY attempt_count DESC;

-- ============================================================
-- SUSPICIOUS ACTIVITY VIEW
-- ============================================================

CREATE OR REPLACE VIEW `suspicious_activity_view` AS
SELECT
    sa.*,
    u.full_name AS user_name,
    u.email AS user_email
FROM suspicious_activity sa
LEFT JOIN users u ON u.id = sa.user_id
WHERE sa.resolved = 0
    AND sa.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY
    CASE sa.severity
        WHEN 'critical' THEN 1
        WHEN 'high' THEN 2
        WHEN 'medium' THEN 3
        WHEN 'low' THEN 4
    END,
    sa.created_at DESC;

/*
|--------------------------------------------------------------------------
| PART 5: TRIGGERS FOR AUTOMATIC LOGGING
|--------------------------------------------------------------------------
| Create triggers for automatic audit logging
|--------------------------------------------------------------------------
*/

DELIMITER //

-- ============================================================
-- TRIGGER: Log user password changes
-- ============================================================

CREATE TRIGGER IF NOT EXISTS `tr_user_password_change`
AFTER UPDATE ON `users`
FOR EACH ROW
BEGIN
    IF OLD.password != NEW.password THEN
        INSERT INTO password_history (user_id, password_hash, created_at)
        VALUES (OLD.id, OLD.password, NOW());

        INSERT INTO audit_logs (user_id, action_type, description, ip_address, entity_type, entity_id)
        VALUES (
            NEW.id,
            'password_changed',
            'User password changed',
            NEW.last_ip,
            'user',
            NEW.id
        );
    END IF;
END//

-- ============================================================
-- TRIGGER: Log user deletions
-- ============================================================

CREATE TRIGGER IF NOT EXISTS `tr_user_delete`
BEFORE DELETE ON `users`
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (user_id, action_type, description, ip_address, entity_type, entity_id, old_values)
    VALUES (
        OLD.id,
        'user_deleted',
        'User account deleted',
        OLD.last_ip,
        'user',
        OLD.id,
        CONCAT('{"email":"', OLD.email, '","role":"', OLD.role, '"}')
    );
END//

-- ============================================================
-- TRIGGER: Update last activity on session
-- ============================================================

CREATE TRIGGER IF NOT EXISTS `tr_session_activity`
AFTER UPDATE ON `user_sessions`
FOR EACH ROW
BEGIN
    IF OLD.last_activity != NEW.last_activity THEN
        -- Could insert into session_history if needed
        NULL;
    END IF;
END//

DELIMITER ;

/*
|--------------------------------------------------------------------------
| PART 6: CLEANUP PROCEDURES
|--------------------------------------------------------------------------
| Create stored procedures for security maintenance
|--------------------------------------------------------------------------
*/

DELIMITER //

-- ============================================================
-- PROCEDURE: Cleanup expired security data
-- ============================================================

CREATE PROCEDURE IF NOT EXISTS `sp_cleanup_security_data`()
BEGIN
    -- Cleanup expired OTPs
    DELETE FROM otps WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 DAY);
    DELETE FROM otps WHERE is_used = 1 AND created_at < DATE_SUB(NOW(), INTERVAL 1 DAY);

    -- Cleanup expired rate limits
    DELETE FROM rate_limits WHERE updated_at < DATE_SUB(NOW(), INTERVAL 1 DAY);

    -- Cleanup expired remember tokens
    DELETE FROM remember_tokens WHERE expires_at < NOW();

    -- Cleanup expired email verification tokens
    DELETE FROM email_verification_tokens WHERE expires_at < NOW();

    -- Cleanup old security logs (keep 90 days)
    DELETE FROM security_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

    -- Cleanup old audit logs (keep 1 year)
    DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

    -- Cleanup old login attempts (keep 30 days)
    DELETE FROM login_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

    -- Mark inactive sessions
    UPDATE user_sessions
    SET is_active = 0, revoked_at = NOW(), logout_reason = 'cleanup_expired'
    WHERE is_active = 1
        AND (
            (expires_at IS NOT NULL AND expires_at < NOW())
            OR last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY)
        );
END//

-- ============================================================
-- PROCEDURE: Check for brute force attacks
-- ============================================================

CREATE PROCEDURE IF NOT EXISTS `sp_check_brute_force`()
BEGIN
    -- Lock accounts with too many failed attempts
    UPDATE users
    SET locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
    WHERE failed_login_attempts >= 5
        AND (locked_until IS NULL OR locked_until < NOW())
        AND status = 'active';
END//

-- ============================================================
-- PROCEDURE: Get security stats
-- ============================================================

CREATE PROCEDURE IF NOT EXISTS `sp_get_security_stats`()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM users WHERE status = 'active') AS active_users,
        (SELECT COUNT(*) FROM user_sessions WHERE is_active = 1 AND revoked_at IS NULL) AS active_sessions,
        (SELECT COUNT(*) FROM login_attempts WHERE success = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) AS failed_logins_today,
        (SELECT COUNT(*) FROM security_logs WHERE event_level = 'critical' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) AS critical_events_today,
        (SELECT COUNT(*) FROM suspicious_activity WHERE resolved = 0) AS unresolved_suspicious,
        (SELECT COUNT(*) FROM otps WHERE is_used = 0 AND expires_at > NOW()) AS active_otps;
END//

DELIMITER ;

/*
|--------------------------------------------------------------------------
| PART 7: INDEX OPTIMIZATION
|--------------------------------------------------------------------------
| Additional indexes for performance
|--------------------------------------------------------------------------
*/

-- Users table indexes for security queries
ALTER TABLE `users`
    ADD INDEX IF NOT EXISTS `idx_users_email` (`email`),
    ADD INDEX IF NOT EXISTS `idx_users_phone` (`phone`),
    ADD INDEX IF NOT EXISTS `idx_users_role` (`role`),
    ADD INDEX IF NOT EXISTS `idx_users_status` (`status`);

-- Sessions table for concurrent session control
ALTER TABLE `user_sessions`
    ADD INDEX IF NOT EXISTS `idx_user_sessions_user_active` (`user_id`, `is_active`);

-- OTP table for verification performance
ALTER TABLE `otps`
    ADD INDEX IF NOT EXISTS `idx_otps_user_created` (`user_id`, `created_at`);

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;

/*
|--------------------------------------------------------------------------
| MIGRATION COMPLETE
|--------------------------------------------------------------------------
| This migration creates the complete security infrastructure for the
| KVN Construction platform. All tables are production-ready with
| appropriate indexes, foreign keys, and constraints.
|--------------------------------------------------------------------------
|
| VERIFICATION QUERIES
|--------------------------------------------------------------------------
|
| Run these to verify migration success:
|
| 1. Check all tables exist:
|    SHOW TABLES LIKE '%log%';
|    SHOW TABLES LIKE '%session%';
|    SHOW TABLES LIKE '%otp%';
|    SHOW TABLES LIKE '%device%';
|    SHOW TABLES LIKE '%token%';
|
| 2. Check table structures:
|    DESCRIBE rate_limits;
|    DESCRIBE security_logs;
|    DESCRIBE otps;
|    DESCRIBE user_devices;
|
| 3. Check indexes:
|    SHOW INDEX FROM user_sessions;
|    SHOW INDEX FROM security_logs;
|
| 4. Check triggers:
|    SHOW TRIGGERS LIKE 'tr_%';
|
| 5. Check stored procedures:
|    SHOW PROCEDURE STATUS WHERE Name LIKE 'sp_%';
|
| ROLLBACK INSTRUCTIONS
|--------------------------------------------------------------------------
|
| If rollback is needed, run:
|
| DROP TRIGGER IF EXISTS tr_user_password_change;
| DROP TRIGGER IF EXISTS tr_user_delete;
| DROP TRIGGER IF EXISTS tr_session_activity;
| DROP PROCEDURE IF EXISTS sp_cleanup_security_data;
| DROP PROCEDURE IF EXISTS sp_check_brute_force;
| DROP PROCEDURE IF EXISTS sp_get_security_stats;
| DROP VIEW IF EXISTS security_overview;
| DROP VIEW IF EXISTS active_sessions_view;
| DROP VIEW IF EXISTS failed_login_attempts_view;
| DROP VIEW IF EXISTS suspicious_activity_view;
| -- Then drop all security tables in reverse order
|
|--------------------------------------------------------------------------
*/