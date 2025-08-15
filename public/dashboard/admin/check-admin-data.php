<?php
require_once __DIR__ . '/../../../config/db.php';

try {
    $database   = new Database();
    $connection = $database->getConnection();

    // Check admin_assignments table
    echo "Admin assignments:\n";
    $result = $connection->query("SELECT * FROM admin_assignments LIMIT 5");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
    } else {
        echo "No admin assignments found\n";
    }

    // Check if table exists and structure
    echo "\nAdmin assignments table structure:\n";
    $result = $connection->query("DESCRIBE admin_assignments");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['Field']} ({$row['Type']})\n";
        }
    } else {
        echo "Table doesn't exist: " . $connection->error . "\n";
    }

    // Check users table for admin users
    echo "\nAdmin users:\n";
    $result = $connection->query("SELECT id, first_name, last_name, user_role, sector_id FROM users WHERE user_role = 'admin' LIMIT 5");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
    } else {
        echo "No admin users found\n";
    }

    // Check sectors
    echo "\nSectors:\n";
    $result = $connection->query("SELECT id, name, district_id FROM sectors LIMIT 5");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
    } else {
        echo "No sectors found\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
