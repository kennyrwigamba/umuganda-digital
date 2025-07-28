<!-- Header -->
<?php include __DIR__ . '/partials/header.php'; ?>

<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div id="main-content" class="content-transition lg:ml-64">
        <!-- Top Navigation -->
        <?php include __DIR__ . '/partials/top-nav.php'; ?>

        <!-- Events Management Content -->
        <main class="p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 ml-4 lg:ml-0">Umuganda Events</h1>
                            <p class="text-gray-600 mt-1 ml-4 lg:ml-0">Schedule and manage community events</p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                            <button
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Export Events
                            </button>
                            <button id="createEventBtn"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg text-sm font-medium hover:from-primary-700 hover:to-primary-800 shadow-sm transition-all">
                                <i class="fas fa-plus mr-2"></i>
                                Create Event
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Events Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Events -->
                    <div
                        class="bg-gradient-to-br from-white to-blue-50 rounded-xl shadow-sm p-6 border border-blue-100 hover:shadow-lg hover:border-blue-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Total Events</p>
                                <p class="text-3xl font-black text-gray-900 mt-2">24</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-primary-600 font-semibold bg-primary-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-calendar text-xs mr-1"></i>
                                        This Year
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">2025</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-calendar-alt text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div
                        class="bg-gradient-to-br from-white to-green-50 rounded-xl shadow-sm p-6 border border-green-100 hover:shadow-lg hover:border-green-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-green-600 uppercase tracking-wide">Upcoming Events
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2">3</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-success-600 font-semibold bg-success-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-clock text-xs mr-1"></i>
                                        Next 30 days
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">scheduled</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-success-500 to-success-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-arrow-up text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Average Attendance -->
                    <div
                        class="bg-gradient-to-br from-white to-purple-50 rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-lg hover:border-purple-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-purple-600 uppercase tracking-wide">Avg. Attendance
                                </p>
                                <p class="text-3xl font-black text-gray-900 mt-2">87.3%</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-purple-600 font-semibold bg-purple-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-users text-xs mr-1"></i>
                                        1,089
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">avg. present</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-user-check text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Next Event -->
                    <div
                        class="bg-gradient-to-br from-white to-orange-50 rounded-xl shadow-sm p-6 border border-orange-100 hover:shadow-lg hover:border-orange-200 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Next Event</p>
                                <p class="text-3xl font-black text-gray-900 mt-2">Jul 26</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-warning-600 font-semibold bg-warning-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-clock text-xs mr-1"></i>
                                        Tomorrow
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2 font-medium">08:00 AM</span>
                                </div>
                            </div>
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-calendar-day text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- View Toggle and Filters -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <!-- View Toggle -->
                            <div class="flex bg-gray-100 rounded-lg p-1">
                                <button id="calendarView"
                                    class="px-4 py-2 text-sm font-medium bg-white text-gray-900 rounded-md shadow-sm transition-all">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    Calendar
                                </button>
                                <button id="listView"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-all">
                                    <i class="fas fa-list mr-2"></i>
                                    List
                                </button>
                            </div>

                            <!-- Date Navigation -->
                            <div class="flex items-center space-x-2">
                                <button id="prevMonth"
                                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <h3 id="currentMonth" class="text-lg font-semibold text-gray-900 min-w-40 text-center">
                                    July 2025</h3>
                                <button id="nextMonth"
                                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- Status Filter -->
                            <div class="relative">
                                <select
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="">All Events</option>
                                    <option value="upcoming">Upcoming</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <button
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
                                Today
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Calendar View -->
                <div id="calendarContainer"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                    <!-- Calendar Header -->
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="calendar-grid">
                            <div class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Sun</div>
                            <div class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Mon</div>
                            <div class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Tue</div>
                            <div class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Wed</div>
                            <div class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Thu</div>
                            <div class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Fri</div>
                            <div class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Sat</div>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="calendar-grid">
                        <!-- Week 1 -->
                        <div class="calendar-day other-month">
                            <div class="text-sm text-gray-400">29</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="text-sm text-gray-400">30</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">1</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">2</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">3</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">4</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">5</div>
                            <div class="mt-2">
                                <div class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded truncate">
                                    <span class="event-dot bg-blue-500"></span>Umuganda
                                </div>
                            </div>
                        </div>

                        <!-- Week 2 -->
                        <div class="calendar-day">
                            <div class="text-sm font-medium">6</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">7</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">8</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">9</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">10</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">11</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">12</div>
                            <div class="mt-2">
                                <div class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded truncate">
                                    <span class="event-dot bg-blue-500"></span>Umuganda
                                </div>
                            </div>
                        </div>

                        <!-- Week 3 -->
                        <div class="calendar-day">
                            <div class="text-sm font-medium">13</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">14</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">15</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">16</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">17</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">18</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">19</div>
                            <div class="mt-2">
                                <div class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded truncate">
                                    <span class="event-dot bg-blue-500"></span>Umuganda
                                </div>
                            </div>
                        </div>

                        <!-- Week 4 -->
                        <div class="calendar-day">
                            <div class="text-sm font-medium">20</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">21</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">22</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">23</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">24</div>
                        </div>
                        <div class="calendar-day today">
                            <div class="text-sm font-medium">25</div>
                            <div class="absolute top-2 right-2">
                                <div class="w-2 h-2 bg-primary-500 rounded-full"></div>
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">26</div>
                            <div class="mt-2">
                                <div class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded truncate">
                                    <span class="event-dot bg-green-500"></span>Umuganda
                                </div>
                                <div class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded truncate mt-1">
                                    <span class="event-dot bg-orange-500"></span>Training
                                </div>
                            </div>
                        </div>

                        <!-- Week 5 -->
                        <div class="calendar-day">
                            <div class="text-sm font-medium">27</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">28</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">29</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">30</div>
                        </div>
                        <div class="calendar-day">
                            <div class="text-sm font-medium">31</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="text-sm text-gray-400">1</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="text-sm text-gray-400">2</div>
                            <div class="mt-2">
                                <div class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded truncate">
                                    <span class="event-dot bg-blue-500"></span>Umuganda
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List View (Hidden by default) -->
                <div id="listContainer"
                    class="hidden bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Events List</h3>
                            <div class="text-sm text-gray-600">
                                <span class="font-medium">8</span> events this month
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-200">
                        <!-- Upcoming Event -->
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-sm">
                                        <i class="fas fa-calendar-check text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900">Monthly Umuganda</h4>
                                        <p class="text-sm text-gray-600">Community cleaning and development activities
                                        </p>
                                        <div class="flex items-center mt-2 space-x-4">
                                            <span class="text-sm text-gray-500">
                                                <i class="fas fa-calendar mr-1"></i>
                                                July 26, 2025
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                <i class="fas fa-clock mr-1"></i>
                                                08:00 AM - 11:00 AM
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                All Cells
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span
                                        class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Upcoming
                                    </span>
                                    <div class="flex space-x-2">
                                        <button class="text-primary-600 hover:text-primary-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Training Event -->
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-sm">
                                        <i class="fas fa-chalkboard-teacher text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900">Community Leadership Training
                                        </h4>
                                        <p class="text-sm text-gray-600">Training session for cell leaders and
                                            coordinators</p>
                                        <div class="flex items-center mt-2 space-x-4">
                                            <span class="text-sm text-gray-500">
                                                <i class="fas fa-calendar mr-1"></i>
                                                July 26, 2025
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                <i class="fas fa-clock mr-1"></i>
                                                02:00 PM - 04:00 PM
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                Community Center
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span
                                        class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                        Upcoming
                                    </span>
                                    <div class="flex space-x-2">
                                        <button class="text-primary-600 hover:text-primary-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Completed Event -->
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-sm">
                                        <i class="fas fa-check-circle text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900">Monthly Umuganda</h4>
                                        <p class="text-sm text-gray-600">Community cleaning and development activities
                                        </p>
                                        <div class="flex items-center mt-2 space-x-4">
                                            <span class="text-sm text-gray-500">
                                                <i class="fas fa-calendar mr-1"></i>
                                                July 19, 2025
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                <i class="fas fa-clock mr-1"></i>
                                                08:00 AM - 11:00 AM
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                <i class="fas fa-users mr-1"></i>
                                                1,089 attended
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span
                                        class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Completed
                                    </span>
                                    <div class="flex space-x-2">
                                        <button class="text-primary-600 hover:text-primary-900">
                                            <i class="fas fa-chart-bar"></i>
                                        </button>
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Event Templates -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-layer-group text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Event Templates</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Create events from predefined templates</p>
                        <div class="space-y-2">
                            <button
                                class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="fas fa-broom mr-2 text-gray-400"></i>
                                Regular Umuganda
                            </button>
                            <button
                                class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="fas fa-graduation-cap mr-2 text-gray-400"></i>
                                Training Session
                            </button>
                            <button
                                class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="fas fa-calendar-plus mr-2 text-gray-400"></i>
                                Special Event
                            </button>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-history text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                <span class="text-gray-600">July 19 event completed</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                <span class="text-gray-600">July 26 event scheduled</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <div class="w-2 h-2 bg-orange-500 rounded-full mr-3"></div>
                                <span class="text-gray-600">Training event added</span>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-bell text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                        </div>
                        <div class="space-y-3">
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-sm text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Tomorrow's event needs confirmation
                                </p>
                            </div>
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Send reminders to residents
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Create Event Modal -->
    <div id="createEventModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Create New Event</h3>
                        <button id="closeEventModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Title</label>
                            <input type="text" placeholder="Enter event title"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Type</label>
                            <select
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="umuganda">Regular Umuganda</option>
                                <option value="training">Training Session</option>
                                <option value="meeting">Community Meeting</option>
                                <option value="special">Special Event</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                <input type="date" value="2025-07-26"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                                <input type="time" value="08:00"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Duration (hours)</label>
                            <input type="number" value="3" min="1" max="12"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <select
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="all">All Cells</option>
                                <option value="gasabo">Gasabo Cell</option>
                                <option value="nyarugenge">Nyarugenge Cell</option>
                                <option value="kicukiro">Kicukiro Cell</option>
                                <option value="center">Community Center</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea rows="3" placeholder="Event description and activities..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"></textarea>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="sendNotifications"
                                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <label for="sendNotifications" class="ml-2 text-sm text-gray-700">Send notifications to all
                                residents</label>
                        </div>

                        <div class="flex space-x-3 pt-4">
                            <button type="submit"
                                class="flex-1 bg-gradient-to-r from-primary-600 to-primary-700 text-white py-2 px-4 rounded-lg font-medium hover:from-primary-700 hover:to-primary-800 transition-all">
                                Create Event
                            </button>
                            <button type="button" id="cancelEventModal"
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

        // View toggle functionality
        const calendarView = document.getElementById('calendarView');
        const listView = document.getElementById('listView');
        const calendarContainer = document.getElementById('calendarContainer');
        const listContainer = document.getElementById('listContainer');

        calendarView.addEventListener('click', () => {
            calendarView.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
            calendarView.classList.remove('text-gray-700');
            listView.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
            listView.classList.add('text-gray-700');

            calendarContainer.classList.remove('hidden');
            listContainer.classList.add('hidden');
        });

        listView.addEventListener('click', () => {
            listView.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
            listView.classList.remove('text-gray-700');
            calendarView.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
            calendarView.classList.add('text-gray-700');

            listContainer.classList.remove('hidden');
            calendarContainer.classList.add('hidden');
        });

        // Modal functionality
        const createEventBtn = document.getElementById('createEventBtn');
        const createEventModal = document.getElementById('createEventModal');
        const closeEventModal = document.getElementById('closeEventModal');
        const cancelEventModal = document.getElementById('cancelEventModal');

        createEventBtn.addEventListener('click', () => {
            createEventModal.classList.remove('hidden');
        });

        closeEventModal.addEventListener('click', () => {
            createEventModal.classList.add('hidden');
        });

        cancelEventModal.addEventListener('click', () => {
            createEventModal.classList.add('hidden');
        });

        // Close modal on outside click
        createEventModal.addEventListener('click', (e) => {
            if (e.target === createEventModal) {
                createEventModal.classList.add('hidden');
            }
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            initializeSidebar();
        });
    </script>
</body>

</html>