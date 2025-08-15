<?php
    session_start();
    $_SESSION['user_id']   = 2; // Admin with assignments
    $_SESSION['user_role'] = 'admin';

    require_once __DIR__ . '/../../../config/db.php';

    // Get simple chart data
    $attendanceRate = 65.5;
    $totalCollected = 23000;
    $totalPending   = 5000;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Fixed Charts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Reports Dashboard - Fixed Charts</h1>

        <!-- Chart Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Attendance Trends -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Attendance Trends</h3>
                <div class="chart-container">
                    <canvas id="attendanceTrendsChart"></canvas>
                </div>
            </div>

            <!-- Revenue Analysis -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Analysis</h3>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Debug Information -->
        <div class="mt-8 bg-gray-100 p-4 rounded">
            <h4 class="font-semibold mb-2">Debug Info:</h4>
            <p>Attendance Rate:                                <?php echo $attendanceRate; ?>%</p>
            <p>Total Collected:                                <?php echo number_format($totalCollected); ?> RWF</p>
            <p>Total Pending:                              <?php echo number_format($totalPending); ?> RWF</p>
        </div>
    </div>

    <script>
        console.log('Page loaded, Chart.js available:', typeof Chart !== 'undefined');

        if (typeof Chart !== 'undefined') {
            console.log('Chart.js version:', Chart.version);

            // Create charts immediately
            try {
                // Attendance Chart
                const attendanceCtx = document.getElementById('attendanceTrendsChart').getContext('2d');
                const attendanceChart = new Chart(attendanceCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                        datasets: [{
                            label: 'Attendance Rate %',
                            data: [60, 65, 70,                                               <?php echo $attendanceRate; ?>, 68],
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

                // Revenue Chart
                const revenueCtx = document.getElementById('revenueChart').getContext('2d');
                const revenueChart = new Chart(revenueCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                        datasets: [{
                            label: 'Collections (RWF)',
                            data: [15000, 18000, 22000,                                                        <?php echo $totalCollected; ?>, 20000],
                            backgroundColor: 'rgba(34, 197, 94, 0.8)',
                            borderColor: '#16a34a',
                            borderWidth: 1
                        }, {
                            label: 'Pending (RWF)',
                            data: [8000, 6000, 4000,                                                     <?php echo $totalPending; ?>, 3000],
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
                console.error('Chart creation failed:', error);
                alert('Chart creation failed: ' + error.message);
            }
        } else {
            console.error('Chart.js not loaded!');
            alert('Chart.js library not loaded!');
        }
    </script>
</body>
</html>
