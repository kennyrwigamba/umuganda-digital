<?php
/**
 * Update Attendance API
 * Update an existing attendance record
 */

session_start();

// Check if user is logged in and is admin
if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

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
if (! isset($input['id']) || ! is_numeric($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid attendance ID']);
    exit;
}

require_once __DIR__ . '/../../../config/db.php';

try {
    global $db;
    $connection = $db->getConnection();
    $connection->autocommit(false);

    $attendanceId = (int) $input['id'];
    $status       = $input['status'] ?? '';
    $checkInTime  = $input['check_in_time'] ?? '';
    $notes        = $input['notes'] ?? '';
    $excuseReason = $input['excuse_reason'] ?? '';
    $adminId      = $_SESSION['user_id'];

    // Validate status
    $validStatuses = ['present', 'absent', 'late', 'excused'];
    if (! in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value']);
        exit;
    }

    // Convert datetime-local format to MySQL timestamp
    if (! empty($checkInTime)) {
        $checkInTime = date('Y-m-d H:i:s', strtotime($checkInTime));
    } else {
        $checkInTime = null;
    }

    // First, verify the attendance record exists and get related info
    $verifyQuery = "SELECT a.id, a.user_id, a.event_id, u.first_name, u.last_name
                    FROM attendance a
                    JOIN users u ON a.user_id = u.id
                    WHERE a.id = ?";
    $verifyStmt = $connection->prepare($verifyQuery);
    $verifyStmt->bind_param('i', $attendanceId);
    $verifyStmt->execute();
    $attendanceRecord = $verifyStmt->get_result()->fetch_assoc();

    if (! $attendanceRecord) {
        echo json_encode(['success' => false, 'message' => 'Attendance record not found']);
        exit;
    }

    // Update the attendance record
    $updateQuery = "
        UPDATE attendance
        SET status = ?,
            check_in_time = ?,
            notes = ?,
            excuse_reason = ?,
            recorded_by = ?,
            updated_at = NOW()
        WHERE id = ?
    ";

    $updateStmt = $connection->prepare($updateQuery);
    $updateStmt->bind_param('ssssii', $status, $checkInTime, $notes, $excuseReason, $adminId, $attendanceId);
    $updateStmt->execute();

    // Handle fines based on status change
    $userId  = $attendanceRecord['user_id'];
    $eventId = $attendanceRecord['event_id'];

                               // Get admin settings for fine amount
    $defaultFineAmount = 5000; // fallback
    $settingsQuery     = "SELECT default_fine_amount FROM admin_settings WHERE admin_id = ? LIMIT 1";
    $settingsStmt      = $connection->prepare($settingsQuery);
    $settingsStmt->bind_param('i', $adminId);
    $settingsStmt->execute();
    $settingsResult = $settingsStmt->get_result();
    $settings       = $settingsResult->fetch_assoc();

    if ($settings && isset($settings['default_fine_amount'])) {
        $defaultFineAmount = (float) $settings['default_fine_amount'];
    }

    // Determine fine amount based on status
    $fineAmount = 0;
    if ($status === 'late') {
        $fineAmount = $defaultFineAmount * 0.5; // Half amount for late
    } elseif ($status === 'absent') {
        $fineAmount = $defaultFineAmount; // Full amount for absence
    }

    // Handle fines
    if ($fineAmount > 0 && $status !== 'excused') {
        // Check if fine already exists for this attendance record
        $fineCheckQuery = "SELECT id FROM fines WHERE attendance_id = ? AND status != 'waived'";
        $fineCheckStmt  = $connection->prepare($fineCheckQuery);
        $fineCheckStmt->bind_param('i', $attendanceId);
        $fineCheckStmt->execute();
        $existingFine = $fineCheckStmt->get_result()->fetch_assoc();

        if ($existingFine) {
            // Update existing fine
            $updateFineQuery = "UPDATE fines SET amount = ?, updated_at = NOW() WHERE id = ?";
            $updateFineStmt  = $connection->prepare($updateFineQuery);
            $updateFineStmt->bind_param('di', $fineAmount, $existingFine['id']);
            $updateFineStmt->execute();
        } else {
            // Create new fine
            $fineReason      = ($status === 'late') ? 'late_arrival' : 'absence';
            $insertFineQuery = "
                INSERT INTO fines (user_id, event_id, attendance_id, amount, reason, status, due_date, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 30 DAY), ?, NOW())
            ";
            $insertFineStmt = $connection->prepare($insertFineQuery);
            $insertFineStmt->bind_param('iiidsi', $userId, $eventId, $attendanceId, $fineAmount, $fineReason, $adminId);
            $insertFineStmt->execute();
        }
    } else {
        // Remove fines if status changed to present or excused
        $removeFineQuery = "UPDATE fines SET status = 'waived', waived_by = ?, waived_reason = 'Status changed to $status', waived_date = NOW() WHERE attendance_id = ? AND status = 'pending'";
        $removeFineStmt  = $connection->prepare($removeFineQuery);
        $removeFineStmt->bind_param('ii', $adminId, $attendanceId);
        $removeFineStmt->execute();
    }

    $connection->commit();
    $connection->autocommit(true);

    echo json_encode([
        'success' => true,
        'message' => "Attendance updated successfully for {$attendanceRecord['first_name']} {$attendanceRecord['last_name']}",
        'data'    => [
            'attendance_id' => $attendanceId,
            'status'        => $status,
            'fine_amount'   => $fineAmount,
        ],
    ]);

} catch (Exception $e) {
    $connection->rollback();
    $connection->autocommit(true);

    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
    ]);
}
