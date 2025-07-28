-- Dummy data for Umuganda Digital Application
-- Created on: July 28, 2025

-- Users table data
-- Note: Passwords are hashed using bcrypt algorithm, default is "password123" for all users

-- Admin users
INSERT INTO users (national_id, first_name, last_name, email, phone, password, cell, sector, district, province, date_of_birth, gender, role, status, profile_picture) VALUES
('1198980012345670', 'John', 'Mugabo', 'admin@umuganda.gov.rw', '+250781234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kimihurura', 'Gasabo', 'Kigali', 'Kigali', '1980-05-15', 'male', 'admin', 'active', 'profile_admin1.jpg'),
('1199070012345671', 'Marie', 'Uwamahoro', 'marie.admin@umuganda.gov.rw', '+250721234568', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Remera', 'Gasabo', 'Kigali', 'Kigali', '1985-08-21', 'female', 'admin', 'active', 'profile_admin2.jpg');

-- Resident users
INSERT INTO users (national_id, first_name, last_name, email, phone, password, cell, sector, district, province, date_of_birth, gender, role, status, profile_picture) VALUES
('1199180012345672', 'Eric', 'Niyonzima', 'eric@example.com', '+250731234569', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kimihurura', 'Gasabo', 'Kigali', 'Kigali', '1990-03-10', 'male', 'resident', 'active', 'profile_resident1.jpg'),
('1199280012345673', 'Alice', 'Mukamana', 'alice@example.com', '+250741234570', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kimihurura', 'Gasabo', 'Kigali', 'Kigali', '1992-07-25', 'female', 'resident', 'active', 'profile_resident2.jpg'),
('1199080012345674', 'David', 'Habimana', 'david@example.com', '+250751234571', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Remera', 'Gasabo', 'Kigali', 'Kigali', '1988-11-15', 'male', 'resident', 'active', 'profile_resident3.jpg'),
('1199380012345675', 'Grace', 'Iradukunda', 'grace@example.com', '+250761234572', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Remera', 'Gasabo', 'Kigali', 'Kigali', '1993-05-20', 'female', 'resident', 'active', 'profile_resident4.jpg'),
('1199480012345676', 'Patrick', 'Mugisha', 'patrick@example.com', '+250771234573', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nyamirambo', 'Nyarugenge', 'Kigali', 'Kigali', '1994-09-03', 'male', 'resident', 'active', 'profile_resident5.jpg'),
('1199580012345677', 'Diane', 'Umutoni', 'diane@example.com', '+250781234574', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nyamirambo', 'Nyarugenge', 'Kigali', 'Kigali', '1995-12-28', 'female', 'resident', 'active', 'profile_resident6.jpg'),
('1199680012345678', 'Jean', 'Bizimana', 'jean@example.com', '+250791234575', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gikondo', 'Kicukiro', 'Kigali', 'Kigali', '1996-02-15', 'male', 'resident', 'active', 'profile_resident7.jpg'),
('1199780012345679', 'Christine', 'Mukashyaka', 'christine@example.com', '+250701234576', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gikondo', 'Kicukiro', 'Kigali', 'Kigali', '1997-06-10', 'female', 'resident', 'active', 'profile_resident8.jpg'),
('1199880012345680', 'Peter', 'Kanyarwanda', 'peter@example.com', '+250711234577', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kabeza', 'Kicukiro', 'Kigali', 'Kigali', '1998-10-22', 'male', 'resident', 'inactive', NULL),
('1199980012345681', 'Sarah', 'Ingabire', 'sarah@example.com', '+250721234578', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kabeza', 'Kicukiro', 'Kigali', 'Kigali', '1999-04-14', 'female', 'resident', 'suspended', NULL),
('1198580012345682', 'Robert', 'Mutabazi', 'robert@example.com', '+250731234579', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Muhima', 'Nyarugenge', 'Kigali', 'Kigali', '1985-08-30', 'male', 'resident', 'active', 'profile_resident11.jpg');

-- Umuganda events table data
-- Past events
INSERT INTO umuganda_events (title, description, event_date, start_time, end_time, location, cell, sector, district, province, max_participants, status, created_by) VALUES
('Community Road Cleaning', 'Monthly road cleaning and maintenance of drainage systems', '2025-06-28', '08:00:00', '11:00:00', 'Kimihurura Main Street', 'Kimihurura', 'Gasabo', 'Kigali', 'Kigali', 100, 'completed', 1),
('School Garden Planting', 'Planting trees and establishing a garden at the local primary school', '2025-05-31', '08:00:00', '11:30:00', 'Remera Primary School', 'Remera', 'Gasabo', 'Kigali', 'Kigali', 50, 'completed', 2),
('Public Park Cleaning', 'Cleaning and maintaining the neighborhood park', '2025-04-26', '08:30:00', '11:00:00', 'Nyamirambo Central Park', 'Nyamirambo', 'Nyarugenge', 'Kigali', 'Kigali', 75, 'completed', 1);

-- Current and upcoming events
INSERT INTO umuganda_events (title, description, event_date, start_time, end_time, location, cell, sector, district, province, max_participants, status, created_by) VALUES
('Road Infrastructure Repair', 'Fixing potholes and improving roadside drainage', '2025-07-26', '08:00:00', '11:30:00', 'Kicukiro Main Road', 'Gikondo', 'Kicukiro', 'Kigali', 'Kigali', 120, 'scheduled', 1),
('Community Garden Maintenance', 'Weeding and planting in the community garden', '2025-08-30', '08:30:00', '11:00:00', 'Gasabo Community Garden', 'Remera', 'Gasabo', 'Kigali', 'Kigali', 80, 'scheduled', 2),
('River Bank Protection Project', 'Planting trees along the riverbank to prevent erosion', '2025-09-27', '08:00:00', '12:00:00', 'Nyabarongo River', 'Nyamirambo', 'Nyarugenge', 'Kigali', 'Kigali', 150, 'scheduled', 1),
('School Playground Development', 'Building new playground facilities for the local school', '2025-10-25', '08:00:00', '11:30:00', 'Kicukiro Secondary School', 'Kabeza', 'Kicukiro', 'Kigali', 'Kigali', 100, 'scheduled', 2);

-- Attendance table data
-- For completed events
-- Event 1 (Community Road Cleaning)
INSERT INTO attendance (user_id, event_id, check_in_time, check_out_time, status, notes, recorded_by) VALUES
(3, 1, '2025-06-28 08:05:00', '2025-06-28 11:00:00', 'present', 'Active participation', 1),
(4, 1, '2025-06-28 08:10:00', '2025-06-28 11:05:00', 'present', 'Helped with coordination', 1),
(5, 1, '2025-06-28 08:30:00', '2025-06-28 11:00:00', 'late', 'Arrived late due to transportation issues', 1),
(6, 1, NULL, NULL, 'absent', NULL, 1),
(7, 1, '2025-06-28 08:15:00', '2025-06-28 10:30:00', 'present', 'Left early due to emergency', 1),
(8, 1, NULL, NULL, 'excused', 'Medical reason with supporting document', 1),
(9, 1, '2025-06-28 08:00:00', '2025-06-28 11:00:00', 'present', 'Very proactive', 1),
(10, 1, '2025-06-28 08:07:00', '2025-06-28 11:00:00', 'present', NULL, 1),
(11, 1, NULL, NULL, 'absent', NULL, 1),
(12, 1, '2025-06-28 08:12:00', '2025-06-28 11:00:00', 'present', NULL, 1);

-- Event 2 (School Garden Planting)
INSERT INTO attendance (user_id, event_id, check_in_time, check_out_time, status, excuse_reason, recorded_by) VALUES
(3, 2, '2025-05-31 08:00:00', '2025-05-31 11:30:00', 'present', NULL, 2),
(4, 2, '2025-05-31 08:15:00', '2025-05-31 11:20:00', 'present', NULL, 2),
(5, 2, NULL, NULL, 'excused', 'Travel out of town', 2),
(6, 2, '2025-05-31 08:25:00', '2025-05-31 11:30:00', 'present', NULL, 2),
(7, 2, NULL, NULL, 'absent', NULL, 2),
(8, 2, '2025-05-31 09:00:00', '2025-05-31 11:30:00', 'late', NULL, 2),
(9, 2, '2025-05-31 08:05:00', '2025-05-31 11:25:00', 'present', NULL, 2),
(10, 2, NULL, NULL, 'excused', 'Family emergency', 2);

-- Event 3 (Public Park Cleaning)
INSERT INTO attendance (user_id, event_id, check_in_time, check_out_time, status, recorded_by) VALUES
(3, 3, NULL, NULL, 'absent', 1),
(4, 3, '2025-04-26 08:25:00', '2025-04-26 11:00:00', 'present', 1),
(5, 3, '2025-04-26 08:30:00', '2025-04-26 11:00:00', 'present', 1),
(6, 3, '2025-04-26 08:20:00', '2025-04-26 11:00:00', 'present', 1),
(7, 3, NULL, NULL, 'excused', 1),
(8, 3, '2025-04-26 08:35:00', '2025-04-26 10:30:00', 'present', 1);

-- For upcoming events (only registered users without check-in/out)
-- Event 4 (Road Infrastructure Repair)
INSERT INTO attendance (user_id, event_id, status) VALUES
(3, 4, 'absent'),
(4, 4, 'absent'),
(5, 4, 'absent'),
(6, 4, 'absent'),
(7, 4, 'absent'),
(8, 4, 'absent'),
(9, 4, 'absent'),
(10, 4, 'absent');

-- Fines table data
-- Fines for Event 1 (Community Road Cleaning)
INSERT INTO fines (user_id, event_id, attendance_id, amount, reason, status, due_date, created_by) VALUES
(6, 1, 4, 5000.00, 'absence', 'pending', '2025-07-15', 1),
(11, 1, 9, 5000.00, 'absence', 'paid', '2025-07-15', 1);

-- Update the paid fine with payment details
UPDATE fines SET status = 'paid', paid_date = '2025-07-05 09:23:15', payment_method = 'Mobile Money', payment_reference = 'MM12345678' WHERE user_id = 11 AND event_id = 1;

-- Fines for Event 2 (School Garden Planting)
INSERT INTO fines (user_id, event_id, attendance_id, amount, reason, status, due_date, created_by) VALUES
(7, 2, 17, 5000.00, 'absence', 'pending', '2025-06-15', 2);

-- Fines for Event 3 (Public Park Cleaning)
INSERT INTO fines (user_id, event_id, attendance_id, amount, reason, reason_description, status, due_date, created_by) VALUES
(3, 3, 21, 5000.00, 'absence', 'No notification of absence provided', 'disputed', '2025-05-15', 1);

-- Waived fine example
INSERT INTO fines (user_id, event_id, attendance_id, amount, reason, status, due_date, waived_by, waived_reason, waived_date, created_by) VALUES
(7, 3, 25, 5000.00, 'absence', 'waived', '2025-05-15', 2, 'Legitimate family emergency with documentation provided', '2025-05-10 14:30:00', 1);

-- Notices table data
INSERT INTO notices (title, content, type, priority, target_audience, publish_date, expiry_date, status, created_by) VALUES
('Upcoming Umuganda Announcement', 'Dear residents, please be informed that the next Umuganda community service will take place on July 26, 2025, from 8:00 AM to 11:30 AM. We will be focusing on road infrastructure repair in Kicukiro. Please bring appropriate tools and protective gear.', 'general', 'medium', 'all', '2025-07-15 08:00:00', '2025-07-26 12:00:00', 'published', 1),
('Important Weather Advisory', 'Heavy rains expected during the upcoming week. All residents are advised to take necessary precautions and ensure proper drainage around their homes. The scheduled Umuganda activities might be affected.', 'urgent', 'high', 'all', '2025-07-20 10:00:00', '2025-07-27 23:59:59', 'published', 2),
('Reminder: Fine Payment Deadline', 'This is a reminder to all residents with pending Umuganda absence fines that the payment deadline is approaching. Please settle your fines by July 31, 2025, to avoid additional penalties.', 'fine_reminder', 'medium', 'all', '2025-07-18 09:00:00', '2025-07-31 23:59:59', 'published', 1),
('System Maintenance Notice', 'The Umuganda Digital system will undergo maintenance on July 29, 2025, from 10:00 PM to 2:00 AM. During this time, the system might be temporarily unavailable.', 'system', 'low', 'all', '2025-07-25 08:00:00', '2025-07-30 00:00:00', 'published', 2),
('Community Achievement Recognition', 'Congratulations to Kimihurura Cell for achieving 95% participation in the last Umuganda event. Your dedication to community service is commendable!', 'general', 'medium', 'specific_location', '2025-07-01 12:00:00', '2025-07-15 23:59:59', 'published', 1);

-- Update specific location for the last notice
UPDATE notices SET cell = 'Kimihurura', sector = 'Gasabo', district = 'Kigali' WHERE title = 'Community Achievement Recognition';

-- Draft notice
INSERT INTO notices (title, content, type, priority, target_audience, status, created_by) VALUES
('New Fine Payment Options', 'We are pleased to announce new payment options for Umuganda fines including bank transfer and mobile money. Details to follow soon.', 'general', 'medium', 'all', 'draft', 1);

-- Notice reads table data
INSERT INTO notice_reads (notice_id, user_id) VALUES
(1, 3), (1, 4), (1, 5), (1, 7), (1, 9),
(2, 3), (2, 4), (2, 6), (2, 8),
(3, 6), (3, 11),
(4, 3), (4, 5), (4, 7), (4, 9),
(5, 3), (5, 4);

-- Settings table data
INSERT INTO settings (setting_key, setting_value, description, category, data_type) VALUES
('site_name', 'Umuganda Digital', 'The name of the application displayed in various places', 'general', 'string'),
('contact_email', 'support@umuganda.gov.rw', 'Contact email address for support inquiries', 'general', 'string'),
('contact_phone', '+250788123456', 'Contact phone number for support inquiries', 'general', 'string'),
('fine_amount', '5000', 'Default fine amount in RWF for absence without excuse', 'fines', 'integer'),
('fine_due_days', '15', 'Number of days after an event that a fine is due', 'fines', 'integer'),
('notice_expiry_days', '30', 'Default number of days after which notices expire', 'notices', 'integer'),
('enable_sms_notifications', 'true', 'Whether SMS notifications are enabled for the system', 'notifications', 'boolean'),
('enable_email_notifications', 'true', 'Whether email notifications are enabled for the system', 'notifications', 'boolean'),
('attendance_check_in_window', '30', 'Number of minutes before the event start time when check-in is available', 'attendance', 'integer'),
('attendance_check_out_window', '30', 'Number of minutes after the event end time when check-out is available', 'attendance', 'integer'),
('system_theme', 'light', 'Default theme for the system UI', 'appearance', 'string'),
('maintenance_mode', 'false', 'Whether the system is in maintenance mode', 'system', 'boolean'),
('default_pagination', '10', 'Default number of items per page in lists', 'system', 'integer'),
('umuganda_schedule', '{"frequency":"monthly","default_day":"last_saturday","default_start_time":"08:00","default_duration":"3"}', 'Default schedule settings for Umuganda events', 'events', 'json');

-- Activity logs table data
INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent) VALUES
(1, 'login', 'user', 1, 'Admin user logged in successfully', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'),
(1, 'create', 'umuganda_event', 1, 'Created new Umuganda event: Community Road Cleaning', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'),
(2, 'create', 'umuganda_event', 2, 'Created new Umuganda event: School Garden Planting', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15'),
(1, 'update', 'attendance', 1, 'Recorded attendance for user #3 at event #1', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'),
(1, 'create', 'fine', 1, 'Created fine for user #6 for absence at event #1', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'),
(3, 'login', 'user', 3, 'User logged in successfully', '192.168.1.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1'),
(3, 'read', 'notice', 1, 'User read notice: Upcoming Umuganda Announcement', '192.168.1.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1'),
(2, 'update', 'user', 10, 'Updated user status to inactive', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15'),
(1, 'update', 'umuganda_event', 1, 'Updated event status to completed', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'),
(11, 'payment', 'fine', 2, 'Fine payment received via Mobile Money', '192.168.1.103', 'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0'),
(2, 'create', 'notice', 2, 'Created new urgent notice: Important Weather Advisory', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15'),
(NULL, 'error', 'system', NULL, 'Database connection timeout error occurred', '192.168.1.100', NULL),
(1, 'create', 'umuganda_event', 4, 'Created new Umuganda event: Road Infrastructure Repair', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'),
(2, 'waive', 'fine', 5, 'Waived fine for user #7 due to documented emergency', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15'),
(4, 'login', 'user', 4, 'User logged in successfully', '192.168.1.104', 'Mozilla/5.0 (Linux; Android 10; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.162 Mobile Safari/537.36');
