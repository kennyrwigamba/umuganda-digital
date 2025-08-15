<?php
require_once 'config/db.php';
require_once 'vendor/autoload.php';

use UmugandaDigital\Repositories\NotificationRepository;
use UmugandaDigital\Repositories\PreferenceRepository;
use UmugandaDigital\Services\NotificationService;

// Enable CORS for API calls
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Initialize database connection
    $database = new Database();
    $conn     = $database->getConnection();

    // Initialize services
    $notificationRepo    = new NotificationRepository($conn);
    $preferenceRepo      = new PreferenceRepository($conn);
    $notificationService = new NotificationService($notificationRepo, $preferenceRepo);

    // Get request method and path
    $method = $_SERVER['REQUEST_METHOD'];
    $path   = $_GET['path'] ?? '';
    $userId = $_GET['user_id'] ?? null;

    // Route handling
    switch ($method) {
        case 'GET':
            handleGetRequest($path, $userId, $notificationRepo, $preferenceRepo);
            break;

        case 'POST':
            handlePostRequest($path, $notificationService, $notificationRepo);
            break;

        case 'PUT':
            handlePutRequest($path, $notificationRepo, $preferenceRepo);
            break;

        default:
            sendError('Method not allowed', 405);
    }

} catch (Exception $e) {
    sendError('Server error: ' . $e->getMessage(), 500);
}

/**
 * Handle GET requests
 */
function handleGetRequest($path, $userId, $notificationRepo, $preferenceRepo)
{
    switch ($path) {
        case 'notifications':
            if (! $userId) {
                sendError('user_id parameter required', 400);
                return;
            }

            $page       = (int) ($_GET['page'] ?? 1);
            $limit      = (int) ($_GET['limit'] ?? 20);
            $type       = $_GET['type'] ?? null;
            $category   = $_GET['category'] ?? null;
            $unreadOnly = isset($_GET['unread_only']) ? (bool) $_GET['unread_only'] : false;

            $notifications = $notificationRepo->getUserNotifications(
                $userId,
                $page,
                $limit,
                $type,
                $category,
                $unreadOnly
            );

            $totalCount = $notificationRepo->getUserNotificationCount(
                $userId,
                $type,
                $category,
                $unreadOnly
            );

            sendSuccess([
                'notifications' => $notifications,
                'pagination'    => [
                    'page'  => $page,
                    'limit' => $limit,
                    'total' => $totalCount,
                    'pages' => ceil($totalCount / $limit),
                ],
            ]);
            break;

        case 'notifications/unread-count':
            if (! $userId) {
                sendError('user_id parameter required', 400);
                return;
            }

            $count = $notificationRepo->getUnreadNotificationCount($userId);
            sendSuccess(['unread_count' => $count]);
            break;

        case 'preferences':
            if (! $userId) {
                sendError('user_id parameter required', 400);
                return;
            }

            $preferences = $preferenceRepo->getUserPreferences($userId);
            sendSuccess(['preferences' => $preferences]);
            break;

        default:
            sendError('Endpoint not found', 404);
    }
}

/**
 * Handle POST requests
 */
function handlePostRequest($path, $notificationService, $notificationRepo)
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (! $input) {
        sendError('Invalid JSON data', 400);
        return;
    }

    switch ($path) {
        case 'notifications':
            $required = ['user_id', 'type', 'title', 'body'];
            foreach ($required as $field) {
                if (! isset($input[$field])) {
                    sendError("Field '$field' is required", 400);
                    return;
                }
            }

            $notificationId = $notificationService->notifyUser(
                $input['user_id'],
                $input['type'],
                $input['title'],
                $input['body'],
                $input['data'] ?? []
            );

            sendSuccess([
                'notification_id' => $notificationId,
                'message'         => 'Notification created successfully',
            ], 201);
            break;

        case 'notifications/mark-read':
            if (! isset($input['notification_id'])) {
                sendError('notification_id is required', 400);
                return;
            }

            $success = $notificationRepo->markAsRead($input['notification_id']);

            if ($success) {
                sendSuccess(['message' => 'Notification marked as read']);
            } else {
                sendError('Failed to mark notification as read', 500);
            }
            break;

        case 'notifications/mark-all-read':
            if (! isset($input['user_id'])) {
                sendError('user_id is required', 400);
                return;
            }

            $count = $notificationRepo->markAllAsRead($input['user_id']);
            sendSuccess([
                'message'      => 'All notifications marked as read',
                'marked_count' => $count,
            ]);
            break;

        case 'push-subscription':
            $required = ['user_id', 'endpoint', 'p256dh', 'auth'];
            foreach ($required as $field) {
                if (! isset($input[$field])) {
                    sendError("Field '$field' is required", 400);
                    return;
                }
            }

            $success = $notificationRepo->savePushSubscription(
                $input['user_id'],
                $input['endpoint'],
                $input['p256dh'],
                $input['auth']
            );

            if ($success) {
                sendSuccess(['message' => 'Push subscription saved successfully'], 201);
            } else {
                sendError('Failed to save push subscription', 500);
            }
            break;

        default:
            sendError('Endpoint not found', 404);
    }
}

/**
 * Handle PUT requests
 */
function handlePutRequest($path, $notificationRepo, $preferenceRepo)
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (! $input) {
        sendError('Invalid JSON data', 400);
        return;
    }

    switch ($path) {
        case 'preferences':
            if (! isset($input['user_id'])) {
                sendError('user_id is required', 400);
                return;
            }

            $success = $preferenceRepo->updateUserPreferences($input['user_id'], $input);

            if ($success) {
                sendSuccess(['message' => 'Preferences updated successfully']);
            } else {
                sendError('Failed to update preferences', 500);
            }
            break;

        default:
            sendError('Endpoint not found', 404);
    }
}

/**
 * Send successful JSON response
 */
function sendSuccess($data, $code = 200)
{
    http_response_code($code);
    echo json_encode([
        'success' => true,
        'data'    => $data,
    ], JSON_PRETTY_PRINT);
    exit;
}

/**
 * Send error JSON response
 */
function sendError($message, $code = 400)
{
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error'   => $message,
    ], JSON_PRETTY_PRINT);
    exit;
}
