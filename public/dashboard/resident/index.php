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
    require_once __DIR__ . '/../../../src/models/UmugandaEvent.php';
    require_once __DIR__ . '/../../../src/models/Attendance.php';
    require_once __DIR__ . '/../../../src/models/Fine.php';
    require_once __DIR__ . '/../../../src/models/Notice.php';
    require_once __DIR__ . '/../../../src/helpers/functions.php';

    // Get user data
    $userModel = new User();
    $user      = $userModel->findById($_SESSION['user_id']);

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
    $location   = htmlspecialchars($user['cell'] . ', ' . $user['sector'] . ', ' . $user['district']);
    $initials   = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

    // Initialize models
    $eventModel      = new UmugandaEvent();
    $attendanceModel = new Attendance();
    $fineModel       = new Fine();
    $noticeModel     = new Notice();

    // Get attendance statistics for current year
    $currentYear     = date('Y');
    $attendanceStats = $attendanceModel->getAttendanceStats([
        'user_id'         => $_SESSION['user_id'],
        'event_date_from' => $currentYear . '-01-01',
        'event_date_to'   => $currentYear . '-12-31',
    ]);

    // Calculate attendance rate
    $totalSessions    = $attendanceStats['total_records'] ?? 0;
    $presentSessions  = ($attendanceStats['present_count'] ?? 0) + ($attendanceStats['late_count'] ?? 0);
    $attendanceRate   = $totalSessions > 0 ? round(($presentSessions / $totalSessions) * 100) . '%' : '0%';
    $sessionsThisYear = $presentSessions . '/' . $totalSessions;

    // Get outstanding fines
    $outstandingAmount = $fineModel->getUserOutstandingAmount($_SESSION['user_id']);
    $outstandingFines  = number_format($outstandingAmount) . ' RWF';

    // Calculate community rank (simplified - based on attendance rate)
    // In a real implementation, this would compare with other users in the same location
    $attendancePercentage = $totalSessions > 0 ? ($presentSessions / $totalSessions) * 100 : 0;
    $communityRank        = $attendancePercentage >= 90 ? '#1-5' :
    ($attendancePercentage >= 80 ? '#6-15' :
        ($attendancePercentage >= 70 ? '#16-30' : '#30+'));

    // Get next upcoming event
    $nextEvent = $eventModel->getEventsByUserLocation($user['cell'], $user['sector'], $user['district'], 1);
    $nextEvent = ! empty($nextEvent) ? $nextEvent[0] : null;

    // Get recent attendance records
    $recentAttendance = $attendanceModel->getUserAttendanceHistory($_SESSION['user_id'], ['limit' => 3]);

    // Get outstanding fines details
    $outstandingFinesDetails = $fineModel->getUserOutstandingFines($_SESSION['user_id']);

    // Get fine statistics
    $fineStats = $fineModel->getUserFineStats($_SESSION['user_id']);

    // Get recent notices
    $recentNotices = $noticeModel->getRecentNoticesForUser(
        $_SESSION['user_id'],
        $user['cell'],
        $user['sector'],
        $user['district'],
        $user['role'],
        3
    );

?>

