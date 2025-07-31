-- Rwanda Administrative Hierarchy Schema
-- This script creates tables for Rwanda's administrative divisions
-- and updates the existing schema to use proper foreign key relationships

USE umuganda_digital;

-- 1. Provinces table
CREATE TABLE provinces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(10) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_code (code)
);

-- 2. Districts table
CREATE TABLE districts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    province_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE CASCADE,
    UNIQUE KEY unique_district_province (name, province_id),
    INDEX idx_name (name),
    INDEX idx_code (code),
    INDEX idx_province (province_id)
);

-- 3. Sectors table
CREATE TABLE sectors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    district_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_sector_district (name, district_id),
    INDEX idx_name (name),
    INDEX idx_code (code),
    INDEX idx_district (district_id)
);

-- 4. Cells table
CREATE TABLE cells (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sector_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sector_id) REFERENCES sectors(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cell_sector (name, sector_id),
    INDEX idx_name (name),
    INDEX idx_code (code),
    INDEX idx_sector (sector_id)
);

-- 5. Admin assignments table (for sector-level administration)
CREATE TABLE admin_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    sector_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sector_id) REFERENCES sectors(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_admin_sector_active (admin_id, sector_id, is_active),
    INDEX idx_admin (admin_id),
    INDEX idx_sector (sector_id),
    INDEX idx_active (is_active)
);

-- Insert Rwanda's provinces
INSERT INTO provinces (name, code) VALUES
('Kigali City', 'KGL'),
('Eastern Province', 'EST'),
('Northern Province', 'NTH'),
('Southern Province', 'STH'),
('Western Province', 'WST');

-- Insert districts for each province
-- Kigali City districts
INSERT INTO districts (province_id, name, code) VALUES
((SELECT id FROM provinces WHERE code = 'KGL'), 'Gasabo', 'GSB'),
((SELECT id FROM provinces WHERE code = 'KGL'), 'Kicukiro', 'KCK'),
((SELECT id FROM provinces WHERE code = 'KGL'), 'Nyarugenge', 'NYR');

-- Eastern Province districts
INSERT INTO districts (province_id, name, code) VALUES
((SELECT id FROM provinces WHERE code = 'EST'), 'Bugesera', 'BGS'),
((SELECT id FROM provinces WHERE code = 'EST'), 'Gatsibo', 'GTB'),
((SELECT id FROM provinces WHERE code = 'EST'), 'Kayonza', 'KYZ'),
((SELECT id FROM provinces WHERE code = 'EST'), 'Kirehe', 'KRH'),
((SELECT id FROM provinces WHERE code = 'EST'), 'Ngoma', 'NGM'),
((SELECT id FROM provinces WHERE code = 'EST'), 'Nyagatare', 'NYG'),
((SELECT id FROM provinces WHERE code = 'EST'), 'Rwamagana', 'RWM');

-- Northern Province districts
INSERT INTO districts (province_id, name, code) VALUES
((SELECT id FROM provinces WHERE code = 'NTH'), 'Burera', 'BRR'),
((SELECT id FROM provinces WHERE code = 'NTH'), 'Gakenke', 'GKK'),
((SELECT id FROM provinces WHERE code = 'NTH'), 'Gicumbi', 'GCB'),
((SELECT id FROM provinces WHERE code = 'NTH'), 'Musanze', 'MSZ'),
((SELECT id FROM provinces WHERE code = 'NTH'), 'Rulindo', 'RLD');

-- Southern Province districts
INSERT INTO districts (province_id, name, code) VALUES
((SELECT id FROM provinces WHERE code = 'STH'), 'Gisagara', 'GSG'),
((SELECT id FROM provinces WHERE code = 'STH'), 'Huye', 'HYE'),
((SELECT id FROM provinces WHERE code = 'STH'), 'Kamonyi', 'KMN'),
((SELECT id FROM provinces WHERE code = 'STH'), 'Muhanga', 'MHG'),
((SELECT id FROM provinces WHERE code = 'STH'), 'Nyamagabe', 'NYM'),
((SELECT id FROM provinces WHERE code = 'STH'), 'Nyanza', 'NYZ'),
((SELECT id FROM provinces WHERE code = 'STH'), 'Nyaruguru', 'NYU'),
((SELECT id FROM provinces WHERE code = 'STH'), 'Ruhango', 'RHG');

