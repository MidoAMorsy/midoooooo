-- Database Schema Update Script (Safe Version)
-- Part 1: Critical Bug Fixes - Schema Mismatches
-- Uses 'IF NOT EXISTS' to avoid errors if columns are already present.

-- --------------------------------------------------------
-- 1. Update `projects` table
-- --------------------------------------------------------
ALTER TABLE `projects` ADD COLUMN IF NOT EXISTS `is_featured` TINYINT(1) DEFAULT 0 AFTER `category`;
ALTER TABLE `projects` ADD COLUMN IF NOT EXISTS `image` VARCHAR(255) NULL AFTER `description`;

-- --------------------------------------------------------
-- 2. Update `services` table
-- --------------------------------------------------------
ALTER TABLE `services` ADD COLUMN IF NOT EXISTS `price_from` DECIMAL(10, 2) NULL AFTER `icon`;
ALTER TABLE `services` ADD COLUMN IF NOT EXISTS `sort_order` INT DEFAULT 0 AFTER `price_from`;
ALTER TABLE `services` ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) DEFAULT 1 AFTER `sort_order`;

-- --------------------------------------------------------
-- 3. Update `contact_messages` table
-- --------------------------------------------------------
ALTER TABLE `contact_messages` ADD COLUMN IF NOT EXISTS `service_required` VARCHAR(100) NULL AFTER `phone`;
ALTER TABLE `contact_messages` ADD COLUMN IF NOT EXISTS `subject` VARCHAR(255) NULL AFTER `service_required`;

-- --------------------------------------------------------
-- 4. Update `site_settings`
-- --------------------------------------------------------
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES 
('header_logo', NULL),
('profile_picture', NULL),
('cv_file', NULL),
('map_embed_url', NULL),
('map_location', NULL);