<!-- Header -->
<?php include __DIR__ . '/partials/header.php'; ?>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 font-sans">
    <div class="flex flex-col md:flex-row h-screen">

        <!-- Sidebar -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden md:ml-0">

            <!-- Top Navbar -->
            <?php include __DIR__ . '/partials/top-nav.php'; ?>

            <!-- Main Dashboard Content -->
            <main
                class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 p-4 md:p-6">
                <div class="max-w-7xl mx-auto space-y-6">

                    <!-- Quick Stats Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div
                            class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
                            <div class="flex items-center">
                                <div class="p-3 bg-success-100 rounded-lg">
                                    <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Attendance Rate</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $attendanceRate; ?></p>
                                    <p class="text-xs text-success-600 mt-1">+5% from last month</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
                            <div class="flex items-center">
                                <div class="p-3 bg-primary-100 rounded-lg">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Sessions This Year</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $sessionsThisYear; ?></p>
                                    <p class="text-xs text-gray-500 mt-1">Out of 30 total</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
                            <div class="flex items-center">
                                <div class="p-3 bg-warning-100 rounded-lg">
                                    <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                        </path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Outstanding Fines</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $outstandingFines; ?></p>
                                    <p class="text-xs text-danger-600 mt-1">1 payment due</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
                            <div class="flex items-center">
                                <div class="p-3 bg-purple-100 rounded-lg">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Community Rank</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $communityRank; ?></p>
                                    <p class="text-xs text-success-600 mt-1">Top 15% in sector</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Umuganda Section -->
                    <section
                        class="bg-gradient-to-r from-success-50 to-emerald-50 border-l-4 border-success-500 rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                                <div class="p-2 bg-success-100 rounded-lg mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6 text-success-600">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                </div>
                                Next Umuganda Session
                            </h3>
                            <div class="flex space-x-2">
                                <?php if ($nextEvent):
                                        $eventDate = new DateTime($nextEvent['event_date']);
                                        $today     = new DateTime();
                                        $daysLeft  = $today->diff($eventDate)->days;
                                        if ($eventDate < $today) {
                                            $daysLeft = 0;
                                        }

                                    ?>
	                                <span
	                                    class="bg-success-600 text-white text-sm font-medium px-4 py-2 rounded-full shadow-sm flex items-center">
	                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
	                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
	                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
	                                    </svg>
	                                    <?php echo $daysLeft; ?> day<?php echo $daysLeft != 1 ? 's' : ''; ?> left
	                                </span>
	                                <?php else: ?>
                                <span
                                    class="bg-gray-500 text-white text-sm font-medium px-4 py-2 rounded-full shadow-sm">
                                    No upcoming events
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($nextEvent):
                                $eventDate     = new DateTime($nextEvent['event_date']);
                                $startTime     = new DateTime($nextEvent['start_time']);
                                $endTime       = new DateTime($nextEvent['end_time']);
                                $duration      = $startTime->diff($endTime);
                                $durationHours = $duration->h + ($duration->i / 60);
                            ?>
	                        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
	                            <div class="bg-white/60 backdrop-blur-sm rounded-lg p-4 border border-success-200">
	                                <div class="flex items-center mb-2">
	                                    <svg class="w-5 h-5 text-success-600 mr-2" fill="none" stroke="currentColor"
	                                        viewBox="0 0 24 24">
	                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
	                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2 2v12a2 2 0 002 2z">
	                                        </path>
	                                    </svg>
	                                    <p class="text-sm text-gray-600 font-medium">Date</p>
	                                </div>
	                                <p class="text-lg font-bold text-gray-800"><?php echo $eventDate->format('l, M j'); ?></p>
	                                <p class="text-sm text-gray-600"><?php echo $eventDate->format('Y'); ?></p>
	                            </div>
	                            <div class="bg-white/60 backdrop-blur-sm rounded-lg p-4 border border-success-200">
	                                <div class="flex items-center mb-2">
	                                    <svg class="w-5 h-5 text-success-600 mr-2" fill="none" stroke="currentColor"
	                                        viewBox="0 0 24 24">
	                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
	                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
	                                    </svg>
	                                    <p class="text-sm text-gray-600 font-medium">Time</p>
	                                </div>
	                                <p class="text-lg font-bold text-gray-800"><?php echo $startTime->format('g:i A') . ' - ' . $endTime->format('g:i A'); ?></p>
	                                <p class="text-sm text-gray-600"><?php echo number_format($durationHours, 1); ?> hours</p>
	                            </div>
	                            <div class="bg-white/60 backdrop-blur-sm rounded-lg p-4 border border-success-200">
	                                <div class="flex items-center mb-2">
	                                    <svg class="w-5 h-5 text-success-600 mr-2" fill="none" stroke="currentColor"
	                                        viewBox="0 0 24 24">
	                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
	                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
	                                        </path>
	                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
	                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
	                                    </svg>
	                                    <p class="text-sm text-gray-600 font-medium">Location</p>
	                                </div>
	                                <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($nextEvent['location']); ?></p>
	                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($nextEvent['cell'] ?? $nextEvent['sector']); ?></p>
	                            </div>
	                            <div class="bg-white/60 backdrop-blur-sm rounded-lg p-4 border border-success-200">
	                                <div class="flex items-center mb-2">
	                                    <svg class="w-5 h-5 text-success-600 mr-2" fill="none" stroke="currentColor"
	                                        viewBox="0 0 24 24">
	                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
	                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
	                                        </path>
	                                    </svg>
	                                    <p class="text-sm text-gray-600 font-medium">Activity</p>
	                                </div>
	                                <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($nextEvent['title']); ?></p>
	                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars(substr($nextEvent['description'], 0, 20)) . '...'; ?></p>
	                            </div>
	                        </div>
	                        <?php else: ?>
                        <div class="text-center py-8">
                            <div class="p-4 bg-gray-100 rounded-full w-16 h-16 mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-500 mx-auto mt-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <p class="text-gray-600 font-medium">No upcoming Umuganda events scheduled</p>
                            <p class="text-sm text-gray-500 mt-1">Check back later for new events</p>
                        </div>
                        <?php endif; ?>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <button
                                class="bg-success-600 hover:bg-success-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 shadow-lg hover:shadow-xl flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add to Calendar
                            </button>
                            <button
                                class="bg-white/80 hover:bg-white text-success-700 border border-success-300 px-6 py-3 rounded-lg font-medium transition-all duration-200 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                View Details
                            </button>
                            <button
                                class="bg-white/80 hover:bg-white text-gray-700 border border-gray-300 px-6 py-3 rounded-lg font-medium transition-all duration-200 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
                                    </path>
                                </svg>
                                Share
                            </button>
                        </div>
                    </section>

                    <!-- Two Column Layout for Medium and Large Screens -->
                    <div class="lg:grid lg:grid-cols-2 lg:gap-6 space-y-6 lg:space-y-0">

                        <!-- Attendance Summary -->
                        <section
                            class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                    <div class="p-2 bg-primary-100 rounded-lg mr-3">
                                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </div>
                                    Recent Attendance
                                </h3>
                                <a href="dashboard-user-attendance.html"
                                    class="text-primary-600 hover:text-primary-700 text-sm font-medium flex items-center">
                                    View All
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                            <div class="space-y-4">
                                <?php if (! empty($recentAttendance)): ?>
