<?php
/**
 * Fines API Endpoint
 * Handles CRUD operations for fines management
 */

session_start();
header('Content-Type: application/json');

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
$connection = $db->getConnection();

$user = new User();

// Get current admin info and sector
$adminId   = $_SESSION['user_id'];
$adminInfo = $user->findById($adminId);

if (! $adminInfo) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid session']);
    exit;
}

// Get admin's assigned sector
$adminSectorQuery = "
SELECT s.name as sector_name, s.id as sector_id
FROM admin_assignments aa
JOIN sectors s ON aa.sector_id = s.id
WHERE aa.admin_id = ? AND aa.is_active = 1
LIMIT 1";

$stmt = $connection->prepare($adminSectorQuery);
$stmt->bind_param('i', $adminId);
$stmt->execute();
$sectorResult = $stmt->get_result()->fetch_assoc();

if ($sectorResult) {
    $adminSectorId = $sectorResult['sector_id'];
} else {
    // Fallback
    if (isset($adminInfo['sector_id']) && $adminInfo['sector_id']) {
        $adminSectorId = $adminInfo['sector_id'];
    } else {
        $adminSectorId = 1; // Default for testing
    }
}

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            handlePost();
            break;
        case 'GET':
            handleGet();
            break;
        case 'PUT':
            handlePut();
            break;
        case 'DELETE':
            handleDelete();
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

function handlePost()
{
    global $connection, $adminId, $adminSectorId;

    $action = $_POST['action'] ?? 'add';

    switch ($action) {
        case 'add':
            handleAddFine();
            break;
        case 'mark_paid':
            handleMarkPaid();
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
}

function handleAddFine()
{
    global $connection, $adminId, $adminSectorId;

    // Validate required fields
    $required = ['resident_id', 'reason', 'amount', 'due_date'];
    foreach ($required as $field) {
        if (! isset($_POST[$field]) || empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field $field is required"]);
            return;
        }
    }

    $residentId        = (int) $_POST['resident_id'];
    $reason            = $_POST['reason'];
    $amount            = (float) $_POST['amount'];
    $dueDate           = $_POST['due_date'];
    $reasonDescription = $_POST['reason_description'] ?? '';

    // Validate resident belongs to admin's sector
    $residentCheckQuery = "SELECT id, sector_id FROM users WHERE id = ? AND role = 'resident'";
    $stmt               = $connection->prepare($residentCheckQuery);
    $stmt->bind_param('i', $residentId);
    $stmt->execute();
    $residentResult = $stmt->get_result()->fetch_assoc();

    if (! $residentResult) {
        echo json_encode(['success' => false, 'message' => 'Invalid resident selected']);
        return;
    }

    if ($residentResult['sector_id'] != $adminSectorId) {
        echo json_encode(['success' => false, 'message' => 'You can only add fines for residents in your sector']);
        return;
    }

    // Validate fine reason
    $validReasons = ['absence', 'late_arrival', 'early_departure', 'other'];
    if (! in_array($reason, $validReasons)) {
        echo json_encode(['success' => false, 'message' => 'Invalid fine reason']);
        return;
    }

    // Validate amount
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
        return;
    }

    // Validate due date
    if (strtotime($dueDate) < strtotime('today')) {
        echo json_encode(['success' => false, 'message' => 'Due date cannot be in the past']);
        return;
    }

    // Get a recent event ID for the fine (this could be improved to let admin select specific event)
    $eventQuery  = "SELECT id FROM umuganda_events ORDER BY event_date DESC LIMIT 1";
    $eventResult = $connection->query($eventQuery);
    $eventId     = $eventResult->fetch_assoc()['id'] ?? 1; // Default to 1 if no events

    // Insert fine
    $insertQuery = "
    INSERT INTO fines (user_id, event_id, amount, reason, reason_description, status, due_date, created_by, created_at)
    VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, NOW())";

    $stmt = $connection->prepare($insertQuery);
    $stmt->bind_param('iidsssi', $residentId, $eventId, $amount, $reason, $reasonDescription, $dueDate, $adminId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Fine added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add fine']);
    }
}

function handleMarkPaid()
{
    global $connection, $adminId, $adminSectorId;

    if (! isset($_POST['fine_id']) || empty($_POST['fine_id'])) {
        echo json_encode(['success' => false, 'message' => 'Fine ID is required']);
        return;
    }

    $fineId = (int) $_POST['fine_id'];

    // Verify fine belongs to admin's sector and is pending
    $fineCheckQuery = "
    SELECT f.id, f.status, u.sector_id
    FROM fines f
    JOIN users u ON f.user_id = u.id
    WHERE f.id = ?";

    $stmt = $connection->prepare($fineCheckQuery);
    $stmt->bind_param('i', $fineId);
    $stmt->execute();
    $fineResult = $stmt->get_result()->fetch_assoc();

    if (! $fineResult) {
        echo json_encode(['success' => false, 'message' => 'Fine not found']);
        return;
    }

    if ($fineResult['sector_id'] != $adminSectorId) {
        echo json_encode(['success' => false, 'message' => 'You can only manage fines in your sector']);
        return;
    }

    if ($fineResult['status'] === 'paid') {
        echo json_encode(['success' => false, 'message' => 'Fine is already paid']);
        return;
    }

    // Update fine to paid
    $updateQuery = "
    UPDATE fines
    SET status = 'paid', paid_date = NOW(), payment_method = 'cash'
    WHERE id = ?";

    $stmt = $connection->prepare($updateQuery);
    $stmt->bind_param('i', $fineId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Fine marked as paid']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update fine']);
    }
}

