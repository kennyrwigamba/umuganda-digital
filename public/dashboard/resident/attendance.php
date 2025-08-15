<?php
    /**
     * Resident Dashboard
     * Main dashboard page for residents
     */

    session_start();

    // Check if user is logged in
    if (! isset($_SESSION['user_id'])) {
        header('Location: ../../login.php');
        exit;
    }

    // Check if user is resident (not admin)
    if ($_SESSION['user_role'] !== 'resident') {
        header('Location: ../admin/index.php');
        exit;
    }

    // Include required files
    require_once __DIR__ . '/../../../src/models/User.php';
    require_once __DIR__ . '/../../../src/models/Attendance.php';
    require_once __DIR__ . '/../../../src/models/UmugandaEvent.php';
    require_once __DIR__ . '/../../../src/models/Fine.php';
    require_once __DIR__ . '/../../../src/helpers/functions.php';

    // Get user data
    $userModel = new User();
    $user      = $userModel->findByIdWithLocation($_SESSION['user_id']);

    if (! $user) {
        // User not found, logout and redirect
        session_destroy();
        header('Location: ../../login.php?message=session_expired');
        exit;
    }

    // Extract user information for display
    $firstName  = htmlspecialchars($user['first_name']);
    $lastName   = htmlspecialchars($user['last_name']);
    $fullName   = $firstName . ' ' . $lastName;
    $email      = htmlspecialchars($user['email']);
    $phone      = htmlspecialchars($user['phone']);
    $nationalId = htmlspecialchars($user['national_id']);
    $location   = htmlspecialchars($user['cell_name'] . ', ' . $user['sector_name'] . ', ' . $user['district_name']);
    $initials   = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

    // Initialize models
    $attendanceModel = new Attendance();
    $eventModel      = new UmugandaEvent();
    $fineModel       = new Fine();

    // Get filter parameters
    $selectedYear   = $_GET['year'] ?? date('Y');
    $selectedStatus = $_GET['status'] ?? 'all';
    $page           = max(1, intval($_GET['page'] ?? 1));
    $limit          = 10; // Records per page

    // Get attendance statistics for selected year
    $attendanceStats = $attendanceModel->getAttendanceStats([
        'user_id'         => $_SESSION['user_id'],
        'event_date_from' => $selectedYear . '-01-01',
        'event_date_to'   => $selectedYear . '-12-31',
    ]);

    // Calculate statistics
    $totalSessions  = $attendanceStats['total_records'] ?? 0;
    $presentCount   = ($attendanceStats['present_count'] ?? 0) + ($attendanceStats['late_count'] ?? 0);
    $absentCount    = $attendanceStats['absent_count'] ?? 0;
    $attendanceRate = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100) : 0;

    // Get attendance records with pagination and filtering
    $filters = [
        'user_id'         => $_SESSION['user_id'],
        'limit'           => $limit,
        'offset'          => ($page - 1) * $limit,
        'event_date_from' => $selectedYear . '-01-01',
        'event_date_to'   => $selectedYear . '-12-31',
    ];

    if ($selectedStatus !== 'all') {
        $filters['status'] = $selectedStatus;
    }

    $attendanceRecords = $attendanceModel->getUserAttendanceHistory($_SESSION['user_id'], $filters);

    // Get total count for pagination
    $totalRecords = $attendanceModel->getUserAttendanceCount($_SESSION['user_id'], [
        'event_date_from' => $selectedYear . '-01-01',
        'event_date_to'   => $selectedYear . '-12-31',
        'status'          => $selectedStatus !== 'all' ? $selectedStatus : null,
    ]);

    $totalPages = ceil($totalRecords / $limit);

    // Get available years for filter
    $availableYears = [];
    $currentYear    = date('Y');
    for ($year = $currentYear; $year >= 2020; $year--) {
        $availableYears[] = $year;
    }

?>