<?php foreach ($recentAttendance as $attendance):
        $eventDate   = new DateTime($attendance['event_date']);
        $statusClass = '';
        $statusText  = '';
        $iconClass   = '';
        $iconSvg     = '';
        $statusNote  = '';

        switch ($attendance['status']) {
            case 'present':
                $statusClass = 'bg-gradient-to-r from-success-50 to-emerald-50 border-success-200 hover:border-success-300';
                $statusText  = 'bg-success-600 text-white';
                $iconClass   = 'bg-success-100 text-success-600';
                $iconSvg     = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>';
                $statusNote  = $attendance['check_in_time'] ? (new DateTime($attendance['check_in_time']))->format('g:i A') : 'On time';
                break;
            case 'late':
                $statusClass = 'bg-gradient-to-r from-warning-50 to-yellow-50 border-warning-200 hover:border-warning-300';
                $statusText  = 'bg-warning-600 text-white';
                $iconClass   = 'bg-warning-100 text-warning-600';
                $iconSvg     = '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>';
                $statusNote  = 'Arrived late';
                break;
            case 'absent':
                $statusClass = 'bg-gradient-to-r from-danger-50 to-red-50 border-danger-200 hover:border-danger-300';
                $statusText  = 'bg-danger-600 text-white';
                $iconClass   = 'bg-danger-100 text-danger-600';
                $iconSvg     = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
                $statusNote  = 'Fine applied';
                break;
            case 'excused':
                $statusClass = 'bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200 hover:border-blue-300';
                $statusText  = 'bg-blue-600 text-white';
                $iconClass   = 'bg-blue-100 text-blue-600';
                $iconSvg     = '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>';
                $statusNote  = 'Excused';
                break;
        }

        $duration = '';
        if ($attendance['start_time'] && $attendance['end_time']) {
            $start    = new DateTime($attendance['start_time']);
            $end      = new DateTime($attendance['end_time']);
            $diff     = $start->diff($end);
            $duration = $diff->h . ' hours';
            if ($diff->i > 0) {
                $duration = $diff->h . '.' . round($diff->i / 60 * 10) . ' hours';
            }
        }
    ?>
	                                <div class="flex items-center justify-between p-4<?php echo $statusClass; ?> rounded-lg border transition-colors">
	                                    <div class="flex items-center">
	                                        <div class="w-10 h-10	                                                              <?php echo $iconClass; ?> rounded-full flex items-center justify-center mr-4">
	                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
	                                                <?php echo $iconSvg; ?>
	                                            </svg>
	                                        </div>
	                                        <div>
	                                            <p class="font-semibold text-gray-800"><?php echo $eventDate->format('M j, Y'); ?></p>
	                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($attendance['event_title'] ?? 'Unknown Event') . ($duration ? ' • ' . $duration : ''); ?></p>
	                                        </div>
	                                    </div>
	                                    <div class="text-right">
	                                        <span class="<?php echo $statusText; ?> text-xs font-medium px-3 py-1 rounded-full"><?php echo ucfirst($attendance['status']); ?></span>
	                                        <p class="text-xs text-gray-500 mt-1"><?php echo $statusNote; ?></p>
	                                    </div>
	                                </div>
	                                <?php endforeach; ?>
