<?php
    /**
     * Admin Assignments Management
     * Allows superadmin to assign regular admins to sectors
     */

    session_start();

    // Check if user is logged in and is superadmin
    if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
        header('Location: ../../login.php');
        exit;
    }

    // Include required files
    require_once __DIR__ . '/../../../config/db.php';

    // Use the global database instance
    global $db;
    $connection = $db->getConnection();

    // Handle assignment actions
    $message     = '';
    $messageType = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'assign':
                    $adminId  = (int) $_POST['admin_id'];
                    $sectorId = (int) $_POST['sector_id'];

                    // Check if assignment already exists
                    $checkQuery = "SELECT id FROM admin_assignments WHERE admin_id = ? AND sector_id = ? AND is_active = 1";
                    $stmt       = $connection->prepare($checkQuery);
                    $stmt->bind_param('ii', $adminId, $sectorId);
                    $stmt->execute();

                    if ($stmt->get_result()->num_rows > 0) {
                        $message     = 'Admin is already assigned to this sector.';
                        $messageType = 'warning';
                    } else {
                        // Deactivate any existing assignments for this admin
                        $deactivateQuery = "UPDATE admin_assignments SET is_active = 0 WHERE admin_id = ?";
                        $stmt            = $connection->prepare($deactivateQuery);
                        $stmt->bind_param('i', $adminId);
                        $stmt->execute();

                        // Create new assignment
                        $assignQuery = "INSERT INTO admin_assignments (admin_id, sector_id, assigned_by, assigned_at) VALUES (?, ?, ?, NOW())";
                        $stmt        = $connection->prepare($assignQuery);
                        $stmt->bind_param('iii', $adminId, $sectorId, $_SESSION['user_id']);

                        if ($stmt->execute()) {
                            $message     = 'Admin successfully assigned to sector.';
                            $messageType = 'success';
                        } else {
                            $message     = 'Failed to assign admin to sector.';
                            $messageType = 'error';
                        }
                    }
                    break;

                case 'unassign':
                    $assignmentId = (int) $_POST['assignment_id'];

                    $unassignQuery = "UPDATE admin_assignments SET is_active = 0 WHERE id = ?";
                    $stmt          = $connection->prepare($unassignQuery);
                    $stmt->bind_param('i', $assignmentId);

                    if ($stmt->execute()) {
                        $message     = 'Admin assignment removed successfully.';
                        $messageType = 'success';
                    } else {
                        $message     = 'Failed to remove admin assignment.';
                        $messageType = 'error';
                    }
                    break;
            }
        }
    }

    // Get all admins (not superadmins)
    $adminsQuery  = "SELECT id, first_name, last_name, email, phone FROM users WHERE role = 'admin' AND status = 'active' ORDER BY first_name, last_name";
    $adminsResult = $connection->query($adminsQuery);
    $admins       = [];
    while ($admin = $adminsResult->fetch_assoc()) {
        $admins[] = $admin;
    }

    // Get all sectors
    $sectorsQuery  = "SELECT s.id, s.name, d.name as district_name FROM sectors s JOIN districts d ON s.district_id = d.id ORDER BY d.name, s.name";
    $sectorsResult = $connection->query($sectorsQuery);
    $sectors       = [];
    while ($sector = $sectorsResult->fetch_assoc()) {
        $sectors[] = $sector;
    }

    // Get current assignments
    $assignmentsQuery = "
    SELECT
        aa.id as assignment_id,
        u.id as admin_id,
        u.first_name,
        u.last_name,
        u.email,
        s.name as sector_name,
        d.name as district_name,
        aa.assigned_at,
        assignedBy.first_name as assigned_by_name
    FROM admin_assignments aa
    JOIN users u ON aa.admin_id = u.id
    JOIN sectors s ON aa.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    JOIN users assignedBy ON aa.assigned_by = assignedBy.id
    WHERE aa.is_active = 1
    ORDER BY d.name, s.name, u.first_name";
    $assignmentsResult = $connection->query($assignmentsQuery);
    $assignments       = [];
    while ($assignment = $assignmentsResult->fetch_assoc()) {
        $assignments[] = $assignment;
    }

    // Get unassigned admins
    $unassignedAdminsQuery = "
    SELECT u.id, u.first_name, u.last_name, u.email
    FROM users u
    LEFT JOIN admin_assignments aa ON u.id = aa.admin_id AND aa.is_active = 1
    WHERE u.role = 'admin' AND u.status = 'active' AND aa.id IS NULL
    ORDER BY u.first_name, u.last_name";
    $unassignedResult = $connection->query($unassignedAdminsQuery);
    $unassignedAdmins = [];
    while ($admin = $unassignedResult->fetch_assoc()) {
        $unassignedAdmins[] = $admin;
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

        <!-- Dashboard Content -->
        <main class="p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Title -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 ml-4 lg:ml-0">Admin Assignments Management</h1>
                    <p class="text-sm text-gray-600 ml-4 lg:ml-0 mt-1">Assign admins to manage specific sectors</p>
                </div>

                <!-- Success/Error Messages -->
                <?php if ($message): ?>
                <div class="mb-6 ml-4 lg:ml-0">
                    <div class="<?php echo $messageType === 'success' ? 'bg-green-50 border-green-200 text-green-800' : ($messageType === 'warning' ? 'bg-yellow-50 border-yellow-200 text-yellow-800' : 'bg-red-50 border-red-200 text-red-800'); ?> border rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas                                              <?php echo $messageType === 'success' ? 'fa-check-circle' : ($messageType === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle'); ?> text-sm"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium"><?php echo htmlspecialchars($message); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Current Assignments -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Current Assignments</h3>
                            <p class="text-sm text-gray-600 mt-1"><?php echo count($assignments); ?> active assignments</p>
                        </div>
                        <div class="p-6">
                            <?php if (empty($assignments)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-users-slash text-gray-300 text-4xl mb-4"></i>
                                <p class="text-gray-500">No admin assignments found</p>
                                <p class="text-sm text-gray-400 mt-1">Start by assigning admins to sectors below</p>
                            </div>
                            <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($assignments as $assignment): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-10 h-10 bg-primary-500 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm font-semibold">
                                                <?php echo strtoupper(substr($assignment['first_name'], 0, 1) . substr($assignment['last_name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($assignment['first_name'] . ' ' . $assignment['last_name']); ?></h4>
                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($assignment['email']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($assignment['sector_name'] . ', ' . $assignment['district_name']); ?></p>
                                        </div>
                                    </div>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="unassign">
                                        <input type="hidden" name="assignment_id" value="<?php echo $assignment['assignment_id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium"
                                                onclick="return confirm('Are you sure you want to remove this assignment?')">
                                            <i class="fas fa-times mr-1"></i>Remove
                                        </button>
                                    </form>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Assign New Admin -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Assign Admin to Sector</h3>
                            <p class="text-sm text-gray-600 mt-1">Select an admin and assign them to manage a sector</p>
                        </div>
                        <div class="p-6">
                            <?php if (empty($unassignedAdmins)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-user-check text-gray-300 text-4xl mb-4"></i>
                                <p class="text-gray-500">All admins are currently assigned</p>
                                <p class="text-sm text-gray-400 mt-1">You can reassign existing admins to different sectors</p>
                            </div>
                            <?php else: ?>
                            <form method="POST" class="space-y-6">
                                <input type="hidden" name="action" value="assign">

                                <!-- Select Admin -->
                                <div>
                                    <label for="admin_id" class="block text-sm font-medium text-gray-700 mb-2">Select Admin</label>
                                    <select name="admin_id" id="admin_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                                        <option value="">Choose an admin...</option>
                                        <?php foreach ($unassignedAdmins as $admin): ?>
                                        <option value="<?php echo $admin['id']; ?>">
                                            <?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name'] . ' (' . $admin['email'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Select Sector -->
                                <div>
                                    <label for="sector_id" class="block text-sm font-medium text-gray-700 mb-2">Select Sector</label>
                                    <select name="sector_id" id="sector_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                                        <option value="">Choose a sector...</option>
                                        <?php foreach ($sectors as $sector): ?>
                                        <option value="<?php echo $sector['id']; ?>">
                                            <?php echo htmlspecialchars($sector['name'] . ' (' . $sector['district_name'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Submit Button -->
                                <div>
                                    <button type="submit" class="w-full bg-primary-600 text-white py-2 px-4 rounded-md hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                                        <i class="fas fa-user-plus mr-2"></i>Assign Admin
                                    </button>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo count($admins); ?></h3>
                                <p class="text-sm text-gray-600">Total Admins</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-user-check text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo count($assignments); ?></h3>
                                <p class="text-sm text-gray-600">Assigned Admins</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-user-times text-orange-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo count($unassignedAdmins); ?></h3>
                                <p class="text-sm text-gray-600">Unassigned Admins</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>
