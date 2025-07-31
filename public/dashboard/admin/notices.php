<?php
    /**
     * Admin Notices & Announcements Dashboard
     * Dynamic notices management for sector-specific communications
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
    require_once __DIR__ . '/../../../src/models/Notice.php';

    // Get database connection
    global $db;
    $connection = $db->getConnection();

    $user   = new User();
    $notice = new Notice();

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

    // Get filter parameters
    $type   = isset($_GET['type']) ? $_GET['type'] : 'all';
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';

    try {
        // 1. NOTICE STATISTICS
        $statsQuery = "
            SELECT
                COUNT(*) as total_notices,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as active_notices,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_notices,
                SUM(CASE WHEN priority = 'high' OR priority = 'critical' THEN 1 ELSE 0 END) as urgent_notices,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as notices_this_month
            FROM notices
            WHERE (sector_id = ? OR sector_id IS NULL) AND created_by = ?";

        $stmt = $connection->prepare($statsQuery);
        $stmt->bind_param('ii', $sectorId, $adminId);
        $stmt->execute();
        $noticeStats = $stmt->get_result()->fetch_assoc();

        // Extract notice statistics
        $totalNotices     = $noticeStats['total_notices'] ?? 0;
        $activeNotices    = $noticeStats['active_notices'] ?? 0;
        $draftNotices     = $noticeStats['draft_notices'] ?? 0;
        $urgentNotices    = $noticeStats['urgent_notices'] ?? 0;
        $noticesThisMonth = $noticeStats['notices_this_month'] ?? 0;

        // 2. GET NOTICES FOR THIS SECTOR WITH VIEW COUNTS
        $noticesQuery = "
            SELECT n.*, u.first_name, u.last_name, u.role as creator_role,
                   COALESCE(view_counts.total_views, 0) as view_count,
                   COALESCE(view_counts.unique_views, 0) as unique_views,
                   CASE
                       WHEN n.expiry_date < NOW() THEN 'expired'
                       WHEN n.status = 'published' AND n.publish_date <= NOW() THEN 'active'
                       WHEN n.status = 'published' AND n.publish_date > NOW() THEN 'scheduled'
                       ELSE n.status
                   END as effective_status
            FROM notices n
            LEFT JOIN users u ON n.created_by = u.id
            LEFT JOIN (
                SELECT
                    notice_id,
                    COUNT(*) as total_views,
                    COUNT(DISTINCT user_id) as unique_views
                FROM notice_reads
                GROUP BY notice_id
            ) view_counts ON n.id = view_counts.notice_id
            WHERE (n.sector_id = ? OR n.sector_id IS NULL) AND n.created_by = ?";

        // Add filters
        if ($type !== 'all') {
            $noticesQuery .= " AND n.type = '$type'";
        }
        if ($status !== 'all') {
            $noticesQuery .= " AND n.status = '$status'";
        }

        $noticesQuery .= " ORDER BY n.created_at DESC";

        $stmt = $connection->prepare($noticesQuery);
        $stmt->bind_param('ii', $sectorId, $adminId);
        $stmt->execute();
        $notices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // 3. ENGAGEMENT METRICS WITH REAL VIEW TRACKING
        $viewsQuery = "
            SELECT
                COUNT(*) as total_views,
                COUNT(DISTINCT user_id) as unique_views,
                COUNT(DISTINCT notice_id) as viewed_notices
            FROM notice_reads nr
            JOIN notices n ON nr.notice_id = n.id
            WHERE n.created_by = ? AND n.sector_id = ?";

        $stmt = $connection->prepare($viewsQuery);
        $stmt->bind_param('ii', $adminId, $sectorId);
        $stmt->execute();
        $viewStats = $stmt->get_result()->fetch_assoc();

        $totalViews    = $viewStats['total_views'] ?? 0;
        $uniqueViews   = $viewStats['unique_views'] ?? 0;
        $viewedNotices = $viewStats['viewed_notices'] ?? 0;

        // Calculate engagement rate as percentage of notices that have been viewed
        $engagementRate = ($totalNotices > 0) ? round(($viewedNotices / $totalNotices) * 100, 1) : 0;

    } catch (Exception $e) {
        // Default values in case of error
        $totalNotices     = 0;
        $activeNotices    = 0;
        $draftNotices     = 0;
        $urgentNotices    = 0;
        $noticesThisMonth = 0;
        $totalViews       = 0;
        $engagementRate   = 0;
        $notices          = [];
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

        <!-- Community Notices Content -->
        <main class="p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 ml-4 lg:ml-0">Community Notices</h1>
                            <p class="text-gray-600 mt-1 ml-4 lg:ml-0">
                                Manage announcements for                                                                                                                 <?php echo htmlspecialchars($sectorName); ?> Sector,<?php echo htmlspecialchars($districtName); ?> District
                            </p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                            <button onclick="toggleFilterModal()"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-filter mr-2"></i>
                                Filter Notices
                            </button>
                            <button id="createNoticeBtn"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg text-sm font-medium hover:from-primary-700 hover:to-primary-800 shadow-sm transition-all">
                                <i class="fas fa-plus mr-2"></i>
                                Create Notice
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Notice Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Notices -->
                    <div
                        class="bg-gradient-to-br from-white to-blue-50 rounded-xl shadow-sm p-6 border border-blue-100 hover:shadow-lg hover:border-blue-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Total Notices</p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $totalNotices; ?></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-primary-600 font-semibold bg-primary-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-bullhorn text-xs mr-1"></i>
                                        This Month
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium"><?php echo $noticesThisMonth; ?> published</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-bullhorn text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Active Notices -->
                    <div
                        class="bg-gradient-to-br from-white to-green-50 rounded-xl shadow-sm p-6 border border-green-100 hover:shadow-lg hover:border-green-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-green-600 uppercase tracking-wide">Active Notices
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $activeNotices; ?></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-success-600 font-semibold bg-success-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-check-circle text-xs mr-1"></i>
                                        Live
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">currently visible</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-success-500 to-success-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-broadcast-tower text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Views This Month -->
                    <div
                        class="bg-gradient-to-br from-white to-purple-50 rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-lg hover:border-purple-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-purple-600 uppercase tracking-wide">Views This
                                    Month</p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $totalViews < 1000 ? number_format($totalViews) : number_format($totalViews / 1000, 1) . 'K'; ?></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-purple-600 font-semibold bg-purple-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-arrow-up text-xs mr-1"></i>
                                        +15%
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">from last month</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-eye text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Engagement Rate -->
                    <div
                        class="bg-gradient-to-br from-white to-orange-50 rounded-xl shadow-sm p-6 border border-orange-100 hover:shadow-lg hover:border-orange-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Draft Notices
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2"><?php echo $draftNotices; ?></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-warning-600 font-semibold bg-warning-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-edit text-xs mr-1"></i>
                                        Pending
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">awaiting review</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-file-alt text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Categories -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <div class="flex flex-wrap gap-3">
                            <a href="?type=all&status=<?php echo urlencode($status); ?>"
                                class="px-4 py-2 text-sm                                                                                                                 <?php echo($type == 'all') ? 'bg-primary-100 text-primary-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-medium transition-colors">
                                All Notices
                            </a>
                            <a href="?type=general&status=<?php echo urlencode($status); ?>"
                                class="px-4 py-2 text-sm                                                                                                                 <?php echo($type == 'general') ? 'bg-primary-100 text-primary-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-medium transition-colors">
                                General
                            </a>
                            <a href="?type=event&status=<?php echo urlencode($status); ?>"
                                class="px-4 py-2 text-sm                                                                                                                 <?php echo($type == 'event') ? 'bg-primary-100 text-primary-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-medium transition-colors">
                                Events
                            </a>
                            <a href="?type=fine_reminder&status=<?php echo urlencode($status); ?>"
                                class="px-4 py-2 text-sm                                                                                                                 <?php echo($type == 'fine_reminder') ? 'bg-primary-100 text-primary-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-medium transition-colors">
                                Reminders
                            </a>
                            <a href="?type=urgent&status=<?php echo urlencode($status); ?>"
                                class="px-4 py-2 text-sm                                                                                                                 <?php echo($type == 'urgent') ? 'bg-primary-100 text-primary-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-medium transition-colors">
                                Urgent
                            </a>
                        </div>

                        <div class="flex items-center space-x-3">
                            <select onchange="filterByStatus(this.value)"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="all"                                                                                                       <?php echo($status == 'all') ? 'selected' : ''; ?>>All Status</option>
                                <option value="published"                                                                                                                   <?php echo($status == 'published') ? 'selected' : ''; ?>>Published</option>
                                <option value="draft"                                                                                                           <?php echo($status == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="archived"                                                                                                                 <?php echo($status == 'archived') ? 'selected' : ''; ?>>Archived</option>
                            </select>
                            <button onclick="refreshNotices()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition-colors">
                                <i class="fas fa-refresh mr-2"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Notices Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                    <?php if (! empty($notices)): ?>
<?php foreach ($notices as $noticeItem): ?>
<?php
    // Priority styling
    $priorityClass = match ($noticeItem['priority']) {
        'critical' => 'bg-red-100 text-red-800',
        'high' => 'bg-red-100 text-red-800',
        'medium' => 'bg-yellow-100 text-yellow-800',
        'low' => 'bg-blue-100 text-blue-800',
        default => 'bg-gray-100 text-gray-800'
    };

    // Type styling
    $typeClass = match ($noticeItem['type']) {
        'urgent' => 'bg-red-100 text-red-800',
        'event' => 'bg-green-100 text-green-800',
        'fine_reminder' => 'bg-purple-100 text-purple-800',
        'system' => 'bg-orange-100 text-orange-800',
        default => 'bg-blue-100 text-blue-800'
    };

    // Status styling
    $statusClass = match ($noticeItem['effective_status']) {
        'published', 'active' => 'bg-green-100 text-green-800',
        'scheduled'           => 'bg-yellow-100 text-yellow-800',
        'draft'               => 'bg-gray-100 text-gray-800',
        'expired'             => 'bg-red-100 text-red-800',
        'archived'            => 'bg-gray-100 text-gray-800',
        default               => 'bg-gray-100 text-gray-800'
    };

    $isArchived = in_array($noticeItem['effective_status'], ['archived', 'expired']);
    $isDraft    = $noticeItem['effective_status'] === 'draft';
?>
                            <div class="notice-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden<?php echo $isArchived ? 'opacity-60' : ($isDraft ? 'opacity-75' : ''); ?>">
                                <div class="p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center space-x-2 flex-wrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full                                                                                                                                                                                                                                   <?php echo $priorityClass; ?>">
                                                <?php echo ucfirst($noticeItem['priority']); ?> Priority
                                            </span>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full                                                                                                                                                                                                                                   <?php echo $typeClass; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $noticeItem['type'])); ?>
                                            </span>
                                        </div>
                                        <div class="relative">
                                            <button onclick="viewAnalytics(<?php echo $noticeItem['id']; ?>)" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                                <i class="fas fa-chart-line mr-1"></i>Analytics
                                            </button>
                                        </div>
                                    </div>

                                    <h3 class="text-lg font-semibold text-gray-900 mb-3"><?php echo htmlspecialchars($noticeItem['title']); ?></h3>
                                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                        <?php echo htmlspecialchars(substr($noticeItem['content'], 0, 150)); ?><?php echo strlen($noticeItem['content']) > 150 ? '...' : ''; ?>
                                    </p>

                                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                        <div class="flex items-center space-x-4">
                                            <span>
                                                <i class="fas fa-calendar mr-1"></i>
                                                <?php echo date('M j, Y', strtotime($noticeItem['created_at'])); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-eye mr-1"></i>
                                                <?php echo number_format($noticeItem['view_count']); ?> views
                                            </span>
                                        </div>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full                                                                                                                                                                                                                           <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($noticeItem['effective_status']); ?>
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <button onclick="editNotice(<?php echo $noticeItem['id']; ?>)" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                                <i class="fas fa-edit mr-1"></i>Edit
                                            </button>
                                            <button onclick="deleteNotice(<?php echo $noticeItem['id']; ?>)" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                <i class="fas fa-trash mr-1"></i>Delete
                                            </button>

                                            <?php if ($noticeItem['effective_status'] === 'published'): ?>
                                                <button onclick="shareNotice(<?php echo $noticeItem['id']; ?>)" class="text-gray-400 hover:text-gray-600 text-sm font-medium">
                                                    <i class="fas fa-share-alt mr-1"></i>Share
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            by                                                                                             <?php echo htmlspecialchars($noticeItem['first_name'] . ' ' . $noticeItem['last_name']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
<?php else: ?>
                        <!-- Empty State -->
                        <div class="col-span-full">
                            <div class="text-center py-12">
                                <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-bullhorn text-gray-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No notices found</h3>
                                <p class="text-gray-500 mb-6">
                                    <?php if ($type !== 'all' || $status !== 'all'): ?>
                                        No notices match your current filters. Try adjusting your search criteria.
                                    <?php else: ?>
                                        You haven't created any notices yet. Create your first notice to get started.
                                    <?php endif; ?>
                                </p>
                                <button id="createFirstNoticeBtn" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Create First Notice
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Load More -->
                <?php if (count($notices) >= 9): ?>
                    <div class="text-center">
                        <button onclick="loadMoreNotices()"
                            class="px-6 py-3 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-refresh mr-2"></i>
                            Load More Notices
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Quick Actions Panel -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Notice Templates -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-layer-group text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Quick Templates</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Use predefined templates for common notices</p>
                        <div class="space-y-2">
                            <button
                                class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                Event Announcement
                            </button>
                            <button
                                class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="fas fa-exclamation-triangle mr-2 text-gray-400"></i>
                                Urgent Notice
                            </button>
                            <button
                                class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="fas fa-bell mr-2 text-gray-400"></i>
                                General Reminder
                            </button>
                        </div>
                    </div>

                    <!-- Analytics -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-chart-bar text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Analytics</h3>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Most Viewed</span>
                                <span class="text-sm font-medium text-gray-900">1,245 views</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Avg. Engagement</span>
                                <span class="text-sm font-medium text-gray-900">78.5%</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Total Reach</span>
                                <span class="text-sm font-medium text-gray-900">3,247 residents</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-history text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-red-500 rounded-full mr-3"></div>
                                <span class="text-gray-600">Urgent notice published</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                <span class="text-gray-600">3 notices scheduled</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                <span class="text-gray-600">1 draft completed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Create Notice Modal -->
    <div id="createNoticeModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Create New Notice</h3>
                        <button id="closeNoticeModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form id="createNoticeForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notice Title</label>
                            <input type="text" id="noticeTitle" name="title" placeholder="Enter notice title" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                                <select id="noticeType" name="type"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="general">General</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="announcement">Announcement</option>
                                    <option value="event">Event</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                <select id="noticePriority" name="priority"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                            <textarea id="noticeContent" name="content" rows="6" placeholder="Write your notice content here..." required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                                <select id="targetAudience" name="target_audience"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="all">All Residents</option>
                                    <option value="cell_leaders">Cell Leaders</option>
                                    <option value="households">Households</option>
                                    <option value="youth">Youth</option>
                                    <option value="women">Women</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Publish Date</label>
                                <input type="datetime-local" id="publishDate" name="publish_date"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date (Optional)</label>
                            <input type="datetime-local" id="expiryDate" name="expiry_date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="noticeStatus" name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>

                        <div class="flex space-x-3 pt-4">
                            <button type="button" id="cancelCreateNotice"
                                class="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="flex-1 bg-gradient-to-r from-primary-600 to-primary-700 text-white py-2 px-4 rounded-lg font-medium hover:from-primary-700 hover:to-primary-800 transition-all">
                                Create Notice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Notice Modal -->
    <div id="editNoticeModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Edit Notice</h3>
                        <button id="closeEditModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form id="editNoticeForm" class="space-y-4">
                        <input type="hidden" id="editNoticeId" name="id">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notice Title</label>
                            <input type="text" id="editNoticeTitle" name="title" placeholder="Enter notice title" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                                <select id="editNoticeType" name="type"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="general">General</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="announcement">Announcement</option>
                                    <option value="event">Event</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                <select id="editNoticePriority" name="priority"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                            <textarea id="editNoticeContent" name="content" rows="6" placeholder="Write your notice content here..." required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                                <select id="editTargetAudience" name="target_audience"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="all">All Residents</option>
                                    <option value="cell_leaders">Cell Leaders</option>
                                    <option value="households">Households</option>
                                    <option value="youth">Youth</option>
                                    <option value="women">Women</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Publish Date</label>
                                <input type="datetime-local" id="editPublishDate" name="publish_date"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date (Optional)</label>
                            <input type="datetime-local" id="editExpiryDate" name="expiry_date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="editNoticeStatus" name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>

                        <div class="flex space-x-3 pt-4">
                            <button type="button" id="cancelEditNotice"
                                class="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="flex-1 bg-gradient-to-r from-primary-600 to-primary-700 text-white py-2 px-4 rounded-lg font-medium hover:from-primary-700 hover:to-primary-800 transition-all">
                                Update Notice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Modal -->
    <div id="analyticsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Notice Analytics</h3>
                        <button id="closeAnalyticsModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <div id="analyticsContent" class="space-y-6">
                        <div class="text-center py-8">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
                            <p class="text-gray-500 mt-2">Loading analytics...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Modal functionality
        const createNoticeBtn = document.getElementById('createNoticeBtn');
        const createFirstNoticeBtn = document.getElementById('createFirstNoticeBtn');
        const createNoticeModal = document.getElementById('createNoticeModal');
        const closeNoticeModal = document.getElementById('closeNoticeModal');
        const cancelCreateNotice = document.getElementById('cancelCreateNotice');

        const editNoticeModal = document.getElementById('editNoticeModal');
        const closeEditModal = document.getElementById('closeEditModal');
        const cancelEditNotice = document.getElementById('cancelEditNotice');

        // Create modal events
        if (createNoticeBtn) {
            createNoticeBtn.addEventListener('click', () => {
                createNoticeModal.classList.remove('hidden');
            });
        }

        if (createFirstNoticeBtn) {
            createFirstNoticeBtn.addEventListener('click', () => {
                createNoticeModal.classList.remove('hidden');
            });
        }

        if (closeNoticeModal) {
            closeNoticeModal.addEventListener('click', () => {
                createNoticeModal.classList.add('hidden');
            });
        }

        if (cancelCreateNotice) {
            cancelCreateNotice.addEventListener('click', () => {
                createNoticeModal.classList.add('hidden');
            });
        }

        // Edit modal events
        if (closeEditModal) {
            closeEditModal.addEventListener('click', () => {
                editNoticeModal.classList.add('hidden');
            });
        }

        if (cancelEditNotice) {
            cancelEditNotice.addEventListener('click', () => {
                editNoticeModal.classList.add('hidden');
            });
        }

        // Close modal on outside click
        if (createNoticeModal) {
            createNoticeModal.addEventListener('click', (e) => {
                if (e.target === createNoticeModal) {
                    createNoticeModal.classList.add('hidden');
                }
            });
        }

        if (editNoticeModal) {
            editNoticeModal.addEventListener('click', (e) => {
                if (e.target === editNoticeModal) {
                    editNoticeModal.classList.add('hidden');
                }
            });
        }

        // Form submissions
        const createNoticeForm = document.getElementById('createNoticeForm');
        if (createNoticeForm) {
            createNoticeForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = new FormData(e.target);
                const noticeData = Object.fromEntries(formData.entries());

                console.log('Creating notice with data:', noticeData);

                try {
                    const response = await fetch('../../api/notices.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(noticeData)
                    });

                    const result = await response.json();
                    console.log('Create response:', result);

                    if (result.success) {
                        alert('Notice created successfully!');
                        createNoticeModal.classList.add('hidden');
                        location.reload(); // Refresh to show new notice
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error creating notice:', error);
                    alert('Failed to create notice. Please try again.');
                }
            });
        }

        const editNoticeForm = document.getElementById('editNoticeForm');
        if (editNoticeForm) {
            editNoticeForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = new FormData(e.target);
                const noticeData = Object.fromEntries(formData.entries());

                console.log('Updating notice with data:', noticeData);

                try {
                    const response = await fetch('../../api/notices.php', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(noticeData)
                    });

                    const result = await response.json();
                    console.log('Update response:', result);

                    if (result.success) {
                        alert('Notice updated successfully!');
                        editNoticeModal.classList.add('hidden');
                        location.reload(); // Refresh to show updated notice
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error updating notice:', error);
                    alert('Failed to update notice. Please try again.');
                }
            });
        }

        // Edit notice function
        async function editNotice(noticeId) {
            console.log('Editing notice with ID:', noticeId);

            try {
                const response = await fetch(`../../api/notices.php?id=${noticeId}`);
                console.log('Edit API response status:', response.status);

                const result = await response.json();
                console.log('Edit API result:', result);

                if (result.success) {
                    const notice = result.notice;

                    // Populate form fields
                    document.getElementById('editNoticeId').value = notice.id;
                    document.getElementById('editNoticeTitle').value = notice.title;
                    document.getElementById('editNoticeContent').value = notice.content;
                    document.getElementById('editNoticeType').value = notice.type;
                    document.getElementById('editNoticePriority').value = notice.priority;
                    document.getElementById('editTargetAudience').value = notice.target_audience;
                    document.getElementById('editNoticeStatus').value = notice.status;

                    // Format dates for datetime-local inputs
                    if (notice.publish_date) {
                        const publishDate = new Date(notice.publish_date);
                        document.getElementById('editPublishDate').value = publishDate.toISOString().slice(0, 16);
                    }

                    if (notice.expiry_date) {
                        const expiryDate = new Date(notice.expiry_date);
                        document.getElementById('editExpiryDate').value = expiryDate.toISOString().slice(0, 16);
                    }

                    // Show modal
                    editNoticeModal.classList.remove('hidden');
                } else {
                    alert('Error loading notice: ' + result.message);
                }
            } catch (error) {
                console.error('Error loading notice:', error);
                alert('Failed to load notice details.');
            }
        }

        // Delete notice function
        async function deleteNotice(noticeId) {
            if (confirm('Are you sure you want to delete this notice?')) {
                console.log('Deleting notice with ID:', noticeId);

                try {
                    const response = await fetch(`../../api/notices.php?id=${noticeId}`, {
                        method: 'DELETE'
                    });

                    const result = await response.json();
                    console.log('Delete response:', result);

                    if (result.success) {
                        alert('Notice deleted successfully!');
                        location.reload(); // Refresh to remove deleted notice
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error deleting notice:', error);
                    alert('Failed to delete notice. Please try again.');
                }
            }
        }

        // View analytics function
        async function viewAnalytics(noticeId) {
            console.log('Viewing analytics for notice ID:', noticeId);

            // Show modal
            const analyticsModal = document.getElementById('analyticsModal');
            analyticsModal.classList.remove('hidden');

            try {
                const response = await fetch(`../../api/notice_analytics.php?notice_id=${noticeId}`);
                const result = await response.json();

                if (result.success) {
                    displayAnalytics(result.analytics, result.timeline);
                } else {
                    document.getElementById('analyticsContent').innerHTML = `
                        <div class="text-center py-8">
                            <div class="text-red-500 mb-4">
                                <i class="fas fa-exclamation-triangle text-2xl"></i>
                            </div>
                            <p class="text-gray-700">${result.message}</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading analytics:', error);
                document.getElementById('analyticsContent').innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-red-500 mb-4">
                            <i class="fas fa-exclamation-triangle text-2xl"></i>
                        </div>
                        <p class="text-gray-700">Failed to load analytics data</p>
                    </div>
                `;
            }
        }

        // Display analytics data
        function displayAnalytics(analytics, timeline) {
            const analyticsContent = document.getElementById('analyticsContent');

            analyticsContent.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <i class="fas fa-eye text-blue-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-gray-600">Total Views</p>
                                <p class="text-2xl font-bold text-gray-900">${analytics.total_views || 0}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <i class="fas fa-users text-green-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-gray-600">Unique Viewers</p>
                                <p class="text-2xl font-bold text-gray-900">${analytics.unique_viewers || 0}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <i class="fas fa-clock text-purple-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-gray-600">Avg. Hours to View</p>
                                <p class="text-2xl font-bold text-gray-900">${analytics.avg_hours_to_view ? Math.round(analytics.avg_hours_to_view) : 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-900 mb-3">Notice Details</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Title:</span>
                            <span class="ml-2 font-medium">${analytics.title}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Published:</span>
                            <span class="ml-2">${new Date(analytics.publish_date).toLocaleDateString()}</span>
                        </div>
                    </div>
                </div>

                ${timeline && timeline.length > 0 ? `
                <div class="bg-white border rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">View Timeline</h4>
                    <div class="space-y-2">
                        ${timeline.map(day => `
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-600">${new Date(day.view_date).toLocaleDateString()}</span>
                                <div class="text-right">
                                    <span class="text-sm font-medium">${day.views_count} views</span>
                                    <span class="text-xs text-gray-500 ml-2">(${day.unique_views_count} unique)</span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : '<p class="text-gray-500 text-center py-4">No timeline data available</p>'}
            `;
        }

        // Analytics modal events
        const analyticsModal = document.getElementById('analyticsModal');
        const closeAnalyticsModal = document.getElementById('closeAnalyticsModal');

        if (closeAnalyticsModal) {
            closeAnalyticsModal.addEventListener('click', () => {
                analyticsModal.classList.add('hidden');
            });
        }

        if (analyticsModal) {
            analyticsModal.addEventListener('click', (e) => {
                if (e.target === analyticsModal) {
                    analyticsModal.classList.add('hidden');
                }
            });
        }

        // Make functions globally available
        window.editNotice = editNotice;
        window.deleteNotice = deleteNotice;
        window.viewAnalytics = viewAnalytics;

        // Debug: Log when script loads
        console.log('Notice page JavaScript loaded');
        console.log('Create notice button:', createNoticeBtn);
        console.log('Create notice modal:', createNoticeModal);
        console.log('Edit notice modal:', editNoticeModal);
    </script>


<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>