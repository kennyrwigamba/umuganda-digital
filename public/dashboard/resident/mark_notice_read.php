<?php
/**
 * Mark Notice as Read Handler
 * AJAX endpoint for marking notices as read
 */

session_start();

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (! isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated',
    ]);
    exit;
}

// Check if user is resident
if ($_SESSION['user_role'] !== 'resident') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied',
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (! $input || ! isset($input['notice_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Notice ID is required',
    ]);
    exit;
}

$noticeId = (int) $input['notice_id'];
$userId   = $_SESSION['user_id'];

try {
    // Include required files
    require_once __DIR__ . '/../../src/models/Notice.php';
    require_once __DIR__ . '/../../src/helpers/functions.php';

    // Initialize model
    $noticeModel = new Notice();

    // Verify notice exists and user can access it
    $notice = $noticeModel->findById($noticeId);

    if (! $notice) {
        echo json_encode([
            'success' => false,
            'message' => 'Notice not found',
        ]);
        exit;
    }

    // Check if notice is published and not expired
    if ($notice['status'] !== 'published') {
        echo json_encode([
            'success' => false,
            'message' => 'Notice is not available',
        ]);
        exit;
    }

    if ($notice['expiry_date'] && strtotime($notice['expiry_date']) < time()) {
        echo json_encode([
            'success' => false,
            'message' => 'Notice has expired',
        ]);
        exit;
    }

    // Mark notice as read
    $result = $noticeModel->markAsRead($noticeId, $userId);

    if ($result) {
        // Log the action
        logActivity("User {$userId} marked notice '{$notice['title']}' as read", 'info');

        echo json_encode([
            'success' => true,
            'message' => 'Notice marked as read successfully',
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to mark notice as read',
        ]);
    }

} catch (Exception $e) {
    error_log("Error marking notice as read: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An internal error occurred',
    ]);
}
