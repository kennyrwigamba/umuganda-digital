<?php
/**
 * Create Sample Data for Testing
 * Creates sample umuganda events, attendance records, and fines
 */

require_once __DIR__ . '/config/db.php';

global $db;
$connection = $db->getConnection();

echo "<h1>Creating Sample Data for Dashboard Testing</h1>\n";

try {
    // Create sample Umuganda events
    echo "<h2>Creating Umuganda Events...</h2>\n";

    $events = [
        ['2025-07-26', 'Monthly Community Work', 'Regular monthly umuganda activity'],
        ['2025-06-29', 'Community Cleanup', 'Special cleanup activity'],
        ['2025-05-25', 'Infrastructure Development', 'Road and bridge maintenance'],
        ['2025-04-27', 'Environment Protection', 'Tree planting and conservation'],
        ['2025-03-30', 'Health and Sanitation', 'Community health improvement'],
    ];

    foreach ($events as $event) {
        $checkEvent = $connection->prepare("SELECT id FROM umuganda_events WHERE event_date = ?");
        $checkEvent->bind_param('s', $event[0]);
        $checkEvent->execute();

        if ($checkEvent->get_result()->num_rows == 0) {
            $insertEvent = $connection->prepare("
                INSERT INTO umuganda_events (title, description, event_date, start_time, end_time,
                                           location, status, created_by)
                VALUES (?, ?, ?, '08:00:00', '11:00:00', 'Community Center', 'completed', 1)
            ");
            $insertEvent->bind_param('sss', $event[1], $event[2], $event[0]);
            $insertEvent->execute();
            echo "<p>✅ Created event: {$event[1]} on {$event[0]}</p>\n";
        }
    }

    // Get all users and events for creating attendance records
    $users    = $connection->query("SELECT id, first_name, last_name FROM users WHERE role = 'resident'")->fetch_all(MYSQLI_ASSOC);
    $eventIds = $connection->query("SELECT id, event_date FROM umuganda_events ORDER BY event_date DESC")->fetch_all(MYSQLI_ASSOC);

    echo "<h2>Creating Attendance Records...</h2>\n";

    $attendanceStatuses = ['present', 'absent', 'late', 'present', 'present']; // Bias towards present
    $attendanceCount    = 0;

    foreach ($eventIds as $event) {
        foreach ($users as $user) {
            $checkAttendance = $connection->prepare("
                SELECT id FROM attendance WHERE user_id = ? AND event_id = ?
            ");
            $checkAttendance->bind_param('ii', $user['id'], $event['id']);
            $checkAttendance->execute();

            if ($checkAttendance->get_result()->num_rows == 0) {
                $status       = $attendanceStatuses[array_rand($attendanceStatuses)];
                $checkInTime  = $status === 'present' ? $event['event_date'] . ' 08:' . rand(0, 30) . ':00' : null;
                $checkOutTime = $status === 'present' ? $event['event_date'] . ' 11:' . rand(0, 30) . ':00' : null;

                $insertAttendance = $connection->prepare("
                    INSERT INTO attendance (user_id, event_id, check_in_time, check_out_time, status)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insertAttendance->bind_param('iisss', $user['id'], $event['id'], $checkInTime, $checkOutTime, $status);
                $insertAttendance->execute();
                $attendanceCount++;
            }
        }
    }
    echo "<p>✅ Created {$attendanceCount} attendance records</p>\n";

    // Create fines for users who were absent or late
    echo "<h2>Creating Fines...</h2>\n";

    $finesQuery = "
        SELECT a.user_id, a.event_id, a.status, u.first_name, u.last_name
        FROM attendance a
        JOIN users u ON a.user_id = u.id
        WHERE a.status IN ('absent', 'late')
        ORDER BY RAND()
        LIMIT 10
    ";

    $finesResult = $connection->query($finesQuery);
    $fineCount   = 0;

    while ($attendance = $finesResult->fetch_assoc()) {
        $checkFine = $connection->prepare("
            SELECT id FROM fines WHERE user_id = ? AND event_id = ?
        ");
        $checkFine->bind_param('ii', $attendance['user_id'], $attendance['event_id']);
        $checkFine->execute();

        if ($checkFine->get_result()->num_rows == 0) {
            $amount  = $attendance['status'] === 'absent' ? 15000 : 5000;
            $reason  = $attendance['status'] === 'absent' ? 'absence' : 'late_arrival';
            $dueDate = date('Y-m-d', strtotime('+30 days'));

            $insertFine = $connection->prepare("
                INSERT INTO fines (user_id, event_id, amount, reason, status, due_date, created_by)
                VALUES (?, ?, ?, ?, 'pending', ?, 1)
            ");
            $insertFine->bind_param('iidss',
                $attendance['user_id'],
                $attendance['event_id'],
                $amount,
                $reason,
                $dueDate
            );
            $insertFine->execute();

            echo "<p>✅ Created fine: {$attendance['first_name']} {$attendance['last_name']} - {$amount} RWF for {$reason}</p>\n";
            $fineCount++;
        }
    }

    echo "<p>✅ Created {$fineCount} fines</p>\n";

    // Create a couple of paid fines for the charts
    $paidFines = $connection->query("SELECT id FROM fines WHERE status = 'pending' LIMIT 3")->fetch_all(MYSQLI_ASSOC);
    foreach ($paidFines as $fine) {
        $connection->query("UPDATE fines SET status = 'paid', paid_date = NOW() WHERE id = {$fine['id']}");
    }
    echo "<p>✅ Marked 3 fines as paid for chart data</p>\n";

    echo "<h2>🎉 Sample Data Creation Complete!</h2>\n";
    echo "<p>The dashboard should now show realistic data including:</p>\n";
    echo "<ul>\n";
    echo "<li>✅ Attendance rates from recent events</li>\n";
    echo "<li>✅ Outstanding fines with real amounts</li>\n";
    echo "<li>✅ Chart data for attendance trends</li>\n";
    echo "<li>✅ Fines distribution data</li>\n";
    echo "</ul>\n";

    echo "<p><a href='public/dashboard/admin/index.php'>Go to Admin Dashboard</a></p>\n";

} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
