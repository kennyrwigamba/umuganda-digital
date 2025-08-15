<?php
require_once 'config/db.php';

try {
    $database = new Database();
    $conn     = $database->getConnection();

    echo "=== Checking user_notification_preferences table structure ===\n";
    $result = $conn->query("DESCRIBE user_notification_preferences");
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']} - {$row['Default']}\n";
    }

    echo "\n=== Sample data ===\n";
    $result = $conn->query("SELECT * FROM user_notification_preferences LIMIT 5");
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
