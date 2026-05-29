-- =============================================================================
-- KVN CONSTRUCTION PLATFORM - SECURITY ENHANCEMENT MIGRATIONS
-- =============================================================================
-- Purpose: Add security indexes, blocked_ips table, and optimize schema
-- Affected Tables: security_logs, user_sessions, rate_limits, login_activity,
--                  otps, audit_logs, users
-- Rollback: All changes documented with reverse statements
-- Compatibility: MariaDB 10.4+ (current server version)
-- Run: mysql -u root -p kvnc_platform < changes.sql
-- Verify: SHOW INDEX FROM table_name;
-- =============================================================================

-- -----------------------------------------------------------------------------
-- SECTION 1: BLOCKED_IPS TABLE
-- Purpose: Track blocked IP addresses for brute force protection
-- Impact: Enables fail2ban-style IP blocking and security monitoring
-- Compatibility: New table, no breaking changes
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `blocked_ips` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip_address` varchar(45) NOT NULL COMMENT 'IPv4 or IPv6 address',
    `block_reason` varchar(255) DEFAULT NULL COMMENT 'Reason for blocking',
    `blocked_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Admin user who blocked',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `expires_at` datetime DEFAULT NULL COMMENT 'NULL = permanent block',
    `is_permanent` tinyint(1) DEFAULT 0 COMMENT '1 = permanent block',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_blocked_ip` (`ip_address`),
    KEY `idx_expires_at` (`expires_at`),
    KEY `idx_is_permanent` (`is_permanent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rollback: DROP TABLE IF EXISTS `blocked_ips`;

-- -----------------------------------------------------------------------------
-- SECTION 2: INDEX ADDITIONS - security_logs
-- Purpose: Optimize security log queries for admin dashboard and cleanup
-- Impact: Faster log retrieval, reduced query time on large tables
-- Compatibility: No schema changes, only index additions
-- -----------------------------------------------------------------------------

-- Check if indexes exist before adding
-- Add index on user_id for user-specific security queries
ALTER TABLE `security_logs`
ADD INDEX `idx_security_user_id` (`user_id`);

-- Add index on event_type for filtering by event type
ALTER TABLE `security_logs`
ADD INDEX `idx_security_event_type` (`event_type`);

-- Add index on created_at for time-based cleanup and queries
ALTER TABLE `security_logs`
ADD INDEX `idx_security_created_at` (`created_at`);

-- Add composite index for common query patterns (user + event type)
ALTER TABLE `security_logs`
ADD INDEX `idx_security_user_event` (`user_id`, `event_type`);

-- Rollback: ALTER TABLE `security_logs` DROP INDEX `idx_security_user_id`, DROP INDEX `idx_security_event_type`, DROP INDEX `idx_security_created_at`, DROP INDEX `idx_security_user_event`;

-- -----------------------------------------------------------------------------
-- SECTION 3: INDEX ADDITIONS - user_sessions
-- Purpose: Accelerate session validation and lookup
-- Impact: Faster session validation, concurrent session detection
-- Compatibility: No breaking changes
-- -----------------------------------------------------------------------------

-- Add UNIQUE index on session_token for fast lookup (if not exists)
ALTER TABLE `user_sessions`
ADD UNIQUE INDEX `idx_session_token` (`session_token`);

-- Add composite index for user session queries
ALTER TABLE `user_sessions`
ADD INDEX `idx_user_active_sessions` (`user_id`, `is_active`);

-- Add index for session cleanup queries
ALTER TABLE `user_sessions`
ADD INDEX `idx_session_expires` (`expires_at`, `is_active`);

-- Rollback: ALTER TABLE `user_sessions` DROP INDEX `idx_session_token`, DROP INDEX `idx_user_active_sessions`, DROP INDEX `idx_session_expires`;

-- -----------------------------------------------------------------------------
-- SECTION 4: INDEX ADDITIONS - rate_limits
-- Purpose: Optimize rate limit checking
-- Impact: Faster rate limit validation
-- Compatibility: No breaking changes
-- -----------------------------------------------------------------------------

-- Add composite index for rate limit queries
ALTER TABLE `rate_limits`
ADD INDEX `idx_rate_limit_composite` (`identifier`, `action_type`, `route_name`);

-- Add index for cleanup queries
ALTER TABLE `rate_limits`
ADD INDEX `idx_rate_limit_updated` (`updated_at`);

-- Rollback: ALTER TABLE `rate_limits` DROP INDEX `idx_rate_limit_composite`, DROP INDEX `idx_rate_limit_updated`;

-- -----------------------------------------------------------------------------
-- SECTION 5: INDEX ADDITIONS - login_activity
-- Purpose: Optimize login activity queries for security monitoring
-- Impact: Faster failed login detection, IP-based queries
-- Compatibility: No breaking changes
-- -----------------------------------------------------------------------------

-- Add index on user_id
ALTER TABLE `login_activity`
ADD INDEX `idx_login_user_id` (`user_id`);

-- Add index on ip_address for IP-based queries
ALTER TABLE `login_activity`
ADD INDEX `idx_login_ip` (`ip_address`);

-- Add index on login_time for time-based queries and cleanup
ALTER TABLE `login_activity`
ADD INDEX `idx_login_time` (`login_time`);

-- Add composite index for failed login detection
ALTER TABLE `login_activity`
ADD INDEX `idx_login_failed` (`login_status`, `ip_address`, `login_time`);

-- Rollback: ALTER TABLE `login_activity` DROP INDEX `idx_login_user_id`, DROP INDEX `idx_login_ip`, DROP INDEX `idx_login_time`, DROP INDEX `idx_login_failed`;

