<?php
    /**
     * Umuganda Events Management - Admin Dashboard
     * Dynamic events management for assigned sector
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
            // Default sector for testing - use first available sector
            $defaultSectorQuery = "SELECT id, name FROM sectors LIMIT 1";
            $defaultResult      = $connection->query($defaultSectorQuery);
            if ($defaultResult && $defaultResult->num_rows > 0) {
                $defaultSector = $defaultResult->fetch_assoc();
                $adminSector   = $defaultSector['name'];
                $adminSectorId = $defaultSector['id'];
            } else {
                // Final fallback
                $adminSector   = 'Kimironko';
                $adminSectorId = 1; // Assuming Kimironko has ID 1
            }
        }
    }

    // Get events statistics for admin's sector
    try {
        // Get total events this year for admin's sector
        $totalEventsQuery = "
        SELECT COUNT(*) as count
        FROM umuganda_events
        WHERE (sector_id = ? OR sector_id IS NULL)
        AND YEAR(event_date) = YEAR(CURRENT_DATE())";
        $stmt = $connection->prepare($totalEventsQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $totalEvents = $stmt->get_result()->fetch_assoc()['count'];

        // Get upcoming events (next 30 days)
        $upcomingEventsQuery = "
        SELECT COUNT(*) as count
        FROM umuganda_events
        WHERE (sector_id = ? OR sector_id IS NULL)
        AND event_date BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY)
        AND status = 'scheduled'";
        $stmt = $connection->prepare($upcomingEventsQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $upcomingEvents = $stmt->get_result()->fetch_assoc()['count'];

        // Get average attendance
        $attendanceQuery = "
        SELECT
            AVG(
                (SELECT COUNT(*) FROM attendance a WHERE a.event_id = ue.id AND a.status IN ('present', 'late'))
            ) as avg_attendance,
            AVG(
                (SELECT COUNT(*) FROM attendance a WHERE a.event_id = ue.id)
            ) as avg_total
        FROM umuganda_events ue
        WHERE (ue.sector_id = ? OR ue.sector_id IS NULL)
        AND ue.status = 'completed'
        AND ue.event_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)";
        $stmt = $connection->prepare($attendanceQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $attendanceResult = $stmt->get_result();
        $attendanceData   = $attendanceResult->fetch_assoc();

        $avgAttendance      = $attendanceData['avg_attendance'] ?? 0;
        $avgTotal           = $attendanceData['avg_total'] ?? 0;
        $attendanceRate     = $avgTotal > 0 ? round(($avgAttendance / $avgTotal) * 100, 1) : 0;
        $avgAttendanceCount = round($avgAttendance);

        // Get next event
        $nextEventQuery = "
        SELECT event_date, start_time, title
        FROM umuganda_events
        WHERE (sector_id = ? OR sector_id IS NULL)
        AND event_date >= CURRENT_DATE()
        AND status = 'scheduled'
        ORDER BY event_date ASC, start_time ASC
        LIMIT 1";
        $stmt = $connection->prepare($nextEventQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $nextEventResult = $stmt->get_result();
        $nextEventData   = $nextEventResult ? $nextEventResult->fetch_assoc() : null;

        if ($nextEventData) {
            $nextEventDate  = date('M j', strtotime($nextEventData['event_date']));
            $nextEventTime  = date('g:i A', strtotime($nextEventData['start_time']));
            $nextEventTitle = $nextEventData['title'];

            // Calculate days until next event
            $daysUntil = ceil((strtotime($nextEventData['event_date']) - time()) / (60 * 60 * 24));
            if ($daysUntil == 0) {
                $nextEventStatus = 'Today';
            } elseif ($daysUntil == 1) {
                $nextEventStatus = 'Tomorrow';
            } else {
                $nextEventStatus = "In $daysUntil days";
            }
        } else {
            $nextEventDate   = 'TBD';
            $nextEventTime   = '';
            $nextEventTitle  = 'No events scheduled';
            $nextEventStatus = 'None';
        }

    } catch (Exception $e) {
        // Default values if queries fail
        $totalEvents        = 0;
        $upcomingEvents     = 0;
        $attendanceRate     = 0;
        $avgAttendanceCount = 0;
        $nextEventDate      = 'TBD';
        $nextEventTime      = '';
        $nextEventTitle     = 'No events scheduled';
        $nextEventStatus    = 'None';
    }

    // Handle search and filters
    $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
    $searchTerm   = isset($_GET['search']) ? $_GET['search'] : '';

    // Build WHERE clause for filters with sector filtering
    $whereConditions = ["(e.sector_id = ? OR e.sector_id IS NULL)"];
    $params          = [$adminSectorId];
    $paramTypes      = 'i';

    if (! empty($statusFilter)) {
        switch ($statusFilter) {
            case 'upcoming':
                $whereConditions[] = "e.event_date >= CURRENT_DATE() AND e.status = 'scheduled'";
                break;
            case 'active':
                $whereConditions[] = "e.status = 'ongoing'";
                break;
            case 'completed':
                $whereConditions[] = "e.status = 'completed'";
                break;
            case 'cancelled':
                $whereConditions[] = "e.status = 'cancelled'";
                break;
        }
    }

    if (! empty($searchTerm)) {
        $whereConditions[] = "(e.title LIKE ? OR e.description LIKE ?)";
        $searchParam       = "%$searchTerm%";
        $params[]          = $searchParam;
        $params[]          = $searchParam;
        $paramTypes .= 'ss';
    }

    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

    // Get events with attendance counts
    $eventsQuery = "
    SELECT
        e.*,
        COALESCE(attendance_stats.total_registered, 0) as total_registered,
        COALESCE(attendance_stats.total_attended, 0) as total_attended
    FROM umuganda_events e
    LEFT JOIN (
        SELECT
            event_id,
            COUNT(*) as total_registered,
            SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) as total_attended
        FROM attendance
        GROUP BY event_id
    ) attendance_stats ON e.id = attendance_stats.event_id
    $whereClause
    ORDER BY e.event_date DESC, e.start_time DESC
    LIMIT 20";

    $stmt = $connection->prepare($eventsQuery);
    if (! empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $eventsResult = $stmt->get_result();
    $events       = [];
    while ($event = $eventsResult->fetch_assoc()) {
        $events[] = $event;
    }

    // Get total residents count for percentage calculation
    $totalResidentsQuery  = "SELECT COUNT(*) as count FROM users WHERE role = 'resident' AND status = 'active'";
    $totalResidentsResult = $connection->query($totalResidentsQuery);
    $totalResidents       = $totalResidentsResult->fetch_assoc()['count'];

    // Helper functions
    function getEventStatus($event)
    {
        $eventDate = strtotime($event['event_date']);
        $today     = strtotime('today');

        if ($event['status'] === 'cancelled') {
            return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>';
        } elseif ($event['status'] === 'completed') {
            return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Completed</span>';
        } elseif ($event['status'] === 'ongoing') {
            return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>';
        } elseif ($eventDate >= $today) {
            return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Upcoming</span>';
        } else {
            return '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Past</span>';
        }
    }

    function getEventIcon($event)
    {
        $type = strtolower($event['title']);
        if (strpos($type, 'umuganda') !== false) {
            return 'fas fa-calendar-check';
        } elseif (strpos($type, 'training') !== false) {
            return 'fas fa-chalkboard-teacher';
        } elseif (strpos($type, 'meeting') !== false) {
            return 'fas fa-users';
        } else {
            return 'fas fa-calendar-alt';
        }
    }

    function getEventCardColor($event)
    {
        $eventDate = strtotime($event['event_date']);
        $today     = strtotime('today');

        if ($event['status'] === 'cancelled') {
            return 'from-red-500 to-red-600';
        } elseif ($event['status'] === 'completed') {
            return 'from-blue-500 to-blue-600';
        } elseif ($event['status'] === 'ongoing') {
            return 'from-green-500 to-green-600';
        } elseif ($eventDate >= $today) {
            return 'from-orange-500 to-orange-600';
        } else {
            return 'from-gray-500 to-gray-600';
        }
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

        <!-- Events Management Content -->
        <main class="p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 ml-4 lg:ml-0">Umuganda Events</h1>
                            <p class="text-gray-600 mt-1 ml-4 lg:ml-0">Schedule and manage community events</p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                            <button
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Export Events
                            </button>
                            <button id="createEventBtn"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg text-sm font-medium hover:from-primary-700 hover:to-primary-800 shadow-sm transition-all">
                                <i class="fas fa-plus mr-2"></i>
                                Create Event
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Events Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Events -->
                    <div
                        class="bg-gradient-to-br from-white to-blue-50 rounded-xl shadow-sm p-6 border border-blue-100 hover:shadow-lg hover:border-blue-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Total Events</p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $totalEvents ?></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-primary-600 font-semibold bg-primary-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-calendar text-xs mr-1"></i>
                                        This Year
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium"><?php echo date('Y') ?></span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-calendar-alt text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div
                        class="bg-gradient-to-br from-white to-green-50 rounded-xl shadow-sm p-6 border border-green-100 hover:shadow-lg hover:border-green-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-green-600 uppercase tracking-wide">Upcoming Events
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $upcomingEvents ?></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-success-600 font-semibold bg-success-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-clock text-xs mr-1"></i>
                                        Next 30 days
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">scheduled</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-success-500 to-success-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-arrow-up text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Average Attendance -->
                    <div
                        class="bg-gradient-to-br from-white to-purple-50 rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-lg hover:border-purple-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-purple-600 uppercase tracking-wide">Avg. Attendance
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $attendanceRate ?>%</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-purple-600 font-semibold bg-purple-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-users text-xs mr-1"></i>
                                        <?php echo number_format($avgAttendanceCount) ?>
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">avg. present</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-user-check text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Next Event -->
                    <div
                        class="bg-gradient-to-br from-white to-orange-50 rounded-xl shadow-sm p-6 border border-orange-100 hover:shadow-lg hover:border-orange-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Next Event</p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $nextEventDate ?></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-warning-600 font-semibold bg-warning-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-clock text-xs mr-1"></i>
                                        <?php echo $nextEventStatus ?>
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium"><?php echo $nextEventTime ?></span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-calendar-day text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- View Toggle and Filters -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-8">
                    <form method="GET" action="" class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <!-- View Toggle -->
                            <div class="flex bg-gray-100 rounded-lg p-1">
                                <button type="button" id="listView"
                                    class="px-4 py-2 text-sm font-medium bg-white text-gray-900 rounded-md shadow-sm transition-all">
                                    <i class="fas fa-list mr-2"></i>
                                    List
                                </button>
                            </div>

                            <!-- Search -->
                            <div class="relative">
                                <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm) ?>"
                                    placeholder="Search events..."
                                    class="px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- Status Filter -->
                            <div class="relative">
                                <select name="status"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="">All Events</option>
                                    <option value="upcoming"                                                                                                                         <?php echo $statusFilter === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                                    <option value="active"                                                                                                                     <?php echo $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="completed"                                                                                                                           <?php echo $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled"                                                                                                                           <?php echo $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>

                            <button type="submit"
                                class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                                Apply Filters
                            </button>

                            <a href="?"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Events List -->
                <div id="eventsList" class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Events</h3>
                            <button id="createEventBtn"
                                class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Create Event
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Event Details
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Time
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Location
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Attendance
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($events)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-calendar-times text-4xl mb-4 text-gray-300"></i>
                                        <p class="text-lg font-medium mb-2">No events found</p>
                                        <p class="text-sm">Create your first event to get started.</p>
                                    </td>
                                </tr>
                                <?php else: ?>
<?php foreach ($events as $event): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($event['title']) ?>
                                            </div>
                                            <?php if (! empty($event['description'])): ?>
                                            <div class="text-sm text-gray-500 mt-1">
                                                <?php echo htmlspecialchars(substr($event['description'], 0, 60)) ?>
<?php echo strlen($event['description']) > 60 ? '...' : '' ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <?php echo date('M j, Y', strtotime($event['event_date'])) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('g:i A', strtotime($event['start_time'])) ?>
<?php if ($event['end_time']): ?>
                                                -<?php echo date('g:i A', strtotime($event['end_time'])) ?>
<?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($event['location']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                            $now        = date('Y-m-d H:i:s');
                                            $eventStart = $event['event_date'] . ' ' . $event['start_time'];
                                            $eventEnd   = $event['event_date'] . ' ' . ($event['end_time'] ?: '23:59:59');

                                            if ($event['status'] === 'cancelled') {
                                                $statusClass = 'bg-red-100 text-red-800';
                                                $statusText  = 'Cancelled';
                                            } elseif ($event['status'] === 'completed') {
                                                $statusClass = 'bg-gray-100 text-gray-800';
                                                $statusText  = 'Completed';
                                            } elseif ($now > $eventEnd) {
                                                $statusClass = 'bg-gray-100 text-gray-800';
                                                $statusText  = 'Ended';
                                            } elseif ($now >= $eventStart && $now <= $eventEnd) {
                                                $statusClass = 'bg-green-100 text-green-800';
                                                $statusText  = 'Ongoing';
                                            } else {
                                                $statusClass = 'bg-blue-100 text-blue-800';
                                                $statusText  = 'Upcoming';
                                            }
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium<?php echo $statusClass ?>">
                                            <?php echo $statusText ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                            $attendanceCount = $event['total_attended'] ?? 0;
                                            $registeredCount = $event['total_registered'] ?? 0;
                                            $percentage      = $totalResidents > 0 ? round(($attendanceCount / $totalResidents) * 100) : 0;
                                        ?>
                                        <div class="text-sm text-gray-900">
                                            <?php echo $attendanceCount ?> /<?php echo $totalResidents ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            (<?php echo $percentage ?>%)
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <button class="text-blue-600 hover:text-blue-900 text-sm"
                                                onclick="viewEvent(<?php echo $event['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($event['status'] !== 'completed' && $event['status'] !== 'cancelled'): ?>
                                            <button class="text-green-600 hover:text-green-900 text-sm"
                                                onclick="editEvent(<?php echo $event['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                            <button class="text-red-600 hover:text-red-900 text-sm"
                                                onclick="deleteEvent(<?php echo $event['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
<?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <!-- Event Templates -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-layer-group text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Event Templates</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Create events from predefined templates</p>
                        <div class="space-y-2">
                            <button
                                class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="fas fa-broom mr-2 text-gray-400"></i>
                                Regular Umuganda
                            </button>
                            <button
                                class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="fas fa-graduation-cap mr-2 text-gray-400"></i>
                                Training Session
                            </button>
                            <button
                                class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="fas fa-calendar-plus mr-2 text-gray-400"></i>
                                Special Event
                            </button>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-history text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                <span class="text-gray-600">July 19 event completed</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                <span class="text-gray-600">July 26 event scheduled</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-orange-500 rounded-full mr-3"></div>
                                <span class="text-gray-600">Training event added</span>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-bell text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                        </div>
                        <div class="space-y-3">
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-sm text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Tomorrow's event needs confirmation
                                </p>
                            </div>
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Send reminders to residents
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Create Event Modal -->
    <div id="createEventModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Create New Event</h3>
                        <button id="closeEventModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form id="createEventForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Title</label>
                            <input type="text" name="title" placeholder="Enter event title" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Type</label>
                            <select name="type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="umuganda">Regular Umuganda</option>
                                <option value="training">Training Session</option>
                                <option value="meeting">Community Meeting</option>
                                <option value="special">Special Event</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                <input type="date" name="event_date" required min="<?php echo date('Y-m-d') ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                                <input type="time" name="start_time" value="08:00" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                                <input type="time" name="end_time" value="11:00"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Max Participants</label>
                                <input type="number" name="max_participants" placeholder="Optional" min="1"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <input type="text" name="location" placeholder="Event location" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Google Maps Location
                                <span class="text-gray-500 text-xs">(Optional - Paste Google Maps link or coordinates)</span>
                            </label>
                            <input type="url" name="google_map_location" placeholder="https://maps.google.com/... or coordinates"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Copy link from Google Maps or enter coordinates (e.g., -1.9441, 30.0619)
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="3" placeholder="Event description and activities..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"></textarea>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="send_notifications" id="sendNotifications"
                                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <label for="sendNotifications" class="ml-2 text-sm text-gray-700">Send notifications to all
                                residents</label>
                        </div>

                        <div class="flex space-x-3 pt-4">
                            <button type="submit" id="createEventSubmit"
                                class="flex-1 bg-gradient-to-r from-primary-600 to-primary-700 text-white py-2 px-4 rounded-lg font-medium hover:from-primary-700 hover:to-primary-800 transition-all">
                                <span class="submit-text">Create Event</span>
                                <span class="submit-spinner hidden">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Creating...
                                </span>
                            </button>
                            <button type="button" id="cancelEventModal"
                                class="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div id="editEventModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Edit Event</h3>
                        <button id="closeEditModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form id="editEventForm" class="space-y-4">
                        <input type="hidden" name="event_id" id="editEventId">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Title</label>
                            <input type="text" name="title" id="editTitle" placeholder="Enter event title" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                <input type="date" name="event_date" id="editEventDate" required min="<?php echo date('Y-m-d') ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                                <input type="time" name="start_time" id="editStartTime" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                                <input type="time" name="end_time" id="editEndTime"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Max Participants</label>
                                <input type="number" name="max_participants" id="editMaxParticipants" placeholder="Optional" min="1"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <input type="text" name="location" id="editLocation" placeholder="Event location" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Google Maps Location
                                <span class="text-gray-500 text-xs">(Optional - Paste Google Maps link or coordinates)</span>
                            </label>
                            <input type="url" name="google_map_location" id="editGoogleMapLocation" placeholder="https://maps.google.com/... or coordinates"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Copy link from Google Maps or enter coordinates (e.g., -1.9441, 30.0619)
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" id="editDescription" rows="3" placeholder="Event description and activities..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"></textarea>
                        </div>

                        <div class="flex space-x-3 pt-4">
                            <button type="submit" id="editEventSubmit"
                                class="flex-1 bg-gradient-to-r from-primary-600 to-primary-700 text-white py-2 px-4 rounded-lg font-medium hover:from-primary-700 hover:to-primary-800 transition-all">
                                <span class="submit-text">Update Event</span>
                                <span class="submit-spinner hidden">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Updating...
                                </span>
                            </button>
                            <button type="button" id="cancelEditModal"
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
        const createEventBtn = document.getElementById('createEventBtn');
        const createEventModal = document.getElementById('createEventModal');
        const closeEventModal = document.getElementById('closeEventModal');
        const cancelEventModal = document.getElementById('cancelEventModal');

        // Get all create event buttons (there are two on the page)
        const createEventBtns = document.querySelectorAll('#createEventBtn');
        createEventBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                createEventModal.classList.remove('hidden');
                // Set minimum date to today
                const dateInput = document.querySelector('input[name="event_date"]');
                if (dateInput) {
                    dateInput.min = new Date().toISOString().split('T')[0];
                }
            });
        });

        closeEventModal.addEventListener('click', () => {
            createEventModal.classList.add('hidden');
            resetForm();
        });

        cancelEventModal.addEventListener('click', () => {
            createEventModal.classList.add('hidden');
            resetForm();
        });

        // Close modal on outside click
        createEventModal.addEventListener('click', (e) => {
            if (e.target === createEventModal) {
                createEventModal.classList.add('hidden');
                resetForm();
            }
        });

        // Reset form function
        function resetForm() {
            const form = document.getElementById('createEventForm');
            form.reset();
            // Reset submit button state
            const submitBtn = document.getElementById('createEventSubmit');
            const submitText = submitBtn.querySelector('.submit-text');
            const submitSpinner = submitBtn.querySelector('.submit-spinner');
            submitText.classList.remove('hidden');
            submitSpinner.classList.add('hidden');
            submitBtn.disabled = false;
        }

        // Show success message
        function showMessage(message, type = 'success') {
            // Create toast notification
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(toast);

            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);

            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }

        // Edit Modal functionality
        const editEventModal = document.getElementById('editEventModal');
        const closeEditModal = document.getElementById('closeEditModal');
        const cancelEditModal = document.getElementById('cancelEditModal');

        closeEditModal.addEventListener('click', () => {
            editEventModal.classList.add('hidden');
            resetEditForm();
        });

        cancelEditModal.addEventListener('click', () => {
            editEventModal.classList.add('hidden');
            resetEditForm();
        });

        // Close edit modal on outside click
        editEventModal.addEventListener('click', (e) => {
            if (e.target === editEventModal) {
                editEventModal.classList.add('hidden');
                resetEditForm();
            }
        });

        // Reset edit form function
        function resetEditForm() {
            const form = document.getElementById('editEventForm');
            form.reset();
            // Reset submit button state
            const submitBtn = document.getElementById('editEventSubmit');
            const submitText = submitBtn.querySelector('.submit-text');
            const submitSpinner = submitBtn.querySelector('.submit-spinner');
            submitText.classList.remove('hidden');
            submitSpinner.classList.add('hidden');
            submitBtn.disabled = false;
        }

        // Form submission handling
        document.getElementById('createEventForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('createEventSubmit');
            const submitText = submitBtn.querySelector('.submit-text');
            const submitSpinner = submitBtn.querySelector('.submit-spinner');

            // Show loading state
            submitText.classList.add('hidden');
            submitSpinner.classList.remove('hidden');
            submitBtn.disabled = true;

            try {
                const formData = new FormData(this);

                // Validate end time is after start time
                const startTime = formData.get('start_time');
                const endTime = formData.get('end_time');

                if (endTime && startTime && endTime <= startTime) {
                    throw new Error('End time must be after start time');
                }

                const response = await fetch('../../api/events.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('Event created successfully!', 'success');
                    createEventModal.classList.add('hidden');
                    resetForm();

                    // Reload page to show new event
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(result.message || 'Failed to create event');
                }

            } catch (error) {
                console.error('Error creating event:', error);
                showMessage(error.message || 'Failed to create event. Please try again.', 'error');
            } finally {
                // Reset button state
                submitText.classList.remove('hidden');
                submitSpinner.classList.add('hidden');
                submitBtn.disabled = false;
            }
        });

        // Edit form submission handling
        document.getElementById('editEventForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('editEventSubmit');
            const submitText = submitBtn.querySelector('.submit-text');
            const submitSpinner = submitBtn.querySelector('.submit-spinner');

            // Show loading state
            submitText.classList.add('hidden');
            submitSpinner.classList.remove('hidden');
            submitBtn.disabled = true;

            try {
                const formData = new FormData(this);
                const eventId = formData.get('event_id');

                // Validate end time is after start time
                const startTime = formData.get('start_time');
                const endTime = formData.get('end_time');

                if (endTime && startTime && endTime <= startTime) {
                    throw new Error('End time must be after start time');
                }

                // Convert FormData to URLSearchParams for PUT request
                const params = new URLSearchParams();
                for (let [key, value] of formData) {
                    if (key !== 'event_id') { // Don't include event_id in body
                        params.append(key, value);
                    }
                }

                const response = await fetch(`../../api/events.php?id=${eventId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: params.toString()
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('Event updated successfully!', 'success');
                    editEventModal.classList.add('hidden');
                    resetEditForm();

                    // Reload page to show updated event
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(result.message || 'Failed to update event');
                }

            } catch (error) {
                console.error('Error updating event:', error);
                showMessage(error.message || 'Failed to update event. Please try again.', 'error');
            } finally {
                // Reset button state
                submitText.classList.remove('hidden');
                submitSpinner.classList.add('hidden');
                submitBtn.disabled = false;
            }
        });

        // Event Management Functions
        function viewEvent(eventId) {
            // Create and show event details modal
            showEventDetails(eventId);
        }

        async function showEventDetails(eventId) {
            try {
                const response = await fetch(`../../api/events.php?id=${eventId}`);
                const result = await response.json();

                if (result.success) {
                    const event = result.event;

                    // Create modal HTML
                    const modalHTML = `
                        <div id="viewEventModal" class="fixed inset-0 bg-black bg-opacity-50 z-50">
                            <div class="flex items-center justify-center min-h-screen p-4">
                                <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-gray-900">Event Details</h3>
                                            <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-times text-lg"></i>
                                            </button>
                                        </div>

                                        <div class="space-y-4">
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-900">${event.title}</h4>
                                                <p class="text-gray-600">${event.description || 'No description provided'}</p>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="text-sm font-medium text-gray-700">Date</label>
                                                    <p class="text-gray-900">${new Date(event.event_date).toLocaleDateString()}</p>
                                                </div>
                                                <div>
                                                    <label class="text-sm font-medium text-gray-700">Time</label>
                                                    <p class="text-gray-900">
                                                        ${event.start_time}${event.end_time ? ' - ' + event.end_time : ''}
                                                    </p>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="text-sm font-medium text-gray-700">Location</label>
                                                <p class="text-gray-900">${event.location}</p>
                                                ${event.google_map_location ? `
                                                    <a href="${event.google_map_location}" target="_blank"
                                                       class="text-primary-600 hover:text-primary-800 text-sm">
                                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                                        View on Google Maps
                                                    </a>
                                                ` : ''}
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="text-sm font-medium text-gray-700">Status</label>
                                                    <p class="text-gray-900 capitalize">${event.status}</p>
                                                </div>
                                                <div>
                                                    <label class="text-sm font-medium text-gray-700">Attendance</label>
                                                    <p class="text-gray-900">${event.total_attended || 0} / ${event.total_registered || 0} registered</p>
                                                </div>
                                            </div>

                                            ${event.max_participants ? `
                                                <div>
                                                    <label class="text-sm font-medium text-gray-700">Max Participants</label>
                                                    <p class="text-gray-900">${event.max_participants}</p>
                                                </div>
                                            ` : ''}

                                            <div>
                                                <label class="text-sm font-medium text-gray-700">Created By</label>
                                                <p class="text-gray-900">${event.first_name} ${event.last_name}</p>
                                            </div>
                                        </div>

                                        <div class="flex justify-end mt-6">
                                            <button onclick="closeViewModal()"
                                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    // Add modal to DOM
                    document.body.insertAdjacentHTML('beforeend', modalHTML);
                } else {
                    showMessage('Failed to load event details', 'error');
                }
            } catch (error) {
                console.error('Error fetching event details:', error);
                showMessage('Failed to load event details', 'error');
            }
        }

        function closeViewModal() {
            const modal = document.getElementById('viewEventModal');
            if (modal) {
                modal.remove();
            }
        }

        async function editEvent(eventId) {
            try {
                // Fetch event details
                const response = await fetch(`../../api/events.php?id=${eventId}`);
                const result = await response.json();

                if (result.success) {
                    const event = result.event;

                    // Populate edit form
                    document.getElementById('editEventId').value = event.id;
                    document.getElementById('editTitle').value = event.title;
                    document.getElementById('editEventDate').value = event.event_date;
                    document.getElementById('editStartTime').value = event.start_time.substring(0, 5); // Remove seconds
                    document.getElementById('editEndTime').value = event.end_time ? event.end_time.substring(0, 5) : '';
                    document.getElementById('editLocation').value = event.location;
                    document.getElementById('editGoogleMapLocation').value = event.google_map_location || '';
                    document.getElementById('editMaxParticipants').value = event.max_participants || '';
                    document.getElementById('editDescription').value = event.description || '';

                    // Show edit modal
                    document.getElementById('editEventModal').classList.remove('hidden');
                } else {
                    showMessage('Failed to load event details', 'error');
                }
            } catch (error) {
                console.error('Error loading event details:', error);
                showMessage('Failed to load event details', 'error');
            }
        }

        async function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                try {
                    const response = await fetch(`../../api/events.php?id=${eventId}`, {
                        method: 'DELETE'
                    });

                    const result = await response.json();

                    if (result.success) {
                        showMessage(result.message, 'success');
                        // Reload page to update list
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        throw new Error(result.message || 'Failed to delete event');
                    }
                } catch (error) {
                    console.error('Error deleting event:', error);
                    showMessage(error.message || 'Failed to delete event', 'error');
                }
            }
        }

    </script>


<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>