function handleGet()
{
    global $connection, $adminSectorId;

    // This could be used for fetching fine details or lists
    // For now, just return success
    echo json_encode(['success' => true, 'message' => 'GET endpoint ready']);
}

function handlePut()
{
    global $connection, $adminId, $adminSectorId;

    // Handle fine updates (edit fine)
    parse_str(file_get_contents("php://input"), $_PUT);

    if (! isset($_PUT['fine_id']) || empty($_PUT['fine_id'])) {
        echo json_encode(['success' => false, 'message' => 'Fine ID is required']);
        return;
    }

    $fineId = (int) $_PUT['fine_id'];

    // Verify fine belongs to admin's sector
    $fineCheckQuery = "
    SELECT f.id, u.sector_id
    FROM fines f
    JOIN users u ON f.user_id = u.id
    WHERE f.id = ?";

    $stmt = $connection->prepare($fineCheckQuery);
    $stmt->bind_param('i', $fineId);
    $stmt->execute();
    $fineResult = $stmt->get_result()->fetch_assoc();

    if (! $fineResult) {
        echo json_encode(['success' => false, 'message' => 'Fine not found']);
        return;
    }

    if ($fineResult['sector_id'] != $adminSectorId) {
        echo json_encode(['success' => false, 'message' => 'You can only edit fines in your sector']);
        return;
    }

    // Build update query based on provided fields
    $updateFields = [];
    $params       = [];
    $paramTypes   = '';

    if (isset($_PUT['amount']) && ! empty($_PUT['amount'])) {
        $updateFields[] = 'amount = ?';
        $params[]       = (float) $_PUT['amount'];
        $paramTypes .= 'd';
    }

    if (isset($_PUT['reason']) && ! empty($_PUT['reason'])) {
        $updateFields[] = 'reason = ?';
        $params[]       = $_PUT['reason'];
        $paramTypes .= 's';
    }

    if (isset($_PUT['reason_description'])) {
        $updateFields[] = 'reason_description = ?';
        $params[]       = $_PUT['reason_description'];
        $paramTypes .= 's';
    }

    if (isset($_PUT['due_date']) && ! empty($_PUT['due_date'])) {
        $updateFields[] = 'due_date = ?';
        $params[]       = $_PUT['due_date'];
        $paramTypes .= 's';
    }

    if (empty($updateFields)) {
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        return;
    }

    $updateFields[] = 'updated_at = NOW()';
    $params[]       = $fineId;
    $paramTypes .= 'i';

    $updateQuery = "UPDATE fines SET " . implode(', ', $updateFields) . " WHERE id = ?";

    $stmt = $connection->prepare($updateQuery);
    $stmt->bind_param($paramTypes, ...$params);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Fine updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update fine']);
    }
}

function handleDelete()
{
    global $connection, $adminSectorId;

    parse_str(file_get_contents("php://input"), $_DELETE);

    if (! isset($_DELETE['fine_id']) || empty($_DELETE['fine_id'])) {
        echo json_encode(['success' => false, 'message' => 'Fine ID is required']);
        return;
    }

    $fineId = (int) $_DELETE['fine_id'];

    // Verify fine belongs to admin's sector
    $fineCheckQuery = "
    SELECT f.id, f.status, u.sector_id
    FROM fines f
    JOIN users u ON f.user_id = u.id
    WHERE f.id = ?";

    $stmt = $connection->prepare($fineCheckQuery);
    $stmt->bind_param('i', $fineId);
    $stmt->execute();
    $fineResult = $stmt->get_result()->fetch_assoc();

    if (! $fineResult) {
        echo json_encode(['success' => false, 'message' => 'Fine not found']);
        return;
    }

    if ($fineResult['sector_id'] != $adminSectorId) {
        echo json_encode(['success' => false, 'message' => 'You can only delete fines in your sector']);
        return;
    }

    // Prevent deletion of paid fines
    if ($fineResult['status'] === 'paid') {
        echo json_encode(['success' => false, 'message' => 'Cannot delete paid fines']);
        return;
    }

    // Delete fine
    $deleteQuery = "DELETE FROM fines WHERE id = ?";
    $stmt        = $connection->prepare($deleteQuery);
    $stmt->bind_param('i', $fineId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Fine deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete fine']);
    }
}
