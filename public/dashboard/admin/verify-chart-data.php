<?php
session_start();
$_SESSION['user_id']   = 2; // Admin with assignments
$_SESSION['user_role'] = 'admin';

require_once __DIR__ . '/../../../config/db.php';

// Get database connection
global $db;
$connection = $db->getConnection();

$adminId = $_SESSION['user_id'];

// Get admin's sector assignment
$adminSectorQuery = "
SELECT aa.sector_id, s.name as sector_name, s.code as sector_code,
       d.name as district_name, d.id as district_id
FROM admin_assignments aa
JOIN sectors s ON aa.sector_id = s.id
JOIN districts d ON s.district_id = d.id
WHERE aa.admin_id = ? AND aa.is_active = 1
LIMIT 1";

$stmt = $connection->prepare($adminSectorQuery);
$stmt->bind_param('i', $adminId);
$stmt->execute();
$adminSector = $stmt->get_result()->fetch_assoc();

if (! $adminSector) {
    die('Error: Admin is not assigned to any sector.');
}

$sectorId   = $adminSector['sector_id'];
$sectorName = $adminSector['sector_name'];

// Date range (last 30 days)
$startDate = date('Y-m-d', strtotime('-30 days'));
$endDate   = date('Y-m-d');

echo "<h1>Data Verification for Sector: {$sectorName} (ID: {$sectorId})</h1>";
echo "<p><strong>Date Range:</strong> {$startDate} to {$endDate}</p><br>";

try {
    // 1. Check attendance data
    echo "<h2>1. Attendance Data</h2>";
    $attendanceQuery = "
    SELECT
        COUNT(*) as total_registered,
        SUM(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) as total_attended,
        ROUND(AVG(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) * 100, 1) as attendance_rate,
        COUNT(DISTINCT a.event_id) as events_count,
        COUNT(DISTINCT a.user_id) as unique_participants
    FROM attendance a
    JOIN umuganda_events e ON a.event_id = e.id
    WHERE e.sector_id = ? AND e.event_date BETWEEN ? AND ?";

    $stmt = $connection->prepare($attendanceQuery);
    $stmt->bind_param('iss', $sectorId, $startDate, $endDate);
    $stmt->execute();
    $attendanceStats = $stmt->get_result()->fetch_assoc();

    echo "<pre>";
    print_r($attendanceStats);
    echo "</pre>";

    // 2. Check fine data
    echo "<h2>2. Fine Collection Data</h2>";
    $fineQuery = "
    SELECT
        COUNT(*) as total_fines,
        SUM(CASE WHEN f.status = 'paid' THEN f.amount ELSE 0 END) as total_collected,
        SUM(CASE WHEN f.status = 'pending' THEN f.amount ELSE 0 END) as total_pending,
        SUM(f.amount) as total_amount,
        ROUND(SUM(CASE WHEN f.status = 'paid' THEN f.amount ELSE 0 END) / SUM(f.amount) * 100, 1) as collection_rate
    FROM fines f
    JOIN users u ON f.user_id = u.id
    WHERE u.sector_id = ? AND f.created_at BETWEEN ? AND ?";

    $stmt = $connection->prepare($fineQuery);
    $stmt->bind_param('iss', $sectorId, $startDate, $endDate);
    $stmt->execute();
    $fineStats = $stmt->get_result()->fetch_assoc();

    echo "<pre>";
    print_r($fineStats);
    echo "</pre>";

    // 3. Monthly attendance trends for charts
    echo "<h2>3. Monthly Attendance Trends (Chart Data)</h2>";
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

    echo "<pre>";
    print_r($attendanceTrends);
    echo "</pre>";

    // 4. Monthly revenue trends for charts
    echo "<h2>4. Monthly Revenue Trends (Chart Data)</h2>";
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

    echo "<pre>";
    print_r($revenueTrends);
    echo "</pre>";

    // 5. Check what events exist for this sector
    echo "<h2>5. Events in this Sector</h2>";
    $eventsQuery = "SELECT id, event_date, status, title FROM umuganda_events WHERE sector_id = ? ORDER BY event_date DESC LIMIT 10";
    $stmt        = $connection->prepare($eventsQuery);
    $stmt->bind_param('i', $sectorId);
    $stmt->execute();
    $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo "<pre>";
    print_r($events);
    echo "</pre>";

    // 6. Check attendance records
    echo "<h2>6. Recent Attendance Records</h2>";
    $attendanceRecordsQuery = "
    SELECT a.id, a.user_id, a.event_id, a.status, a.created_at, e.event_date, e.title
    FROM attendance a
    JOIN umuganda_events e ON a.event_id = e.id
    WHERE e.sector_id = ?
    ORDER BY a.created_at DESC
    LIMIT 10";

    $stmt = $connection->prepare($attendanceRecordsQuery);
    $stmt->bind_param('i', $sectorId);
    $stmt->execute();
    $attendanceRecords = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo "<pre>";
    print_r($attendanceRecords);
    echo "</pre>";

    // 7. Check fines records
    echo "<h2>7. Recent Fines Records</h2>";
    $finesRecordsQuery = "
    SELECT f.id, f.user_id, f.amount, f.status, f.reason, f.created_at, u.first_name, u.last_name
    FROM fines f
    JOIN users u ON f.user_id = u.id
    WHERE u.sector_id = ?
    ORDER BY f.created_at DESC
    LIMIT 10";

    $stmt = $connection->prepare($finesRecordsQuery);
    $stmt->bind_param('i', $sectorId);
    $stmt->execute();
    $finesRecords = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo "<pre>";
    print_r($finesRecords);
    echo "</pre>";

} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}
