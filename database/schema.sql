-- Admin auth schema + seed for KVn_Construction
-- Creates database `kvn_construction` and required `admins` table.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `kvn_construction`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `kvn_construction`;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_email` (`email`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Seed default admin credentials (as requested)
-- Email: admin@kvn.com
-- Password: password
-- Password is bcrypt hash (from your existing dump)
INSERT INTO `admins` (`name`, `email`, `password`)
SELECT 'Admin', 'admin@kvn.com',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE NOT EXISTS (
  SELECT 1 FROM `admins` WHERE `email` = 'admin@kvn.com' LIMIT 1
);

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
