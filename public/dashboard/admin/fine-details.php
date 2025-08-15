<?php
session_start();
require_once __DIR__ . '/../../../config/db.php';

// Check if user is logged in and is admin
if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("HTTP/1.1 401 Unauthorized");
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if ID is provided
if (! isset($_GET['id']) || empty($_GET['id'])) {
    header("HTTP/1.1 400 Bad Request");
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Fine ID is required']);
    exit();
}

$fine_id = intval($_GET['id']);

try {
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

    $stmt = $db->prepare($query);
    if (! $stmt) {
        throw new Exception("Failed to prepare statement: " . $db->error);
    }

    $stmt->bind_param('i', $fine_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $fine   = $result->fetch_assoc();

    if (! $fine) {
        header("HTTP/1.1 404 Not Found");
        header("Content-Type: application/json");
        echo json_encode(['success' => false, 'message' => 'Fine not found']);
        exit();
    }

    // Format dates for display
    if ($fine['due_date']) {
        $fine['due_date'] = date('Y-m-d', strtotime($fine['due_date']));
    }

    if ($fine['created_at']) {
        $fine['created_at'] = date('Y-m-d H:i:s', strtotime($fine['created_at']));
    }

    if ($fine['payment_date']) {
        $fine['payment_date'] = date('Y-m-d H:i:s', strtotime($fine['payment_date']));
    }

    if ($fine['event_date']) {
        $fine['event_date'] = date('Y-m-d', strtotime($fine['event_date']));
    }

    $stmt->close();

    header("Content-Type: application/json");
    echo json_encode([
        'success' => true,
        'fine'    => $fine,
    ]);

} catch (Exception $e) {
    error_log("Error in fine-details.php: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    header("Content-Type: application/json");
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching fine details',
    ]);
}