<!-- Header -->
<?php include __DIR__ . '/partials/header.php'; ?>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 font-sans">
    <div class="flex flex-col md:flex-row h-screen">
        <!-- Sidebar -->
        <?php include 'partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col md:ml-0">
            <!-- Top Navbar -->
            <?php include 'partials/top-nav.php'; ?>

            <!-- Main Content -->
            <main
                class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 p-4 md:p-6">
                <div class="max-w-7xl mx-auto space-y-6">

                    <!-- Attendance Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Total Sessions Card -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v4a2 2 0 002 2h4a2 2 0 002-2V5z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900"><?php echo $totalSessions; ?></div>
                                    <div class="text-sm text-gray-600">Total Sessions</div>
                                </div>
                            </div>
                        </div>

                        <!-- Present Card -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-success-500 to-success-600 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900"><?php echo $presentCount; ?></div>
                                    <div class="text-sm text-gray-600">Present</div>
                                </div>
                            </div>
                        </div>

                        <!-- Absent Card -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-danger-500 to-danger-600 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900"><?php echo $absentCount; ?></div>
                                    <div class="text-sm text-gray-600">Absent</div>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Rate Card -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-warning-500 to-warning-600 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900"><?php echo $attendanceRate; ?>%</div>
                                    <div class="text-sm text-gray-600">Attendance Rate</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter and Search Section -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6">
                        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Community Work Sessions</h3>
                            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                                <!-- Year Filter -->
                                <select id="yearFilter" name="year" onchange="applyFilters()"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <?php foreach ($availableYears as $year): ?>
                                    <option value="<?php echo $year; ?>"<?php echo $year == $selectedYear ? 'selected' : ''; ?>>
                                        <?php echo $year; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <!-- Status Filter -->
                                <select id="statusFilter" name="status" onchange="applyFilters()"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="all"                                                                                                               <?php echo $selectedStatus == 'all' ? 'selected' : ''; ?>>All Status</option>
                                    <option value="present"                                                                                                                       <?php echo $selectedStatus == 'present' ? 'selected' : ''; ?>>Present</option>
                                    <option value="late"                                                                                                                 <?php echo $selectedStatus == 'late' ? 'selected' : ''; ?>>Late</option>
                                    <option value="absent"                                                                                                                     <?php echo $selectedStatus == 'absent' ? 'selected' : ''; ?>>Absent</option>
                                    <option value="excused"                                                                                                                       <?php echo $selectedStatus == 'excused' ? 'selected' : ''; ?>>Excused</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Records Table -->
                    <div
                        class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Detailed Attendance Records</h3>
                        </div>

                        <!-- Desktop Table View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Activity</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Location</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Hours</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fine</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (! empty($attendanceRecords)): ?>
<?php foreach ($attendanceRecords as $record):
        $eventDate   = new DateTime($record['event_date']);
        $statusClass = '';
        $statusText  = '';
        $statusIcon  = '';
        $hoursWorked = '';
        $fineAmount  = '';

        // Calculate hours worked
        if ($record['check_in_time'] && $record['check_out_time']) {
            $checkIn     = new DateTime($record['check_in_time']);
            $checkOut    = new DateTime($record['check_out_time']);
            $diff        = $checkIn->diff($checkOut);
            $hoursWorked = $diff->h . '.' . round($diff->i / 60 * 10) . ' hrs';
        } elseif ($record['start_time'] && $record['end_time']) {
        $start       = new DateTime($record['start_time']);
        $end         = new DateTime($record['end_time']);
        $diff        = $start->diff($end);
        $hoursWorked = $diff->h . '.' . round($diff->i / 60 * 10) . ' hrs';
    }

    // Get fine amount if applicable
    if ($record['status'] == 'absent') {
        $userFines = $fineModel->getUserFines($_SESSION['user_id'], [
            'event_id' => $record['event_id'],
            'limit'    => 1,
        ]);
        if (! empty($userFines)) {
            $fineAmount = number_format($userFines[0]['amount']) . ' RWF';
        }
    }

    // Set status styling
    switch ($record['status']) {
        case 'present':
            $statusClass = 'bg-success-100 text-success-800';
            $statusText  = 'Present';
            $statusIcon  = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>';
            break;
        case 'late':
            $statusClass = 'bg-warning-100 text-warning-800';
            $statusText  = 'Late';
            $statusIcon  = '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>';
            break;
        case 'absent':
            $statusClass = 'bg-danger-100 text-danger-800';
            $statusText  = 'Absent';
            $statusIcon  = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
            break;
        case 'excused':
            $statusClass = 'bg-blue-100 text-blue-800';
            $statusText  = 'Excused';
            $statusIcon  = '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>';
            break;
    }
