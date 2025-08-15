<?php
/**
 * List user notifications API endpoint
 * GET /api/notifications/list.php
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/helpers/functions.php';
require_once __DIR__ . '/../../src/helpers/env.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use UmugandaDigital\Services\NotificationService;
use UmugandaDigital\Repositories\NotificationRepository;
use UmugandaDigital\Repositories\PreferenceRepository;

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    errorResponse('Authentication required', 401);
}

$userId = $_SESSION['user_id'];

try {
    // Get query parameters
    $status = $_GET['status'] ?? 'all'; // 'unread', 'all'
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 20)));

    // Validate status parameter
    if (!in_array($status, ['all', 'unread'])) {
        errorResponse('Invalid status parameter. Use "all" or "unread"', 400);
    }

    $connection = $db->getConnection();
    $notificationRepo = new NotificationRepository($connection);
    $preferenceRepo = new PreferenceRepository($connection);
    $notificationService = new NotificationService($notificationRepo, $preferenceRepo);
    
    // Get notifications
    $notifications = $notificationService->getUserNotifications($userId, $status, $page, $perPage);
    
    // Get total unread count
    $unreadCount = $notificationService->getUnreadCount($userId);
    
    // Format response
    $response = [
        'notifications' => $notifications['data'] ?? [],
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $notifications['total'] ?? 0,
            'total_pages' => $notifications['total_pages'] ?? 0
        ],
        'unread_count' => $unreadCount
    ];
    
    successResponse($response, 'Notifications retrieved successfully');

} catch (Exception $e) {
    error_log("Error in notifications/list.php: " . $e->getMessage());
    errorResponse('Failed to retrieve notifications', 500);
}
?>
