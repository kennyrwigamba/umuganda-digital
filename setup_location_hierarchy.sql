-- Master script to set up location hierarchy system
-- Run this script to implement the complete location hierarchy for Umuganda Digital
-- 
-- This script will:
-- 1. Create the location hierarchy tables (provinces, districts, sectors, cells)
-- 2. Populate them with Rwanda's administrative divisions
-- 3. Add admin assignment functionality
-- 4. Migrate existing data to use the new structure
-- 5. Create helper functions and views

-- Execute in order:
SOURCE location_hierarchy_schema.sql;
SOURCE location_hierarchy_migration.sql;
SOURCE location_helper_functions.sql;

-- Verification queries to check the setup
SELECT 'Location Hierarchy Setup Verification' as status;

-- Check provinces
SELECT 'Provinces' as table_name, COUNT(*) as record_count FROM provinces;

-- Check districts
SELECT 'Districts' as table_name, COUNT(*) as record_count FROM districts;

-- Check sectors  
SELECT 'Sectors' as table_name, COUNT(*) as record_count FROM sectors;

-- Check cells
SELECT 'Cells' as table_name, COUNT(*) as record_count FROM cells;

-- Check admin assignments
SELECT 'Admin Assignments' as table_name, COUNT(*) as record_count FROM admin_assignments;

-- Show sample location hierarchy
SELECT 'Sample Location Hierarchy' as info;
SELECT 
    p.name as Province,
    d.name as District, 
    s.name as Sector,
    c.name as Cell
FROM provinces p
JOIN districts d ON p.id = d.province_id
JOIN sectors s ON d.id = s.district_id  
JOIN cells c ON s.id = c.sector_id
WHERE p.name = 'Kigali City'
ORDER BY d.name, s.name, c.name
LIMIT 10;

-- Show updated admin user
SELECT 'Updated Admin User Location' as info;
SELECT 
    u.first_name,
    u.last_name,
    u.email,
    p.name as province,
    d.name as district,
    s.name as sector,
    c.name as cell
FROM users u
LEFT JOIN cells c ON u.cell_id = c.id
LEFT JOIN sectors s ON u.sector_id = s.id
LEFT JOIN districts d ON u.district_id = d.id
LEFT JOIN provinces p ON u.province_id = p.id
WHERE u.role = 'admin';

SELECT 'Location Hierarchy Setup Complete!' as status;