?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo $eventDate->format('M j, Y'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($record['event_title']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($record['location']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium                                                                                                                                                                                                                                                                 <?php echo $statusClass; ?>">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <?php echo $statusIcon; ?>
                                                </svg>
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $hoursWorked ?: ($record['status'] == 'absent' ? '0.0 hrs' : '-'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if ($fineAmount): ?>
                                                <span class="text-danger-600 font-medium"><?php echo $fineAmount; ?></span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
<?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <div class="text-gray-500">
                                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                                <p class="text-lg font-medium">No attendance records found</p>
                                                <p class="text-sm">Try adjusting your filters or check back later</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="md:hidden space-y-4 p-4">
                            <?php if (! empty($attendanceRecords)): ?>
<?php foreach ($attendanceRecords as $record):
        $eventDate   = new DateTime($record['event_date']);
        $statusClass = '';
        $statusText  = '';
        $hoursWorked = '';
        $fineAmount  = '';

        // Calculate hours worked
        if ($record['check_in_time'] && $record['check_out_time']) {
            $checkIn     = new DateTime($record['check_in_time']);
            $checkOut    = new DateTime($record['check_out_time']);
            $diff        = $checkIn->diff($checkOut);
            $hoursWorked = $diff->h . '.' . round($diff->i / 60 * 10) . ' hrs';
        } elseif ($record['start_time'] && $record['end_time']) {
        $start       = new DateTime($record['start_time']);
        $end         = new DateTime($record['end_time']);
        $diff        = $start->diff($end);
        $hoursWorked = $diff->h . '.' . round($diff->i / 60 * 10) . ' hrs';
    }

    // Get fine amount if applicable
    if ($record['status'] == 'absent') {
        $userFines = $fineModel->getUserFines($_SESSION['user_id'], [
            'event_id' => $record['event_id'],
            'limit'    => 1,
        ]);
        if (! empty($userFines)) {
            $fineAmount = number_format($userFines[0]['amount']) . ' RWF';
        }
    }

    // Set status styling based on record status
    switch ($record['status']) {
        case 'present':
            $statusClass = 'bg-gradient-to-r from-success-50 to-emerald-50 border border-success-200';
            $statusText  = 'Present';
            break;
        case 'late':
            $statusClass = 'bg-gradient-to-r from-warning-50 to-yellow-50 border border-warning-200';
            $statusText  = 'Late';
            break;
        case 'absent':
            $statusClass = 'bg-gradient-to-r from-danger-50 to-red-50 border border-danger-200';
            $statusText  = 'Absent';
            break;
        case 'excused':
            $statusClass = 'bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200';
            $statusText  = 'Excused';
            break;
    }
?>
                            <div class="<?php echo $statusClass; ?> rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-medium text-gray-900"><?php echo $eventDate->format('M j, Y'); ?></h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?php echo $record['status'] == 'present' || $record['status'] == 'late' ? 'success' : ($record['status'] == 'absent' ? 'danger' : ($record['status'] == 'excused' ? 'blue' : 'warning')); ?>-100 text-<?php echo $record['status'] == 'present' || $record['status'] == 'late' ? 'success' : ($record['status'] == 'absent' ? 'danger' : ($record['status'] == 'excused' ? 'blue' : 'warning')); ?>-800">
                                        <?php echo $statusText; ?>
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <p><span class="font-medium">Activity:</span>                                                                                                                                                                   <?php echo htmlspecialchars($record['event_title']); ?></p>
                                    <p><span class="font-medium">Location:</span>                                                                                                                                                                   <?php echo htmlspecialchars($record['location']); ?></p>
                                    <?php if ($hoursWorked): ?>
                                    <p><span class="font-medium">Hours:</span><?php echo $hoursWorked; ?></p>
                                    <?php endif; ?>
<?php if ($fineAmount): ?>
                                    <p><span class="font-medium">Fine:</span> <span class="text-danger-600 font-medium"><?php echo $fineAmount; ?></span></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <!-- Show more button for pagination -->
                            <?php if ($totalPages > 1): ?>
                            <div class="text-center pt-4">
                                <?php if ($page < $totalPages): ?>
                                <a href="?year=<?php echo $selectedYear; ?>&status=<?php echo $selectedStatus; ?>&page=<?php echo $page + 1; ?>"
                                    class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                    Load More Records
                                </a>
                                <?php else: ?>
                                <span class="px-6 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                                    All Records Loaded
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
<?php else: ?>
                            <div class="text-center py-8">
                                <div class="text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No attendance records found</p>
                                    <p class="text-sm">Try adjusting your filters or check back later</p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pagination -->
                        <div
                            class="bg-gray-50 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <?php if ($page > 1): ?>
                                <a href="?year=<?php echo $selectedYear; ?>&status=<?php echo $selectedStatus; ?>&page=<?php echo $page - 1; ?>"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Previous
                                </a>
                                <?php else: ?>
                                <span
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                                    Previous
                                </span>
                                <?php endif; ?>

                                <?php if ($page < $totalPages): ?>
                                <a href="?year=<?php echo $selectedYear; ?>&status=<?php echo $selectedStatus; ?>&page=<?php echo $page + 1; ?>"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Next
                                </a>
                                <?php else: ?>
                                <span
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                                    Next
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing <span class="font-medium"><?php echo min(($page - 1) * $limit + 1, $totalRecords); ?></span>
                                        to <span class="font-medium"><?php echo min($page * $limit, $totalRecords); ?></span>
                                        of <span class="font-medium"><?php echo $totalRecords; ?></span> results
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <?php if ($page > 1): ?>
                                        <a href="?year=<?php echo $selectedYear; ?>&status=<?php echo $selectedStatus; ?>&page=<?php echo $page - 1; ?>"
                                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Previous</span>
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                        <?php else: ?>
                                        <span
                                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                            <span class="sr-only">Previous</span>
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                        <?php endif; ?>

                                        <?php
                                            // Show page numbers (simplified - show first, current-1, current, current+1, last)
                                            $startPage = max(1, $page - 2);
                                            $endPage   = min($totalPages, $page + 2);

                                        for ($i = $startPage; $i <= $endPage; $i++): ?>
<?php if ($i == $page): ?>
                                            <span
                                                class="relative inline-flex items-center px-4 py-2 border border-primary-500 bg-primary-50 text-sm font-medium text-primary-600">
                                                <?php echo $i; ?>
                                            </span>
                                            <?php else: ?>
                                            <a href="?year=<?php echo $selectedYear; ?>&status=<?php echo $selectedStatus; ?>&page=<?php echo $i; ?>"
                                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                <?php echo $i; ?>
                                            </a>
                                            <?php endif; ?>
<?php endfor; ?>

                                        <?php if ($page < $totalPages): ?>
                                        <a href="?year=<?php echo $selectedYear; ?>&status=<?php echo $selectedStatus; ?>&page=<?php echo $page + 1; ?>"
                                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Next</span>
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                        <?php else: ?>
                                        <span
                                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                            <span class="sr-only">Next</span>
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                        <?php endif; ?>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Filter functionality
        function applyFilters() {
            const yearFilter = document.getElementById('yearFilter');
            const statusFilter = document.getElementById('statusFilter');

            const year = yearFilter ? yearFilter.value : '<?php echo $selectedYear; ?>';
            const status = statusFilter ? statusFilter.value : '<?php echo $selectedStatus; ?>';

            // Build URL with filters
            const url = new URL(window.location.href);
            url.searchParams.set('year', year);
            url.searchParams.set('status', status);
            url.searchParams.set('page', '1'); // Reset to first page when filtering

            // Redirect to filtered URL
            window.location.href = url.toString();
        }

        // Initialize filter values on page load
        document.addEventListener('DOMContentLoaded', function() {
            const yearFilter = document.getElementById('yearFilter');
            const statusFilter = document.getElementById('statusFilter');

            if (yearFilter) {
                yearFilter.value = '<?php echo $selectedYear; ?>';
            }

            if (statusFilter) {
                statusFilter.value = '<?php echo $selectedStatus; ?>';
            }
        });
    </script>

<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>