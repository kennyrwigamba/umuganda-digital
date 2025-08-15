<?php
/**
 * Mark notifications as read API endpoint
 * POST /api/notifications/mark-read.php
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
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    errorResponse('Authentication required', 401);
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        errorResponse('Invalid JSON input', 400);
    }
    
    $connection = $db->getConnection();
    $notificationRepo = new NotificationRepository($connection);
    $preferenceRepo = new PreferenceRepository($connection);
    $notificationService = new NotificationService($notificationRepo, $preferenceRepo);
    
    // Check if marking all as read
    if (isset($input['all']) && $input['all'] === true) {
        // Get all notification IDs for user
        $allNotifications = $notificationService->getUserNotifications($userId, 'unread', 1, 1000);
        $allIds = array_column($allNotifications['data'] ?? [], 'id');
        
        if (!empty($allIds)) {
            $result = $notificationService->markAsRead($userId, $allIds);
            successResponse(['marked_count' => count($allIds)], 'All notifications marked as read');
        } else {
            successResponse(['marked_count' => 0], 'No notifications to mark as read');
        }
    }
    
    // Check if marking specific notifications
    if (isset($input['ids']) && is_array($input['ids'])) {
        $notificationIds = array_map('intval', $input['ids']);
        $result = $notificationService->markAsRead($userId, $notificationIds);
        successResponse(['marked_count' => count($notificationIds)], 'Notifications marked as read');
    }
    
    errorResponse('Invalid request. Provide either "all": true or "ids": [...]', 400);

} catch (Exception $e) {
    error_log("Error in notifications/mark-read.php: " . $e->getMessage());
    errorResponse('Failed to mark notifications as read', 500);
}
?>
