-- Database Patch Script
-- Run this to update your database schema

-- 1. Projects Table
ALTER TABLE projects ADD COLUMN IF NOT EXISTS is_featured TINYINT(1) DEFAULT 0;
ALTER TABLE projects ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT NULL;

-- 2. Services Table
ALTER TABLE services ADD COLUMN IF NOT EXISTS price_from DECIMAL(10,2) DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS sort_order INT DEFAULT 0;
ALTER TABLE services ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

-- 3. Contact Messages Table
ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS service_required VARCHAR(100) DEFAULT NULL;
ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS subject VARCHAR(255) DEFAULT NULL;

-- 4. Site Settings keys
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES 
('header_logo', ''),
('profile_picture', ''),
('cv_file', ''),
('map_embed_url', ''),
('admin_notification_email', 'admin@example.com'),
('attendance_grace_period', '15'),
('attendance_shift_start', '09:00'),
('attendance_shift_end', '17:00');

-- 5. Attendance Records Table
CREATE TABLE IF NOT EXISTS attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL,
    employee_name VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    time_in TIME DEFAULT NULL,
    time_out TIME DEFAULT NULL,
    late_minutes INT DEFAULT 0,
    early_minutes INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'present',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_entry (employee_id, date)
);

-- 6. Attendance Employees Table
CREATE TABLE IF NOT EXISTS attendance_employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emp_code VARCHAR(50) NOT NULL UNIQUE,
    name_en VARCHAR(100) NOT NULL,
    name_ar VARCHAR(100) DEFAULT NULL,
    salary DECIMAL(10,2) DEFAULT 0,
    incentives DECIMAL(10,2) DEFAULT 0,
    shift_start TIME DEFAULT '09:00',
    shift_end TIME DEFAULT '17:00',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