-- Western Province districts
INSERT INTO districts (province_id, name, code) VALUES
((SELECT id FROM provinces WHERE code = 'WST'), 'Karongi', 'KRG'),
((SELECT id FROM provinces WHERE code = 'WST'), 'Ngororero', 'NGR'),
((SELECT id FROM provinces WHERE code = 'WST'), 'Nyabihu', 'NYB'),
((SELECT id FROM provinces WHERE code = 'WST'), 'Nyamasheke', 'NYS'),
((SELECT id FROM provinces WHERE code = 'WST'), 'Rubavu', 'RBV'),
((SELECT id FROM provinces WHERE code = 'WST'), 'Rusizi', 'RSZ'),
((SELECT id FROM provinces WHERE code = 'WST'), 'Rutsiro', 'RTS');

-- Insert sectors for Kigali City (sample sectors for demonstration)
-- Gasabo District sectors
INSERT INTO sectors (district_id, name, code) VALUES
((SELECT id FROM districts WHERE code = 'GSB'), 'Bumbogo', 'BMB'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Gatsata', 'GTS'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Gikomero', 'GKM'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Gisozi', 'GSZ'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Jabana', 'JBN'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Jali', 'JAL'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Kacyiru', 'KCY'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Kimihurura', 'KMH'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Kimironko', 'KMR'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Kinyinya', 'KNY'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Ndera', 'NDR'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Nduba', 'NDB'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Remera', 'RMR'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Rusororo', 'RSR'),
((SELECT id FROM districts WHERE code = 'GSB'), 'Rutunga', 'RTG');

-- Kicukiro District sectors
INSERT INTO sectors (district_id, name, code) VALUES
((SELECT id FROM districts WHERE code = 'KCK'), 'Gahanga', 'GHG'),
((SELECT id FROM districts WHERE code = 'KCK'), 'Gatenga', 'GTG'),
((SELECT id FROM districts WHERE code = 'KCK'), 'Gikondo', 'GKD'),
((SELECT id FROM districts WHERE code = 'KCK'), 'Kagarama', 'KGR'),
((SELECT id FROM districts WHERE code = 'KCK'), 'Kanombe', 'KNB'),
((SELECT id FROM districts WHERE code = 'KCK'), 'Kicukiro', 'KCK'),
((SELECT id FROM districts WHERE code = 'KCK'), 'Kigarama', 'KGM'),
((SELECT id FROM districts WHERE code = 'KCK'), 'Masaka', 'MSK'),
((SELECT id FROM districts WHERE code = 'KCK'), 'Niboye', 'NBY'),
((SELECT id FROM districts WHERE code = 'KCK'), 'Nyarugunga', 'NYG');

-- Nyarugenge District sectors
INSERT INTO sectors (district_id, name, code) VALUES
((SELECT id FROM districts WHERE code = 'NYR'), 'Gitega', 'GTG'),
((SELECT id FROM districts WHERE code = 'NYR'), 'Kanyinya', 'KYN'),
((SELECT id FROM districts WHERE code = 'NYR'), 'Kigali', 'KGL'),
((SELECT id FROM districts WHERE code = 'NYR'), 'Kimisagara', 'KMS'),
((SELECT id FROM districts WHERE code = 'NYR'), 'Mageragere', 'MGR'),
((SELECT id FROM districts WHERE code = 'NYR'), 'Muhima', 'MHM'),
((SELECT id FROM districts WHERE code = 'NYR'), 'Nyakabanda', 'NYK'),
((SELECT id FROM districts WHERE code = 'NYR'), 'Nyamirambo', 'NYM'),
((SELECT id FROM districts WHERE code = 'NYR'), 'Nyarugenge', 'NYR'),
((SELECT id FROM districts WHERE code = 'NYR'), 'Rwezamenyo', 'RWZ');

-- Insert sample cells for some sectors (focusing on Nyarugenge district for demonstration)
-- Kimisagara Sector cells
INSERT INTO cells (sector_id, name, code) VALUES
((SELECT id FROM sectors WHERE code = 'KMS'), 'Bibare', 'BBR'),
((SELECT id FROM sectors WHERE code = 'KMS'), 'Kivugiza', 'KVG'),
((SELECT id FROM sectors WHERE code = 'KMS'), 'Rugenge', 'RGG'),
((SELECT id FROM sectors WHERE code = 'KMS'), 'Rwampara', 'RWP');

-- Nyamirambo Sector cells
INSERT INTO cells (sector_id, name, code) VALUES
((SELECT id FROM sectors WHERE code = 'NYM'), 'Muhima', 'MHM'),
((SELECT id FROM sectors WHERE code = 'NYM'), 'Nyamirambo', 'NYM'),
((SELECT id FROM sectors WHERE code = 'NYM'), 'Nyakabanda', 'NYK'),
((SELECT id FROM sectors WHERE code = 'NYM'), 'Gitega', 'GTG');

-- Kigali Sector cells
INSERT INTO cells (sector_id, name, code) VALUES
((SELECT id FROM sectors WHERE code = 'KGL'), 'Gisimenti', 'GSM'),
((SELECT id FROM sectors WHERE code = 'KGL'), 'Ubugobe', 'UBG'),
((SELECT id FROM sectors WHERE code = 'KGL'), 'Urugwiro', 'URG');

-- Add sample cells for Gasabo district sectors
-- Kimironko Sector cells
INSERT INTO cells (sector_id, name, code) VALUES
((SELECT id FROM sectors WHERE code = 'KMR'), 'Biryogo', 'BRY'),
((SELECT id FROM sectors WHERE code = 'KMR'), 'Kimironko', 'KMR'),
((SELECT id FROM sectors WHERE code = 'KMR'), 'Nyarutarama', 'NYT'),
((SELECT id FROM sectors WHERE code = 'KMR'), 'Ururembo', 'URR');

-- Remera Sector cells
INSERT INTO cells (sector_id, name, code) VALUES
((SELECT id FROM sectors WHERE code = 'RMR'), 'Gishushu', 'GSH'),
((SELECT id FROM sectors WHERE code = 'RMR'), 'Rukiri I', 'RK1'),
((SELECT id FROM sectors WHERE code = 'RMR'), 'Rukiri II', 'RK2'),
((SELECT id FROM sectors WHERE code = 'RMR'), 'Urugendo', 'URG');

-- Kacyiru Sector cells
INSERT INTO cells (sector_id, name, code) VALUES
((SELECT id FROM sectors WHERE code = 'KCY'), 'Kamatamu', 'KMT'),
((SELECT id FROM sectors WHERE code = 'KCY'), 'Kibagabaga', 'KBG'),
((SELECT id FROM sectors WHERE code = 'KCY'), 'Kimihurura', 'KMH'),
((SELECT id FROM sectors WHERE code = 'KCY'), 'Ubumwe', 'UBW');

-- Add sample cells for Kicukiro district sectors
-- Niboye Sector cells
INSERT INTO cells (sector_id, name, code) VALUES
((SELECT id FROM sectors WHERE code = 'NBY'), 'Gatenga', 'GTG'),
((SELECT id FROM sectors WHERE code = 'NBY'), 'Niboye', 'NBY'),
((SELECT id FROM sectors WHERE code = 'NBY'), 'Rebero', 'RBR');

-- Kanombe Sector cells
INSERT INTO cells (sector_id, name, code) VALUES
((SELECT id FROM sectors WHERE code = 'KNB'), 'Busanza', 'BSZ'),
((SELECT id FROM sectors WHERE code = 'KNB'), 'Kanombe', 'KNB'),
((SELECT id FROM sectors WHERE code = 'KNB'), 'Muyange', 'MYG');
