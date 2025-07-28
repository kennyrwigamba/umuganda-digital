-- Umuganda Digital Database Schema
-- Create database and tables for the application

-- Create database
CREATE DATABASE IF NOT EXISTS umuganda_digital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE umuganda_digital;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    national_id VARCHAR(16) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    cell VARCHAR(100) NOT NULL,
    sector VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    role ENUM('admin', 'resident') DEFAULT 'resident',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    profile_picture VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    
    INDEX idx_email (email),
    INDEX idx_national_id (national_id),
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_location (cell, sector, district)
);

-- Umuganda events table
CREATE TABLE umuganda_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    cell VARCHAR(100),
    sector VARCHAR(100),
    district VARCHAR(100),
    province VARCHAR(100),
    max_participants INT NULL,
    status ENUM('scheduled', 'ongoing', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_event_date (event_date),
    INDEX idx_status (status),
    INDEX idx_location (cell, sector, district),
    INDEX idx_created_by (created_by)
);

-- Attendance table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    check_in_time TIMESTAMP NULL,
    check_out_time TIMESTAMP NULL,
    status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'absent',
    excuse_reason TEXT NULL,
    excuse_document VARCHAR(255) NULL,
    notes TEXT NULL,
    recorded_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES umuganda_events(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_event (user_id, event_id),
    INDEX idx_user_id (user_id),
    INDEX idx_event_id (event_id),
    INDEX idx_status (status),
    INDEX idx_check_in (check_in_time)
);

-- Fines table
CREATE TABLE fines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    attendance_id INT NULL,
    amount DECIMAL(10,2) NOT NULL,
    reason ENUM('absence', 'late_arrival', 'early_departure', 'other') NOT NULL,
    reason_description TEXT NULL,
    status ENUM('pending', 'paid', 'waived', 'disputed') DEFAULT 'pending',
    due_date DATE NULL,
    paid_date TIMESTAMP NULL,
    payment_method VARCHAR(50) NULL,
    payment_reference VARCHAR(100) NULL,
    waived_by INT NULL,
    waived_reason TEXT NULL,
    waived_date TIMESTAMP NULL,
    notes TEXT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES umuganda_events(id) ON DELETE CASCADE,
    FOREIGN KEY (attendance_id) REFERENCES attendance(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (waived_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_event_id (event_id),
    INDEX idx_status (status),
    INDEX idx_amount (amount),
    INDEX idx_due_date (due_date)
);

-- Community notices table
CREATE TABLE notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('general', 'urgent', 'event', 'fine_reminder', 'system') DEFAULT 'general',
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    target_audience ENUM('all', 'residents', 'admins', 'specific_location') DEFAULT 'all',
    cell VARCHAR(100) NULL,
    sector VARCHAR(100) NULL,
    district VARCHAR(100) NULL,
    province VARCHAR(100) NULL,
    publish_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date TIMESTAMP NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_type (type),
    INDEX idx_priority (priority),
    INDEX idx_status (status),
    INDEX idx_publish_date (publish_date),
    INDEX idx_target_location (cell, sector, district)
);

-- Notice views/reads tracking
CREATE TABLE notice_reads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notice_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (notice_id) REFERENCES notices(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_notice_user (notice_id, user_id),
    INDEX idx_notice_id (notice_id),
    INDEX idx_user_id (user_id)
);

-- System settings table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    description TEXT NULL,
    category VARCHAR(50) DEFAULT 'general',
    data_type ENUM('string', 'integer', 'decimal', 'boolean', 'json') DEFAULT 'string',
    is_editable BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_category (category),
    INDEX idx_key (setting_key)
);

-- Activity logs table
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id INT NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description, category, data_type) VALUES
('app_name', 'Umuganda Digital', 'Application name', 'general', 'string'),
('default_fine_amount', '5000', 'Default fine amount for absence (RWF)', 'fines', 'decimal'),
('late_fine_amount', '2000', 'Fine amount for late arrival (RWF)', 'fines', 'decimal'),
('fine_due_days', '30', 'Number of days to pay fine', 'fines', 'integer'),
('attendance_deadline', '10:00:00', 'Deadline time for attendance', 'attendance', 'string'),
('notification_enabled', 'true', 'Enable email notifications', 'notifications', 'boolean'),
('max_excuse_days', '2', 'Maximum days before event to submit excuse', 'attendance', 'integer');

-- Insert default admin user (password: admin123)
INSERT INTO users (
    national_id, 
    first_name, 
    last_name, 
    email, 
    phone, 
    password, 
    cell, 
    sector, 
    district, 
    province, 
    date_of_birth, 
    gender, 
    role, 
    status
) VALUES (
    '1234567890123456',
    'System',
    'Administrator',
    'admin@umuganda-digital.rw',
    '+250788000000',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
    'Kimisagara',
    'Nyarugenge',
    'Nyarugenge',
    'Kigali City',
    '1990-01-01',
    'male',
    'admin',
    'active'
);

-- Create sample Umuganda event for testing
INSERT INTO umuganda_events (
    title,
    description,
    event_date,
    start_time,
    end_time,
    location,
    cell,
    sector,
    district,
    province,
    status,
    created_by
) VALUES (
    'Monthly Umuganda - July 2025',
    'Community service activities including cleaning, tree planting, and infrastructure development.',
    '2025-07-26',
    '08:00:00',
    '11:00:00',
    'Community Center',
    'Kimisagara',
    'Nyarugenge',
    'Nyarugenge',
    'Kigali City',
    'scheduled',
    1
);
