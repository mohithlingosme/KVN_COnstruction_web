-- ============================================
-- KVN Construction - Database Index Migration
-- ============================================
-- This script adds/verifies critical indexes
-- for production performance optimization.
-- Run AFTER the main schema is imported.
-- ============================================

-- ============================================
-- 1. CRITICAL QUERY PATHS (already in schema)
-- ============================================
-- These indexes are verified to exist in the main schema.
-- Run as a safety check:

-- Users table: email, phone, role+status lookups
ALTER TABLE `users` 
    ADD INDEX IF NOT EXISTS `idx_users_email_status` (`email`, `status`),
    ADD INDEX IF NOT EXISTS `idx_users_phone_status` (`phone`, `status`);

-- Leads: status-based filtering, assignment lookups
ALTER TABLE `leads` 
    ADD INDEX IF NOT EXISTS `idx_leads_status_date` (`status_id`, `created_at`),
    ADD INDEX IF NOT EXISTS `idx_leads_assigned_date` (`assigned_to`, `created_at`);

-- Estimator requests: status filtering, phone lookups  
ALTER TABLE `estimator_requests`
    ADD INDEX IF NOT EXISTS `idx_estimator_requests_status` (`status`, `created_at`),
    ADD INDEX IF NOT EXISTS `idx_estimator_requests_phone_status` (`phone`, `status`);

-- OTPs: validation lookups
ALTER TABLE `otps`
    ADD INDEX IF NOT EXISTS `idx_otps_user_type_used` (`user_id`, `otp_type`, `is_used`),
    ADD INDEX IF NOT EXISTS `idx_otps_phone_type_used` (`phone`, `otp_type`, `is_used`);

-- Client projects: client+status filtering
ALTER TABLE `client_projects`
    ADD INDEX IF NOT EXISTS `idx_client_projects_status_date` (`status_id`, `created_at`);

-- Invoices: payment status filtering
ALTER TABLE `client_invoices`
    ADD INDEX IF NOT EXISTS `idx_client_invoices_status` (`payment_status`, `due_date`);

-- Sessions: active session lookups
ALTER TABLE `user_sessions`
    ADD INDEX IF NOT EXISTS `idx_user_sessions_active_user` (`user_id`, `is_active`);

-- Rate limiting
ALTER TABLE `rate_limits`
    ADD INDEX IF NOT EXISTS `idx_rate_limits_identifier_action_blocked` (`identifier`(120), `action_type`, `blocked_until`);

-- ============================================
-- 2. FULLTEXT SEARCH INDEXES (verify)
-- ============================================
-- These FULLTEXT indexes should exist for search functionality:
-- - ft_leads_search (full_name, email, phone, location, project_type)
-- - ft_projects_search (project_name, client_name, project_type, location, site_address)
-- - ft_blog_posts_search (title, excerpt, content)
-- - ft_services_search (service_name, short_description, description)
-- - ft_construction_packages_search (package_name, short_description, description)
-- - ft_portfolio_search (title, short_description, description, location)

-- ============================================
-- 3. COMPOSITE INDEXES FOR REPORTS
-- ============================================
ALTER TABLE `project_payments`
    ADD INDEX IF NOT EXISTS `idx_project_payments_date_status` (`payment_date`, `payment_status`);

ALTER TABLE `project_milestones`
    ADD INDEX IF NOT EXISTS `idx_project_milestones_status_date` (`status`, `expected_date`);

ALTER TABLE `audit_logs`
    ADD INDEX IF NOT EXISTS `idx_audit_logs_date_action` (`created_at`, `action_type`);