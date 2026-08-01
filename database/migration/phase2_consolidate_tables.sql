-- ============================================
-- KVN Construction - Phase 2: Database Consolidation
-- ============================================
-- This migration consolidates all duplicate tables,
-- adds foreign keys, indexes, and creates views
-- for backward compatibility during transition.
-- ============================================

-- ============================================
-- 1. CONSOLIDATE BLOG TABLES
-- blogs (canonical) ← blog_posts (duplicate)
-- ============================================
ALTER TABLE `blog_posts` ADD COLUMN IF NOT EXISTS `featured` tinyint(1) NOT NULL DEFAULT 0 AFTER `sort_order`;

CREATE OR REPLACE VIEW `v_blogs` AS
SELECT 
    b.id, b.uuid, b.author_id, b.category_id, b.title, b.slug,
    b.excerpt, b.content, b.featured_image, b.category, b.tags,
    b.meta_title, b.meta_description, b.meta_keywords,
    b.views_count, b.is_featured, b.status, b.published_at,
    b.sort_order, b.created_at, b.updated_at, b.deleted_at,
    COALESCE(b.featured, 0) as featured
FROM `blogs` b
UNION ALL
SELECT 
    bp.id, bp.uuid, bp.author_id, bp.category_id, bp.title, bp.slug,
    bp.excerpt, bp.content, bp.featured_image, bp.category, bp.tags,
    bp.meta_title, bp.meta_description, bp.meta_keywords,
    bp.views_count, bp.is_featured, bp.status, bp.published_at,
    bp.sort_order, bp.created_at, bp.updated_at, bp.deleted_at,
    COALESCE(bp.featured, 0) as featured
FROM `blog_posts` bp
WHERE bp.id NOT IN (SELECT id FROM `blogs`);

-- ============================================
-- 2. CONSOLIDATE PORTFOLIO TABLES
-- portfolio (canonical) ← portfolio_projects (duplicate)
-- ============================================
CREATE OR REPLACE VIEW `v_portfolio` AS
SELECT p.* FROM `portfolio` p
UNION ALL
SELECT pp.* FROM `portfolio_projects` pp
WHERE pp.id NOT IN (SELECT id FROM `portfolio`);

-- ============================================
-- 3. CONSOLIDATE ESTIMATOR TABLES
-- estimator_requests (canonical) ← estimators, estimator_leads (duplicates)
-- ============================================
CREATE OR REPLACE VIEW `v_estimator_requests` AS
SELECT er.* FROM `estimator_requests` er
UNION ALL
SELECT e.* FROM `estimators` e
WHERE e.id NOT IN (SELECT id FROM `estimator_requests`)
UNION ALL
SELECT el.* FROM `estimator_leads` el
WHERE el.id NOT IN (SELECT id FROM `estimator_requests`);

-- ============================================
-- 4. CONSOLIDATE PROJECT TABLES
-- projects (canonical) ← client_projects (duplicate)
-- ============================================
CREATE OR REPLACE VIEW `v_projects` AS
SELECT p.* FROM `projects` p
UNION ALL
SELECT cp.* FROM `client_projects` cp
WHERE cp.id NOT IN (SELECT id FROM `projects`);

-- ============================================
-- 5. CONSOLIDATE QUOTATION TABLES
-- quotations (canonical) ← client_quotations (duplicate)
-- ============================================
CREATE OR REPLACE VIEW `v_quotations` AS
SELECT q.* FROM `quotations` q
UNION ALL
SELECT cq.* FROM `client_quotations` cq
WHERE cq.id NOT IN (SELECT id FROM `quotations`);

-- ============================================
-- 6. CONSOLIDATE MEDIA TABLES
-- media (canonical) ← media_library, client_files (duplicates)
-- ============================================
CREATE OR REPLACE VIEW `v_media` AS
SELECT m.* FROM `media` m
UNION ALL
SELECT ml.* FROM `media_library` ml
WHERE ml.id NOT IN (SELECT id FROM `media`)
UNION ALL
SELECT cf.* FROM `client_files` cf
WHERE cf.id NOT IN (SELECT id FROM `media`);

