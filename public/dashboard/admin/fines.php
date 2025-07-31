<?php
    /**
     * Fines Management - Admin Dashboard
     * Dynamic fines management for assigned sector
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

    // Include required files
    require_once __DIR__ . '/../../../config/db.php';
    require_once __DIR__ . '/../../../src/models/User.php';

    // Use the global database instance
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

    // Get fines statistics for admin's sector
    try {
        // Get total outstanding fines
        $outstandingQuery = "
        SELECT SUM(f.amount) as total_amount, COUNT(*) as count
        FROM fines f
        JOIN users u ON f.user_id = u.id
        WHERE u.sector_id = ? AND f.status = 'pending'";
        $stmt = $connection->prepare($outstandingQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $outstandingResult = $stmt->get_result()->fetch_assoc();
        $outstandingAmount = $outstandingResult['total_amount'] ?? 0;
        $outstandingCount  = $outstandingResult['count'] ?? 0;

        // Get collected this month
        $collectedQuery = "
        SELECT SUM(f.amount) as total_amount
        FROM fines f
        JOIN users u ON f.user_id = u.id
        WHERE u.sector_id = ? AND f.status = 'paid'
        AND MONTH(f.paid_date) = MONTH(CURRENT_DATE())
        AND YEAR(f.paid_date) = YEAR(CURRENT_DATE())";
        $stmt = $connection->prepare($collectedQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $collectedResult = $stmt->get_result()->fetch_assoc();
        $collectedAmount = $collectedResult['total_amount'] ?? 0;

        // Get average fine amount
        $averageQuery = "
        SELECT AVG(f.amount) as avg_amount
        FROM fines f
        JOIN users u ON f.user_id = u.id
        WHERE u.sector_id = ?";
        $stmt = $connection->prepare($averageQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $averageResult = $stmt->get_result()->fetch_assoc();
        $averageAmount = $averageResult['avg_amount'] ?? 0;

        // Get payment rate
        $totalFinesQuery = "
        SELECT COUNT(*) as total_count
        FROM fines f
        JOIN users u ON f.user_id = u.id
        WHERE u.sector_id = ?";
        $stmt = $connection->prepare($totalFinesQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $totalFinesResult = $stmt->get_result()->fetch_assoc();
        $totalFines       = $totalFinesResult['total_count'] ?? 0;

        $paidFinesQuery = "
        SELECT COUNT(*) as paid_count
        FROM fines f
        JOIN users u ON f.user_id = u.id
        WHERE u.sector_id = ? AND f.status = 'paid'";
        $stmt = $connection->prepare($paidFinesQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $paidFinesResult = $stmt->get_result()->fetch_assoc();
        $paidFines       = $paidFinesResult['paid_count'] ?? 0;

        $paymentRate = $totalFines > 0 ? round(($paidFines / $totalFines) * 100, 1) : 0;

        // Get chart data - Collections over last 6 months
        $collectionsChartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-$i months"));
            $monthEnd   = date('Y-m-t', strtotime("-$i months"));
            $monthName  = date('M', strtotime("-$i months"));

            $monthQuery = "
            SELECT COALESCE(SUM(f.amount), 0) as total
            FROM fines f
            JOIN users u ON f.user_id = u.id
            WHERE u.sector_id = ? AND f.status = 'paid'
            AND DATE(f.paid_date) BETWEEN ? AND ?";

            $stmt = $connection->prepare($monthQuery);
            $stmt->bind_param('iss', $adminSectorId, $monthStart, $monthEnd);
            $stmt->execute();
            $monthTotal = $stmt->get_result()->fetch_assoc()['total'];

            $collectionsChartData[] = [
                'month'  => $monthName,
                'amount' => (int) $monthTotal,
            ];
        }

        // Get fine types distribution
        $fineTypesQuery = "
        SELECT
            f.reason,
            COUNT(*) as count
        FROM fines f
        JOIN users u ON f.user_id = u.id
        WHERE u.sector_id = ?
        GROUP BY f.reason
        ORDER BY count DESC";

        $stmt = $connection->prepare($fineTypesQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $fineTypesResult = $stmt->get_result();

        $fineTypesData  = [];
        $fineTypeLabels = [];
        while ($row = $fineTypesResult->fetch_assoc()) {
            $label = match ($row['reason']) {
                'absence' => 'Absence',
                'late_arrival' => 'Late Arrival',
                'early_departure' => 'Early Departure',
                'other' => 'Other',
                default => ucfirst($row['reason'])
            };
            $fineTypeLabels[] = $label;
            $fineTypesData[]  = (int) $row['count'];
        }

        // Fill with defaults if no data
        if (empty($fineTypesData)) {
            $fineTypeLabels = ['Absence', 'Late Arrival', 'Early Departure', 'Other'];
            $fineTypesData  = [0, 0, 0, 0];
        }

    } catch (Exception $e) {
        // Default values if queries fail
        $outstandingAmount = 0;
        $outstandingCount  = 0;
        $collectedAmount   = 0;
        $averageAmount     = 0;
        $paymentRate       = 0;

        // Default chart data
        $collectionsChartData = [
            ['month' => 'Jan', 'amount' => 0],
            ['month' => 'Feb', 'amount' => 0],
            ['month' => 'Mar', 'amount' => 0],
            ['month' => 'Apr', 'amount' => 0],
            ['month' => 'May', 'amount' => 0],
            ['month' => 'Jun', 'amount' => 0],
        ];
        $fineTypeLabels = ['Absence', 'Late Arrival', 'Early Departure', 'Other'];
        $fineTypesData  = [0, 0, 0, 0];
    }

    // Get residents in admin's sector for dropdown
    $residentsQuery = "SELECT id, first_name, last_name, national_id FROM users WHERE role = 'resident' AND sector_id = ? ORDER BY first_name, last_name";
    $stmt           = $connection->prepare($residentsQuery);
    $stmt->bind_param('i', $adminSectorId);
    $stmt->execute();
    $residentsResult = $stmt->get_result();
    $residents       = [];
    while ($resident = $residentsResult->fetch_assoc()) {
        $residents[] = $resident;
    }

    // Handle pagination
    $page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit  = 10;
    $offset = ($page - 1) * $limit;

    // Handle search and filters
    $searchTerm   = isset($_GET['search']) ? $_GET['search'] : '';
    $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
    $typeFilter   = isset($_GET['type']) ? $_GET['type'] : '';
    $dateFrom     = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    $dateTo       = isset($_GET['date_to']) ? $_GET['date_to'] : '';

    // Build WHERE clause for filters
    $whereConditions = ["u.sector_id = ?"];
    $params          = [$adminSectorId];
    $paramTypes      = 'i';

    if (! empty($searchTerm)) {
        $whereConditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.national_id LIKE ?)";
        $searchParam       = "%$searchTerm%";
        $params[]          = $searchParam;
        $params[]          = $searchParam;
        $params[]          = $searchParam;
        $paramTypes .= 'sss';
    }

    if (! empty($statusFilter)) {
        $whereConditions[] = "f.status = ?";
        $params[]          = $statusFilter;
        $paramTypes .= 's';
    }

    if (! empty($typeFilter)) {
        $whereConditions[] = "f.reason = ?";
        $params[]          = $typeFilter;
        $paramTypes .= 's';
    }

    if (! empty($dateFrom)) {
        $whereConditions[] = "DATE(f.created_at) >= ?";
        $params[]          = $dateFrom;
        $paramTypes .= 's';
    }

    if (! empty($dateTo)) {
        $whereConditions[] = "DATE(f.created_at) <= ?";
        $params[]          = $dateTo;
        $paramTypes .= 's';
    }

    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

    // Get total count for pagination
    $countQuery = "
    SELECT COUNT(*) as total
    FROM fines f
    JOIN users u ON f.user_id = u.id
    $whereClause";
    $stmt = $connection->prepare($countQuery);
    if (! empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $totalFinesForPagination = $stmt->get_result()->fetch_assoc()['total'];
    $totalPages              = ceil($totalFinesForPagination / $limit);

    // Get fines with user details
    $finesQuery = "
    SELECT
        f.*,
        u.first_name,
        u.last_name,
        u.national_id,
        ue.title as event_title
    FROM fines f
    JOIN users u ON f.user_id = u.id
    LEFT JOIN umuganda_events ue ON f.event_id = ue.id
    $whereClause
    ORDER BY f.created_at DESC
    LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $paramTypes .= 'ii';

    $stmt = $connection->prepare($finesQuery);
    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $finesResult = $stmt->get_result();
    $fines       = [];
    while ($fine = $finesResult->fetch_assoc()) {
        $fines[] = $fine;
    }

    // Helper functions
    function formatAmount($amount)
    {
        return number_format($amount, 0, '.', ',');
    }

    function getStatusBadge($status)
    {
        switch ($status) {
            case 'paid':
                return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800"><i class="fas fa-check mr-1"></i>Paid</span>';
            case 'pending':
                return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-danger-100 text-danger-800"><i class="fas fa-exclamation-circle mr-1"></i>Unpaid</span>';
            case 'waived':
                return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800"><i class="fas fa-times mr-1"></i>Waived</span>';
            case 'disputed':
                return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800"><i class="fas fa-question mr-1"></i>Disputed</span>';
            default:
                return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
        }
    }

    function getReasonBadge($reason)
    {
        switch ($reason) {
            case 'absence':
                return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"><i class="fas fa-user-times mr-1"></i>Absence</span>';
            case 'late_arrival':
                return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800"><i class="fas fa-clock mr-1"></i>Late Arrival</span>';
            case 'early_departure':
                return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"><i class="fas fa-door-open mr-1"></i>Early Departure</span>';
            case 'other':
                return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800"><i class="fas fa-ellipsis-h mr-1"></i>Other</span>';
            default:
                return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
        }
    }

    function isOverdue($dueDate, $status)
    {
        if ($status === 'paid' || $status === 'waived') {
            return false;
        }
        if (! $dueDate) {
            return false;
        }
        return strtotime($dueDate) < time();
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

        <!-- Fines Management Content -->
        <main class="p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 ml-4 lg:ml-0">Fines Management</h1>
                            <p class="text-gray-600 mt-1 ml-4 lg:ml-0">Monitor and manage Umuganda fines & payments</p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                            <button
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Export Report
                            </button>
                            <button id="addFineBtn"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg text-sm font-medium hover:from-primary-700 hover:to-primary-800 shadow-sm transition-all">
                                <i class="fas fa-plus mr-2"></i>
                                Add Fine
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Fines Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Outstanding -->
                    <div
                        class="bg-gradient-to-br from-white to-red-50 rounded-xl shadow-sm p-6 border border-red-100 hover:shadow-lg hover:border-red-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-red-600 uppercase tracking-wide">Outstanding Fines
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo formatAmount($outstandingAmount)?> <span
                                        class="text-lg text-red-700 font-bold">RWF</span></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-danger-600 font-semibold bg-danger-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-exclamation-circle text-xs mr-1"></i>
                                        <?php echo $outstandingCount?>
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">unpaid fines</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-danger-500 to-danger-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Collected This Month -->
                    <div
                        class="bg-gradient-to-br from-white to-green-50 rounded-xl shadow-sm p-6 border border-green-100 hover:shadow-lg hover:border-green-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-green-600 uppercase tracking-wide">Collected This
                                    Month</p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo formatAmount($collectedAmount)?> <span
                                        class="text-lg text-green-700 font-bold">RWF</span></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-success-600 font-semibold bg-success-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-arrow-up text-xs mr-1"></i>
                                        Current
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">month collection</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-success-500 to-success-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-money-check-alt text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Average Fine Amount -->
                    <div
                        class="bg-gradient-to-br from-white to-orange-50 rounded-xl shadow-sm p-6 border border-orange-100 hover:shadow-lg hover:border-orange-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Average Fine
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo formatAmount($averageAmount)?> <span
                                        class="text-lg text-orange-700 font-bold">RWF</span></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-warning-600 font-semibold bg-warning-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-equals text-xs mr-1"></i>
                                        Average
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">fine amount</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-calculator text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Rate -->
                    <div
                        class="bg-gradient-to-br from-white to-blue-50 rounded-xl shadow-sm p-6 border border-blue-100 hover:shadow-lg hover:border-blue-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Payment Rate</p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $paymentRate?>%</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-primary-600 font-semibold bg-primary-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-percentage text-xs mr-1"></i>
                                        <?php echo $paymentRate >= 70 ? 'Good' : ($paymentRate >= 50 ? 'Fair' : 'Poor')?>
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">collection rate</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-chart-pie text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Fine Collections Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Fine Collections</h3>
                            <div class="flex space-x-2">
                                <button
                                    class="px-3 py-1 text-sm bg-primary-100 text-primary-700 rounded-md font-medium hover:bg-primary-200 transition-colors">6M</button>
                                <button
                                    class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-md transition-colors">1Y</button>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="collectionsChart"></canvas>
                        </div>
                    </div>

                    <!-- Fine Types Distribution -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Fine Types</h3>
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
                            <canvas id="fineTypesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Filters and Controls -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-8">
                    <form method="GET" action="" class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <!-- Date Range Filter -->
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                                <div class="flex gap-2">
                                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom)?>"
                                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <span class="flex items-center text-gray-500">to</span>
                                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo)?>"
                                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                                <select name="status"
                                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''?>>Unpaid</option>
                                    <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''?>>Paid</option>
                                    <option value="waived" <?php echo $statusFilter === 'waived' ? 'selected' : ''?>>Waived</option>
                                    <option value="disputed" <?php echo $statusFilter === 'disputed' ? 'selected' : ''?>>Disputed</option>
                                </select>
                            </div>

                            <!-- Fine Type Filter -->
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fine Type</label>
                                <select name="type"
                                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="">All Types</option>
                                    <option value="absence" <?php echo $typeFilter === 'absence' ? 'selected' : ''?>>Absence</option>
                                    <option value="late_arrival" <?php echo $typeFilter === 'late_arrival' ? 'selected' : ''?>>Late Arrival</option>
                                    <option value="early_departure" <?php echo $typeFilter === 'early_departure' ? 'selected' : ''?>>Early Departure</option>
                                    <option value="other" <?php echo $typeFilter === 'other' ? 'selected' : ''?>>Other</option>
                                </select>
                            </div>

                            <!-- Search -->
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                                <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm)?>"
                                    placeholder="Name or ID..."
                                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit"
                                class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                                <i class="fas fa-filter mr-2"></i>
                                Apply Filters
                            </button>
                            <a href="?"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                Clear All
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Fines Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Fines & Payments</h3>
                            <div class="flex items-center space-x-3">
                                <div class="text-sm text-gray-600">
                                    Showing <span class="font-medium"><?php echo min(($page - 1) * $limit + 1, $totalFinesForPagination)?>-<?php echo min($page * $limit, $totalFinesForPagination)?></span> of <span
                                        class="font-medium"><?php echo $totalFinesForPagination?></span> fines
                                </div>
                                <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                                    <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1?><?php echo $searchTerm ? '&search=' . urlencode($searchTerm) : ''?><?php echo $statusFilter ? '&status=' . urlencode($statusFilter) : ''?><?php echo $typeFilter ? '&type=' . urlencode($typeFilter) : ''?><?php echo $dateFrom ? '&date_from=' . urlencode($dateFrom) : ''?><?php echo $dateTo ? '&date_to=' . urlencode($dateTo) : ''?>"
                                        class="px-3 py-1 text-xs bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                    <?php else: ?>
                                    <span class="px-3 py-1 text-xs bg-gray-100 text-gray-400">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                    <?php endif; ?>

                                    <span class="px-3 py-1 text-xs bg-white text-gray-700"><?php echo $page?></span>

                                    <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?php echo $page + 1?><?php echo $searchTerm ? '&search=' . urlencode($searchTerm) : ''?><?php echo $statusFilter ? '&status=' . urlencode($statusFilter) : ''?><?php echo $typeFilter ? '&type=' . urlencode($typeFilter) : ''?><?php echo $dateFrom ? '&date_from=' . urlencode($dateFrom) : ''?><?php echo $dateTo ? '&date_to=' . urlencode($dateTo) : ''?>"
                                        class="px-3 py-1 text-xs bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                    <?php else: ?>
                                    <span class="px-3 py-1 text-xs bg-gray-100 text-gray-400">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox"
                                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Resident
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fine Type
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Issue Date
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($fines)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-file-invoice text-4xl mb-4"></i>
                                            <p class="text-lg font-medium">No fines found</p>
                                            <p class="text-sm">No fines match your current filters.</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
<?php foreach ($fines as $fine): ?>
<?php
    $isOverdueStatus = isOverdue($fine['due_date'], $fine['status']);
    $initials        = strtoupper(substr($fine['first_name'], 0, 1) . substr($fine['last_name'], 0, 1));

    // Determine card color based on status
    $cardColor = 'from-primary-500 to-primary-600';
    if ($fine['status'] === 'paid') {
        $cardColor = 'from-success-500 to-success-600';
    } elseif ($isOverdueStatus) {
        $cardColor = 'from-red-600 to-red-700';
    } elseif ($fine['status'] === 'pending') {
        $cardColor = 'from-danger-500 to-danger-600';
    } elseif ($fine['status'] === 'waived') {
        $cardColor = 'from-gray-500 to-gray-600';
    }
?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" value="<?php echo $fine['id']?>"
                                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-gradient-to-br <?php echo $cardColor?> rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                <span class="text-white text-sm font-semibold"><?php echo $initials?></span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($fine['first_name'] . ' ' . $fine['last_name'])?></div>
                                                <div class="text-sm text-gray-500">ID: <?php echo htmlspecialchars($fine['national_id'])?> • <?php echo htmlspecialchars($adminSector)?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo getReasonBadge($fine['reason'])?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo formatAmount($fine['amount'])?> RWF</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M j, Y', strtotime($fine['created_at']))?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($isOverdueStatus): ?>
                                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-clock mr-1"></i>Overdue
                                            </span>
                                        <?php else: ?>
                                            <?php echo getStatusBadge($fine['status'])?>
<?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <?php if ($fine['status'] === 'pending'): ?>
                                        <button class="text-success-600 hover:text-success-900 mr-3 mark-paid-btn"
                                            data-fine-id="<?php echo $fine['id']?>" title="Mark as Paid">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <?php endif; ?>

                                        <button class="text-primary-600 hover:text-primary-900 mr-3 edit-fine-btn"
                                            data-fine-id="<?php echo $fine['id']?>" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button class="text-gray-400 hover:text-gray-600 view-fine-btn"
                                            data-fine-id="<?php echo $fine['id']?>" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
<?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bulk Actions Footer -->
                <div class="mt-6 bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-600">Bulk Actions:</span>
                            <div class="flex space-x-2">
                                <button
                                    class="px-3 py-2 text-sm bg-success-100 text-success-700 rounded-md hover:bg-success-200 transition-colors">
                                    <i class="fas fa-check mr-1"></i>
                                    Mark as Paid
                                </button>
                                <button
                                    class="px-3 py-2 text-sm bg-warning-100 text-warning-700 rounded-md hover:bg-warning-200 transition-colors">
                                    <i class="fas fa-paper-plane mr-1"></i>
                                    Send Reminder
                                </button>
                                <button
                                    class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-download mr-1"></i>
                                    Export Selected
                                </button>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">0</span> selected fines
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Fine Modal -->
    <div id="addFineModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Add New Fine</h3>
                        <button id="closeFineModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form id="addFineForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Resident</label>
                            <select name="resident_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">Select a resident</option>
                                <?php foreach ($residents as $resident): ?>
                                <option value="<?php echo $resident['id']?>">
                                    <?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name'])?>
                                    (ID: <?php echo htmlspecialchars($resident['national_id'])?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fine Type</label>
                            <select name="reason" id="fineTypeSelect" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="absence" data-amount="15000">Absence (15,000 RWF)</option>
                                <option value="late_arrival" data-amount="5000">Late Arrival (5,000 RWF)</option>
                                <option value="early_departure" data-amount="3000">Early Departure (3,000 RWF)</option>
                                <option value="other" data-amount="0">Other (Custom Amount)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount (RWF)</label>
                            <input type="number" name="amount" id="fineAmountInput" value="15000" step="1000" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                            <input type="date" name="due_date" value="<?php echo date('Y-m-d', strtotime('+30 days'))?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason Description</label>
                            <textarea name="reason_description" rows="3" placeholder="Detailed reason for fine..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"></textarea>
                        </div>

                        <div class="flex space-x-3 pt-4">
                            <button type="submit"
                                class="flex-1 bg-gradient-to-r from-primary-600 to-primary-700 text-white py-2 px-4 rounded-lg font-medium hover:from-primary-700 hover:to-primary-800 transition-all">
                                <i class="fas fa-plus mr-2"></i>
                                Add Fine
                            </button>
                            <button type="button" id="cancelFineModal"
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
        const addFineBtn = document.getElementById('addFineBtn');
        const addFineModal = document.getElementById('addFineModal');
        const closeFineModal = document.getElementById('closeFineModal');
        const cancelFineModal = document.getElementById('cancelFineModal');
        const addFineForm = document.getElementById('addFineForm');
        const fineTypeSelect = document.getElementById('fineTypeSelect');
        const fineAmountInput = document.getElementById('fineAmountInput');

        // Show modal
        addFineBtn.addEventListener('click', () => {
            addFineModal.classList.remove('hidden');
        });

        // Hide modal
        function hideModal() {
            addFineModal.classList.add('hidden');
            addFineForm.reset();
        }

        closeFineModal.addEventListener('click', hideModal);
        cancelFineModal.addEventListener('click', hideModal);

        // Close modal on outside click
        addFineModal.addEventListener('click', (e) => {
            if (e.target === addFineModal) {
                hideModal();
            }
        });

        // Update amount based on fine type
        fineTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const amount = selectedOption.getAttribute('data-amount');
            if (amount && amount !== '0') {
                fineAmountInput.value = amount;
            }
        });

        // Handle Add Fine form submission
        addFineForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('/public/api/fines.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Show success message
                    showNotification('Fine added successfully!', 'success');

                    // Hide modal and refresh page
                    hideModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(result.message || 'Failed to add fine', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Network error occurred', 'error');
            } finally {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });

        // Handle Mark as Paid buttons
        document.addEventListener('click', async function(e) {
            if (e.target.closest('.mark-paid-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.mark-paid-btn');
                const fineId = btn.getAttribute('data-fine-id');

                if (confirm('Mark this fine as paid?')) {
                    const originalHtml = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    btn.disabled = true;

                    try {
                        const response = await fetch('/public/api/fines.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=mark_paid&fine_id=${fineId}`
                        });

                        const result = await response.json();

                        if (result.success) {
                            showNotification('Fine marked as paid!', 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showNotification(result.message || 'Failed to update fine', 'error');
                            btn.innerHTML = originalHtml;
                            btn.disabled = false;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showNotification('Network error occurred', 'error');
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }
                }
            }
        });

        // Notification function
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'} mr-2"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Auto remove
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Charts
            if (typeof Chart !== 'undefined') {
                // Fine Collections Chart
                const collectionsCtx = document.getElementById('collectionsChart').getContext('2d');
                const collectionsChart = new Chart(collectionsCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_column($collectionsChartData, 'month'))?>,
                        datasets: [{
                            label: 'Collections (RWF)',
                            data: <?php echo json_encode(array_column($collectionsChartData, 'amount'))?>,
                            backgroundColor: 'rgba(34, 197, 94, 0.8)',
                            borderColor: '#16a34a',
                            borderWidth: 1,
                            borderRadius: 4,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Collections: ' + new Intl.NumberFormat().format(context.parsed.y) + ' RWF';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f3f4f6',
                                    borderDash: [2, 2]
                                },
                                ticks: {
                                    callback: function (value) {
                                        if (value >= 1000000) {
                                            return (value / 1000000) + 'M';
                                        } else if (value >= 1000) {
                                            return (value / 1000) + 'K';
                                        }
                                        return value;
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
                        }
                    }
                });

                // Fine Types Chart
                const fineTypesCtx = document.getElementById('fineTypesChart').getContext('2d');
                const fineTypesChart = new Chart(fineTypesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode($fineTypeLabels)?>,
                        datasets: [{
                            data: <?php echo json_encode($fineTypesData)?>,
                            backgroundColor: ['#ef4444', '#f59e0b', '#f97316', '#6b7280'],
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
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((context.parsed * 100) / total).toFixed(1) : 0;
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>


<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>