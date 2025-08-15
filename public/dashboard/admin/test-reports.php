<?php
session_start();
// Simulate admin session
$_SESSION['user_id']   = 1;
$_SESSION['user_role'] = 'admin';

require_once __DIR__ . '/../../../config/db.php';

try {
    $database   = new Database();
    $connection = $database->getConnection();

    echo "Database connection: Success\n";

    // Test admin sector assignment query
    $adminId          = 1;
    $adminSectorQuery = "
    SELECT aa.sector_id, s.name as sector_name, s.code as sector_code,
           d.name as district_name, d.id as district_id
    FROM admin_assignments aa
    JOIN sectors s ON aa.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    WHERE aa.admin_id = ? AND aa.is_active = 1
    LIMIT 1";

    $stmt = $connection->prepare($adminSectorQuery);
    if (! $stmt) {
        echo "Failed to prepare admin sector query: " . $connection->error . "\n";
    } else {
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
        $adminSector = $stmt->get_result()->fetch_assoc();

        if ($adminSector) {
            echo "Admin sector found: " . $adminSector['sector_name'] . "\n";
            $sectorId = $adminSector['sector_id'];

            // Test attendance query
            $startDate = date('Y-m-01');
            $endDate   = date('Y-m-d');

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
            if (! $stmt) {
                echo "Failed to prepare attendance query: " . $connection->error . "\n";
            } else {
                $stmt->bind_param('iss', $sectorId, $startDate, $endDate);
                $stmt->execute();
                $attendanceStats = $stmt->get_result()->fetch_assoc();

                echo "Attendance stats:\n";
                print_r($attendanceStats);

                $stmt->close();
            }

        } else {
            echo "No admin sector assignment found\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
