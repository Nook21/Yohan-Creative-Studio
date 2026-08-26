-- ============================================================
-- Amanuel Yohannes Portfolio CMS
-- Database Schema + Seed Data
-- Import via phpMyAdmin or: mysql -u root portfolio < database.sql
-- Then visit http://localhost/pro/setup.php to set your password
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `portfolio` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `portfolio`;

-- ============================================================
-- TABLES
-- ============================================================

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin') NOT NULL DEFAULT 'admin',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `short_description` varchar(300) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `year` year DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('published','draft') NOT NULL DEFAULT 'draft',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `fk_project_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_project_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `type` enum('image','video') NOT NULL DEFAULT 'image',
  `file_path` varchar(255) NOT NULL,
  `thumbnail_path` varchar(255) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `alt_text` varchar(200) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL COMMENT 'bytes',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `fk_media_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `about` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `biography` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin user
-- IMPORTANT: After import, visit /setup.php to set a real password.
-- Temporary password below is: changeme123
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Amanuel Yohannes', 'admin@portfolio.local', '$2y$12$LcMqUMoN9oqFqFqFqFqFquXvXvXvXvXvXvXvXvXvXvXvXvXvXvXu', 'admin');

-- ============================================================
-- Categories
-- ============================================================
INSERT INTO `categories` (`name`, `slug`, `display_order`) VALUES
('Magazines & Annual Reports', 'magazines-annual-reports', 1),
('Brochures', 'brochures', 2),
('Banners', 'banners', 3),
('Posters', 'posters', 4),
('Social Media', 'social-media', 5),
('Branding', 'branding', 6),
('Logo Design', 'logo-design', 7);

-- ============================================================
-- Clients
-- ============================================================
INSERT INTO `clients` (`name`, `logo`, `description`, `display_order`, `status`) VALUES
('Ethiopian Airlines', NULL, NULL, 1, 'active'),
('Royal Specialized Dental Clinic', NULL, NULL, 2, 'active'),
('Orbis International', NULL, NULL, 3, 'active'),
('Birhan International Bank', NULL, NULL, 4, 'active'),
('Abay Insurance', NULL, NULL, 5, 'active'),
('Expertise France', NULL, NULL, 6, 'active'),
('UN Women', NULL, NULL, 7, 'active'),
('African Union', NULL, NULL, 8, 'active'),
('Arada Moments', NULL, NULL, 9, 'active'),
('Pharo Foundation', NULL, NULL, 10, 'active'),
('Save the Children', NULL, NULL, 11, 'active'),
('Global Insurance Company', NULL, NULL, 12, 'active'),
('Danny Tech', NULL, NULL, 13, 'active'),
('Lilo Coffee', NULL, NULL, 14, 'active'),
('LSSYA', NULL, NULL, 15, 'active');

-- ============================================================
-- Projects
-- ============================================================
INSERT INTO `projects` (`category_id`, `client_id`, `title`, `slug`, `short_description`, `description`, `year`, `cover_image`, `featured`, `status`, `display_order`) VALUES
(1, 1,  'Ethiopian Airlines Magazine', 'ethiopian-airlines-magazine', 'In-flight magazine design for Ethiopian Airlines.', 'Full editorial design and layout for the Ethiopian Airlines in-flight magazine, covering feature articles, travel destinations, and brand storytelling.', 2024, NULL, 1, 'published', 1),
(1, 2,  'Royal Specialized Dental Clinic', 'royal-specialized-dental-clinic', 'Corporate profile design for Royal Specialized Dental Clinic.', 'Comprehensive corporate profile publication designed to communicate the clinic''s services, expertise, and brand identity to patients and partners.', 2024, NULL, 0, 'published', 2),
(1, 3,  'Orbis International Annual Report', 'orbis-international-annual-report', 'Annual report design for Orbis International.', 'Editorial design and layout for Orbis International''s annual report, presenting program impact, financials, and organizational achievements.', 2024, NULL, 1, 'published', 3),
(1, 4,  'Birhan International Bank Annual Report', 'birhan-international-bank-annual-report', 'Annual report design for Birhan International Bank.', 'Full annual report design covering financial highlights, board messages, and corporate governance for Birhan International Bank.', 2024, NULL, 0, 'published', 4),
(1, 5,  'Abay Insurance Annual Report', 'abay-insurance-annual-report', 'Annual report design for Abay Insurance.', 'Annual report publication design for Abay Insurance, communicating financial performance and company milestones.', 2023, NULL, 0, 'published', 5),
(1, 6,  'Expertise France Compendium', 'expertise-france-compendium', 'Compendium publication design for Expertise France.', 'Editorial layout and design for Expertise France''s program compendium, documenting development projects and impact across regions.', 2023, NULL, 0, 'published', 6),
(1, 7,  'UN Women Training Manual', 'un-women-training-manual', 'Training manual design for UN Women.', 'Layout and typographic design for a UN Women training manual, structured for clarity and accessibility across diverse audiences.', 2023, NULL, 0, 'published', 7),
(1, 8,  'African Union Case Study', 'african-union-case-study', 'Case study publication design for the African Union.', 'Editorial design for an African Union case study publication, presenting policy findings and program outcomes.', 2023, NULL, 0, 'published', 8),
(1, 9,  'Arada Moments Sponsorship Magazine', 'arada-moments-sponsorship-magazine', 'Sponsorship magazine design for Arada Moments.', 'Magazine design for Arada Moments'' sponsorship edition, combining editorial storytelling with event and brand promotion.', 2024, NULL, 0, 'published', 9),
(1, 10, 'Pharo Foundation Annual Report', 'pharo-foundation-annual-report', 'Annual report design for Pharo Foundation.', 'Annual report design for Pharo Foundation, highlighting education and development initiatives across East Africa.', 2024, NULL, 1, 'published', 10),
(2, 11, 'Save the Children / USAID Brochure', 'save-the-children-usaid-brochure', 'Brochure design for Save the Children in partnership with USAID.', 'Program brochure designed for Save the Children and USAID, communicating project goals, beneficiary stories, and impact data.', 2023, NULL, 0, 'published', 11),
(2, 12, 'Global Insurance Company Brochure', 'global-insurance-company-brochure', 'Corporate brochure design for Global Insurance Company.', 'Marketing brochure design for Global Insurance Company, presenting product offerings and brand positioning.', 2023, NULL, 0, 'published', 12),
(3, 9,  'Arada Moments Banners', 'arada-moments-banners', 'Event and promotional banner designs for Arada Moments.', 'Series of large-format event banners and promotional display materials designed for Arada Moments events.', 2024, NULL, 0, 'published', 13),
(4, 9,  'Arada Moments Posters', 'arada-moments-posters', 'Event poster designs for Arada Moments.', 'Creative poster series designed for Arada Moments events, combining bold typography with striking visual compositions.', 2024, NULL, 0, 'published', 14),
(5, NULL, 'Social Media Design', 'social-media-design', 'Social media graphic design assets for various clients.', 'Collection of social media graphic assets including post designs, story templates, and campaign visuals created for multiple clients.', 2024, NULL, 0, 'published', 15),
(6, 13, 'Danny Tech Branding', 'danny-tech-branding', 'Complete brand identity design for Danny Tech.', 'Full brand identity system for Danny Tech, including logo, color palette, typography, and brand application guidelines.', 2024, NULL, 1, 'published', 16),
(6, 14, 'Lilo Coffee Branding', 'lilo-coffee-branding', 'Brand identity design for Lilo Coffee.', 'Brand identity design for Lilo Coffee, capturing the warmth and character of the brand through visual language, packaging concepts, and brand guidelines.', 2024, NULL, 1, 'published', 17),
(6, 15, 'LSSYA Branding', 'lssya-branding', 'Brand identity design for LSSYA.', 'Complete brand identity design for LSSYA, establishing a cohesive visual system across all brand touchpoints.', 2023, NULL, 0, 'published', 18),
(7, NULL, 'Logo Design Collection', 'logo-design-collection', 'Collection of logo design work across various industries and clients.', 'A curated collection of logo design projects spanning multiple industries, demonstrating versatility in mark-making, typography, and visual identity.', 2024, NULL, 0, 'published', 19);

-- ============================================================
-- Services
-- ============================================================
INSERT INTO `services` (`title`, `description`, `icon`, `display_order`, `status`) VALUES
('Editorial & Publication Design', 'Magazine layouts, annual reports, corporate profiles, training manuals, and compendiums designed with precision and editorial clarity.', 'bi-journal-richtext', 1, 'active'),
('Brand Identity Design', 'Complete brand identity systems including logo design, color palettes, typography, and brand guidelines that communicate your vision.', 'bi-palette', 2, 'active'),
('Print & Marketing Collateral', 'Brochures, flyers, banners, and posters crafted to make a lasting impression across print and large-format media.', 'bi-printer', 3, 'active'),
('Social Media Design', 'Scroll-stopping social media graphics, post templates, story designs, and campaign visuals tailored for digital platforms.', 'bi-phone', 4, 'active'),
('Logo Design', 'Distinctive, memorable logos that capture the essence of your brand and stand the test of time.', 'bi-vector-pen', 5, 'active'),
('Corporate Communications', 'Annual reports, corporate profiles, and institutional publications that present your organization with authority and clarity.', 'bi-building', 6, 'active');

-- ============================================================
-- About
-- ============================================================
INSERT INTO `about` (`name`, `title`, `biography`, `profile_image`) VALUES
('Amanuel Yohannes', 'Graphic Designer',
'Amanuel Yohannes is a professional graphic designer with extensive experience in editorial design, brand identity, and print communications. His work spans a diverse range of clients including international organizations, financial institutions, NGOs, and emerging brands across Ethiopia and beyond.\n\nWith a meticulous eye for typography, layout, and visual storytelling, Amanuel brings clarity and creativity to every project — from large-scale annual reports and corporate publications to brand identities and social media campaigns.\n\nHis portfolio reflects a commitment to design that communicates with purpose, precision, and lasting visual impact.',
NULL);

-- ============================================================
-- Settings
-- ============================================================
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_title',       'Amanuel Yohannes — Graphic Designer'),
('site_description', 'Professional graphic designer specializing in editorial design, brand identity, and print communications.'),
('contact_email',    ''),
('contact_phone',    ''),
('contact_location', 'Addis Ababa, Ethiopia'),
('social_instagram', ''),
('social_linkedin',  ''),
('social_behance',   ''),
('social_twitter',   ''),
('copyright_text',   '© 2026 Amanuel Yohannes. All rights reserved.'),
('hero_title',       'Design That Speaks.'),
('hero_subtitle',    'Editorial design, brand identity, and print communications — crafted with precision and purpose.'),
('hero_cta_text',    'View Work'),
('show_clients',     '1'),
('show_about',       '1'),
('show_services',    '1'),
('favicon',          ''),
('profile_image',    ''),
('smtp_enabled',     '0'),
('smtp_host',        ''),
('smtp_port',        '587'),
('smtp_username',    ''),
('smtp_password',    ''),
('smtp_encryption',  'tls'),
('smtp_from_name',   'Amanuel Yohannes'),
('smtp_from_email',  '');
