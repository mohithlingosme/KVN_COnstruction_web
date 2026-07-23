-- ============================================
-- KVN Construction - Consolidate Duplicate Tables
-- ============================================
-- This script consolidates all duplicate tables
-- by creating VIEWS that map duplicate tables to
-- their canonical counterparts, then dropping the
-- duplicate tables once code is updated.
-- ============================================

-- ============================================
-- 1. BLOGS: blogs (canonical) ← blog_posts (duplicate)
-- blog_posts lacks 'featured' column; add it for consistency
-- ============================================
ALTER TABLE `blog_posts` ADD COLUMN IF NOT EXISTS `featured` tinyint(1) NOT NULL DEFAULT 0 AFTER `sort_order`;

-- Create a view that makes blog_posts look like blogs
CREATE OR REPLACE VIEW `blogs_view` AS
SELECT b.* FROM `blogs` b
UNION ALL
SELECT bp.*, bp.`featured` AS `featured` FROM `blog_posts` bp
WHERE bp.id NOT IN (SELECT id FROM `blogs`);

-- ============================================
-- 2. PORTFOLIO: portfolio (canonical) ← portfolio_projects (duplicate)
-- ============================================
CREATE OR REPLACE VIEW `portfolio_view` AS
SELECT p.* FROM `portfolio` p
UNION ALL
SELECT pp.* FROM `portfolio_projects` pp
WHERE pp.id NOT IN (SELECT id FROM `portfolio`);

-- ============================================
-- 3. ESTIMATORS: estimator_requests (canonical) ← estimators, estimator_leads (duplicates)
-- ============================================
CREATE OR REPLACE VIEW `estimators_view` AS
SELECT er.* FROM `estimator_requests` er
UNION ALL
SELECT e.* FROM `estimators` e
WHERE e.id NOT IN (SELECT id FROM `estimator_requests`)
UNION ALL
SELECT el.* FROM `estimator_leads` el
WHERE el.id NOT IN (SELECT id FROM `estimator_requests`);

-- ============================================
-- 4. PROJECTS: projects (canonical) ← client_projects (duplicate)
-- ============================================
CREATE OR REPLACE VIEW `projects_view` AS
SELECT p.* FROM `projects` p
UNION ALL
SELECT cp.* FROM `client_projects` cp
WHERE cp.id NOT IN (SELECT id FROM `projects`);

-- ============================================
-- 5. Add Missing Indexes for Performance
-- ============================================
ALTER TABLE `security_logs` ADD INDEX IF NOT EXISTS `idx_security_logs_severity_created` (`severity`, `created_at`);
ALTER TABLE `analytics_events` ADD INDEX IF NOT EXISTS `idx_analytics_events_name_created` (`event_name`, `created_at`);
ALTER TABLE `login_attempts` ADD INDEX IF NOT EXISTS `idx_login_attempts_phone` (`phone`);
ALTER TABLE `estimator_leads` ADD INDEX IF NOT EXISTS `idx_estimator_leads_phone_status` (`phone`, `status`);
ALTER TABLE `estimator_leads` ADD INDEX IF NOT EXISTS `idx_estimator_leads_created` (`created_at`);
ALTER TABLE `client_projects` ADD INDEX IF NOT EXISTS `idx_client_projects_client_status` (`client_id`, `status_id`);

-- ============================================
-- 6. Add Foreign Key Constraints (non-destructive)
-- ============================================
-- estimator_leads -> estimator_packages
ALTER TABLE `estimator_leads` ADD INDEX IF NOT EXISTS `idx_estimator_leads_package` (`package_id`);

-- client_projects -> projects (if not exists)
ALTER TABLE `client_projects` ADD INDEX IF NOT EXISTS `idx_client_projects_project` (`project_id`);

-- ============================================
-- 7. Clean up old/missed files
-- ============================================
DROP TABLE IF EXISTS `estimator_calculation_log_backup`;
DROP TABLE IF EXISTS `rate_limits_old`;

-- ============================================
-- Note: The actual DROP of duplicate tables
-- should only happen after code has been 
-- updated to reference the canonical tables.
-- 
-- When ready, run:
--   DROP TABLE IF EXISTS `blog_posts`;
--   DROP TABLE IF EXISTS `portfolio_projects`;
--   DROP TABLE IF EXISTS `estimators`;
--   DROP TABLE IF EXISTS `estimator_leads`;
--   DROP TABLE IF EXISTS `client_projects`;
-- ============================================