<?php
require_once __DIR__ . '/../../../config/db.php';

try {
    $database = new Database();
    $db       = $database->getConnection();

    // Show all tables
    $result = $db->query("SHOW TABLES");

    if ($result) {
        echo "Available tables:\n";
        while ($row = $result->fetch_array()) {
            echo "- " . $row[0] . "\n";
        }
    } else {
        echo "Failed to show tables: " . $db->error . "\n";
    }

    // Check the structure of fines table
    echo "\nFines table structure:\n";
    $result = $db->query("DESCRIBE fines");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['Field']} ({$row['Type']})\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