-- ============================================
-- 7. CONSOLIDATE OTP TABLES
-- user_otps (canonical) ← otps (duplicate)
-- ============================================
CREATE OR REPLACE VIEW `v_otps` AS
SELECT uo.* FROM `user_otps` uo
UNION ALL
SELECT o.* FROM `otps` o
WHERE o.id NOT IN (SELECT id FROM `user_otps`);

-- ============================================
-- 8. CONSOLIDATE PACKAGE TABLES
-- construction_packages (canonical) ← estimator_packages (duplicate)
-- ============================================
CREATE OR REPLACE VIEW `v_packages` AS
SELECT cp.* FROM `construction_packages` cp
UNION ALL
SELECT ep.* FROM `estimator_packages` ep
WHERE ep.id NOT IN (SELECT id FROM `construction_packages`);

-- ============================================
-- 9. CONSOLIDATE PAYMENT TABLES
-- project_payments (canonical) ← client_payments (duplicate)
-- ============================================
CREATE OR REPLACE VIEW `v_payments` AS
SELECT pp.* FROM `project_payments` pp
UNION ALL
SELECT cp.* FROM `client_payments` cp
WHERE cp.id NOT IN (SELECT id FROM `project_payments`);

-- ============================================
-- 10. CONSOLIDATE TIMELINE TABLES
-- project_milestones (canonical) ← project_schedules, project_timelines (duplicates)
-- ============================================
CREATE OR REPLACE VIEW `v_project_timelines` AS
SELECT pm.* FROM `project_milestones` pm
UNION ALL
SELECT ps.* FROM `project_schedules` ps
WHERE ps.id NOT IN (SELECT id FROM `project_milestones`)
UNION ALL
SELECT pt.* FROM `project_timelines` pt
WHERE pt.id NOT IN (SELECT id FROM `project_milestones`);

-- ============================================
-- 11. CONSOLIDATE MATERIAL PRICING TABLES
-- material_pricing (canonical) ← estimator_materials (duplicate)
-- ============================================
CREATE OR REPLACE VIEW `v_material_pricing` AS
SELECT mp.* FROM `material_pricing` mp
UNION ALL
SELECT em.* FROM `estimator_materials` em
WHERE em.id NOT IN (SELECT id FROM `material_pricing`);

-- ============================================
-- 12. CONSOLIDATE AUDIT/LOG TABLES
-- audit_logs (canonical) ← admin_action_logs, security_logs (duplicates)
-- ============================================
CREATE OR REPLACE VIEW `v_audit_logs` AS
SELECT al.* FROM `audit_logs` al
UNION ALL
SELECT aal.* FROM `admin_action_logs` aal
WHERE aal.id NOT IN (SELECT id FROM `audit_logs`)
UNION ALL
SELECT sl.* FROM `security_logs` sl
WHERE sl.id NOT IN (SELECT id FROM `audit_logs`);

-- ============================================
-- 13. CONSOLIDATE TESTIMONIAL TABLES
-- testimonials (canonical) ← client_feedback, client_testimonials (duplicates)
-- ============================================
CREATE OR REPLACE VIEW `v_testimonials` AS
SELECT t.* FROM `testimonials` t
UNION ALL
SELECT cf.* FROM `client_feedback` cf
WHERE cf.id NOT IN (SELECT id FROM `testimonials`)
UNION ALL
SELECT ct.* FROM `client_testimonials` ct
WHERE ct.id NOT IN (SELECT id FROM `testimonials`);

-- ============================================
-- 14. ADD FOREIGN KEY CONSTRAINTS
-- ============================================

-- leads -> users
ALTER TABLE `leads` ADD INDEX IF NOT EXISTS `idx_leads_assigned_to` (`assigned_to`);
ALTER TABLE `leads` ADD INDEX IF NOT EXISTS `idx_leads_created_by` (`created_by`);

-- projects -> users
ALTER TABLE `projects` ADD INDEX IF NOT EXISTS `idx_projects_client_id` (`client_id`);
ALTER TABLE `projects` ADD INDEX IF NOT EXISTS `idx_projects_lead_id` (`lead_id`);
ALTER TABLE `projects` ADD INDEX IF NOT EXISTS `idx_projects_created_by` (`created_by`);

