<?php
// Simulate session for testing
session_start();
$_SESSION['user_id']   = 1;
$_SESSION['user_role'] = 'admin';
$_GET['id']            = 1; // Test with fine ID 1

// Include the fine-details.php logic
require_once __DIR__ . '/../../../config/db.php';

try {
    $fine_id = intval($_GET['id']);
    echo "Testing with fine ID: $fine_id\n";

    $database = new Database();
    $db       = $database->getConnection();

    // Query to get fine details with related information
    $query = "SELECT
                f.id,
                f.user_id,
                f.event_id,
                f.amount,
                f.reason,
                f.reason_description,
                f.status,
                f.due_date,
                f.paid_date as payment_date,
                f.payment_method,
                f.payment_reference,
                f.created_at,
                f.created_by,
                u.first_name,
                u.last_name,
                u.email,
                e.title as event_title,
                e.event_date,
                admin.first_name as created_by_name
              FROM fines f
              LEFT JOIN users u ON f.user_id = u.id
              LEFT JOIN umuganda_events e ON f.event_id = e.id
              LEFT JOIN users admin ON f.created_by = admin.id
              WHERE f.id = ?";

    echo "Preparing query...\n";
    $stmt = $db->prepare($query);
    if (! $stmt) {
        throw new Exception("Failed to prepare statement: " . $db->error);
    }

    echo "Binding parameters...\n";
    $stmt->bind_param('i', $fine_id);

    echo "Executing query...\n";
    $result = $stmt->execute();
    if (! $result) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    echo "Getting result...\n";
    $result = $stmt->get_result();
    $fine   = $result->fetch_assoc();

    if (! $fine) {
        echo "Fine not found\n";
    } else {
        echo "Fine found:\n";
        print_r($fine);
    }

    $stmt->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
