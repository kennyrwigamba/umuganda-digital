<?php
    /**
     * Admin Settings - Admin Dashboard
     * Personal preferences and account settings
     */

    session_start();

    // Check if user is logged in
    if (! isset($_SESSION['user_id'])) {
        header('Location: ../../login.php');
        exit;
    }

    // Check if user is admin (superadmins have their own dashboard)
    if ($_SESSION['user_role'] !== 'admin') {
        // Redirect based on role
        if ($_SESSION['user_role'] === 'superadmin') {
            header('Location: ../superadmin/index.php');
        } else {
            header('Location: ../resident/index.php');
        }
        exit;
    }

    // Include required files
    require_once __DIR__ . '/../../../config/db.php';
    require_once __DIR__ . '/../../../src/models/User.php';

    // Use the global database instance
    global $db;
    $connection = $db->getConnection();

    $user = new User();

    // Get current admin info
    $adminId   = $_SESSION['user_id'];
    $adminInfo = $user->findById($adminId);

    if (! $adminInfo) {
        // User not found, logout and redirect
        session_destroy();
        header('Location: ../../login.php?message=session_expired');
        exit;
    }

    // Extract user information for display
    $firstName = htmlspecialchars($adminInfo['first_name']);
    $lastName  = htmlspecialchars($adminInfo['last_name']);
    $fullName  = $firstName . ' ' . $lastName;
    $initials  = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_preferences':
                    try {
                        // Insert or update admin settings
                        $stmt = $connection->prepare("INSERT INTO admin_settings (admin_id, notification_email, notification_sms, default_fine_amount, session_duration, language, timezone)
                                          VALUES (?, ?, ?, ?, ?, ?, ?)
                                          ON DUPLICATE KEY UPDATE
                                          notification_email = VALUES(notification_email),
                                          notification_sms = VALUES(notification_sms),
                                          default_fine_amount = VALUES(default_fine_amount),
                                          session_duration = VALUES(session_duration),
                                          language = VALUES(language),
                                          timezone = VALUES(timezone)");

                        if (! $stmt) {
                            throw new Exception("Failed to prepare statement: " . $connection->error);
                        }

                        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
                        $sms_notifications   = isset($_POST['sms_notifications']) ? 1 : 0;

                        $stmt->bind_param("iiiisss",
                            $adminId,
                            $email_notifications,
                            $sms_notifications,
                            $_POST['default_fine_amount'],
                            $_POST['session_duration'],
                            $_POST['language'],
                            $_POST['timezone']
                        );

                        $stmt->execute();
                        $success_message = "Preferences updated successfully!";
                    } catch (Exception $e) {
                        $error_message = "Error updating preferences: " . $e->getMessage();
                    }
                    break;

                case 'update_profile':
                    try {
                        $stmt = $connection->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?");
                        if (! $stmt) {
                            throw new Exception("Failed to prepare statement: " . $connection->error);
                        }

                        $stmt->bind_param("ssssi", $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['phone'], $adminId);
                        $stmt->execute();

                        // Update session
                        $_SESSION['first_name'] = $_POST['first_name'];
                        $_SESSION['last_name']  = $_POST['last_name'];
                        $_SESSION['email']      = $_POST['email'];

                        $success_message = "Profile updated successfully!";
                    } catch (Exception $e) {
                        $error_message = "Error updating profile: " . $e->getMessage();
                    }
                    break;

                case 'change_password':
                    try {
                        // Verify current password
                        $stmt = $connection->prepare("SELECT password FROM users WHERE id = ?");
                        if (! $stmt) {
                            throw new Exception("Failed to prepare statement: " . $connection->error);
                        }

                        $stmt->bind_param("i", $adminId);
                        $stmt->execute();
                        $result    = $stmt->get_result();
                        $user_data = $result->fetch_assoc();

                        if (! $user_data || ! password_verify($_POST['current_password'], $user_data['password'])) {
                            $error_message = "Current password is incorrect.";
                        } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
                            $error_message = "New passwords do not match.";
                        } else {
                            $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                            $stmt            = $connection->prepare("UPDATE users SET password = ? WHERE id = ?");
                            if (! $stmt) {
                                throw new Exception("Failed to prepare statement: " . $connection->error);
                            }

                            $stmt->bind_param("si", $hashed_password, $adminId);
                            $stmt->execute();

                            $success_message = "Password changed successfully!";
                        }
                    } catch (Exception $e) {
                        $error_message = "Error changing password: " . $e->getMessage();
                    }
                    break;
            }
        }
    }

    // Fetch current admin data
    $stmt = $connection->prepare("SELECT u.*, as_.* FROM users u
                      LEFT JOIN admin_settings as_ ON u.id = as_.admin_id
                      WHERE u.id = ?");
    if (! $stmt) {
        throw new Exception("Failed to prepare statement: " . $connection->error);
    }

    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result     = $stmt->get_result();
    $admin_data = $result->fetch_assoc();

    // Default values if no settings exist
    $settings = [
        'notification_email'  => $admin_data['notification_email'] ?? 1,
        'notification_sms'    => $admin_data['notification_sms'] ?? 0,
        'default_fine_amount' => $admin_data['default_fine_amount'] ?? 1000,
        'session_duration'    => $admin_data['session_duration'] ?? 60,
        'language'            => $admin_data['language'] ?? 'en',
        'timezone'            => $admin_data['timezone'] ?? 'Africa/Kigali',
    ];
?>

<!-- Header -->
<?php include __DIR__ . '/partials/header.php'; ?>

<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div id="main-content" class="content-transition lg:ml-64">
        <!-- Top Navigation -->
        <?php include __DIR__ . '/partials/top-nav.php'; ?>

        <!-- Admin Settings Content -->
        <main class="p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 ml-4 lg:ml-0 flex items-center">
                                <i class="fas fa-cog text-primary-600 mr-3"></i>
                                Admin Settings
                            </h1>
                            <p class="text-gray-600 mt-2 ml-4 lg:ml-0">Manage your personal preferences and account settings</p>
                        </div>
                        <div class="mt-4 sm:mt-0 ml-4 lg:ml-0">
                            <div class="flex items-center space-x-3 bg-white px-4 py-2 rounded-lg shadow-sm border">
                                <div class="w-10 h-10 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-semibold">
                                    <?php echo $initials; ?>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo $fullName; ?></p>
                                    <p class="text-xs text-gray-500">Administrator</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center shadow-sm">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <span><?php echo htmlspecialchars($success_message); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center shadow-sm">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <span><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Tabs -->
                <div class="mb-8">
                    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                        <nav class="flex" aria-label="Tabs">
                            <button data-tab="preferences" class="settings-tab active flex-1 px-6 py-4 text-sm font-medium text-center border-b-2 border-primary-500 bg-primary-50 text-primary-700 transition-all duration-200">
                                <i class="fas fa-cog mr-2"></i>
                                <span class="hidden sm:inline">Preferences</span>
                            </button>
                            <button data-tab="profile" class="settings-tab flex-1 px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-all duration-200">
                                <i class="fas fa-user mr-2"></i>
                                <span class="hidden sm:inline">Profile</span>
                            </button>
                            <button data-tab="security" class="settings-tab flex-1 px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-all duration-200">
                                <i class="fas fa-shield-alt mr-2"></i>
                                <span class="hidden sm:inline">Security</span>
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <!-- Preferences Tab -->
                    <div id="preferences-tab" class="tab-content">
                        <div class="p-8">
                            <div class="flex items-center mb-8">
                                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-cog text-primary-600 text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900">Admin Preferences</h2>
                                    <p class="text-gray-600">Configure your notification and system preferences</p>
                                </div>
                            </div>

                            <form method="POST" class="space-y-8">
                                <input type="hidden" name="action" value="update_preferences">

                                <!-- Notification Settings Card -->
                                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                    <div class="flex items-center mb-6">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-bell text-blue-600"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">Notification Settings</h3>
                                    </div>
                                    <div class="space-y-4">
                                        <label class="flex items-center p-4 bg-white rounded-lg border border-gray-200 hover:border-primary-300 transition-colors cursor-pointer">
                                            <input type="checkbox" name="email_notifications"                                                                                              <?php echo $settings['notification_email'] ? 'checked' : ''; ?>
                                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 focus:ring-offset-0">
                                            <div class="ml-4">
                                                <span class="text-sm font-medium text-gray-900">Email notifications</span>
                                                <p class="text-xs text-gray-500">Receive notifications via email for important updates</p>
                                            </div>
                                        </label>
                                        <label class="flex items-center p-4 bg-white rounded-lg border border-gray-200 hover:border-primary-300 transition-colors cursor-pointer">
                                            <input type="checkbox" name="sms_notifications"                                                                                            <?php echo $settings['notification_sms'] ? 'checked' : ''; ?>
                                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 focus:ring-offset-0">
                                            <div class="ml-4">
                                                <span class="text-sm font-medium text-gray-900">SMS notifications</span>
                                                <p class="text-xs text-gray-500">Receive critical alerts via SMS</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Fine Management -->
                                <div class="bg-white rounded-lg shadow p-6">
                                    <div class="flex items-center mb-4">
                                        <i class="fas fa-money-bill-wave text-yellow-500 mr-3"></i>
                                        <h3 class="text-lg font-medium text-gray-900">Fine Management</h3>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">Configure default fine settings for community service events.</p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Default Fine Amount (RWF)</label>
                                            <input type="number" name="default_fine_amount" value="<?php echo $settings['default_fine_amount']; ?>"
                                                   min="0" step="100" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 transition-colors duration-200">
                                        </div>
                                    </div>
                                </div>

                                <!-- Session Settings -->
                                <div class="bg-white rounded-lg shadow p-6">
                                    <div class="flex items-center mb-4">
                                        <i class="fas fa-clock text-blue-500 mr-3"></i>
                                        <h3 class="text-lg font-medium text-gray-900">Session Settings</h3>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">Manage your login session preferences and security settings.</p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Session Duration (minutes)</label>
                                            <select name="session_duration" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                                <option value="30"                                                                                                                                                                                                       <?php echo $settings['session_duration'] == 30 ? 'selected' : ''; ?>>30 minutes</option>
                                                <option value="60"                                                                                                                                                                                                       <?php echo $settings['session_duration'] == 60 ? 'selected' : ''; ?>>1 hour</option>
                                                <option value="120"                                                                                                                                                                                                          <?php echo $settings['session_duration'] == 120 ? 'selected' : ''; ?>>2 hours</option>
                                                <option value="240"                                                                                                                                                                                                          <?php echo $settings['session_duration'] == 240 ? 'selected' : ''; ?>>4 hours</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Language and Localization -->
                                <div class="bg-white rounded-lg shadow p-6">
                                    <div class="flex items-center mb-4">
                                        <i class="fas fa-globe text-green-500 mr-3"></i>
                                        <h3 class="text-lg font-medium text-gray-900">Language & Localization</h3>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">Configure language and regional settings for your interface.</p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                                            <select name="language" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 transition-colors duration-200">
                                                <option value="en"                                                                                                                                                                                                       <?php echo $settings['language'] == 'en' ? 'selected' : ''; ?>>English</option>
                                                <option value="rw"                                                                                                                                                                                                       <?php echo $settings['language'] == 'rw' ? 'selected' : ''; ?>>Kinyarwanda</option>
                                                <option value="fr"                                                                                                                                                                                                       <?php echo $settings['language'] == 'fr' ? 'selected' : ''; ?>>French</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Time Zone</label>
                                            <select name="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 transition-colors duration-200">
                                                <option value="Africa/Kigali"                                                                                                                                                                                                                                        <?php echo $settings['timezone'] == 'Africa/Kigali' ? 'selected' : ''; ?>>Kigali (GMT+2)</option>
                                                <option value="UTC"                                                                                                                                                                                                          <?php echo $settings['timezone'] == 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 font-medium">
                                        <i class="fas fa-save mr-2"></i>
                                        Save Preferences
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Profile Tab -->
                    <div id="profile-tab" class="tab-content hidden">
                        <div class="p-6">
                            <div class="bg-white rounded-lg shadow p-6 mb-6">
                                <div class="flex items-center mb-4">
                                    <i class="fas fa-user-circle text-blue-500 mr-3 text-xl"></i>
                                    <h2 class="text-xl font-semibold text-gray-900">Profile Information</h2>
                                </div>
                                <p class="text-sm text-gray-600 mb-6">Update your personal information and contact details.</p>

                                <form method="POST" class="space-y-6">
                                    <input type="hidden" name="action" value="update_profile">

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($admin_data['first_name'] ?? ''); ?>"
                                                   required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($admin_data['last_name'] ?? ''); ?>"
                                                   required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                            <input type="email" name="email" value="<?php echo htmlspecialchars($admin_data['email'] ?? ''); ?>"
                                                   required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($admin_data['phone'] ?? ''); ?>"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                            <input type="text" value="Administrator" readonly
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500">
                                        </div>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 font-medium">
                                            <i class="fas fa-user-edit mr-2"></i>
                                            Update Profile
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Security Tab -->
                    <div id="security-tab" class="tab-content hidden">
                        <div class="p-6">
                            <div class="bg-white rounded-lg shadow p-6 mb-6">
                                <div class="flex items-center mb-4">
                                    <i class="fas fa-shield-alt text-red-500 mr-3 text-xl"></i>
                                    <h2 class="text-xl font-semibold text-gray-900">Security Settings</h2>
                                </div>
                                <p class="text-sm text-gray-600 mb-6">Manage your password and account security preferences.</p>

                                <form method="POST" class="space-y-6">
                                    <input type="hidden" name="action" value="change_password">

                                    <div class="max-w-md">
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                            <input type="password" name="current_password" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                                        </div>

                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                            <input type="password" name="new_password" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                                        </div>

                                        <div class="mb-6">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                            <input type="password" name="confirm_password" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                                        </div>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200 font-medium">
                                            <i class="fas fa-key mr-2"></i>
                                            Change Password
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Session Information -->
                            <div class="bg-white rounded-lg shadow p-6">
                                <div class="flex items-center mb-4">
                                    <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                                    <h3 class="text-lg font-medium text-gray-900">Session Information</h3>
                                </div>
                                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700">Last Login:</span>
                                            <span class="text-gray-600"><?php echo date('M j, Y g:i A'); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700">Session ID:</span>
                                            <span class="text-gray-600 font-mono"><?php echo substr(session_id(), 0, 8); ?>...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        const tabs = document.querySelectorAll('.settings-tab');
        const tabContents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');

                // Remove active class from all tabs
                tabs.forEach(t => {
                    t.classList.remove('bg-primary-100', 'text-primary-700');
                    t.classList.add('text-gray-500', 'hover:text-gray-700');
                });

                // Add active class to clicked tab
                this.classList.remove('text-gray-500', 'hover:text-gray-700');
                this.classList.add('bg-primary-100', 'text-primary-700');

                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });

                // Show target tab content
                document.getElementById(targetTab + '-tab').classList.remove('hidden');
            });
        });
    </script>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');

            sidebar.classList.toggle('-translate-x-full');
            mainContent.classList.toggle('lg:ml-64');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuButton = document.querySelector('[onclick="toggleMobileMenu()"]');

            if (!sidebar.contains(event.target) && !menuButton.contains(event.target)) {
                sidebar.classList.add('-translate-x-full');
                document.getElementById('main-content').classList.add('lg:ml-64');
            }
        });
    </script>

</body>
</html>

<?php include __DIR__ . '/partials/footer.php'; ?>