<?php else: ?>
                                <div class="text-center py-8">
                                    <div class="p-4 bg-gray-100 rounded-full w-16 h-16 mx-auto mb-4">
                                        <svg class="w-8 h-8 text-gray-500 mx-auto mt-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-600 font-medium">No attendance records found</p>
                                    <p class="text-sm text-gray-500 mt-1">Attend Umuganda events to see your history here</p>
                                </div>
                                <?php endif; ?>

                            <!-- Enhanced Attendance Stats -->
                            <div class="mt-6 grid grid-cols-2 gap-4">
                                <div
                                    class="p-4 bg-gradient-to-r from-primary-50 to-blue-50 border border-primary-200 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-600 font-medium">Attendance Rate</p>
                                            <p class="text-2xl font-bold text-primary-600"><?php echo $attendanceRate; ?></p>
                                        </div>
                                        <div
                                            class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="text-xs text-success-600 mt-2 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                        </svg>
                                        +5% this month
                                    </p>
                                </div>
                                <div
                                    class="p-4 bg-gradient-to-r from-success-50 to-emerald-50 border border-success-200 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-600 font-medium">This Year</p>
                                            <p class="text-2xl font-bold text-success-600"><?php echo $sessionsThisYear; ?></p>
                                        </div>
                                        <div
                                            class="w-12 h-12 bg-success-100 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-2">4 sessions remaining</p>
                                </div>
                            </div>
                        </section>

                        <!-- Fine Overview & Recent Notifications -->
                        <div class="space-y-6">
                            <!-- Fine Overview -->
                            <section
                                class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                        <div class="p-2 bg-warning-100 rounded-lg mr-3">
                                            <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                                </path>
                                            </svg>
                                        </div>
                                        Fine Status
                                    </h3>
                                    <a href="dashboard-user-fines.html"
                                        class="text-primary-600 hover:text-primary-700 text-sm font-medium flex items-center">
                                        Manage Payments
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>

                                <!-- Outstanding Fine Alert -->
                                <?php if (! empty($outstandingFinesDetails)): ?>
<?php $firstOutstandingFine = $outstandingFinesDetails[0];
    $dueDate                                                        = $firstOutstandingFine['due_date'] ? new DateTime($firstOutstandingFine['due_date']) : null;
?>
                                <div
                                    class="bg-gradient-to-r from-danger-50 to-red-50 border-l-4 border-danger-500 rounded-lg p-4 mb-6">
                                    <div class="flex items-start justify-between">
                                        <div class="flex">
                                            <div class="p-2 bg-danger-100 rounded-lg mr-3">
                                                <svg class="w-5 h-5 text-danger-600" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-danger-800">Outstanding Fine</p>
                                                <p class="text-sm text-danger-700 mt-1">
                                                    <?php echo htmlspecialchars($firstOutstandingFine['event_title']); ?> •
                                                    <?php echo(new DateTime($firstOutstandingFine['event_date']))->format('M j, Y'); ?>
                                                </p>
                                                <p class="text-2xl font-bold text-danger-800 mt-2"><?php echo number_format($firstOutstandingFine['amount']); ?> RWF</p>
                                                <p class="text-xs text-danger-600 mt-1">
                                                    <?php if ($dueDate): ?>
                                                        Due:<?php echo $dueDate->format('M j, Y'); ?>
