<?php
session_start();
$_SESSION['user_id']   = 2; // Admin with assignments
$_SESSION['user_role'] = 'admin';

require_once __DIR__ . '/../../../config/db.php';

try {
    $database   = new Database();
    $connection = $database->getConnection();
    $adminId    = $_SESSION['user_id'];

    // Get admin sector
    $adminSectorQuery = "
    SELECT aa.sector_id, s.name as sector_name
    FROM admin_assignments aa
    JOIN sectors s ON aa.sector_id = s.id
    WHERE aa.admin_id = ? AND aa.is_active = 1
    LIMIT 1";

    $stmt = $connection->prepare($adminSectorQuery);
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $adminSector = $stmt->get_result()->fetch_assoc();
    $sectorId    = $adminSector['sector_id'];

    echo "Testing chart data for Sector ID: $sectorId\n\n";

                                                       // Test the attendance trends query
    $startDate = date('Y-m-d', strtotime('-90 days')); // Get 3 months of data
    $endDate   = date('Y-m-d');

    echo "Date range: $startDate to $endDate\n\n";

    $trendsQuery = "
    SELECT
        DATE_FORMAT(e.event_date, '%Y-%m') as month,
        COUNT(DISTINCT a.id) as registrations,
        SUM(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) as attended,
        ROUND(SUM(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) / COUNT(DISTINCT a.id) * 100, 1) as rate
    FROM umuganda_events e
    LEFT JOIN attendance a ON e.id = a.event_id
    WHERE e.sector_id = ? AND e.event_date BETWEEN ? AND ?
    GROUP BY DATE_FORMAT(e.event_date, '%Y-%m')
    ORDER BY month";

    $stmt = $connection->prepare($trendsQuery);
    $stmt->bind_param('iss', $sectorId, $startDate, $endDate);
    $stmt->execute();
    $attendanceTrends = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo "=== Attendance Trends Data ===\n";
    if (empty($attendanceTrends)) {
        echo "No attendance trend data found!\n";

        // Check if there are any events
        $eventCheck = $connection->query("SELECT COUNT(*) as count FROM umuganda_events WHERE sector_id = $sectorId");
        $eventCount = $eventCheck->fetch_assoc()['count'];
        echo "Total events in sector: $eventCount\n";

        // Check if there are any attendance records
        $attendanceCheck = $connection->query("
            SELECT COUNT(*) as count
            FROM attendance a
            JOIN umuganda_events e ON a.event_id = e.id
            WHERE e.sector_id = $sectorId
        ");
        $attendanceCount = $attendanceCheck->fetch_assoc()['count'];
        echo "Total attendance records: $attendanceCount\n";

    } else {
        foreach ($attendanceTrends as $trend) {
            echo "Month: {$trend['month']}, Registrations: {$trend['registrations']}, Attended: {$trend['attended']}, Rate: {$trend['rate']}%\n";
        }
    }

    // Test fine statistics for revenue chart
    echo "\n=== Revenue Chart Data ===\n";
    $fineQuery = "
    SELECT
        COUNT(*) as total_fines,
        SUM(CASE WHEN f.status = 'paid' THEN f.amount ELSE 0 END) as total_collected,
        SUM(CASE WHEN f.status = 'pending' THEN f.amount ELSE 0 END) as total_pending,
        SUM(f.amount) as total_amount
    FROM fines f
    JOIN users u ON f.user_id = u.id
    WHERE u.sector_id = ? AND f.created_at BETWEEN ? AND ?";

    $stmt = $connection->prepare($fineQuery);
    $stmt->bind_param('iss', $sectorId, $startDate, $endDate);
    $stmt->execute();
    $fineStats = $stmt->get_result()->fetch_assoc();

    echo "Total Collected: {$fineStats['total_collected']} RWF\n";
    echo "Total Pending: {$fineStats['total_pending']} RWF\n";
    echo "Total Amount: {$fineStats['total_amount']} RWF\n";

    // Test monthly revenue trends
    echo "\n=== Monthly Revenue Trends ===\n";
    $revenueTrendsQuery = "
    SELECT
        DATE_FORMAT(f.created_at, '%Y-%m') as month,
        SUM(CASE WHEN f.status = 'paid' THEN f.amount ELSE 0 END) as collected,
        SUM(CASE WHEN f.status = 'pending' THEN f.amount ELSE 0 END) as pending,
        COUNT(*) as total_fines
    FROM fines f
    JOIN users u ON f.user_id = u.id
    WHERE u.sector_id = ? AND f.created_at BETWEEN ? AND ?
    GROUP BY DATE_FORMAT(f.created_at, '%Y-%m')
    ORDER BY month";

    $stmt = $connection->prepare($revenueTrendsQuery);
    $stmt->bind_param('iss', $sectorId, $startDate, $endDate);
    $stmt->execute();
    $revenueTrends = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($revenueTrends)) {
        echo "No monthly revenue data found!\n";
    } else {
        foreach ($revenueTrends as $trend) {
            echo "Month: {$trend['month']}, Collected: {$trend['collected']}, Pending: {$trend['pending']}, Total Fines: {$trend['total_fines']}\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
