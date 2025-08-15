<?php
/**
 * Attendance Details API
 * Fetch detailed information about a specific attendance record
 */

session_start();

// Check if user is logged in and is admin
if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if (! isset($_GET['id']) || ! is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid attendance ID']);
    exit;
}

require_once __DIR__ . '/../../../config/db.php';

try {
    global $db;
    $connection   = $db->getConnection();
    $attendanceId = (int) $_GET['id'];

    // Get attendance record with related information
    $query = "
        SELECT
            a.id,
            a.user_id,
            a.event_id,
            a.status,
            a.check_in_time,
            a.notes,
            a.excuse_reason,
            a.created_at,
            a.updated_at,
            u.first_name,
            u.last_name,
            u.email,
            e.title as event_title,
            e.event_date,
            recorder.first_name as recorded_by_first_name,
            recorder.last_name as recorded_by_last_name,
            CONCAT(recorder.first_name, ' ', recorder.last_name) as recorded_by_name
        FROM attendance a
        JOIN users u ON a.user_id = u.id
        JOIN umuganda_events e ON a.event_id = e.id
        LEFT JOIN users recorder ON a.recorded_by = recorder.id
        WHERE a.id = ?
    ";

    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $attendanceId);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();

    if (! $record) {
        echo json_encode(['success' => false, 'message' => 'Attendance record not found']);
        exit;
    }

    // Format the check_in_time for datetime-local input
    if ($record['check_in_time']) {
        $record['check_in_time'] = date('Y-m-d\TH:i', strtotime($record['check_in_time']));
    }

    // Format created_at for display
    if ($record['created_at']) {
        $record['created_at'] = date('M j, Y H:i', strtotime($record['created_at']));
    }

    echo json_encode([
        'success' => true,
        'record'  => $record,
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
    ]);
}
