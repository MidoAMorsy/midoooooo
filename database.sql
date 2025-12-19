-- =====================================================
-- Ahmed Ashraf Portfolio Website - Complete Database Schema
-- Database: mido_db
-- Created: 2025-12-15
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- =====================================================
-- MAIN WEBSITE TABLES
-- =====================================================

-- Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `role` ENUM('super_admin', 'admin', 'editor') DEFAULT 'admin',
    `is_active` TINYINT(1) DEFAULT 1,
    `reset_token` VARCHAR(100) DEFAULT NULL,
    `reset_token_expiry` DATETIME DEFAULT NULL,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pages Table
CREATE TABLE IF NOT EXISTS `pages` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title_en` VARCHAR(255) NOT NULL,
    `title_ar` VARCHAR(255) DEFAULT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `content_en` LONGTEXT,
    `content_ar` LONGTEXT,
    `meta_title_en` VARCHAR(255) DEFAULT NULL,
    `meta_title_ar` VARCHAR(255) DEFAULT NULL,
    `meta_description_en` TEXT DEFAULT NULL,
    `meta_description_ar` TEXT DEFAULT NULL,
    `meta_keywords` VARCHAR(500) DEFAULT NULL,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('published', 'draft') DEFAULT 'published',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name_en` VARCHAR(100) NOT NULL,
    `name_ar` VARCHAR(100) DEFAULT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description_en` TEXT DEFAULT NULL,
    `description_ar` TEXT DEFAULT NULL,
    `parent_id` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tags Table
CREATE TABLE IF NOT EXISTS `tags` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name_en` VARCHAR(50) NOT NULL,
    `name_ar` VARCHAR(50) DEFAULT NULL,
    `slug` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Posts Table
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title_en` VARCHAR(255) NOT NULL,
    `title_ar` VARCHAR(255) DEFAULT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `content_en` LONGTEXT,
    `content_ar` LONGTEXT,
    `excerpt_en` TEXT DEFAULT NULL,
    `excerpt_ar` TEXT DEFAULT NULL,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `category_id` INT(11) DEFAULT NULL,
    `author_id` INT(11) NOT NULL,
    `views` INT(11) DEFAULT 0,
    `status` ENUM('published', 'draft', 'pending') DEFAULT 'draft',
    `meta_title_en` VARCHAR(255) DEFAULT NULL,
    `meta_title_ar` VARCHAR(255) DEFAULT NULL,
    `meta_description_en` TEXT DEFAULT NULL,
    `meta_description_ar` TEXT DEFAULT NULL,
    `published_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `category_id` (`category_id`),
    KEY `author_id` (`author_id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Post Tags Pivot Table
CREATE TABLE IF NOT EXISTS `post_tags` (
    `post_id` INT(11) NOT NULL,
    `tag_id` INT(11) NOT NULL,
    PRIMARY KEY (`post_id`, `tag_id`),
    KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments Table
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `post_id` INT(11) NOT NULL,
    `parent_id` INT(11) DEFAULT NULL,
    `author_name` VARCHAR(100) NOT NULL,
    `author_email` VARCHAR(100) NOT NULL,
    `content` TEXT NOT NULL,
    `status` ENUM('approved', 'pending', 'spam') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `post_id` (`post_id`),
    KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Media Table
CREATE TABLE IF NOT EXISTS `media` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `filename` VARCHAR(255) NOT NULL,
    `filepath` VARCHAR(500) NOT NULL,
    `filetype` VARCHAR(50) NOT NULL,
    `filesize` INT(11) DEFAULT 0,
    `alt_text` VARCHAR(255) DEFAULT NULL,
    `caption` TEXT DEFAULT NULL,
    `folder` VARCHAR(100) DEFAULT 'general',
    `uploaded_by` INT(11) DEFAULT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `folder` (`folder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects Table
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title_en` VARCHAR(255) NOT NULL,
    `title_ar` VARCHAR(255) DEFAULT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description_en` TEXT,
    `description_ar` TEXT,
    `content_en` LONGTEXT,
    `content_ar` LONGTEXT,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `gallery` TEXT DEFAULT NULL,
    `technologies` VARCHAR(500) DEFAULT NULL,
    `demo_url` VARCHAR(255) DEFAULT NULL,
    `github_url` VARCHAR(255) DEFAULT NULL,
    `category` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('published', 'draft') DEFAULT 'published',
    `featured` TINYINT(1) DEFAULT 0,
    `sort_order` INT(11) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services Table
CREATE TABLE IF NOT EXISTS `services` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title_en` VARCHAR(255) NOT NULL,
    `title_ar` VARCHAR(255) DEFAULT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description_en` TEXT,
    `description_ar` TEXT,
    `content_en` LONGTEXT,
    `content_ar` LONGTEXT,
    `icon` VARCHAR(100) DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `price` DECIMAL(10,2) DEFAULT NULL,
    `price_type` ENUM('fixed', 'hourly', 'project', 'custom') DEFAULT 'custom',
    `features_en` TEXT DEFAULT NULL,
    `features_ar` TEXT DEFAULT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `featured` TINYINT(1) DEFAULT 0,
    `sort_order` INT(11) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact Messages Table
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `subject` VARCHAR(255) DEFAULT NULL,
    `message` TEXT NOT NULL,
    `service_id` INT(11) DEFAULT NULL,
    `status` ENUM('unread', 'read', 'replied', 'archived') DEFAULT 'unread',
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `replied_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEO Settings Table
CREATE TABLE IF NOT EXISTS `seo_settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `page_type` ENUM('page', 'post', 'project', 'service', 'category') NOT NULL,
    `page_id` INT(11) NOT NULL,
    `meta_title_en` VARCHAR(255) DEFAULT NULL,
    `meta_title_ar` VARCHAR(255) DEFAULT NULL,
    `meta_description_en` TEXT DEFAULT NULL,
    `meta_description_ar` TEXT DEFAULT NULL,
    `meta_keywords` VARCHAR(500) DEFAULT NULL,
    `og_title` VARCHAR(255) DEFAULT NULL,
    `og_description` TEXT DEFAULT NULL,
    `og_image` VARCHAR(255) DEFAULT NULL,
    `twitter_title` VARCHAR(255) DEFAULT NULL,
    `twitter_description` TEXT DEFAULT NULL,
    `twitter_image` VARCHAR(255) DEFAULT NULL,
    `canonical_url` VARCHAR(255) DEFAULT NULL,
    `robots` VARCHAR(100) DEFAULT 'index, follow',
    `schema_markup` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `page_type_id` (`page_type`, `page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site Settings Table
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT DEFAULT NULL,
    `setting_group` VARCHAR(50) DEFAULT 'general',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs Table
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `admin_id` INT(11) DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `entity_type` VARCHAR(50) DEFAULT NULL,
    `entity_id` INT(11) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `admin_id` (`admin_id`),
    KEY `action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Newsletter Subscribers Table
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `unsubscribed_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ATTENDANCE SYSTEM TABLES
-- =====================================================

-- Attendance Employees Table
CREATE TABLE IF NOT EXISTS `attendance_employees` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `emp_code` VARCHAR(50) NOT NULL,
    `name_en` VARCHAR(100) NOT NULL,
    `name_ar` VARCHAR(100) DEFAULT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `department` VARCHAR(100) DEFAULT NULL,
    `position` VARCHAR(100) DEFAULT NULL,
    `salary` DECIMAL(10,2) DEFAULT 0,
    `incentives` DECIMAL(10,2) DEFAULT 0,
    `shift_start` TIME DEFAULT '09:00:00',
    `shift_end` TIME DEFAULT '17:00:00',
    `grace_period` INT(11) DEFAULT 15,
    `leave_balance` DECIMAL(5,2) DEFAULT 21,
    `permission_balance` DECIMAL(5,2) DEFAULT 6,
    `weekend_days` VARCHAR(20) DEFAULT '5,6',
    `status` ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    `hire_date` DATE DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `emp_code` (`emp_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance Records Table
CREATE TABLE IF NOT EXISTS `attendance_records` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `emp_id` INT(11) NOT NULL,
    `date` DATE NOT NULL,
    `check_in` TIME DEFAULT NULL,
    `check_out` TIME DEFAULT NULL,
    `late_minutes` INT(11) DEFAULT 0,
    `early_departure` INT(11) DEFAULT 0,
    `overtime_minutes` INT(11) DEFAULT 0,
    `work_hours` DECIMAL(5,2) DEFAULT 0,
    `status` ENUM('present', 'absent', 'leave', 'holiday', 'weekend', 'excused') DEFAULT 'present',
    `deduction_type` ENUM('none', 'quarter', 'half', 'full') DEFAULT 'none',
    `deduction_amount` DECIMAL(10,2) DEFAULT 0,
    `notes` TEXT DEFAULT NULL,
    `is_manual` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `emp_date` (`emp_id`, `date`),
    KEY `date` (`date`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance Settings Table
CREATE TABLE IF NOT EXISTS `attendance_settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_name` VARCHAR(100) NOT NULL,
    `setting_value` TEXT DEFAULT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance Adjustments Table
CREATE TABLE IF NOT EXISTS `attendance_adjustments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `record_id` INT(11) NOT NULL,
    `adjusted_by` INT(11) DEFAULT NULL,
    `adjustment_type` VARCHAR(50) NOT NULL,
    `old_value` TEXT DEFAULT NULL,
    `new_value` TEXT DEFAULT NULL,
    `reason` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `record_id` (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance Import History
CREATE TABLE IF NOT EXISTS `attendance_imports` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `filename` VARCHAR(255) NOT NULL,
    `records_imported` INT(11) DEFAULT 0,
    `records_failed` INT(11) DEFAULT 0,
    `imported_by` INT(11) DEFAULT NULL,
    `import_date` DATE DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- QR GENERATOR TABLES
-- =====================================================

-- QR Codes Table
CREATE TABLE IF NOT EXISTS `qr_codes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `certificate_code` VARCHAR(100) NOT NULL,
    `student_id` VARCHAR(100) DEFAULT NULL,
    `qr_image_path` VARCHAR(255) DEFAULT NULL,
    `verification_url` VARCHAR(500) DEFAULT NULL,
    `color` VARCHAR(7) DEFAULT '#000000',
    `batch_id` INT(11) DEFAULT NULL,
    `generated_by` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `certificate_code` (`certificate_code`),
    KEY `batch_id` (`batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QR History Table
CREATE TABLE IF NOT EXISTS `qr_history` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `batch_name` VARCHAR(255) DEFAULT NULL,
    `generation_mode` ENUM('single', 'range', 'batch') DEFAULT 'single',
    `total_generated` INT(11) DEFAULT 0,
    `color_used` VARCHAR(7) DEFAULT '#000000',
    `generated_by` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CERTIFICATE CREATOR TABLES
-- =====================================================

-- Certificate Templates Table
CREATE TABLE IF NOT EXISTS `certificate_templates` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `template_name` VARCHAR(255) NOT NULL,
    `template_path` VARCHAR(255) NOT NULL,
    `thumbnail_path` VARCHAR(255) DEFAULT NULL,
    `width` INT(11) DEFAULT NULL,
    `height` INT(11) DEFAULT NULL,
    `text_settings` JSON DEFAULT NULL,
    `qr_settings` JSON DEFAULT NULL,
    `created_by` INT(11) DEFAULT NULL,
    `is_default` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generated Certificates Table
CREATE TABLE IF NOT EXISTS `generated_certificates` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `template_id` INT(11) NOT NULL,
    `recipient_name` VARCHAR(255) NOT NULL,
    `certificate_code` VARCHAR(100) DEFAULT NULL,
    `certificate_path` VARCHAR(255) DEFAULT NULL,
    `qr_code_id` INT(11) DEFAULT NULL,
    `additional_data` JSON DEFAULT NULL,
    `generated_by` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `template_id` (`template_id`),
    KEY `qr_code_id` (`qr_code_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Custom Fonts Table
CREATE TABLE IF NOT EXISTS `custom_fonts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `font_name` VARCHAR(100) NOT NULL,
    `font_family` VARCHAR(100) NOT NULL,
    `font_path` VARCHAR(255) NOT NULL,
    `font_type` ENUM('ttf', 'otf', 'woff', 'woff2') DEFAULT 'ttf',
    `is_arabic` TINYINT(1) DEFAULT 0,
    `uploaded_by` INT(11) DEFAULT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT DEFAULT DATA
-- =====================================================

-- Default Admin User (password: admin123)
INSERT INTO `admins` (`username`, `email`, `password`, `full_name`, `role`, `is_active`) VALUES
('admin', 'AhmedFrost@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmed Ashraf', 'super_admin', 1);

-- Default Site Settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name_en', 'Ahmed Ashraf', 'general'),
('site_name_ar', 'أحمد أشرف', 'general'),
('site_tagline_en', 'Marketing Professional & IT Expert', 'general'),
('site_tagline_ar', 'محترف تسويق وخبير تقنية المعلومات', 'general'),
('site_description_en', 'Professional portfolio of Ahmed Ashraf - Marketing specialist with IT background', 'general'),
('site_description_ar', 'الموقع الشخصي لأحمد أشرف - متخصص تسويق بخلفية تقنية', 'general'),
('contact_email', 'AhmedFrost@gmail.com', 'contact'),
('contact_phone', '+20-112-738-8682', 'contact'),
('contact_address_en', 'Cairo, Egypt', 'contact'),
('contact_address_ar', 'القاهرة، مصر', 'contact'),
('facebook_url', 'https://www.fb.com/anamidoxp', 'social'),
('twitter_url', '', 'social'),
('linkedin_url', '', 'social'),
('instagram_url', '', 'social'),
('youtube_url', '', 'social'),
('github_url', '', 'social'),
('google_analytics', '', 'analytics'),
('default_language', 'en', 'general'),
('maintenance_mode', '0', 'general');

-- Default Attendance Settings
INSERT INTO `attendance_settings` (`setting_name`, `setting_value`, `description`) VALUES
('work_start_time', '09:00', 'Default work start time'),
('work_end_time', '17:00', 'Default work end time'),
('grace_period', '15', 'Grace period in minutes'),
('weekend_days', '5,6', 'Weekend days (0=Sunday, 6=Saturday)'),
('late_threshold_quarter', '15', 'Minutes late for quarter day deduction'),
('late_threshold_half', '60', 'Minutes late for half day deduction'),
('late_threshold_full', '120', 'Minutes late for full day deduction'),
('permission_hours_monthly', '6', 'Monthly permission hours quota'),
('overtime_multiplier', '1.5', 'Overtime pay multiplier');

-- Default Categories
INSERT INTO `categories` (`name_en`, `name_ar`, `slug`, `description_en`, `description_ar`) VALUES
('Marketing', 'التسويق', 'marketing', 'Marketing tips and strategies', 'نصائح واستراتيجيات التسويق'),
('Technology', 'التكنولوجيا', 'technology', 'Technology news and tutorials', 'أخبار ودروس التكنولوجيا'),
('Business', 'الأعمال', 'business', 'Business insights and tips', 'رؤى ونصائح الأعمال'),
('Personal', 'شخصي', 'personal', 'Personal thoughts and experiences', 'أفكار وتجارب شخصية');

-- Default Services
INSERT INTO `services` (`title_en`, `title_ar`, `slug`, `description_en`, `description_ar`, `icon`, `price_type`, `features_en`, `features_ar`, `status`, `featured`, `sort_order`) VALUES
('Digital Marketing Strategy', 'استراتيجية التسويق الرقمي', 'digital-marketing-strategy', 'Comprehensive digital marketing strategy tailored to your business needs', 'استراتيجية تسويق رقمي شاملة مصممة خصيصاً لاحتياجات عملك', 'fas fa-bullseye', 'project', 'Market Analysis|Competitor Research|Strategy Development|KPI Setting|Monthly Reports', 'تحليل السوق|بحث المنافسين|تطوير الاستراتيجية|تحديد مؤشرات الأداء|تقارير شهرية', 'active', 1, 1),
('Social Media Management', 'إدارة وسائل التواصل الاجتماعي', 'social-media-management', 'Complete social media management across all platforms', 'إدارة كاملة لوسائل التواصل الاجتماعي عبر جميع المنصات', 'fas fa-share-alt', 'custom', 'Content Creation|Scheduling|Community Management|Analytics|Paid Campaigns', 'إنشاء المحتوى|الجدولة|إدارة المجتمع|التحليلات|الحملات المدفوعة', 'active', 1, 2),
('SEO Optimization', 'تحسين محركات البحث', 'seo-optimization', 'Improve your website ranking on search engines', 'تحسين ترتيب موقعك في محركات البحث', 'fas fa-search', 'project', 'Technical SEO|On-Page SEO|Off-Page SEO|Keyword Research|Monthly Reports', 'SEO التقني|SEO على الصفحة|SEO خارج الصفحة|بحث الكلمات المفتاحية|تقارير شهرية', 'active', 1, 3),
('Content Creation', 'إنشاء المحتوى', 'content-creation', 'Engaging content that resonates with your audience', 'محتوى جذاب يتفاعل مع جمهورك', 'fas fa-pen-fancy', 'custom', 'Blog Posts|Social Media Content|Video Scripts|Infographics|Email Newsletters', 'مقالات المدونة|محتوى التواصل الاجتماعي|نصوص الفيديو|الإنفوجرافيك|النشرات البريدية', 'active', 0, 4),
('Brand Development', 'تطوير العلامة التجارية', 'brand-development', 'Build a strong and memorable brand identity', 'بناء هوية علامة تجارية قوية ولا تُنسى', 'fas fa-palette', 'project', 'Brand Strategy|Visual Identity|Brand Guidelines|Voice & Tone|Brand Audit', 'استراتيجية العلامة|الهوية البصرية|دليل العلامة|الصوت والنبرة|تدقيق العلامة', 'active', 0, 5),
('IT Consulting', 'استشارات تقنية المعلومات', 'it-consulting', 'Technical consulting and IT infrastructure support', 'استشارات تقنية ودعم البنية التحتية لتقنية المعلومات', 'fas fa-laptop-code', 'hourly', 'Network Setup|System Administration|Security Audit|Technical Support|Training', 'إعداد الشبكات|إدارة الأنظمة|تدقيق الأمان|الدعم التقني|التدريب', 'active', 0, 6);

-- Default Projects (The Three Apps)
INSERT INTO `projects` (`title_en`, `title_ar`, `slug`, `description_en`, `description_ar`, `technologies`, `demo_url`, `category`, `status`, `featured`, `sort_order`) VALUES
('MidOo Smart Attendance System', 'نظام الحضور الذكي ميدو', 'smart-attendance-system', 'A comprehensive attendance management system with Excel import, smart calculations, and detailed reporting.', 'نظام شامل لإدارة الحضور مع استيراد الإكسل، الحسابات الذكية، والتقارير المفصلة.', 'PHP,MySQL,JavaScript,Excel', '/apps/attendance/', 'Web Application', 'published', 1, 1),
('Certificate QR Generator', 'مولد QR للشهادات', 'qr-generator', 'Generate QR codes for certificates with multiple generation modes and batch export capabilities.', 'إنشاء رموز QR للشهادات مع أوضاع إنشاء متعددة وإمكانيات التصدير الجماعي.', 'PHP,MySQL,JavaScript,QR Library', '/apps/qr-generator/', 'Web Application', 'published', 1, 2),
('Pro Certificate Creator', 'مُنشئ الشهادات الاحترافي', 'certificate-creator', 'Design and generate professional certificates with Arabic font support and bulk generation.', 'تصميم وإنشاء شهادات احترافية مع دعم الخطوط العربية والإنشاء الجماعي.', 'PHP,MySQL,JavaScript,GD Library', '/apps/certificate-creator/', 'Web Application', 'published', 1, 3);

-- Default Tags
INSERT INTO `tags` (`name_en`, `name_ar`, `slug`) VALUES
('Marketing', 'التسويق', 'marketing'),
('SEO', 'سيو', 'seo'),
('Web Development', 'تطوير الويب', 'web-development'),
('Social Media', 'وسائل التواصل', 'social-media'),
('Tips', 'نصائح', 'tips'),
('Tutorials', 'دروس', 'tutorials');

-- Sample Blog Posts
INSERT INTO `posts` (`title_en`, `title_ar`, `slug`, `content_en`, `content_ar`, `excerpt_en`, `excerpt_ar`, `category_id`, `author_id`, `status`, `published_at`) VALUES
('10 SEO Tips for Better Rankings', '10 نصائح سيو للترتيب الأفضل', '10-seo-tips-better-rankings', 'Search engine optimization is crucial for any website...\n\n1. Optimize your title tags\n2. Write quality content\n3. Build quality backlinks\n4. Improve page speed\n5. Use proper heading structure\n6. Optimize images\n7. Create internal links\n8. Make your site mobile-friendly\n9. Use schema markup\n10. Monitor your analytics', 'تحسين محركات البحث أمر بالغ الأهمية لأي موقع...\n\n1. تحسين عناوين الصفحات\n2. كتابة محتوى عالي الجودة\n3. بناء روابط خلفية جيدة\n4. تحسين سرعة الصفحة\n5. استخدام هيكل العناوين الصحيح\n6. تحسين الصور\n7. إنشاء روابط داخلية\n8. جعل الموقع متوافق مع الجوال\n9. استخدام Schema markup\n10. مراقبة التحليلات', 'Learn the essential SEO tips to improve your website ranking on search engines.', 'تعلم نصائح السيو الأساسية لتحسين ترتيب موقعك في محركات البحث.', 2, 1, 'published', NOW()),
('The Future of Digital Marketing', 'مستقبل التسويق الرقمي', 'future-digital-marketing', 'Digital marketing is constantly evolving. In this post, we explore the trends that will shape the industry in the coming years.\n\nAI and Machine Learning\nVoice Search Optimization\nVideo Marketing\nPersonalization\nInteractive Content', 'التسويق الرقمي يتطور باستمرار. في هذا المقال، نستكشف الاتجاهات التي ستشكل الصناعة في السنوات القادمة.\n\nالذكاء الاصطناعي\nتحسين البحث الصوتي\nتسويق الفيديو\nالتخصيص\nالمحتوى التفاعلي', 'Explore the trends shaping the future of digital marketing and how to stay ahead.', 'استكشف الاتجاهات التي تشكل مستقبل التسويق الرقمي وكيفية البقاء في المقدمة.', 1, 1, 'published', NOW()),
('Building Your Personal Brand', 'بناء علامتك التجارية الشخصية', 'building-personal-brand', 'In today is digital age, having a strong personal brand is essential for career growth and professional success.\n\nDefine your unique value proposition\nBe consistent across platforms\nShare valuable content regularly\nEngage with your audience\nBuild genuine relationships', 'في العصر الرقمي الحالي، يعد امتلاك علامة تجارية شخصية قوية أمرًا ضروريًا للنمو الوظيفي والنجاح المهني.\n\nحدد عرض القيمة الفريد الخاص بك\nكن متسقًا عبر المنصات\nشارك محتوى قيمًا بانتظام\nتفاعل مع جمهورك\nابنِ علاقات حقيقية', 'Learn how to build a strong personal brand that stands out in the digital landscape.', 'تعلم كيفية بناء علامة تجارية شخصية قوية تبرز في المشهد الرقمي.', 3, 1, 'published', NOW());

-- =====================================================
-- FOREIGN KEY CONSTRAINTS
-- =====================================================

ALTER TABLE `posts`
    ADD CONSTRAINT `fk_posts_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    ADD CONSTRAINT `fk_posts_author` FOREIGN KEY (`author_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE;

ALTER TABLE `post_tags`
    ADD CONSTRAINT `fk_post_tags_post` FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_post_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE;

ALTER TABLE `comments`
    ADD CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE;

ALTER TABLE `attendance_records`
    ADD CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`emp_id`) REFERENCES `attendance_employees`(`id`) ON DELETE CASCADE;

ALTER TABLE `attendance_adjustments`
    ADD CONSTRAINT `fk_adjustments_record` FOREIGN KEY (`record_id`) REFERENCES `attendance_records`(`id`) ON DELETE CASCADE;

ALTER TABLE `qr_codes`
    ADD CONSTRAINT `fk_qr_batch` FOREIGN KEY (`batch_id`) REFERENCES `qr_history`(`id`) ON DELETE SET NULL;

ALTER TABLE `generated_certificates`
    ADD CONSTRAINT `fk_cert_template` FOREIGN KEY (`template_id`) REFERENCES `certificate_templates`(`id`) ON DELETE CASCADE;

COMMIT;
