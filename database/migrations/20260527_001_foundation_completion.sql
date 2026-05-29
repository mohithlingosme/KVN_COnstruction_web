CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    role_key VARCHAR(100) NOT NULL UNIQUE,
    role_name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    permission_key VARCHAR(150) NOT NULL UNIQUE,
    permission_name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_role_permission (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_roles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_role (user_id, role_id),
    CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_verification_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    verified_at DATETIME DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_email_verification_token_hash (token_hash),
    KEY idx_email_verification_user_id (user_id),
    CONSTRAINT fk_email_verification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homepage_slides (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homepage_sections (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(100) NOT NULL UNIQUE,
    section_title VARCHAR(255) DEFAULT NULL,
    section_content LONGTEXT DEFAULT NULL,
    section_payload LONGTEXT DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    status ENUM('draft','published') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cta_blocks (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS route_seo_meta (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_derivatives (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    media_id BIGINT UNSIGNED NOT NULL,
    derivative_type VARCHAR(100) NOT NULL,
    derivative_path VARCHAR(255) NOT NULL,
    width INT DEFAULT NULL,
    height INT DEFAULT NULL,
    file_size BIGINT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_media_derivatives_media_id (media_id),
    CONSTRAINT fk_media_derivatives_media FOREIGN KEY (media_id) REFERENCES media_library(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO roles (id, role_key, role_name) VALUES
    (1, 'admin', 'Administrator'),
    (2, 'client', 'Client'),
    (3, 'employee', 'Employee');

INSERT IGNORE INTO permissions (permission_key, permission_name) VALUES
    ('dashboard.view', 'View dashboard'),
    ('leads.manage', 'Manage leads'),
    ('projects.manage', 'Manage projects'),
    ('quotations.manage', 'Manage quotations'),
    ('cms.manage', 'Manage CMS'),
    ('security.view', 'View security dashboards'),
    ('media.manage', 'Manage media');

INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT id, 1 FROM users WHERE role = 'admin';

INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT id, 2 FROM users WHERE role = 'client';

INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT id, 3 FROM users WHERE role = 'employee';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

ALTER TABLE user_sessions
    ADD INDEX idx_user_sessions_active (user_id, is_active, revoked_at),
    ADD INDEX idx_user_sessions_remember_token (remember_token),
    ADD INDEX idx_user_sessions_expires_at (expires_at);

ALTER TABLE security_logs
    ADD INDEX idx_security_logs_user_event (user_id, event_type, created_at),
    ADD INDEX idx_security_logs_level_created (event_level, created_at);
