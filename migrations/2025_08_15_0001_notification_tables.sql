-- Notification System Tables Migration
-- Date: 2025-08-15
-- Description: Creates all tables needed for the notification system

-- Enable foreign key checks
SET foreign_key_checks = 1;

-- 1. Notifications table (main notification records)
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,                               -- null = broadcast notification
  title VARCHAR(150) NOT NULL,
  body TEXT NOT NULL,
  type VARCHAR(50) NOT NULL,                      -- e.g. attendance_recorded, event_reminder
  category ENUM('attendance','event','fine','payment','announcement','system','report','other') DEFAULT 'other',
  priority ENUM('low','normal','high','critical') DEFAULT 'normal',
  data LONGTEXT NULL CHECK (JSON_VALID(data)),    -- additional metadata as JSON
  status ENUM('pending','queued','sent','failed') DEFAULT 'pending',
  error_message TEXT NULL,
  sent_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_notifications_user (user_id, created_at),
  INDEX idx_notifications_type (type),
  INDEX idx_notifications_status (status),
  INDEX idx_notifications_category (category),
  INDEX idx_notifications_priority (priority),
  
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Notification channels table (per-channel delivery tracking)
CREATE TABLE IF NOT EXISTS notification_channels (
  id INT AUTO_INCREMENT PRIMARY KEY,
  notification_id INT NOT NULL,
  channel ENUM('email','push','inapp') NOT NULL,
  status ENUM('pending','sent','failed','skipped') DEFAULT 'pending',
  attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  last_error TEXT NULL,
  attempted_at TIMESTAMP NULL DEFAULT NULL,
  sent_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_nc_status (status),
  INDEX idx_nc_channel (channel),
  INDEX idx_nc_notification (notification_id),
  
  CONSTRAINT fk_nc_notification FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
  UNIQUE KEY unique_notification_channel (notification_id, channel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. User notification preferences
CREATE TABLE IF NOT EXISTS user_notification_preferences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  
  -- Attendance notifications
  attendance_email TINYINT(1) DEFAULT 1,
  attendance_push TINYINT(1) DEFAULT 1,
  attendance_inapp TINYINT(1) DEFAULT 1,
  
  -- Event notifications
  event_email TINYINT(1) DEFAULT 1,
  event_push TINYINT(1) DEFAULT 1,
  event_inapp TINYINT(1) DEFAULT 1,
  
  -- Fine notifications
  fine_email TINYINT(1) DEFAULT 1,
  fine_push TINYINT(1) DEFAULT 1,
  fine_inapp TINYINT(1) DEFAULT 1,
  
  -- Payment notifications
  payment_email TINYINT(1) DEFAULT 1,
  payment_push TINYINT(1) DEFAULT 1,
  payment_inapp TINYINT(1) DEFAULT 1,
  
  -- Announcement notifications
  announcement_email TINYINT(1) DEFAULT 1,
  announcement_push TINYINT(1) DEFAULT 1,
  announcement_inapp TINYINT(1) DEFAULT 1,
  
  -- System notifications
  system_email TINYINT(1) DEFAULT 1,
  system_push TINYINT(1) DEFAULT 1,
  system_inapp TINYINT(1) DEFAULT 1,
  
  -- Report notifications
  report_email TINYINT(1) DEFAULT 1,
  report_push TINYINT(1) DEFAULT 0,  -- default off for reports
  report_inapp TINYINT(1) DEFAULT 1,
  
  -- Other notifications
  other_email TINYINT(1) DEFAULT 1,
  other_push TINYINT(1) DEFAULT 1,
  other_inapp TINYINT(1) DEFAULT 1,
  
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_unp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_unp_updated (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Push subscriptions (for web push notifications)
CREATE TABLE IF NOT EXISTS push_subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  endpoint TEXT NOT NULL,
  endpoint_hash CHAR(64) NOT NULL,              -- SHA256(endpoint) for uniqueness
  p256dh VARCHAR(255) NOT NULL,                 -- encryption key
  auth VARCHAR(255) NOT NULL,                   -- auth secret
  user_agent VARCHAR(255) NULL,
  is_active TINYINT(1) DEFAULT 1,
  last_failure_at TIMESTAMP NULL DEFAULT NULL,
  revoked_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_ps_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_endpoint_hash (endpoint_hash),
  INDEX idx_ps_user (user_id, is_active),
  INDEX idx_ps_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Notification reads (track what user has read)
CREATE TABLE IF NOT EXISTS notification_reads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  notification_id INT NOT NULL,
  user_id INT NOT NULL,
  read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  UNIQUE KEY unique_notification_user (notification_id, user_id),
  INDEX idx_nr_user (user_id, read_at),
  INDEX idx_nr_notification (notification_id),
  
  CONSTRAINT fk_nr_notification FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
  CONSTRAINT fk_nr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill notification preferences for existing users
INSERT INTO user_notification_preferences (user_id)
SELECT u.id FROM users u
LEFT JOIN user_notification_preferences p ON p.user_id = u.id
WHERE p.user_id IS NULL;

-- Success message
SELECT 'Notification system tables created successfully!' as message;
