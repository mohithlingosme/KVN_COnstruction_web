-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 07, 2026 at 11:34 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kvnc_platform`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cleanup_security_data` ()   BEGIN
  DELETE FROM `otps` WHERE `expires_at` < DATE_SUB(NOW(), INTERVAL 1 DAY);
  DELETE FROM `rate_limits` WHERE `updated_at` < DATE_SUB(NOW(), INTERVAL 1 DAY);
  DELETE FROM `remember_tokens` WHERE `expires_at` < NOW();
  DELETE FROM `email_verification_tokens` WHERE `expires_at` < NOW();
  UPDATE `user_sessions`
     SET `is_active` = 0, `revoked_at` = NOW(), `logout_reason` = 'cleanup_expired'
   WHERE `is_active` = 1
     AND ((`expires_at` IS NOT NULL AND `expires_at` < NOW())
       OR (`last_activity` IS NOT NULL AND `last_activity` < DATE_SUB(NOW(), INTERVAL 30 DAY)));
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `about_advantages`
--

CREATE TABLE `about_advantages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `about_page`
--

CREATE TABLE `about_page` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `hero_title` varchar(255) NOT NULL,
  `hero_description` text NOT NULL,
  `mission_title` varchar(255) NOT NULL,
  `mission_content` text NOT NULL,
  `vision_title` varchar(255) NOT NULL,
  `vision_content` text NOT NULL,
  `process_content` text NOT NULL,
  `why_choose_content` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `about_page`
--

INSERT INTO `about_page` (`id`, `hero_title`, `hero_description`, `mission_title`, `mission_content`, `vision_title`, `vision_content`, `process_content`, `why_choose_content`, `updated_at`) VALUES
(1, 'About KVN Construction', 'A construction partner for homeowners who want clarity and dependable execution.', 'Our Mission', 'To make construction transparent, measurable and professionally managed.', 'Our Vision', 'To become Karnataka\'s most trusted tech-enabled construction company.', 'Estimate, design, plan, build, track and hand over with documented milestones.', 'We combine site engineering discipline with customer-friendly communication.', '2026-06-01 14:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `about_process_steps`
--

CREATE TABLE `about_process_steps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `step_title` varchar(255) NOT NULL,
  `step_description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `about_specifications`
--

CREATE TABLE `about_specifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `spec_title` varchar(255) NOT NULL,
  `spec_value` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_sessions_view`
-- (See below for the actual view)
--
CREATE TABLE `active_sessions_view` (
`user_id` bigint(20) unsigned
,`full_name` varchar(150)
,`email` varchar(150)
,`role` varchar(50)
,`session_token` varchar(255)
,`ip_address` varchar(45)
,`user_agent` varchar(255)
,`last_activity` datetime
,`session_started` timestamp
,`minutes_inactive` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `admin_action_logs`
--

CREATE TABLE `admin_action_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_data` text DEFAULT NULL,
  `new_data` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `analytics_events`
--

CREATE TABLE `analytics_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event_name` varchar(120) NOT NULL,
  `entity_type` varchar(80) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `properties_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action_type` varchar(100) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext DEFAULT NULL,
  `new_values` longtext DEFAULT NULL,
  `old_values_json` longtext DEFAULT NULL,
  `new_values_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action_type`, `action`, `description`, `ip_address`, `entity_type`, `entity_id`, `old_values`, `new_values`, `old_values_json`, `new_values_json`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'database_seeded', 'database_seeded', 'Production seed data imported successfully', NULL, 'database', NULL, NULL, NULL, NULL, NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `blocked_users`
--

CREATE TABLE `blocked_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(100) NOT NULL,
  `reason` text NOT NULL,
  `blocked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'blocked'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `author_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `views_count` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'published',
  `published_at` timestamp NULL DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `uuid`, `author_id`, `category_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `category`, `tags`, `meta_title`, `meta_description`, `meta_keywords`, `views_count`, `is_featured`, `status`, `published_at`, `sort_order`, `created_at`, `updated_at`, `deleted_at`, `featured`) VALUES
(1, 'fdbaa8eb-5dc9-11f1-b171-4c0f3ee9b145', 1, 1, 'Modern Villa Construction Trends 2026', 'modern-villa-construction-trends-2026', 'Explore the latest luxury villa construction trends.', '<p>Modern villa construction now focuses on smart-home readiness, efficient envelopes, durable materials and cleaner project tracking.</p>', 'blogs/villa-trends.jpg', 'Construction', 'villa,luxury,construction', 'Modern Villa Construction Trends 2026', 'Latest trends in luxury villa construction.', 'villa construction,luxury homes', 0, 1, 'published', '2026-06-01 14:55:55', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL, 0),
(2, 'fdbafcc3-5dc9-11f1-b171-4c0f3ee9b145', 1, 1, 'How to Read a Construction Quotation', 'how-to-read-construction-quotation', 'A simple homeowner guide to quotation line items.', '<p>A good quotation separates civil work, finishes, taxes, exclusions and payment milestones clearly.</p>', 'blogs/quotation-guide.jpg', 'Construction', 'quotation,costing', 'How to Read a Construction Quotation', 'Understand home construction quotation line items.', 'construction quotation,cost estimate', 0, 0, 'published', '2026-06-01 14:55:55', 2, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_categories`
--

