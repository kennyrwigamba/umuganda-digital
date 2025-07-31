<?php
/**
 * Residents API Handler
 * Handles CRUD operations for residents from admin dashboard
 */

session_start();

// Check if user is logged in and is admin
if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Include required files
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/helpers/functions.php';

// Use the global database instance
global $db;
$connection = $db->getConnection();

// Get admin's assigned sector
$adminId          = $_SESSION['user_id'];
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
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Admin not assigned to any sector']);
    exit;
}

$adminSectorId = $sectorResult['sector_id'];

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            if ($action === 'add') {
                handleAddResident($connection, $adminSectorId);
            }
            break;

        case 'PUT':
            if ($action === 'edit') {
                handleEditResident($connection, $adminSectorId);
            }
            break;

        case 'DELETE':
            if ($action === 'delete') {
                handleDeleteResident($connection, $adminSectorId);
            }
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

/**
 * Handle adding a new resident
 */
function handleAddResident($connection, $adminSectorId)
{
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $required = ['first_name', 'last_name', 'email', 'phone', 'national_id', 'cell_id'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }

    // Validate email format
    if (! filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }

    // Check if national ID already exists
    $checkQuery = "SELECT id FROM users WHERE national_id = ?";
    $stmt       = $connection->prepare($checkQuery);
    $stmt->bind_param('s', $input['national_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'National ID already exists']);
        return;
    }

    // Check if email already exists
    $checkQuery = "SELECT id FROM users WHERE email = ?";
    $stmt       = $connection->prepare($checkQuery);
    $stmt->bind_param('s', $input['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        return;
    }

    // Verify cell belongs to admin's sector
    $cellQuery = "SELECT id FROM cells WHERE id = ? AND sector_id = ?";
    $stmt      = $connection->prepare($cellQuery);
    $stmt->bind_param('ii', $input['cell_id'], $adminSectorId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid cell selection']);
        return;
    }

    // Generate default password
    $defaultPassword = password_hash('resident123', PASSWORD_DEFAULT);

    // Insert new resident
    $insertQuery = "
        INSERT INTO users (
            national_id, first_name, last_name, email, phone, password,
            cell_id, sector_id, date_of_birth, gender, role, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, '1990-01-01', 'male', 'resident', 'active')";

    $stmt = $connection->prepare($insertQuery);
    $stmt->bind_param('sssssiis',
        $input['national_id'],
        $input['first_name'],
        $input['last_name'],
        $input['email'],
        $input['phone'],
        $defaultPassword,
        $input['cell_id'],
        $adminSectorId
    );

    if ($stmt->execute()) {
        $newId = $connection->insert_id;
        echo json_encode([
            'success'     => true,
            'message'     => 'Resident added successfully',
            'resident_id' => $newId,
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to add resident']);
    }
}

/**
 * Handle editing a resident
 */
function handleEditResident($connection, $adminSectorId)
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Resident ID is required']);
        return;
    }

    // Verify resident belongs to admin's sector
    $checkQuery = "SELECT id FROM users WHERE id = ? AND sector_id = ? AND role = 'resident'";
    $stmt       = $connection->prepare($checkQuery);
    $stmt->bind_param('ii', $input['id'], $adminSectorId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Resident not found or access denied']);
        return;
    }

    // Validate required fields
    $required = ['first_name', 'last_name', 'email', 'phone', 'cell_id', 'status'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }

    // Validate email format
    if (! filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }

    // Check if email exists for other users
    $checkQuery = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt       = $connection->prepare($checkQuery);
    $stmt->bind_param('si', $input['email'], $input['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email already exists for another user']);
        return;
    }

    // Verify cell belongs to admin's sector
    $cellQuery = "SELECT id FROM cells WHERE id = ? AND sector_id = ?";
    $stmt      = $connection->prepare($cellQuery);
    $stmt->bind_param('ii', $input['cell_id'], $adminSectorId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid cell selection']);
        return;
    }

    // Update resident
    $updateQuery = "
        UPDATE users
        SET first_name = ?, last_name = ?, email = ?, phone = ?,
            cell_id = ?, status = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ? AND sector_id = ?";

    $stmt = $connection->prepare($updateQuery);
    $stmt->bind_param('ssssisii',
        $input['first_name'],
        $input['last_name'],
        $input['email'],
        $input['phone'],
        $input['cell_id'],
        $input['status'],
        $input['id'],
        $adminSectorId
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Resident updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update resident']);
    }
}

/**
 * Handle deleting a resident
 */
function handleDeleteResident($connection, $adminSectorId)
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Resident ID is required']);
        return;
    }

    // Verify resident belongs to admin's sector
    $checkQuery = "SELECT id FROM users WHERE id = ? AND sector_id = ? AND role = 'resident'";
    $stmt       = $connection->prepare($checkQuery);
    $stmt->bind_param('ii', $input['id'], $adminSectorId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Resident not found or access denied']);
        return;
    }

    // Start transaction for cascading deletes
    $connection->begin_transaction();

    try {
        // Delete related records first (attendance, fines)
        $deleteAttendanceQuery = "DELETE FROM attendance WHERE user_id = ?";
        $stmt                  = $connection->prepare($deleteAttendanceQuery);
        $stmt->bind_param('i', $input['id']);
        $stmt->execute();

        $deleteFinesQuery = "DELETE FROM fines WHERE user_id = ?";
        $stmt             = $connection->prepare($deleteFinesQuery);
        $stmt->bind_param('i', $input['id']);
        $stmt->execute();

        // Delete the user
        $deleteUserQuery = "DELETE FROM users WHERE id = ? AND sector_id = ?";
        $stmt            = $connection->prepare($deleteUserQuery);
        $stmt->bind_param('ii', $input['id'], $adminSectorId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $connection->commit();
            echo json_encode(['success' => true, 'message' => 'Resident deleted successfully']);
        } else {
            $connection->rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete resident']);
        }
    } catch (Exception $e) {
        $connection->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting resident: ' . $e->getMessage()]);
    }
}
