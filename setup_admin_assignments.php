<?php
/**
 * Setup Location Hierarchy and Admin Assignments
 * This script creates the location hierarchy and assigns admins to sectors
 */

require_once __DIR__ . '/config/db.php';

// Use the global database instance
global $db;
$connection = $db->getConnection();

echo "<h1>Setting up Location Hierarchy and Admin Assignments</h1>\n";

try {
    // Check if location tables exist, if not create them
    $checkProvinces = "SHOW TABLES LIKE 'provinces'";
    $result         = $connection->query($checkProvinces);

    if ($result->num_rows == 0) {
        echo "<h2>Creating Location Hierarchy Tables...</h2>\n";

        // Create provinces table
        $provinceTable = "
        CREATE TABLE provinces (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            code VARCHAR(10) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX idx_name (name),
            INDEX idx_code (code)
        )";
        $connection->query($provinceTable);
        echo "<p>✅ Provinces table created</p>\n";

        // Create districts table
        $districtTable = "
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
        )";
        $connection->query($districtTable);
        echo "<p>✅ Districts table created</p>\n";

        // Create sectors table
        $sectorTable = "
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
        )";
        $connection->query($sectorTable);
        echo "<p>✅ Sectors table created</p>\n";

        // Create cells table
        $cellTable = "
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
        )";
        $connection->query($cellTable);
        echo "<p>✅ Cells table created</p>\n";

        // Create admin assignments table
        $adminAssignmentTable = "
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
        )";
        $connection->query($adminAssignmentTable);
        echo "<p>✅ Admin assignments table created</p>\n";
    } else {
        echo "<p>ℹ️ Location hierarchy tables already exist</p>\n";
    }

    // Insert provinces if not exist
    $provinceData = [
        ['Kigali City', 'KGL'],
        ['Eastern Province', 'EST'],
        ['Northern Province', 'NTH'],
        ['Southern Province', 'STH'],
        ['Western Province', 'WST'],
    ];

    foreach ($provinceData as $province) {
        $stmt = $connection->prepare("INSERT IGNORE INTO provinces (name, code) VALUES (?, ?)");
        $stmt->bind_param('ss', $province[0], $province[1]);
        $stmt->execute();
    }
    echo "<p>✅ Provinces inserted</p>\n";

    // Insert Kigali City districts
    $kigaliDistricts = [
        ['Gasabo', 'GSB'],
        ['Kicukiro', 'KCK'],
        ['Nyarugenge', 'NYR'],
    ];

    $kigaliId = $connection->query("SELECT id FROM provinces WHERE code = 'KGL'")->fetch_assoc()['id'];

    foreach ($kigaliDistricts as $district) {
        $stmt = $connection->prepare("INSERT IGNORE INTO districts (province_id, name, code) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $kigaliId, $district[0], $district[1]);
        $stmt->execute();
    }
    echo "<p>✅ Kigali districts inserted</p>\n";

    // Insert sectors for Gasabo and Nyarugenge
    $gasaboId     = $connection->query("SELECT id FROM districts WHERE code = 'GSB'")->fetch_assoc()['id'];
    $nyarugengeId = $connection->query("SELECT id FROM districts WHERE code = 'NYR'")->fetch_assoc()['id'];

    $gasaboSectors = [
        ['Kimironko', 'KMR'],
        ['Remera', 'RMR'],
        ['Kacyiru', 'KCY'],
        ['Kinyinya', 'KNY'],
        ['Gisozi', 'GSZ'],
    ];

    $nyarugengeSectors = [
        ['Kimisagara', 'KMS'],
        ['Nyamirambo', 'NYM'],
        ['Muhima', 'MHM'],
        ['Nyarugenge', 'NYR'],
    ];

    foreach ($gasaboSectors as $sector) {
        $stmt = $connection->prepare("INSERT IGNORE INTO sectors (district_id, name, code) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $gasaboId, $sector[0], $sector[1]);
        $stmt->execute();
    }

    foreach ($nyarugengeSectors as $sector) {
        $stmt = $connection->prepare("INSERT IGNORE INTO sectors (district_id, name, code) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $nyarugengeId, $sector[0], $sector[1]);
        $stmt->execute();
    }
    echo "<p>✅ Sectors inserted</p>\n";

    // Create test admin users if they don't exist
    $testAdmins = [
        [
            'national_id' => 'ADM001',
            'first_name'  => 'John',
            'last_name'   => 'Gasabo',
            'email'       => 'admin.gasabo@umuganda.rw',
            'phone'       => '+250788000001',
            'password'    => password_hash('admin123', PASSWORD_DEFAULT),
            'role'        => 'admin',
            'sector_name' => 'Kimironko',
        ],
        [
            'national_id' => 'ADM002',
            'first_name'  => 'Marie',
            'last_name'   => 'Nyarugenge',
            'email'       => 'admin.nyarugenge@umuganda.rw',
            'phone'       => '+250788000002',
            'password'    => password_hash('admin123', PASSWORD_DEFAULT),
            'role'        => 'admin',
            'sector_name' => 'Kimisagara',
        ],
    ];

    foreach ($testAdmins as $admin) {
        $checkAdmin = $connection->prepare("SELECT id FROM users WHERE email = ?");
        $checkAdmin->bind_param('s', $admin['email']);
        $checkAdmin->execute();
        $result = $checkAdmin->get_result();

        if ($result->num_rows == 0) {
            // Get sector ID for the admin
            $sectorResult = $connection->query("SELECT id FROM sectors WHERE name = '{$admin['sector_name']}'");
            $sectorId     = $sectorResult->fetch_assoc()['id'];

            // Get district ID from sector
            $districtResult = $connection->query("SELECT district_id FROM sectors WHERE id = $sectorId");
            $districtId     = $districtResult->fetch_assoc()['district_id'];

            // Get province ID from district
            $provinceResult = $connection->query("SELECT province_id FROM districts WHERE id = $districtId");
            $provinceId     = $provinceResult->fetch_assoc()['province_id'];

            $insertAdmin = $connection->prepare("
                INSERT INTO users (national_id, first_name, last_name, email, phone, password,
                                  cell_id, sector_id, district_id, province_id, role, status, gender, date_of_birth)
                VALUES (?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, 'active', 'male', '1990-01-01')
            ");
            $insertAdmin->bind_param('sssssiiiis',
                $admin['national_id'],
                $admin['first_name'],
                $admin['last_name'],
                $admin['email'],
                $admin['phone'],
                $admin['password'],
                $sectorId,
                $districtId,
                $provinceId,
                $admin['role']
            );
            $insertAdmin->execute();
            echo "<p>✅ Created admin: {$admin['first_name']} {$admin['last_name']} for {$admin['sector_name']} sector</p>\n";
        }
    }

    // Assign admins to sectors
    echo "<h2>Assigning Admins to Sectors...</h2>\n";

    // Get admin IDs and sector IDs
    $kimironkoSectorId  = $connection->query("SELECT id FROM sectors WHERE code = 'KMR'")->fetch_assoc()['id'];
    $kimisagaraSectorId = $connection->query("SELECT id FROM sectors WHERE code = 'KMS'")->fetch_assoc()['id'];

    $gasaboAdminId     = $connection->query("SELECT id FROM users WHERE email = 'admin.gasabo@umuganda.rw'")->fetch_assoc()['id'];
    $nyarugengeAdminId = $connection->query("SELECT id FROM users WHERE email = 'admin.nyarugenge@umuganda.rw'")->fetch_assoc()['id'];

    // Super admin (assuming user ID 1 exists or create one)
    $superAdminQuery  = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
    $superAdminResult = $connection->query($superAdminQuery);

    if ($superAdminResult->num_rows == 0) {
        // Create a super admin
        $createSuperAdmin = $connection->prepare("
            INSERT INTO users (national_id, first_name, last_name, email, phone, password,
                              cell_id, sector_id, district_id, province_id, role, status, gender, date_of_birth)
            VALUES ('SUPER001', 'Super', 'Admin', 'super@umuganda.rw', '+250788000000',
                    ?, NULL, NULL, NULL, NULL, 'admin', 'active', 'male', '1985-01-01')
        ");
        $superPassword = password_hash('super123', PASSWORD_DEFAULT);
        $createSuperAdmin->bind_param('s', $superPassword);
        $createSuperAdmin->execute();
        $superAdminId = $connection->insert_id;
        echo "<p>✅ Created super admin</p>\n";
    } else {
        $superAdminId = $superAdminResult->fetch_assoc()['id'];
    }

    // Insert admin assignments
    $assignments = [
        [$gasaboAdminId, $kimironkoSectorId, $superAdminId, 'Assigned to manage Kimironko sector in Gasabo district'],
        [$nyarugengeAdminId, $kimisagaraSectorId, $superAdminId, 'Assigned to manage Kimisagara sector in Nyarugenge district'],
    ];

    foreach ($assignments as $assignment) {
        $checkAssignment = $connection->prepare("
            SELECT id FROM admin_assignments
            WHERE admin_id = ? AND sector_id = ? AND is_active = 1
        ");
        $checkAssignment->bind_param('ii', $assignment[0], $assignment[1]);
        $checkAssignment->execute();

        if ($checkAssignment->get_result()->num_rows == 0) {
            $insertAssignment = $connection->prepare("
                INSERT INTO admin_assignments (admin_id, sector_id, assigned_by, notes, is_active)
                VALUES (?, ?, ?, ?, 1)
            ");
            $insertAssignment->bind_param('iiss', $assignment[0], $assignment[1], $assignment[2], $assignment[3]);
            $insertAssignment->execute();
            echo "<p>✅ Admin assignment created</p>\n";
        } else {
            echo "<p>ℹ️ Admin assignment already exists</p>\n";
        }
    }

    // Create some test residents for each sector
    echo "<h2>Creating Test Residents...</h2>\n";

    $testResidents = [
        // Kimironko sector residents
        ['RES001', 'Alice', 'Uwimana', 'alice.uwimana@example.com', '+250788111001', 'Kimironko'],
        ['RES002', 'Bob', 'Mugisha', 'bob.mugisha@example.com', '+250788111002', 'Kimironko'],
        ['RES003', 'Claire', 'Ingabire', 'claire.ingabire@example.com', '+250788111003', 'Kimironko'],

        // Kimisagara sector residents
        ['RES004', 'David', 'Nkurunziza', 'david.nkurunziza@example.com', '+250788111004', 'Kimisagara'],
        ['RES005', 'Emma', 'Mukamana', 'emma.mukamana@example.com', '+250788111005', 'Kimisagara'],
        ['RES006', 'Frank', 'Habimana', 'frank.habimana@example.com', '+250788111006', 'Kimisagara'],
    ];

    foreach ($testResidents as $resident) {
        $checkResident = $connection->prepare("SELECT id FROM users WHERE email = ?");
        $checkResident->bind_param('s', $resident[3]);
        $checkResident->execute();

        if ($checkResident->get_result()->num_rows == 0) {
            // Get sector ID for the resident
            $sectorResult = $connection->query("SELECT id FROM sectors WHERE name = '{$resident[5]}'");
            $sectorId     = $sectorResult->fetch_assoc()['id'];

            // Get district ID from sector
            $districtResult = $connection->query("SELECT district_id FROM sectors WHERE id = $sectorId");
            $districtId     = $districtResult->fetch_assoc()['district_id'];

            // Get province ID from district
            $provinceResult = $connection->query("SELECT province_id FROM districts WHERE id = $districtId");
            $provinceId     = $provinceResult->fetch_assoc()['province_id'];

            $insertResident = $connection->prepare("
                INSERT INTO users (national_id, first_name, last_name, email, phone, password,
                                  cell_id, sector_id, district_id, province_id, role, status, gender, date_of_birth)
                VALUES (?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, 'resident', 'active', 'male', '1990-01-01')
            ");
            $password = password_hash('resident123', PASSWORD_DEFAULT);
            $insertResident->bind_param('ssssssiii',
                $resident[0], $resident[1], $resident[2], $resident[3], $resident[4], $password,
                $sectorId, $districtId, $provinceId
            );
            $insertResident->execute();
            echo "<p>✅ Created resident: {$resident[1]} {$resident[2]} in {$resident[5]} sector</p>\n";
        }
    }

    echo "<h2>🎉 Setup Complete!</h2>\n";
    echo "<h3>Test Accounts Created:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>Super Admin:</strong> super@umuganda.rw / super123</li>\n";
    echo "<li><strong>Gasabo Admin:</strong> admin.gasabo@umuganda.rw / admin123 (manages Kimironko sector)</li>\n";
    echo "<li><strong>Nyarugenge Admin:</strong> admin.nyarugenge@umuganda.rw / admin123 (manages Kimisagara sector)</li>\n";
    echo "<li><strong>Residents:</strong> [email]@example.com / resident123</li>\n";
    echo "</ul>\n";

    echo "<p><a href='public/login.php'>Go to Login Page</a></p>\n";
    echo "<p><a href='public/dashboard/admin/index.php'>Go to Admin Dashboard</a></p>\n";

} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Error details: " . $e->getTraceAsString() . "</p>\n";
}
