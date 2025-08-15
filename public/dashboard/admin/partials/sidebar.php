<?php
    function active_link($link = null)
    {
        $current_path = $_SERVER['REQUEST_URI'];

        if ($current_path === '/public/dashboard/admin/' . $link) {
            return 'text-white font-semibold bg-gradient-to-r from-primary-600 to-primary-700 rounded-lg shadow-md hover:from-primary-700 hover:to-primary-800';
        } else {
            return 'text-gray-600 font-medium hover:text-primary-700 hover:bg-gradient-to-r hover:from-primary-50 hover:to-primary-100';
        }

    }

?>

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

<!-- Sidebar -->
<aside id="sidebar"
    class="fixed top-0 left-0 z-50 w-64 h-screen bg-white border-r border-gray-200 transform sidebar-transition">
    <!-- Logo Section -->
    <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            <div
                class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-users text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Umuganda</h1>
                <p class="text-sm text-gray-500">Admin Dashboard</p>
            </div>
        </div>
        <button id="close-sidebar"
            class="lg:hidden p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="mt-6 px-4 pb-20">
        <div class="space-y-2">
            <!-- Dashboard -->
            <a href="index.php"
                class="<?php echo active_link('index.php') ?> group flex items-center px-4 py-3 text-sm rounded-lg transition-all duration-200">
                <i class="fas fa-tachometer-alt mr-3 text-lg"></i>
                Dashboard
            </a>

            <!-- Residents Management -->
            <a href="manage-residents.php"
                class="<?php echo active_link('manage-residents.php') ?> group flex items-center px-4 py-3 text-sm rounded-lg transition-all duration-200">
                <i
                    class="fas fa-users mr-3 text-lg"></i>
                Manage Residents
            </a>

            <!-- Attendance Tracking Dropdown -->
            <div class="relative">
                <div class="<?php echo(strpos($_SERVER['REQUEST_URI'], 'attendance') !== false || strpos($_SERVER['REQUEST_URI'], 'qr') !== false) ? 'text-white font-semibold bg-gradient-to-r from-primary-600 to-primary-700 rounded-lg shadow-md' : 'text-gray-600 font-medium hover:text-primary-700 hover:bg-gradient-to-r hover:from-primary-50 hover:to-primary-100' ?> group flex items-center justify-between w-full px-4 py-3 text-sm rounded-lg transition-all duration-200">
                    <a href="attendance-tracking.php" class="flex items-center flex-1">
                        <i class="fas fa-clipboard-check mr-3 text-lg"></i>
                        Attendance Tracking
                    </a>
                    <button onclick="toggleDropdown('attendanceDropdown')" class="p-1 hover:bg-black hover:bg-opacity-10 rounded transition-colors">
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" id="attendanceChevron"></i>
                    </button>
                </div>
                <div id="attendanceDropdown" class="hidden mt-1 ml-6 space-y-1">
                    <a href="attendance-marking.php"
                        class="<?php echo active_link('attendance-marking.php') ?> group flex items-center px-4 py-2 text-sm rounded-lg transition-all duration-200">
                        <i class="fas fa-check-circle mr-3 text-base"></i>
                        Attendance Marking
                    </a>
                    <a href="qr-generator.php"
                        class="<?php echo active_link('qr-generator.php') ?> group flex items-center px-4 py-2 text-sm rounded-lg transition-all duration-200">
                        <i class="fas fa-qrcode mr-3 text-base"></i>
                        QR Generator
                    </a>
                </div>
            </div>

            <!-- Fines & Payments -->
            <a href="fines.php"
                class="<?php echo active_link('fines.php') ?> group flex items-center px-4 py-3 text-sm rounded-lg transition-all duration-200">
                <i
                    class="fas fa-money-bill-wave mr-3 text-lg"></i>
                Fines Management
            </a>

            <!-- Events -->
            <a href="umuganda-events.php"
                class="<?php echo active_link('umuganda-events.php') ?> group flex items-center px-4 py-3 text-sm rounded-lg transition-all duration-200">
                <i
                    class="fas fa-calendar-alt mr-3 text-lg"></i>
                Umuganda Events
            </a>

            <!-- Reports -->
            <a href="reports.php"
                class="<?php echo active_link('reports.php') ?> group flex items-center px-4 py-3 text-sm rounded-lg transition-all duration-200">
                <i
                    class="fas fa-chart-bar mr-3 text-lg"></i>
                Reports & Analytics
            </a>

            <!-- Notices -->
            <a href="notices.php"
                class="<?php echo active_link('notices.php') ?> group flex items-center px-4 py-3 text-sm rounded-lg transition-all duration-200">
                <i
                    class="fas fa-bullhorn mr-3 text-lg"></i>
                Community Notices
            </a>

            <!-- Settings -->
            <a href="settings.php"
                class="<?php echo active_link('settings.php') ?> group flex items-center px-4 py-3 text-sm rounded-lg transition-all duration-200">
                <i
                    class="fas fa-cog mr-3 text-lg"></i>
                Settings
            </a>
        </div>
    </nav>
</aside>

<script>
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const chevron = document.getElementById(dropdownId.replace('Dropdown', 'Chevron'));

    if (dropdown.classList.contains('hidden')) {
        dropdown.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        dropdown.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Auto-expand dropdown if we're on one of the child pages
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    if (currentPath.includes('attendance') || currentPath.includes('qr')) {
        const attendanceDropdown = document.getElementById('attendanceDropdown');
        const attendanceChevron = document.getElementById('attendanceChevron');
        if (attendanceDropdown && attendanceChevron) {
            attendanceDropdown.classList.remove('hidden');
            attendanceChevron.style.transform = 'rotate(180deg)';
        }
    }
});
</script>