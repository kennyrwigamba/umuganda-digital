<?php
require_once 'config/db.php';

try {
    $database = new Database();
    $conn     = $database->getConnection();

    $result = $conn->query('DESCRIBE push_subscriptions');

    if ($result) {
        echo "Push subscriptions table structure:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "Table does not exist: " . $conn->error . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
