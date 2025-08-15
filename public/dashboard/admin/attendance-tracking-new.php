<?php
    /**
     * Attendance Tracking Dashboard
     * View and manage attendance records for Umuganda events
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
    require_once __DIR__ . '/../../../src/models/UmugandaEvent.php';

    // Use the global database instance
    global $db;
    $connection = $db->getConnection();

    $user          = new User();
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
    $firstName = htmlspecialchars($adminInfo['first_name']);
    $lastName  = htmlspecialchars($adminInfo['last_name']);
    $fullName  = $firstName . ' ' . $lastName;
    $initials  = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

    // Get admin's assigned sector from admin_assignments table
    $adminSectorQuery = "
    SELECT s.name as sector_name, s.id as sector_id, s.code as sector_code,
           d.name as district_name, d.id as district_id
    FROM admin_assignments aa
    JOIN sectors s ON aa.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    WHERE aa.admin_id = ? AND aa.is_active = 1
    LIMIT 1";

    $stmt = $connection->prepare($adminSectorQuery);
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $sectorResult = $stmt->get_result()->fetch_assoc();

    if ($sectorResult) {
        $sectorId     = $sectorResult['sector_id'];
        $sectorName   = $sectorResult['sector_name'];
        $sectorCode   = $sectorResult['sector_code'];
        $districtName = $sectorResult['district_name'];
    } else {
        // Fallback: try to get sector from user table if available
        if (isset($adminInfo['sector_id']) && $adminInfo['sector_id']) {
            $sectorQuery = "SELECT name, code FROM sectors WHERE id = ?";
            $stmt        = $connection->prepare($sectorQuery);
            $stmt->bind_param('i', $adminInfo['sector_id']);
            $stmt->execute();
            $sectorData   = $stmt->get_result()->fetch_assoc();
            $sectorId     = $adminInfo['sector_id'];
            $sectorName   = $sectorData ? $sectorData['name'] : 'Kimironko';
            $sectorCode   = $sectorData ? $sectorData['code'] : 'KIM';
            $districtName = 'Gasabo';
        } else {
            // Default sector for testing
            $sectorId     = 1;
            $sectorName   = 'Kimironko';
            $sectorCode   = 'KIM';
            $districtName = 'Gasabo';
        }
    }

    // Initialize variables
    $selectedEventId = $_GET['event_id'] ?? null;
    $selectedStatus  = $_GET['status'] ?? '';
    $selectedCell    = $_GET['cell'] ?? '';
    $searchTerm      = $_GET['search'] ?? '';

    // Get current or upcoming Umuganda events for this sector
    try {
        $eventsQuery = "
        SELECT e.id, e.title, e.description, e.event_date, e.start_time, e.end_time,
               e.location, e.status
        FROM umuganda_events e
        WHERE e.sector_id = ?
        AND e.status IN ('scheduled', 'ongoing', 'completed')
        ORDER BY e.event_date DESC, e.start_time DESC
        LIMIT 20";

        $eventsStmt = $connection->prepare($eventsQuery);
        $eventsStmt->bind_param('i', $sectorId);
        $eventsStmt->execute();
        $eventsResult    = $eventsStmt->get_result();
        $availableEvents = $eventsResult->fetch_all(MYSQLI_ASSOC);

        // If no events found with sector_id, try with sector name (fallback)
        if (empty($availableEvents)) {
            $fallbackQuery = "
            SELECT e.id, e.title, e.description, e.event_date, e.start_time, e.end_time,
                   e.location, e.status
            FROM umuganda_events e
            ORDER BY e.event_date DESC, e.start_time DESC
            LIMIT 20";

            $fallbackStmt = $connection->prepare($fallbackQuery);
            $fallbackStmt->execute();
            $fallbackResult  = $fallbackStmt->get_result();
            $availableEvents = $fallbackResult->fetch_all(MYSQLI_ASSOC);
        }
    } catch (Exception $e) {
        $availableEvents = [];
        if (! isset($error)) {
            $error = "Error loading events: " . $e->getMessage();
        }
    }

    // If no event selected, default to the most recent/current event
    if (! $selectedEventId && ! empty($availableEvents)) {
        $selectedEventId = $availableEvents[0]['id'];
    }

    // Get selected event details
    $selectedEvent = null;
    if ($selectedEventId) {
        foreach ($availableEvents as $event) {
            if ($event['id'] == $selectedEventId) {
                $selectedEvent = $event;
                break;
            }
        }
    }

    // Initialize stats and attendance records
    $attendanceStats = [
        'total_residents' => 0,
        'present'         => 0,
        'late'            => 0,
        'absent'          => 0,
        'excused'         => 0,
        'not_marked'      => 0,
    ];
    $attendanceRecords = [];
    $cells             = [];

    if (! $selectedEvent) {
        $error = "No Umuganda event found. Please contact your administrator to create events.";
    } else {
        try {
            // Get attendance statistics for the selected event
            $statsQuery = "
            SELECT
                COUNT(*) as total_residents,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused
            FROM users u
            LEFT JOIN attendance a ON u.id = a.user_id AND a.event_id = ?
            WHERE u.role = 'resident' AND u.status = 'active' AND u.sector_id = ?";

            $statsStmt = $connection->prepare($statsQuery);
            $statsStmt->bind_param('ii', $selectedEventId, $sectorId);
            $statsStmt->execute();
            $statsResult = $statsStmt->get_result()->fetch_assoc();

            if ($statsResult) {
                $attendanceStats['total_residents'] = $statsResult['total_residents'];
                $attendanceStats['present']         = $statsResult['present'];
                $attendanceStats['late']            = $statsResult['late'];
                $attendanceStats['absent']          = $statsResult['absent'];
                $attendanceStats['excused']         = $statsResult['excused'];
                $attendanceStats['not_marked']      = $attendanceStats['total_residents'] -
                    ($attendanceStats['present'] + $attendanceStats['late'] + $attendanceStats['absent'] + $attendanceStats['excused']);
            }

            // Get detailed attendance records
            $query = "
            SELECT u.id as user_id, u.first_name, u.last_name, u.email,
                   c.name as cell_name, a.status, a.check_in_time, a.notes, a.excuse_reason,
                   a.created_at as marked_at, f.amount as fine_amount, f.status as fine_status,
                   recorder.first_name as recorded_by_name
            FROM users u
            LEFT JOIN cells c ON u.cell_id = c.id
            LEFT JOIN attendance a ON u.id = a.user_id AND a.event_id = ?
            LEFT JOIN fines f ON a.id = f.attendance_id AND f.status != 'waived'
            LEFT JOIN users recorder ON a.recorded_by = recorder.id
            WHERE u.role = 'resident' AND u.status = 'active' AND u.sector_id = ?";

            $params = [$selectedEventId, $sectorId];
            $types  = 'ii';

            // Add filters
            if ($selectedStatus) {
                if ($selectedStatus === 'not_marked') {
                    $query .= " AND a.status IS NULL";
                } else {
                    $query .= " AND a.status = ?";
                    $params[] = $selectedStatus;
                    $types .= 's';
                }
            }

            if ($selectedCell) {
                $query .= " AND c.name = ?";
                $params[] = $selectedCell;
                $types .= 's';
            }

            if ($searchTerm) {
                $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
                $searchPattern = "%$searchTerm%";
                $params[]      = $searchPattern;
                $params[]      = $searchPattern;
                $params[]      = $searchPattern;
                $types .= 'sss';
            }

            $query .= " ORDER BY u.first_name, u.last_name";

            $stmt = $connection->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result            = $stmt->get_result();
            $attendanceRecords = $result->fetch_all(MYSQLI_ASSOC);

            // Get unique cells for filter
            $cellQuery = "SELECT DISTINCT c.name as cell_name
                     FROM users u
                     JOIN cells c ON u.cell_id = c.id
                     WHERE u.role = 'resident' AND u.sector_id = ?
                     ORDER BY c.name";
            $cellStmt = $connection->prepare($cellQuery);
            $cellStmt->bind_param('i', $sectorId);
            $cellStmt->execute();
            $cellResult = $cellStmt->get_result();
            $cells      = array_column($cellResult->fetch_all(MYSQLI_ASSOC), 'cell_name');

        } catch (Exception $e) {
            $error             = "Database error: " . $e->getMessage();
            $attendanceRecords = [];
            $cells             = [];
        }
    }

    // Handle success message from redirects
    $successMessage = $_SESSION['success_message'] ?? null;
    if ($successMessage) {
        unset($_SESSION['success_message']);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Tracking - Umuganda Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        'success': {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                        },
                        'danger': {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                        },
                        'warning': {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div id="main-content" class="content-transition lg:ml-64">
        <!-- Top Navigation -->
        <?php include __DIR__ . '/partials/top-nav.php'; ?>

        <!-- Main Content Area -->
        <main class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Attendance Tracking</h1>
                            <p class="mt-1 text-sm text-gray-600">
                                Monitor and manage Umuganda attendance for <strong><?php echo htmlspecialchars($sectorName); ?></strong> sector
                            </p>
                            <?php if (isset($error)): ?>
                                <div class="mt-2 text-red-600 text-sm">Error:<?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
<?php if ($successMessage): ?>
                                <div class="mt-2 text-green-600 text-sm">✅<?php echo htmlspecialchars($successMessage); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                            <button onclick="exportReport()"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                                <i class="fas fa-download mr-2"></i>
                                Export Report
                            </button>
                            <a href="attendance-marking.php<?php echo $selectedEventId ? '?event_id=' . $selectedEventId : ''; ?>"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors shadow-sm">
                                <i class="fas fa-plus mr-2"></i>
                                Mark Attendance
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Attendance Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Present -->
                    <div class="bg-gradient-to-br from-white to-green-50 rounded-xl shadow-sm p-6 border border-green-100 hover:shadow-lg hover:border-green-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-green-600 uppercase tracking-wide">Present</p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $attendanceStats['present']; ?></p>
                                <div class="flex items-center mt-3">
                                    <span class="inline-flex items-center text-sm text-success-600 font-semibold bg-success-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-check text-xs mr-1"></i>
                                        <?php echo $attendanceStats['total_residents'] > 0 ? round(($attendanceStats['present'] / $attendanceStats['total_residents']) * 100, 1) : 0; ?>%
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">of total</span>
                                </div>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-success-500 to-success-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-user-check text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Late -->
                    <div class="bg-gradient-to-br from-white to-orange-50 rounded-xl shadow-sm p-6 border border-orange-100 hover:shadow-lg hover:border-orange-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Late Arrivals</p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $attendanceStats['late']; ?></p>
                                <div class="flex items-center mt-3">
                                    <span class="inline-flex items-center text-sm text-warning-600 font-semibold bg-warning-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-clock text-xs mr-1"></i>
                                        <?php echo $attendanceStats['total_residents'] > 0 ? round(($attendanceStats['late'] / $attendanceStats['total_residents']) * 100, 1) : 0; ?>%
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">of total</span>
                                </div>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-clock text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Absent -->
                    <div class="bg-gradient-to-br from-white to-red-50 rounded-xl shadow-sm p-6 border border-red-100 hover:shadow-lg hover:border-red-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-red-600 uppercase tracking-wide">Absent</p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $attendanceStats['absent']; ?></p>
                                <div class="flex items-center mt-3">
                                    <span class="inline-flex items-center text-sm text-danger-600 font-semibold bg-danger-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-user-times text-xs mr-1"></i>
                                        <?php echo $attendanceStats['total_residents'] > 0 ? round(($attendanceStats['absent'] / $attendanceStats['total_residents']) * 100, 1) : 0; ?>%
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">of total</span>
                                </div>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-danger-500 to-danger-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-user-times text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Not Marked -->
                    <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-lg hover:border-gray-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Not Marked</p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $attendanceStats['not_marked']; ?></p>
                                <div class="flex items-center mt-3">
                                    <span class="inline-flex items-center text-sm text-gray-600 font-semibold bg-gray-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-question text-xs mr-1"></i>
                                        <?php echo $attendanceStats['total_residents'] > 0 ? round(($attendanceStats['not_marked'] / $attendanceStats['total_residents']) * 100, 1) : 0; ?>%
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">pending</span>
                                </div>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-gray-400 to-gray-500 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-user-question text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex flex-col sm:flex-row gap-4 flex-1">
                            <!-- Event Filter -->
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Umuganda Event</label>
                                <select id="eventFilter" onchange="applyFilters()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                                    <?php if (empty($availableEvents)): ?>
                                        <option value="">No events available</option>
                                    <?php else: ?>
<?php foreach ($availableEvents as $event): ?>
                                            <option value="<?php echo $event['id']; ?>"<?php echo $selectedEventId == $event['id'] ? ' selected' : ''; ?>>
                                                <?php echo htmlspecialchars($event['title']); ?> -
                                                <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                                (<?php echo ucfirst($event['status']); ?>)
                                            </option>
                                        <?php endforeach; ?>
<?php endif; ?>
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="statusFilter" onchange="applyFilters()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                                    <option value="">All Statuses</option>
                                    <option value="present"<?php echo $selectedStatus === 'present' ? ' selected' : ''; ?>>Present</option>
                                    <option value="late"<?php echo $selectedStatus === 'late' ? ' selected' : ''; ?>>Late</option>
                                    <option value="absent"<?php echo $selectedStatus === 'absent' ? ' selected' : ''; ?>>Absent</option>
                                    <option value="excused"<?php echo $selectedStatus === 'excused' ? ' selected' : ''; ?>>Excused</option>
                                    <option value="not_marked"<?php echo $selectedStatus === 'not_marked' ? ' selected' : ''; ?>>Not Marked</option>
                                </select>
                            </div>

                            <!-- Cell Filter -->
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cell</label>
                                <select id="cellFilter" onchange="applyFilters()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                                    <option value="">All Cells</option>
                                    <?php foreach ($cells as $cell): ?>
                                        <option value="<?php echo htmlspecialchars($cell); ?>"<?php echo $selectedCell === $cell ? ' selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cell); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="relative">
                                <input type="text" id="searchInput" placeholder="Search residents..." value="<?php echo htmlspecialchars($searchTerm); ?>"
                                    class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                            <button onclick="applyFilters()"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                                <i class="fas fa-filter mr-2"></i>
                                Apply
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Attendance Records Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Table Header -->
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Attendance Records
                                <?php if ($selectedEvent): ?>
                                    <span class="text-sm font-normal text-gray-600 ml-2">
                                        for                                            <?php echo htmlspecialchars($selectedEvent['title']); ?>
                                    </span>
                                <?php endif; ?>
                            </h3>
                            <div class="mt-3 sm:mt-0 text-sm text-gray-600">
                                <?php echo count($attendanceRecords); ?> residents
                                <?php if ($selectedEventId): ?>
                                    |<?php echo htmlspecialchars($selectedEvent['event_date']); ?>
<?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Table Content -->
                    <div class="overflow-x-auto">
                        <?php if (! empty($attendanceRecords)): ?>
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Resident
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Cell
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Check-in Time
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Fine
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Notes
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Recorded By
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($attendanceRecords as $record): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center">
                                                            <span class="text-white text-xs font-semibold">
                                                                <?php echo strtoupper(substr($record['first_name'], 0, 1) . substr($record['last_name'], 0, 1)); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            <?php echo htmlspecialchars($record['email'] ?: 'N/A'); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                    <?php echo htmlspecialchars($record['cell_name'] ?: 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($record['status']): ?>
<?php
    $statusColors = [
        'present' => 'bg-green-100 text-green-800',
        'late'    => 'bg-orange-100 text-orange-800',
        'absent'  => 'bg-red-100 text-red-800',
        'excused' => 'bg-blue-100 text-blue-800',
    ];
    $statusIcons = [
        'present' => 'fas fa-check',
        'late'    => 'fas fa-clock',
        'absent'  => 'fas fa-times',
        'excused' => 'fas fa-user-shield',
    ];
?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium<?php echo $statusColors[$record['status']]; ?>">
                                                        <i class="<?php echo $statusIcons[$record['status']]; ?> mr-1"></i>
                                                        <?php echo ucfirst($record['status']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        <i class="fas fa-question mr-1"></i>
                                                        Not Marked
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php if ($record['check_in_time']): ?>
<?php echo date('H:i', strtotime($record['check_in_time'])); ?>
<?php else: ?>
                                                    <span class="text-gray-400">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php if ($record['fine_amount'] && $record['fine_amount'] > 0): ?>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-coins mr-1"></i>
                                                        <?php echo number_format($record['fine_amount']); ?> RWF
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-gray-400">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <div class="max-w-xs">
                                                    <?php if ($record['notes']): ?>
                                                        <div class="truncate" title="<?php echo htmlspecialchars($record['notes']); ?>">
                                                            <?php echo htmlspecialchars($record['notes']); ?>
                                                        </div>
                                                    <?php endif; ?>
<?php if ($record['excuse_reason']): ?>
                                                        <div class="text-blue-600 text-xs mt-1 truncate" title="<?php echo htmlspecialchars($record['excuse_reason']); ?>">
                                                            <i class="fas fa-info-circle mr-1"></i>
                                                            <?php echo htmlspecialchars($record['excuse_reason']); ?>
                                                        </div>
                                                    <?php endif; ?>
<?php if (! $record['notes'] && ! $record['excuse_reason']): ?>
                                                        <span class="text-gray-400">-</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php if ($record['recorded_by_name']): ?>
                                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($record['recorded_by_name']); ?></div>
                                                    <?php if ($record['marked_at']): ?>
                                                        <div class="text-xs text-gray-500"><?php echo date('M j, H:i', strtotime($record['marked_at'])); ?></div>
                                                    <?php endif; ?>
<?php else: ?>
                                                    <span class="text-gray-400">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <!-- Empty State -->
                            <div class="text-center py-12">
                                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-users text-gray-400 text-xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No attendance records found</h3>
                                <p class="text-gray-500 mb-4">
                                    <?php if (! $selectedEvent): ?>
                                        Please select an event to view attendance records.
                                    <?php else: ?>
                                        No attendance records match your current filters.
                                    <?php endif; ?>
                                </p>
                                <?php if ($selectedEvent): ?>
                                    <a href="attendance-marking.php?event_id=<?php echo $selectedEventId; ?>"
                                       class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                                        <i class="fas fa-plus mr-2"></i>
                                        Start Marking Attendance
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        // Filter functionality
        function applyFilters() {
            const eventFilter = document.getElementById('eventFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const cellFilter = document.getElementById('cellFilter').value;
            const searchInput = document.getElementById('searchInput').value;

            // Build URL parameters
            const params = new URLSearchParams();
            if (eventFilter) params.append('event_id', eventFilter);
            if (statusFilter) params.append('status', statusFilter);
            if (cellFilter) params.append('cell', cellFilter);
            if (searchInput) params.append('search', searchInput);

            // Reload page with filters
            window.location.href = 'attendance-tracking.php?' + params.toString();
        }

        // Export functionality
        function exportReport() {
            const eventFilter = document.getElementById('eventFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const cellFilter = document.getElementById('cellFilter').value;
            const searchInput = document.getElementById('searchInput').value;

            // Build URL parameters for export
            const params = new URLSearchParams();
            if (eventFilter) params.append('event_id', eventFilter);
            if (statusFilter) params.append('status', statusFilter);
            if (cellFilter) params.append('cell', cellFilter);
            if (searchInput) params.append('search', searchInput);
            params.append('export', 'csv');

            // Open export URL in new window
            window.open('attendance-export.php?' + params.toString(), '_blank');
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            // Only if not typing in an input field
            if (event.target.tagName !== 'INPUT' && event.target.tagName !== 'TEXTAREA' && event.target.tagName !== 'SELECT') {
                switch(event.key) {
                    case 'r': // R to refresh/reload
                    case 'R':
                        event.preventDefault();
                        window.location.reload();
                        break;
                    case 'm': // M to mark attendance
                    case 'M':
                        event.preventDefault();
                        const eventId = document.getElementById('eventFilter').value;
                        window.location.href = 'attendance-marking.php' + (eventId ? '?event_id=' + eventId : '');
                        break;
                    case 'e': // E to export
                    case 'E':
                        event.preventDefault();
                        exportReport();
                        break;
                }
            }
        });

        // Auto-refresh functionality (optional)
        setInterval(function() {
            // Auto-refresh every 5 minutes to show real-time updates
            // Uncomment if you want auto-refresh
            // window.location.reload();
        }, 300000); // 5 minutes
    </script>

    <!-- Footer -->
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
