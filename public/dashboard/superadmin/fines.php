<!-- Header -->
<?php include __DIR__ . '/partials/header.php'; ?>

<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div id="main-content" class="content-transition lg:ml-64">
        <!-- Top Navigation -->
        <?php include __DIR__ . '/partials/top-nav.php'; ?>

        <!-- Fines Management Content -->
        <main class="p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 ml-4 lg:ml-0">Fines Management</h1>
                            <p class="text-gray-600 mt-1 ml-4 lg:ml-0">Monitor and manage Umuganda fines & payments</p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                            <button
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Export Report
                            </button>
                            <button id="addFineBtn"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg text-sm font-medium hover:from-primary-700 hover:to-primary-800 shadow-sm transition-all">
                                <i class="fas fa-plus mr-2"></i>
                                Add Fine
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Fines Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Outstanding -->
                    <div
                        class="bg-gradient-to-br from-white to-red-50 rounded-xl shadow-sm p-6 border border-red-100 hover:shadow-lg hover:border-red-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-red-600 uppercase tracking-wide">Outstanding Fines
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2">428K <span
                                        class="text-lg text-red-700 font-bold">RWF</span></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-danger-600 font-semibold bg-danger-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-exclamation-circle text-xs mr-1"></i>
                                        156
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">unpaid fines</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-danger-500 to-danger-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Collected This Month -->
                    <div
                        class="bg-gradient-to-br from-white to-green-50 rounded-xl shadow-sm p-6 border border-green-100 hover:shadow-lg hover:border-green-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-green-600 uppercase tracking-wide">Collected This
                                    Month</p>
                                <p class="text-3xl font-black text-gray-900 mt-2">285K <span
                                        class="text-lg text-green-700 font-bold">RWF</span></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-success-600 font-semibold bg-success-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-arrow-up text-xs mr-1"></i>
                                        +18%
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">from last month</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-success-500 to-success-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-money-check-alt text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Average Fine Amount -->
                    <div
                        class="bg-gradient-to-br from-white to-orange-50 rounded-xl shadow-sm p-6 border border-orange-100 hover:shadow-lg hover:border-orange-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Average Fine
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2">12K <span
                                        class="text-lg text-orange-700 font-bold">RWF</span></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-warning-600 font-semibold bg-warning-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-equals text-xs mr-1"></i>
                                        Standard
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">fine amount</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-calculator text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Rate -->
                    <div
                        class="bg-gradient-to-br from-white to-blue-50 rounded-xl shadow-sm p-6 border border-blue-100 hover:shadow-lg hover:border-blue-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Payment Rate</p>
                                <p class="text-3xl font-black text-gray-900 mt-2">73.2%</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-primary-600 font-semibold bg-primary-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-percentage text-xs mr-1"></i>
                                        Good
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">collection rate</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-chart-pie text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Fine Collections Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Fine Collections</h3>
                            <div class="flex space-x-2">
                                <button
                                    class="px-3 py-1 text-sm bg-primary-100 text-primary-700 rounded-md font-medium hover:bg-primary-200 transition-colors">6M</button>
                                <button
                                    class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-md transition-colors">1Y</button>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="collectionsChart"></canvas>
                        </div>
                    </div>

                    <!-- Fine Types Distribution -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Fine Types</h3>
                            <div class="flex space-x-2">
                                <button
                                    class="px-3 py-1 text-sm bg-primary-100 text-primary-700 rounded-md font-medium hover:bg-primary-200 transition-colors">This
                                    Month</button>
                                <button
                                    class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-md transition-colors">All
                                    Time</button>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="fineTypesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Filters and Controls -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <!-- Date Range Filter -->
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                                <div class="flex gap-2">
                                    <input type="date" value="2025-01-01"
                                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <span class="flex items-center text-gray-500">to</span>
                                    <input type="date" value="2025-07-26"
                                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                                <select
                                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="">All Status</option>
                                    <option value="unpaid">Unpaid</option>
                                    <option value="paid">Paid</option>
                                    <option value="partial">Partial</option>
                                    <option value="overdue">Overdue</option>
                                </select>
                            </div>

                            <!-- Fine Type Filter -->
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fine Type</label>
                                <select
                                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="">All Types</option>
                                    <option value="absence">Absence</option>
                                    <option value="late">Late Arrival</option>
                                    <option value="no-show">No Show</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-filter mr-2"></i>
                                Apply Filters
                            </button>
                            <button
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
                                Clear All
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Fines Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Fines & Payments</h3>
                            <div class="flex items-center space-x-3">
                                <div class="text-sm text-gray-600">
                                    Showing <span class="font-medium">1-10</span> of <span
                                        class="font-medium">156</span> fines
                                </div>
                                <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                                    <button
                                        class="px-3 py-1 text-xs bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button
                                        class="px-3 py-1 text-xs bg-white text-gray-700 hover:bg-gray-50 transition-colors">
                                        1
                                    </button>
                                    <button
                                        class="px-3 py-1 text-xs bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
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
                                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Resident
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fine Type
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Issue Date
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Unpaid Fine -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-gradient-to-br from-danger-500 to-danger-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                <span class="text-white text-sm font-semibold">PC</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Paul Cyiza</div>
                                                <div class="text-sm text-gray-500">ID: 001250 • Kicukiro</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-user-times mr-1"></i>
                                            Absence
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">15,000 RWF
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">July 20, 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-danger-100 text-danger-800">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            Unpaid
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-success-600 hover:text-success-900 mr-3"
                                            title="Mark as Paid">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <button class="text-primary-600 hover:text-primary-900 mr-3" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-gray-400 hover:text-gray-600" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Paid Fine -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-gradient-to-br from-success-500 to-success-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                <span class="text-white text-sm font-semibold">JB</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Jean Baptiste</div>
                                                <div class="text-sm text-gray-500">ID: 001248 • Nyarugenge</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Late Arrival
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">5,000 RWF
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">July 13, 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">
                                            <i class="fas fa-check mr-1"></i>
                                            Paid
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-primary-600 hover:text-primary-900 mr-3" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-gray-400 hover:text-gray-600" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Overdue Fine -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-gradient-to-br from-red-600 to-red-700 rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                <span class="text-white text-sm font-semibold">EK</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Eric Kamanzi</div>
                                                <div class="text-sm text-gray-500">ID: 001252 • Gasabo</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-ban mr-1"></i>
                                            No Show
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">25,000 RWF
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">June 29, 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Overdue
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-success-600 hover:text-success-900 mr-3"
                                            title="Mark as Paid">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <button class="text-primary-600 hover:text-primary-900 mr-3" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-gray-400 hover:text-gray-600" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Partial Payment -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                <span class="text-white text-sm font-semibold">AN</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Alice Nyiraneza</div>
                                                <div class="text-sm text-gray-500">ID: 001251 • Gasabo</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-user-times mr-1"></i>
                                            Absence
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">15,000 RWF
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">July 06, 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-coins mr-1"></i>
                                            Partial (10K)
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-success-600 hover:text-success-900 mr-3"
                                            title="Complete Payment">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <button class="text-primary-600 hover:text-primary-900 mr-3" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-gray-400 hover:text-gray-600" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Recent Fine -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                <span class="text-white text-sm font-semibold">SM</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Sarah Mukamana</div>
                                                <div class="text-sm text-gray-500">ID: 001247 • Gasabo</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Late Arrival
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">5,000 RWF
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">July 26, 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">
                                            <i class="fas fa-hourglass-half mr-1"></i>
                                            Recent
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-success-600 hover:text-success-900 mr-3"
                                            title="Mark as Paid">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <button class="text-primary-600 hover:text-primary-900 mr-3" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-gray-400 hover:text-gray-600" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bulk Actions Footer -->
                <div class="mt-6 bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-600">Bulk Actions:</span>
                            <div class="flex space-x-2">
                                <button
                                    class="px-3 py-2 text-sm bg-success-100 text-success-700 rounded-md hover:bg-success-200 transition-colors">
                                    <i class="fas fa-check mr-1"></i>
                                    Mark as Paid
                                </button>
                                <button
                                    class="px-3 py-2 text-sm bg-warning-100 text-warning-700 rounded-md hover:bg-warning-200 transition-colors">
                                    <i class="fas fa-paper-plane mr-1"></i>
                                    Send Reminder
                                </button>
                                <button
                                    class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-download mr-1"></i>
                                    Export Selected
                                </button>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">0</span> selected fines
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Fine Modal -->
    <div id="addFineModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Add New Fine</h3>
                        <button id="closeFineModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Resident ID</label>
                            <input type="text" placeholder="Enter resident ID"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fine Type</label>
                            <select
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="absence">Absence (15,000 RWF)</option>
                                <option value="late">Late Arrival (5,000 RWF)</option>
                                <option value="no-show">No Show (25,000 RWF)</option>
                                <option value="other">Other (Custom Amount)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount (RWF)</label>
                            <input type="number" value="15000" step="1000"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Incident Date</label>
                            <input type="date" value="2025-07-26"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                            <textarea rows="3" placeholder="Reason for fine..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"></textarea>
                        </div>

                        <div class="flex space-x-3 pt-4">
                            <button type="submit"
                                class="flex-1 bg-gradient-to-r from-primary-600 to-primary-700 text-white py-2 px-4 rounded-lg font-medium hover:from-primary-700 hover:to-primary-800 transition-all">
                                Add Fine
                            </button>
                            <button type="button" id="cancelFineModal"
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
        const addFineBtn = document.getElementById('addFineBtn');
        const addFineModal = document.getElementById('addFineModal');
        const closeFineModal = document.getElementById('closeFineModal');
        const cancelFineModal = document.getElementById('cancelFineModal');

        addFineBtn.addEventListener('click', () => {
            addFineModal.classList.remove('hidden');
        });

        closeFineModal.addEventListener('click', () => {
            addFineModal.classList.add('hidden');
        });

        cancelFineModal.addEventListener('click', () => {
            addFineModal.classList.add('hidden');
        });

        // Close modal on outside click
        addFineModal.addEventListener('click', (e) => {
            if (e.target === addFineModal) {
                addFineModal.classList.add('hidden');
            }
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Charts
            // Fine Collections Chart
            const collectionsCtx = document.getElementById('collectionsChart').getContext('2d');
            const collectionsChart = new Chart(collectionsCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Collections (RWF)',
                        data: [285000, 320000, 195000, 275000, 380000, 285000],
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: '#16a34a',
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f3f4f6',
                                borderDash: [2, 2]
                            },
                            ticks: {
                                callback: function (value) {
                                    return value / 1000 + 'K';
                                },
                                color: '#6b7280',
                                font: {
                                    size: 12
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6b7280',
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });

            // Fine Types Chart
            const fineTypesCtx = document.getElementById('fineTypesChart').getContext('2d');
            const fineTypesChart = new Chart(fineTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Absence', 'Late Arrival', 'No Show', 'Other'],
                    datasets: [{
                        data: [65, 25, 8, 2],
                        backgroundColor: ['#ef4444', '#f59e0b', '#f97316', '#6b7280'],
                        borderWidth: 0,
                        cutout: '65%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 12
                                },
                                color: '#6b7280'
                            }
                        }
                    }
                }
            });
        });
    </script>


<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>