<?php else: ?>
                                                        Payment due
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col space-y-2">
                                            <button
                                                class="bg-danger-600 hover:bg-danger-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                                    </path>
                                                </svg>
                                                Pay Now
                                            </button>
                                            <button
                                                class="bg-white text-danger-600 border border-danger-300 text-sm font-medium px-4 py-2 rounded-lg hover:bg-danger-50 transition-all duration-200">
                                                View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div
                                    class="bg-gradient-to-r from-success-50 to-emerald-50 border-l-4 border-success-500 rounded-lg p-4 mb-6">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-success-100 rounded-lg mr-3">
                                            <svg class="w-5 h-5 text-success-600" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-success-800">No Outstanding Fines</p>
                                            <p class="text-sm text-success-700 mt-1">You have no pending fine payments</p>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Payment History Summary -->
                                <?php
                                    $paidFines    = $fineModel->getUserFines($_SESSION['user_id'], ['status' => 'paid', 'limit' => 1]);
                                    $lastPaidFine = ! empty($paidFines) ? $paidFines[0] : null;
                                ?>
<?php if ($lastPaidFine): ?>
                                <div
                                    class="bg-gradient-to-r from-success-50 to-emerald-50 border border-success-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="p-2 bg-success-100 rounded-lg mr-3">
                                                <svg class="w-5 h-5 text-success-600" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-success-800">Recent Payment</p>
                                                <p class="text-sm text-success-700"><?php echo number_format($lastPaidFine['amount']); ?> RWF •<?php echo(new DateTime($lastPaidFine['paid_date']))->format('M j, Y'); ?></p>
                                                <p class="text-xs text-success-600 mt-1">Payment method:                                                                                                         <?php echo htmlspecialchars($lastPaidFine['payment_method'] ?? 'N/A'); ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span
                                                class="bg-success-600 text-white text-xs font-medium px-2 py-1 rounded-full">Paid</span>
                                            <p class="text-xs text-success-600 mt-1">Receipt: #<?php echo htmlspecialchars($lastPaidFine['payment_reference'] ?? 'N/A'); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div
                                    class="bg-gradient-to-r from-gray-50 to-slate-50 border border-gray-200 rounded-lg p-4">
                                    <div class="text-center py-4">
                                        <p class="text-gray-600 font-medium">No payment history</p>
                                        <p class="text-sm text-gray-500 mt-1">No fine payments have been made yet</p>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Quick Payment Stats -->
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($fineStats['total_paid'] ?? 0); ?></p>
                                        <p class="text-xs text-gray-600">Total Paid (RWF)</p>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                                        <p class="text-2xl font-bold text-warning-600"><?php echo $fineStats['pending_fines'] ?? 0; ?></p>
                                        <p class="text-xs text-gray-600">Pending Payment<?php echo($fineStats['pending_fines'] ?? 0) != 1 ? 's' : ''; ?></p>
                                    </div>
                                </div>
                            </section>

                            <!-- Recent Notifications -->
                            <section
                                class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z">
                                                </path>
                                            </svg>
                                        </div>
                                        Community Notices
                                    </h3>
                                    <a href="dashboard-user-notices.html"
                                        class="text-primary-600 hover:text-primary-700 text-sm font-medium flex items-center">
                                        View All
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>

                                <div class="space-y-4">
                                    <?php if (! empty($recentNotices)): ?>
