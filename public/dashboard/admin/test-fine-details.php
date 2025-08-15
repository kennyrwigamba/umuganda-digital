<?php
session_start();
echo "Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "\n";
echo "Session user_role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Not set') . "\n";
echo "GET id: " . (isset($_GET['id']) ? $_GET['id'] : 'Not set') . "\n";

require_once __DIR__ . '/../../../config/db.php';

try {
    $database = new Database();
    $db       = $database->getConnection();
    echo "Database connection: Success\n";

    // Test a simple query
    $result = $db->query("SELECT COUNT(*) as count FROM fines");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Fines count: " . $row['count'] . "\n";
    } else {
        echo "Query failed: " . $db->error . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
