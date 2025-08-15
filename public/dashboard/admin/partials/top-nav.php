<!-- Top Header -->
<header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Mobile menu button -->
            <button id="mobile-menu-btn"
                class="lg:hidden p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors">
                <i class="fas fa-bars text-xl"></i>
            </button>

            <!-- Desktop Sidebar Toggle -->
            <button id="desktop-sidebar-toggle"
                class="hidden lg:block p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors mr-4">
                <i class="fas fa-bars text-xl"></i>
            </button>

            <!-- Search -->
            <div class="relative hidden md:block">
                <input type="text" placeholder="Search residents..."
                    class="w-80 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center space-x-4">


                <!-- Notifications -->
                <button class="relative p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors">
                    <i class="fas fa-bell text-xl"></i>
                    <span
                        class="absolute -top-1 -right-1 h-4 w-4 bg-danger-500 rounded-full flex items-center justify-center">
                        <span class="text-xs text-white font-medium">3</span>
                    </span>
                </button>

                <!-- User Menu -->
                <div class="relative">
                    <button onclick="toggleUserDropdown()" class="flex items-center space-x-3 focus:outline-none">
                        <div
                            class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center shadow-sm">
                            <span class="text-white text-sm font-medium"><?php echo $initials; ?></span>
                        </div>
                        <span class="hidden md:block text-sm font-medium"><?php echo $fullName; ?></span>
                        <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform duration-200" id="userMenuChevron"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                        <a href="settings.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-user-edit mr-3 text-gray-400"></i>
                            Edit Profile
                        </a>
                        <hr class="my-1 border-gray-200">
                        <button onclick="logout()" data-logout-btn
                            class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors">
                            <i class="fas fa-sign-out-alt mr-3 text-gray-400"></i>
                            <span class="logout-text">Logout</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    const chevron = document.getElementById('userMenuChevron');

    if (dropdown.classList.contains('hidden')) {
        dropdown.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        dropdown.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('userDropdown');
    const userMenuButton = event.target.closest('button[onclick="toggleUserDropdown()"]');

    if (!userMenuButton && !dropdown.contains(event.target)) {
        dropdown.classList.add('hidden');
        document.getElementById('userMenuChevron').style.transform = 'rotate(0deg)';
    }
});
</script>