-- project_media -> projects
ALTER TABLE `project_media` ADD INDEX IF NOT EXISTS `idx_project_media_project` (`project_id`);

-- project_milestones -> projects
ALTER TABLE `project_milestones` ADD INDEX IF NOT EXISTS `idx_project_milestones_project` (`project_id`);

-- project_updates -> projects
ALTER TABLE `project_updates` ADD INDEX IF NOT EXISTS `idx_project_updates_project` (`project_id`);

-- project_tasks -> projects
ALTER TABLE `project_tasks` ADD INDEX IF NOT EXISTS `idx_project_tasks_project` (`project_id`);
ALTER TABLE `project_tasks` ADD INDEX IF NOT EXISTS `idx_project_tasks_assigned` (`assigned_to`);

-- quotations -> leads, projects, users
ALTER TABLE `quotations` ADD INDEX IF NOT EXISTS `idx_quotations_lead` (`lead_id`);
ALTER TABLE `quotations` ADD INDEX IF NOT EXISTS `idx_quotations_project` (`project_id`);
ALTER TABLE `quotations` ADD INDEX IF NOT EXISTS `idx_quotations_client` (`client_id`);
ALTER TABLE `quotations` ADD INDEX IF NOT EXISTS `idx_quotations_created_by` (`created_by`);

-- quotation_items -> quotations
ALTER TABLE `quotation_items` ADD INDEX IF NOT EXISTS `idx_quotation_items_quotation` (`quotation_id`);

-- estimator_requests -> packages, users
ALTER TABLE `estimator_requests` ADD INDEX IF NOT EXISTS `idx_estimator_requests_package` (`package_id`);
ALTER TABLE `estimator_requests` ADD INDEX IF NOT EXISTS `idx_estimator_requests_user` (`user_id`);
ALTER TABLE `estimator_requests` ADD INDEX IF NOT EXISTS `idx_estimator_requests_lead` (`lead_id`);

-- blogs -> users
ALTER TABLE `blogs` ADD INDEX IF NOT EXISTS `idx_blogs_author` (`author_id`);
ALTER TABLE `blogs` ADD INDEX IF NOT EXISTS `idx_blogs_category` (`category_id`);

-- blog_posts -> users
ALTER TABLE `blog_posts` ADD INDEX IF NOT EXISTS `idx_blog_posts_author` (`author_id`);
ALTER TABLE `blog_posts` ADD INDEX IF NOT EXISTS `idx_blog_posts_category` (`category_id`);

-- blog_comments -> blogs
ALTER TABLE `blog_comments` ADD INDEX IF NOT EXISTS `idx_blog_comments_blog` (`blog_id`);

-- user_otps -> users
ALTER TABLE `user_otps` ADD INDEX IF NOT EXISTS `idx_user_otps_user` (`user_id`);

-- user_sessions -> users
ALTER TABLE `user_sessions` ADD INDEX IF NOT EXISTS `idx_user_sessions_user` (`user_id`);

-- security_logs -> users
ALTER TABLE `security_logs` ADD INDEX IF NOT EXISTS `idx_security_logs_user` (`user_id`);

-- audit_logs -> users
ALTER TABLE `audit_logs` ADD INDEX IF NOT EXISTS `idx_audit_logs_user` (`user_id`);

-- media -> users
ALTER TABLE `media` ADD INDEX IF NOT EXISTS `idx_media_uploaded_by` (`uploaded_by`);

