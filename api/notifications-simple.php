<?php
require_once 'config/db.php';
require_once 'vendor/autoload.php';

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

    // Get request method and path
    $method = $_SERVER['REQUEST_METHOD'];
    $path   = $_GET['path'] ?? '';
    $userId = $_GET['user_id'] ?? null;

    // Route handling
    switch ($method) {
        case 'GET':
            handleGetRequest($path, $userId, $conn);
            break;

        case 'POST':
            handlePostRequest($path, $conn);
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
function handleGetRequest($path, $userId, $conn)
{
    switch ($path) {
        case 'notifications':
            if (! $userId) {
                sendError('user_id parameter required', 400);
                return;
            }

            $page   = (int) ($_GET['page'] ?? 1);
            $limit  = (int) ($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;

            // Get notifications for user
            $stmt = $conn->prepare(
                "SELECT n.*,
                        nr.read_at,
                        CASE WHEN nr.id IS NULL THEN 0 ELSE 1 END as is_read
                 FROM notifications n
                 LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = ?
                 WHERE n.user_id = ? OR n.user_id IS NULL
                 ORDER BY n.created_at DESC
                 LIMIT ? OFFSET ?"
            );

            $stmt->bind_param('iiii', $userId, $userId, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();

            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }

            // Get total count
            $countStmt = $conn->prepare(
                "SELECT COUNT(*) as count
                 FROM notifications n
                 WHERE n.user_id = ? OR n.user_id IS NULL"
            );
            $countStmt->bind_param('i', $userId);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $totalCount  = $countResult->fetch_assoc()['count'];

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

            $stmt = $conn->prepare(
                "SELECT COUNT(*) as count
                 FROM notifications n
                 LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = ?
                 WHERE (n.user_id = ? OR n.user_id IS NULL) AND nr.id IS NULL"
            );

            $stmt->bind_param('ii', $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $count  = $result->fetch_assoc()['count'];

            sendSuccess(['unread_count' => $count]);
            break;

        default:
            sendError('Endpoint not found', 404);
    }
}

/**
 * Handle POST requests
 */
function handlePostRequest($path, $conn)
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (! $input) {
        sendError('Invalid JSON data', 400);
        return;
    }

    switch ($path) {
        case 'notifications/mark-read':
            if (! isset($input['notification_id']) || ! isset($input['user_id'])) {
                sendError('notification_id and user_id are required', 400);
                return;
            }

            $stmt = $conn->prepare(
                "INSERT IGNORE INTO notification_reads (notification_id, user_id, read_at)
                 VALUES (?, ?, NOW())"
            );

            $stmt->bind_param('ii', $input['notification_id'], $input['user_id']);
            $success = $stmt->execute();

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

            // Get all unread notifications for the user
            $stmt = $conn->prepare(
                "INSERT IGNORE INTO notification_reads (notification_id, user_id, read_at)
                 SELECT n.id, ?, NOW()
                 FROM notifications n
                 LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = ?
                 WHERE (n.user_id = ? OR n.user_id IS NULL) AND nr.id IS NULL"
            );

            $stmt->bind_param('iii', $input['user_id'], $input['user_id'], $input['user_id']);
            $success = $stmt->execute();
            $count   = $stmt->affected_rows;

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

            // Insert or update the subscription
            $stmt = $conn->prepare(
                "INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth, is_active)
                 VALUES (?, ?, ?, ?, 1)
                 ON DUPLICATE KEY UPDATE
                    p256dh = VALUES(p256dh),
                    auth = VALUES(auth),
                    is_active = 1,
                    updated_at = CURRENT_TIMESTAMP"
            );

            $success = $stmt->bind_param('isss', $input['user_id'], $input['endpoint'], $input['p256dh'], $input['auth']);
            $success = $stmt->execute();

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