<?php foreach ($recentNotices as $notice):
        $priorityClass = '';
        $priorityText  = '';
        $iconClass     = '';
        $iconSvg       = '';
        $borderClass   = '';

        switch ($notice['priority']) {
            case 'critical':
            case 'high':
                $priorityClass = 'bg-gradient-to-r from-danger-50 to-red-50 border-l-4 border-danger-500';
                $priorityText  = 'bg-danger-600 text-white';
                $iconClass     = 'bg-danger-100 text-danger-600';
                $iconSvg       = '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>';
                break;
            case 'medium':
                if ($notice['type'] == 'urgent') {
                    $priorityClass = 'bg-gradient-to-r from-warning-50 to-yellow-50 border-l-4 border-warning-500';
                    $priorityText  = 'bg-warning-600 text-white';
                    $iconClass     = 'bg-warning-100 text-warning-600';
                    $iconSvg       = '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>';
                } else {
                    $priorityClass = 'bg-gradient-to-r from-primary-50 to-blue-50 border border-primary-200';
                    $priorityText  = 'bg-primary-600 text-white';
                    $iconClass     = 'bg-primary-100 text-primary-600';
                    $iconSvg       = '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>';
                }
                break;
            case 'low':
            default:
                $priorityClass = 'bg-gradient-to-r from-gray-50 to-slate-50 border border-gray-200';
                $priorityText  = 'bg-gray-600 text-white';
                $iconClass     = 'bg-gray-100 text-gray-600';
                $iconSvg       = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                break;
        }

        $publishDate = new DateTime($notice['publish_date']);
        $now         = new DateTime();
        $timeAgo     = $now->diff($publishDate);

        if ($timeAgo->days > 0) {
            $timeText = $timeAgo->days . ' day' . ($timeAgo->days > 1 ? 's' : '') . ' ago';
        } elseif ($timeAgo->h > 0) {
        $timeText = $timeAgo->h . ' hour' . ($timeAgo->h > 1 ? 's' : '') . ' ago';
    } else {
        $timeText = $timeAgo->i . ' minute' . ($timeAgo->i > 1 ? 's' : '') . ' ago';
    }
?>
                                    <div class="<?php echo $priorityClass; ?> rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-start justify-between">
                                            <div class="flex">
                                                <div class="p-1                                                                <?php echo $iconClass; ?> rounded-lg mr-3 mt-1">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <?php echo $iconSvg; ?>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="flex items-center space-x-2 mb-1">
                                                        <span class="<?php echo $priorityText; ?> text-xs font-medium px-2 py-1 rounded-full"><?php echo ucfirst($notice['priority']); ?></span>
                                                        <p class="text-xs text-gray-600"><?php echo $timeText; ?></p>
                                                        <?php if (! $notice['is_read']): ?>
                                                        <span class="bg-blue-500 text-white text-xs font-medium px-2 py-1 rounded-full">New</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($notice['title']); ?></p>
                                                    <p class="text-sm text-gray-700 mt-1"><?php echo htmlspecialchars(substr($notice['content'], 0, 120)) . (strlen($notice['content']) > 120 ? '...' : ''); ?></p>
                                                </div>
                                            </div>
                                            <div class="flex flex-col space-y-1">
                                                <?php if (! $notice['is_read']): ?>
                                                <button class="text-primary-600 hover:text-primary-800 text-xs font-medium">
                                                    Mark Read
                                                </button>
                                                <?php endif; ?>
                                                <button class="text-gray-600 hover:text-gray-800 text-xs">
                                                    View Full
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
<?php else: ?>
                                    <div class="text-center py-8">
                                        <div class="p-4 bg-gray-100 rounded-full w-16 h-16 mx-auto mb-4">
                                            <svg class="w-8 h-8 text-gray-500 mx-auto mt-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                                            </svg>
                                        </div>
                                        <p class="text-gray-600 font-medium">No recent notices</p>
                                        <p class="text-sm text-gray-500 mt-1">Check back later for community updates</p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Quick Actions -->
                                <div class="mt-6 flex flex-wrap gap-2">
                                    <button
                                        class="bg-primary-600 hover:bg-primary-700 text-white text-sm px-4 py-2 rounded-lg transition-colors flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Mark All Read
                                    </button>
                                    <button
                                        class="bg-white text-gray-700 border border-gray-300 text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                                        Notification Settings
                                    </button>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

<?php include __DIR__ . '/partials/footer.php'; ?>