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
                    <h1 class="text-2xl font-bold text-gray-900 ml-4 lg:ml-0">Dashboard Overview</h1>
                </div>
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Residents -->
                    <div
                        class="bg-gradient-to-br from-white to-blue-50 rounded-xl shadow-sm p-6 border border-blue-100 hover:shadow-lg hover:border-blue-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Total Residents
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2">1,247</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-success-600 font-semibold bg-success-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-arrow-up text-xs mr-1"></i>
                                        +12
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">this month</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-users text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Rate -->
                    <div
                        class="bg-gradient-to-br from-white to-green-50 rounded-xl shadow-sm p-6 border border-green-100 hover:shadow-lg hover:border-green-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-green-600 uppercase tracking-wide">Attendance Rate
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2">87.3%</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-success-600 font-semibold bg-success-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-arrow-up text-xs mr-1"></i>
                                        +2.1%
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">from last session</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-success-500 to-success-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-chart-line text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Unpaid Fines -->
                    <div
                        class="bg-gradient-to-br from-white to-orange-50 rounded-xl shadow-sm p-6 border border-orange-100 hover:shadow-lg hover:border-orange-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Unpaid Fines
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2">428K <span
                                        class="text-lg text-orange-700 font-bold">RWF</span></p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-danger-600 font-semibold bg-danger-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-exclamation-circle text-xs mr-1"></i>
                                        156
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">residents owe fines</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Next Umuganda -->
                    <div
                        class="bg-gradient-to-br from-white to-purple-50 rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-lg hover:border-purple-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-purple-600 uppercase tracking-wide">Next Umuganda
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2">July 27</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-primary-600 font-semibold bg-primary-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-calendar text-xs mr-1"></i>
                                        2025
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">Saturday</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-calendar-alt text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Attendance Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Attendance Trends</h3>
                            <div class="flex space-x-2">
                                <button
                                    class="px-3 py-1 text-sm bg-primary-100 text-primary-700 rounded-md font-medium hover:bg-primary-200 transition-colors">6M</button>
                                <button
                                    class="px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 rounded-md transition-colors">1Y</button>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>

                    <!-- Fines Distribution -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Fines Distribution</h3>
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
                            <canvas id="finesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Data Tables Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Residents -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Residents</h3>
                                <button
                                    class="text-sm text-primary-600 hover:text-primary-700 font-medium transition-colors">View
                                    All</button>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Resident</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cell</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                    <span class="text-white text-sm font-semibold">SM</span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Sarah Mukamana</div>
                                                    <div class="text-sm text-gray-500">ID: 001247</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">Gasabo
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">Active</span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-10 h-10 bg-gradient-to-br from-warning-500 to-warning-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                    <span class="text-white text-sm font-semibold">JB</span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Jean Baptiste</div>
                                                    <div class="text-sm text-gray-500">ID: 001248</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            Nyarugenge</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">Fine
                                                Due</span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-10 h-10 bg-gradient-to-br from-success-500 to-success-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                    <span class="text-white text-sm font-semibold">MC</span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Marie Claire</div>
                                                    <div class="text-sm text-gray-500">ID: 001249</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            Kicukiro</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">Active</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Outstanding Fines -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Outstanding Fines</h3>
                                <button
                                    class="text-sm text-primary-600 hover:text-primary-700 font-medium transition-colors">View
                                    All</button>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Resident</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reason</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-10 h-10 bg-gradient-to-br from-danger-500 to-danger-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                    <span class="text-white text-sm font-semibold">PC</span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Paul Cyiza</div>
                                                    <div class="text-sm text-gray-500">ID: 001250</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">15,000
                                            RWF</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-danger-100 text-danger-800">Absence</span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-10 h-10 bg-gradient-to-br from-warning-500 to-warning-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                    <span class="text-white text-sm font-semibold">AN</span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Alice Nyiraneza</div>
                                                    <div class="text-sm text-gray-500">ID: 001251</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">5,000
                                            RWF</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">Late
                                                Arrival</span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-10 h-10 bg-gradient-to-br from-danger-500 to-danger-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                    <span class="text-white text-sm font-semibold">EK</span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Eric Kamanzi</div>
                                                    <div class="text-sm text-gray-500">ID: 001252</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">25,000
                                            RWF</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-danger-100 text-danger-800">No
                                                Show</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Charts
            // Attendance Chart
            const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
            const attendanceChart = new Chart(attendanceCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Attendance Rate %',
                        data: [82, 85, 78, 90, 87, 89],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
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
                            max: 100,
                            grid: {
                                color: '#f3f4f6',
                                borderDash: [2, 2]
                            },
                            ticks: {
                                callback: function (value) {
                                    return value + '%';
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
                    },
                    elements: {
                        point: {
                            hoverRadius: 8
                        }
                    }
                }
            });

            // Fines Distribution Chart
            const finesCtx = document.getElementById('finesChart').getContext('2d');
            const finesChart = new Chart(finesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Absence Fines', 'Late Arrival', 'No Show', 'Paid'],
                    datasets: [{
                        data: [128500, 85000, 75000, 340000],
                        backgroundColor: ['#ef4444', '#f59e0b', '#f97316', '#22c55e'],
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
