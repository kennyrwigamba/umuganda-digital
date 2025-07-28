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
    $noticeModel = new Notice();

    // Get filter parameters
    $selectedType = $_GET['type'] ?? 'all';
    $searchQuery  = $_GET['search'] ?? '';

    // Get notices for this user based on their location
    $userLocation = [
        'cell'     => $user['cell'],
        'sector'   => $user['sector'],
        'district' => $user['district'],
        'role'     => $user['role'],
    ];

    // Get all notices for the user (we'll filter in frontend for better UX)
    $allNotices = $noticeModel->getNoticesForUser(
        $_SESSION['user_id'],
        $user['cell'],
        $user['sector'],
        $user['district'],
        'resident',
        50// Get more notices for filtering
    );

    // Apply server-side type filtering if specified
    if ($selectedType !== 'all') {
        $typeMap = [
            'urgent'   => ['urgent'],
            'schedule' => ['schedule'],
            'general'  => ['general'],
            'events'   => ['event'],
        ];

        if (isset($typeMap[$selectedType])) {
            $allNotices = array_filter($allNotices, function ($notice) use ($typeMap, $selectedType) {
                return in_array($notice['type'], $typeMap[$selectedType]) ||
                    ($selectedType === 'urgent' && in_array($notice['priority'], ['critical', 'high']));
            });
        }
    }

    // Get unread notices count
    $unreadCount = $noticeModel->getUnreadNoticesCount(
        $_SESSION['user_id'],
        $user['cell'],
        $user['sector'],
        $user['district'],
        'resident'
    );

    // Separate urgent notices (critical/high priority)
    $urgentNotices = array_filter($allNotices, function ($notice) {
        return in_array($notice['priority'], ['critical', 'high']) && ! $notice['is_read'];
    });

    // Filter notices based on search query if provided
    if (! empty($searchQuery)) {
        $allNotices = array_filter($allNotices, function ($notice) use ($searchQuery) {
            $searchTerm = strtolower($searchQuery);
            return strpos(strtolower($notice['title']), $searchTerm) !== false ||
            strpos(strtolower($notice['content']), $searchTerm) !== false;
        });
    }

?>

<!-- Header -->
<?php include __DIR__ . '/partials/header.php'; ?>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 font-sans">
    <div class="flex flex-col md:flex-row h-screen">
        <!-- Sidebar -->
        <?php include 'partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden md:ml-0">
            <!-- Top Navbar -->
            <?php include 'partials/top-nav.php'; ?>

            <!-- Main Content -->
            <main
                class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 p-4 md:p-6">
                <div class="max-w-7xl mx-auto space-y-6">

                    <!-- Notice Categories and Filters -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6">
                        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
                            <div class="flex flex-wrap gap-2">
                                <button onclick="filterNotices('all')"
                                    class="filter-btn                                                      <?php echo $selectedType === 'all' ? 'active bg-primary-100 text-primary-700 border-primary-200' : 'bg-gray-100 text-gray-700 border-gray-200'; ?> px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                                    All Notices
                                </button>
                                <button onclick="filterNotices('urgent')"
                                    class="filter-btn                                                      <?php echo $selectedType === 'urgent' ? 'active bg-primary-100 text-primary-700 border-primary-200' : 'bg-gray-100 text-gray-700 border-gray-200'; ?> px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-danger-50 hover:text-danger-700 hover:border-danger-200">
                                    Urgent
                                </button>
                                <button onclick="filterNotices('schedule')"
                                    class="filter-btn                                                      <?php echo $selectedType === 'schedule' ? 'active bg-primary-100 text-primary-700 border-primary-200' : 'bg-gray-100 text-gray-700 border-gray-200'; ?> px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-warning-50 hover:text-warning-700 hover:border-warning-200">
                                    Schedule Changes
                                </button>
                                <button onclick="filterNotices('general')"
                                    class="filter-btn                                                      <?php echo $selectedType === 'general' ? 'active bg-primary-100 text-primary-700 border-primary-200' : 'bg-gray-100 text-gray-700 border-gray-200'; ?> px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-primary-50 hover:text-primary-700 hover:border-primary-200">
                                    General Info
                                </button>
                                <button onclick="filterNotices('events')"
                                    class="filter-btn                                                      <?php echo $selectedType === 'events' ? 'active bg-primary-100 text-primary-700 border-primary-200' : 'bg-gray-100 text-gray-700 border-gray-200'; ?> px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-success-50 hover:text-success-700 hover:border-success-200">
                                    Events
                                </button>
                            </div>
                            <div class="flex gap-3 w-full lg:w-auto">
                                <form method="GET" class="relative flex-1 lg:w-64">
                                    <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>"
                                           placeholder="Search notices..."
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($selectedType); ?>">
                                </form>
                                <button
                                    class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Important/Urgent Notices Section -->
                    <?php if (! empty($urgentNotices)): ?>