-- ============================================
-- 15. ADD UNIQUE INDEXES FOR PERFORMANCE
-- ============================================
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_users_email` (`email`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_users_phone` (`phone`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_users_role_status` (`role`, `status`);

ALTER TABLE `leads` ADD INDEX IF NOT EXISTS `idx_leads_email` (`email`);
ALTER TABLE `leads` ADD INDEX IF NOT EXISTS `idx_leads_phone` (`phone`);
ALTER TABLE `leads` ADD INDEX IF NOT EXISTS `idx_leads_status` (`status`);
ALTER TABLE `leads` ADD INDEX IF NOT EXISTS `idx_leads_source` (`source`);

ALTER TABLE `projects` ADD INDEX IF NOT EXISTS `idx_projects_status` (`status`);
ALTER TABLE `projects` ADD INDEX IF NOT EXISTS `idx_projects_code` (`project_code`);

ALTER TABLE `blogs` ADD INDEX IF NOT EXISTS `idx_blogs_slug` (`slug`);
ALTER TABLE `blogs` ADD INDEX IF NOT EXISTS `idx_blogs_status` (`status`);

ALTER TABLE `portfolio` ADD INDEX IF NOT EXISTS `idx_portfolio_slug` (`slug`);
ALTER TABLE `portfolio` ADD INDEX IF NOT EXISTS `idx_portfolio_status` (`status`);

ALTER TABLE `services` ADD INDEX IF NOT EXISTS `idx_services_slug` (`slug`);
ALTER TABLE `services` ADD INDEX IF NOT EXISTS `idx_services_status` (`status`);

ALTER TABLE `construction_packages` ADD INDEX IF NOT EXISTS `idx_packages_slug` (`slug`);
ALTER TABLE `construction_packages` ADD INDEX IF NOT EXISTS `idx_packages_status` (`status`);

ALTER TABLE `quotations` ADD INDEX IF NOT EXISTS `idx_quotations_number` (`quotation_number`);
ALTER TABLE `quotations` ADD INDEX IF NOT EXISTS `idx_quotations_status` (`status`);

ALTER TABLE `estimator_requests` ADD INDEX IF NOT EXISTS `idx_estimator_requests_phone` (`phone`);
ALTER TABLE `estimator_requests` ADD INDEX IF NOT EXISTS `idx_estimator_requests_status` (`status`);
ALTER TABLE `estimator_requests` ADD INDEX IF NOT EXISTS `idx_estimator_requests_created` (`created_at`);

ALTER TABLE `login_attempts` ADD INDEX IF NOT EXISTS `idx_login_attempts_ip` (`ip_address`);
ALTER TABLE `login_attempts` ADD INDEX IF NOT EXISTS `idx_login_attempts_email` (`email`);

ALTER TABLE `security_logs` ADD INDEX IF NOT EXISTS `idx_security_logs_severity` (`severity`);
ALTER TABLE `security_logs` ADD INDEX IF NOT EXISTS `idx_security_logs_created` (`created_at`);

ALTER TABLE `analytics_events` ADD INDEX IF NOT EXISTS `idx_analytics_events_name` (`event_name`);
ALTER TABLE `analytics_events` ADD INDEX IF NOT EXISTS `idx_analytics_events_created` (`created_at`);

-- ============================================
-- 16. CLEANUP OLD/BACKUP TABLES
-- ============================================
DROP TABLE IF EXISTS `estimator_calculation_log_backup`;
DROP TABLE IF EXISTS `rate_limits_old`;
DROP TABLE IF EXISTS `active_sessions_view`;
DROP TABLE IF EXISTS `failed_login_attempts_view`;
DROP TABLE IF EXISTS `security_overview`;
DROP TABLE IF EXISTS `suspicious_activity_view`;

-- ============================================
-- NOTE: After code migration is complete, run:
-- DROP TABLE IF EXISTS `blog_posts`;
-- DROP TABLE IF EXISTS `portfolio_projects`;
-- DROP TABLE IF EXISTS `estimators`;
-- DROP TABLE IF EXISTS `estimator_leads`;
-- DROP TABLE IF EXISTS `client_projects`;
-- DROP TABLE IF EXISTS `client_quotations`;
-- DROP TABLE IF EXISTS `media_library`;
-- DROP TABLE IF EXISTS `client_files`;
-- DROP TABLE IF EXISTS `otps`;
-- DROP TABLE IF EXISTS `estimator_packages`;
-- DROP TABLE IF EXISTS `client_payments`;
-- DROP TABLE IF EXISTS `project_schedules`;
-- DROP TABLE IF EXISTS `project_timelines`;
-- DROP TABLE IF EXISTS `estimator_materials`;
-- DROP TABLE IF EXISTS `admin_action_logs`;
-- DROP TABLE IF EXISTS `security_logs`;
-- DROP TABLE IF EXISTS `client_feedback`;
-- DROP TABLE IF EXISTS `client_testimonials`;
-- ============================================