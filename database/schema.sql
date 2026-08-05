-- KVN Construction Platform
-- Canonical MariaDB schema derived from the current PHP repositories/services/controllers.

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE DATABASE IF NOT EXISTS kvnc_platform
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE kvnc_platform;

-- ---------------------------------------------------------------------------
-- Core identity / auth
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(191) NOT NULL,
  name VARCHAR(191) NULL,
  title VARCHAR(191) NULL,
  email VARCHAR(191) NULL,
  phone VARCHAR(32) NULL,
  password VARCHAR(255) NULL,
  password_hash VARCHAR(255) NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'client',
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  profile_image VARCHAR(255) NULL,
  phone_verified TINYINT(1) NOT NULL DEFAULT 0,
  failed_attempts INT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  last_login DATETIME NULL,
  last_ip VARCHAR(45) NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_phone (phone),
  KEY idx_users_role_status (role, status),
  KEY idx_users_deleted_at (deleted_at),
  KEY idx_users_last_login (last_login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clients (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  company_name VARCHAR(191) NULL,
  gst_number VARCHAR(64) NULL,
  contact_name VARCHAR(191) NULL,
  full_name VARCHAR(191) NULL,
  name VARCHAR(191) NULL,
  email VARCHAR(191) NULL,
  phone VARCHAR(32) NULL,
  password VARCHAR(255) NULL,
  address TEXT NULL,
  city VARCHAR(120) NULL,
  state VARCHAR(120) NULL,
  pincode VARCHAR(20) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_clients_user (user_id),
  KEY idx_clients_email (email),
  KEY idx_clients_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_key VARCHAR(80) NOT NULL,
  name VARCHAR(120) NOT NULL,
  guard_name VARCHAR(50) NOT NULL DEFAULT 'web',
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_key (role_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  permission_key VARCHAR(120) NOT NULL,
  name VARCHAR(191) NOT NULL,
  guard_name VARCHAR(50) NOT NULL DEFAULT 'web',
  permission_group VARCHAR(80) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_permissions_key (permission_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_roles (
  user_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  assigned_by BIGINT UNSIGNED NULL,
  PRIMARY KEY (user_id, role_id),
  KEY idx_user_roles_role (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (role_id, permission_id),
  KEY idx_role_permissions_permission (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_sessions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  session_token VARCHAR(255) NOT NULL,
  fingerprint_hash VARCHAR(255) NULL,
  device_hash VARCHAR(255) NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  is_admin_session TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_activity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME NULL,
  revoked_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_sessions_token (session_token),
  KEY idx_user_sessions_user (user_id),
  KEY idx_user_sessions_activity (last_activity),
  KEY idx_user_sessions_active (is_active, revoked_at, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS remember_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  expires_at DATETIME NOT NULL,
  revoked_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_remember_tokens_hash (token_hash),
  KEY idx_remember_tokens_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_otps (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  phone_number VARCHAR(32) NULL,
  otp VARCHAR(255) NULL,
  otp_hash VARCHAR(255) NULL,
  purpose VARCHAR(50) NOT NULL DEFAULT 'login',
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  resend_count INT UNSIGNED NOT NULL DEFAULT 0,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  is_used TINYINT(1) NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL,
  verified_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_otps_user_type (user_id, purpose),
  KEY idx_otps_phone_type (phone_number, purpose),
  KEY idx_otps_email_type (phone_number, purpose),
  KEY idx_otps_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER $$
DROP TRIGGER IF EXISTS tr_user_otps_sync_insert$$
CREATE TRIGGER tr_user_otps_sync_insert
BEFORE INSERT ON user_otps
FOR EACH ROW
BEGIN
  IF NEW.otp_hash IS NULL AND NEW.otp IS NOT NULL THEN
    SET NEW.otp_hash = NEW.otp;
  END IF;
  IF NEW.otp IS NULL AND NEW.otp_hash IS NOT NULL THEN
    SET NEW.otp = NEW.otp_hash;
  END IF;
END$$

DROP TRIGGER IF EXISTS tr_user_otps_sync_update$$
CREATE TRIGGER tr_user_otps_sync_update
BEFORE UPDATE ON user_otps
FOR EACH ROW
BEGIN
  IF NEW.otp_hash IS NULL AND NEW.otp IS NOT NULL THEN
    SET NEW.otp_hash = NEW.otp;
  END IF;
  IF NEW.otp IS NULL AND NEW.otp_hash IS NOT NULL THEN
    SET NEW.otp = NEW.otp_hash;
  END IF;
END$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS rate_limits (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(191) NOT NULL,
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  blocked_until DATETIME NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_rate_limits_key ( `key` ),
  KEY idx_rate_limits_blocked (blocked_until),
  KEY idx_rate_limits_updated (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  email VARCHAR(191) NULL,
  phone VARCHAR(32) NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  success TINYINT(1) NOT NULL DEFAULT 0,
  login_type VARCHAR(50) NULL,
  failure_reason VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_login_attempts_ip (ip_address),
  KEY idx_login_attempts_email (email),
  KEY idx_login_attempts_success (success)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blocked_users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  reason VARCHAR(255) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'blocked',
  blocked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  blocked_until DATETIME NULL,
  unblocked_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_blocked_users_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_sessions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  session_token VARCHAR(255) NOT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  last_activity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_sessions_token (session_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS security_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  event_type VARCHAR(100) NOT NULL,
  event_level VARCHAR(30) NULL,
  severity VARCHAR(30) NOT NULL DEFAULT 'info',
  details TEXT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_security_logs_user_event (user_id, event_type),
  KEY idx_security_logs_level_created (severity, created_at),
  KEY idx_security_logs_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  action VARCHAR(120) NOT NULL,
  entity_type VARCHAR(120) NULL,
  entity_id BIGINT UNSIGNED NULL,
  details TEXT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_audit_logs_user_action (user_id, action),
  KEY idx_audit_logs_entity (entity_type, entity_id),
  KEY idx_audit_logs_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mail_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  recipient VARCHAR(191) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  status VARCHAR(30) NOT NULL,
  error_message TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sms_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  phone_number VARCHAR(32) NOT NULL,
  message TEXT NOT NULL,
  status VARCHAR(30) NOT NULL,
  provider VARCHAR(80) NULL,
  error_message TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_sms_logs_phone (phone_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_verification_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  verified_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_email_verification_tokens_hash (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_history (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_password_history_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suspicious_activity (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  activity_type VARCHAR(120) NOT NULL,
  severity VARCHAR(30) NOT NULL DEFAULT 'warning',
  details TEXT NULL,
  resolved TINYINT(1) NOT NULL DEFAULT 0,
  resolved_at DATETIME NULL,
  resolved_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS session_history (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  session_token VARCHAR(255) NOT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  action VARCHAR(80) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_session_history_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_devices (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  device_hash VARCHAR(255) NOT NULL,
  user_agent TEXT NULL,
  ip_address VARCHAR(45) NULL,
  is_trusted TINYINT(1) NOT NULL DEFAULT 0,
  last_seen_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_user_devices_user_hash (user_id, device_hash),
  KEY idx_user_devices_trusted (is_trusted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS analytics_events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  event_name VARCHAR(191) NOT NULL,
  source VARCHAR(120) NULL,
  data_json JSON NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS schema_migrations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  migration_name VARCHAR(255) NOT NULL,
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_schema_migrations_name (migration_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Settings / CMS
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` VARCHAR(80) NOT NULL,
  `key` VARCHAR(191) NOT NULL,
  `value` LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_site_settings_key (`group`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS general_settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  site_name VARCHAR(191) NULL,
  site_tagline VARCHAR(255) NULL,
  admin_email VARCHAR(191) NULL,
  support_email VARCHAR(191) NULL,
  phone VARCHAR(32) NULL,
  whatsapp VARCHAR(32) NULL,
  address TEXT NULL,
  facebook_link VARCHAR(255) NULL,
  instagram_link VARCHAR(255) NULL,
  youtube_link VARCHAR(255) NULL,
  linkedin_link VARCHAR(255) NULL,
  logo VARCHAR(255) NULL,
  favicon VARCHAR(255) NULL,
  footer_text TEXT NULL,
  maintenance_mode VARCHAR(10) NOT NULL DEFAULT 'off',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sms_settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  sms_provider VARCHAR(120) NULL,
  api_key VARCHAR(255) NULL,
  sender_id VARCHAR(120) NULL,
  auth_token VARCHAR(255) NULL,
  api_url VARCHAR(255) NULL,
  admin_mobile VARCHAR(32) NULL,
  sms_status VARCHAR(20) NOT NULL DEFAULT 'enabled',
  notify_contact_form VARCHAR(10) NOT NULL DEFAULT 'yes',
  notify_new_lead VARCHAR(10) NOT NULL DEFAULT 'yes',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS integration_settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  google_maps_api VARCHAR(255) NULL,
  google_recaptcha_site_key VARCHAR(255) NULL,
  google_recaptcha_secret_key VARCHAR(255) NULL,
  facebook_pixel_id VARCHAR(255) NULL,
  whatsapp_number VARCHAR(32) NULL,
  youtube_channel VARCHAR(255) NULL,
  instagram_url VARCHAR(255) NULL,
  linkedin_url VARCHAR(255) NULL,
  telegram_url VARCHAR(255) NULL,
  chatbot_status VARCHAR(20) NOT NULL DEFAULT 'disabled',
  recaptcha_status VARCHAR(20) NOT NULL DEFAULT 'enabled',
  whatsapp_chat_status VARCHAR(20) NOT NULL DEFAULT 'enabled',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS security_settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_username VARCHAR(120) NULL,
  admin_email VARCHAR(191) NULL,
  admin_password VARCHAR(255) NULL,
  session_timeout INT UNSIGNED NOT NULL DEFAULT 30,
  login_attempt_limit INT UNSIGNED NOT NULL DEFAULT 5,
  two_factor_auth VARCHAR(20) NOT NULL DEFAULT 'disabled',
  maintenance_lock VARCHAR(20) NOT NULL DEFAULT 'disabled',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS about_page (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  hero_title VARCHAR(255) NULL,
  hero_description TEXT NULL,
  mission_title VARCHAR(255) NULL,
  mission_content TEXT NULL,
  vision_title VARCHAR(255) NULL,
  vision_content TEXT NULL,
  process_content TEXT NULL,
  why_choose_content TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS about_advantages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  icon VARCHAR(120) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_about_advantages_status_order (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS about_process_steps (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  step_number INT UNSIGNED NOT NULL DEFAULT 0,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  icon VARCHAR(120) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_about_process_steps_status_order (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS about_specifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  value VARCHAR(255) NULL,
  icon VARCHAR(120) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_about_specifications_status_order (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_page (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  hero_title VARCHAR(255) NULL,
  hero_description TEXT NULL,
  phone VARCHAR(32) NULL,
  email VARCHAR(191) NULL,
  office_address TEXT NULL,
  office_hours VARCHAR(191) NULL,
  google_map_link VARCHAR(255) NULL,
  form_title VARCHAR(255) NULL,
  form_description TEXT NULL,
  why_choose_title VARCHAR(255) NULL,
  why_choose_content TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_page_features (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  icon VARCHAR(120) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_contact_page_features_status_order (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homepage_content (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  hero_title VARCHAR(255) NULL,
  hero_subtitle TEXT NULL,
  hero_button_text VARCHAR(120) NULL,
  hero_button_link VARCHAR(255) NULL,
  section2_title VARCHAR(255) NULL,
  section2_content TEXT NULL,
  services_title VARCHAR(255) NULL,
  services_content TEXT NULL,
  cta_title VARCHAR(255) NULL,
  cta_button_text VARCHAR(120) NULL,
  cta_button_link VARCHAR(255) NULL,
  content_json JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seo_settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  page_name VARCHAR(120) NULL,
  route VARCHAR(191) NULL,
  meta_title VARCHAR(255) NULL,
  meta_description TEXT NULL,
  meta_keywords TEXT NULL,
  canonical_url VARCHAR(255) NULL,
  robots_meta VARCHAR(255) NULL,
  google_analytics VARCHAR(255) NULL,
  google_search_console VARCHAR(255) NULL,
  facebook_meta_title VARCHAR(255) NULL,
  facebook_meta_description TEXT NULL,
  twitter_meta_title VARCHAR(255) NULL,
  twitter_meta_description TEXT NULL,
  sitemap_status VARCHAR(20) NOT NULL DEFAULT 'enabled',
  seo_status VARCHAR(20) NOT NULL DEFAULT 'enabled',
  og_title VARCHAR(255) NULL,
  og_description TEXT NULL,
  og_image VARCHAR(255) NULL,
  robots VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_seo_settings_page_name (page_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS faqs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  category VARCHAR(120) NULL,
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_faqs_status_order (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Content / catalog
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS services (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NULL,
  name VARCHAR(255) NULL,
  service_name VARCHAR(255) NULL,
  slug VARCHAR(191) NULL,
  short_description TEXT NULL,
  description TEXT NULL,
  icon VARCHAR(120) NULL,
  featured_image VARCHAR(255) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_services_slug (slug),
  KEY idx_services_status_featured (status, is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  name VARCHAR(255) NULL,
  slug VARCHAR(191) NOT NULL,
  short_description TEXT NULL,
  description TEXT NULL,
  project_type VARCHAR(120) NULL,
  location VARCHAR(191) NULL,
  client_name VARCHAR(191) NULL,
  budget DECIMAL(14,2) NULL,
  area_sqft DECIMAL(12,2) NULL,
  completion_year SMALLINT UNSIGNED NULL,
  featured_image VARCHAR(255) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'draft',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_portfolio_slug (slug),
  KEY idx_portfolio_status_featured (status, is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_categories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_name VARCHAR(191) NOT NULL,
  slug VARCHAR(191) NOT NULL,
  description TEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_blog_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_tags (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tag_name VARCHAR(191) NOT NULL,
  slug VARCHAR(191) NOT NULL,
  description TEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_blog_tags_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blogs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  name VARCHAR(255) NULL,
  slug VARCHAR(191) NOT NULL,
  category_id BIGINT UNSIGNED NULL,
  category VARCHAR(191) NULL,
  excerpt TEXT NULL,
  content LONGTEXT NULL,
  featured_image VARCHAR(255) NULL,
  meta_title VARCHAR(255) NULL,
  meta_description TEXT NULL,
  tags TEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'draft',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  author_id BIGINT UNSIGNED NULL,
  views_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_blog_posts_slug (slug),
  KEY idx_blogs_status_published (status, published_at),
  KEY idx_blog_posts_category (category_id),
  FULLTEXT KEY ft_blogs_search (title, excerpt, content, tags),
  FULLTEXT KEY ft_blog_posts_search (title, excerpt, content, tags)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_comments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  blog_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  name VARCHAR(191) NULL,
  email VARCHAR(191) NULL,
  comment TEXT NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  is_approved TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_blog_comments_blog (blog_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS testimonials (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NULL,
  name VARCHAR(255) NULL,
  client_name VARCHAR(191) NOT NULL,
  client_image VARCHAR(255) NULL,
  designation VARCHAR(191) NULL,
  company_name VARCHAR(191) NULL,
  review TEXT NOT NULL,
  rating TINYINT UNSIGNED NULL,
  video_url VARCHAR(255) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  approved_by BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_testimonials_status_featured (status, is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS testimonial_videos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_name VARCHAR(191) NOT NULL,
  youtube_url VARCHAR(255) NULL,
  description TEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS video_categories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(191) NOT NULL,
  slug VARCHAR(191) NOT NULL,
  description TEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_video_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS videos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  name VARCHAR(255) NULL,
  slug VARCHAR(191) NULL,
  youtube_url VARCHAR(255) NULL,
  video_url VARCHAR(255) NULL,
  description TEXT NULL,
  category_id BIGINT UNSIGNED NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS construction_packages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  package_name VARCHAR(191) NOT NULL,
  slug VARCHAR(191) NOT NULL,
  base_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  material_grade VARCHAR(120) NULL,
  estimated_timeline VARCHAR(120) NULL,
  description TEXT NULL,
  features LONGTEXT NULL,
  package_image VARCHAR(255) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_construction_packages_slug (slug),
  KEY idx_construction_packages_status_price (status, base_price),
  KEY idx_construction_packages_featured (is_featured, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS package_features (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  package_id BIGINT UNSIGNED NOT NULL,
  feature_name VARCHAR(191) NOT NULL,
  description TEXT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_package_features_package (package_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS package_specifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  package_id BIGINT UNSIGNED NOT NULL,
  spec_name VARCHAR(191) NOT NULL,
  spec_value VARCHAR(255) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_package_specs_package (package_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Leads / estimator
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS lead_statuses (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  status_key VARCHAR(80) NOT NULL,
  name VARCHAR(120) NOT NULL,
  color VARCHAR(30) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_lead_statuses_key (status_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leads (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(191) NULL,
  name VARCHAR(191) NULL,
  title VARCHAR(255) NULL,
  phone VARCHAR(32) NOT NULL,
  email VARCHAR(191) NULL,
  project_location VARCHAR(191) NULL,
  location VARCHAR(191) NULL,
  project_type VARCHAR(120) NULL,
  lead_type VARCHAR(120) NULL,
  budget VARCHAR(120) NULL,
  budget_amount DECIMAL(14,2) NULL,
  message TEXT NULL,
  lead_source VARCHAR(120) NULL,
  status_id BIGINT UNSIGNED NULL,
  status VARCHAR(60) NOT NULL DEFAULT 'new',
  assigned_to BIGINT UNSIGNED NULL,
  created_by BIGINT UNSIGNED NULL,
  deleted_by BIGINT UNSIGNED NULL,
  deleted_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_leads_status_created (status, created_at),
  KEY idx_leads_assigned (assigned_to),
  KEY idx_leads_phone (phone),
  KEY idx_leads_deleted_at (deleted_at),
  FULLTEXT KEY ft_leads_search (full_name, name, email, phone, project_location, location, message)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lead_activities (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  activity_type VARCHAR(120) NOT NULL,
  description TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_lead_activities_lead (lead_id),
  KEY idx_lead_activities_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lead_followups (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  followup_date DATE NULL,
  note TEXT NULL,
  status VARCHAR(60) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_lead_followups_lead (lead_id),
  KEY idx_lead_followups_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estimator_requests (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(191) NOT NULL,
  name VARCHAR(191) NULL,
  phone VARCHAR(32) NOT NULL,
  email VARCHAR(191) NULL,
  location VARCHAR(191) NULL,
  plot_area DECIMAL(12,2) NOT NULL DEFAULT 0,
  floors INT UNSIGNED NOT NULL DEFAULT 1,
  package_id BIGINT UNSIGNED NULL,
  estimated_cost DECIMAL(14,2) NOT NULL DEFAULT 0,
  ip_address VARCHAR(45) NULL,
  lead_id BIGINT UNSIGNED NULL,
  reviewed_by BIGINT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  status VARCHAR(60) NOT NULL DEFAULT 'new',
  notes TEXT NULL,
  source VARCHAR(120) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_estimator_requests_status_created (status, created_at),
  KEY idx_estimator_requests_package (package_id),
  KEY idx_estimator_requests_lead (lead_id),
  KEY idx_estimator_requests_user (reviewed_by),
  KEY idx_estimator_requests_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estimator_packages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  package_name VARCHAR(191) NOT NULL,
  slug VARCHAR(191) NOT NULL,
  base_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  material_grade VARCHAR(120) NULL,
  estimated_timeline VARCHAR(120) NULL,
  description TEXT NULL,
  features LONGTEXT NULL,
  package_image VARCHAR(255) NULL,
  short_description TEXT NULL,
  specifications LONGTEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_estimator_packages_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estimator_pricing (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  package_id BIGINT UNSIGNED NULL,
  package_name VARCHAR(191) NOT NULL,
  price_per_sqft DECIMAL(14,2) NOT NULL DEFAULT 0,
  description TEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_estimator_pricing_package (package_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estimator_materials (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  category VARCHAR(120) NOT NULL,
  name VARCHAR(191) NOT NULL,
  unit VARCHAR(50) NULL,
  rate DECIMAL(14,2) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_material_pricing_category_grade (category, rate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estimator_calculation_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  request_id BIGINT UNSIGNED NULL,
  package_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  input_data JSON NULL,
  result_data JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_estimator_log_request (request_id),
  KEY idx_estimator_log_package (package_id),
  KEY idx_estimator_log_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Projects / financial / support
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS project_statuses (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  status_key VARCHAR(80) NOT NULL,
  name VARCHAR(120) NOT NULL,
  color VARCHAR(30) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_project_statuses_key (status_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_code VARCHAR(80) NULL,
  title VARCHAR(255) NULL,
  name VARCHAR(255) NULL,
  project_name VARCHAR(255) NOT NULL,
  project_type VARCHAR(120) NOT NULL,
  slug VARCHAR(191) NULL,
  client_id BIGINT UNSIGNED NULL,
  client_user_id BIGINT UNSIGNED NULL,
  lead_id BIGINT UNSIGNED NULL,
  quotation_id BIGINT UNSIGNED NULL,
  status_id BIGINT UNSIGNED NULL,
  location VARCHAR(191) NULL,
  description TEXT NULL,
  budget DECIMAL(14,2) NOT NULL DEFAULT 0,
  progress INT UNSIGNED NOT NULL DEFAULT 0,
  start_date DATE NULL,
  end_date DATE NULL,
  project_image VARCHAR(255) NULL,
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  status VARCHAR(60) NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_projects_code (project_code),
  KEY idx_projects_client_status (client_id, status),
  KEY idx_projects_dates (start_date, end_date),
  KEY idx_projects_deleted_at (deleted_at),
  FULLTEXT KEY ft_projects_search (project_name, project_type, location, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_milestones (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id BIGINT UNSIGNED NOT NULL,
  client_project_id BIGINT UNSIGNED NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  due_date DATE NULL,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  status VARCHAR(60) NOT NULL DEFAULT 'pending',
  completed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_project_milestones_project (project_id),
  KEY idx_project_milestones_client_project (client_project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_timelines (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id BIGINT UNSIGNED NULL,
  client_id BIGINT UNSIGNED NULL,
  title VARCHAR(255) NULL,
  schedule_date DATE NULL,
  due_date DATE NULL,
  status VARCHAR(60) NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_project_timelines_client (client_id),
  KEY idx_project_timelines_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_updates (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id BIGINT UNSIGNED NOT NULL,
  client_project_id BIGINT UNSIGNED NULL,
  title VARCHAR(255) NULL,
  description TEXT NULL,
  progress INT UNSIGNED NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED NULL,
  image_path VARCHAR(255) NULL,
  status VARCHAR(60) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_project_updates_project (project_id),
  KEY idx_project_updates_client_project (client_project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_gallery (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id BIGINT UNSIGNED NOT NULL,
  client_project_id BIGINT UNSIGNED NULL,
  image_path VARCHAR(255) NOT NULL,
  caption VARCHAR(255) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_project_gallery_project (project_id),
  KEY idx_project_gallery_client_project (client_project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_media (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id BIGINT UNSIGNED NOT NULL,
  client_project_id BIGINT UNSIGNED NULL,
  uploaded_by BIGINT UNSIGNED NULL,
  filename VARCHAR(255) NULL,
  original_name VARCHAR(255) NULL,
  file_path VARCHAR(255) NOT NULL,
  file_type VARCHAR(50) NULL,
  file_size BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_project_media_project (project_id),
  KEY idx_project_media_client_project (client_project_id),
  KEY idx_project_media_uploaded_by (uploaded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_files (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id BIGINT UNSIGNED NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NULL,
  uploaded_by BIGINT UNSIGNED NULL,
  file_size BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_project_files_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_tasks (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id BIGINT UNSIGNED NOT NULL,
  assigned_to BIGINT UNSIGNED NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  due_date DATE NULL,
  priority VARCHAR(30) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_project_tasks_project (project_id),
  KEY idx_project_tasks_assigned_to (assigned_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NULL,
  project_id BIGINT UNSIGNED NULL,
  invoice_id BIGINT UNSIGNED NULL,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  balance_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  payment_method VARCHAR(80) NULL,
  payment_status VARCHAR(60) NOT NULL DEFAULT 'Pending',
  transaction_id VARCHAR(191) NULL,
  notes TEXT NULL,
  payment_date DATE NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_project_payments_project (project_id),
  KEY idx_client_payments_client_status (client_id, payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_invoices (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  project_id BIGINT UNSIGNED NULL,
  invoice_number VARCHAR(120) NOT NULL,
  invoice_date DATE NULL,
  due_date DATE NULL,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  amount_paid DECIMAL(14,2) NOT NULL DEFAULT 0,
  balance_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  payment_status VARCHAR(60) NOT NULL DEFAULT 'Pending',
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_client_invoices_number (invoice_number),
  KEY idx_client_invoices_client_status (client_id, payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_transactions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NULL,
  project_id BIGINT UNSIGNED NULL,
  payment_id BIGINT UNSIGNED NULL,
  invoice_id BIGINT UNSIGNED NULL,
  transaction_id VARCHAR(191) NULL,
  gateway VARCHAR(80) NULL,
  payment_method VARCHAR(80) NULL,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  currency VARCHAR(10) NOT NULL DEFAULT 'INR',
  status VARCHAR(60) NOT NULL DEFAULT 'Pending',
  gateway_response TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY uq_payment_transactions_id (transaction_id),
  KEY idx_payment_transactions_client (client_id),
  KEY idx_payment_transactions_invoice (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_receipts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  project_id BIGINT UNSIGNED NULL,
  payment_id BIGINT UNSIGNED NULL,
  receipt_number VARCHAR(120) NOT NULL,
  file_name VARCHAR(255) NULL,
  file_path VARCHAR(255) NULL,
  issued_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_payment_receipts_number (receipt_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  quotation_number VARCHAR(120) NOT NULL,
  quotation_no VARCHAR(120) NULL,
  title VARCHAR(255) NULL,
  name VARCHAR(255) NULL,
  client_id BIGINT UNSIGNED NULL,
  client_user_id BIGINT UNSIGNED NULL,
  client_name VARCHAR(191) NULL,
  client_phone VARCHAR(32) NULL,
  project_id BIGINT UNSIGNED NULL,
  lead_id BIGINT UNSIGNED NULL,
  project_type VARCHAR(120) NULL,
  project_location VARCHAR(191) NULL,
  estimated_cost DECIMAL(14,2) NOT NULL DEFAULT 0,
  quotation_date DATE NULL,
  valid_till DATE NULL,
  valid_until DATE NULL,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  gst_percentage DECIMAL(6,2) NOT NULL DEFAULT 0,
  gst_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  gst DECIMAL(14,2) NOT NULL DEFAULT 0,
  discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  discount DECIMAL(14,2) NOT NULL DEFAULT 0,
  grand_total DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  notes TEXT NULL,
  terms_conditions TEXT NULL,
  terms TEXT NULL,
  status VARCHAR(60) NOT NULL DEFAULT 'pending',
  approved_by BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  approval_remarks TEXT NULL,
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_quotations_number (quotation_number),
  UNIQUE KEY uq_quotations_uuid (quotation_no),
  KEY idx_quotations_client (client_id),
  KEY idx_quotations_project (project_id),
  KEY idx_quotations_status_created (status, created_at),
  KEY idx_quotations_valid_until (valid_till)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotation_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  quotation_id BIGINT UNSIGNED NOT NULL,
  item_name VARCHAR(255) NOT NULL,
  description TEXT NULL,
  quantity DECIMAL(14,2) NOT NULL DEFAULT 0,
  price DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_quotation_items_quotation (quotation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotation_versions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  quotation_id BIGINT UNSIGNED NOT NULL,
  version_number INT UNSIGNED NOT NULL DEFAULT 1,
  snapshot_json JSON NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_quotation_versions_quotation (quotation_id),
  KEY idx_quotation_versions_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotation_downloads (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  quotation_id BIGINT UNSIGNED NULL,
  file_name VARCHAR(255) NULL,
  file_path VARCHAR(255) NULL,
  downloaded_by BIGINT UNSIGNED NULL,
  downloaded_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_quotation_downloads_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_tickets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ticket_number VARCHAR(120) NULL,
  client_id BIGINT UNSIGNED NOT NULL,
  subject VARCHAR(255) NOT NULL,
  priority VARCHAR(30) NOT NULL DEFAULT 'Medium',
  status VARCHAR(30) NOT NULL DEFAULT 'Open',
  assigned_to BIGINT UNSIGNED NULL,
  last_message_at DATETIME NULL,
  closed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_support_tickets_number (ticket_number),
  KEY idx_support_tickets_client_status (client_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_messages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ticket_id BIGINT UNSIGNED NOT NULL,
  sender_id BIGINT UNSIGNED NULL,
  message TEXT NOT NULL,
  is_internal TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_support_messages_ticket (ticket_id),
  KEY idx_support_messages_client (sender_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_messages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  subject VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'open',
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  replied_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_client_messages_client_read (client_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  read_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_feedback (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NULL,
  rating TINYINT UNSIGNED NULL,
  subject VARCHAR(255) NULL,
  message TEXT NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_client_feedback_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_documents (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  project_id BIGINT UNSIGNED NULL,
  document_type VARCHAR(120) NULL,
  title VARCHAR(255) NULL,
  file_name VARCHAR(255) NULL,
  file_path VARCHAR(255) NULL,
  file_type VARCHAR(50) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Pending',
  uploaded_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_client_documents_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_permits (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  project_id BIGINT UNSIGNED NULL,
  permit_type VARCHAR(120) NULL,
  permit_number VARCHAR(120) NULL,
  expiry_date DATE NULL,
  file_name VARCHAR(255) NULL,
  file_path VARCHAR(255) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_client_permits_number (permit_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_agreements (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  project_id BIGINT UNSIGNED NULL,
  agreement_type VARCHAR(120) NULL,
  agreement_number VARCHAR(120) NULL,
  file_name VARCHAR(255) NULL,
  file_path VARCHAR(255) NULL,
  signed_at DATETIME NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_client_agreements_number (agreement_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_downloads (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  quotation_id BIGINT UNSIGNED NULL,
  document_id BIGINT UNSIGNED NULL,
  file_name VARCHAR(255) NULL,
  file_path VARCHAR(255) NULL,
  downloaded_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_client_downloads_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_quotations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  project_id BIGINT UNSIGNED NULL,
  quotation_id BIGINT UNSIGNED NULL,
  quotation_number VARCHAR(120) NOT NULL,
  total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  status VARCHAR(60) NOT NULL DEFAULT 'pending',
  approved_at DATETIME NULL,
  approved_by BIGINT UNSIGNED NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_client_quotations_number (quotation_number),
  KEY idx_client_quotations_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_uploaded_images (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  filename VARCHAR(255) NOT NULL,
  title VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_client_uploaded_images_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_uploaded_videos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  video_url VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_client_uploaded_videos_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_testimonials (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NULL,
  review TEXT NOT NULL,
  rating TINYINT UNSIGNED NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_client_testimonials_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_reports (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id BIGINT UNSIGNED NULL,
  report_period VARCHAR(120) NULL,
  total_projects INT UNSIGNED NOT NULL DEFAULT 0,
  summary TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_project_reports_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS revenue_reports (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  period_start DATE NULL,
  period_end DATE NULL,
  total_revenue DECIMAL(14,2) NOT NULL DEFAULT 0,
  expenses DECIMAL(14,2) NOT NULL DEFAULT 0,
  profit DECIMAL(14,2) NOT NULL DEFAULT 0,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  uploaded_by BIGINT UNSIGNED NULL,
  file_name VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) NULL,
  file_path VARCHAR(255) NOT NULL,
  file_type VARCHAR(50) NULL,
  mime_type VARCHAR(120) NULL,
  file_size BIGINT UNSIGNED NULL,
  alt_text VARCHAR(255) NULL,
  caption TEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_media_uploader (uploaded_by),
  KEY idx_media_type (file_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_derivatives (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  media_id BIGINT UNSIGNED NOT NULL,
  derivative_type VARCHAR(80) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  width INT UNSIGNED NULL,
  height INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_media_derivatives_media (media_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Compatibility views
-- ---------------------------------------------------------------------------

CREATE OR REPLACE VIEW blog_posts AS SELECT * FROM blogs;
CREATE OR REPLACE VIEW portfolio_projects AS SELECT * FROM portfolio;
CREATE OR REPLACE VIEW client_projects AS SELECT * FROM projects;
CREATE OR REPLACE VIEW client_payments AS SELECT * FROM payments;
CREATE OR REPLACE VIEW project_schedules AS SELECT * FROM project_timelines;
CREATE OR REPLACE VIEW client_schedules AS SELECT * FROM project_timelines;
CREATE OR REPLACE VIEW media_library AS SELECT * FROM media;
CREATE OR REPLACE VIEW client_files AS SELECT * FROM media;
CREATE OR REPLACE VIEW otps AS SELECT * FROM user_otps;
CREATE OR REPLACE VIEW estimators AS SELECT * FROM estimator_requests;
CREATE OR REPLACE VIEW estimator_leads AS SELECT * FROM estimator_requests;
CREATE OR REPLACE VIEW active_sessions_view AS
  SELECT us.id, us.user_id, us.session_token, us.ip_address, us.user_agent, us.last_activity, us.created_at AS session_started, us.expires_at, u.full_name, u.email
  FROM user_sessions us
  LEFT JOIN users u ON u.id = us.user_id
  WHERE us.is_active = 1 AND us.revoked_at IS NULL;
CREATE OR REPLACE VIEW failed_login_attempts_view AS
  SELECT ip_address, email, COUNT(*) AS attempt_count, MAX(created_at) AS last_attempt
  FROM login_attempts
  WHERE success = 0 AND created_at > CURRENT_TIMESTAMP - INTERVAL 24 HOUR
  GROUP BY ip_address, email
  HAVING COUNT(*) >= 3;
CREATE OR REPLACE VIEW security_overview AS
  SELECT
    COUNT(CASE WHEN severity = 'critical' THEN 1 END) AS critical_events,
    COUNT(CASE WHEN severity = 'warning' THEN 1 END) AS warning_events,
    COUNT(CASE WHEN severity = 'info' THEN 1 END) AS info_events,
    COUNT(CASE WHEN created_at > CURRENT_TIMESTAMP - INTERVAL 24 HOUR THEN 1 END) AS events_today
  FROM security_logs
  WHERE created_at > CURRENT_TIMESTAMP - INTERVAL 7 DAY;
CREATE OR REPLACE VIEW suspicious_activity_view AS
  SELECT sa.*, u.full_name AS user_name, u.email AS user_email
  FROM suspicious_activity sa
  LEFT JOIN users u ON u.id = sa.user_id
  WHERE sa.resolved = 0 AND sa.created_at > CURRENT_TIMESTAMP - INTERVAL 7 DAY;
CREATE OR REPLACE VIEW blogs_view AS
  SELECT b.*, bc.category_name
  FROM blogs b
  LEFT JOIN blog_categories bc ON bc.id = b.category_id;
CREATE OR REPLACE VIEW portfolio_view AS SELECT * FROM portfolio;
CREATE OR REPLACE VIEW projects_view AS
  SELECT p.*, u.full_name AS client_name, u.email AS client_email, u.phone AS client_phone
  FROM projects p
  LEFT JOIN users u ON u.id = p.client_id;
CREATE OR REPLACE VIEW estimators_view AS SELECT * FROM estimator_requests;

-- ---------------------------------------------------------------------------
-- Foreign keys
-- ---------------------------------------------------------------------------

ALTER TABLE clients
  ADD CONSTRAINT fk_clients_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE user_roles
  ADD CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE role_permissions
  ADD CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE user_sessions
  ADD CONSTRAINT fk_user_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE remember_tokens
  ADD CONSTRAINT fk_remember_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE user_otps
  ADD CONSTRAINT fk_user_otps_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE login_attempts
  ADD CONSTRAINT fk_login_attempts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE blocked_users
  ADD CONSTRAINT fk_blocked_users_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE admin_sessions
  ADD CONSTRAINT fk_admin_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE security_logs
  ADD CONSTRAINT fk_security_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE audit_logs
  ADD CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE email_verification_tokens
  ADD CONSTRAINT fk_email_verification_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE password_history
  ADD CONSTRAINT fk_password_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE suspicious_activity
  ADD CONSTRAINT fk_suspicious_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_suspicious_activity_resolved_by FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE session_history
  ADD CONSTRAINT fk_session_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE user_devices
  ADD CONSTRAINT fk_user_devices_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE blogs
  ADD CONSTRAINT fk_blogs_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_blogs_category FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE blog_comments
  ADD CONSTRAINT fk_blog_comments_blog FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE package_features
  ADD CONSTRAINT fk_package_features_package FOREIGN KEY (package_id) REFERENCES construction_packages(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE package_specifications
  ADD CONSTRAINT fk_package_specs_package FOREIGN KEY (package_id) REFERENCES construction_packages(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE leads
  ADD CONSTRAINT fk_leads_status FOREIGN KEY (status_id) REFERENCES lead_statuses(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_leads_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_leads_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_leads_deleted_by FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE lead_activities
  ADD CONSTRAINT fk_lead_activities_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_lead_activities_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE lead_followups
  ADD CONSTRAINT fk_lead_followups_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_lead_followups_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE estimator_requests
  ADD CONSTRAINT fk_estimator_requests_package FOREIGN KEY (package_id) REFERENCES estimator_packages(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_estimator_requests_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_estimator_requests_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE estimator_pricing
  ADD CONSTRAINT fk_estimator_pricing_package FOREIGN KEY (package_id) REFERENCES estimator_packages(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE estimator_calculation_log
  ADD CONSTRAINT fk_estimator_log_request FOREIGN KEY (request_id) REFERENCES estimator_requests(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_estimator_log_package FOREIGN KEY (package_id) REFERENCES estimator_packages(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_estimator_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE projects
  ADD CONSTRAINT fk_projects_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_projects_client_user FOREIGN KEY (client_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_projects_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_projects_status FOREIGN KEY (status_id) REFERENCES project_statuses(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_projects_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE project_milestones
  ADD CONSTRAINT fk_project_milestones_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_project_milestones_client_project FOREIGN KEY (client_project_id) REFERENCES projects(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE project_timelines
  ADD CONSTRAINT fk_project_timelines_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_project_timelines_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE project_updates
  ADD CONSTRAINT fk_project_updates_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_project_updates_client_project FOREIGN KEY (client_project_id) REFERENCES projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_project_updates_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE project_gallery
  ADD CONSTRAINT fk_project_gallery_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_project_gallery_client_project FOREIGN KEY (client_project_id) REFERENCES projects(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE project_media
  ADD CONSTRAINT fk_project_media_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_project_media_client_project FOREIGN KEY (client_project_id) REFERENCES projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_project_media_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE project_files
  ADD CONSTRAINT fk_project_files_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_project_files_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE project_tasks
  ADD CONSTRAINT fk_project_tasks_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_project_tasks_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE payments
  ADD CONSTRAINT fk_payments_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_payments_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE client_invoices
  ADD CONSTRAINT fk_client_invoices_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_client_invoices_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE payment_transactions
  ADD CONSTRAINT fk_payment_transactions_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_payment_transactions_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_payment_transactions_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_payment_transactions_invoice FOREIGN KEY (invoice_id) REFERENCES client_invoices(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE payment_receipts
  ADD CONSTRAINT fk_payment_receipts_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_payment_receipts_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_payment_receipts_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE quotations
  ADD CONSTRAINT fk_quotations_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_quotations_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_quotations_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_quotations_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_quotations_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE quotation_items
  ADD CONSTRAINT fk_quotation_items_quotation FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE quotation_versions
  ADD CONSTRAINT fk_quotation_versions_quotation FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_quotation_versions_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE quotation_downloads
  ADD CONSTRAINT fk_quotation_downloads_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_quotation_downloads_quotation FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_quotation_downloads_downloaded_by FOREIGN KEY (downloaded_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE support_tickets
  ADD CONSTRAINT fk_support_tickets_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_support_tickets_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE support_messages
  ADD CONSTRAINT fk_support_messages_ticket FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_support_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE client_messages
  ADD CONSTRAINT fk_client_messages_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE client_notifications
  ADD CONSTRAINT fk_client_notifications_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE client_feedback
  ADD CONSTRAINT fk_client_feedback_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE client_documents
  ADD CONSTRAINT fk_client_documents_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_client_documents_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE client_permits
  ADD CONSTRAINT fk_client_permits_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_client_permits_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE client_agreements
  ADD CONSTRAINT fk_client_agreements_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_client_agreements_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE client_downloads
  ADD CONSTRAINT fk_client_downloads_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_client_downloads_quotation FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE client_quotations
  ADD CONSTRAINT fk_client_quotations_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_client_quotations_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_client_quotations_quotation FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE client_uploaded_images
  ADD CONSTRAINT fk_client_uploaded_images_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE client_uploaded_videos
  ADD CONSTRAINT fk_client_uploaded_videos_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE client_testimonials
  ADD CONSTRAINT fk_client_testimonials_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE project_reports
  ADD CONSTRAINT fk_project_reports_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE media
  ADD CONSTRAINT fk_media_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE media_derivatives
  ADD CONSTRAINT fk_media_derivatives_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE ON UPDATE CASCADE;

-- End of schema
