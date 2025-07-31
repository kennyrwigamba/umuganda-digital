-- Location Helper Functions and Procedures
-- This script provides utility functions for working with the location hierarchy

USE umuganda_digital;

DELIMITER //

-- Function to get the full location path for a cell
CREATE FUNCTION GetLocationPath(cell_id INT) 
RETURNS VARCHAR(500)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE location_path VARCHAR(500);
    
    SELECT CONCAT(p.name, ' > ', d.name, ' > ', s.name, ' > ', c.name)
    INTO location_path
    FROM cells c
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    JOIN provinces p ON d.province_id = p.id
    WHERE c.id = cell_id;
    
    RETURN COALESCE(location_path, 'Unknown Location');
END //

-- Function to check if an admin can manage a specific cell
CREATE FUNCTION CanAdminManageCell(admin_id INT, cell_id INT)
RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE can_manage BOOLEAN DEFAULT FALSE;
    
    SELECT TRUE INTO can_manage
    FROM admin_assignments aa
    JOIN cells c ON aa.sector_id = c.sector_id
    WHERE aa.admin_id = admin_id 
    AND c.id = cell_id 
    AND aa.is_active = TRUE
    LIMIT 1;
    
    RETURN COALESCE(can_manage, FALSE);
END //

-- Function to get the sector ID from a cell ID
CREATE FUNCTION GetSectorFromCell(cell_id INT)
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE sector_id INT;
    
    SELECT c.sector_id INTO sector_id
    FROM cells c
    WHERE c.id = cell_id;
    
    RETURN sector_id;
END //

-- Function to get the district ID from a cell ID
CREATE FUNCTION GetDistrictFromCell(cell_id INT)
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE district_id INT;
    
    SELECT s.district_id INTO district_id
    FROM cells c
    JOIN sectors s ON c.sector_id = s.id
    WHERE c.id = cell_id;
    
    RETURN district_id;
END //

-- Function to get the province ID from a cell ID
CREATE FUNCTION GetProvinceFromCell(cell_id INT)
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE province_id INT;
    
    SELECT d.province_id INTO province_id
    FROM cells c
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    WHERE c.id = cell_id;
    
    RETURN province_id;
END //

DELIMITER ;

-- Create additional helper views for location management

-- View for location hierarchy (for dropdown population)
CREATE OR REPLACE VIEW location_hierarchy AS
SELECT 
    p.id as province_id,
    p.name as province_name,
    p.code as province_code,
    d.id as district_id,
    d.name as district_name,
    d.code as district_code,
    s.id as sector_id,
    s.name as sector_name,
    s.code as sector_code,
    c.id as cell_id,
    c.name as cell_name,
    c.code as cell_code,
    CONCAT(p.name, ' > ', d.name, ' > ', s.name, ' > ', c.name) as full_path
FROM provinces p
JOIN districts d ON p.id = d.province_id
JOIN sectors s ON d.id = s.district_id
JOIN cells c ON s.id = c.sector_id
ORDER BY p.name, d.name, s.name, c.name;

-- View for admin sectors (shows which sectors each admin manages)
CREATE OR REPLACE VIEW admin_sectors AS
SELECT 
    u.id as admin_id,
    u.first_name,
    u.last_name,
    u.email,
    s.id as sector_id,
    s.name as sector_name,
    d.name as district_name,
    p.name as province_name,
    aa.assigned_at,
    aa.is_active,
    aa.notes
FROM users u
JOIN admin_assignments aa ON u.id = aa.admin_id
JOIN sectors s ON aa.sector_id = s.id
JOIN districts d ON s.district_id = d.id
JOIN provinces p ON d.province_id = p.id
WHERE u.role = 'admin'
ORDER BY u.last_name, u.first_name, s.name;

-- View for resident counts by location
CREATE OR REPLACE VIEW resident_counts_by_location AS
SELECT 
    p.id as province_id,
    p.name as province_name,
    d.id as district_id,
    d.name as district_name,
    s.id as sector_id,
    s.name as sector_name,
    c.id as cell_id,
    c.name as cell_name,
    COUNT(u.id) as resident_count,
    COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_residents,
    COUNT(CASE WHEN u.status = 'inactive' THEN 1 END) as inactive_residents,
    COUNT(CASE WHEN u.status = 'suspended' THEN 1 END) as suspended_residents
FROM provinces p
JOIN districts d ON p.id = d.province_id
JOIN sectors s ON d.id = s.district_id
JOIN cells c ON s.id = c.sector_id
LEFT JOIN users u ON c.id = u.cell_id AND u.role = 'resident'
GROUP BY p.id, d.id, s.id, c.id
ORDER BY p.name, d.name, s.name, c.name;