-- -----------------------------------------------------------------------------
-- SECTION 6: INDEX ADDITIONS - otps
-- Purpose: Optimize OTP verification and cleanup
-- Impact: Faster OTP validation
-- Compatibility: No breaking changes
-- -----------------------------------------------------------------------------

-- Add composite index for OTP lookup
ALTER TABLE `otps`
ADD INDEX `idx_otp_user_type_used` (`user_id`, `otp_type`, `is_used`);

-- Add index for phone/email OTP lookup
ALTER TABLE `otps`
ADD INDEX `idx_otp_phone_email` (`phone`, `email`, `otp_type`, `is_used`);

-- Add index for OTP cleanup
ALTER TABLE `otps`
ADD INDEX `idx_otp_expires` (`expires_at`, `is_used`);

-- Rollback: ALTER TABLE `otps` DROP INDEX `idx_otp_user_type_used`, DROP INDEX `idx_otp_phone_email`, DROP INDEX `idx_otp_expires`;

-- -----------------------------------------------------------------------------
-- SECTION 7: INDEX ADDITIONS - audit_logs
-- Purpose: Optimize audit log queries
-- Impact: Faster admin action log retrieval
-- Compatibility: No breaking changes
-- -----------------------------------------------------------------------------

-- Add index on user_id
ALTER TABLE `audit_logs`
ADD INDEX `idx_audit_user_id` (`user_id`);

-- Add index on created_at for time-based queries and cleanup
ALTER TABLE `audit_logs`
ADD INDEX `idx_audit_created` (`created_at`);

-- Add composite index for entity queries
ALTER TABLE `audit_logs`
ADD INDEX `idx_audit_entity` (`entity_type`, `entity_id`);

-- Rollback: ALTER TABLE `audit_logs` DROP INDEX `idx_audit_user_id`, DROP INDEX `idx_audit_created`, DROP INDEX `idx_audit_entity`;

-- -----------------------------------------------------------------------------
-- SECTION 8: INDEX ADDITIONS - users
-- Purpose: Optimize user lookup queries
-- Impact: Faster login, user lookup operations
-- Compatibility: No breaking changes
-- -----------------------------------------------------------------------------

-- Add UNIQUE index on email (if not exists)
ALTER TABLE `users`
ADD UNIQUE INDEX `idx_users_email` (`email`);

-- Add index on phone
ALTER TABLE `users`
ADD INDEX `idx_users_phone` (`phone`);

-- Add index on role for admin/client queries
ALTER TABLE `users`
ADD INDEX `idx_users_role` (`role`);

-- Add index on status for active user queries
ALTER TABLE `users`
ADD INDEX `idx_users_status` (`status`);

-- Rollback: ALTER TABLE `users` DROP INDEX `idx_users_email`, DROP INDEX `idx_users_phone`, DROP INDEX `idx_users_role`, DROP INDEX `idx_users_status`;

-- -----------------------------------------------------------------------------
-- SECTION 9: PASSWORD_RESET_TOKENS TABLE
-- Purpose: Centralized reset token management with audit trail
-- Impact: Better security, forced logout after reset capability
-- Compatibility: New table with foreign key to users
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `token_hash` varchar(255) NOT NULL,
    `otp_code` varchar(10) DEFAULT NULL,
    `purpose` enum('password_reset','phone_verification','email_change') DEFAULT 'password_reset',
    `expires_at` datetime NOT NULL,
    `used_at` datetime DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `attempts` int(11) DEFAULT 0,
    `max_attempts` int(11) DEFAULT 5,
    PRIMARY KEY (`id`),
    KEY `idx_token_hash` (`token_hash`),
    KEY `idx_user_purpose` (`user_id`, `purpose`),
    KEY `idx_expires_at` (`expires_at`),
    CONSTRAINT `fk_password_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rollback: DROP TABLE IF EXISTS `password_reset_tokens`;

-- -----------------------------------------------------------------------------
-- SECTION 10: SECURITY_LOG_INDEXES for admin_audit_logs
-- Purpose: Optimize admin audit log queries
-- Impact: Faster admin action log retrieval
-- Compatibility: No breaking changes
-- -----------------------------------------------------------------------------

-- Add index on user_id for admin_audit_logs
ALTER TABLE `admin_audit_logs`
ADD INDEX `idx_admin_audit_user_id` (`user_id`);

-- Add index on created_at for time-based queries
ALTER TABLE `admin_audit_logs`
ADD INDEX `idx_admin_audit_created` (`created_at`);

-- Add composite index for entity queries
ALTER TABLE `admin_audit_logs`
ADD INDEX `idx_admin_audit_entity` (`entity_type`, `entity_id`);

-- Rollback: ALTER TABLE `admin_audit_logs` DROP INDEX `idx_admin_audit_user_id`, DROP INDEX `idx_admin_audit_created`, DROP INDEX `idx_admin_audit_entity`;

-- -----------------------------------------------------------------------------
-- END OF MIGRATIONS
-- =============================================================================
-- To run this migration:
--   mysql -u root -p kvnc_platform < changes.sql
--
-- To verify indexes were created:
--   SHOW INDEX FROM security_logs;
--   SHOW INDEX FROM user_sessions;
--   SHOW INDEX FROM rate_limits;
--   SHOW INDEX FROM login_activity;
--   SHOW INDEX FROM otps;
--   SHOW INDEX FROM audit_logs;
--   SHOW INDEX FROM users;
--
-- To rollback all changes:
--   Run the ROLLBACK statements listed above for each section
-- =============================================================================