INSERT INTO `blog_categories` (`id`, `category_name`, `name`, `slug`, `description`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Construction', 'Construction', 'construction', 'Construction planning and execution insights.', 'active', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'Home Design', 'Home Design', 'home-design', 'Design and interiors guidance.', 'active', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `blog_comments`
--

CREATE TABLE `blog_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `blog_id` bigint(20) UNSIGNED NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `ip_address` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `author_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `views_count` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'published',
  `published_at` timestamp NULL DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `uuid`, `author_id`, `category_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `category`, `tags`, `meta_title`, `meta_description`, `meta_keywords`, `views_count`, `is_featured`, `status`, `published_at`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fdbaa8eb-5dc9-11f1-b171-4c0f3ee9b145', 1, 1, 'Modern Villa Construction Trends 2026', 'modern-villa-construction-trends-2026', 'Explore the latest luxury villa construction trends.', '<p>Modern villa construction now focuses on smart-home readiness, efficient envelopes, durable materials and cleaner project tracking.</p>', 'blogs/villa-trends.jpg', 'Construction', 'villa,luxury,construction', 'Modern Villa Construction Trends 2026', 'Latest trends in luxury villa construction.', 'villa construction,luxury homes', 0, 1, 'published', '2026-06-01 14:55:55', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'fdbafcc3-5dc9-11f1-b171-4c0f3ee9b145', 1, 1, 'How to Read a Construction Quotation', 'how-to-read-construction-quotation', 'A simple homeowner guide to quotation line items.', '<p>A good quotation separates civil work, finishes, taxes, exclusions and payment milestones clearly.</p>', 'blogs/quotation-guide.jpg', 'Construction', 'quotation,costing', 'How to Read a Construction Quotation', 'Understand home construction quotation line items.', 'construction quotation,cost estimate', 0, 0, 'published', '2026-06-01 14:55:55', 2, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `blog_tags`
--

CREATE TABLE `blog_tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tag_name` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_name` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `sms_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `user_id`, `client_name`, `full_name`, `email`, `phone`, `password`, `address`, `city`, `state`, `pincode`, `profile_image`, `status`, `email_notifications`, `sms_notifications`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'Ananya Rao', 'Ananya Rao', 'ananya.rao@example.com', '9876500001', NULL, 'Indiranagar, Bengaluru', 'Bengaluru', 'Karnataka', '560038', NULL, 'active', 1, 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client_agreements`
--

CREATE TABLE `client_agreements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `agreement_title` varchar(255) NOT NULL,
  `agreement_number` varchar(100) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `agreement_type` varchar(100) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `agreement_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `file_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_documents`
--

CREATE TABLE `client_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `document_title` varchar(255) NOT NULL,
  `document_category` varchar(100) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` varchar(50) NOT NULL,
  `upload_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Active',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_downloads`
--

CREATE TABLE `client_downloads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `document_title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` varchar(50) NOT NULL,
  `total_downloads` int(11) NOT NULL DEFAULT 0,
  `last_downloaded` datetime DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_feedback`
--

CREATE TABLE `client_feedback` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `testimonial` text NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_files`
--

CREATE TABLE `client_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_invoices`
--

CREATE TABLE `client_invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `invoice_title` varchar(255) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL,
  `payment_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_messages`
--

CREATE TABLE `client_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_notifications`
--

CREATE TABLE `client_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_payments`
--

CREATE TABLE `client_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `payment_type` varchar(255) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_permits`
--

CREATE TABLE `client_permits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `permit_title` varchar(255) NOT NULL,
  `permit_number` varchar(100) NOT NULL,
  `authority_name` varchar(255) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `file_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_projects`
--

CREATE TABLE `client_projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `client_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quotation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED DEFAULT 1,
  `project_code` varchar(60) DEFAULT NULL,
  `project_name` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `project_type` varchar(120) DEFAULT NULL,
  `site_address` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expected_end_date` date DEFAULT NULL,
  `progress` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'Planning',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_projects`
--

INSERT INTO `client_projects` (`id`, `client_id`, `client_user_id`, `quotation_id`, `status_id`, `project_code`, `project_name`, `name`, `project_type`, `site_address`, `location`, `start_date`, `expected_end_date`, `progress`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 2, 1, 2, 'KVN-2026-001', 'Whitefield Premium Villa', 'Whitefield Premium Villa', 'Villa Construction', 'Plot 18, Whitefield, Bengaluru', 'Whitefield', '2026-05-15', '2027-03-15', 18, 'Foundation', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client_quotations`
--

CREATE TABLE `client_quotations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `quotation_number` varchar(100) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `quotation_title` varchar(255) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `valid_until` date DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_quotations`
--

INSERT INTO `client_quotations` (`id`, `client_id`, `quotation_number`, `project_name`, `quotation_title`, `amount`, `total_amount`, `status`, `valid_until`, `file_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'QTN-202606-0001', 'Whitefield Premium Villa', 'Premium Villa Construction Quotation', 14500000.00, 14500000.00, 'Pending', '2026-07-01', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client_testimonials`
--

CREATE TABLE `client_testimonials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `testimonial` text NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_uploaded_images`
--

CREATE TABLE `client_uploaded_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `image_title` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_uploaded_videos`
--

CREATE TABLE `client_uploaded_videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `image_title` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `construction_packages`
--

CREATE TABLE `construction_packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `package_name` varchar(150) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `slug` varchar(180) NOT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `package_type` varchar(100) DEFAULT NULL,
  `base_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `price_per_sqft` decimal(12,2) NOT NULL DEFAULT 0.00,
  `min_area_sqft` int(11) DEFAULT NULL,
  `max_area_sqft` int(11) DEFAULT NULL,
  `delivery_time_months` int(11) DEFAULT NULL,
  `duration_months` int(11) DEFAULT NULL,
  `specifications` longtext DEFAULT NULL,
  `features` longtext DEFAULT NULL,
  `addons` longtext DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `package_image` varchar(255) DEFAULT NULL,
  `brochure_file` varchar(255) DEFAULT NULL,
  `includes_gst` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `seo_keywords` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `construction_packages`
--

INSERT INTO `construction_packages` (`id`, `uuid`, `package_name`, `name`, `slug`, `short_description`, `description`, `package_type`, `base_price`, `price_per_sqft`, `min_area_sqft`, `max_area_sqft`, `delivery_time_months`, `duration_months`, `specifications`, `features`, `addons`, `featured_image`, `package_image`, `brochure_file`, `includes_gst`, `status`, `is_featured`, `display_order`, `sort_order`, `seo_title`, `seo_description`, `seo_keywords`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fdaedfbb-5dc9-11f1-b171-4c0f3ee9b145', 'Silver Package', 'Silver Package', 'silver-package', 'Affordable quality construction package.', 'Structural construction with reliable materials, standard fittings, and essential finish quality for budget-conscious homeowners.', 'Residential', 500000.00, 1850.00, 600, 4000, 8, 8, 'Standard cement, vitrified flooring, UPVC windows', 'Structural work, plumbing, electrical, standard flooring', 'False ceiling, modular kitchen', 'packages/silver-package.jpg', NULL, NULL, 1, 'active', 1, 1, 1, 'Silver Construction Package', 'Affordable residential construction package in Karnataka.', 'construction,silver package,home construction', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'fdaf3dd8-5dc9-11f1-b171-4c0f3ee9b145', 'Gold Package', 'Gold Package', 'gold-package', 'Premium modern construction package.', 'Premium interiors, branded fittings, modular kitchen support, and stronger finish specifications for modern family homes.', 'Residential', 800000.00, 2450.00, 800, 6000, 10, 10, 'Premium tiles, teak doors, branded fittings', 'Premium interiors, modular kitchen, branded fixtures', 'Solar system, smart lighting', 'packages/gold-package.jpg', NULL, NULL, 1, 'active', 1, 2, 2, 'Gold Construction Package', 'Premium home construction package with branded fittings.', 'gold package,premium construction', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 'fdaf4107-5dc9-11f1-b171-4c0f3ee9b145', 'Platinum Villa Package', 'Platinum Villa Package', 'platinum-villa-package', 'Luxury villa construction with smart-home readiness.', 'High-end villa package with enhanced structural detailing, designer finishes, premium bathware, smart wiring, and landscape coordination.', 'Villa', 1200000.00, 3200.00, 1200, 12000, 14, 14, 'Italian marble, teak wood, smart wiring, premium facade', 'Luxury finishes, smart home provisions, designer consultation', 'Home automation, landscaped sit-out, solar backup', 'packages/platinum-villa.jpg', NULL, NULL, 1, 'active', 1, 3, 3, 'Platinum Villa Construction Package', 'Luxury villa construction package for premium homes.', 'villa construction,luxury homes,platinum package', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

--
-- Triggers `construction_packages`
--
DELIMITER $$
CREATE TRIGGER `tr_construction_packages_alias_insert` BEFORE INSERT ON `construction_packages` FOR EACH ROW BEGIN
  IF NEW.name IS NULL OR NEW.name = '' THEN
    SET NEW.name = NEW.package_name;
  END IF;
  IF NEW.package_name IS NULL OR NEW.package_name = '' THEN
    SET NEW.package_name = NEW.name;
  END IF;
  IF NEW.duration_months IS NULL THEN
    SET NEW.duration_months = NEW.delivery_time_months;
  END IF;
  IF NEW.delivery_time_months IS NULL THEN
    SET NEW.delivery_time_months = NEW.duration_months;
  END IF;
  IF NEW.base_price = 0 AND NEW.price_per_sqft > 0 THEN
    SET NEW.base_price = NEW.price_per_sqft;
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_construction_packages_alias_update` BEFORE UPDATE ON `construction_packages` FOR EACH ROW BEGIN
  IF NEW.name IS NULL OR NEW.name = '' THEN
    SET NEW.name = NEW.package_name;
  END IF;
  IF NEW.package_name IS NULL OR NEW.package_name = '' THEN
    SET NEW.package_name = NEW.name;
  END IF;
  IF NEW.duration_months IS NULL THEN
    SET NEW.duration_months = NEW.delivery_time_months;
  END IF;
  IF NEW.delivery_time_months IS NULL THEN
    SET NEW.delivery_time_months = NEW.duration_months;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `contact_page`
--

CREATE TABLE `contact_page` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `hero_title` varchar(255) NOT NULL,
  `hero_description` text NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `office_address` text NOT NULL,
  `office_hours` varchar(255) NOT NULL,
  `google_map_link` text NOT NULL,
  `form_title` varchar(255) NOT NULL,
  `form_description` text NOT NULL,
  `why_choose_title` varchar(255) NOT NULL,
  `why_choose_content` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_page`
--

INSERT INTO `contact_page` (`id`, `hero_title`, `hero_description`, `phone`, `email`, `office_address`, `office_hours`, `google_map_link`, `form_title`, `form_description`, `why_choose_title`, `why_choose_content`, `updated_at`) VALUES
(1, 'Talk to KVN Construction', 'Share your site details and our team will help you plan the next step.', '+91 98765 43210', 'info@kvnconstruction.com', '42 Brigade Road, Bengaluru, Karnataka 560001', 'Mon-Sat, 9:30 AM to 6:30 PM', 'https://maps.google.com', 'Request a callback', 'Tell us about your project requirement.', 'Why clients choose us', 'Transparent estimates, experienced engineers and structured project tracking.', '2026-06-01 14:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `contact_page_features`
--

CREATE TABLE `contact_page_features` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cta_blocks`
--

CREATE TABLE `cta_blocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `block_key` varchar(100) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `secondary_text` varchar(100) DEFAULT NULL,
  `secondary_link` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cta_blocks`
--

INSERT INTO `cta_blocks` (`id`, `block_key`, `title`, `description`, `button_text`, `button_link`, `secondary_text`, `secondary_link`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'estimate_cta', 'Ready to plan your construction budget?', 'Use the estimator or speak to our sales team for a site-specific quotation.', 'Start Estimate', '/public/estimator.php', NULL, NULL, 'published', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_verification_tokens`
--

CREATE TABLE `email_verification_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimators`
--

CREATE TABLE `estimators` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `plot_area` decimal(12,2) NOT NULL,
  `floors` int(11) NOT NULL DEFAULT 1,
  `package_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_zone_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quality` varchar(30) NOT NULL DEFAULT 'standard',
  `estimated_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimator_calculation_log`
--

CREATE TABLE `estimator_calculation_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `package_id` bigint(20) UNSIGNED DEFAULT NULL,
  `plot_area` decimal(12,2) NOT NULL,
  `floors` int(11) NOT NULL,
  `base_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `labor_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `material_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `location_multiplier` decimal(6,2) NOT NULL DEFAULT 1.00,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimator_leads`
--

CREATE TABLE `estimator_leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `plot_area` decimal(12,2) NOT NULL,
  `floors` int(11) NOT NULL DEFAULT 1,
  `package_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_zone_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quality` varchar(30) NOT NULL DEFAULT 'standard',
  `estimated_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimator_materials`
--

CREATE TABLE `estimator_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `quality_grade` varchar(30) NOT NULL DEFAULT 'standard',
  `supplier` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimator_packages`
--

CREATE TABLE `estimator_packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `package_name` varchar(150) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `slug` varchar(180) NOT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `package_type` varchar(100) DEFAULT NULL,
  `base_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `price_per_sqft` decimal(12,2) NOT NULL DEFAULT 0.00,
  `min_area_sqft` int(11) DEFAULT NULL,
  `max_area_sqft` int(11) DEFAULT NULL,
  `delivery_time_months` int(11) DEFAULT NULL,
  `duration_months` int(11) DEFAULT NULL,
  `specifications` longtext DEFAULT NULL,
  `features` longtext DEFAULT NULL,
  `addons` longtext DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `package_image` varchar(255) DEFAULT NULL,
  `brochure_file` varchar(255) DEFAULT NULL,
  `includes_gst` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `seo_keywords` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimator_pricing`
--

CREATE TABLE `estimator_pricing` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `price_per_sqft` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimator_requests`
--

CREATE TABLE `estimator_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `plot_area` decimal(12,2) NOT NULL,
  `floors` int(11) NOT NULL DEFAULT 1,
  `package_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_zone_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quality` varchar(30) NOT NULL DEFAULT 'standard',
  `estimated_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `estimator_requests`
--

INSERT INTO `estimator_requests` (`id`, `uuid`, `lead_id`, `user_id`, `full_name`, `email`, `phone`, `location`, `plot_area`, `floors`, `package_id`, `location_zone_id`, `quality`, `estimated_cost`, `status`, `reviewed_by`, `reviewed_at`, `ip_address`, `user_agent`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fdb310c2-5dc9-11f1-b171-4c0f3ee9b145', 2, NULL, 'Meera Iyer', 'meera.iyer@example.com', '9880033344', 'Sarjapur Road', 1200.00, 2, 2, 2, 'standard', 12637800.00, 'pending', NULL, NULL, '127.0.0.1', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'fdb362a3-5dc9-11f1-b171-4c0f3ee9b145', 1, NULL, 'Sanjay Kumar', 'sanjay.kumar@example.com', '9880011122', 'Whitefield', 1800.00, 2, 3, 1, 'luxury', 30988800.00, 'reviewed', NULL, NULL, '127.0.0.1', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `failed_login_attempts_view`
-- (See below for the actual view)
--
CREATE TABLE `failed_login_attempts_view` (
`ip_address` varchar(45)
,`email` varchar(150)
,`attempt_count` bigint(21)
,`last_attempt` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'How accurate is the online estimator?', 'The estimator gives a planning range based on area, package and location. A site visit is required for a final quotation.', 'Estimator', 1, 1, 'active', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'Do your package prices include GST?', 'Yes, active construction packages are configured with GST-inclusive pricing unless mentioned otherwise.', 'Pricing', 2, 2, 'active', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 'Can I track project progress online?', 'Yes, clients can use the client portal to view milestones, updates, documents and payments.', 'Client Portal', 3, 3, 'active', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `general_settings`
--

CREATE TABLE `general_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `site_name` varchar(255) NOT NULL,
  `site_tagline` varchar(255) NOT NULL,
  `admin_email` varchar(150) NOT NULL,
  `support_email` varchar(150) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `whatsapp` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `facebook_link` varchar(255) NOT NULL DEFAULT '',
  `instagram_link` varchar(255) NOT NULL DEFAULT '',
  `youtube_link` varchar(255) NOT NULL DEFAULT '',
  `linkedin_link` varchar(255) NOT NULL DEFAULT '',
  `logo` varchar(255) NOT NULL DEFAULT '',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `general_settings`
--

INSERT INTO `general_settings` (`id`, `site_name`, `site_tagline`, `admin_email`, `support_email`, `phone`, `whatsapp`, `address`, `facebook_link`, `instagram_link`, `youtube_link`, `linkedin_link`, `logo`, `updated_at`) VALUES
(1, 'KVN Construction', 'Building Dreams, Delivering Excellence', 'admin@kvnconstruction.com', 'support@kvnconstruction.com', '+91 98765 43210', '+91 98765 43210', '42 Brigade Road, Bengaluru', 'https://facebook.com/kvnconstruction', 'https://instagram.com/kvnconstruction', 'https://youtube.com/@kvnconstruction', 'https://linkedin.com/company/kvnconstruction', 'assets/images/logo.png', '2026-06-01 14:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `homepage_content`
--

CREATE TABLE `homepage_content` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `hero_title` varchar(255) NOT NULL,
  `hero_subtitle` text NOT NULL,
  `hero_button_text` varchar(100) NOT NULL,
  `hero_button_link` varchar(255) NOT NULL,
  `section2_title` varchar(255) NOT NULL,
  `section2_content` text NOT NULL,
  `services_title` varchar(255) NOT NULL,
  `services_content` text NOT NULL,
  `cta_title` varchar(255) NOT NULL,
  `cta_button_text` varchar(100) NOT NULL,
  `cta_button_link` varchar(255) NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `homepage_sections`
--

CREATE TABLE `homepage_sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `section_key` varchar(100) NOT NULL,
  `section_title` varchar(255) DEFAULT NULL,
  `section_content` longtext DEFAULT NULL,
  `section_payload` longtext DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `homepage_sections`
--

INSERT INTO `homepage_sections` (`id`, `section_key`, `section_title`, `section_content`, `section_payload`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'services_intro', 'Construction services built around trust', 'From residential homes to premium villas, our process keeps cost, quality and timelines visible.', NULL, 1, 'published', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `homepage_slides`
--

CREATE TABLE `homepage_slides` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `primary_button_text` varchar(100) DEFAULT NULL,
  `primary_button_link` varchar(255) DEFAULT NULL,
  `secondary_button_text` varchar(100) DEFAULT NULL,
  `secondary_button_link` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `homepage_slides`
--

INSERT INTO `homepage_slides` (`id`, `title`, `subtitle`, `image_path`, `primary_button_text`, `primary_button_link`, `secondary_button_text`, `secondary_button_link`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Build your home with clarity', 'Transparent estimates, professional execution and client portal tracking.', 'hero/home-construction.jpg', 'Get Estimate', '/public/estimator.php', 'View Projects', '/public/projects.php', 1, 'published', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `integration_settings`
--

CREATE TABLE `integration_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `google_maps_api` varchar(255) NOT NULL DEFAULT '',
  `google_recaptcha_site_key` varchar(255) NOT NULL DEFAULT '',
  `google_recaptcha_secret_key` varchar(255) NOT NULL DEFAULT '',
  `facebook_pixel_id` varchar(255) NOT NULL DEFAULT '',
  `whatsapp_number` varchar(30) NOT NULL DEFAULT '',
  `youtube_channel` varchar(255) NOT NULL DEFAULT '',
  `instagram_url` varchar(255) NOT NULL DEFAULT '',
  `linkedin_url` varchar(255) NOT NULL DEFAULT '',
  `telegram_url` varchar(255) NOT NULL DEFAULT '',
  `chatbot_status` varchar(20) NOT NULL DEFAULT 'disabled',
  `recaptcha_status` varchar(20) NOT NULL DEFAULT 'enabled',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `labor_pricing`
--

CREATE TABLE `labor_pricing` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `work_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `rate_per_sqft` decimal(12,2) NOT NULL,
  `min_area_sqft` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quality_grade` varchar(30) NOT NULL DEFAULT 'standard',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `labor_pricing`
--

INSERT INTO `labor_pricing` (`id`, `work_type`, `description`, `rate_per_sqft`, `min_area_sqft`, `quality_grade`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Foundation', 'Excavation and foundation work', 150.00, 0.00, 'standard', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'Structural', 'RCC columns, beams and slab', 350.00, 0.00, 'standard', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 'Masonry', 'Blockwork and brickwork', 180.00, 0.00, 'standard', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(4, 'Plumbing', 'Pipe fitting and fixtures', 160.00, 0.00, 'standard', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(5, 'Electrical', 'Wiring and switchboard installation', 130.00, 0.00, 'standard', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(6, 'Painting', 'Putty, primer and paint application', 100.00, 0.00, 'standard', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(7, 'Luxury Finishing', 'Premium finish supervision', 260.00, 0.00, 'luxury', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `status_id` bigint(20) UNSIGNED DEFAULT 1,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `project_type` varchar(120) DEFAULT NULL,
  `project_location` varchar(255) DEFAULT NULL,
  `budget` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `source_detail` varchar(255) DEFAULT NULL,
  `lead_source` varchar(150) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'New',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `uuid`, `status_id`, `assigned_to`, `full_name`, `email`, `phone`, `location`, `project_type`, `project_location`, `budget`, `message`, `notes`, `source`, `source_detail`, `lead_source`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`, `deleted_by`) VALUES
(1, 'fdb210e3-5dc9-11f1-b171-4c0f3ee9b145', 2, 4, 'Sanjay Kumar', 'sanjay.kumar@example.com', '9880011122', 'Whitefield, Bengaluru', 'Villa Construction', 'Whitefield', '1.2 Cr - 1.5 Cr', 'Looking for a 4BHK villa with premium finishes.', NULL, 'website', 'contact_form', 'Website', 'New', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL, NULL),
(2, 'fdb27e06-5dc9-11f1-b171-4c0f3ee9b145', 3, 4, 'Meera Iyer', 'meera.iyer@example.com', '9880033344', 'Sarjapur Road, Bengaluru', 'Duplex House', 'Sarjapur Road', '80 L - 1 Cr', 'Need estimate for 2400 sqft duplex construction.', NULL, 'estimator', 'cost_estimator', 'Estimator', 'New', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL, NULL),
(3, 'fdb280eb-5dc9-11f1-b171-4c0f3ee9b145', 1, 4, 'Arjun Shetty', 'arjun.shetty@example.com', '9880055566', 'Mysuru', 'Farmhouse', 'Mysuru outskirts', '60 L - 80 L', 'Exploring package options for farmhouse construction.', NULL, 'phone', 'inbound_call', 'Phone', 'New', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL, NULL);

--
-- Triggers `leads`
--
DELIMITER $$
CREATE TRIGGER `tr_leads_status_sync_insert` BEFORE INSERT ON `leads` FOR EACH ROW BEGIN
  IF NEW.status IS NULL OR NEW.status = '' THEN
    SELECT `name` INTO @lead_status_name FROM `lead_statuses` WHERE `id` = COALESCE(NEW.status_id, 1) LIMIT 1;
    SET NEW.status = COALESCE(@lead_status_name, 'New');
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_leads_status_sync_update` BEFORE UPDATE ON `leads` FOR EACH ROW BEGIN
  IF NEW.status_id <> OLD.status_id OR NEW.status IS NULL OR NEW.status = '' THEN
    SELECT `name` INTO @lead_status_name_update FROM `lead_statuses` WHERE `id` = COALESCE(NEW.status_id, 1) LIMIT 1;
    SET NEW.status = COALESCE(@lead_status_name_update, NEW.status, 'New');
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lead_activities`
--

CREATE TABLE `lead_activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `activity_type` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_followups`
--

CREATE TABLE `lead_followups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `followup_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_statuses`
--

CREATE TABLE `lead_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_statuses`
--

INSERT INTO `lead_statuses` (`id`, `name`, `color`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'New', '#2196F3', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'Contacted', '#FF9800', 2, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 'Qualified', '#9C27B0', 3, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(4, 'Proposal Sent', '#00BCD4', 4, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(5, 'Negotiation', '#FFC107', 5, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(6, 'Won', '#4CAF50', 6, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(7, 'Lost', '#F44336', 7, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `location_zones`
--

CREATE TABLE `location_zones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `zone_name` varchar(120) NOT NULL,
  `multiplier` decimal(6,2) NOT NULL DEFAULT 1.00,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `location_zones`
--

INSERT INTO `location_zones` (`id`, `zone_name`, `multiplier`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Premium Bengaluru Core', 1.22, 'active', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'Urban Bengaluru', 1.12, 'active', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 'Suburban Bengaluru', 1.00, 'active', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(4, 'Tier-2 Karnataka City', 0.94, 'active', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(5, 'Rural Karnataka', 0.88, 'active', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `browser` text DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'failed',
  `attempted_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_logs`
--

CREATE TABLE `mail_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `recipient` varchar(150) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mail_logs`
--

INSERT INTO `mail_logs` (`id`, `recipient`, `subject`, `status`, `error_message`, `ip_address`, `created_at`) VALUES
(1, 'admin@kvnconstruction.com', 'Seed database ready', 'success', NULL, '127.0.0.1', '2026-06-01 14:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `material_pricing`
--

CREATE TABLE `material_pricing` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `quality_grade` varchar(30) NOT NULL DEFAULT 'standard',
  `supplier` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_pricing`
--

INSERT INTO `material_pricing` (`id`, `material_name`, `category`, `unit`, `unit_price`, `quality_grade`, `supplier`, `brand`, `last_updated`, `is_active`, `status`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Cement OPC 53', 'Structural', 'Bag 50kg', 420.00, 'standard', 'Local distributor', NULL, '2026-06-01 20:25:55', 1, 'active', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'Steel Fe500D', 'Structural', 'Quintal', 6200.00, 'standard', 'JSW dealer', NULL, '2026-06-01 20:25:55', 1, 'active', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 'River Sand', 'Masonry', 'Cubic Meter', 2900.00, 'standard', 'Approved quarry', NULL, '2026-06-01 20:25:55', 1, 'active', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(4, 'Vitrified Tile', 'Flooring', 'Sq Ft', 75.00, 'standard', 'Kajaria dealer', NULL, '2026-06-01 20:25:55', 1, 'active', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(5, 'Premium Paint', 'Finishing', 'Liter', 280.00, 'premium', 'Asian Paints dealer', NULL, '2026-06-01 20:25:55', 1, 'active', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(6, 'Smart Switch Module', 'Electrical', 'Piece', 850.00, 'luxury', 'Automation partner', NULL, '2026-06-01 20:25:55', 1, 'active', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_derivatives`
--

CREATE TABLE `media_derivatives` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `media_id` bigint(20) UNSIGNED NOT NULL,
  `derivative_type` varchar(100) NOT NULL,
  `derivative_path` varchar(255) NOT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_library`
--

CREATE TABLE `media_library` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `otp_code` varchar(255) NOT NULL,
  `otp_type` varchar(50) NOT NULL,
  `attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `resend_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `last_sent_at` datetime DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp_attempts`
--

CREATE TABLE `otp_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `package_features`
--

CREATE TABLE `package_features` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `feature_name` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `package_features`
--

INSERT INTO `package_features` (`id`, `package_id`, `feature_name`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'RCC framed structure', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 1, 'Standard vitrified tiles', 2, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 1, 'Basic plumbing and electrical', 3, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(4, 2, 'Premium tiles and sanitaryware', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(5, 2, 'Branded electrical fixtures', 2, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(6, 2, 'Modular kitchen allowance', 3, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(7, 3, 'Luxury flooring and facade', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(8, 3, 'Smart home wiring', 2, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(9, 3, 'Designer consultation', 3, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `package_specifications`
--

CREATE TABLE `package_specifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `category` varchar(100) NOT NULL,
  `specification_name` varchar(255) NOT NULL,
  `specification_value` varchar(255) DEFAULT NULL,
  `is_included` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `package_specifications`
--

INSERT INTO `package_specifications` (`id`, `package_id`, `category`, `specification_name`, `specification_value`, `is_included`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Structure', 'Concrete Grade', 'M20/M25 as per design', 1, 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 2, 'Flooring', 'Tile Range', 'Premium vitrified tiles', 1, 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 3, 'Automation', 'Smart Wiring', 'Lighting, security and network ready', 1, 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_history`
--

CREATE TABLE `password_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_receipts`
--

CREATE TABLE `payment_receipts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `receipt_number` varchar(100) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `payment_method` varchar(100) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `payment_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `transaction_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `permission_key` varchar(150) NOT NULL,
  `permission_name` varchar(150) NOT NULL,
  `module` varchar(80) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `uuid`, `permission_key`, `permission_name`, `module`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fda6e2f3-5dc9-11f1-b171-4c0f3ee9b145', 'dashboard.view', 'View dashboard', 'core', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'fda71cbe-5dc9-11f1-b171-4c0f3ee9b145', 'users.manage', 'Manage users', 'rbac', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 'fda71d5f-5dc9-11f1-b171-4c0f3ee9b145', 'leads.manage', 'Manage leads', 'crm', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(4, 'fda71d89-5dc9-11f1-b171-4c0f3ee9b145', 'projects.manage', 'Manage projects', 'projects', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(5, 'fda71db2-5dc9-11f1-b171-4c0f3ee9b145', 'quotations.manage', 'Manage quotations', 'quotations', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(6, 'fda71dd6-5dc9-11f1-b171-4c0f3ee9b145', 'estimators.manage', 'Manage estimator engine', 'estimator', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(7, 'fda71df7-5dc9-11f1-b171-4c0f3ee9b145', 'cms.manage', 'Manage CMS', 'cms', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(8, 'fda71e18-5dc9-11f1-b171-4c0f3ee9b145', 'seo.manage', 'Manage SEO', 'seo', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(9, 'fda71e3d-5dc9-11f1-b171-4c0f3ee9b145', 'media.manage', 'Manage media', 'cms', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(10, 'fda71e5e-5dc9-11f1-b171-4c0f3ee9b145', 'security.view', 'View security logs', 'security', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(11, 'fda71e7d-5dc9-11f1-b171-4c0f3ee9b145', 'reports.view', 'View reports', 'reports', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `portfolio`
--

CREATE TABLE `portfolio` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `project_type` varchar(120) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `client_name` varchar(150) DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `area_sqft` decimal(12,2) DEFAULT NULL,
  `completion_year` varchar(20) DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `gallery` longtext DEFAULT NULL,
  `project_status` varchar(50) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `seo_keywords` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `portfolio`
--

INSERT INTO `portfolio` (`id`, `uuid`, `title`, `slug`, `short_description`, `description`, `project_type`, `location`, `client_name`, `budget`, `area_sqft`, `completion_year`, `featured_image`, `gallery`, `project_status`, `status`, `is_featured`, `display_order`, `sort_order`, `seo_title`, `seo_description`, `seo_keywords`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fdbc905b-5dc9-11f1-b171-4c0f3ee9b145', 'Whitefield Premium Villa', 'whitefield-premium-villa', 'Luxury 4BHK villa with smart-home readiness.', 'A premium villa project with modern facade, open-plan living and landscaped sit-out.', 'Villa', 'Whitefield, Bengaluru', 'Sanjay Kumar', 14500000.00, 3600.00, '2027', 'portfolio/whitefield-villa.jpg', NULL, 'Ongoing', 'active', 1, 1, 1, 'Whitefield Premium Villa Project', 'Premium villa construction project in Whitefield.', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'fdbd2c40-5dc9-11f1-b171-4c0f3ee9b145', 'Indiranagar Duplex Remodel', 'indiranagar-duplex-remodel', 'Contemporary duplex renovation.', 'Complete interior and facade remodel for an existing duplex residence.', 'Renovation', 'Indiranagar, Bengaluru', 'Private Client', 4200000.00, 2200.00, '2025', 'portfolio/indiranagar-duplex.jpg', NULL, 'Completed', 'active', 1, 2, 2, 'Indiranagar Duplex Remodel', 'Duplex remodeling project by KVN Construction.', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `portfolio_projects`
--

CREATE TABLE `portfolio_projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `project_type` varchar(120) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `client_name` varchar(150) DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `area_sqft` decimal(12,2) DEFAULT NULL,
  `completion_year` varchar(20) DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `gallery` longtext DEFAULT NULL,
  `project_status` varchar(50) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `seo_keywords` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `portfolio_projects`
--

INSERT INTO `portfolio_projects` (`id`, `uuid`, `title`, `slug`, `short_description`, `description`, `project_type`, `location`, `client_name`, `budget`, `area_sqft`, `completion_year`, `featured_image`, `gallery`, `project_status`, `status`, `is_featured`, `display_order`, `sort_order`, `seo_title`, `seo_description`, `seo_keywords`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fdbc905b-5dc9-11f1-b171-4c0f3ee9b145', 'Whitefield Premium Villa', 'whitefield-premium-villa', 'Luxury 4BHK villa with smart-home readiness.', 'A premium villa project with modern facade, open-plan living and landscaped sit-out.', 'Villa', 'Whitefield, Bengaluru', 'Sanjay Kumar', 14500000.00, 3600.00, '2027', 'portfolio/whitefield-villa.jpg', NULL, 'Ongoing', 'active', 1, 1, 1, 'Whitefield Premium Villa Project', 'Premium villa construction project in Whitefield.', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'fdbd2c40-5dc9-11f1-b171-4c0f3ee9b145', 'Indiranagar Duplex Remodel', 'indiranagar-duplex-remodel', 'Contemporary duplex renovation.', 'Complete interior and facade remodel for an existing duplex residence.', 'Renovation', 'Indiranagar, Bengaluru', 'Private Client', 4200000.00, 2200.00, '2025', 'portfolio/indiranagar-duplex.jpg', NULL, 'Completed', 'active', 1, 2, 2, 'Indiranagar Duplex Remodel', 'Duplex remodeling project by KVN Construction.', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED DEFAULT 1,
  `project_code` varchar(60) DEFAULT NULL,
  `project_name` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `client_name` varchar(150) DEFAULT NULL,
  `project_type` varchar(120) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `site_address` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `expected_end_date` date DEFAULT NULL,
  `expected_completion` date DEFAULT NULL,
  `progress` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'Planning',
  `site_engineer` varchar(150) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `uuid`, `client_id`, `client_user_id`, `lead_id`, `status_id`, `project_code`, `project_name`, `name`, `client_name`, `project_type`, `location`, `site_address`, `description`, `budget`, `start_date`, `end_date`, `expected_end_date`, `expected_completion`, `progress`, `status`, `site_engineer`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fdb3f0e0-5dc9-11f1-b171-4c0f3ee9b145', 2, 2, 1, 2, 'KVN-2026-001', 'Whitefield Premium Villa', 'Whitefield Premium Villa', 'Sanjay Kumar', 'Villa Construction', 'Whitefield', 'Plot 18, Whitefield, Bengaluru', NULL, 14500000.00, '2026-05-15', NULL, '2027-03-15', NULL, 18, 'Foundation', 'Rohit Menon', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'fdb46eee-5dc9-11f1-b171-4c0f3ee9b145', 2, 2, 2, 1, 'KVN-2026-002', 'Sarjapur Duplex Residence', 'Sarjapur Duplex Residence', 'Meera Iyer', 'Duplex House', 'Sarjapur Road', 'Site 22, Sarjapur Road, Bengaluru', NULL, 9800000.00, '2026-07-01', NULL, '2027-02-28', NULL, 5, 'Planning', 'Rohit Menon', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

--
-- Triggers `projects`
--
DELIMITER $$
CREATE TRIGGER `tr_projects_alias_insert` BEFORE INSERT ON `projects` FOR EACH ROW BEGIN
  IF NEW.name IS NULL OR NEW.name = '' THEN
    SET NEW.name = NEW.project_name;
  END IF;
  IF NEW.project_name IS NULL OR NEW.project_name = '' THEN
    SET NEW.project_name = NEW.name;
  END IF;
  IF NEW.client_user_id IS NULL THEN
    SET NEW.client_user_id = NEW.client_id;
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_projects_alias_update` BEFORE UPDATE ON `projects` FOR EACH ROW BEGIN
  IF NEW.name IS NULL OR NEW.name = '' THEN
    SET NEW.name = NEW.project_name;
  END IF;
  IF NEW.project_name IS NULL OR NEW.project_name = '' THEN
    SET NEW.project_name = NEW.name;
  END IF;
  IF NEW.client_user_id IS NULL THEN
    SET NEW.client_user_id = NEW.client_id;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `project_files`
--

CREATE TABLE `project_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_gallery`
--

CREATE TABLE `project_gallery` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_milestones`
--

CREATE TABLE `project_milestones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `milestone_title` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `progress` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_milestones`
--

INSERT INTO `project_milestones` (`id`, `project_id`, `client_project_id`, `milestone_title`, `title`, `description`, `due_date`, `completed_at`, `progress`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Foundation Completion', 'Foundation Completion', 'Excavation, footing and plinth beam completion.', '2026-06-21', NULL, 45, 'In Progress', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 1, 1, 'Ground Floor Slab', 'Ground Floor Slab', 'RCC slab casting for ground floor.', '2026-07-26', NULL, 0, 'Pending', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_payments`
--

CREATE TABLE `project_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_payments`
--

INSERT INTO `project_payments` (`id`, `project_id`, `amount`, `payment_date`, `payment_method`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 2500000.00, '2026-05-20', 'Bank Transfer', 'paid', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_reports`
--

CREATE TABLE `project_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED DEFAULT 1,
  `project_code` varchar(60) DEFAULT NULL,
  `project_name` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `client_name` varchar(150) DEFAULT NULL,
  `project_type` varchar(120) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `site_address` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `expected_end_date` date DEFAULT NULL,
  `expected_completion` date DEFAULT NULL,
  `progress` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'Planning',
  `site_engineer` varchar(150) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_schedules`
--

CREATE TABLE `project_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `timeline_title` varchar(255) DEFAULT NULL,
  `milestone_title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `timeline_date` date DEFAULT NULL,
  `progress` int(11) NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_statuses`
--

CREATE TABLE `project_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_statuses`
--

INSERT INTO `project_statuses` (`id`, `name`, `color`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Planning', '#607D8B', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'Foundation', '#795548', 2, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 'Structure', '#3F51B5', 3, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(4, 'Finishing', '#009688', 4, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(5, 'Completed', '#4CAF50', 5, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(6, 'On Hold', '#F44336', 6, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_tasks`
--

CREATE TABLE `project_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `task_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_timelines`
--

CREATE TABLE `project_timelines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `timeline_title` varchar(255) DEFAULT NULL,
  `milestone_title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `timeline_date` date DEFAULT NULL,
  `progress` int(11) NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_updates`
--

CREATE TABLE `project_updates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `update_date` date DEFAULT NULL,
  `progress` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_updates`
--

INSERT INTO `project_updates` (`id`, `project_id`, `client_project_id`, `title`, `description`, `update_date`, `progress`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Foundation steel work started', 'Steel binding and shuttering work has started after excavation approval.', '2026-06-01', 18, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `quotation_number` varchar(100) NOT NULL,
  `quotation_no` varchar(100) DEFAULT NULL,
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_name` varchar(150) DEFAULT NULL,
  `client_phone` varchar(20) DEFAULT NULL,
  `project_type` varchar(120) DEFAULT NULL,
  `project_location` varchar(255) DEFAULT NULL,
  `estimated_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gst` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'draft',
  `quotation_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `valid_until` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `uuid`, `quotation_number`, `quotation_no`, `lead_id`, `client_id`, `project_id`, `client_name`, `client_phone`, `project_type`, `project_location`, `estimated_cost`, `subtotal`, `gst`, `discount`, `total`, `status`, `quotation_status`, `valid_until`, `notes`, `terms`, `approved_by`, `approved_at`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fdb50977-5dc9-11f1-b171-4c0f3ee9b145', 'QTN-202606-0001', 'QTN-202606-0001', 1, 2, 1, 'Sanjay Kumar', '9880011122', 'Villa Construction', 'Whitefield', 14500000.00, 12288135.59, 2211864.41, 0.00, 14500000.00, 'sent', 'Pending', '2026-07-01', 'Premium villa construction quotation.', NULL, NULL, NULL, 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'fdb55edf-5dc9-11f1-b171-4c0f3ee9b145', 'QTN-202606-0002', 'QTN-202606-0002', 2, 2, 2, 'Meera Iyer', '9880033344', 'Duplex House', 'Sarjapur Road', 9800000.00, 8305084.75, 1494915.25, 0.00, 9800000.00, 'draft', 'Pending', '2026-07-01', 'Duplex construction estimate.', NULL, NULL, NULL, 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

--
-- Triggers `quotations`
--
DELIMITER $$
CREATE TRIGGER `tr_quotations_alias_insert` BEFORE INSERT ON `quotations` FOR EACH ROW BEGIN
  IF NEW.quotation_no IS NULL OR NEW.quotation_no = '' THEN
    SET NEW.quotation_no = NEW.quotation_number;
  END IF;
  IF NEW.quotation_number IS NULL OR NEW.quotation_number = '' THEN
    SET NEW.quotation_number = NEW.quotation_no;
  END IF;
  IF NEW.quotation_status IS NULL OR NEW.quotation_status = '' THEN
    SET NEW.quotation_status = NEW.status;
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_quotations_alias_update` BEFORE UPDATE ON `quotations` FOR EACH ROW BEGIN
  IF NEW.quotation_no IS NULL OR NEW.quotation_no = '' THEN
    SET NEW.quotation_no = NEW.quotation_number;
  END IF;
  IF NEW.quotation_number IS NULL OR NEW.quotation_number = '' THEN
    SET NEW.quotation_number = NEW.quotation_no;
  END IF;
  IF NEW.quotation_status IS NULL OR NEW.quotation_status = '' THEN
    SET NEW.quotation_status = NEW.status;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quotation_downloads`
--

CREATE TABLE `quotation_downloads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `document_title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` varchar(50) NOT NULL,
  `total_downloads` int(11) NOT NULL DEFAULT 0,
  `last_downloaded` datetime DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quotation_id` bigint(20) UNSIGNED NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT 1.00,
  `rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotation_items`
--

INSERT INTO `quotation_items` (`id`, `quotation_id`, `item_name`, `description`, `quantity`, `rate`, `amount`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Civil Construction', 'RCC structure, masonry and plastering', 3600.00, 2100.00, 7560000.00, 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 1, 'Premium Finishing', 'Flooring, painting, bathware and fixtures', 3600.00, 1250.00, 4500000.00, 2, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 1, 'Smart Home Readiness', 'Networking and automation provisions', 3600.00, 63.89, 230000.00, 3, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(4, 2, 'Duplex Construction', 'Standard construction package for duplex residence', 2400.00, 2450.00, 5880000.00, 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(5, 2, 'Interiors Allowance', 'Kitchen, wardrobes and fixture allowance', 1.00, 2425084.75, 2425084.75, 2, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quotation_versions`
--

CREATE TABLE `quotation_versions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quotation_id` bigint(20) UNSIGNED NOT NULL,
  `version_number` int(11) NOT NULL DEFAULT 1,
  `payload_json` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `route_name` varchar(255) DEFAULT NULL,
  `attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `blocked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `revenue_reports`
--

CREATE TABLE `revenue_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `project_type` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `role_key` varchar(100) NOT NULL,
  `role_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `uuid`, `role_key`, `role_name`, `description`, `is_system`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fd400af9-5dc9-11f1-b171-4c0f3ee9b145', 'admin', 'Administrator', 'Full platform access', 1, '2026-06-01 14:55:54', '2026-06-01 14:55:54', NULL),
(2, 'fd406c11-5dc9-11f1-b171-4c0f3ee9b145', 'client', 'Client', 'Client portal access', 1, '2026-06-01 14:55:54', '2026-06-01 14:55:54', NULL),
(3, 'fd406d9c-5dc9-11f1-b171-4c0f3ee9b145', 'employee', 'Employee', 'Operations and project staff', 1, '2026-06-01 14:55:54', '2026-06-01 14:55:54', NULL),
(4, 'fd406e14-5dc9-11f1-b171-4c0f3ee9b145', 'sales', 'Sales Executive', 'CRM and quotations access', 1, '2026-06-01 14:55:54', '2026-06-01 14:55:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 1, '2026-06-01 14:55:55'),
(2, 1, 2, '2026-06-01 14:55:55'),
(3, 1, 3, '2026-06-01 14:55:55'),
(4, 1, 4, '2026-06-01 14:55:55'),
(5, 1, 5, '2026-06-01 14:55:55'),
(6, 1, 6, '2026-06-01 14:55:55'),
(7, 1, 7, '2026-06-01 14:55:55'),
(8, 1, 8, '2026-06-01 14:55:55'),
(9, 1, 9, '2026-06-01 14:55:55'),
(10, 1, 10, '2026-06-01 14:55:55'),
(11, 1, 11, '2026-06-01 14:55:55'),
(16, 3, 1, '2026-06-01 14:55:55'),
(17, 3, 6, '2026-06-01 14:55:55'),
(18, 3, 3, '2026-06-01 14:55:55'),
(19, 3, 9, '2026-06-01 14:55:55'),
(20, 3, 4, '2026-06-01 14:55:55'),
(21, 3, 5, '2026-06-01 14:55:55'),
(22, 3, 11, '2026-06-01 14:55:55'),
(23, 4, 1, '2026-06-01 14:55:55'),
(24, 4, 6, '2026-06-01 14:55:55'),
(25, 4, 3, '2026-06-01 14:55:55'),
(26, 4, 5, '2026-06-01 14:55:55'),
(27, 4, 11, '2026-06-01 14:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `route_seo_meta`
--

CREATE TABLE `route_seo_meta` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `route_key` varchar(150) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `schema_json` longtext DEFAULT NULL,
  `robots_directive` varchar(100) NOT NULL DEFAULT 'index,follow',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `route_seo_meta`
--

INSERT INTO `route_seo_meta` (`id`, `route_key`, `meta_title`, `meta_description`, `canonical_url`, `og_image`, `schema_json`, `robots_directive`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'home', 'KVN Construction | Home Construction in Bengaluru', 'Plan, estimate and build homes with KVN Construction.', '/', '/assets/images/og-image.jpg', '{\"@context\":\"https://schema.org\",\"@type\":\"LocalBusiness\",\"name\":\"KVN Construction\"}', 'index,follow', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'estimator', 'Construction Cost Estimator | KVN Construction', 'Estimate home construction cost by area, floor count, package and location.', '/public/estimator.php', '/assets/images/og-image.jpg', NULL, 'index,follow', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 'services', 'Construction Services | KVN Construction', 'Residential, villa and renovation services in Karnataka.', '/public/services.php', '/assets/images/og-image.jpg', NULL, 'index,follow', '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `schema_migrations`
--

CREATE TABLE `schema_migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `migration_name` varchar(255) NOT NULL,
  `applied_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_level` varchar(30) NOT NULL DEFAULT 'info',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `event_details` text DEFAULT NULL,
  `request_uri` varchar(255) DEFAULT NULL,
  `request_method` varchar(10) DEFAULT NULL,
  `created_by_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `security_overview`
-- (See below for the actual view)
--
CREATE TABLE `security_overview` (
`critical_events` bigint(21)
,`warning_events` bigint(21)
,`info_events` bigint(21)
,`events_today` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `security_settings`
--

CREATE TABLE `security_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_username` varchar(100) NOT NULL,
  `admin_email` varchar(150) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `session_timeout` int(11) NOT NULL DEFAULT 30,
  `login_attempt_limit` int(11) NOT NULL DEFAULT 5,
  `two_factor_auth` varchar(20) NOT NULL DEFAULT 'disabled',
  `maintenance_lock` varchar(20) NOT NULL DEFAULT 'disabled',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `security_settings`
--

INSERT INTO `security_settings` (`id`, `admin_username`, `admin_email`, `admin_password`, `session_timeout`, `login_attempt_limit`, `two_factor_auth`, `maintenance_lock`, `updated_at`) VALUES
(1, 'admin', 'admin@kvnconstruction.com', '$2y$10$v7AM/X4dYq3FGoyO04Q6mennqf0lolFGdjOPYROOtgNNaCGmfG5LK', 30, 5, 'disabled', 'disabled', '2026-06-01 14:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `seo_settings`
--

CREATE TABLE `seo_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `page_name` varchar(100) DEFAULT NULL,
  `meta_title` varchar(255) NOT NULL DEFAULT '',
  `meta_description` text NOT NULL,
  `meta_keywords` text NOT NULL,
  `canonical_url` varchar(255) NOT NULL DEFAULT '',
  `og_title` varchar(255) NOT NULL DEFAULT '',
  `og_description` text NOT NULL,
  `og_image` varchar(255) NOT NULL DEFAULT '',
  `robots` varchar(100) NOT NULL DEFAULT 'index, follow',
  `robots_meta` varchar(100) NOT NULL DEFAULT 'index, follow',
  `google_analytics` text NOT NULL,
  `google_search_console` text NOT NULL,
  `facebook_meta_title` varchar(255) NOT NULL DEFAULT '',
  `facebook_meta_description` text NOT NULL,
  `twitter_meta_title` varchar(255) NOT NULL DEFAULT '',
  `twitter_meta_description` text NOT NULL,
  `sitemap_status` varchar(30) NOT NULL DEFAULT 'enabled',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seo_settings`
--

INSERT INTO `seo_settings` (`id`, `page_name`, `meta_title`, `meta_description`, `meta_keywords`, `canonical_url`, `og_title`, `og_description`, `og_image`, `robots`, `robots_meta`, `google_analytics`, `google_search_console`, `facebook_meta_title`, `facebook_meta_description`, `twitter_meta_title`, `twitter_meta_description`, `sitemap_status`, `updated_at`) VALUES
(1, 'global', 'KVN Construction', 'Construction services, cost estimation and project tracking.', 'construction,bengaluru,villa,home', 'https://kvnconstruction.com', 'KVN Construction', 'Build with clarity.', '/assets/images/og-image.jpg', 'index, follow', 'index, follow', '', '', 'KVN Construction', 'Build with clarity.', 'KVN Construction', 'Build with clarity.', 'enabled', '2026-06-01 14:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `service_name` varchar(150) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `slug` varchar(180) NOT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `seo_keywords` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `uuid`, `service_name`, `title`, `slug`, `short_description`, `description`, `icon`, `featured_image`, `featured`, `status`, `sort_order`, `seo_title`, `seo_description`, `seo_keywords`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fdb8ee39-5dc9-11f1-b171-4c0f3ee9b145', 'Residential Construction', 'Residential Construction', 'residential-construction', 'End-to-end home construction.', 'Design coordination, approvals support, material procurement, construction execution and handover.', 'home', NULL, 1, 'active', 1, 'Residential Construction in Bengaluru', 'Complete home construction services by KVN Construction.', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'fdb934a4-5dc9-11f1-b171-4c0f3ee9b145', 'Villa Construction', 'Villa Construction', 'villa-construction', 'Premium villa construction packages.', 'Luxury villa construction with premium materials, project tracking and quality checks.', 'building', NULL, 1, 'active', 2, 'Villa Construction Services', 'Premium villa construction services in Karnataka.', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 'fdb935ce-5dc9-11f1-b171-4c0f3ee9b145', 'Renovation and Remodeling', 'Renovation and Remodeling', 'renovation-remodeling', 'Upgrade existing spaces.', 'Interior upgrades, structural remodeling, bathroom renovation and facade improvements.', 'tool', NULL, 1, 'active', 3, 'Home Renovation Services', 'Renovation and remodeling services for homes and villas.', NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `session_history`
--

CREATE TABLE `session_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `session_token_hash` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `end_reason` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `setting_key` varchar(150) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `setting_group` varchar(80) NOT NULL DEFAULT 'general',
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `is_public`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'company_name', 'KVN Construction', 'company', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'company_tagline', 'Building Dreams, Delivering Excellence', 'company', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 'company_phone', '+91 98765 43210', 'company', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(4, 'company_email', 'info@kvnconstruction.com', 'company', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(5, 'company_address', '42 Brigade Road, Bengaluru, Karnataka 560001', 'company', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(6, 'currency', 'INR', 'finance', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(7, 'gst_percentage', '18', 'finance', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(8, 'facebook_url', 'https://facebook.com/kvnconstruction', 'social', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(9, 'instagram_url', 'https://instagram.com/kvnconstruction', 'social', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(10, 'linkedin_url', 'https://linkedin.com/company/kvnconstruction', 'social', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(11, 'security.login_rate_limit', '5', 'security', 0, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(12, 'security.otp_expiry_minutes', '5', 'security', 0, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'success',
  `provider_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_settings`
--

CREATE TABLE `sms_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sms_provider` varchar(100) NOT NULL,
  `api_key` varchar(255) NOT NULL DEFAULT '',
  `sender_id` varchar(100) NOT NULL DEFAULT '',
  `auth_token` varchar(255) NOT NULL DEFAULT '',
  `api_url` varchar(255) NOT NULL DEFAULT '',
  `admin_mobile` varchar(20) NOT NULL DEFAULT '',
  `sms_status` varchar(20) NOT NULL DEFAULT 'enabled',
  `notify_contact_form` varchar(10) NOT NULL DEFAULT 'yes',
  `notify_new_lead` varchar(10) NOT NULL DEFAULT 'yes',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_settings`
--

INSERT INTO `sms_settings` (`id`, `sms_provider`, `api_key`, `sender_id`, `auth_token`, `api_url`, `admin_mobile`, `sms_status`, `notify_contact_form`, `notify_new_lead`, `updated_at`) VALUES
(1, 'textlocal', '', 'KVNCON', '', '', '9876543210', 'disabled', 'yes', 'yes', '2026-06-01 14:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `smtp_settings`
--

CREATE TABLE `smtp_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `smtp_host` varchar(255) NOT NULL,
  `smtp_port` int(11) NOT NULL DEFAULT 587,
  `smtp_username` varchar(255) NOT NULL DEFAULT '',
  `smtp_password` varchar(255) NOT NULL DEFAULT '',
  `smtp_encryption` varchar(20) NOT NULL DEFAULT 'tls',
  `from_email` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `reply_to_email` varchar(255) NOT NULL,
  `mail_driver` varchar(20) NOT NULL DEFAULT 'smtp',
  `smtp_status` varchar(20) NOT NULL DEFAULT 'enabled',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `smtp_settings`
--

INSERT INTO `smtp_settings` (`id`, `smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `smtp_encryption`, `from_email`, `from_name`, `reply_to_email`, `mail_driver`, `smtp_status`, `updated_at`) VALUES
(1, 'smtp.example.com', 587, '', '', 'tls', 'noreply@kvnconstruction.com', 'KVN Construction', 'support@kvnconstruction.com', 'smtp', 'disabled', '2026-06-01 14:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `support_messages`
--

CREATE TABLE `support_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sender_type` varchar(50) NOT NULL DEFAULT 'client',
  `sender_id` bigint(20) UNSIGNED DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `ticket_number` varchar(100) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `priority` varchar(50) NOT NULL DEFAULT 'Medium',
  `status` varchar(50) NOT NULL DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suspicious_activity`
--

CREATE TABLE `suspicious_activity` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `activity_type` varchar(100) NOT NULL,
  `severity` varchar(30) NOT NULL DEFAULT 'medium',
  `details` text DEFAULT NULL,
  `resolved` tinyint(1) NOT NULL DEFAULT 0,
  `resolved_at` datetime DEFAULT NULL,
  `resolved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `suspicious_activity_view`
-- (See below for the actual view)
--
CREATE TABLE `suspicious_activity_view` (
`id` bigint(20) unsigned
,`user_id` bigint(20) unsigned
,`ip_address` varchar(45)
,`activity_type` varchar(100)
,`severity` varchar(30)
,`details` text
,`resolved` tinyint(1)
,`resolved_at` datetime
,`resolved_by` bigint(20) unsigned
,`created_at` timestamp
,`updated_at` timestamp
,`deleted_at` timestamp
,`user_name` varchar(150)
,`user_email` varchar(150)
);

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_name` varchar(150) NOT NULL,
  `client_location` varchar(150) DEFAULT NULL,
  `review` text NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `project_type` varchar(120) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `client_name`, `client_location`, `review`, `rating`, `project_type`, `image`, `video_url`, `featured`, `status`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Ananya Rao', 'Indiranagar', 'KVN Construction kept the project transparent from quotation to handover. The milestone updates were very helpful.', 5, 'Duplex Renovation', NULL, NULL, 1, 'active', 1, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'Sanjay Kumar', 'Whitefield', 'Professional team, clear costing and responsive site coordination.', 5, 'Villa Construction', NULL, NULL, 1, 'active', 2, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `testimonial_videos`
--

CREATE TABLE `testimonial_videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_name` varchar(150) NOT NULL,
  `title` varchar(255) NOT NULL,
  `video_url` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT uuid(),
  `full_name` varchar(150) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'client',
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `failed_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `failed_login_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `password_changed_at` datetime DEFAULT NULL,
  `last_password_change` datetime DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `phone_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL,
  `phone_verified_at` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_activity_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `last_login_user_agent` varchar(255) DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `last_user_agent` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `full_name`, `name`, `email`, `phone`, `password`, `role`, `status`, `avatar`, `remember_token`, `failed_attempts`, `failed_login_attempts`, `locked_until`, `password_changed_at`, `last_password_change`, `email_verified`, `phone_verified`, `email_verified_at`, `phone_verified_at`, `last_login`, `last_activity_at`, `last_login_ip`, `last_login_user_agent`, `last_ip`, `last_user_agent`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fdaacbdd-5dc9-11f1-b171-4c0f3ee9b145', 'KVN Admin', 'KVN Admin', 'admin@kvnconstruction.com', '9876543210', '$2y$10$v7AM/X4dYq3FGoyO04Q6mennqf0lolFGdjOPYROOtgNNaCGmfG5LK', 'admin', 'active', NULL, NULL, 0, 0, NULL, NULL, NULL, 1, 1, '2026-06-01 20:25:55', '2026-06-01 20:25:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(2, 'fdab142f-5dc9-11f1-b171-4c0f3ee9b145', 'Ananya Rao', 'Ananya Rao', 'ananya.rao@example.com', '9876500001', '$2y$10$v7AM/X4dYq3FGoyO04Q6mennqf0lolFGdjOPYROOtgNNaCGmfG5LK', 'client', 'active', NULL, NULL, 0, 0, NULL, NULL, NULL, 1, 1, '2026-06-01 20:25:55', '2026-06-01 20:25:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(3, 'fdab152c-5dc9-11f1-b171-4c0f3ee9b145', 'Rohit Menon', 'Rohit Menon', 'rohit.menon@example.com', '9876500002', '$2y$10$v7AM/X4dYq3FGoyO04Q6mennqf0lolFGdjOPYROOtgNNaCGmfG5LK', 'employee', 'active', NULL, NULL, 0, 0, NULL, NULL, NULL, 1, 1, '2026-06-01 20:25:55', '2026-06-01 20:25:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL),
(4, 'fdab159f-5dc9-11f1-b171-4c0f3ee9b145', 'Priya Nair', 'Priya Nair', 'priya.nair@example.com', '9876500003', '$2y$10$v7AM/X4dYq3FGoyO04Q6mennqf0lolFGdjOPYROOtgNNaCGmfG5LK', 'sales', 'active', NULL, NULL, 0, 0, NULL, NULL, NULL, 1, 1, '2026-06-01 20:25:55', '2026-06-01 20:25:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-01 14:55:55', '2026-06-01 14:55:55', NULL);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `tr_users_password_history` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
  IF OLD.password <> NEW.password THEN
    INSERT INTO `password_history` (`user_id`, `password_hash`, `created_at`)
    VALUES (OLD.id, OLD.password, NOW());

    INSERT INTO `audit_logs` (`user_id`, `action_type`, `action`, `description`, `entity_type`, `entity_id`, `old_values`, `new_values`, `created_at`)
    VALUES (NEW.id, 'password_changed', 'password_changed', 'User password changed', 'user', NEW.id, NULL, NULL, NOW());
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_users_soft_delete_audit` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
  IF OLD.deleted_at IS NULL AND NEW.deleted_at IS NOT NULL THEN
    INSERT INTO `audit_logs` (`user_id`, `action_type`, `action`, `description`, `entity_type`, `entity_id`, `old_values`, `created_at`)
    VALUES (NEW.id, 'user_soft_deleted', 'user_soft_deleted', 'User account soft deleted', 'user', NEW.id, CONCAT('email=', COALESCE(OLD.email, '')), NOW());
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_devices`
--

CREATE TABLE `user_devices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `device_hash` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `is_trusted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `created_at`) VALUES
(1, 1, 1, '2026-06-01 14:55:55'),
(2, 2, 2, '2026-06-01 14:55:55'),
(3, 3, 3, '2026-06-01 14:55:55'),
(4, 4, 4, '2026-06-01 14:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `fingerprint_hash` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `revoked_at` datetime DEFAULT NULL,
  `logout_reason` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video_categories`
--

CREATE TABLE `video_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_name` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure for view `active_sessions_view`
--
DROP TABLE IF EXISTS `active_sessions_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_sessions_view`  AS SELECT `u`.`id` AS `user_id`, `u`.`full_name` AS `full_name`, `u`.`email` AS `email`, `u`.`role` AS `role`, `us`.`session_token` AS `session_token`, `us`.`ip_address` AS `ip_address`, `us`.`user_agent` AS `user_agent`, `us`.`last_activity` AS `last_activity`, `us`.`created_at` AS `session_started`, timestampdiff(MINUTE,`us`.`last_activity`,current_timestamp()) AS `minutes_inactive` FROM (`user_sessions` `us` join `users` `u` on(`u`.`id` = `us`.`user_id`)) WHERE `us`.`is_active` = 1 AND `us`.`revoked_at` is null AND (`us`.`expires_at` is null OR `us`.`expires_at` > current_timestamp()) ;

-- --------------------------------------------------------

--
-- Structure for view `failed_login_attempts_view`
--
DROP TABLE IF EXISTS `failed_login_attempts_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `failed_login_attempts_view`  AS SELECT `login_attempts`.`ip_address` AS `ip_address`, `login_attempts`.`email` AS `email`, count(0) AS `attempt_count`, max(`login_attempts`.`created_at`) AS `last_attempt` FROM `login_attempts` WHERE `login_attempts`.`success` = 0 AND `login_attempts`.`created_at` > current_timestamp() - interval 24 hour GROUP BY `login_attempts`.`ip_address`, `login_attempts`.`email` HAVING count(0) >= 3 ;

-- --------------------------------------------------------

--
-- Structure for view `security_overview`
--
DROP TABLE IF EXISTS `security_overview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `security_overview`  AS SELECT count(case when `security_logs`.`event_level` = 'critical' then 1 end) AS `critical_events`, count(case when `security_logs`.`event_level` = 'warning' then 1 end) AS `warning_events`, count(case when `security_logs`.`event_level` = 'info' then 1 end) AS `info_events`, count(case when `security_logs`.`created_at` > current_timestamp() - interval 24 hour then 1 end) AS `events_today` FROM `security_logs` WHERE `security_logs`.`created_at` > current_timestamp() - interval 7 day ;

-- --------------------------------------------------------

--
-- Structure for view `suspicious_activity_view`
--
DROP TABLE IF EXISTS `suspicious_activity_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `suspicious_activity_view`  AS SELECT `sa`.`id` AS `id`, `sa`.`user_id` AS `user_id`, `sa`.`ip_address` AS `ip_address`, `sa`.`activity_type` AS `activity_type`, `sa`.`severity` AS `severity`, `sa`.`details` AS `details`, `sa`.`resolved` AS `resolved`, `sa`.`resolved_at` AS `resolved_at`, `sa`.`resolved_by` AS `resolved_by`, `sa`.`created_at` AS `created_at`, `sa`.`updated_at` AS `updated_at`, `sa`.`deleted_at` AS `deleted_at`, `u`.`full_name` AS `user_name`, `u`.`email` AS `user_email` FROM (`suspicious_activity` `sa` left join `users` `u` on(`u`.`id` = `sa`.`user_id`)) WHERE `sa`.`resolved` = 0 AND `sa`.`created_at` > current_timestamp() - interval 7 day ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_advantages`
--
ALTER TABLE `about_advantages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `about_page`
--
ALTER TABLE `about_page`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `about_process_steps`
--
ALTER TABLE `about_process_steps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `about_specifications`
--
ALTER TABLE `about_specifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_action_logs`
--
ALTER TABLE `admin_action_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_admin_action_logs_admin` (`admin_id`);

--
-- Indexes for table `analytics_events`
--
ALTER TABLE `analytics_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_logs_user_action` (`user_id`,`action_type`,`created_at`),
  ADD KEY `idx_audit_logs_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_audit_logs_created` (`created_at`);

--
-- Indexes for table `blocked_users`
--
ALTER TABLE `blocked_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_blog_posts_uuid` (`uuid`),
  ADD UNIQUE KEY `uq_blog_posts_slug` (`slug`),
  ADD KEY `idx_blogs_status_published` (`status`,`published_at`);
ALTER TABLE `blogs` ADD FULLTEXT KEY `ft_blogs_search` (`title`,`excerpt`,`content`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_blog_categories_slug` (`slug`);

--
-- Indexes for table `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_blog_comments_blog` (`blog_id`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_blog_posts_uuid` (`uuid`),
  ADD UNIQUE KEY `uq_blog_posts_slug` (`slug`),
  ADD KEY `idx_blog_posts_status_published` (`status`,`published_at`),
  ADD KEY `idx_blog_posts_category` (`category_id`),
  ADD KEY `fk_blog_posts_author` (`author_id`);
ALTER TABLE `blog_posts` ADD FULLTEXT KEY `ft_blog_posts_search` (`title`,`excerpt`,`content`);

--
-- Indexes for table `blog_tags`
--
ALTER TABLE `blog_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_blog_tags_slug` (`slug`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_clients_email` (`email`);

--
-- Indexes for table `client_agreements`
--
ALTER TABLE `client_agreements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_client_agreements_number` (`agreement_number`);

--
-- Indexes for table `client_documents`
--
ALTER TABLE `client_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_downloads`
--
ALTER TABLE `client_downloads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_feedback`
--
ALTER TABLE `client_feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_files`
--
ALTER TABLE `client_files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_media_library_uuid` (`uuid`);

--
-- Indexes for table `client_invoices`
--
ALTER TABLE `client_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_client_invoices_number` (`invoice_number`),
  ADD KEY `idx_client_invoices_client_status` (`client_id`,`payment_status`,`due_date`);

--
-- Indexes for table `client_messages`
--
ALTER TABLE `client_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client_messages_client_read` (`client_id`,`is_read`,`created_at`);

--
-- Indexes for table `client_notifications`
--
ALTER TABLE `client_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_payments`
--
ALTER TABLE `client_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client_payments_client_status` (`client_id`,`payment_status`,`created_at`);

--
-- Indexes for table `client_permits`
--
ALTER TABLE `client_permits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_client_permits_number` (`permit_number`);

--
-- Indexes for table `client_projects`
--
ALTER TABLE `client_projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_client_projects_code` (`project_code`),
  ADD KEY `idx_client_projects_client_status` (`client_id`,`status_id`),
  ADD KEY `idx_client_projects_dates` (`start_date`,`expected_end_date`),
  ADD KEY `fk_client_projects_client_user` (`client_user_id`),
  ADD KEY `fk_client_projects_status` (`status_id`),
  ADD KEY `fk_client_projects_quotation` (`quotation_id`);

--
-- Indexes for table `client_quotations`
--
ALTER TABLE `client_quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_client_quotations_number` (`quotation_number`);

--
-- Indexes for table `client_testimonials`
--
ALTER TABLE `client_testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_uploaded_images`
--
ALTER TABLE `client_uploaded_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_uploaded_videos`
--
ALTER TABLE `client_uploaded_videos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `construction_packages`
--
ALTER TABLE `construction_packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_construction_packages_uuid` (`uuid`),
  ADD UNIQUE KEY `uq_construction_packages_slug` (`slug`),
  ADD KEY `idx_construction_packages_status_price` (`status`,`price_per_sqft`),
  ADD KEY `idx_construction_packages_featured` (`is_featured`,`display_order`);
ALTER TABLE `construction_packages` ADD FULLTEXT KEY `ft_construction_packages_search` (`package_name`,`short_description`,`description`);

--
-- Indexes for table `contact_page`
--
ALTER TABLE `contact_page`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_page_features`
--
ALTER TABLE `contact_page_features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cta_blocks`
--
ALTER TABLE `cta_blocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cta_blocks_key` (`block_key`);

--
-- Indexes for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email_verification_tokens_hash` (`token_hash`),
  ADD KEY `fk_email_verification_tokens_user` (`user_id`);

--
-- Indexes for table `estimators`
--
ALTER TABLE `estimators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_estimator_requests_uuid` (`uuid`);

--
-- Indexes for table `estimator_calculation_log`
--
ALTER TABLE `estimator_calculation_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estimator_log_request` (`request_id`),
  ADD KEY `idx_estimator_log_package_created` (`package_id`,`created_at`),
  ADD KEY `fk_estimator_log_user` (`user_id`);

--
-- Indexes for table `estimator_leads`
--
ALTER TABLE `estimator_leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_estimator_requests_uuid` (`uuid`);

--
-- Indexes for table `estimator_materials`
--
ALTER TABLE `estimator_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `estimator_packages`
--
ALTER TABLE `estimator_packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_construction_packages_uuid` (`uuid`),
  ADD UNIQUE KEY `uq_construction_packages_slug` (`slug`);

--
-- Indexes for table `estimator_pricing`
--
ALTER TABLE `estimator_pricing`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `estimator_requests`
--
ALTER TABLE `estimator_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_estimator_requests_uuid` (`uuid`),
  ADD KEY `idx_estimator_requests_status_created` (`status`,`created_at`),
  ADD KEY `idx_estimator_requests_package` (`package_id`,`created_at`),
  ADD KEY `idx_estimator_requests_lead` (`lead_id`),
  ADD KEY `idx_estimator_requests_phone` (`phone`),
  ADD KEY `fk_estimator_requests_user` (`user_id`),
  ADD KEY `fk_estimator_requests_zone` (`location_zone_id`),
  ADD KEY `fk_estimator_requests_reviewed_by` (`reviewed_by`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_faqs_status_order` (`status`,`sort_order`);

--
-- Indexes for table `general_settings`
--
ALTER TABLE `general_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `homepage_content`
--
ALTER TABLE `homepage_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `homepage_sections`
--
ALTER TABLE `homepage_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_homepage_sections_key` (`section_key`);

--
-- Indexes for table `homepage_slides`
--
ALTER TABLE `homepage_slides`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `integration_settings`
--
ALTER TABLE `integration_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `labor_pricing`
--
ALTER TABLE `labor_pricing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_labor_pricing_type_grade` (`work_type`,`quality_grade`,`is_active`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_leads_uuid` (`uuid`),
  ADD KEY `idx_leads_status_created` (`status_id`,`created_at`),
  ADD KEY `idx_leads_assigned` (`assigned_to`,`created_at`),
  ADD KEY `idx_leads_phone` (`phone`),
  ADD KEY `idx_leads_source` (`source`),
  ADD KEY `idx_leads_deleted_at` (`deleted_at`),
  ADD KEY `fk_leads_created_by` (`created_by`),
  ADD KEY `fk_leads_deleted_by` (`deleted_by`);
ALTER TABLE `leads` ADD FULLTEXT KEY `ft_leads_search` (`full_name`,`email`,`phone`,`location`,`project_type`);

--
-- Indexes for table `lead_activities`
--
ALTER TABLE `lead_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lead_activities_lead` (`lead_id`,`created_at`),
  ADD KEY `fk_lead_activities_user` (`user_id`);

--
-- Indexes for table `lead_followups`
--
ALTER TABLE `lead_followups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lead_followups_lead` (`lead_id`,`created_at`),
  ADD KEY `idx_lead_followups_user` (`user_id`,`followup_date`);

--
-- Indexes for table `lead_statuses`
--
ALTER TABLE `lead_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `location_zones`
--
ALTER TABLE `location_zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_location_zones_name` (`zone_name`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_ip` (`ip_address`,`created_at`),
  ADD KEY `idx_login_attempts_email` (`email`,`created_at`),
  ADD KEY `idx_login_attempts_success` (`success`,`created_at`);

--
-- Indexes for table `mail_logs`
--
ALTER TABLE `mail_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `material_pricing`
--
ALTER TABLE `material_pricing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_material_pricing_category_grade` (`category`,`quality_grade`,`is_active`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_media_library_uuid` (`uuid`);

--
-- Indexes for table `media_derivatives`
--
ALTER TABLE `media_derivatives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_media_derivatives_media` (`media_id`);

--
-- Indexes for table `media_library`
--
ALTER TABLE `media_library`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_media_library_uuid` (`uuid`),
  ADD KEY `fk_media_library_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_otps_user_type` (`user_id`,`otp_type`,`is_used`),
  ADD KEY `idx_otps_phone_type` (`phone`,`otp_type`,`is_used`),
  ADD KEY `idx_otps_email_type` (`email`,`otp_type`,`is_used`),
  ADD KEY `idx_otps_expires` (`expires_at`);

--
-- Indexes for table `otp_attempts`
--
ALTER TABLE `otp_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_features`
--
ALTER TABLE `package_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_package_features_package` (`package_id`,`sort_order`);

--
-- Indexes for table `package_specifications`
--
ALTER TABLE `package_specifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_package_specs_package` (`package_id`,`category`);

--
-- Indexes for table `password_history`
--
ALTER TABLE `password_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_password_history_user` (`user_id`);

--
-- Indexes for table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_payment_receipts_number` (`receipt_number`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_payment_transactions_id` (`transaction_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_permissions_uuid` (`uuid`),
  ADD UNIQUE KEY `uq_permissions_key` (`permission_key`);

--
-- Indexes for table `portfolio`
--
ALTER TABLE `portfolio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_portfolio_projects_uuid` (`uuid`),
  ADD UNIQUE KEY `uq_portfolio_projects_slug` (`slug`);

--
-- Indexes for table `portfolio_projects`
--
ALTER TABLE `portfolio_projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_portfolio_projects_uuid` (`uuid`),
  ADD UNIQUE KEY `uq_portfolio_projects_slug` (`slug`),
  ADD KEY `idx_status_id` (`status`, `id`),
  ADD KEY `idx_portfolio_status_featured` (`status`,`is_featured`,`display_order`);
ALTER TABLE `portfolio_projects` ADD FULLTEXT KEY `ft_portfolio_search` (`title`,`short_description`,`description`,`location`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_projects_uuid` (`uuid`),
  ADD UNIQUE KEY `uq_projects_code` (`project_code`),
  ADD KEY `idx_projects_client_status` (`client_id`,`status_id`),
  ADD KEY `idx_projects_dates` (`start_date`,`expected_end_date`),
  ADD KEY `idx_projects_deleted_at` (`deleted_at`),
  ADD KEY `fk_projects_client_user` (`client_user_id`),
  ADD KEY `fk_projects_lead` (`lead_id`),
  ADD KEY `fk_projects_status` (`status_id`),
  ADD KEY `fk_projects_created_by` (`created_by`);
ALTER TABLE `projects` ADD FULLTEXT KEY `ft_projects_search` (`project_name`,`client_name`,`project_type`,`location`,`site_address`);

--
-- Indexes for table `project_files`
--
ALTER TABLE `project_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_project_files_project` (`project_id`);

--
-- Indexes for table `project_gallery`
--
ALTER TABLE `project_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_project_gallery_project` (`project_id`),
  ADD KEY `fk_project_gallery_client_project` (`client_project_id`);

--
-- Indexes for table `project_milestones`
--
ALTER TABLE `project_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_project_milestones_project` (`project_id`),
  ADD KEY `fk_project_milestones_client_project` (`client_project_id`);

--
-- Indexes for table `project_payments`
--
ALTER TABLE `project_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_project_payments_project` (`project_id`);

--
-- Indexes for table `project_reports`
--
ALTER TABLE `project_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_projects_uuid` (`uuid`),
  ADD UNIQUE KEY `uq_projects_code` (`project_code`);

--
-- Indexes for table `project_schedules`
--
ALTER TABLE `project_schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_statuses`
--
ALTER TABLE `project_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_project_tasks_project` (`project_id`),
  ADD KEY `fk_project_tasks_assigned_to` (`assigned_to`);

--
-- Indexes for table `project_timelines`
--
ALTER TABLE `project_timelines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_updates`
--
ALTER TABLE `project_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_project_updates_project` (`project_id`),
  ADD KEY `fk_project_updates_client_project` (`client_project_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_quotations_uuid` (`uuid`),
  ADD UNIQUE KEY `uq_quotations_number` (`quotation_number`),
  ADD KEY `idx_quotations_status_created` (`status`,`created_at`),
  ADD KEY `idx_quotations_lead` (`lead_id`),
  ADD KEY `idx_quotations_client` (`client_id`),
  ADD KEY `idx_quotations_project` (`project_id`),
  ADD KEY `idx_quotations_valid_until` (`valid_until`),
  ADD KEY `fk_quotations_approved_by` (`approved_by`),
  ADD KEY `fk_quotations_created_by` (`created_by`);

--
-- Indexes for table `quotation_downloads`
--
ALTER TABLE `quotation_downloads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quotation_items_quotation` (`quotation_id`,`sort_order`);

--
-- Indexes for table `quotation_versions`
--
ALTER TABLE `quotation_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_quotation_versions_quotation` (`quotation_id`),
  ADD KEY `fk_quotation_versions_created_by` (`created_by`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rate_limits_identifier_action` (`identifier`(120),`action_type`),
  ADD KEY `idx_rate_limits_blocked` (`blocked_until`),
  ADD KEY `idx_rate_limits_updated` (`updated_at`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_remember_tokens_hash` (`token_hash`),
  ADD KEY `fk_remember_tokens_user` (`user_id`);

--
-- Indexes for table `revenue_reports`
--
ALTER TABLE `revenue_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_roles_uuid` (`uuid`),
  ADD UNIQUE KEY `uq_roles_key` (`role_key`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_role_permission` (`role_id`,`permission_id`),
  ADD KEY `fk_role_permissions_permission` (`permission_id`);

--
-- Indexes for table `route_seo_meta`
--
ALTER TABLE `route_seo_meta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_route_seo_meta_key` (`route_key`),
  ADD KEY `idx_route_seo_meta_deleted` (`deleted_at`);

--
-- Indexes for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_schema_migrations_name` (`migration_name`);

--
-- Indexes for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_security_logs_user_event` (`user_id`,`event_type`,`created_at`),
  ADD KEY `idx_security_logs_level_created` (`event_level`,`created_at`),
  ADD KEY `idx_security_logs_ip` (`ip_address`,`created_at`);

--
-- Indexes for table `security_settings`
--
ALTER TABLE `security_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `seo_settings`
--
ALTER TABLE `seo_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_seo_settings_page_name` (`page_name`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_services_uuid` (`uuid`),
  ADD UNIQUE KEY `uq_services_slug` (`slug`),
  ADD KEY `idx_services_status_featured` (`status`,`featured`,`sort_order`);
ALTER TABLE `services` ADD FULLTEXT KEY `ft_services_search` (`service_name`,`short_description`,`description`);

--
-- Indexes for table `session_history`
--
ALTER TABLE `session_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_session_history_user` (`user_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_site_settings_key` (`setting_key`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sms_settings`
--
ALTER TABLE `sms_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `smtp_settings`
--
ALTER TABLE `smtp_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_support_messages_ticket` (`ticket_id`,`created_at`),
  ADD KEY `idx_support_messages_client` (`client_id`,`created_at`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_support_tickets_number` (`ticket_number`),
  ADD KEY `idx_support_tickets_client_status` (`client_id`,`status`,`created_at`);

--
-- Indexes for table `suspicious_activity`
--
ALTER TABLE `suspicious_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_suspicious_activity_user` (`user_id`),
  ADD KEY `fk_suspicious_activity_resolved_by` (`resolved_by`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_testimonials_status_featured` (`status`,`featured`,`sort_order`);

--
-- Indexes for table `testimonial_videos`
--
ALTER TABLE `testimonial_videos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_uuid` (`uuid`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD UNIQUE KEY `uq_users_phone` (`phone`),
  ADD KEY `idx_users_role_status` (`role`,`status`),
  ADD KEY `idx_users_last_login` (`last_login`),
  ADD KEY `idx_users_deleted_at` (`deleted_at`),
  ADD KEY `fk_users_created_by` (`created_by`);

--
-- Indexes for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_devices_user_hash` (`user_id`,`device_hash`),
  ADD KEY `idx_user_devices_trusted` (`user_id`,`is_trusted`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_role` (`user_id`,`role_id`),
  ADD KEY `fk_user_roles_role` (`role_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_sessions_token` (`session_token`),
  ADD KEY `idx_user_sessions_active` (`user_id`,`is_active`,`revoked_at`),
  ADD KEY `idx_user_sessions_remember` (`remember_token`),
  ADD KEY `idx_user_sessions_expires` (`expires_at`),
  ADD KEY `idx_user_sessions_activity` (`last_activity`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `video_categories`
--
ALTER TABLE `video_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_video_categories_slug` (`slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_advantages`
--
ALTER TABLE `about_advantages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `about_page`
--
ALTER TABLE `about_page`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `about_process_steps`
--
ALTER TABLE `about_process_steps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `about_specifications`
--
ALTER TABLE `about_specifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_action_logs`
--
ALTER TABLE `admin_action_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `analytics_events`
--
ALTER TABLE `analytics_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blocked_users`
--
ALTER TABLE `blocked_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blog_comments`
--
ALTER TABLE `blog_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blog_tags`
--
ALTER TABLE `blog_tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `client_agreements`
--
ALTER TABLE `client_agreements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_documents`
--
ALTER TABLE `client_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_downloads`
--
ALTER TABLE `client_downloads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_feedback`
--
ALTER TABLE `client_feedback`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_files`
--
ALTER TABLE `client_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_invoices`
--
ALTER TABLE `client_invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_messages`
--
ALTER TABLE `client_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_notifications`
--
ALTER TABLE `client_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_payments`
--
ALTER TABLE `client_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_permits`
--
ALTER TABLE `client_permits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_projects`
--
ALTER TABLE `client_projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `client_quotations`
--
ALTER TABLE `client_quotations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `client_testimonials`
--
ALTER TABLE `client_testimonials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_uploaded_images`
--
ALTER TABLE `client_uploaded_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_uploaded_videos`
--
ALTER TABLE `client_uploaded_videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `construction_packages`
--
ALTER TABLE `construction_packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_page`
--
ALTER TABLE `contact_page`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_page_features`
--
ALTER TABLE `contact_page_features`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cta_blocks`
--
ALTER TABLE `cta_blocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimators`
--
ALTER TABLE `estimators`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimator_calculation_log`
--
ALTER TABLE `estimator_calculation_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimator_leads`
--
ALTER TABLE `estimator_leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimator_materials`
--
ALTER TABLE `estimator_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimator_packages`
--
ALTER TABLE `estimator_packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimator_pricing`
--
ALTER TABLE `estimator_pricing`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimator_requests`
--
ALTER TABLE `estimator_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `general_settings`
--
ALTER TABLE `general_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `homepage_content`
--
ALTER TABLE `homepage_content`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `homepage_sections`
--
ALTER TABLE `homepage_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `homepage_slides`
--
ALTER TABLE `homepage_slides`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `integration_settings`
--
ALTER TABLE `integration_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `labor_pricing`
--
ALTER TABLE `labor_pricing`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lead_activities`
--
ALTER TABLE `lead_activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_followups`
--
ALTER TABLE `lead_followups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_statuses`
--
ALTER TABLE `lead_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `location_zones`
--
ALTER TABLE `location_zones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_logs`
--
ALTER TABLE `mail_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `material_pricing`
--
ALTER TABLE `material_pricing`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_derivatives`
--
ALTER TABLE `media_derivatives`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_library`
--
ALTER TABLE `media_library`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp_attempts`
--
ALTER TABLE `otp_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `package_features`
--
ALTER TABLE `package_features`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `package_specifications`
--
ALTER TABLE `package_specifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_history`
--
ALTER TABLE `password_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `portfolio`
--
ALTER TABLE `portfolio`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `portfolio_projects`
--
ALTER TABLE `portfolio_projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `project_files`
--
ALTER TABLE `project_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_gallery`
--
ALTER TABLE `project_gallery`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_milestones`
--
ALTER TABLE `project_milestones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `project_payments`
--
ALTER TABLE `project_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_reports`
--
ALTER TABLE `project_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_schedules`
--
ALTER TABLE `project_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_statuses`
--
ALTER TABLE `project_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `project_tasks`
--
ALTER TABLE `project_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_timelines`
--
ALTER TABLE `project_timelines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_updates`
--
ALTER TABLE `project_updates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `quotation_downloads`
--
ALTER TABLE `quotation_downloads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `quotation_versions`
--
ALTER TABLE `quotation_versions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `revenue_reports`
--
ALTER TABLE `revenue_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `route_seo_meta`
--
ALTER TABLE `route_seo_meta`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_settings`
--
ALTER TABLE `security_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `seo_settings`
--
ALTER TABLE `seo_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `session_history`
--
ALTER TABLE `session_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_settings`
--
ALTER TABLE `sms_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `smtp_settings`
--
ALTER TABLE `smtp_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suspicious_activity`
--
ALTER TABLE `suspicious_activity`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `testimonial_videos`
--
ALTER TABLE `testimonial_videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_devices`
--
ALTER TABLE `user_devices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `video_categories`
--
ALTER TABLE `video_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_action_logs`
--
ALTER TABLE `admin_action_logs`
  ADD CONSTRAINT `fk_admin_action_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD CONSTRAINT `fk_blog_comments_blog` FOREIGN KEY (`blog_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `fk_blog_posts_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_blog_posts_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `client_projects`
--
ALTER TABLE `client_projects`
  ADD CONSTRAINT `fk_client_projects_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_client_projects_client_user` FOREIGN KEY (`client_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_client_projects_quotation` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_client_projects_status` FOREIGN KEY (`status_id`) REFERENCES `project_statuses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD CONSTRAINT `fk_email_verification_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `estimator_calculation_log`
--
ALTER TABLE `estimator_calculation_log`
  ADD CONSTRAINT `fk_estimator_log_package` FOREIGN KEY (`package_id`) REFERENCES `construction_packages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estimator_log_request` FOREIGN KEY (`request_id`) REFERENCES `estimator_requests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estimator_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `estimator_requests`
--
ALTER TABLE `estimator_requests`
  ADD CONSTRAINT `fk_estimator_requests_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estimator_requests_package` FOREIGN KEY (`package_id`) REFERENCES `construction_packages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estimator_requests_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estimator_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estimator_requests_zone` FOREIGN KEY (`location_zone_id`) REFERENCES `location_zones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `fk_leads_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_leads_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_leads_deleted_by` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_leads_status` FOREIGN KEY (`status_id`) REFERENCES `lead_statuses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `lead_activities`
--
ALTER TABLE `lead_activities`
  ADD CONSTRAINT `fk_lead_activities_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lead_activities_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `lead_followups`
--
ALTER TABLE `lead_followups`
  ADD CONSTRAINT `fk_lead_followups_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lead_followups_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `media_derivatives`
--
ALTER TABLE `media_derivatives`
  ADD CONSTRAINT `fk_media_derivatives_media` FOREIGN KEY (`media_id`) REFERENCES `media_library` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `media_library`
--
ALTER TABLE `media_library`
  ADD CONSTRAINT `fk_media_library_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `otps`
--
ALTER TABLE `otps`
  ADD CONSTRAINT `fk_otps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `package_features`
--
ALTER TABLE `package_features`
  ADD CONSTRAINT `fk_package_features_package` FOREIGN KEY (`package_id`) REFERENCES `construction_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `package_specifications`
--
ALTER TABLE `package_specifications`
  ADD CONSTRAINT `fk_package_specs_package` FOREIGN KEY (`package_id`) REFERENCES `construction_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `password_history`
--
ALTER TABLE `password_history`
  ADD CONSTRAINT `fk_password_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_projects_client_user` FOREIGN KEY (`client_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_projects_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_projects_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_projects_status` FOREIGN KEY (`status_id`) REFERENCES `project_statuses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `project_files`
--
ALTER TABLE `project_files`
  ADD CONSTRAINT `fk_project_files_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project_gallery`
--
ALTER TABLE `project_gallery`
  ADD CONSTRAINT `fk_project_gallery_client_project` FOREIGN KEY (`client_project_id`) REFERENCES `client_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_project_gallery_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project_milestones`
--
ALTER TABLE `project_milestones`
  ADD CONSTRAINT `fk_project_milestones_client_project` FOREIGN KEY (`client_project_id`) REFERENCES `client_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_project_milestones_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project_payments`
--
ALTER TABLE `project_payments`
  ADD CONSTRAINT `fk_project_payments_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD CONSTRAINT `fk_project_tasks_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_project_tasks_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project_updates`
--
ALTER TABLE `project_updates`
  ADD CONSTRAINT `fk_project_updates_client_project` FOREIGN KEY (`client_project_id`) REFERENCES `client_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_project_updates_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `fk_quotations_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quotations_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quotations_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quotations_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quotations_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `fk_quotation_items_quotation` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quotation_versions`
--
ALTER TABLE `quotation_versions`
  ADD CONSTRAINT `fk_quotation_versions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quotation_versions_quotation` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `fk_remember_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD CONSTRAINT `fk_security_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `session_history`
--
ALTER TABLE `session_history`
  ADD CONSTRAINT `fk_session_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `suspicious_activity`
--
ALTER TABLE `suspicious_activity`
  ADD CONSTRAINT `fk_suspicious_activity_resolved_by` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_suspicious_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD CONSTRAINT `fk_user_devices_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
