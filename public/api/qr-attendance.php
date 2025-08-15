<?php
/**
 * QR Code Attendance API# Validate required fields
if (! isset($input['user_id']) || ! isset($input['event_id'])) {
error_log("QR Attendance API: Missing required fields. Input: " . json_encode($input));
echo json_encode(['success' => false, 'message' => 'Missing required fields: user_id, event_id']);
exit;
}

$userId       = (int) $input['user_id'];
$eventId      = (int) $input['event_id'];
$status       = $input['status'] ?? 'present';
$checkInTime  = $input['check_in_time'] ?? date('H:i:s');
$notes        = $input['notes'] ?? '';
$excuseReason = $input['excuse_reason'] ?? '';
$adminId      = $_SESSION['user_id'];

// Log the incoming request for debugging
error_log("QR Attendance API: Processing request - User: $userId, Event: $eventId, Status: $status");R code-based attendance marking
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();

// Check if user is logged in and is admin
if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (! $input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Validate required fields
if (! isset($input['user_id']) || ! isset($input['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: user_id, event_id']);
    exit;
}

$userId       = (int) $input['user_id'];
$eventId      = (int) $input['event_id'];
$status       = $input['status'] ?? 'present';
$providedTime = $input['check_in_time'] ?? '';
// Handle timestamp format for check_in_time column
if (! empty($providedTime) && $providedTime !== '00:00:00') {
    $checkInTime = date('Y-m-d') . ' ' . $providedTime;
} else {
    $checkInTime = date('Y-m-d H:i:s');
}
$notes        = $input['notes'] ?? '';
$excuseReason = $input['excuse_reason'] ?? '';
$adminId      = $_SESSION['user_id'];

// Log admin ID for debugging
error_log("QR Attendance API: Admin ID from session: $adminId");

try {
    global $db;
    $connection = $db->getConnection();

    // Verify the admin exists and is active
    $adminCheckQuery = "SELECT id FROM users WHERE id = ? AND role IN ('admin', 'superadmin') AND status = 'active'";
    $adminStmt       = $connection->prepare($adminCheckQuery);
    $adminStmt->bind_param('i', $adminId);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();

    if (! $adminResult->fetch_assoc()) {
        error_log("QR Attendance API: Admin user not found or inactive for ID: $adminId");
        echo json_encode(['success' => false, 'message' => 'Admin user not found or inactive']);
        exit;
    }

    // Verify the user exists and is active
    $userCheckQuery = "SELECT id, first_name, last_name FROM users WHERE id = ? AND role = 'resident' AND status = 'active'";
    $userStmt       = $connection->prepare($userCheckQuery);
    $userStmt->bind_param('i', $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $user       = $userResult->fetch_assoc();

    if (! $user) {
        echo json_encode(['success' => false, 'message' => 'User not found or inactive']);
        exit;
    }

    // Verify the event exists
    $eventCheckQuery = "SELECT id, title FROM umuganda_events WHERE id = ?";
    $eventStmt       = $connection->prepare($eventCheckQuery);
    $eventStmt->bind_param('i', $eventId);
    $eventStmt->execute();
    $eventResult = $eventStmt->get_result();
    $event       = $eventResult->fetch_assoc();

    if (! $event) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }

    $connection->autocommit(false);

    // Check if attendance record already exists
    $checkQuery = "SELECT id FROM attendance WHERE user_id = ? AND event_id = ?";
    $checkStmt  = $connection->prepare($checkQuery);
    $checkStmt->bind_param('ii', $userId, $eventId);
    $checkStmt->execute();
    $checkResult    = $checkStmt->get_result();
    $existingRecord = $checkResult->fetch_assoc();

    if ($existingRecord) {
        // Update existing record
        $updateQuery = "UPDATE attendance
                       SET status = ?, check_in_time = ?, notes = ?, excuse_reason = ?, recorded_by = ?, updated_at = NOW()
                       WHERE user_id = ? AND event_id = ?";
        $updateStmt = $connection->prepare($updateQuery);
        $updateStmt->bind_param('ssssiii', $status, $checkInTime, $notes, $excuseReason, $adminId, $userId, $eventId);
        $updateStmt->execute();
        $attendanceId = $existingRecord['id'];
        $action       = 'updated';
    } else {
        // Insert new record
        $insertQuery = "INSERT INTO attendance (user_id, event_id, status, check_in_time, notes, excuse_reason, recorded_by, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $insertStmt = $connection->prepare($insertQuery);
        $insertStmt->bind_param('iissssi', $userId, $eventId, $status, $checkInTime, $notes, $excuseReason, $adminId);
        $insertStmt->execute();
        $attendanceId = $connection->insert_id;
        $action       = 'created';
    }

    // Handle fines based on status
    $fineAmount = 0;
    if ($status === 'late' || $status === 'absent') {
        // Fetch fine amount from admin_settings for current admin
        $settingsQuery = "SELECT default_fine_amount FROM admin_settings WHERE admin_id = ? LIMIT 1";
        $settingsStmt  = $connection->prepare($settingsQuery);
        $settingsStmt->bind_param('i', $adminId);
        $settingsStmt->execute();
        $settingsResult = $settingsStmt->get_result();
        $settings       = $settingsResult->fetch_assoc();

        error_log("QR Attendance API: Checking settings for admin ID: $adminId");
        error_log("QR Attendance API: Settings found: " . json_encode($settings));

        if ($settings && isset($settings['default_fine_amount'])) {
            $fineAmount = (float) $settings['default_fine_amount'];
            error_log("QR Attendance API: Using admin settings fine amount: $fineAmount");
        } else {
            // Fallback to default values if admin settings not found
            $fineAmount = ($status === 'late') ? 500 : 1000;
            error_log("QR Attendance API: Using fallback fine amount: $fineAmount (no admin settings found)");
        }
    }

    if ($fineAmount > 0 && $status !== 'excused') {
        // Check if fine already exists
        $fineCheckQuery = "SELECT id FROM fines WHERE attendance_id = ? AND status != 'waived'";
        $fineCheckStmt  = $connection->prepare($fineCheckQuery);
        $fineCheckStmt->bind_param('i', $attendanceId);
        $fineCheckStmt->execute();
        $fineExists = $fineCheckStmt->get_result()->fetch_assoc();

        if ($fineExists) {
            // Update existing fine
            $updateFineQuery = "UPDATE fines SET amount = ?, updated_at = NOW() WHERE id = ?";
            $updateFineStmt  = $connection->prepare($updateFineQuery);
            $updateFineStmt->bind_param('di', $fineAmount, $fineExists['id']);
            $updateFineStmt->execute();
        } else {
            // Create new fine
            $fineReason      = ($status === 'late') ? 'late_arrival' : 'absence';
            $insertFineQuery = "INSERT INTO fines (user_id, event_id, attendance_id, amount, reason, status, due_date, created_by, created_at)
                               VALUES (?, ?, ?, ?, ?, 'unpaid', DATE_ADD(NOW(), INTERVAL 30 DAY), ?, NOW())";
            $insertFineStmt = $connection->prepare($insertFineQuery);
            $insertFineStmt->bind_param('iiidsi', $userId, $eventId, $attendanceId, $fineAmount, $fineReason, $adminId);
            $insertFineStmt->execute();
        }
    }

    $connection->commit();
    $connection->autocommit(true);

    echo json_encode([
        'success' => true,
        'message' => "Attendance {$action} successfully for {$user['first_name']} {$user['last_name']}",
        'data'    => [
            'user_id'       => $userId,
            'user_name'     => $user['first_name'] . ' ' . $user['last_name'],
            'event_id'      => $eventId,
            'event_title'   => $event['title'],
            'status'        => $status,
            'check_in_time' => $checkInTime,
            'fine_amount'   => $fineAmount,
            'action'        => $action,
        ],
    ]);

} catch (Exception $e) {
    if (isset($connection)) {
        $connection->rollback();
        $connection->autocommit(true);
    }

    error_log("QR Attendance Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug'   => [
            'file'     => $e->getFile(),
            'line'     => $e->getLine(),
            'user_id'  => $userId ?? 'unknown',
            'event_id' => $eventId ?? 'unknown',
        ],
    ]);
}
