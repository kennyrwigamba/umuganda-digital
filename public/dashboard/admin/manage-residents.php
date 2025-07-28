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
                            <p class="mt-2 text-sm text-gray-600">Add, edit, and manage community residents</p>
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

                    <!-- Active Residents -->
                    <div
                        class="bg-gradient-to-br from-white to-green-50 rounded-xl shadow-sm p-6 border border-green-100 hover:shadow-lg hover:border-green-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-green-600 uppercase tracking-wide">Active Residents
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2">1,189</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-success-600 font-semibold bg-success-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-check-circle text-xs mr-1"></i>
                                        95.3%
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
                                <p class="text-3xl font-black text-gray-900 mt-2">23</p>
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
                                <p class="text-3xl font-black text-gray-900 mt-2">35</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-danger-600 font-semibold bg-danger-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-user-times text-xs mr-1"></i>
                                        2.8%
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
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                            <!-- Status Filter -->
                            <select
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option>All Status</option>
                                <option>Active</option>
                                <option>Inactive</option>
                                <option>Pending</option>
                            </select>

                            <!-- Cell Filter -->
                            <select
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option>All Cells</option>
                                <option>Gasabo</option>
                                <option>Nyarugenge</option>
                                <option>Kicukiro</option>
                            </select>

                            <!-- Date Filter -->
                            <input type="date"
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div class="flex space-x-2">
                            <button
                                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Export
                            </button>
                            <button
                                class="inline-flex items-center px-4 py-2 bg-primary-100 hover:bg-primary-200 text-primary-700 font-medium rounded-lg transition-colors">
                                <i class="fas fa-filter mr-2"></i>
                                Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Residents Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Residents Directory</h3>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500">Showing 1-20 of 1,247</span>
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
                                <!-- Resident 1 -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    </td>
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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">sarah.mukamana@email.com</div>
                                        <div class="text-sm text-gray-500">+250 788 123 456</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Gasabo
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jan 15, 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button
                                                class="text-primary-600 hover:text-primary-900 transition-colors edit-btn"
                                                data-id="001247" data-name="Sarah Mukamana"
                                                data-email="sarah.mukamana@email.com" data-phone="+250 788 123 456"
                                                data-cell="Gasabo" data-status="Active">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                class="text-success-600 hover:text-success-900 transition-colors view-btn"
                                                data-id="001247" data-name="Sarah Mukamana"
                                                data-email="sarah.mukamana@email.com" data-phone="+250 788 123 456"
                                                data-cell="Gasabo" data-status="Active" data-date="Jan 15, 2025">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button
                                                class="text-danger-600 hover:text-danger-900 transition-colors delete-btn"
                                                data-id="001247" data-name="Sarah Mukamana">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Resident 2 -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    </td>
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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">jean.baptiste@email.com</div>
                                        <div class="text-sm text-gray-500">+250 788 234 567</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Nyarugenge
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jan 10, 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">Pending</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button
                                                class="text-primary-600 hover:text-primary-900 transition-colors edit-btn"
                                                data-id="001248" data-name="Jean Baptiste"
                                                data-email="jean.baptiste@email.com" data-phone="+250 788 234 567"
                                                data-cell="Nyarugenge" data-status="Pending">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                class="text-success-600 hover:text-success-900 transition-colors view-btn"
                                                data-id="001248" data-name="Jean Baptiste"
                                                data-email="jean.baptiste@email.com" data-phone="+250 788 234 567"
                                                data-cell="Nyarugenge" data-status="Pending" data-date="Jan 10, 2025">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button
                                                class="text-danger-600 hover:text-danger-900 transition-colors delete-btn"
                                                data-id="001248" data-name="Jean Baptiste">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Resident 3 -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    </td>
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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">marie.claire@email.com</div>
                                        <div class="text-sm text-gray-500">+250 788 345 678</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kicukiro
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Dec 28, 2024</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button
                                                class="text-primary-600 hover:text-primary-900 transition-colors edit-btn"
                                                data-id="001249" data-name="Marie Claire"
                                                data-email="marie.claire@email.com" data-phone="+250 788 345 678"
                                                data-cell="Kicukiro" data-status="Active">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                class="text-success-600 hover:text-success-900 transition-colors view-btn"
                                                data-id="001249" data-name="Marie Claire"
                                                data-email="marie.claire@email.com" data-phone="+250 788 345 678"
                                                data-cell="Kicukiro" data-status="Active" data-date="Dec 28, 2024">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button
                                                class="text-danger-600 hover:text-danger-900 transition-colors delete-btn"
                                                data-id="001249" data-name="Marie Claire">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Resident 4 -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    </td>
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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">paul.cyiza@email.com</div>
                                        <div class="text-sm text-gray-500">+250 788 456 789</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Gasabo
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Dec 20, 2024</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-danger-100 text-danger-800">Inactive</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button
                                                class="text-primary-600 hover:text-primary-900 transition-colors edit-btn"
                                                data-id="001250" data-name="Paul Cyiza"
                                                data-email="paul.cyiza@email.com" data-phone="+250 788 456 789"
                                                data-cell="Gasabo" data-status="Inactive">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                class="text-success-600 hover:text-success-900 transition-colors view-btn"
                                                data-id="001250" data-name="Paul Cyiza"
                                                data-email="paul.cyiza@email.com" data-phone="+250 788 456 789"
                                                data-cell="Gasabo" data-status="Inactive" data-date="Dec 20, 2024">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button
                                                class="text-danger-600 hover:text-danger-900 transition-colors delete-btn"
                                                data-id="001250" data-name="Paul Cyiza">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Resident 5 -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                <span class="text-white text-sm font-semibold">AN</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Alice Nyiraneza</div>
                                                <div class="text-sm text-gray-500">ID: 001251</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">alice.nyiraneza@email.com</div>
                                        <div class="text-sm text-gray-500">+250 788 567 890</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kicukiro
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Dec 15, 2024</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button
                                                class="text-primary-600 hover:text-primary-900 transition-colors edit-btn"
                                                data-id="001251" data-name="Alice Nyiraneza"
                                                data-email="alice.nyiraneza@email.com" data-phone="+250 788 567 890"
                                                data-cell="Kicukiro" data-status="Active">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                class="text-success-600 hover:text-success-900 transition-colors view-btn"
                                                data-id="001251" data-name="Alice Nyiraneza"
                                                data-email="alice.nyiraneza@email.com" data-phone="+250 788 567 890"
                                                data-cell="Kicukiro" data-status="Active" data-date="Dec 15, 2024">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button
                                                class="text-danger-600 hover:text-danger-900 transition-colors delete-btn"
                                                data-id="001251" data-name="Alice Nyiraneza">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of
                                <span class="font-medium">1,247</span> results
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-100 transition-colors">Previous</button>
                                <button
                                    class="px-3 py-1 text-sm bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors">1</button>
                                <button
                                    class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-100 transition-colors">2</button>
                                <button
                                    class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-100 transition-colors">3</button>
                                <button
                                    class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-100 transition-colors">Next</button>
                            </div>
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
                        <input type="text" id="addName"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="addEmail"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="addPhone"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cell</label>
                        <select id="addCell"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Cell</option>
                            <option value="Gasabo">Gasabo</option>
                            <option value="Nyarugenge">Nyarugenge</option>
                            <option value="Kicukiro">Kicukiro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">National ID</label>
                        <input type="text" id="addNationalId"
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
                        <input type="text" id="editName"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="editEmail"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="editPhone"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cell</label>
                        <select id="editCell"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            <option value="Gasabo">Gasabo</option>
                            <option value="Nyarugenge">Nyarugenge</option>
                            <option value="Kicukiro">Kicukiro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="editStatus"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            <option value="Active">Active</option>
                            <option value="Pending">Pending</option>
                            <option value="Inactive">Inactive</option>
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

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            initializeSidebar();
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
                // Here you would typically send data to server
                console.log('Adding new resident...');
                addResidentModal.classList.add('hidden');
                clearAddForm();
                // Show success message (you can implement this)
                alert('Resident added successfully!');
            });

            // Edit buttons
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const data = this.dataset;
                    document.getElementById('editName').value = data.name;
                    document.getElementById('editEmail').value = data.email;
                    document.getElementById('editPhone').value = data.phone;
                    document.getElementById('editCell').value = data.cell;
                    document.getElementById('editStatus').value = data.status;
                    editResidentModal.classList.remove('hidden');
                });
            });

            document.getElementById('cancelEditResident').addEventListener('click', function () {
                editResidentModal.classList.add('hidden');
            });

            document.getElementById('confirmEditResident').addEventListener('click', function () {
                // Here you would typically send updated data to server
                console.log('Updating resident...');
                editResidentModal.classList.add('hidden');
                alert('Resident updated successfully!');
            });

            // View buttons
            document.querySelectorAll('.view-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const data = this.dataset;
                    document.getElementById('viewName').textContent = data.name;
                    document.getElementById('viewId').textContent = data.id;
                    document.getElementById('viewEmail').textContent = data.email;
                    document.getElementById('viewPhone').textContent = data.phone;
                    document.getElementById('viewCell').textContent = data.cell;
                    document.getElementById('viewDate').textContent = data.date;

                    const statusElement = document.getElementById('viewStatus');
                    statusElement.textContent = data.status;
                    statusElement.className = 'text-sm inline-flex px-3 py-1 text-xs font-semibold rounded-full ' +
                        (data.status === 'Active' ? 'bg-success-100 text-success-800' :
                            data.status === 'Pending' ? 'bg-warning-100 text-warning-800' :
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
                // Here you would typically send delete request to server
                console.log('Deleting resident...');
                deleteResidentModal.classList.add('hidden');
                alert('Resident deleted successfully!');
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
    </script>
</body>

</html>