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

        $startDate = date('Y-m-d', strtotime('-90 days'));
        $endDate   = date('Y-m-d');

        // Get attendance trends
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

        // Get revenue trends
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

        $totalCollected = 23000;
        $totalPending   = 0;
        $attendanceRate = 62.5;

    } catch (Exception $e) {
        $attendanceTrends = [];
        $revenueTrends    = [];
        $totalCollected   = 0;
        $totalPending     = 0;
        $attendanceRate   = 0;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chart Debug Test</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .chart-container { position: relative; height: 300px; width: 600px; margin: 20px 0; }
        .debug-info { background: #f5f5f5; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Chart Debug Test</h1>

    <div class="debug-info">
        <h3>Debug Information:</h3>
        <p><strong>Attendance Trends Data:</strong>                                                    <?php echo json_encode($attendanceTrends); ?></p>
        <p><strong>Revenue Trends Data:</strong>                                                 <?php echo json_encode($revenueTrends); ?></p>
        <p><strong>Total Collected:</strong>                                             <?php echo $totalCollected; ?> RWF</p>
        <p><strong>Total Pending:</strong>                                           <?php echo $totalPending; ?> RWF</p>
        <p><strong>Attendance Rate:</strong>                                             <?php echo $attendanceRate; ?>%</p>
    </div>

    <h2>Attendance Trends Chart</h2>
    <div class="chart-container">
        <canvas id="attendanceChart"></canvas>
    </div>

    <h2>Revenue Chart</h2>
    <div class="chart-container">
        <canvas id="revenueChart"></canvas>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Chart debug test started...');

            // Test attendance chart
            try {
                const attendanceData =                                       <?php echo json_encode($attendanceTrends); ?>;
                console.log('Attendance data:', attendanceData);

                const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
                const attendanceLabels = attendanceData.length > 0 ? attendanceData.map(item => item.month) : ['Current'];
                const attendanceRates = attendanceData.length > 0 ? attendanceData.map(item => parseFloat(item.rate) || 0) : [<?php echo $attendanceRate; ?>];

                console.log('Attendance labels:', attendanceLabels);
                console.log('Attendance rates:', attendanceRates);

                const attendanceChart = new Chart(attendanceCtx, {
                    type: 'line',
                    data: {
                        labels: attendanceLabels,
                        datasets: [{
                            label: 'Attendance Rate %',
                            data: attendanceRates,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
                console.log('Attendance chart created successfully');

            } catch (error) {
                console.error('Error creating attendance chart:', error);
            }

            // Test revenue chart
            try {
                const revenueData =                                    <?php echo json_encode($revenueTrends); ?>;
                console.log('Revenue data:', revenueData);

                const revenueCtx = document.getElementById('revenueChart').getContext('2d');
                const revenueLabels = revenueData.length > 0 ? revenueData.map(item => item.month) : ['Current'];
                const collectedData = revenueData.length > 0 ? revenueData.map(item => parseFloat(item.collected) || 0) : [<?php echo $totalCollected; ?>];
                const pendingData = revenueData.length > 0 ? revenueData.map(item => parseFloat(item.pending) || 0) : [<?php echo $totalPending; ?>];

                console.log('Revenue labels:', revenueLabels);
                console.log('Collected data:', collectedData);
                console.log('Pending data:', pendingData);

                const revenueChart = new Chart(revenueCtx, {
                    type: 'bar',
                    data: {
                        labels: revenueLabels,
                        datasets: [{
                            label: 'Collections (RWF)',
                            data: collectedData,
                            backgroundColor: 'rgba(34, 197, 94, 0.8)',
                            borderColor: '#16a34a',
                            borderWidth: 1
                        }, {
                            label: 'Pending (RWF)',
                            data: pendingData,
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderColor: '#dc2626',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                console.log('Revenue chart created successfully');

            } catch (error) {
                console.error('Error creating revenue chart:', error);
            }
        });
    </script>
</body>
</html>
