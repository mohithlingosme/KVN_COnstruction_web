ALTER TABLE security_logs
    DROP INDEX idx_security_logs_user_event,
    DROP INDEX idx_security_logs_level_created;

ALTER TABLE user_sessions
    DROP INDEX idx_user_sessions_active,
    DROP INDEX idx_user_sessions_remember_token,
    DROP INDEX idx_user_sessions_expires_at;

DROP TABLE IF EXISTS media_derivatives;
DROP TABLE IF EXISTS route_seo_meta;
DROP TABLE IF EXISTS cta_blocks;
DROP TABLE IF EXISTS homepage_sections;
DROP TABLE IF EXISTS homepage_slides;
DROP TABLE IF EXISTS email_verification_tokens;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;
