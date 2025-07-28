-- Sample Notices Data for Testing
-- Insert sample notices into the database

USE umuganda_digital;

-- Sample notices with different types and priorities
INSERT INTO notices (title, content, type, priority, target_audience, status, created_by, publish_date) VALUES

-- Critical/High Priority Notices (Urgent)
('Weather Alert: Umuganda Session Rescheduled', 
'Tomorrow''s Umuganda session (July 29, 2025) has been moved to 8:30 AM due to expected heavy rainfall. Please bring rain gear and dress appropriately. Safety is our top priority.',
'urgent', 'critical', 'all', 'published', 1, '2025-07-28 06:00:00'),

('Emergency Road Closure Notice',
'Due to urgent road repairs, the main access road to the community center will be closed from July 30-31, 2025. Please use the alternative route via Market Street. Emergency services remain accessible.',
'urgent', 'high', 'all', 'published', 1, '2025-07-27 14:30:00'),

-- Schedule Changes
('August Umuganda Schedule Update',
'The August monthly Umuganda schedule has been updated. Please note the new time changes for the first Saturday of August due to a national holiday celebration. Start time moved to 7:00 AM.',
'general', 'medium', 'all', 'published', 1, '2025-07-24 10:30:00'),

('Weekend Community Work Sessions',
'Starting August 2025, we will have additional weekend community work sessions every second Saturday of the month. This is to accommodate working residents who cannot attend weekday sessions.',
'general', 'medium', 'all', 'published', 1, '2025-07-23 09:15:00'),

-- Events
('Community Garden Project Launch',
'We''re excited to announce the launch of our new community garden project! Join us for the inaugural planting session on August 10, 2025, at 9:00 AM. All community members are welcome to participate in this sustainable initiative. Tools and seeds will be provided.',
'event', 'medium', 'all', 'published', 1, '2025-07-22 14:15:00'),

('Safety Training Workshop',
'Mandatory safety training workshop for all community work participants on July 30, 2025, at 2:00 PM. Learn about proper tool usage, safety protocols, and emergency procedures. Limited to 50 participants - registration required.',
'event', 'high', 'all', 'published', 1, '2025-07-20 09:10:00'),

('Annual Community Health Fair',
'Join us for our annual community health fair on August 15, 2025, from 8:00 AM to 4:00 PM at the sector office. Free health screenings, vaccinations, and health education sessions available for all residents.',
'event', 'medium', 'all', 'published', 1, '2025-07-19 11:20:00'),

-- General Information
('New Recycling Guidelines',
'Updated recycling and waste management guidelines are now in effect. Please familiarize yourself with the new sorting requirements to help our community maintain its environmental standards. Download the full guide from our website.',
'general', 'medium', 'all', 'published', 1, '2025-07-18 11:45:00'),

('Tool Distribution Schedule',
'Community tools and equipment will be distributed every Friday from 2:00 PM to 4:00 PM at the sector office. Please bring your registration card and sign the equipment log. Available: cleaning tools, safety equipment, gardening supplies.',
'general', 'low', 'all', 'published', 1, '2025-07-17 15:20:00'),

('Community Newsletter July Edition',
'The July edition of our community newsletter is now available. Read about recent achievements, upcoming events, and important announcements. Pick up your copy at the sector office or download the digital version.',
'general', 'low', 'all', 'published', 1, '2025-07-16 08:30:00'),

-- Older Notices
('June Umuganda Participation Recognition',
'Congratulations to all residents who achieved 100% attendance in June 2025! Your dedication to community service is commendable. Recognition certificates will be distributed during the next community meeting.',
'general', 'low', 'all', 'published', 1, '2025-07-05 16:00:00'),

('Water Supply Maintenance Notice',
'Scheduled water supply maintenance will be conducted on August 5, 2025, from 6:00 AM to 2:00 PM. Please store sufficient water for daily needs. Emergency water points will be available at designated locations.',
'general', 'medium', 'all', 'published', 1, '2025-07-03 12:00:00'),

-- Future/Expired Notice for testing
('Community Meeting - August Planning',
'Monthly community planning meeting for August activities. All sector leaders and interested residents are invited to attend and contribute ideas for upcoming projects and initiatives.',
'general', 'medium', 'all', 'published', 1, '2025-07-01 09:00:00');

-- Sample notice reads (some notices marked as read by users)
-- Assuming user IDs 2, 3, 4 exist (residents)
INSERT INTO notice_reads (notice_id, user_id, read_at) VALUES
(3, 2, '2025-07-24 11:00:00'),  -- August schedule read by user 2
(4, 2, '2025-07-23 10:00:00'),  -- Weekend sessions read by user 2
(5, 2, '2025-07-22 15:00:00'),  -- Garden project read by user 2
(8, 2, '2025-07-18 12:00:00'),  -- Recycling guidelines read by user 2
(9, 2, '2025-07-17 16:00:00'),  -- Tool distribution read by user 2

(5, 3, '2025-07-22 16:30:00'),  -- Garden project read by user 3
(6, 3, '2025-07-20 10:30:00'),  -- Safety training read by user 3
(8, 3, '2025-07-18 13:15:00'),  -- Recycling guidelines read by user 3

(3, 4, '2025-07-24 12:30:00'),  -- August schedule read by user 4
(7, 4, '2025-07-19 12:00:00'),  -- Health fair read by user 4
(10, 4, '2025-07-16 09:00:00'); -- Newsletter read by user 4
