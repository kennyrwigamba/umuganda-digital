-- Migration script to update existing schema to use location hierarchy
-- This script should be run after location_hierarchy_schema.sql

USE umuganda_digital;

-- 1. First, add new foreign key columns to users table
ALTER TABLE users 
ADD COLUMN cell_id INT NULL AFTER province,
ADD COLUMN sector_id INT NULL AFTER cell_id,
ADD COLUMN district_id INT NULL AFTER sector_id,
ADD COLUMN province_id INT NULL AFTER district_id;

-- 2. Add foreign key constraints
ALTER TABLE users
ADD CONSTRAINT fk_users_cell FOREIGN KEY (cell_id) REFERENCES cells(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_users_sector FOREIGN KEY (sector_id) REFERENCES sectors(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_users_district FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_users_province FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE SET NULL;

-- 3. Add indexes for the new foreign key columns
ALTER TABLE users
ADD INDEX idx_cell_id (cell_id),
ADD INDEX idx_sector_id (sector_id),
ADD INDEX idx_district_id (district_id),
ADD INDEX idx_province_id (province_id);

-- 4. Update existing admin user with proper location IDs
-- Find Kimisagara cell ID and set it for the admin user
UPDATE users 
SET 
    cell_id = (
        SELECT c.id 
        FROM cells c 
        JOIN sectors s ON c.sector_id = s.id 
        WHERE c.name = 'Kivugiza' AND s.name = 'Kimisagara'
    ),
    sector_id = (
        SELECT s.id 
        FROM sectors s 
        JOIN districts d ON s.district_id = d.id 
        WHERE s.name = 'Kimisagara' AND d.name = 'Nyarugenge'
    ),
    district_id = (
        SELECT d.id 
        FROM districts d 
        JOIN provinces p ON d.province_id = p.id 
        WHERE d.name = 'Nyarugenge' AND p.name = 'Kigali City'
    ),
    province_id = (
        SELECT id FROM provinces WHERE name = 'Kigali City'
    )
WHERE email = 'admin@umuganda-digital.rw';

-- 5. Update umuganda_events table to use location hierarchy
ALTER TABLE umuganda_events
ADD COLUMN cell_id INT NULL AFTER province,
ADD COLUMN sector_id INT NULL AFTER cell_id,
ADD COLUMN district_id INT NULL AFTER sector_id,
ADD COLUMN province_id INT NULL AFTER district_id;

-- Add foreign key constraints for events
ALTER TABLE umuganda_events
ADD CONSTRAINT fk_events_cell FOREIGN KEY (cell_id) REFERENCES cells(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_events_sector FOREIGN KEY (sector_id) REFERENCES sectors(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_events_district FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_events_province FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE SET NULL;

-- Add indexes for events location
ALTER TABLE umuganda_events
ADD INDEX idx_event_cell_id (cell_id),
ADD INDEX idx_event_sector_id (sector_id),
ADD INDEX idx_event_district_id (district_id),
ADD INDEX idx_event_province_id (province_id);

-- 6. Update existing event with proper location IDs
UPDATE umuganda_events 
SET 
    cell_id = (
        SELECT c.id 
        FROM cells c 
        JOIN sectors s ON c.sector_id = s.id 
        WHERE c.name = 'Kivugiza' AND s.name = 'Kimisagara'
    ),
    sector_id = (
        SELECT s.id 
        FROM sectors s 
        JOIN districts d ON s.district_id = d.id 
        WHERE s.name = 'Kimisagara' AND d.name = 'Nyarugenge'
    ),
    district_id = (
        SELECT d.id 
        FROM districts d 
        JOIN provinces p ON d.province_id = p.id 
        WHERE d.name = 'Nyarugenge' AND p.name = 'Kigali City'
    ),
    province_id = (
        SELECT id FROM provinces WHERE name = 'Kigali City'
    )
WHERE title = 'Monthly Umuganda - July 2025';

-- 7. Update notices table to use location hierarchy
ALTER TABLE notices
ADD COLUMN cell_id INT NULL AFTER province,
ADD COLUMN sector_id INT NULL AFTER cell_id,
ADD COLUMN district_id INT NULL AFTER sector_id,
ADD COLUMN province_id INT NULL AFTER district_id;

-- Add foreign key constraints for notices
ALTER TABLE notices
ADD CONSTRAINT fk_notices_cell FOREIGN KEY (cell_id) REFERENCES cells(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_notices_sector FOREIGN KEY (sector_id) REFERENCES sectors(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_notices_district FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_notices_province FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE SET NULL;

-- Add indexes for notices location
ALTER TABLE notices
ADD INDEX idx_notice_cell_id (cell_id),
ADD INDEX idx_notice_sector_id (sector_id),
ADD INDEX idx_notice_district_id (district_id),
ADD INDEX idx_notice_province_id (province_id);

-- 8. Create views for easier querying with location hierarchy
-- View to get users with full location hierarchy
CREATE OR REPLACE VIEW users_with_location AS
SELECT 
    u.id,
    u.national_id,
    u.first_name,
    u.last_name,
    u.email,
    u.phone,
    u.role,
    u.status,
    u.date_of_birth,
    u.gender,
    u.profile_picture,
    u.created_at,
    u.updated_at,
    u.last_login,
    -- Legacy location fields (keep for backward compatibility)
    u.cell as legacy_cell,
    u.sector as legacy_sector,
    u.district as legacy_district,
    u.province as legacy_province,
    -- New hierarchical location
    c.name as cell_name,
    c.code as cell_code,
    s.name as sector_name,
    s.code as sector_code,
    d.name as district_name,
    d.code as district_code,
    p.name as province_name,
    p.code as province_code,
    -- IDs for foreign key relationships
    u.cell_id,
    u.sector_id,
    u.district_id,
    u.province_id
FROM users u
LEFT JOIN cells c ON u.cell_id = c.id
LEFT JOIN sectors s ON u.sector_id = s.id
LEFT JOIN districts d ON u.district_id = d.id
LEFT JOIN provinces p ON u.province_id = p.id;

-- View to get events with full location hierarchy
CREATE OR REPLACE VIEW events_with_location AS
SELECT 
    e.id,
    e.title,
    e.description,
    e.event_date,
    e.start_time,
    e.end_time,
    e.location,
    e.max_participants,
    e.status,
    e.created_by,
    e.created_at,
    e.updated_at,
    -- Legacy location fields
    e.cell as legacy_cell,
    e.sector as legacy_sector,
    e.district as legacy_district,
    e.province as legacy_province,
    -- New hierarchical location
    c.name as cell_name,
    c.code as cell_code,
    s.name as sector_name,
    s.code as sector_code,
    d.name as district_name,
    d.code as district_code,
    p.name as province_name,
    p.code as province_code,
    -- IDs for foreign key relationships
    e.cell_id,
    e.sector_id,
    e.district_id,
    e.province_id
FROM umuganda_events e
LEFT JOIN cells c ON e.cell_id = c.id
LEFT JOIN sectors s ON e.sector_id = s.id
LEFT JOIN districts d ON e.district_id = d.id
LEFT JOIN provinces p ON e.province_id = p.id;

-- View to get notices with full location hierarchy
CREATE OR REPLACE VIEW notices_with_location AS
SELECT 
    n.id,
    n.title,
    n.content,
    n.type,
    n.priority,
    n.target_audience,
    n.publish_date,
    n.expiry_date,
    n.status,
    n.created_by,
    n.created_at,
    n.updated_at,
    -- Legacy location fields
    n.cell as legacy_cell,
    n.sector as legacy_sector,
    n.district as legacy_district,
    n.province as legacy_province,
    -- New hierarchical location
    c.name as cell_name,
    c.code as cell_code,
    s.name as sector_name,
    s.code as sector_code,
    d.name as district_name,
    d.code as district_code,
    p.name as province_name,
    p.code as province_code,
    -- IDs for foreign key relationships
    n.cell_id,
    n.sector_id,
    n.district_id,
    n.province_id
FROM notices n
LEFT JOIN cells c ON n.cell_id = c.id
LEFT JOIN sectors s ON n.sector_id = s.id
LEFT JOIN districts d ON n.district_id = d.id
LEFT JOIN provinces p ON n.province_id = p.id;

-- 9. Create helper procedures for location management
DELIMITER //

-- Procedure to get all cells in a sector
CREATE PROCEDURE GetCellsInSector(IN sector_id INT)
BEGIN
    SELECT 
        c.id,
        c.name,
        c.code,
        s.name as sector_name,
        d.name as district_name,
        p.name as province_name
    FROM cells c
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    JOIN provinces p ON d.province_id = p.id
    WHERE c.sector_id = sector_id
    ORDER BY c.name;
END //

-- Procedure to get all sectors in a district
CREATE PROCEDURE GetSectorsInDistrict(IN district_id INT)
BEGIN
    SELECT 
        s.id,
        s.name,
        s.code,
        d.name as district_name,
        p.name as province_name
    FROM sectors s
    JOIN districts d ON s.district_id = d.id
    JOIN provinces p ON d.province_id = p.id
    WHERE s.district_id = district_id
    ORDER BY s.name;
END //

-- Procedure to get all districts in a province
CREATE PROCEDURE GetDistrictsInProvince(IN province_id INT)
BEGIN
    SELECT 
        d.id,
        d.name,
        d.code,
        p.name as province_name
    FROM districts d
    JOIN provinces p ON d.province_id = p.id
    WHERE d.province_id = province_id
    ORDER BY d.name;
END //

-- Procedure to get residents in a sector (for admin management)
CREATE PROCEDURE GetResidentsInSector(IN sector_id INT)
BEGIN
    SELECT 
        u.id,
        u.national_id,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        u.status,
        c.name as cell_name,
        s.name as sector_name,
        d.name as district_name,
        p.name as province_name
    FROM users u
    JOIN cells c ON u.cell_id = c.id
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    JOIN provinces p ON d.province_id = p.id
    WHERE u.sector_id = sector_id AND u.role = 'resident'
    ORDER BY u.last_name, u.first_name;
END //

DELIMITER ;

-- 10. Insert some sample admin assignments
-- Assign the default admin to manage Kimisagara sector
INSERT INTO admin_assignments (admin_id, sector_id, assigned_by, notes)
SELECT 
    u.id as admin_id,
    s.id as sector_id,
    u.id as assigned_by,
    'Default assignment for system administrator'
FROM users u, sectors s
WHERE u.email = 'admin@umuganda-digital.rw' 
AND s.name = 'Kimisagara'
AND NOT EXISTS (
    SELECT 1 FROM admin_assignments aa 
    WHERE aa.admin_id = u.id AND aa.sector_id = s.id
);

-- Note: The legacy location columns (cell, sector, district, province) in users, 
-- umuganda_events, and notices tables are kept for backward compatibility.
-- New applications should use the new foreign key relationships (cell_id, sector_id, etc.)
-- and the corresponding views for easier querying.
