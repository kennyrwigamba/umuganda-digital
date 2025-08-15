<?php
    /**
     * Admin Reports & Analytics Dashboard
     * Dynamic reporting system for sector-specific data
     */

    // Authentication and Authorization
    session_start();
    if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: ../../login.php');
        exit;
    }

    // Include required files
    require_once __DIR__ . '/../../../config/db.php';
    require_once __DIR__ . '/../../../src/models/User.php';

    // Get database connection
    global $db;
    $connection = $db->getConnection();

    $user = new User();

    // Get current admin info
    $adminId   = $_SESSION['user_id'];
    $adminInfo = $user->findById($adminId);

    if (! $adminInfo) {
        // User not found, logout and redirect
        session_destroy();
        header('Location: ../../login.php?message=session_expired');
        exit;
    }

    // Extract user information for display
    $firstName = htmlspecialchars($adminInfo['first_name']);
    $lastName  = htmlspecialchars($adminInfo['last_name']);
    $fullName  = $firstName . ' ' . $lastName;
    $initials  = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

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
        die('Error: Admin is not assigned to any sector. Please contact super admin.');
    }

    $sectorId     = $adminSector['sector_id'];
    $sectorName   = $adminSector['sector_name'];
    $districtName = $adminSector['district_name'];

                                                                                    // Date range handling
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // First day of current month
    $endDate   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');      // Today

    // Get time period filter (default to 30 days)
    $period = isset($_GET['period']) ? $_GET['period'] : '30';

    switch ($period) {
        case '7':
            $startDate = date('Y-m-d', strtotime('-7 days'));
            break;
        case '30':
            $startDate = date('Y-m-d', strtotime('-30 days'));
            break;
        case '90':
            $startDate = date('Y-m-d', strtotime('-90 days'));
            break;
        case 'year':
            $startDate = date('Y-01-01');
            break;
    }

    try {
        // 1. ATTENDANCE METRICS
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

        // 2. FINE COLLECTION METRICS
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

        // 3. EVENT SUCCESS METRICS
        $eventQuery = "
    SELECT
        COUNT(*) as total_events,
        SUM(CASE WHEN e.status = 'completed' THEN 1 ELSE 0 END) as completed_events,
        SUM(CASE WHEN e.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_events,
        ROUND(SUM(CASE WHEN e.status = 'completed' THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as success_rate
    FROM umuganda_events e
    WHERE e.sector_id = ? AND e.event_date BETWEEN ? AND ?";

        $stmt = $connection->prepare($eventQuery);
        $stmt->bind_param('iss', $sectorId, $startDate, $endDate);
        $stmt->execute();
        $eventStats = $stmt->get_result()->fetch_assoc();

        // 4. USER ENGAGEMENT METRICS
        $engagementQuery = "
    SELECT
        COUNT(DISTINCT u.id) as total_users,
        COUNT(DISTINCT a.user_id) as active_users,
        ROUND(COUNT(DISTINCT a.user_id) / COUNT(DISTINCT u.id) * 100, 1) as engagement_rate
    FROM users u
    LEFT JOIN attendance a ON u.id = a.user_id
        AND a.created_at BETWEEN ? AND ?
    WHERE u.sector_id = ? AND u.role = 'resident'";

        $stmt = $connection->prepare($engagementQuery);
        $stmt->bind_param('ssi', $startDate, $endDate, $sectorId);
        $stmt->execute();
        $engagementStats = $stmt->get_result()->fetch_assoc();

        // 5. MONTHLY ATTENDANCE TRENDS (for chart)
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

        // 6. MONTHLY REVENUE TRENDS (for chart)
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

        // 7. CELL PERFORMANCE (if applicable)
        $cellQuery = "
    SELECT
        c.name as cell_name,
        COUNT(DISTINCT u.id) as total_residents,
        COUNT(DISTINCT a.user_id) as active_participants,
        ROUND(COUNT(DISTINCT a.user_id) / COUNT(DISTINCT u.id) * 100, 1) as participation_rate
    FROM cells c
    LEFT JOIN users u ON c.id = u.cell_id
    LEFT JOIN attendance a ON u.id = a.user_id
        AND a.created_at BETWEEN ? AND ?
    WHERE c.sector_id = ?
    GROUP BY c.id, c.name
    ORDER BY participation_rate DESC";

        $stmt = $connection->prepare($cellQuery);
        $stmt->bind_param('ssi', $startDate, $endDate, $sectorId);
        $stmt->execute();
        $cellPerformance = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Set default values for calculations
        $attendanceRate = $attendanceStats['attendance_rate'] ?? 0;
        $collectionRate = $fineStats['collection_rate'] ?? 0;
        $successRate    = $eventStats['success_rate'] ?? 0;
        $engagementRate = $engagementStats['engagement_rate'] ?? 0;

        $totalCollected  = $fineStats['total_collected'] ?? 0;
        $totalPending    = $fineStats['total_pending'] ?? 0;
        $totalEvents     = $eventStats['total_events'] ?? 0;
        $completedEvents = $eventStats['completed_events'] ?? 0;
        $avgAttendance   = $attendanceStats['total_attended'] ?? 0;

    } catch (Exception $e) {
        // Default values in case of error
        $attendanceRate   = 0;
        $collectionRate   = 0;
        $successRate      = 0;
        $engagementRate   = 0;
        $totalCollected   = 0;
        $totalPending     = 0;
        $totalEvents      = 0;
        $completedEvents  = 0;
        $avgAttendance    = 0;
        $attendanceTrends = [];
        $revenueTrends    = [];
        $cellPerformance  = [];
    }

?>
<!-- Header -->
<?php include __DIR__ . '/partials/header.php'; ?>

<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div id="main-content" class="content-transition lg:ml-64">
        <!-- Top Navigation -->
        <?php include __DIR__ . '/partials/top-nav.php'; ?>

        <!-- Reports & Analytics Content -->
        <main class="p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 ml-4 lg:ml-0">Reports & Analytics</h1>
                            <p class="text-gray-600 mt-1 ml-4 lg:ml-0">
                                <?php echo htmlspecialchars($sectorName); ?> Sector,<?php echo htmlspecialchars($districtName); ?> District
                                <span class="text-primary-600 font-medium">
                                    (<?php echo date('M j', strtotime($startDate)); ?> -<?php echo date('M j, Y', strtotime($endDate)); ?>)
                                </span>
                            </p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                            <button onclick="toggleDateRangeModal()"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Date Range
                            </button>
                            <button onclick="generateReport()"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg text-sm font-medium hover:from-primary-700 hover:to-primary-800 shadow-sm transition-all">
                                <i class="fas fa-file-download mr-2"></i>
                                Generate Report
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Time Period Filter -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <div class="flex flex-wrap gap-2">
                            <a href="?period=7"
                                class="px-4 py-2 text-sm                                                                                                                                                                         <?php echo($period == '7') ? 'bg-primary-100 text-primary-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-medium transition-colors">
                                Last 7 Days
                            </a>
                            <a href="?period=30"
                                class="px-4 py-2 text-sm                                                                                                                                                                         <?php echo($period == '30') ? 'bg-primary-100 text-primary-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-medium transition-colors">
                                Last 30 Days
                            </a>
                            <a href="?period=90"
                                class="px-4 py-2 text-sm                                                                                                                                                                         <?php echo($period == '90') ? 'bg-primary-100 text-primary-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-medium transition-colors">
                                Last 3 Months
                            </a>
                            <a href="?period=year"
                                class="px-4 py-2 text-sm                                                                                                                                                                         <?php echo($period == 'year') ? 'bg-primary-100 text-primary-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-medium transition-colors">
                                This Year
                            </a>
                            <button onclick="toggleCustomRange()"
                                class="px-4 py-2 text-sm                                                                                                                                                                         <?php echo(isset($_GET['start_date']) && isset($_GET['end_date'])) ? 'bg-primary-100 text-primary-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-medium transition-colors">
                                Custom Range
                            </button>
                        </div>

                        <form method="GET" id="customRangeForm" class="flex items-center space-x-3                                                                                                                                                                                                                                                                                                       <?php echo(! isset($_GET['start_date'])) ? 'hidden' : ''; ?>">
                            <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <span class="text-gray-500">to</span>
                            <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700">
                                Apply
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Key Metrics Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Attendance Rate -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-check text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold"><?php echo number_format($attendanceRate, 1); ?>%</div>
                                <div class="text-blue-100 text-sm">Attendance Rate</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-blue-100 text-sm">
                            <span>Avg.                                                                                                                   <?php echo number_format($avgAttendance); ?> present</span>
                            <span class="flex items-center">
                                <i class="fas fa-<?php echo($attendanceRate >= 80) ? 'arrow-up' : (($attendanceRate >= 60) ? 'minus' : 'arrow-down'); ?> mr-1"></i>
                                <?php echo($attendanceRate >= 80) ? 'Good' : (($attendanceRate >= 60) ? 'Fair' : 'Low'); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Revenue Collection -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-money-bill-wave text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold"><?php echo number_format($totalCollected / 1000, 0); ?>K</div>
                                <div class="text-green-100 text-sm">RWF Collected</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-green-100 text-sm">
                            <span>This period</span>
                            <span class="flex items-center">
                                <i class="fas fa-<?php echo($collectionRate >= 70) ? 'arrow-up' : (($collectionRate >= 50) ? 'minus' : 'arrow-down'); ?> mr-1"></i>
                                <?php echo number_format($collectionRate, 0); ?>%
                            </span>
                        </div>
                    </div>

                    <!-- Event Success Rate -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-check text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold"><?php echo number_format($successRate, 1); ?>%</div>
                                <div class="text-purple-100 text-sm">Event Success</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-purple-100 text-sm">
                            <span><?php echo $completedEvents; ?> events completed</span>
                            <span class="flex items-center">
                                <i class="fas fa-<?php echo($successRate >= 90) ? 'check' : (($successRate >= 70) ? 'exclamation' : 'times'); ?> mr-1"></i>
                                <?php echo($successRate >= 90) ? 'Excellent' : (($successRate >= 70) ? 'Good' : 'Needs Improvement'); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Community Engagement -->
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold"><?php echo number_format($engagementRate, 1); ?>%</div>
                                <div class="text-orange-100 text-sm">Engagement</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-orange-100 text-sm">
                            <span>Active participation</span>
                            <span class="flex items-center">
                                <i class="fas fa-<?php echo($engagementRate >= 70) ? 'arrow-up' : (($engagementRate >= 50) ? 'minus' : 'arrow-down'); ?> mr-1"></i>
                                <?php echo($engagementRate >= 70) ? 'High' : (($engagementRate >= 50) ? 'Medium' : 'Low'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Attendance Trends -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Attendance Trends</h3>
                            <div class="flex space-x-2">
                                <button
                                    class="px-3 py-1 text-sm bg-primary-100 text-primary-700 rounded-md font-medium">
                                    Monthly
                                </button>
                                <button class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-md">
                                    Weekly
                                </button>
                            </div>
                        </div>
                        <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                            <canvas id="attendanceTrendsChart"></canvas>
                        </div>
                    </div>

                    <!-- Revenue Analysis -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Revenue Analysis</h3>
                            <div class="flex space-x-2">
                                <button
                                    class="px-3 py-1 text-sm bg-primary-100 text-primary-700 rounded-md font-medium">
                                    Collections
                                </button>
                                <button class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-md">
                                    Outstanding
                                </button>
                            </div>
                        </div>
                        <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Cell Performance -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Cell Performance</h3>
                        <div class="space-y-4">
                            <?php if (! empty($cellPerformance)): ?>
<?php
    $colors     = ['blue', 'green', 'purple', 'orange', 'red', 'indigo', 'pink'];
    $colorIndex = 0;
?>
<?php foreach ($cellPerformance as $cell): ?>
<?php $color = $colors[$colorIndex % count($colors)]; ?>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-3 h-3 bg-<?php echo $color; ?>-500 rounded-full"></div>
                                            <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($cell['cell_name']); ?></span>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                                <div class="bg-<?php echo $color; ?>-500 h-2 rounded-full" style="width:<?php echo $cell['participation_rate']; ?>%"></div>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900 w-10"><?php echo number_format($cell['participation_rate'], 0); ?>%</span>
                                        </div>
                                    </div>
                                    <?php $colorIndex++; ?>
<?php endforeach; ?>
<?php else: ?>
                                <div class="text-center py-4">
                                    <div class="text-gray-500">
                                        <i class="fas fa-info-circle mb-2"></i>
                                        <p>No cell data available for this period</p>
                                        <p class="text-sm">Cell performance will appear here when residents are assigned to cells</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Monthly Goals -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Monthly Goals</h3>
                        <div class="space-y-6">
                            <!-- Attendance Goal -->
                            <?php
                                $attendanceTarget   = 90;
                                $attendanceProgress = $attendanceStats['attendance_rate'] ?? 0;
                                $attendanceProgress = min(100, max(0, $attendanceProgress)); // Ensure 0-100 range
                                $attendanceOffset   = 226.2 - (($attendanceProgress / 100) * 226.2);
                            ?>
                            <div class="text-center">
                                <div class="relative inline-flex">
                                    <svg class="w-20 h-20">
                                        <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="8"
                                            fill="transparent" class="text-gray-200" />
                                        <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="8"
                                            fill="transparent" stroke-dasharray="226.2" stroke-dashoffset="<?php echo $attendanceOffset; ?>"
                                            class="text-blue-500 progress-ring" />
                                    </svg>
                                    <span
                                        class="absolute inset-0 flex items-center justify-center text-sm font-bold text-gray-900">
                                        <?php echo number_format($attendanceProgress, 0); ?>%
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <div class="text-sm font-medium text-gray-900">Attendance Goal</div>
                                    <div class="text-xs text-gray-500">Target:                                                                                                                                                                                                                                           <?php echo $attendanceTarget; ?>%</div>
                                </div>
                            </div>

                            <!-- Collection Goal -->
                            <?php
                                $collectionTarget   = 400000; // 400K RWF
                                $actualCollection   = $fineStats['total_collected'] ?? 0;
                                $collectionProgress = $collectionTarget > 0 ? min(100, ($actualCollection / $collectionTarget) * 100) : 0;
                                $collectionOffset   = 226.2 - (($collectionProgress / 100) * 226.2);
                            ?>
                            <div class="text-center">
                                <div class="relative inline-flex">
                                    <svg class="w-20 h-20">
                                        <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="8"
                                            fill="transparent" class="text-gray-200" />
                                        <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="8"
                                            fill="transparent" stroke-dasharray="226.2" stroke-dashoffset="<?php echo $collectionOffset; ?>"
                                            class="text-green-500 progress-ring" />
                                    </svg>
                                    <span
                                        class="absolute inset-0 flex items-center justify-center text-sm font-bold text-gray-900">
                                        <?php echo number_format($collectionProgress, 0); ?>%
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <div class="text-sm font-medium text-gray-900">Collection Goal</div>
                                    <div class="text-xs text-gray-500">Target:                                                                                                                                                                                                                                           <?php echo number_format($collectionTarget); ?> RWF</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Performers -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Top Performers</h3>
                        <div class="space-y-4">
                            <!-- Best Attendance -->
                            <?php
                                $bestAttendanceCell = null;
                                $highestRate        = 0;
                                foreach ($cellPerformance as $cell) {
                                    if ($cell['participation_rate'] > $highestRate) {
                                        $highestRate        = $cell['participation_rate'];
                                        $bestAttendanceCell = $cell;
                                    }
                                }
                            ?>
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-trophy text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Best Attendance</div>
                                    <div class="text-xs text-gray-500">
                                        <?php if ($bestAttendanceCell): ?>
<?php echo htmlspecialchars($bestAttendanceCell['cell_name']); ?> -<?php echo number_format($bestAttendanceCell['participation_rate'], 0); ?>%
                                        <?php else: ?>
                                            No data available
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-lg font-bold text-yellow-500">🥇</div>
                            </div>

                            <!-- Highest Collection -->
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-green-400 to-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-coins text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Highest Collection</div>
                                    <div class="text-xs text-gray-500">
                                        Total:                                                                                                                                           <?php echo number_format($fineStats['total_collected'] ?? 0); ?> RWF
                                    </div>
                                </div>
                                <div class="text-lg font-bold text-green-500">💰</div>
                            </div>

                            <!-- Most Active -->
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-users text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Most Active</div>
                                    <div class="text-xs text-gray-500">
                                        <?php echo $engagementStats['total_events'] ?? 0; ?> events organized
                                    </div>
                                </div>
                                <div class="text-lg font-bold text-blue-500">⭐</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Reports Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Detailed Reports</h3>
                            <div class="flex items-center space-x-3">
                                <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <option>All Reports</option>
                                    <option>Attendance Reports</option>
                                    <option>Financial Reports</option>
                                    <option>Event Reports</option>
                                </select>
                                <button
                                    class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm hover:bg-primary-700 transition-colors">
                                    <i class="fas fa-download mr-2"></i>
                                    Export
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Report Type
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Period
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Generated
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-chart-line text-blue-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Monthly Attendance Report
                                                </div>
                                                <div class="text-sm text-gray-500">Comprehensive attendance analysis
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">July 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Ready
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 hours ago</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-primary-600 hover:text-primary-900 mr-3">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-money-bill-wave text-green-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Financial Summary Report
                                                </div>
                                                <div class="text-sm text-gray-500">Collections and outstanding amounts
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Q2 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Processing
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Processing...</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-gray-400">
                                            <i class="fas fa-clock"></i>
                                        </button>
                                    </td>
                                </tr>

                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-calendar-alt text-purple-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Event Performance Report
                                                </div>
                                                <div class="text-sm text-gray-500">Event success metrics and analysis
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">June 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Ready
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1 day ago</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-primary-600 hover:text-primary-900 mr-3">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Generate Report Modal -->
    <div id="generateReportModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Generate Custom Report</h3>
                        <button id="closeReportModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                            <select
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="attendance">Attendance Report</option>
                                <option value="financial">Financial Report</option>
                                <option value="events">Events Report</option>
                                <option value="comprehensive">Comprehensive Report</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="date" value="2025-06-01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="date" value="2025-07-25"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cells</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" checked
                                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">All Cells</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Gasabo Only</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Nyarugenge Only</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Kicukiro Only</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                            <select
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="pdf">PDF Document</option>
                                <option value="excel">Excel Spreadsheet</option>
                                <option value="csv">CSV File</option>
                            </select>
                        </div>

                        <div class="flex space-x-3 pt-4">
                            <button type="submit"
                                class="flex-1 bg-gradient-to-r from-primary-600 to-primary-700 text-white py-2 px-4 rounded-lg font-medium hover:from-primary-700 hover:to-primary-800 transition-all">
                                Generate Report
                            </button>
                            <button type="button" id="cancelReportModal"
                                class="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Modal functionality
        const generateReportBtn = document.getElementById('generateReportBtn');
        const generateReportModal = document.getElementById('generateReportModal');
        const closeReportModal = document.getElementById('closeReportModal');
        const cancelReportModal = document.getElementById('cancelReportModal');

        // Only add event listeners if elements exist
        if (generateReportBtn && generateReportModal) {
            generateReportBtn.addEventListener('click', () => {
                generateReportModal.classList.remove('hidden');
            });
        }

        if (closeReportModal && generateReportModal) {
            closeReportModal.addEventListener('click', () => {
                generateReportModal.classList.add('hidden');
            });
        }

        if (cancelReportModal && generateReportModal) {
            cancelReportModal.addEventListener('click', () => {
                generateReportModal.classList.add('hidden');
            });
        }

        // Close modal on outside click
        if (generateReportModal) {
            generateReportModal.addEventListener('click', (e) => {
                if (e.target === generateReportModal) {
                    generateReportModal.classList.add('hidden');
                }
            });
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Reports page loaded, initializing charts...');

            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded!');
                document.getElementById('attendanceTrendsChart').outerHTML = '<div class="text-center p-4 text-red-600 bg-red-50 rounded">Chart.js library not loaded!</div>';
                document.getElementById('revenueChart').outerHTML = '<div class="text-center p-4 text-red-600 bg-red-50 rounded">Chart.js library not loaded!</div>';
                return;
            }

            console.log('Chart.js version:', Chart.version);

            // Check if canvas elements exist
            const attendanceCanvas = document.getElementById('attendanceTrendsChart');
            const revenueCanvas = document.getElementById('revenueChart');

            console.log('Attendance canvas found:', !!attendanceCanvas);
            console.log('Revenue canvas found:', !!revenueCanvas);

            if (!attendanceCanvas || !revenueCanvas) {
                console.error('Canvas elements not found!');
                return;
            }

            // Initialize Charts with dynamic data
            try {
                // Attendance Trends Chart
                console.log('Initializing attendance trends chart...');
                const attendanceTrendsCtx = attendanceCanvas.getContext('2d');
                console.log('Attendance context created:', !!attendanceTrendsCtx);

                // Dynamic chart data from PHP
                const attendanceTrendData =                                            <?php echo json_encode($attendanceTrends); ?>;
                console.log('Attendance trend data:', attendanceTrendData);
                console.log('Attendance trend data length:', attendanceTrendData.length);

                const chartLabels = attendanceTrendData.length > 0 ? attendanceTrendData.map(item => item.month) : ['Current Period'];
                const attendanceRates = attendanceTrendData.length > 0 ? attendanceTrendData.map(item => parseFloat(item.rate) || 0) : [<?php echo $attendanceRate; ?>];

                console.log('Chart labels:', chartLabels);
                console.log('Attendance rates:', attendanceRates);
                const targetData = new Array(chartLabels.length).fill(90); // 90% target

                const attendanceTrendsChart = new Chart(attendanceTrendsCtx, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                        label: 'Attendance Rate %',
                        data: attendanceRates.length > 0 ? attendanceRates : [<?php echo $attendanceStats['attendance_rate'] ?? 0; ?>],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }, {
                        label: 'Target %',
                        data: targetData.length > 0 ? targetData : [90],
                        borderColor: '#ef4444',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        pointHoverRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: '#f3f4f6'
                            },
                            ticks: {
                                callback: function (value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            console.log('Attendance chart created successfully!');

            // Revenue Chart with dynamic data
            console.log('Initializing revenue chart...');
            const revenueCtx = revenueCanvas.getContext('2d');
            console.log('Revenue context created:', !!revenueCtx);

            // Monthly revenue trend data
            const revenueTrendData =                                                                         <?php echo json_encode($revenueTrends); ?>;
            console.log('Revenue trend data:', revenueTrendData);

            const revenueLabels = revenueTrendData.length > 0 ? revenueTrendData.map(item => item.month) : chartLabels;
            const collectedData = revenueTrendData.length > 0 ? revenueTrendData.map(item => parseFloat(item.collected) || 0) : [<?php echo $totalCollected; ?>];
            const pendingData = revenueTrendData.length > 0 ? revenueTrendData.map(item => parseFloat(item.pending) || 0) : [<?php echo $totalPending; ?>];

            console.log('Revenue labels:', revenueLabels);
            console.log('Collected data:', collectedData);
            console.log('Pending data:', pendingData);

            const revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: revenueLabels.length > 0 ? revenueLabels : ['Current Period'],
                    datasets: [{
                        label: 'Collections (RWF)',
                        data: collectedData,
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: '#16a34a',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                    }, {
                        label: 'Pending (RWF)',
                        data: pendingData,
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: '#dc2626',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f3f4f6'
                            },
                            ticks: {
                                callback: function (value) {
                                    return (value / 1000).toFixed(0) + 'K';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('Error initializing charts:', error);
            console.error('Error details:', error.message);
            console.error('Error stack:', error.stack);
            console.log('Attempting to create fallback charts...');

            // Show error in the charts
            document.getElementById('attendanceTrendsChart').outerHTML = '<div class="text-center p-4 text-red-600 bg-red-50 rounded">Chart Error: ' + error.message + '</div>';
            document.getElementById('revenueChart').outerHTML = '<div class="text-center p-4 text-red-600 bg-red-50 rounded">Chart Error: ' + error.message + '</div>';
            return;

            // Fallback: Create simple charts with test data
            try {
                const attendanceCtx = document.getElementById('attendanceTrendsChart');
                if (attendanceCtx) {
                    const fallbackAttendanceChart = new Chart(attendanceCtx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: ['Current Period'],
                            datasets: [{
                                label: 'Attendance Rate %',
                                data: [<?php echo $attendanceRate; ?>],
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 2
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
                    console.log('Fallback attendance chart created');
                }

                const revenueCtx = document.getElementById('revenueChart');
                if (revenueCtx) {
                    const fallbackRevenueChart = new Chart(revenueCtx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: ['Current Period'],
                            datasets: [{
                                label: 'Collections (RWF)',
                                data: [<?php echo $totalCollected; ?>],
                                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                borderColor: '#16a34a',
                                borderWidth: 1
                            }, {
                                label: 'Pending (RWF)',
                                data: [<?php echo $totalPending; ?>],
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
                    console.log('Fallback revenue chart created');
                }
            } catch (fallbackError) {
                console.error('Fallback chart creation also failed:', fallbackError);
                // Show error message
                const errorMsg = '<div class="text-center p-4 text-red-600 bg-red-50 rounded">Error loading charts. Please check the console for details.</div>';
                const attChart = document.getElementById('attendanceTrendsChart');
                const revChart = document.getElementById('revenueChart');
                if (attChart) attChart.outerHTML = errorMsg;
                if (revChart) revChart.outerHTML = errorMsg;
            }
        }
        });
    </script>

<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>