<?php foreach ($urgentNotices as $urgentNotice):
        $publishDate = new DateTime($urgentNotice['publish_date']);
        $timeAgo     = getTimeAgo($urgentNotice['publish_date']);
    ?>
	                        <div class="bg-gradient-to-r from-danger-50 to-red-50 border-l-4 border-danger-500 rounded-xl shadow-lg p-6">
	                            <div class="flex items-start">
	                                <div class="flex-shrink-0">
	                                    <svg class="h-6 w-6 text-danger-500" fill="currentColor" viewBox="0 0 20 20">
	                                        <path fill-rule="evenodd"
	                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
	                                            clip-rule="evenodd"></path>
	                                    </svg>
	                                </div>
	                                <div class="ml-3 flex-1">
	                                    <h3 class="text-lg font-medium text-danger-800"><?php echo htmlspecialchars($urgentNotice['title']); ?></h3>
	                                    <p class="text-sm text-danger-700 mt-1"><?php echo htmlspecialchars($urgentNotice['content']); ?></p>
	                                    <p class="text-xs text-danger-600 mt-2">
	                                        Posted	                                               <?php echo $timeAgo; ?> •
	                                        <?php echo ucfirst($urgentNotice['priority']); ?> Priority
	                                        <?php if ($urgentNotice['creator_first_name']): ?>
	                                        • By <?php echo htmlspecialchars($urgentNotice['creator_first_name'] . ' ' . $urgentNotice['creator_last_name']); ?>
<?php endif; ?>
                                    </p>
                                </div>
                                <div class="ml-4">
                                    <button onclick="markAsRead(<?php echo $urgentNotice['id']; ?>, this)"
                                            class="text-danger-700 hover:text-danger-900 text-sm font-medium">
                                        Mark as Read
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
<?php endif; ?>

                    <!-- Notices Grid -->
                    <div class="space-y-4" id="noticesContainer">
                        <?php if (! empty($allNotices)): ?>
<?php foreach ($allNotices as $notice):
        // Skip urgent notices as they're displayed above
        if (in_array($notice['priority'], ['critical', 'high']) && ! $notice['is_read']) {
            continue;
        }

        $publishDate = new DateTime($notice['publish_date']);
        $timeAgo     = getTimeAgo($notice['publish_date']);

        // Determine notice type styling
        $typeClass = '';
        $typeBadge = '';
        switch ($notice['type']) {
            case 'schedule':
                $typeClass = 'bg-warning-100 text-warning-800';
                $typeBadge = 'Schedule Change';
                break;
            case 'event':
                $typeClass = 'bg-success-100 text-success-800';
                $typeBadge = 'Event';
                break;
            case 'general':
                $typeClass = 'bg-primary-100 text-primary-800';
                $typeBadge = 'General Info';
                break;
            case 'urgent':
                $typeClass = 'bg-danger-100 text-danger-800';
                $typeBadge = 'Urgent';
                break;
            default:
                $typeClass = 'bg-gray-100 text-gray-800';
                $typeBadge = ucfirst($notice['type']);
        }

        // Check if notice is new (published within last 3 days)
        $isNew = (time() - strtotime($notice['publish_date'])) < (3 * 24 * 60 * 60);
    ?>
	                            <div class="notice-item bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200"
	                                 data-category="<?php echo htmlspecialchars($notice['type']); ?>"
	                                 data-priority="<?php echo htmlspecialchars($notice['priority']); ?>"
	                                 data-notice-id="<?php echo $notice['id']; ?>">
	                                <div class="flex items-start justify-between">
	                                    <div class="flex-1">
	                                        <div class="flex items-center space-x-2 mb-3">
	                                            <span class="<?php echo $typeClass; ?> text-xs font-medium px-2.5 py-0.5 rounded-full">
	                                                <?php echo $typeBadge; ?>
	                                            </span>
	                                            <span class="text-sm text-gray-500">
	                                                <?php echo $publishDate->format('M j, Y • g:i A'); ?>
	                                            </span>
	                                            <?php if ($isNew): ?>
	                                            <span class="bg-primary-100 text-primary-800 text-xs font-medium px-2 py-0.5 rounded-full">NEW</span>
	                                            <?php endif; ?>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                            <?php echo htmlspecialchars($notice['title']); ?>
                                        </h3>
                                        <p class="text-gray-700 mb-3">
                                            <?php echo htmlspecialchars(substr($notice['content'], 0, 300)); ?>
