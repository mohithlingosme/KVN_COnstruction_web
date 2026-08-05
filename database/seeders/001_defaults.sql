-- Minimal development defaults only.

USE kvnc_platform;

INSERT INTO roles (id, role_key, name, guard_name, status, created_at, updated_at)
VALUES
  (1, 'super_admin', 'Super Admin', 'web', 'active', NOW(), NOW()),
  (2, 'admin', 'Admin', 'web', 'active', NOW(), NOW()),
  (3, 'client', 'Client', 'web', 'active', NOW(), NOW()),
  (4, 'guest', 'Guest', 'web', 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  role_key = VALUES(role_key),
  name = VALUES(name),
  guard_name = VALUES(guard_name),
  status = VALUES(status),
  updated_at = NOW();

INSERT INTO permissions (id, permission_key, name, guard_name, permission_group, status, created_at, updated_at)
VALUES
  (1, 'manage_users', 'Manage Users', 'web', 'admin', 'active', NOW(), NOW()),
  (2, 'manage_projects', 'Manage Projects', 'web', 'operations', 'active', NOW(), NOW()),
  (3, 'manage_leads', 'Manage Leads', 'web', 'sales', 'active', NOW(), NOW()),
  (4, 'manage_quotations', 'Manage Quotations', 'web', 'sales', 'active', NOW(), NOW()),
  (5, 'manage_content', 'Manage Content', 'web', 'cms', 'active', NOW(), NOW()),
  (6, 'manage_settings', 'Manage Settings', 'web', 'system', 'active', NOW(), NOW()),
  (7, 'manage_security', 'Manage Security', 'web', 'security', 'active', NOW(), NOW()),
  (8, 'manage_support', 'Manage Support', 'web', 'support', 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  permission_key = VALUES(permission_key),
  name = VALUES(name),
  guard_name = VALUES(guard_name),
  permission_group = VALUES(permission_group),
  status = VALUES(status),
  updated_at = NOW();

INSERT INTO role_permissions (role_id, permission_id, assigned_at)
VALUES
  (1, 1, NOW()), (1, 2, NOW()), (1, 3, NOW()), (1, 4, NOW()),
  (1, 5, NOW()), (1, 6, NOW()), (1, 7, NOW()), (1, 8, NOW()),
  (2, 2, NOW()), (2, 3, NOW()), (2, 4, NOW()), (2, 5, NOW()), (2, 8, NOW())
ON DUPLICATE KEY UPDATE assigned_at = NOW();

INSERT INTO users (
  id, full_name, name, title, email, phone, password, password_hash, role, status,
  profile_image, phone_verified, failed_attempts, locked_until, last_login, last_ip,
  created_at, updated_at, deleted_at
)
VALUES (
  1, 'System Administrator', 'System Administrator', 'Administrator',
  'admin@kvnconstruction.local', '9999999999',
  '$2y$10$8FDtLYKRnFf/IW8EcadeT.vEvJQnMdApIbn8FdmoDb.3ssbRoOc8q',
  '$2y$10$8FDtLYKRnFf/IW8EcadeT.vEvJQnMdApIbn8FdmoDb.3ssbRoOc8q',
  'super_admin', 'active', NULL, 1, 0, NULL, NULL, NULL, NOW(), NOW(), NULL
)
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  name = VALUES(name),
  title = VALUES(title),
  email = VALUES(email),
  phone = VALUES(phone),
  password = VALUES(password),
  password_hash = VALUES(password_hash),
  role = VALUES(role),
  status = VALUES(status),
  phone_verified = VALUES(phone_verified),
  updated_at = NOW();

INSERT INTO user_roles (user_id, role_id, assigned_at)
VALUES (1, 1, NOW())
ON DUPLICATE KEY UPDATE assigned_at = NOW();

INSERT INTO lead_statuses (id, status_key, name, color, sort_order, is_active, created_at, updated_at)
VALUES
  (1, 'new', 'New', '#0ea5e9', 1, 1, NOW(), NOW()),
  (2, 'contacted', 'Contacted', '#f59e0b', 2, 1, NOW(), NOW()),
  (3, 'qualified', 'Qualified', '#10b981', 3, 1, NOW(), NOW()),
  (4, 'won', 'Won', '#22c55e', 4, 1, NOW(), NOW()),
  (5, 'lost', 'Lost', '#ef4444', 5, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  status_key = VALUES(status_key),
  name = VALUES(name),
  color = VALUES(color),
  sort_order = VALUES(sort_order),
  is_active = VALUES(is_active),
  updated_at = NOW();

INSERT INTO project_statuses (id, status_key, name, color, sort_order, is_active, created_at, updated_at)
VALUES
  (1, 'pending', 'Pending', '#f59e0b', 1, 1, NOW(), NOW()),
  (2, 'ongoing', 'Ongoing', '#3b82f6', 2, 1, NOW(), NOW()),
  (3, 'completed', 'Completed', '#22c55e', 3, 1, NOW(), NOW()),
  (4, 'cancelled', 'Cancelled', '#ef4444', 4, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  status_key = VALUES(status_key),
  name = VALUES(name),
  color = VALUES(color),
  sort_order = VALUES(sort_order),
  is_active = VALUES(is_active),
  updated_at = NOW();

INSERT INTO general_settings (id, site_name, site_tagline, admin_email, support_email, phone, whatsapp, address, maintenance_mode, created_at, updated_at)
VALUES
  (1, 'KVN Construction', 'Building homes, delivering trust', 'admin@kvnconstruction.local', 'support@kvnconstruction.local', '+91-0000000000', '+91-0000000000', 'Bengaluru, Karnataka, India', 'off', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  site_name = VALUES(site_name),
  site_tagline = VALUES(site_tagline),
  admin_email = VALUES(admin_email),
  support_email = VALUES(support_email),
  phone = VALUES(phone),
  whatsapp = VALUES(whatsapp),
  address = VALUES(address),
  maintenance_mode = VALUES(maintenance_mode),
  updated_at = NOW();

INSERT INTO sms_settings (id, sms_provider, admin_mobile, sms_status, notify_contact_form, notify_new_lead, created_at, updated_at)
VALUES
  (1, 'disabled', '+91-0000000000', 'enabled', 'yes', 'yes', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  sms_provider = VALUES(sms_provider),
  admin_mobile = VALUES(admin_mobile),
  sms_status = VALUES(sms_status),
  notify_contact_form = VALUES(notify_contact_form),
  notify_new_lead = VALUES(notify_new_lead),
  updated_at = NOW();

INSERT INTO integration_settings (id, whatsapp_chat_status, recaptcha_status, chatbot_status, created_at, updated_at)
VALUES
  (1, 'enabled', 'enabled', 'disabled', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  whatsapp_chat_status = VALUES(whatsapp_chat_status),
  recaptcha_status = VALUES(recaptcha_status),
  chatbot_status = VALUES(chatbot_status),
  updated_at = NOW();

INSERT INTO security_settings (id, admin_username, admin_email, admin_password, session_timeout, login_attempt_limit, two_factor_auth, maintenance_lock, created_at, updated_at)
VALUES
  (1, 'admin', 'admin@kvnconstruction.local', '$2y$10$8FDtLYKRnFf/IW8EcadeT.vEvJQnMdApIbn8FdmoDb.3ssbRoOc8q', 30, 5, 'disabled', 'disabled', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  admin_username = VALUES(admin_username),
  admin_email = VALUES(admin_email),
  admin_password = VALUES(admin_password),
  session_timeout = VALUES(session_timeout),
  login_attempt_limit = VALUES(login_attempt_limit),
  two_factor_auth = VALUES(two_factor_auth),
  maintenance_lock = VALUES(maintenance_lock),
  updated_at = NOW();

INSERT INTO seo_settings (id, page_name, meta_title, meta_description, meta_keywords, canonical_url, sitemap_status, seo_status, created_at, updated_at)
VALUES
  (1, 'homepage', 'KVN Construction', 'Construction and renovation services', 'construction, renovation, villas, interiors', 'https://kvnconstruction.local/', 'enabled', 'enabled', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  page_name = VALUES(page_name),
  meta_title = VALUES(meta_title),
  meta_description = VALUES(meta_description),
  meta_keywords = VALUES(meta_keywords),
  canonical_url = VALUES(canonical_url),
  sitemap_status = VALUES(sitemap_status),
  seo_status = VALUES(seo_status),
  updated_at = NOW();
