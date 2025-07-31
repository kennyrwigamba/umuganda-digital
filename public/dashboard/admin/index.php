<?php
    /**
     * Admin Dashboard
     * Main dashboard page for admins - sector level management
     */

    session_start();

    // Check if user is logged in
    if (! isset($_SESSION['user_id'])) {
        header('Location: ../../login.php');
        exit;
    }

    // Check if user is admin (superadmins have their own dashboard)
    if ($_SESSION['user_role'] !== 'admin') {
        // Redirect based on role
        if ($_SESSION['user_role'] === 'superadmin') {
            header('Location: ../superadmin/index.php');
        } else {
            header('Location: ../resident/index.php');
        }
        exit;
    }

    // Include required classes
    require_once __DIR__ . '/../../../config/db.php';
    require_once __DIR__ . '/../../../src/models/User.php';
    require_once __DIR__ . '/../../../src/models/Attendance.php';
    require_once __DIR__ . '/../../../src/models/Fine.php';
    require_once __DIR__ . '/../../../src/models/UmugandaEvent.php';

    // Use the global database instance
    global $db;
    $connection = $db->getConnection();

    $user          = new User();
    $attendance    = new Attendance();
    $fine          = new Fine();
    $umugandaEvent = new UmugandaEvent();

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
    $firstName  = htmlspecialchars($adminInfo['first_name']);
    $lastName   = htmlspecialchars($adminInfo['last_name']);
    $fullName   = $firstName . ' ' . $lastName;
    $initials   = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

    // Get admin's assigned sector from admin_assignments table
    $adminSectorQuery = "
    SELECT s.name as sector_name, s.id as sector_id
    FROM admin_assignments aa
    JOIN sectors s ON aa.sector_id = s.id
    WHERE aa.admin_id = ? AND aa.is_active = 1
    LIMIT 1";

    $stmt = $connection->prepare($adminSectorQuery);
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $sectorResult = $stmt->get_result()->fetch_assoc();

    if ($sectorResult) {
        $adminSector   = $sectorResult['sector_name'];
        $adminSectorId = $sectorResult['sector_id'];
    } else {
        // Fallback: try to get sector from user table if available
        if (isset($adminInfo['sector_id']) && $adminInfo['sector_id']) {
            $sectorQuery = "SELECT name FROM sectors WHERE id = ?";
            $stmt        = $connection->prepare($sectorQuery);
            $stmt->bind_param('i', $adminInfo['sector_id']);
            $stmt->execute();
            $sectorData    = $stmt->get_result()->fetch_assoc();
            $adminSector   = $sectorData ? $sectorData['name'] : 'Kimironko'; // Default for demo
            $adminSectorId = $adminInfo['sector_id'];
        } else {
            // Default sector for testing
            $adminSector   = 'Kimironko';
            $adminSectorId = 1; // Assuming Kimironko has ID 1
        }
    }

    // Get dashboard statistics for the admin's sector
    try {
        // Get total residents in admin's sector
        $totalResidentsQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'resident' AND sector_id = ? AND status = 'active'";
        $stmt                = $connection->prepare($totalResidentsQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $totalResidents = $stmt->get_result()->fetch_assoc()['count'];

        // Get new residents this month
        $newResidentsQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'resident' AND sector_id = ? AND status = 'active' AND DATE_FORMAT(created_at, '%Y-%m') = ?";
        $stmt              = $connection->prepare($newResidentsQuery);
        $currentMonth      = date('Y-m');
        $stmt->bind_param('is', $adminSectorId, $currentMonth);
        $stmt->execute();
        $newResidents = $stmt->get_result()->fetch_assoc()['count'];

        // Get latest Umuganda event attendance rate for this sector
        $attendanceQuery = "
        SELECT
            COUNT(a.id) as total_attendance,
            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
            ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 1) as attendance_rate
        FROM attendance a
        JOIN users u ON a.user_id = u.id
        JOIN umuganda_events e ON a.event_id = e.id
        WHERE u.sector_id = ? AND e.event_date = (
            SELECT MAX(event_date) FROM umuganda_events WHERE status = 'completed'
        )";
        $stmt = $connection->prepare($attendanceQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $attendanceData = $stmt->get_result()->fetch_assoc();
        $attendanceRate = $attendanceData['attendance_rate'] ?? 0;

        // Get unpaid fines for admin's sector
        $finesQuery = "
        SELECT
            SUM(f.amount) as total_unpaid,
            COUNT(DISTINCT f.user_id) as residents_with_fines
        FROM fines f
        JOIN users u ON f.user_id = u.id
        WHERE u.sector_id = ? AND f.status = 'pending'";
        $stmt = $connection->prepare($finesQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $finesData          = $stmt->get_result()->fetch_assoc();
        $totalUnpaidFines   = $finesData['total_unpaid'] ?? 0;
        $residentsWithFines = $finesData['residents_with_fines'] ?? 0;

        // Get next Umuganda event
        $nextEventQuery = "SELECT * FROM umuganda_events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 1";
        $stmt           = $connection->prepare($nextEventQuery);
        $stmt->execute();
        $nextEvent     = $stmt->get_result()->fetch_assoc();
        $nextEventDate = $nextEvent ? date('M d', strtotime($nextEvent['event_date'])) : 'Not Scheduled';
        $nextEventDay  = $nextEvent ? date('l', strtotime($nextEvent['event_date'])) : '';
        $nextEventYear = $nextEvent ? date('Y', strtotime($nextEvent['event_date'])) : '';

    } catch (Exception $e) {
        // Default values if queries fail
        $totalResidents     = 0;
        $newResidents       = 0;
        $attendanceRate     = 0;
        $totalUnpaidFines   = 0;
        $residentsWithFines = 0;
        $nextEventDate      = 'Not Scheduled';
        $nextEventDay       = '';
        $nextEventYear      = '';
    }

    // Format unpaid fines for display
    $finesDisplay = number_format($totalUnpaidFines / 1000, 0) . 'K';
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

        <!-- Dashboard Content -->
        <main class="p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Title -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 ml-4 lg:ml-0">Dashboard Overview</h1>
                    <p class="text-sm text-gray-600 ml-4 lg:ml-0 mt-1">Managing                                                                                                                                                               <?php echo htmlspecialchars($adminSector); ?> Sector</p>
                </div>
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Residents -->
                    <div
                        class="bg-gradient-to-br from-white to-blue-50 rounded-xl shadow-sm p-6 border border-blue-100 hover:shadow-lg hover:border-blue-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Total Residents
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo number_format($totalResidents); ?></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-success-600 font-semibold bg-success-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-arrow-up text-xs mr-1"></i>
                                        +<?php echo $newResidents; ?>
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">this month</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-users text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Rate -->
                    <div
                        class="bg-gradient-to-br from-white to-green-50 rounded-xl shadow-sm p-6 border border-green-100 hover:shadow-lg hover:border-green-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-green-600 uppercase tracking-wide">Attendance Rate
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $attendanceRate; ?>%</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm                                                                                                                                                               <?php echo $attendanceRate >= 80 ? 'text-success-600 bg-success-50' : 'text-warning-600 bg-warning-50'; ?> font-semibold px-2 py-1 rounded-full">
                                        <i class="fas                                                                                                           <?php echo $attendanceRate >= 80 ? 'fa-arrow-up' : 'fa-arrow-down'; ?> text-xs mr-1"></i>
                                        <?php echo $attendanceRate >= 80 ? 'Good' : 'Needs Improvement'; ?>
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">last session</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-success-500 to-success-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-chart-line text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Unpaid Fines -->
                    <div
                        class="bg-gradient-to-br from-white to-orange-50 rounded-xl shadow-sm p-6 border border-orange-100 hover:shadow-lg hover:border-orange-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Unpaid Fines
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $finesDisplay; ?> <span
                                        class="text-lg text-orange-700 font-bold">RWF</span></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-danger-600 font-semibold bg-danger-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-exclamation-circle text-xs mr-1"></i>
                                        <?php echo $residentsWithFines; ?>
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">residents owe fines</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Next Umuganda -->
                    <div
                        class="bg-gradient-to-br from-white to-purple-50 rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-lg hover:border-purple-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-purple-600 uppercase tracking-wide">Next Umuganda
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $nextEventDate; ?></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-primary-600 font-semibold bg-primary-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-calendar text-xs mr-1"></i>
                                        <?php echo $nextEventYear; ?>
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium"><?php echo $nextEventDay; ?></span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-calendar-alt text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Attendance Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Attendance Trends</h3>
                            <div class="flex space-x-2">
                                <button
                                    class="px-3 py-1 text-sm bg-primary-100 text-primary-700 rounded-md font-medium hover:bg-primary-200 transition-colors">6M</button>
                                <button
                                    class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-md transition-colors">1Y</button>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>

                    <!-- Fines Distribution -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Fines Distribution</h3>
                            <div class="flex space-x-2">
                                <button
                                    class="px-3 py-1 text-sm bg-primary-100 text-primary-700 rounded-md font-medium hover:bg-primary-200 transition-colors">This
                                    Month</button>
                                <button
                                    class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-md transition-colors">All
                                    Time</button>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="finesChart"></canvas>
                        </div>
                    </div>
                </div>

                <?php
                    // Get attendance data for chart (last 6 months)
                    $attendanceChartQuery = "
                    SELECT
                        DATE_FORMAT(e.event_date, '%b') as month,
                        ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 1) as attendance_rate
                    FROM umuganda_events e
                    LEFT JOIN attendance a ON e.id = a.event_id
                    LEFT JOIN users u ON a.user_id = u.id
                    WHERE e.event_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                        AND e.status = 'completed'
                        AND (u.sector_id = ? OR u.sector_id IS NULL)
                    GROUP BY DATE_FORMAT(e.event_date, '%Y-%m'), DATE_FORMAT(e.event_date, '%b')
                    ORDER BY e.event_date ASC";

                    $stmt = $connection->prepare($attendanceChartQuery);
                    $stmt->bind_param('i', $adminSectorId);
                    $stmt->execute();
                    $attendanceChartData = $stmt->get_result();

                    $chartLabels = [];
                    $chartData   = [];
                    while ($row = $attendanceChartData->fetch_assoc()) {
                        $chartLabels[] = $row['month'];
                        $chartData[]   = $row['attendance_rate'] ?? 0;
                    }

                    // Ensure we have at least 6 data points
                    $months        = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    $currentMonth  = (int) date('n') - 1; // 0-based index
                    $defaultLabels = [];
                    $defaultData   = [];

                    for ($i = 5; $i >= 0; $i--) {
                        $monthIndex      = ($currentMonth - $i + 12) % 12;
                        $defaultLabels[] = $months[$monthIndex];
                        $defaultData[]   = 0;
                    }

                    if (empty($chartLabels)) {
                        $chartLabels = $defaultLabels;
                        $chartData   = $defaultData;
                    }

                    // Get fines distribution data
                    $finesDistributionQuery = "
                    SELECT
                        f.reason,
                        SUM(f.amount) as total_amount,
                        COUNT(*) as count
                    FROM fines f
                    JOIN users u ON f.user_id = u.id
                    WHERE u.sector_id = ? AND f.status IN ('pending', 'paid')
                    GROUP BY f.reason";

                    $stmt = $connection->prepare($finesDistributionQuery);
                    $stmt->bind_param('i', $adminSectorId);
                    $stmt->execute();
                    $finesDistributionData = $stmt->get_result();

                    $finesLabels  = [];
                    $finesAmounts = [];
                    $finesColors  = ['#ef4444', '#f59e0b', '#f97316', '#22c55e'];
                    $colorIndex   = 0;

                    while ($row = $finesDistributionData->fetch_assoc()) {
                        $finesLabels[]  = ucfirst(str_replace('_', ' ', $row['reason']));
                        $finesAmounts[] = $row['total_amount'];
                    }

                    // Add default data if no fines
                    if (empty($finesLabels)) {
                        $finesLabels  = ['No Data Available'];
                        $finesAmounts = [1];
                        $finesColors  = ['#e5e7eb'];
                    }
                ?>

                <!-- Data Tables Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Residents -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Residents</h3>
                                <button
                                    class="text-sm text-primary-600 hover:text-primary-700 font-medium transition-colors">View
                                    All</button>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Resident</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cell</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php
                                        // Get recent residents from admin's sector
                                        $recentResidentsQuery = "
                                        SELECT u.first_name, u.last_name, u.national_id, u.status, u.created_at,
                                               COALESCE(c.name, 'No Cell') as cell_name
                                        FROM users u
                                        LEFT JOIN cells c ON u.cell_id = c.id
                                        WHERE u.role = 'resident' AND u.sector_id = ?
                                        ORDER BY u.created_at DESC
                                        LIMIT 5";
                                        $stmt = $connection->prepare($recentResidentsQuery);
                                        $stmt->bind_param('i', $adminSectorId);
                                        $stmt->execute();
                                        $recentResidents = $stmt->get_result();

                                        $colors     = ['primary', 'warning', 'success', 'danger', 'purple'];
                                        $colorIndex = 0;

                                        while ($resident = $recentResidents->fetch_assoc()):
                                            $initials = strtoupper(substr($resident['first_name'], 0, 1) . substr($resident['last_name'], 0, 1));
                                            $color    = $colors[$colorIndex % count($colors)];
                                            $colorIndex++;
                                            $statusColor = $resident['status'] === 'active' ? 'success' : 'warning';
                                            $statusText  = ucfirst($resident['status']);
                                        ?>
		                                    <tr class="hover:bg-gray-50 transition-colors">
		                                        <td class="px-6 py-4 whitespace-nowrap">
		                                            <div class="flex items-center">
		                                                <div
		                                                    class="w-10 h-10 bg-gradient-to-br from-<?php echo $color; ?>-500 to-<?php echo $color; ?>-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
		                                                    <span class="text-white text-sm font-semibold"><?php echo $initials; ?></span>
		                                                </div>
		                                                <div>
		                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?></div>
		                                                    <div class="text-sm text-gray-500">ID:		                                                                                          	                                                                                           <?php echo htmlspecialchars($resident['national_id']); ?></div>
		                                                </div>
		                                            </div>
		                                        </td>
		                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($resident['cell_name']); ?></td>
		                                        <td class="px-6 py-4 whitespace-nowrap">
		                                            <span
		                                                class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-<?php echo $statusColor; ?>-100 text-<?php echo $statusColor; ?>-800"><?php echo $statusText; ?></span>
		                                        </td>
		                                    </tr>
		                                    <?php endwhile; ?>

                                    <?php if ($recentResidents->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No residents found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Outstanding Fines -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Outstanding Fines</h3>
                                <button
                                    class="text-sm text-primary-600 hover:text-primary-700 font-medium transition-colors">View
                                    All</button>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Resident</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reason</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php
                                        // Get outstanding fines from admin's sector
                                        $outstandingFinesQuery = "
                                        SELECT u.first_name, u.last_name, u.national_id, f.amount, f.reason
                                        FROM fines f
                                        JOIN users u ON f.user_id = u.id
                                        WHERE u.sector_id = ? AND f.status = 'pending'
                                        ORDER BY f.amount DESC
                                        LIMIT 5";
                                        $stmt = $connection->prepare($outstandingFinesQuery);
                                        $stmt->bind_param('i', $adminSectorId);
                                        $stmt->execute();
                                        $outstandingFines = $stmt->get_result();

                                        $colorIndex = 0;

                                        while ($fine = $outstandingFines->fetch_assoc()):
                                            $initials    = strtoupper(substr($fine['first_name'], 0, 1) . substr($fine['last_name'], 0, 1));
                                            $color       = $fine['amount'] >= 20000 ? 'danger' : ($fine['amount'] >= 10000 ? 'warning' : 'orange');
                                            $reasonText  = str_replace('_', ' ', ucfirst($fine['reason']));
                                            $reasonColor = $fine['reason'] === 'absence' ? 'danger' : ($fine['reason'] === 'late_arrival' ? 'warning' : 'orange');
                                        ?>
		                                    <tr class="hover:bg-gray-50 transition-colors">
		                                        <td class="px-6 py-4 whitespace-nowrap">
		                                            <div class="flex items-center">
		                                                <div
		                                                    class="w-10 h-10 bg-gradient-to-br from-<?php echo $color; ?>-500 to-<?php echo $color; ?>-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
		                                                    <span class="text-white text-sm font-semibold"><?php echo $initials; ?></span>
		                                                </div>
		                                                <div>
		                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($fine['first_name'] . ' ' . $fine['last_name']); ?></div>
		                                                    <div class="text-sm text-gray-500">ID:		                                                                                          	                                                                                           <?php echo htmlspecialchars($fine['national_id']); ?></div>
		                                                </div>
		                                            </div>
		                                        </td>
		                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo number_format($fine['amount']); ?> RWF</td>
		                                        <td class="px-6 py-4 whitespace-nowrap">
		                                            <span
		                                                class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-<?php echo $reasonColor; ?>-100 text-<?php echo $reasonColor; ?>-800"><?php echo $reasonText; ?></span>
		                                        </td>
		                                    </tr>
		                                    <?php endwhile; ?>

                                    <?php if ($outstandingFines->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No outstanding fines</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Charts
            // Attendance Chart
            const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
            const attendanceChart = new Chart(attendanceCtx, {
                type: 'line',
                data: {
                    labels:                                                       <?php echo json_encode($chartLabels); ?>,
                    datasets: [{
                        label: 'Attendance Rate %',
                        data:                                                           <?php echo json_encode($chartData); ?>,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: '#f3f4f6',
                                borderDash: [2, 2]
                            },
                            ticks: {
                                callback: function (value) {
                                    return value + '%';
                                },
                                color: '#6b7280',
                                font: {
                                    size: 12
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6b7280',
                                font: {
                                    size: 12
                                }
                            }
                        }
                    },
                    elements: {
                        point: {
                            hoverRadius: 8
                        }
                    }
                }
            });

            // Fines Distribution Chart
            const finesCtx = document.getElementById('finesChart').getContext('2d');
            const finesChart = new Chart(finesCtx, {
                type: 'doughnut',
                data: {
                    labels:                                                       <?php echo json_encode($finesLabels); ?>,
                    datasets: [{
                        data:                                                           <?php echo json_encode($finesAmounts); ?>,
                        backgroundColor:                                                                                 <?php echo json_encode(array_slice($finesColors, 0, count($finesLabels))); ?>,
                        borderWidth: 0,
                        cutout: '65%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 12
                                },
                                color: '#6b7280'
                            }
                        }
                    }
                }
            });
        });
    </script>

<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>
