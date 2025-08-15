<?php
require_once 'config/db.php';

try {
    $database = new Database();
    $conn     = $database->getConnection();

    $sql = file_get_contents('create_push_subscriptions_table.sql');

    if ($conn->query($sql)) {
        echo "Push subscriptions table created successfully\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
