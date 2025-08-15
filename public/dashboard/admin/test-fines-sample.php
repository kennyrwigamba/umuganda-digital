<?php
require_once __DIR__ . '/../../../config/db.php';

try {
    $database = new Database();
    $db       = $database->getConnection();

    // Get some sample fine IDs
    $result = $db->query("SELECT id, user_id, amount, status FROM fines LIMIT 5");

    if ($result) {
        echo "Sample fines:\n";
        while ($row = $result->fetch_assoc()) {
            echo "ID: {$row['id']}, User: {$row['user_id']}, Amount: {$row['amount']}, Status: {$row['status']}\n";
        }
    } else {
        echo "Query failed: " . $db->error . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