<?php if (strlen($notice['content']) > 300): ?>...<?php endif; ?>
                                        </p>

                                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4V7a2 2 0 012-2h4a2 2 0 012 2v4M8 15v4a2 2 0 002 2h4a2 2 0 002-2v-4">
                                                    </path>
                                                </svg>
                                                Posted                                                       <?php echo $timeAgo; ?>
                                            </span>
                                            <?php if ($notice['creator_first_name']): ?>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                    </path>
                                                </svg>
                                                <?php echo htmlspecialchars($notice['creator_first_name'] . ' ' . $notice['creator_last_name']); ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($notice['expiry_date']): ?>
                                        <div class="mt-2">
                                            <span class="text-xs text-gray-500">
                                                Expires:                                                         <?php echo(new DateTime($notice['expiry_date']))->format('M j, Y'); ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4 flex flex-col space-y-2">
                                        <?php if ($notice['is_read']): ?>
                                            <span class="text-success-600 text-sm font-medium">✓ Read</span>
                                        <?php else: ?>
                                            <button onclick="markAsRead(<?php echo $notice['id']; ?>, this)"
                                                class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                                                Mark as Read
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="viewNoticeDetails(<?php echo $notice['id']; ?>)"
                                            class="text-gray-600 hover:text-gray-800 text-sm">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
