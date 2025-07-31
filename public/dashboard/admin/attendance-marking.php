<?php
    /**
     * Bulk Attendance Marking
     * Mark attendance for multiple residents at once
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
            $sectorName   = $sectorData ? $sectorData['name'] : 'Kimironko'; // Default for demo
            $sectorCode   = $sectorData ? $sectorData['code'] : 'KIM';
            $districtName = 'Gasabo'; // Default district
        } else {
                               // Default sector for testing
            $sectorId     = 1; // Assuming Kimironko has ID 1
            $sectorName   = 'Kimironko';
            $sectorCode   = 'KIM';
            $districtName = 'Gasabo';
        }
    }

    // Initialize variables
    $selectedEventId = $_GET['event_id'] ?? null;
    $selectedCell    = $_GET['cell'] ?? '';
    $searchTerm      = $_GET['search'] ?? '';

    // Get current or upcoming Umuganda events for this sector
    // First, let's check what columns exist and build the query accordingly
    try {
        $eventsQuery = "
            SELECT e.id, e.title, e.description, e.event_date, e.start_time, e.end_time,
                   e.location, e.status
            FROM umuganda_events e
            WHERE e.sector_id = ?
            AND e.status IN ('scheduled', 'ongoing', 'completed')
            ORDER BY e.event_date DESC, e.start_time DESC
            LIMIT 10";

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
                WHERE e.status IN ('scheduled', 'ongoing', 'completed')
                ORDER BY e.event_date DESC, e.start_time DESC
                LIMIT 10";

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

    if (! $selectedEvent) {
        $error            = "No Umuganda event found. Please contact your administrator to create events.";
        $residents        = [];
        $cells            = [];
        $attendanceLookup = [];
    } else {
        // Get all residents in the sector
        try {
            // Use proper foreign key relationships for location hierarchy
            $query = "SELECT u.id as user_id, u.first_name, u.last_name, u.email,
                             c.name as cell_name, u.cell_id
                      FROM users u
                      LEFT JOIN cells c ON u.cell_id = c.id
                      WHERE u.role = 'resident' AND u.status = 'active' AND u.sector_id = ?";

            $params = [$sectorId];
            $types  = 'i';

            // Add cell filter if selected
            if ($selectedCell) {
                $query .= " AND c.name = ?";
                $params[] = $selectedCell;
                $types .= 's';
            }

            // Add search filter if provided
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
            $result    = $stmt->get_result();
            $residents = $result->fetch_all(MYSQLI_ASSOC);

            // Get existing attendance records for the selected event
            $attendanceQuery = "
                SELECT a.user_id, a.status, a.check_in_time, a.notes, a.excuse_reason,
                       f.amount as fine_amount
                FROM attendance a
                LEFT JOIN fines f ON a.id = f.attendance_id AND f.status != 'waived'
                WHERE a.event_id = ?";

            $attendanceStmt = $connection->prepare($attendanceQuery);
            $attendanceStmt->bind_param('i', $selectedEventId);
            $attendanceStmt->execute();
            $attendanceResult   = $attendanceStmt->get_result();
            $existingAttendance = $attendanceResult->fetch_all(MYSQLI_ASSOC);

            // Create a lookup array for existing attendance
            $attendanceLookup = [];
            foreach ($existingAttendance as $record) {
                $attendanceLookup[$record['user_id']] = $record;
            }

            // Get unique cells for filter (using location hierarchy)
            $cellQuery = "SELECT DISTINCT c.name as cell_name
                         FROM users u
                         JOIN cells c ON u.cell_id = c.id
                         WHERE u.role = 'resident' AND u.sector_id = ?
                         ORDER BY c.name";
            $cellStmt = $connection->prepare($cellQuery);
            $cellStmt->bind_param('i', $sectorId);
            $cellStmt->execute();
            $cellResult = $cellStmt->get_result();
            $cells      = $cellResult->fetch_all(MYSQLI_ASSOC);
            $cells      = array_column($cells, 'cell_name');

        } catch (Exception $e) {
            $error            = "Database error: " . $e->getMessage();
            $residents        = [];
            $cells            = [];
            $attendanceLookup = [];
        }
    }

    // Handle form submission
    if ($_POST && isset($_POST['attendance_data']) && $selectedEventId) {
        try {
            $connection->autocommit(false);

            $attendanceData = json_decode($_POST['attendance_data'], true);

            foreach ($attendanceData as $data) {
                // Check if attendance record already exists
                $checkQuery = "SELECT id FROM attendance WHERE user_id = ? AND event_id = ?";
                $checkStmt  = $connection->prepare($checkQuery);
                $checkStmt->bind_param('ii', $data['user_id'], $selectedEventId);
                $checkStmt->execute();
                $checkResult    = $checkStmt->get_result();
                $existingRecord = $checkResult->fetch_assoc();

                if ($existingRecord) {
                    // Update existing record
                    $updateQuery = "UPDATE attendance
                                   SET status = ?, check_in_time = ?, notes = ?, excuse_reason = ?, recorded_by = ?, updated_at = NOW()
                                   WHERE user_id = ? AND event_id = ?";
                    $updateStmt = $connection->prepare($updateQuery);
                    $updateStmt->bind_param('ssssiii',
                        $data['status'],
                        $data['check_in_time'],
                        $data['notes'],
                        $data['excuse_reason'],
                        $adminId,
                        $data['user_id'],
                        $selectedEventId
                    );
                    $updateStmt->execute();
                    $attendanceId = $existingRecord['id'];
                } else {
                    // Insert new record
                    $insertQuery = "INSERT INTO attendance (user_id, event_id, status, check_in_time, notes, excuse_reason, recorded_by, created_at)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                    $insertStmt = $connection->prepare($insertQuery);
                    $insertStmt->bind_param('iissssi',
                        $data['user_id'],
                        $selectedEventId,
                        $data['status'],
                        $data['check_in_time'],
                        $data['notes'],
                        $data['excuse_reason'],
                        $adminId
                    );
                    $insertStmt->execute();
                    $attendanceId = $connection->insert_id;
                }

                // Handle fines based on attendance status
                if (isset($data['fine_amount']) && $data['fine_amount'] > 0) {
                    // Check if fine already exists
                    $fineCheckQuery = "SELECT id FROM fines WHERE attendance_id = ? AND status != 'waived'";
                    $fineCheckStmt  = $connection->prepare($fineCheckQuery);
                    $fineCheckStmt->bind_param('i', $attendanceId);
                    $fineCheckStmt->execute();
                    $fineExists = $fineCheckStmt->get_result()->fetch_assoc();

                    if ($fineExists) {
                        // Update existing fine
                        $updateFineQuery = "UPDATE fines SET amount = ?, updated_at = NOW() WHERE id = ?";
                        $updateFineStmt  = $connection->prepare($updateFineQuery);
                        $updateFineStmt->bind_param('di', $data['fine_amount'], $fineExists['id']);
                        $updateFineStmt->execute();
                    } else {
                        // Create new fine
                        $fineReason      = ($data['status'] === 'late') ? 'late_arrival' : (($data['status'] === 'absent') ? 'absence' : 'other');
                        $insertFineQuery = "INSERT INTO fines (user_id, event_id, attendance_id, amount, reason, status, due_date, created_by, created_at)
                                           VALUES (?, ?, ?, ?, ?, 'unpaid', DATE_ADD(NOW(), INTERVAL 30 DAY), ?, NOW())";
                        $insertFineStmt = $connection->prepare($insertFineQuery);
                        $insertFineStmt->bind_param('iiidsi',
                            $data['user_id'],
                            $selectedEventId,
                            $attendanceId,
                            $data['fine_amount'],
                            $fineReason,
                            $adminId
                        );
                        $insertFineStmt->execute();
                    }
                }
            }

            $connection->commit();
            $connection->autocommit(true);
            $_SESSION['success_message'] = "Attendance records updated successfully for " . htmlspecialchars($selectedEvent['title']) . "!";
            header("Location: attendance-tracking.php?event_id=" . $selectedEventId);
            exit();

        } catch (Exception $e) {
            $connection->rollback();
            $connection->autocommit(true);
            $error = "Error saving attendance: " . $e->getMessage();
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

        <!-- Bulk Attendance Marking Content -->
        <main class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center mb-2">
                                <button onclick="window.location.href='attendance-tracking.php'"
                                    class="mr-3 p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-arrow-left"></i>
                                </button>
                                <h1 class="text-2xl font-bold text-gray-900">Bulk Attendance Marking</h1>
                            </div>
                            <p class="text-gray-600">
                                <?php if ($selectedEvent): ?>
                                    Mark attendance for <strong><?php echo htmlspecialchars($selectedEvent['title']); ?></strong> in<?php echo htmlspecialchars($sectorName); ?> Sector
                                    <br><span class="text-sm text-gray-500">
                                        📅                                                                                                                                                                                 <?php echo date('F j, Y', strtotime($selectedEvent['event_date'])); ?> at<?php echo date('g:i A', strtotime($selectedEvent['start_time'])); ?>
                                        | 📍<?php echo htmlspecialchars($selectedEvent['location']); ?>
                                        | Status: <span class="px-2 py-1 text-xs rounded-full<?php echo $selectedEvent['status'] === 'completed' ? 'bg-green-100 text-green-800' : ($selectedEvent['status'] === 'ongoing' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                            <?php echo ucfirst($selectedEvent['status']); ?>
                                        </span>
                                    </span>
                                <?php else: ?>
                                    No event selected
                                <?php endif; ?>
                            </p>
                            <?php if (isset($error)): ?>
                                <div class="mt-2 text-red-600 text-sm">Error:<?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                            <button onclick="toggleQRScanner()"
                                class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors shadow-sm">
                                <i class="fas fa-qrcode mr-2"></i>
                                QR Scanner
                                <span class="ml-2 text-xs opacity-75">(Space)</span>
                            </button>
                            <button onclick="markAllPresent()"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                                <i class="fas fa-check-double mr-2"></i>
                                Mark All Present
                            </button>
                            <button onclick="saveAllAttendance()"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                                <i class="fas fa-save mr-2"></i>
                                Save Attendance
                            </button>
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
                                            <option value="<?php echo $event['id']; ?>"<?php echo $selectedEventId == $event['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($event['title']); ?> -
                                                <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                                (<?php echo ucfirst($event['status']); ?>)
                                            </option>
                                        <?php endforeach; ?>
<?php endif; ?>
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

                <!-- Progress Bar -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Attendance Progress</span>
                        <span class="text-sm text-gray-600" id="progressText">0 of                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             <?php echo count($residents); ?> marked</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-primary-600 h-2 rounded-full transition-all duration-300" id="progressBar" style="width: 0%"></div>
                    </div>
                </div>

                <!-- QR Scanner Section (Hidden by default) -->
                <div id="qrScannerSection" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 hidden">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">📱 QR Code Scanner</h3>
                        <div class="text-sm text-gray-600">
                            <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                <i class="fas fa-info-circle mr-1"></i>
                                Fast Attendance Marking
                            </span>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-semibold text-blue-900 mb-2">📋 How to Use:</h4>
                                <ol class="text-sm text-blue-800 space-y-1">
                                    <li>1. <strong>Select an Umuganda event</strong> from the dropdown above</li>
                                    <li>2. <strong>Click "Start Scanner"</strong> and allow camera access</li>
                                    <li>3. <strong>Scan resident QR codes</strong> - attendance will be marked automatically</li>
                                    <li>4. <strong>View real-time progress</strong> in the recent scans section</li>
                                </ol>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-blue-900 mb-2">⌨️ Keyboard Shortcuts:</h4>
                                <ul class="text-sm text-blue-800 space-y-1">
                                    <li><kbd class="px-1 py-0.5 bg-blue-200 rounded text-xs">Space</kbd> - Toggle scanner</li>
                                    <li><kbd class="px-1 py-0.5 bg-blue-200 rounded text-xs">Q</kbd> - Open scanner</li>
                                    <li><kbd class="px-1 py-0.5 bg-blue-200 rounded text-xs">Esc</kbd> - Stop scanner</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Scan Status Message -->
                    <div id="scanMessage" class="hidden"></div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Scanner Column -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-3">📷 Camera Scanner</h4>
                            <div id="qrReader" class="w-full max-w-md mx-auto bg-gray-100 rounded-lg h-64 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-qrcode text-4xl text-gray-400 mb-2"></i>
                                    <p class="text-gray-600">Click "Start Scanner" to begin</p>
                                    <p class="text-xs text-gray-500 mt-2">Ensure good lighting for best results</p>
                                </div>
                            </div>
                            <div class="mt-4 flex gap-3">
                                <button onclick="startQRScanner()" id="startScanBtn"
                                    class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                                    <i class="fas fa-play mr-2"></i>
                                    Start Scanner
                                </button>
                                <button onclick="stopQRScanner()" id="stopScanBtn"
                                    class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors shadow-sm" disabled>
                                    <i class="fas fa-stop mr-2"></i>
                                    Stop Scanner
                                </button>
                            </div>
                            <div class="mt-3 text-center">
                                <a href="qr-generator.php" target="_blank"
                                   class="text-sm text-primary-600 hover:text-primary-700 underline">
                                    <i class="fas fa-external-link-alt mr-1"></i>
                                    Generate QR Codes
                                </a>
                            </div>
                        </div>

                        <!-- Recent Scans Column -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-3">📊 Recent Scans</h4>
                            <div class="bg-gray-50 rounded-lg p-4 h-64 overflow-y-auto">
                                <div id="recentScans">
                                    <div class="text-center text-gray-500 py-8">
                                        <i class="fas fa-qrcode text-2xl mb-2"></i>
                                        <p class="text-sm">No scans yet</p>
                                        <p class="text-xs">Scanned residents will appear here</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between text-xs text-gray-600">
                                <span>Real-time attendance tracking</span>
                                <span id="scanCounter">0 scanned</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Residents List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Table Header -->
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Residents List
                                <span class="text-sm font-normal text-gray-600 ml-2">
                                    (<?php echo count($residents); ?> residents)
                                </span>
                            </h3>
                            <div class="mt-3 sm:mt-0 flex items-center gap-3">
                                <button onclick="selectAll()"
                                    class="text-sm px-3 py-1 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                    Select All
                                </button>
                                <button onclick="clearAll()"
                                    class="text-sm px-3 py-1 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                    Clear All
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Table Content -->
                    <div class="overflow-x-auto">
                        <?php if (! empty($residents)): ?>
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()"
                                                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                        </th>
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
                                            Time
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Fine (RWF)
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Notes/Excuse
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="residentsTableBody">
                                    <?php foreach ($residents as $resident): ?>
<?php
    $existingRecord = $attendanceLookup[$resident['user_id']] ?? null;
    $isMarked       = $existingRecord !== null;
?>
                                        <tr class="hover:bg-gray-50 transition-colors resident-row" data-user-id="<?php echo $resident['user_id']; ?>">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="checkbox" class="resident-checkbox w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                                                    data-user-id="<?php echo $resident['user_id']; ?>"
                                                    onchange="updateProgress()"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           <?php echo $isMarked ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center">
                                                            <span class="text-white text-xs font-semibold">
                                                                <?php echo strtoupper(substr($resident['first_name'], 0, 1) . substr($resident['last_name'], 0, 1)); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            <?php echo htmlspecialchars($resident['email'] ?: 'N/A'); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                    <?php echo htmlspecialchars($resident['cell_name'] ?: 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <select class="status-select text-sm px-3 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                    data-user-id="<?php echo $resident['user_id']; ?>" onchange="updateAttendanceRecord(this)">
                                                    <option value="">Select Status</option>
                                                    <option value="present"<?php echo($existingRecord && $existingRecord['status'] === 'present') ? ' selected' : ''; ?>>Present</option>
                                                    <option value="late"<?php echo($existingRecord && $existingRecord['status'] === 'late') ? ' selected' : ''; ?>>Late</option>
                                                    <option value="absent"<?php echo($existingRecord && $existingRecord['status'] === 'absent') ? ' selected' : ''; ?>>Absent</option>
                                                    <option value="excused"<?php echo($existingRecord && $existingRecord['status'] === 'excused') ? ' selected' : ''; ?>>Excused</option>
                                                </select>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="time" class="time-input text-sm px-3 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                    data-user-id="<?php echo $resident['user_id']; ?>"
                                                    value="<?php echo $existingRecord ? date('H:i', strtotime($existingRecord['check_in_time'])) : ''; ?>">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number" class="fine-input text-sm px-3 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 w-24"
                                                    data-user-id="<?php echo $resident['user_id']; ?>"
                                                    min="0" step="100" placeholder="0"
                                                    value="<?php echo $existingRecord ? ($existingRecord['fine_amount'] ?? '') : ''; ?>">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="space-y-1">
                                                    <input type="text" class="notes-input text-sm px-3 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 w-32"
                                                        data-user-id="<?php echo $resident['user_id']; ?>"
                                                        placeholder="Notes"
                                                        value="<?php echo $existingRecord ? htmlspecialchars($existingRecord['notes'] ?? '') : ''; ?>">
                                                    <input type="text" class="excuse-input text-sm px-3 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 w-32"
                                                        data-user-id="<?php echo $resident['user_id']; ?>"
                                                        placeholder="Excuse reason"
                                                        style="display: none;"
                                                        value="<?php echo $existingRecord ? htmlspecialchars($existingRecord['excuse_reason'] ?? '') : ''; ?>">
                                                </div>
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
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No residents found</h3>
                                <p class="text-gray-500">
                                    No residents match your current filters. Try adjusting your search criteria.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <?php if (! empty($residents)): ?>
                    <div class="mt-6 flex justify-center">
                        <div class="flex gap-4">
                            <button onclick="window.location.href='attendance-tracking.php'"
                                class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                Cancel
                            </button>
                            <button onclick="saveAllAttendance()"
                                class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                                <i class="fas fa-save mr-2"></i>
                                Save All Attendance Records
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Hidden form for submission -->
    <form id="attendanceForm" method="POST" class="hidden">
        <input type="hidden" name="attendance_data" id="attendanceData">
    </form>

    <!-- Custom CSS for QR Scanner Animations -->
    <style>
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }

        .animate-pulse {
            animation: pulse 1.5s ease-in-out infinite;
        }

        .qr-scan-success {
            animation: successFlash 0.5s ease-in-out;
        }

        @keyframes successFlash {
            0% { background-color: rgb(34, 197, 94); }
            100% { background-color: rgb(240, 253, 244); }
        }

        /* QR Reader styling */
        #qrReader {
            border: 2px dashed #d1d5db;
            transition: all 0.3s ease;
            position: relative;
            z-index: 10;
        }

        #qrReader.scanning {
            border-color: #10b981;
            background-color: #f0fdf4;
        }

        /* QR Scanner Section */
        #qrScannerSection {
            position: relative;
            z-index: 20;
        }

        /* Ensure main content doesn't overlap with sidebar */
        #main-content {
            position: relative;
            z-index: 1;
        }

        /* Recent scans scroll styling */
        #recentScans::-webkit-scrollbar {
            width: 4px;
        }

        #recentScans::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 2px;
        }

        #recentScans::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }

        #recentScans::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Keyboard shortcut styling */
        kbd {
            font-family: monospace;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>

    <!-- Scripts -->
        <!-- QR Code Scanner Library - Local Production Version -->
    <script src="/assets/js/html5-qrcode.min.js" type="text/javascript"></script>
    <script>
        let qrCodeScanner = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateProgress();

            // Add keyboard shortcuts
            document.addEventListener('keydown', function(event) {
                // Only if not typing in an input field
                if (event.target.tagName !== 'INPUT' && event.target.tagName !== 'TEXTAREA' && event.target.tagName !== 'SELECT') {
                    switch(event.key) {
                        case ' ': // Spacebar to toggle QR scanner
                            event.preventDefault();
                            toggleQRScanner();
                            break;
                        case 'Escape': // ESC to stop scanner
                            event.preventDefault();
                            if (qrCodeScanner) {
                                stopQRScanner();
                            }
                            break;
                        case 'q': // Q to open QR scanner
                        case 'Q':
                            event.preventDefault();
                            const qrSection = document.getElementById('qrScannerSection');
                            if (qrSection.classList.contains('hidden')) {
                                toggleQRScanner();
                            }
                            break;
                    }
                }
            });
        });

        // Filter functionality
        function applyFilters() {
            const eventFilter = document.getElementById('eventFilter').value;
            const cellFilter = document.getElementById('cellFilter').value;
            const searchInput = document.getElementById('searchInput').value;

            // Build URL parameters
            const params = new URLSearchParams();
            if (eventFilter) params.append('event_id', eventFilter);
            if (cellFilter) params.append('cell', cellFilter);
            if (searchInput) params.append('search', searchInput);

            // Reload page with filters
            window.location.href = 'attendance-marking.php?' + params.toString();
        }

        // Progress tracking
        function updateProgress() {
            const checkboxes = document.querySelectorAll('.resident-checkbox');
            const checked = document.querySelectorAll('.resident-checkbox:checked');
            const total = checkboxes.length;
            const marked = checked.length;

            const percentage = total > 0 ? (marked / total) * 100 : 0;

            document.getElementById('progressBar').style.width = percentage + '%';
            document.getElementById('progressText').textContent = `${marked} of ${total} marked`;
        }

        // Select all functionality
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAllCheckbox');
            const checkboxes = document.querySelectorAll('.resident-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
                if (selectAll.checked) {
                    // Auto-set status to present when selected
                    const userId = checkbox.dataset.userId;
                    const statusSelect = document.querySelector(`.status-select[data-user-id="${userId}"]`);
                    if (statusSelect && !statusSelect.value) {
                        statusSelect.value = 'present';
                        updateAttendanceRecord(statusSelect);
                    }
                }
            });

            updateProgress();
        }

        function selectAll() {
            document.getElementById('selectAllCheckbox').checked = true;
            toggleSelectAll();
        }

        function clearAll() {
            document.getElementById('selectAllCheckbox').checked = false;
            toggleSelectAll();
        }

        // Mark all present
        function markAllPresent() {
            if (confirm('Mark all residents as present?')) {
                const checkboxes = document.querySelectorAll('.resident-checkbox');
                const currentTime = new Date().toTimeString().slice(0, 5);

                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                    const userId = checkbox.dataset.userId;

                    // Set status to present
                    const statusSelect = document.querySelector(`.status-select[data-user-id="${userId}"]`);
                    if (statusSelect) {
                        statusSelect.value = 'present';
                    }

                    // Set current time
                    const timeInput = document.querySelector(`.time-input[data-user-id="${userId}"]`);
                    if (timeInput && !timeInput.value) {
                        timeInput.value = currentTime;
                    }
                });

                updateProgress();
            }
        }

        // Update attendance record when status changes
        function updateAttendanceRecord(selectElement) {
            const userId = selectElement.dataset.userId;
            const checkbox = document.querySelector(`.resident-checkbox[data-user-id="${userId}"]`);
            const timeInput = document.querySelector(`.time-input[data-user-id="${userId}"]`);
            const fineInput = document.querySelector(`.fine-input[data-user-id="${userId}"]`);
            const excuseInput = document.querySelector(`.excuse-input[data-user-id="${userId}"]`);

            // Auto-check the checkbox when status is selected
            if (selectElement.value) {
                checkbox.checked = true;

                // Set default time if not set
                if (!timeInput.value) {
                    timeInput.value = new Date().toTimeString().slice(0, 5);
                }

                // Show/hide excuse input based on status
                if (selectElement.value === 'excused') {
                    excuseInput.style.display = 'block';
                    fineInput.value = '0'; // No fine for excused
                } else {
                    excuseInput.style.display = 'none';
                    excuseInput.value = '';
                }

                // Set default fine based on status
                if (selectElement.value === 'late' && !fineInput.value) {
                    fineInput.value = '500'; // Default late fine
                } else if (selectElement.value === 'absent' && !fineInput.value) {
                    fineInput.value = '1000'; // Default absence fine
                } else if (selectElement.value === 'present' && !fineInput.value) {
                    fineInput.value = '0';
                }
            } else {
                checkbox.checked = false;
                excuseInput.style.display = 'none';
            }

            updateProgress();
        }

        // QR Scanner functionality
        function toggleQRScanner() {
            const qrSection = document.getElementById('qrScannerSection');
            qrSection.classList.toggle('hidden');
        }

        function startQRScanner() {
            const qrReader = document.getElementById('qrReader');
            const startBtn = document.getElementById('startScanBtn');
            const stopBtn = document.getElementById('stopScanBtn');

            // Check if event is selected first
            const eventFilter = document.getElementById('eventFilter');
            if (!eventFilter || !eventFilter.value) {
                showScanMessage('⚠️ Please select an Umuganda event before starting the scanner', 'error');
                return;
            }

            // Visual feedback
            startBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Starting...';
            startBtn.disabled = true;

            // Check for camera permissions and support
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showScanMessage('❌ Camera not supported on this device', 'error');
                startBtn.innerHTML = '<i class="fas fa-play mr-2"></i> Start Scanner';
                startBtn.disabled = false;
                return;
            }

            // Test camera permissions first
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    // Stop the test stream immediately
                    stream.getTracks().forEach(track => track.stop());

                    // Now start the actual QR scanner
                    try {
                        if (qrCodeScanner) {
                            qrCodeScanner.stop().catch(() => {});
                        }

                        qrCodeScanner = new Html5QrcodeScanner("qrReader", {
                            fps: 10,
                            qrbox: {width: 250, height: 250},
                            aspectRatio: 1.0,
                            disableFlip: false,
                            verbose: false,
                            showTorchButtonIfSupported: true,
                            showZoomSliderIfSupported: true
                        });

                        qrCodeScanner.render(onScanSuccess, onScanFailure);

                        // Update UI
                        qrReader.classList.add('scanning');
                        startBtn.innerHTML = '<i class="fas fa-check mr-2"></i> Scanner Active';
                        stopBtn.disabled = false;
                        stopBtn.innerHTML = '<i class="fas fa-stop mr-2"></i> Stop Scanner';

                        showScanMessage('📷 Scanner ready! Point camera at resident QR codes', 'success');

                    } catch (error) {
                        console.error('QR Scanner Error:', error);
                        showScanMessage('❌ Failed to initialize QR scanner: ' + error.message, 'error');

                        // Reset buttons
                        startBtn.innerHTML = '<i class="fas fa-play mr-2"></i> Start Scanner';
                        startBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Camera permission error:', error);

                    let errorMessage = '❌ Camera access denied. ';
                    if (error.name === 'NotAllowedError') {
                        errorMessage += 'Please allow camera access in your browser settings and try again.';
                    } else if (error.name === 'NotFoundError') {
                        errorMessage += 'No camera found on this device.';
                    } else if (error.name === 'NotReadableError') {
                        errorMessage += 'Camera is already in use by another application.';
                    } else {
                        errorMessage += 'Please check your camera and permissions.';
                    }

                    showScanMessage(errorMessage, 'error');

                    // Reset buttons
                    startBtn.innerHTML = '<i class="fas fa-play mr-2"></i> Start Scanner';
                    startBtn.disabled = false;
                });
        }

        function stopQRScanner() {
            const qrReader = document.getElementById('qrReader');
            const startBtn = document.getElementById('startScanBtn');
            const stopBtn = document.getElementById('stopScanBtn');

            stopBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Stopping...';
            stopBtn.disabled = true;

            if (qrCodeScanner) {
                qrCodeScanner.stop().then(() => {
                    qrCodeScanner.clear();

                    // Reset UI
                    qrReader.classList.remove('scanning');
                    qrReader.innerHTML = `
                        <div class="text-center">
                            <i class="fas fa-qrcode text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">Click "Start Scanner" to begin</p>
                            <p class="text-xs text-gray-500 mt-2">Ensure good lighting for best results</p>
                        </div>
                    `;

                    startBtn.innerHTML = '<i class="fas fa-play mr-2"></i> Start Scanner';
                    startBtn.disabled = false;
                    stopBtn.innerHTML = '<i class="fas fa-stop mr-2"></i> Stop Scanner';

                    showScanMessage('📷 Scanner stopped', 'info');
                }).catch(error => {
                    console.error('Error stopping scanner:', error);

                    // Force reset
                    startBtn.innerHTML = '<i class="fas fa-play mr-2"></i> Start Scanner';
                    startBtn.disabled = false;
                    stopBtn.innerHTML = '<i class="fas fa-stop mr-2"></i> Stop Scanner';
                    stopBtn.disabled = true;
                });
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            let userId, userName;

            try {
                // Try to parse as JSON first (new QR format)
                const qrData = JSON.parse(decodedText);

                if (qrData.type === 'umuganda_resident' && qrData.id) {
                    userId = qrData.id;
                    userName = qrData.name;

                    // Show scan feedback with resident info
                    showScanMessage(`📱 Scanned: ${userName} (ID: ${userId})`, 'info');
                } else {
                    showScanMessage('Invalid QR code: Not a resident QR code', 'error');
                    return;
                }
            } catch (e) {
                // Fallback: treat as plain user ID (old format)
                const plainUserId = decodedText.trim();

                if (!/^\d+$/.test(plainUserId)) {
                    showScanMessage('Invalid QR code format. Please scan a resident QR code.', 'error');
                    return;
                }

                userId = plainUserId;
                userName = null; // Will be fetched from API
            }

            // Check if we have a selected event
            const eventFilter = document.getElementById('eventFilter');
            const selectedEventId = eventFilter ? eventFilter.value : null;

            if (!selectedEventId) {
                showScanMessage('⚠️ Please select an Umuganda event first', 'error');
                return;
            }

            // Immediate UI feedback
            showScanMessage(`⏳ Marking attendance for ${userName || `User ${userId}`}...`, 'info');

            // Find and mark the resident in the UI
            const checkbox = document.querySelector(`.resident-checkbox[data-user-id="${userId}"]`);
            if (checkbox) {
                // Mark in UI first for immediate feedback
                checkbox.checked = true;
                const statusSelect = document.querySelector(`.status-select[data-user-id="${userId}"]`);
                if (statusSelect) {
                    statusSelect.value = 'present';
                    updateAttendanceRecord(statusSelect);
                }
            }

            // Save to database via API
            markAttendanceViaAPI(userId, selectedEventId, 'present', userName)
                .then(response => {
                    if (response.success) {
                        const finalUserName = response.data.user_name || userName || `User ${userId}`;
                        addToRecentScans(userId, finalUserName);
                        showScanMessage(`✅ ${finalUserName} marked present successfully!`, 'success');

                        // Update progress counter
                        updateProgress();

                        // Play success sound (optional)
                        playSuccessSound();
                    } else {
                        showScanMessage(`❌ Error: ${response.message}`, 'error');

                        // Uncheck if marking failed
                        if (checkbox) {
                            checkbox.checked = false;
                        }
                    }
                })
                .catch(error => {
                    console.error('API Error:', error);
                    showScanMessage('❌ Failed to save attendance. Please try again.', 'error');

                    // Uncheck if marking failed
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                });
        }

        // API function to mark attendance
        async function markAttendanceViaAPI(userId, eventId, status, userName = null) {
            const response = await fetch('../api/qr-attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: parseInt(userId),
                    event_id: parseInt(eventId),
                    status: status,
                    check_in_time: new Date().toTimeString().slice(0, 8),
                    notes: 'Marked via QR code scan',
                    user_name: userName // Pass along the user name if available
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        }

        // Show scan message with different types
        function showScanMessage(message, type) {
            const messageDiv = document.getElementById('scanMessage');
            if (messageDiv) {
                messageDiv.textContent = message;

                let className = 'mt-4 p-3 rounded-lg text-sm border ';
                switch(type) {
                    case 'success':
                        className += 'bg-green-100 text-green-800 border-green-200';
                        break;
                    case 'error':
                        className += 'bg-red-100 text-red-800 border-red-200';
                        break;
                    case 'info':
                        className += 'bg-blue-100 text-blue-800 border-blue-200';
                        break;
                    default:
                        className += 'bg-gray-100 text-gray-800 border-gray-200';
                }

                messageDiv.className = className;

                // Auto-hide after 5 seconds for success/info, 7 seconds for errors
                const hideDelay = type === 'error' ? 7000 : 5000;
                setTimeout(() => {
                    messageDiv.textContent = '';
                    messageDiv.className = 'hidden';
                }, hideDelay);
            }
        }

        // Play success sound for successful scans
        function playSuccessSound() {
            try {
                // Create a simple beep sound
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = 800; // High pitch beep
                oscillator.type = 'sine';

                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.2);
            } catch (e) {
                // Silently ignore if audio context is not supported
                console.log('Audio feedback not available');
            }
        }

        function onScanFailure(error) {
            // Handle scan failure silently
        }

        function addToRecentScans(userId, userName) {
            const recentScans = document.getElementById('recentScans');
            const scanCounter = document.getElementById('scanCounter');

            // Use provided userName or try to get from DOM
            let name = userName;
            if (!name) {
                const residentRow = document.querySelector(`.resident-row[data-user-id="${userId}"]`);
                if (residentRow) {
                    name = residentRow.querySelector('.text-sm.font-medium').textContent;
                } else {
                    name = `User ID: ${userId}`;
                }
            }

            const time = new Date().toLocaleTimeString();

            const scanItem = document.createElement('div');
            scanItem.className = 'flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg mb-2 animate-pulse';
            scanItem.innerHTML = `
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-check text-white text-xs"></i>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-green-900">${name}</div>
                        <div class="text-xs text-green-600">Scanned at ${time}</div>
                    </div>
                </div>
                <div class="text-green-600">
                    <i class="fas fa-qrcode"></i>
                </div>
            `;

            // Remove "No scans yet" message if it exists
            const noScansMessage = recentScans.querySelector('.text-center');
            if (noScansMessage) {
                recentScans.innerHTML = '';
            }

            recentScans.insertBefore(scanItem, recentScans.firstChild);

            // Keep only last 5 scans
            while (recentScans.children.length > 5) {
                recentScans.removeChild(recentScans.lastChild);
            }

            // Update scan counter
            const currentCount = recentScans.children.length;
            if (scanCounter) {
                scanCounter.textContent = `${currentCount} scanned`;
            }

            // Remove animation after 2 seconds
            setTimeout(() => {
                scanItem.classList.remove('animate-pulse');
                scanItem.classList.add('bg-green-100');
            }, 2000);
        }

        // Save all attendance
        function saveAllAttendance() {
            const attendanceData = [];
            const checkedBoxes = document.querySelectorAll('.resident-checkbox:checked');

            if (checkedBoxes.length === 0) {
                alert('Please mark at least one resident\'s attendance');
                return;
            }

            let hasErrors = false;

            checkedBoxes.forEach(checkbox => {
                const userId = checkbox.dataset.userId;
                const statusSelect = document.querySelector(`.status-select[data-user-id="${userId}"]`);
                const timeInput = document.querySelector(`.time-input[data-user-id="${userId}"]`);
                const fineInput = document.querySelector(`.fine-input[data-user-id="${userId}"]`);
                const notesInput = document.querySelector(`.notes-input[data-user-id="${userId}"]`);
                const excuseInput = document.querySelector(`.excuse-input[data-user-id="${userId}"]`);

                if (!statusSelect.value) {
                    alert('Please select status for all marked residents');
                    hasErrors = true;
                    return;
                }

                // Validate excuse reason for excused status
                if (statusSelect.value === 'excused' && !excuseInput.value.trim()) {
                    alert('Please provide excuse reason for excused residents');
                    hasErrors = true;
                    return;
                }

                attendanceData.push({
                    user_id: userId,
                    status: statusSelect.value,
                    check_in_time: timeInput.value || null,
                    fine_amount: fineInput.value || 0,
                    notes: notesInput.value || '',
                    excuse_reason: excuseInput.value || ''
                });
            });

            if (!hasErrors && attendanceData.length > 0) {
                if (confirm(`Save attendance for ${attendanceData.length} residents?`)) {
                    document.getElementById('attendanceData').value = JSON.stringify(attendanceData);
                    document.getElementById('attendanceForm').submit();
                }
            }
        }

        // Real-time search (if needed for client-side filtering)
        document.getElementById('searchInput').addEventListener('input', function(e) {
            // Optional: Add client-side search filtering here
        });

        // Keyboard shortcuts for QR scanner
        document.addEventListener('keydown', function(e) {
            // Space bar to toggle QR scanner
            if (e.code === 'Space' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                const qrSection = document.getElementById('qrScannerSection');
                if (qrSection.classList.contains('hidden')) {
                    toggleQRScanner();
                    setTimeout(() => startQRScanner(), 500);
                } else {
                    if (qrCodeScanner) {
                        stopQRScanner();
                    } else {
                        startQRScanner();
                    }
                }
            }

            // Escape to stop scanner
            if (e.code === 'Escape' && qrCodeScanner) {
                stopQRScanner();
            }
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Update initial progress
            updateProgress();

            // Show helpful tip if QR scanner is available
            const hasCamera = navigator.mediaDevices && navigator.mediaDevices.getUserMedia;
            if (hasCamera) {
                console.log('📱 QR Scanner ready! Press SPACE to quick-start scanning.');
            }

            // Auto-open QR scanner if event is pre-selected and no residents are marked yet
            const eventFilter = document.getElementById('eventFilter');
            const checkedBoxes = document.querySelectorAll('.resident-checkbox:checked');

            if (eventFilter && eventFilter.value && checkedBoxes.length === 0) {
                // Auto-show QR scanner after 2 seconds for better UX
                setTimeout(() => {
                    const qrSection = document.getElementById('qrScannerSection');
                    if (qrSection && qrSection.classList.contains('hidden')) {
                        showScanMessage('💡 Tip: Use the QR scanner for fast attendance marking!', 'info');
                    }
                }, 2000);
            }
        });
    </script>

<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>
