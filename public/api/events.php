<?php
/**
 * Events API - Handle CRUD operations for umuganda events
 */

// Turn off error display to prevent HTML output in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();

// Check if user is logged in and is admin
if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Include required files
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/models/User.php';

// Use the global database instance
global $db;

// Set content type to JSON first
header('Content-Type: application/json');

try {
    $connection = $db->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            handleCreateEvent($connection);
            break;
        case 'PUT':
            handleUpdateEvent($connection);
            break;
        case 'DELETE':
            handleDeleteEvent($connection);
            break;
        case 'GET':
            handleGetEvent($connection);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function handleCreateEvent($connection)
{
    // Validate required fields
    $requiredFields = ['title', 'event_date', 'start_time', 'location'];
    foreach ($requiredFields as $field) {
        if (! isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }

    // Get admin info
    $adminId = $_SESSION['user_id'];

    // Get admin's sector assignment
    $adminSectorQuery = "
    SELECT s.id as sector_id
    FROM admin_assignments aa
    JOIN sectors s ON aa.sector_id = s.id
    WHERE aa.admin_id = ? AND aa.is_active = 1
    LIMIT 1";

    $stmt = $connection->prepare($adminSectorQuery);
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $sectorResult = $stmt->get_result()->fetch_assoc();

    if (! $sectorResult) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin is not assigned to any sector. Please contact super admin.']);
        return;
    }

    $sectorId = $sectorResult['sector_id'];

    // Sanitize and validate input
    $title             = trim($_POST['title']);
    $description       = isset($_POST['description']) ? trim($_POST['description']) : null;
    $eventDate         = $_POST['event_date'];
    $startTime         = $_POST['start_time'];
    $endTime           = isset($_POST['end_time']) && ! empty($_POST['end_time']) ? $_POST['end_time'] : null;
    $location          = trim($_POST['location']);
    $googleMapLocation = isset($_POST['google_map_location']) ? trim($_POST['google_map_location']) : null;
    $maxParticipants   = isset($_POST['max_participants']) && ! empty($_POST['max_participants']) ? intval($_POST['max_participants']) : null;

    // Validate date is not in the past
    if (strtotime($eventDate) < strtotime('today')) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Event date cannot be in the past']);
        return;
    }

    // Validate time format and logic
    if ($endTime && strtotime($endTime) <= strtotime($startTime)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'End time must be after start time']);
        return;
    }

    // Insert event into database
    $insertQuery = "
    INSERT INTO umuganda_events
    (title, description, event_date, start_time, end_time, location, sector_id, max_participants, status, created_by, google_map_location)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', ?, ?)";

    $stmt = $connection->prepare($insertQuery);

    // Check if prepare was successful
    if (! $stmt) {
        error_log("Prepare failed: " . $connection->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database prepare error']);
        return;
    }

    // Convert integers to strings to avoid binding issues
    $sectorIdStr        = strval($sectorId);
    $maxParticipantsStr = strval($maxParticipants);
    $adminIdStr         = strval($adminId);

    $stmt->bind_param('ssssssssss', $title, $description, $eventDate, $startTime, $endTime, $location, $sectorIdStr, $maxParticipantsStr, $adminIdStr, $googleMapLocation);

    if ($stmt->execute()) {
        $eventId = $connection->insert_id;

        // Get the created event details
        $getEventQuery = "SELECT * FROM umuganda_events WHERE id = ?";
        $getStmt       = $connection->prepare($getEventQuery);
        $getStmt->bind_param('i', $eventId);
        $getStmt->execute();
        $eventData = $getStmt->get_result()->fetch_assoc();

        echo json_encode([
            'success' => true,
            'message' => 'Event created successfully',
            'event'   => $eventData,
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create event. Please try again.']);
    }
}

function handleUpdateEvent($connection)
{
    // Get event ID from URL parameter
    $eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (! $eventId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Event ID is required']);
        return;
    }

    // Parse PUT data
    parse_str(file_get_contents("php://input"), $_PUT);

    // Validate required fields
    $requiredFields = ['title', 'event_date', 'start_time', 'location'];
    foreach ($requiredFields as $field) {
        if (! isset($_PUT[$field]) || empty(trim($_PUT[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }

    // Check if event exists and admin has permission
    $adminId         = $_SESSION['user_id'];
    $checkEventQuery = "
    SELECT * FROM umuganda_events
    WHERE id = ? AND (created_by = ? OR sector_id IN (
        SELECT sector_id FROM admin_assignments WHERE admin_id = ? AND is_active = 1
    ))";

    $stmt = $connection->prepare($checkEventQuery);
    $stmt->bind_param('iii', $eventId, $adminId, $adminId);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    if (! $event) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Event not found or access denied']);
        return;
    }

    // Sanitize input
    $title             = trim($_PUT['title']);
    $description       = isset($_PUT['description']) ? trim($_PUT['description']) : $event['description'];
    $eventDate         = $_PUT['event_date'];
    $startTime         = $_PUT['start_time'];
    $endTime           = isset($_PUT['end_time']) && ! empty($_PUT['end_time']) ? $_PUT['end_time'] : $event['end_time'];
    $location          = trim($_PUT['location']);
    $googleMapLocation = isset($_PUT['google_map_location']) ? trim($_PUT['google_map_location']) : $event['google_map_location'];
    $maxParticipants   = isset($_PUT['max_participants']) && ! empty($_PUT['max_participants']) ? intval($_PUT['max_participants']) : $event['max_participants'];

    // Update event
    $updateQuery = "
    UPDATE umuganda_events
    SET title = ?, description = ?, event_date = ?, start_time = ?, end_time = ?,
        location = ?, google_map_location = ?, max_participants = ?, updated_at = NOW()
    WHERE id = ?";

    $stmt = $connection->prepare($updateQuery);

    // Convert to strings to avoid binding issues
    $maxParticipantsStr = strval($maxParticipants);
    $eventIdStr         = strval($eventId);

    $stmt->bind_param('sssssssss', $title, $description, $eventDate, $startTime, $endTime, $location, $googleMapLocation, $maxParticipantsStr, $eventIdStr);if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update event: ' . $connection->error]);
    }
}

function handleDeleteEvent($connection)
{
    // Get event ID from URL parameter
    $eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (! $eventId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Event ID is required']);
        return;
    }

    // Check if event exists and admin has permission
    $adminId         = $_SESSION['user_id'];
    $checkEventQuery = "
    SELECT * FROM umuganda_events
    WHERE id = ? AND (created_by = ? OR sector_id IN (
        SELECT sector_id FROM admin_assignments WHERE admin_id = ? AND is_active = 1
    ))";

    $stmt = $connection->prepare($checkEventQuery);
    $stmt->bind_param('iii', $eventId, $adminId, $adminId);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    if (! $event) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Event not found or access denied']);
        return;
    }

    // Check if event has attendance records
    $attendanceQuery = "SELECT COUNT(*) as count FROM attendance WHERE event_id = ?";
    $stmt            = $connection->prepare($attendanceQuery);
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $attendanceCount = $stmt->get_result()->fetch_assoc()['count'];

    if ($attendanceCount > 0) {
        // If there are attendance records, mark as cancelled instead of deleting
        $cancelQuery = "UPDATE umuganda_events SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
        $stmt        = $connection->prepare($cancelQuery);
        $stmt->bind_param('i', $eventId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Event cancelled successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to cancel event']);
        }
    } else {
        // No attendance records, safe to delete
        $deleteQuery = "DELETE FROM umuganda_events WHERE id = ?";
        $stmt        = $connection->prepare($deleteQuery);
        $stmt->bind_param('i', $eventId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete event']);
        }
    }
}

function handleGetEvent($connection)
{
    // Get event ID from URL parameter
    $eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (! $eventId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Event ID is required']);
        return;
    }

    // Get event details
    $eventQuery = "
    SELECT e.*,
           COALESCE(attendance_stats.total_registered, 0) as total_registered,
           COALESCE(attendance_stats.total_attended, 0) as total_attended,
           u.first_name, u.last_name
    FROM umuganda_events e
    LEFT JOIN (
        SELECT
            event_id,
            COUNT(*) as total_registered,
            SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) as total_attended
        FROM attendance
        GROUP BY event_id
    ) attendance_stats ON e.id = attendance_stats.event_id
    LEFT JOIN users u ON e.created_by = u.id
    WHERE e.id = ?";

    $stmt = $connection->prepare($eventQuery);
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    if ($event) {
        echo json_encode(['success' => true, 'event' => $event]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Event not found']);
    }
}
