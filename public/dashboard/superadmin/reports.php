<!-- Header -->
<?php include __DIR__ . '/partials/header.php'; ?>

<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div id="main-content" class="content-transition lg:ml-64">
        <!-- Top Navigation -->
        <?php include __DIR__ . '/partials/top-nav.php'; ?>

        <!-- Reports & Analytics Content -->
        <main class="p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 ml-4 lg:ml-0">Reports & Analytics</h1>
                            <p class="text-gray-600 mt-1 ml-4 lg:ml-0">Comprehensive insights and data analysis</p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                            <button
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Date Range
                            </button>
                            <button id="generateReportBtn"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg text-sm font-medium hover:from-primary-700 hover:to-primary-800 shadow-sm transition-all">
                                <i class="fas fa-file-download mr-2"></i>
                                Generate Report
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Time Period Filter -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <div class="flex flex-wrap gap-2">
                            <button
                                class="px-4 py-2 text-sm bg-primary-100 text-primary-700 rounded-lg font-medium hover:bg-primary-200 transition-colors">
                                Last 7 Days
                            </button>
                            <button
                                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                Last 30 Days
                            </button>
                            <button
                                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                Last 3 Months
                            </button>
                            <button
                                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                This Year
                            </button>
                            <button
                                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                Custom Range
                            </button>
                        </div>

                        <div class="flex items-center space-x-3">
                            <input type="date" value="2025-06-25"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <span class="text-gray-500">to</span>
                            <input type="date" value="2025-07-25"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                </div>

                <!-- Key Metrics Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Attendance Rate -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-check text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold">87.3%</div>
                                <div class="text-blue-100 text-sm">Attendance Rate</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-blue-100 text-sm">
                            <span>Avg. 1,089 present</span>
                            <span class="flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i>
                                +2.1%
                            </span>
                        </div>
                    </div>

                    <!-- Revenue Collection -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-money-bill-wave text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold">285K</div>
                                <div class="text-green-100 text-sm">RWF Collected</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-green-100 text-sm">
                            <span>This month</span>
                            <span class="flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i>
                                +18%
                            </span>
                        </div>
                    </div>

                    <!-- Event Success Rate -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-check text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold">94.2%</div>
                                <div class="text-purple-100 text-sm">Event Success</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-purple-100 text-sm">
                            <span>24 events completed</span>
                            <span class="flex items-center">
                                <i class="fas fa-check mr-1"></i>
                                Excellent
                            </span>
                        </div>
                    </div>

                    <!-- Community Engagement -->
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold">76.8%</div>
                                <div class="text-orange-100 text-sm">Engagement</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-orange-100 text-sm">
                            <span>Active participation</span>
                            <span class="flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i>
                                +5.3%
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Attendance Trends -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Attendance Trends</h3>
                            <div class="flex space-x-2">
                                <button
                                    class="px-3 py-1 text-sm bg-primary-100 text-primary-700 rounded-md font-medium">
                                    Monthly
                                </button>
                                <button class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-md">
                                    Weekly
                                </button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="attendanceTrendsChart"></canvas>
                        </div>
                    </div>

                    <!-- Revenue Analysis -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Revenue Analysis</h3>
                            <div class="flex space-x-2">
                                <button
                                    class="px-3 py-1 text-sm bg-primary-100 text-primary-700 rounded-md font-medium">
                                    Collections
                                </button>
                                <button class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-md">
                                    Outstanding
                                </button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Cell Performance -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Cell Performance</h3>
                        <div class="space-y-4">
                            <!-- Gasabo -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                    <span class="text-sm font-medium text-gray-700">Gasabo</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: 92%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 w-10">92%</span>
                                </div>
                            </div>

                            <!-- Nyarugenge -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                    <span class="text-sm font-medium text-gray-700">Nyarugenge</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: 88%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 w-10">88%</span>
                                </div>
                            </div>

                            <!-- Kicukiro -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                                    <span class="text-sm font-medium text-gray-700">Kicukiro</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                        <div class="bg-purple-500 h-2 rounded-full" style="width: 84%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 w-10">84%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Goals -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Monthly Goals</h3>
                        <div class="space-y-6">
                            <!-- Attendance Goal -->
                            <div class="text-center">
                                <div class="relative inline-flex">
                                    <svg class="w-20 h-20">
                                        <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="8"
                                            fill="transparent" class="text-gray-200" />
                                        <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="8"
                                            fill="transparent" stroke-dasharray="226.2" stroke-dashoffset="45.24"
                                            class="text-blue-500 progress-ring" />
                                    </svg>
                                    <span
                                        class="absolute inset-0 flex items-center justify-center text-sm font-bold text-gray-900">
                                        80%
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <div class="text-sm font-medium text-gray-900">Attendance Goal</div>
                                    <div class="text-xs text-gray-500">Target: 90%</div>
                                </div>
                            </div>

                            <!-- Collection Goal -->
                            <div class="text-center">
                                <div class="relative inline-flex">
                                    <svg class="w-20 h-20">
                                        <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="8"
                                            fill="transparent" class="text-gray-200" />
                                        <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="8"
                                            fill="transparent" stroke-dasharray="226.2" stroke-dashoffset="67.86"
                                            class="text-green-500 progress-ring" />
                                    </svg>
                                    <span
                                        class="absolute inset-0 flex items-center justify-center text-sm font-bold text-gray-900">
                                        70%
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <div class="text-sm font-medium text-gray-900">Collection Goal</div>
                                    <div class="text-xs text-gray-500">Target: 400K RWF</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Performers -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Top Performers</h3>
                        <div class="space-y-4">
                            <!-- Best Attendance -->
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-gold-400 to-gold-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-trophy text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Best Attendance</div>
                                    <div class="text-xs text-gray-500">Gasabo Cell - 92%</div>
                                </div>
                                <div class="text-lg font-bold text-gold-500">🥇</div>
                            </div>

                            <!-- Most Improved -->
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-silver-400 to-silver-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-arrow-up text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Most Improved</div>
                                    <div class="text-xs text-gray-500">Kicukiro Cell - +8%</div>
                                </div>
                                <div class="text-lg font-bold text-silver-500">🥈</div>
                            </div>

                            <!-- Highest Collection -->
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-bronze-400 to-bronze-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-coins text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Highest Collection</div>
                                    <div class="text-xs text-gray-500">Nyarugenge - 125K RWF</div>
                                </div>
                                <div class="text-lg font-bold text-bronze-500">🥉</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Reports Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Detailed Reports</h3>
                            <div class="flex items-center space-x-3">
                                <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <option>All Reports</option>
                                    <option>Attendance Reports</option>
                                    <option>Financial Reports</option>
                                    <option>Event Reports</option>
                                </select>
                                <button
                                    class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm hover:bg-primary-700 transition-colors">
                                    <i class="fas fa-download mr-2"></i>
                                    Export
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Report Type
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Period
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Generated
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-chart-line text-blue-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Monthly Attendance Report
                                                </div>
                                                <div class="text-sm text-gray-500">Comprehensive attendance analysis
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">July 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Ready
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 hours ago</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-primary-600 hover:text-primary-900 mr-3">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-money-bill-wave text-green-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Financial Summary Report
                                                </div>
                                                <div class="text-sm text-gray-500">Collections and outstanding amounts
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Q2 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Processing
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Processing...</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-gray-400">
                                            <i class="fas fa-clock"></i>
                                        </button>
                                    </td>
                                </tr>

                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-calendar-alt text-purple-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Event Performance Report
                                                </div>
                                                <div class="text-sm text-gray-500">Event success metrics and analysis
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">June 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Ready
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1 day ago</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-primary-600 hover:text-primary-900 mr-3">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Generate Report Modal -->
    <div id="generateReportModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Generate Custom Report</h3>
                        <button id="closeReportModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                            <select
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="attendance">Attendance Report</option>
                                <option value="financial">Financial Report</option>
                                <option value="events">Events Report</option>
                                <option value="comprehensive">Comprehensive Report</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="date" value="2025-06-01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="date" value="2025-07-25"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cells</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" checked
                                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">All Cells</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Gasabo Only</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Nyarugenge Only</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Kicukiro Only</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                            <select
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="pdf">PDF Document</option>
                                <option value="excel">Excel Spreadsheet</option>
                                <option value="csv">CSV File</option>
                            </select>
                        </div>

                        <div class="flex space-x-3 pt-4">
                            <button type="submit"
                                class="flex-1 bg-gradient-to-r from-primary-600 to-primary-700 text-white py-2 px-4 rounded-lg font-medium hover:from-primary-700 hover:to-primary-800 transition-all">
                                Generate Report
                            </button>
                            <button type="button" id="cancelReportModal"
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
        const generateReportBtn = document.getElementById('generateReportBtn');
        const generateReportModal = document.getElementById('generateReportModal');
        const closeReportModal = document.getElementById('closeReportModal');
        const cancelReportModal = document.getElementById('cancelReportModal');

        generateReportBtn.addEventListener('click', () => {
            generateReportModal.classList.remove('hidden');
        });

        closeReportModal.addEventListener('click', () => {
            generateReportModal.classList.add('hidden');
        });

        cancelReportModal.addEventListener('click', () => {
            generateReportModal.classList.add('hidden');
        });

        // Close modal on outside click
        generateReportModal.addEventListener('click', (e) => {
            if (e.target === generateReportModal) {
                generateReportModal.classList.add('hidden');
            }
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Charts
            // Attendance Trends Chart
            const attendanceTrendsCtx = document.getElementById('attendanceTrendsChart').getContext('2d');
            const attendanceTrendsChart = new Chart(attendanceTrendsCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                    datasets: [{
                        label: 'Attendance Rate %',
                        data: [82, 85, 78, 90, 87, 89, 87],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }, {
                        label: 'Target %',
                        data: [90, 90, 90, 90, 90, 90, 90],
                        borderColor: '#ef4444',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        pointHoverRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: '#f3f4f6'
                            },
                            ticks: {
                                callback: function (value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                    datasets: [{
                        label: 'Collections (RWF)',
                        data: [285000, 320000, 195000, 275000, 380000, 295000, 285000],
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: '#16a34a',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                    }, {
                        label: 'Outstanding (RWF)',
                        data: [125000, 98000, 156000, 110000, 85000, 120000, 145000],
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: '#dc2626',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f3f4f6'
                            },
                            ticks: {
                                callback: function (value) {
                                    return value / 1000 + 'K';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>

<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>