-- Additional stored procedures for location management

DELIMITER //

-- Procedure to assign an admin to a sector
CREATE PROCEDURE AssignAdminToSector(
    IN p_admin_id INT,
    IN p_sector_id INT,
    IN p_assigned_by INT,
    IN p_notes TEXT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Check if admin exists and has admin role
    IF NOT EXISTS (SELECT 1 FROM users WHERE id = p_admin_id AND role = 'admin') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User is not an admin';
    END IF;
    
    -- Check if sector exists
    IF NOT EXISTS (SELECT 1 FROM sectors WHERE id = p_sector_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sector does not exist';
    END IF;
    
    -- Deactivate any existing assignments for this admin-sector combination
    UPDATE admin_assignments 
    SET is_active = FALSE 
    WHERE admin_id = p_admin_id AND sector_id = p_sector_id;
    
    -- Create new assignment
    INSERT INTO admin_assignments (admin_id, sector_id, assigned_by, notes, is_active)
    VALUES (p_admin_id, p_sector_id, p_assigned_by, p_notes, TRUE);
    
    COMMIT;
END //

-- Procedure to remove admin assignment from a sector
CREATE PROCEDURE RemoveAdminFromSector(
    IN p_admin_id INT,
    IN p_sector_id INT
)
BEGIN
    UPDATE admin_assignments 
    SET is_active = FALSE 
    WHERE admin_id = p_admin_id AND sector_id = p_sector_id;
END //

-- Procedure to get all residents an admin can manage
CREATE PROCEDURE GetAdminManagedResidents(IN p_admin_id INT)
BEGIN
    SELECT DISTINCT
        u.id,
        u.national_id,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        u.status,
        u.date_of_birth,
        u.gender,
        c.name as cell_name,
        s.name as sector_name,
        d.name as district_name,
        p.name as province_name,
        u.created_at
    FROM users u
    JOIN cells c ON u.cell_id = c.id
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    JOIN provinces p ON d.province_id = p.id
    JOIN admin_assignments aa ON s.id = aa.sector_id
    WHERE aa.admin_id = p_admin_id 
    AND aa.is_active = TRUE
    AND u.role = 'resident'
    ORDER BY s.name, c.name, u.last_name, u.first_name;
END //

-- Procedure to validate and set user location
CREATE PROCEDURE SetUserLocation(
    IN p_user_id INT,
    IN p_cell_id INT
)
BEGIN
    DECLARE v_sector_id INT;
    DECLARE v_district_id INT;
    DECLARE v_province_id INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Check if cell exists and get hierarchy
    SELECT 
        c.sector_id,
        s.district_id,
        d.province_id
    INTO v_sector_id, v_district_id, v_province_id
    FROM cells c
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    WHERE c.id = p_cell_id;
    
    IF v_sector_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid cell ID';
    END IF;
    
    -- Update user location
    UPDATE users 
    SET 
        cell_id = p_cell_id,
        sector_id = v_sector_id,
        district_id = v_district_id,
        province_id = v_province_id
    WHERE id = p_user_id;
    
    COMMIT;
END //

-- Procedure to get location statistics for an admin
CREATE PROCEDURE GetAdminLocationStats(IN p_admin_id INT)
BEGIN
    SELECT 
        s.name as sector_name,
        d.name as district_name,
        p.name as province_name,
        COUNT(DISTINCT c.id) as total_cells,
        COUNT(u.id) as total_residents,
        COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_residents,
        COUNT(CASE WHEN u.status = 'inactive' THEN 1 END) as inactive_residents,
        COUNT(CASE WHEN u.status = 'suspended' THEN 1 END) as suspended_residents
    FROM admin_assignments aa
    JOIN sectors s ON aa.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    JOIN provinces p ON d.province_id = p.id
    LEFT JOIN cells c ON s.id = c.sector_id
    LEFT JOIN users u ON c.id = u.cell_id AND u.role = 'resident'
    WHERE aa.admin_id = p_admin_id AND aa.is_active = TRUE
    GROUP BY s.id, d.id, p.id
    ORDER BY s.name;
END //

DELIMITER ;

-- Insert some additional settings for location management
INSERT INTO settings (setting_key, setting_value, description, category, data_type) VALUES
('enable_location_hierarchy', 'true', 'Enable hierarchical location management', 'location', 'boolean'),
('require_cell_assignment', 'true', 'Require users to be assigned to a specific cell', 'location', 'boolean'),
('allow_cross_sector_admin', 'false', 'Allow admins to manage multiple sectors', 'location', 'boolean'),
('location_validation_strict', 'true', 'Strict validation of location hierarchy', 'location', 'boolean')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;
