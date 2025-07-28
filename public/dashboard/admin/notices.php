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
                            <p class="text-gray-600 mt-1 ml-4 lg:ml-0">Manage community announcements and notifications
                            </p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                            <button
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
                                <p class="text-3xl font-black text-gray-900 mt-2">28</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-primary-600 font-semibold bg-primary-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-bullhorn text-xs mr-1"></i>
                                        This Month
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">published</span>
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
                                <p class="text-3xl font-black text-gray-900 mt-2">12</p>
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
                                <p class="text-3xl font-black text-gray-900 mt-2">3.2K</p>
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
                                <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Engagement Rate
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2">78.5%</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-warning-600 font-semibold bg-warning-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-users text-xs mr-1"></i>
                                        Good
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">interaction rate</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-chart-line text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Categories -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <div class="flex flex-wrap gap-3">
                            <button
                                class="px-4 py-2 text-sm bg-primary-100 text-primary-700 rounded-lg font-medium hover:bg-primary-200 transition-colors">
                                All Notices
                            </button>
                            <button
                                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                Announcements
                            </button>
                            <button
                                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                Events
                            </button>
                            <button
                                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                Reminders
                            </button>
                            <button
                                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                Urgent
                            </button>
                        </div>

                        <div class="flex items-center space-x-3">
                            <select
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option>All Status</option>
                                <option>Published</option>
                                <option>Draft</option>
                                <option>Scheduled</option>
                                <option>Archived</option>
                            </select>
                            <select
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option>All Priority</option>
                                <option>High</option>
                                <option>Medium</option>
                                <option>Low</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Notices Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                    <!-- High Priority Notice -->
                    <div
                        class="notice-card notice-priority-high bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        High Priority
                                    </span>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Announcement
                                    </span>
                                </div>
                                <div class="relative">
                                    <button class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Urgent: Umuganda Schedule Change</h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">Due to the national holiday, tomorrow's
                                Umuganda session has been rescheduled to Sunday, July 28th at the usual time. All
                                residents are expected to participate...</p>

                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center space-x-4">
                                    <span><i class="fas fa-calendar mr-1"></i>July 25, 2025</span>
                                    <span><i class="fas fa-eye mr-1"></i>1,245 views</span>
                                </div>
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Published
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <button class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button class="text-gray-400 hover:text-gray-600 text-sm font-medium">
                                        <i class="fas fa-share-alt mr-1"></i>Share
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500">
                                    by Admin User
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Medium Priority Notice -->
                    <div
                        class="notice-card notice-priority-medium bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Medium Priority
                                    </span>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Event
                                    </span>
                                </div>
                                <div class="relative">
                                    <button class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Community Leadership Training</h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">Join us for a comprehensive leadership
                                training session on July 26th at 2:00 PM. This session will cover community management,
                                conflict resolution, and effective communication...</p>

                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center space-x-4">
                                    <span><i class="fas fa-calendar mr-1"></i>July 24, 2025</span>
                                    <span><i class="fas fa-eye mr-1"></i>892 views</span>
                                </div>
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Published
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <button class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button class="text-gray-400 hover:text-gray-600 text-sm font-medium">
                                        <i class="fas fa-share-alt mr-1"></i>Share
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500">
                                    by Admin User
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduled Notice -->
                    <div
                        class="notice-card notice-priority-low bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Low Priority
                                    </span>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                        Reminder
                                    </span>
                                </div>
                                <div class="relative">
                                    <button class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Monthly Fee Payment Reminder</h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">This is a friendly reminder that monthly
                                community fees are due by the end of the month. Please ensure timely payment to avoid
                                any inconvenience...</p>

                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center space-x-4">
                                    <span><i class="fas fa-clock mr-1"></i>Scheduled for July 30</span>
                                    <span><i class="fas fa-eye mr-1"></i>0 views</span>
                                </div>
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Scheduled
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <button class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button class="text-gray-400 hover:text-gray-600 text-sm font-medium">
                                        <i class="fas fa-clock mr-1"></i>Reschedule
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500">
                                    by Admin User
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Draft Notice -->
                    <div
                        class="notice-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden opacity-75">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Draft
                                    </span>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Announcement
                                    </span>
                                </div>
                                <div class="relative">
                                    <button class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Water Supply Maintenance</h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">Planned maintenance work on the community
                                water supply system will take place next week. Residents are advised to store sufficient
                                water...</p>

                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center space-x-4">
                                    <span><i class="fas fa-edit mr-1"></i>Last edited: July 23</span>
                                    <span><i class="fas fa-eye mr-1"></i>Not published</span>
                                </div>
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Draft
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <button class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                        <i class="fas fa-edit mr-1"></i>Continue Editing
                                    </button>
                                    <button class="text-success-600 hover:text-success-900 text-sm font-medium">
                                        <i class="fas fa-paper-plane mr-1"></i>Publish
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500">
                                    by Admin User
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Notice -->
                    <div
                        class="notice-card notice-priority-medium bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Medium Priority
                                    </span>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                        Information
                                    </span>
                                </div>
                                <div class="relative">
                                    <button class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-3">New Community Guidelines</h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">Updated community guidelines have been
                                issued regarding waste management and recycling practices. Please review the new
                                requirements and implement them...</p>

                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center space-x-4">
                                    <span><i class="fas fa-calendar mr-1"></i>July 22, 2025</span>
                                    <span><i class="fas fa-eye mr-1"></i>654 views</span>
                                </div>
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Published
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <button class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button class="text-gray-400 hover:text-gray-600 text-sm font-medium">
                                        <i class="fas fa-share-alt mr-1"></i>Share
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500">
                                    by Admin User
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Archived Notice -->
                    <div
                        class="notice-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden opacity-60">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Archived
                                    </span>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Event
                                    </span>
                                </div>
                                <div class="relative">
                                    <button class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Independence Day Celebration</h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">Join us for the Independence Day
                                celebration on July 1st. The event will feature cultural performances, traditional food,
                                and community activities...</p>

                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center space-x-4">
                                    <span><i class="fas fa-calendar mr-1"></i>June 28, 2025</span>
                                    <span><i class="fas fa-eye mr-1"></i>2,134 views</span>
                                </div>
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Archived
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <button class="text-gray-400 hover:text-gray-600 text-sm font-medium">
                                        <i class="fas fa-archive mr-1"></i>View Archive
                                    </button>
                                    <button class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                        <i class="fas fa-undo mr-1"></i>Restore
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500">
                                    by Admin User
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Load More -->
                <div class="text-center">
                    <button
                        class="px-6 py-3 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-refresh mr-2"></i>
                        Load More Notices
                    </button>
                </div>

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

                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notice Title</label>
                            <input type="text" placeholder="Enter notice title"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="announcement">Announcement</option>
                                    <option value="event">Event</option>
                                    <option value="reminder">Reminder</option>
                                    <option value="information">Information</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                <select
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                            <textarea rows="6" placeholder="Write your notice content here..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                                <select
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="all">All Residents</option>
                                    <option value="gasabo">Gasabo Cell</option>
                                    <option value="nyarugenge">Nyarugenge Cell</option>
                                    <option value="kicukiro">Kicukiro Cell</option>
                                    <option value="leaders">Cell Leaders</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Publish Date</label>
                                <input type="datetime-local" value="2025-07-25T09:00"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox"
                                    class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                <span class="ml-2 text-sm text-gray-700">Send SMS notifications</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" checked
                                    class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                <span class="ml-2 text-sm text-gray-700">Pin to top</span>
                            </label>
                        </div>

                        <div class="flex space-x-3 pt-4">
                            <button type="button"
                                class="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                Save Draft
                            </button>
                            <button type="submit"
                                class="flex-1 bg-gradient-to-r from-primary-600 to-primary-700 text-white py-2 px-4 rounded-lg font-medium hover:from-primary-700 hover:to-primary-800 transition-all">
                                Publish Notice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Mobile menu functionality
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const desktopSidebarToggle = document.getElementById('desktop-sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const closeSidebar = document.getElementById('close-sidebar');
        const mainContent = document.getElementById('main-content');

        let sidebarHidden = false;

        // Initialize sidebar position based on screen size
        function initializeSidebar() {
            if (window.innerWidth >= 1024) {
                // Desktop - show sidebar by default
                sidebar.classList.remove('-translate-x-full');
                mainContent.classList.add('lg:ml-64');
                mainContent.classList.remove('lg:ml-0');
                sidebarHidden = false;
            } else {
                // Mobile - hide sidebar by default
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            }
        }

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        }

        function hideSidebar() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        }

        function toggleDesktopSidebar() {
            if (window.innerWidth >= 1024) {
                if (sidebarHidden) {
                    // Show sidebar
                    sidebar.classList.remove('-translate-x-full');
                    mainContent.classList.add('lg:ml-64');
                    mainContent.classList.remove('lg:ml-0');
                    sidebarHidden = false;
                } else {
                    // Hide sidebar
                    sidebar.classList.add('-translate-x-full');
                    mainContent.classList.remove('lg:ml-64');
                    mainContent.classList.add('lg:ml-0');
                    sidebarHidden = true;
                }
            }
        }

        // Event listeners
        mobileMenuBtn.addEventListener('click', toggleSidebar);
        desktopSidebarToggle.addEventListener('click', toggleDesktopSidebar);
        sidebarOverlay.addEventListener('click', hideSidebar);
        closeSidebar.addEventListener('click', hideSidebar);

        // Handle window resize
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) {
                // Desktop view - hide mobile overlay
                sidebarOverlay.classList.add('hidden');

                // Reset sidebar position for desktop if it wasn't manually hidden
                if (!sidebarHidden) {
                    sidebar.classList.remove('-translate-x-full');
                    mainContent.classList.add('lg:ml-64');
                    mainContent.classList.remove('lg:ml-0');
                }
            } else {
                // Mobile view - reset to default mobile behavior
                sidebar.classList.add('-translate-x-full');
                mainContent.classList.remove('lg:ml-0');
                mainContent.classList.add('lg:ml-64');
                sidebarHidden = false;
            }
        });

        // Modal functionality
        const createNoticeBtn = document.getElementById('createNoticeBtn');
        const createNoticeModal = document.getElementById('createNoticeModal');
        const closeNoticeModal = document.getElementById('closeNoticeModal');

        createNoticeBtn.addEventListener('click', () => {
            createNoticeModal.classList.remove('hidden');
        });

        closeNoticeModal.addEventListener('click', () => {
            createNoticeModal.classList.add('hidden');
        });

        // Close modal on outside click
        createNoticeModal.addEventListener('click', (e) => {
            if (e.target === createNoticeModal) {
                createNoticeModal.classList.add('hidden');
            }
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            initializeSidebar();
        });
    </script>
</body>

</html>