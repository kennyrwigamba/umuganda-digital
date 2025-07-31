<?php
    /**
     * Manage Residents - Admin Dashboard
     * Dynamic resident management for assigned sector
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

    // Get residents statistics for admin's sector
    try {
        // Get total residents
        $totalResidentsQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'resident' AND sector_id = ?";
        $stmt                = $connection->prepare($totalResidentsQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $totalResidents = $stmt->get_result()->fetch_assoc()['count'];

        // Get active residents
        $activeResidentsQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'resident' AND sector_id = ? AND status = 'active'";
        $stmt                 = $connection->prepare($activeResidentsQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $activeResidents = $stmt->get_result()->fetch_assoc()['count'];

        // Get pending residents
        $pendingResidentsQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'resident' AND sector_id = ? AND status = 'pending'";
        $stmt                  = $connection->prepare($pendingResidentsQuery);
        $stmt->bind_param('i', $adminSectorId);
        $stmt->execute();
        $pendingResidents = $stmt->get_result()->fetch_assoc()['count'];

        // Get inactive residents
        $inactiveResidents = $totalResidents - $activeResidents - $pendingResidents;

        // Calculate active rate
        $activeRate = $totalResidents > 0 ? round(($activeResidents / $totalResidents) * 100, 1) : 0;

    } catch (Exception $e) {
        // Default values if queries fail
        $totalResidents    = 0;
        $activeResidents   = 0;
        $pendingResidents  = 0;
        $inactiveResidents = 0;
        $activeRate        = 0;
    }

    // Get cells in admin's sector for dropdowns
    $cellsQuery = "SELECT id, name FROM cells WHERE sector_id = ? ORDER BY name";
    $stmt       = $connection->prepare($cellsQuery);
    $stmt->bind_param('i', $adminSectorId);
    $stmt->execute();
    $cellsResult = $stmt->get_result();
    $cells       = [];
    while ($cell = $cellsResult->fetch_assoc()) {
        $cells[] = $cell;
    }

    // Handle pagination
    $page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit  = 10;
    $offset = ($page - 1) * $limit;

    // Handle search and filters
    $searchTerm   = isset($_GET['search']) ? $_GET['search'] : '';
    $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
    $cellFilter   = isset($_GET['cell']) ? $_GET['cell'] : '';

    // Build WHERE clause for filters
    $whereConditions = ["u.role = 'resident'", "u.sector_id = ?"];
    $params          = [$adminSectorId];
    $paramTypes      = 'i';

    if (! empty($searchTerm)) {
        $whereConditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.national_id LIKE ?)";
        $searchPattern     = "%$searchTerm%";
        $params            = array_merge($params, [$searchPattern, $searchPattern, $searchPattern, $searchPattern]);
        $paramTypes .= 'ssss';
    }

    if (! empty($statusFilter)) {
        $whereConditions[] = "u.status = ?";
        $params[]          = $statusFilter;
        $paramTypes .= 's';
    }

    if (! empty($cellFilter)) {
        $whereConditions[] = "u.cell_id = ?";
        $params[]          = $cellFilter;
        $paramTypes .= 'i';
    }

    $whereClause = implode(' AND ', $whereConditions);

    // Get residents for current page
    $residentsQuery = "
    SELECT
        u.id,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        u.national_id,
        u.status,
        u.created_at,
        COALESCE(c.name, 'No Cell') as cell_name,
        c.id as cell_id
    FROM users u
    LEFT JOIN cells c ON u.cell_id = c.id
    WHERE $whereClause
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?";

    $stmt     = $connection->prepare($residentsQuery);
    $params[] = $limit;
    $params[] = $offset;
    $paramTypes .= 'ii';
    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $residentsResult = $stmt->get_result();

    $residents = [];
    while ($resident = $residentsResult->fetch_assoc()) {
        $residents[] = $resident;
    }

    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM users u LEFT JOIN cells c ON u.cell_id = c.id WHERE $whereClause";
    $countStmt  = $connection->prepare($countQuery);
    // Remove limit and offset params for count
    $countParams     = array_slice($params, 0, -2);
    $countParamTypes = substr($paramTypes, 0, -2);
    $countStmt->bind_param($countParamTypes, ...$countParams);
    $countStmt->execute();
    $totalCount = $countStmt->get_result()->fetch_assoc()['total'];

    $totalPages = ceil($totalCount / $limit);
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
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Manage Residents</h1>
                            <p class="mt-2 text-sm text-gray-600">Add, edit, and manage residents in                                                                                                                                                                                                         <?php echo htmlspecialchars($adminSector); ?> Sector</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <button id="addResidentBtn"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                                <i class="fas fa-plus mr-2"></i>
                                Add New Resident
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Overview -->
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
                                    <span class="inline-flex items-center text-sm text-blue-600 font-semibold bg-blue-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-users text-xs mr-1"></i>
                                        <?php echo htmlspecialchars($adminSector); ?>
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">sector</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-users text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Active Residents -->
                    <div
                        class="bg-gradient-to-br from-white to-green-50 rounded-xl shadow-sm p-6 border border-green-100 hover:shadow-lg hover:border-green-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-green-600 uppercase tracking-wide">Active Residents
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo number_format($activeResidents); ?></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-success-600 font-semibold bg-success-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-check-circle text-xs mr-1"></i>
                                        <?php echo $activeRate; ?>%
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">active rate</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-success-500 to-success-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-user-check text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Approvals -->
                    <div
                        class="bg-gradient-to-br from-white to-orange-50 rounded-xl shadow-sm p-6 border border-orange-100 hover:shadow-lg hover:border-orange-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Pending
                                    Approvals</p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo number_format($pendingResidents); ?></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-warning-600 font-semibold bg-warning-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-clock text-xs mr-1"></i>
                                        Pending
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">review required</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-user-clock text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Inactive Residents -->
                    <div
                        class="bg-gradient-to-br from-white to-red-50 rounded-xl shadow-sm p-6 border border-red-100 hover:shadow-lg hover:border-red-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-red-600 uppercase tracking-wide">Inactive Residents
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo number_format($inactiveResidents); ?></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-danger-600 font-semibold bg-danger-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-user-times text-xs mr-1"></i>
                                        <?php echo $totalResidents > 0 ? round(($inactiveResidents / $totalResidents) * 100, 1) : 0; ?>%
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">of total</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-danger-500 to-danger-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-user-slash text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Actions -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-8">
                    <form method="GET" class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                            <!-- Search -->
                            <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>"
                                placeholder="Search residents..."
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">

                            <!-- Status Filter -->
                            <select name="status"
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">All Status</option>
                                <option value="active"                                                                                                             <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive"                                                                                                                 <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="pending"                                                                                                               <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            </select>

                            <!-- Cell Filter -->
                            <select name="cell"
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">All Cells</option>
                                <?php foreach ($cells as $cell): ?>
                                <option value="<?php echo $cell['id']; ?>"<?php echo $cellFilter == $cell['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cell['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="flex space-x-2">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-primary-100 hover:bg-primary-200 text-primary-700 font-medium rounded-lg transition-colors">
                                <i class="fas fa-search mr-2"></i>
                                Search
                            </button>
                            <a href="?"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                                <i class="fas fa-times mr-2"></i>
                                Clear
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Residents Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Residents Directory</h3>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500">
                                    Showing                                                                                       <?php echo min(($page - 1) * $limit + 1, $totalCount); ?>-<?php echo min($page * $limit, $totalCount); ?> of<?php echo number_format($totalCount); ?>
                                </span>
                                <div class="flex space-x-1">
                                    <button class="p-1 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button class="p-1 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
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
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Resident</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contact</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cell</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Registration Date</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($residents)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">No residents found</h3>
                                            <p class="text-gray-500">
                                                <?php if (! empty($searchTerm) || ! empty($statusFilter) || ! empty($cellFilter)): ?>
                                                    Try adjusting your search or filter criteria.
                                                <?php else: ?>
                                                    Get started by adding your first resident.
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
<?php
    $colors     = ['primary', 'warning', 'success', 'danger', 'purple', 'indigo'];
    $colorIndex = 0;
    foreach ($residents as $resident):
        $initials = strtoupper(substr($resident['first_name'], 0, 1) . substr($resident['last_name'], 0, 1));
        $color    = $colors[$colorIndex % count($colors)];
        $colorIndex++;

        $statusColor = match ($resident['status']) {
            'active' => 'success',
            'pending' => 'warning',
            'inactive' => 'danger',
            default => 'gray'
        };

        $statusText    = ucfirst($resident['status']);
        $formattedDate = date('M d, Y', strtotime($resident['created_at']));
    ?>
		                                <tr class="hover:bg-gray-50 transition-colors">
		                                    <td class="px-6 py-4 whitespace-nowrap">
		                                        <input type="checkbox"
		                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
		                                    </td>
		                                    <td class="px-6 py-4 whitespace-nowrap">
		                                        <div class="flex items-center">
		                                            <div
		                                                class="w-10 h-10 bg-gradient-to-br from-<?php echo $color; ?>-500 to-<?php echo $color; ?>-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
		                                                <span class="text-white text-sm font-semibold"><?php echo $initials; ?></span>
		                                            </div>
		                                            <div>
		                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?></div>
		                                                <div class="text-sm text-gray-500">ID:		                                                                                      	                                                                                       <?php echo htmlspecialchars($resident['national_id']); ?></div>
		                                            </div>
		                                        </div>
		                                    </td>
		                                    <td class="px-6 py-4 whitespace-nowrap">
		                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($resident['email']); ?></div>
		                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($resident['phone']); ?></div>
		                                    </td>
		                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($resident['cell_name']); ?></td>
		                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $formattedDate; ?></td>
		                                    <td class="px-6 py-4 whitespace-nowrap">
		                                        <span
		                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-<?php echo $statusColor; ?>-100 text-<?php echo $statusColor; ?>-800"><?php echo $statusText; ?></span>
		                                    </td>
		                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
		                                        <div class="flex space-x-2">
		                                            <button
		                                                class="text-primary-600 hover:text-primary-900 transition-colors edit-btn"
		                                                data-id="<?php echo $resident['id']; ?>"
		                                                data-name="<?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?>"
		                                                data-email="<?php echo htmlspecialchars($resident['email']); ?>"
		                                                data-phone="<?php echo htmlspecialchars($resident['phone']); ?>"
		                                                data-cell="<?php echo htmlspecialchars($resident['cell_name']); ?>"
		                                                data-cell-id="<?php echo $resident['cell_id']; ?>"
		                                                data-status="<?php echo htmlspecialchars($resident['status']); ?>">
		                                                <i class="fas fa-edit"></i>
		                                            </button>
		                                            <button
		                                                class="text-success-600 hover:text-success-900 transition-colors view-btn"
		                                                data-id="<?php echo $resident['id']; ?>"
		                                                data-name="<?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?>"
		                                                data-email="<?php echo htmlspecialchars($resident['email']); ?>"
		                                                data-phone="<?php echo htmlspecialchars($resident['phone']); ?>"
		                                                data-cell="<?php echo htmlspecialchars($resident['cell_name']); ?>"
		                                                data-status="<?php echo htmlspecialchars($resident['status']); ?>"
		                                                data-date="<?php echo $formattedDate; ?>"
		                                                data-national-id="<?php echo htmlspecialchars($resident['national_id']); ?>">
		                                                <i class="fas fa-eye"></i>
		                                            </button>
		                                            <button
		                                                class="text-danger-600 hover:text-danger-900 transition-colors delete-btn"
		                                                data-id="<?php echo $resident['id']; ?>"
		                                                data-name="<?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?>">
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

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                Showing <span class="font-medium"><?php echo min(($page - 1) * $limit + 1, $totalCount); ?></span> to
                                <span class="font-medium"><?php echo min($page * $limit, $totalCount); ?></span> of
                                <span class="font-medium"><?php echo number_format($totalCount); ?></span> results
                            </div>
                            <?php if ($totalPages > 1): ?>
                            <div class="flex space-x-2">
                                <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo ! empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo ! empty($statusFilter) ? '&status=' . urlencode($statusFilter) : ''; ?><?php echo ! empty($cellFilter) ? '&cell=' . urlencode($cellFilter) : ''; ?>"
                                    class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-100 transition-colors">Previous</a>
                                <?php endif; ?>

                                <?php
                                    $startPage = max(1, $page - 2);
                                    $endPage   = min($totalPages, $page + 2);

                                    for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                <a href="?page=<?php echo $i; ?><?php echo ! empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo ! empty($statusFilter) ? '&status=' . urlencode($statusFilter) : ''; ?><?php echo ! empty($cellFilter) ? '&cell=' . urlencode($cellFilter) : ''; ?>"
                                    class="px-3 py-1 text-sm                                                                                                                         <?php echo $i === $page ? 'bg-primary-600 text-white' : 'border border-gray-300 hover:bg-gray-100'; ?> rounded-md transition-colors"><?php echo $i; ?></a>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo ! empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo ! empty($statusFilter) ? '&status=' . urlencode($statusFilter) : ''; ?><?php echo ! empty($cellFilter) ? '&cell=' . urlencode($cellFilter) : ''; ?>"
                                    class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-100 transition-colors">Next</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add New Resident Modal -->
    <div id="addResidentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-primary-100">
                        <i class="fas fa-user-plus text-primary-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Add New Resident</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Fill in the details to register a new community resident.
                            </p>
                        </div>
                    </div>
                </div>
                <form class="mt-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="addName" placeholder="Enter first and last name"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="addEmail" placeholder="resident@example.com"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="addPhone" placeholder="+250 7xx xxx xxx"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cell</label>
                        <select id="addCell"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Cell</option>
                            <?php foreach ($cells as $cell): ?>
                            <option value="<?php echo $cell['id']; ?>"><?php echo htmlspecialchars($cell['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">National ID</label>
                        <input type="text" id="addNationalId" placeholder="16-digit national ID number"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </form>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="button" id="confirmAddResident"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:col-start-2 sm:text-sm">
                        Add Resident
                    </button>
                    <button type="button" id="cancelAddResident"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Resident Modal -->
    <div id="editResidentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-warning-100">
                        <i class="fas fa-user-edit text-warning-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Resident</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Update resident information.</p>
                        </div>
                    </div>
                </div>
                <form class="mt-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="editName" placeholder="Enter first and last name"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="editEmail" placeholder="resident@example.com"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="editPhone" placeholder="+250 7xx xxx xxx"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cell</label>
                        <select id="editCell"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            <?php foreach ($cells as $cell): ?>
                            <option value="<?php echo $cell['id']; ?>"><?php echo htmlspecialchars($cell['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="editStatus"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="button" id="confirmEditResident"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-warning-600 text-base font-medium text-white hover:bg-warning-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning-500 sm:col-start-2 sm:text-sm">
                        Update Resident
                    </button>
                    <button type="button" id="cancelEditResident"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Resident Modal -->
    <div id="viewResidentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-success-100">
                        <i class="fas fa-user text-success-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Resident Details</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Complete information about the resident.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Full Name:</span>
                            <span class="text-sm text-gray-900" id="viewName">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Resident ID:</span>
                            <span class="text-sm text-gray-900" id="viewId">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Email:</span>
                            <span class="text-sm text-gray-900" id="viewEmail">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Phone:</span>
                            <span class="text-sm text-gray-900" id="viewPhone">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Cell:</span>
                            <span class="text-sm text-gray-900" id="viewCell">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Registration Date:</span>
                            <span class="text-sm text-gray-900" id="viewDate">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-500">Status:</span>
                            <span class="text-sm" id="viewStatus">-</span>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6">
                    <button type="button" id="closeViewResident"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Resident Modal -->
    <div id="deleteResidentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-danger-100">
                        <i class="fas fa-exclamation-triangle text-danger-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Resident</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to delete <span
                                    id="deleteResidentName" class="font-medium text-gray-900"></span>? This action
                                cannot be undone.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="button" id="confirmDeleteResident"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-danger-600 text-base font-medium text-white hover:bg-danger-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-500 sm:col-start-2 sm:text-sm">
                        Delete
                    </button>
                    <button type="button" id="cancelDeleteResident"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            initializeModals();
        });

        // Modal functionality
        function initializeModals() {
            const addResidentBtn = document.getElementById('addResidentBtn');
            const addResidentModal = document.getElementById('addResidentModal');
            const editResidentModal = document.getElementById('editResidentModal');
            const viewResidentModal = document.getElementById('viewResidentModal');
            const deleteResidentModal = document.getElementById('deleteResidentModal');

            // Add New Resident Modal
            addResidentBtn.addEventListener('click', function () {
                addResidentModal.classList.remove('hidden');
            });

            document.getElementById('cancelAddResident').addEventListener('click', function () {
                addResidentModal.classList.add('hidden');
                clearAddForm();
            });

            document.getElementById('confirmAddResident').addEventListener('click', function () {
                addNewResident();
            });

            // Edit buttons
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const data = this.dataset;
                    const [firstName, lastName] = data.name.split(' ', 2);
                    document.getElementById('editName').value = firstName + ' ' + (lastName || '');
                    document.getElementById('editEmail').value = data.email;
                    document.getElementById('editPhone').value = data.phone;
                    document.getElementById('editCell').value = data.cellId; // Use cell ID
                    document.getElementById('editStatus').value = data.status;
                    editResidentModal.dataset.residentId = data.id; // Store resident ID
                    editResidentModal.classList.remove('hidden');
                });
            });

            document.getElementById('cancelEditResident').addEventListener('click', function () {
                editResidentModal.classList.add('hidden');
            });

            document.getElementById('confirmEditResident').addEventListener('click', function () {
                updateResident();
            });

            // View buttons
            document.querySelectorAll('.view-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const data = this.dataset;
                    document.getElementById('viewName').textContent = data.name;
                    document.getElementById('viewId').textContent = data.nationalId; // Use national ID
                    document.getElementById('viewEmail').textContent = data.email;
                    document.getElementById('viewPhone').textContent = data.phone;
                    document.getElementById('viewCell').textContent = data.cell;
                    document.getElementById('viewDate').textContent = data.date;

                    const statusElement = document.getElementById('viewStatus');
                    statusElement.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                    statusElement.className = 'text-sm inline-flex px-3 py-1 text-xs font-semibold rounded-full ' +
                        (data.status === 'active' ? 'bg-success-100 text-success-800' :
                            data.status === 'pending' ? 'bg-warning-100 text-warning-800' :
                                'bg-danger-100 text-danger-800');

                    viewResidentModal.classList.remove('hidden');
                });
            });

            document.getElementById('closeViewResident').addEventListener('click', function () {
                viewResidentModal.classList.add('hidden');
            });

            // Delete buttons
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const data = this.dataset;
                    document.getElementById('deleteResidentName').textContent = data.name;
                    deleteResidentModal.dataset.residentId = data.id;
                    deleteResidentModal.classList.remove('hidden');
                });
            });

            document.getElementById('cancelDeleteResident').addEventListener('click', function () {
                deleteResidentModal.classList.add('hidden');
            });

            document.getElementById('confirmDeleteResident').addEventListener('click', function () {
                deleteResident();
            });

            // Close modals when clicking outside
            [addResidentModal, editResidentModal, viewResidentModal, deleteResidentModal].forEach(modal => {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                        if (modal === addResidentModal) clearAddForm();
                    }
                });
            });
        }

        function clearAddForm() {
            document.getElementById('addName').value = '';
            document.getElementById('addEmail').value = '';
            document.getElementById('addPhone').value = '';
            document.getElementById('addCell').value = '';
            document.getElementById('addNationalId').value = '';
        }

        // AJAX Functions for CRUD operations
        async function addNewResident() {
            const form = {
                first_name: document.getElementById('addName').value.split(' ')[0],
                last_name: document.getElementById('addName').value.split(' ').slice(1).join(' '),
                email: document.getElementById('addEmail').value,
                phone: document.getElementById('addPhone').value,
                cell_id: document.getElementById('addCell').value,
                national_id: document.getElementById('addNationalId').value
            };

            // Basic validation
            if (!form.first_name || !form.last_name || !form.email || !form.phone || !form.cell_id || !form.national_id) {
                showNotification('Please fill in all required fields', 'error');
                return;
            }

            try {
                showLoadingState('confirmAddResident', true);

                const response = await fetch('/public/api/residents.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(form)
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Resident added successfully!', 'success');
                    document.getElementById('addResidentModal').classList.add('hidden');
                    clearAddForm();
                    // Reload page to show new resident
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(result.message || 'Failed to add resident', 'error');
                }
            } catch (error) {
                showNotification('Network error. Please try again.', 'error');
                console.error('Error:', error);
            } finally {
                showLoadingState('confirmAddResident', false);
            }
        }

        async function updateResident() {
            const modal = document.getElementById('editResidentModal');
            const residentId = modal.dataset.residentId;

            const form = {
                id: residentId,
                first_name: document.getElementById('editName').value.split(' ')[0],
                last_name: document.getElementById('editName').value.split(' ').slice(1).join(' '),
                email: document.getElementById('editEmail').value,
                phone: document.getElementById('editPhone').value,
                cell_id: document.getElementById('editCell').value,
                status: document.getElementById('editStatus').value
            };

            // Basic validation
            if (!form.first_name || !form.last_name || !form.email || !form.phone || !form.cell_id) {
                showNotification('Please fill in all required fields', 'error');
                return;
            }

            try {
                showLoadingState('confirmEditResident', true);

                const response = await fetch('/public/api/residents.php?action=edit', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(form)
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Resident updated successfully!', 'success');
                    modal.classList.add('hidden');
                    // Reload page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(result.message || 'Failed to update resident', 'error');
                }
            } catch (error) {
                showNotification('Network error. Please try again.', 'error');
                console.error('Error:', error);
            } finally {
                showLoadingState('confirmEditResident', false);
            }
        }

        async function deleteResident() {
            const modal = document.getElementById('deleteResidentModal');
            const residentId = modal.dataset.residentId;

            try {
                showLoadingState('confirmDeleteResident', true);

                const response = await fetch('/public/api/residents.php?action=delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: residentId })
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Resident deleted successfully!', 'success');
                    modal.classList.add('hidden');
                    // Reload page to reflect deletion
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(result.message || 'Failed to delete resident', 'error');
                }
            } catch (error) {
                showNotification('Network error. Please try again.', 'error');
                console.error('Error:', error);
            } finally {
                showLoadingState('confirmDeleteResident', false);
            }
        }

        // Helper functions
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;

            const bgColor = type === 'success' ? 'bg-green-500' :
                           type === 'error' ? 'bg-red-500' :
                           type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';

            notification.className += ` ${bgColor} text-white`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <span class="flex-1">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }, 5000);
        }

        function showLoadingState(buttonId, loading) {
            const button = document.getElementById(buttonId);
            if (loading) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            } else {
                button.disabled = false;
                // Restore original text based on button
                const originalTexts = {
                    'confirmAddResident': 'Add Resident',
                    'confirmEditResident': 'Update Resident',
                    'confirmDeleteResident': 'Delete Resident'
                };
                button.innerHTML = originalTexts[buttonId] || 'Submit';
            }
        }
    </script>


<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>