<!-- JavaScript for Profile Dropdown -->
    <script>
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            const button = document.getElementById('profileButton');
            
            if (dropdown.classList.contains('opacity-0')) {
                // Show dropdown
                dropdown.classList.remove('opacity-0', 'invisible', 'translate-y-1');
                dropdown.classList.add('opacity-100', 'visible', 'translate-y-0');
                button.classList.add('ring-2', 'ring-primary-500', 'ring-offset-2');
            } else {
                // Hide dropdown
                dropdown.classList.add('opacity-0', 'invisible', 'translate-y-1');
                dropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
                button.classList.remove('ring-2', 'ring-primary-500', 'ring-offset-2');
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const button = document.getElementById('profileButton');
            
            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add('opacity-0', 'invisible', 'translate-y-1');
                dropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
                button.classList.remove('ring-2', 'ring-primary-500', 'ring-offset-2');
            }
        });

        // Close dropdown when pressing Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const dropdown = document.getElementById('profileDropdown');
                const button = document.getElementById('profileButton');
                
                dropdown.classList.add('opacity-0', 'invisible', 'translate-y-1');
                dropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
                button.classList.remove('ring-2', 'ring-primary-500', 'ring-offset-2');
            }
        });
    </script>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleMobileMenu() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
        overlay.addEventListener('click', toggleMobileMenu);

        // Close mobile menu when window is resized to desktop
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
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
    </script>
</body>

</html>
