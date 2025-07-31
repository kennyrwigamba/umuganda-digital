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

        // Logout functionality
        async function logout() {
            try {
                // Show confirmation dialog
                if (!confirm('Are you sure you want to log out?')) {
                    return;
                }

                // Show loading state
                const logoutBtn = document.querySelector('[data-logout-btn]');
                const logoutText = logoutBtn.querySelector('.logout-text');
                if (logoutBtn && logoutText) {
                    logoutBtn.disabled = true;
                    logoutText.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Logging out...';
                }

                // Make logout request
                const response = await fetch('/api/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.success || !response.ok) {
                    // Redirect to login page regardless of API response
                    window.location.href = '/public/login.php?message=logged_out';
                } else {
                    throw new Error(result.error || 'Logout failed');
                }

            } catch (error) {
                console.error('Logout error:', error);

                // Fallback: redirect to logout page
                window.location.href = '/public/logout.php';
            }
        }


        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            initializeSidebar();
        });
    </script>
</body>

</html>