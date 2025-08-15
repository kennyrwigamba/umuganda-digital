<?php
/**
 * Get unread notification count API endpoint
 * GET /api/notifications/unread-count.php
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
    errorResponse('Authentication required', 401);
}

$userId = $_SESSION['user_id'];

try {
    $connection = $db->getConnection();
    $notificationRepo = new NotificationRepository($connection);
    $preferenceRepo = new PreferenceRepository($connection);
    $notificationService = new NotificationService($notificationRepo, $preferenceRepo);
    
    // Get unread count
    $unreadCount = $notificationService->getUnreadCount($userId);
    
    successResponse(['unread_count' => $unreadCount], 'Unread count retrieved successfully');

} catch (Exception $e) {
    error_log("Error in notifications/unread-count.php: " . $e->getMessage());
    errorResponse('Failed to retrieve unread count', 500);
}
?>