<?php else: ?>
                            <div class="text-center py-12">
                                <div class="text-gray-500">
                                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                        </path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No notices found</h3>
                                    <p class="text-gray-600">
                                        <?php if (! empty($searchQuery)): ?>
                                            No notices match your search for "<?php echo htmlspecialchars($searchQuery); ?>".
                                        <?php else: ?>
                                            There are no notices available at this time.
                                        <?php endif; ?>
                                    </p>
                                    <?php if (! empty($searchQuery)): ?>
                                    <a href="?" class="inline-flex items-center mt-4 text-primary-600 hover:text-primary-800">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                        </svg>
                                        View all notices
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Load More Button -->
                    <?php if (count($allNotices) >= 10): ?>
                    <div class="text-center py-6">
                        <button onclick="loadMoreNotices()"
                            class="bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 transition-colors font-medium">
                            Load More Notices
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Notice Detail Modal -->
    <div id="noticeModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 id="modalNoticeTitle" class="text-lg leading-6 font-medium text-gray-900">Notice Details</h3>
                            <div class="mt-2">
                                <p id="modalNoticeContent" class="text-sm text-gray-500">View full notice details and take action if required.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button onclick="closeNoticeModal()"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentPage = 1;
        let isLoading = false;

        // Filter functionality
        function filterNotices(category) {
            const filterBtns = document.querySelectorAll('.filter-btn');
            const notices = document.querySelectorAll('.notice-item');

            // Update active filter button
            filterBtns.forEach(btn => {
                btn.classList.remove('active', 'bg-primary-100', 'text-primary-700', 'border-primary-200');
                btn.classList.add('bg-gray-100', 'text-gray-700', 'border-gray-200');
            });

            event.target.classList.remove('bg-gray-100', 'text-gray-700', 'border-gray-200');
            event.target.classList.add('active', 'bg-primary-100', 'text-primary-700', 'border-primary-200');

            // Update URL and reload page with filter
            const url = new URL(window.location.href);
            url.searchParams.set('type', category);
            if (category === 'all') {
                url.searchParams.delete('type');
            }
            window.location.href = url.toString();
        }

        // Mark as read functionality
        async function markAsRead(noticeId, button) {
            try {
                const response = await fetch('mark_notice_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        notice_id: noticeId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Update button to show read status
                    button.outerHTML = '<span class="text-success-600 text-sm font-medium">✓ Read</span>';
                    showNotification('Notice marked as read', 'success');
                } else {
                    showNotification(result.message || 'Failed to mark notice as read', 'error');
                }
            } catch (error) {
                console.error('Error marking notice as read:', error);
                showNotification('An error occurred while marking notice as read', 'error');
            }
        }

        // View notice details
        function viewNoticeDetails(noticeId) {
            // For now, just show an alert - in a real implementation this would open a modal or navigate to a details page
            showNoticeModal(noticeId);
        }

        // Show notice modal
        function showNoticeModal(noticeId) {
            // Find the notice element
            const noticeElement = document.querySelector(`[data-notice-id="${noticeId}"]`);
            if (noticeElement) {
                const title = noticeElement.querySelector('h3').textContent;
                const content = noticeElement.querySelector('p').textContent;

                // Update modal content
                document.getElementById('modalNoticeTitle').textContent = title;
                document.getElementById('modalNoticeContent').textContent = content;

                // Show modal
                document.getElementById('noticeModal').classList.remove('hidden');
            }
        }

        // Modal functionality
        function closeNoticeModal() {
            document.getElementById('noticeModal').classList.add('hidden');
        }

        // Load more notices
        async function loadMoreNotices() {
            if (isLoading) return;

            isLoading = true;
            const loadButton = document.querySelector('button[onclick="loadMoreNotices()"]');
            const originalText = loadButton.textContent;
            loadButton.textContent = 'Loading...';
            loadButton.disabled = true;

            try {
                const url = new URL(window.location.href);
                url.searchParams.set('page', currentPage + 1);
                url.searchParams.set('ajax', '1');

                const response = await fetch(url);
                const data = await response.json();

                if (data.success && data.notices && data.notices.length > 0) {
                    // Append new notices to the container
                    const container = document.getElementById('noticesContainer');
                    container.insertAdjacentHTML('beforeend', data.html);
                    currentPage++;

                    // Hide load more button if no more notices
                    if (data.notices.length < 10) {
                        loadButton.style.display = 'none';
                    }
                } else {
                    loadButton.style.display = 'none';
                    showNotification('No more notices to load', 'info');
                }
            } catch (error) {
                console.error('Error loading more notices:', error);
                showNotification('Failed to load more notices', 'error');
            } finally {
                isLoading = false;
                loadButton.textContent = originalText;
                loadButton.disabled = false;
            }
        }

        // Notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full shadow-lg rounded-lg p-4 ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;

            document.body.appendChild(notification);

            // Auto-remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Search functionality (now handled by form submission)
        const searchForm = document.querySelector('form');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                // Let the form submit normally
            });
        }

        // Update filter buttons based on URL parameters
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const selectedType = urlParams.get('type') || 'all';

            // Update active filter button
            const filterBtns = document.querySelectorAll('.filter-btn');
            filterBtns.forEach(btn => {
                const btnCategory = btn.getAttribute('onclick').match(/filterNotices\('(.+?)'\)/)[1];
                if (btnCategory === selectedType) {
                    btn.classList.remove('bg-gray-100', 'text-gray-700', 'border-gray-200');
                    btn.classList.add('active', 'bg-primary-100', 'text-primary-700', 'border-primary-200');
                } else {
                    btn.classList.remove('active', 'bg-primary-100', 'text-primary-700', 'border-primary-200');
                    btn.classList.add('bg-gray-100', 'text-gray-700', 'border-gray-200');
                }
            });

            // Close modal when clicking outside
            document.getElementById('noticeModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeNoticeModal();
                }
            });
        });
    </script>


<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>