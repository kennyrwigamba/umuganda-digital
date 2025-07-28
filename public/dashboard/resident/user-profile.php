<?php
    /**
     * Resident Dashboard
     * Main dashboard page for residents
     */

    session_start();

    // Check if user is logged in
    if (! isset($_SESSION['user_id'])) {
        header('Location: ../../login.php');
        exit;
    }

    // Check if user is resident (not admin)
    if ($_SESSION['user_role'] !== 'resident') {
        header('Location: ../admin/index.php');
        exit;
    }

    // Include required files
    require_once __DIR__ . '/../../../src/models/User.php';
    require_once __DIR__ . '/../../../src/helpers/functions.php';

    // Initialize models and variables
    $userModel   = new User();
    $message     = '';
    $messageType = '';

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'update_personal':
                $result = handlePersonalInfoUpdate();
                break;
            case 'update_contact':
                $result = handleContactUpdate();
                break;
            case 'update_preferences':
                $result = handlePreferencesUpdate();
                break;
            case 'change_password':
                $result = handlePasswordChange();
                break;
            default:
                $result = ['success' => false, 'message' => 'Invalid action'];
        }

        $message     = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }

    // Get user data
    $user = $userModel->findById($_SESSION['user_id']);

    if (! $user) {
        // User not found, logout and redirect
        session_destroy();
        header('Location: ../../login.php?message=session_expired');
        exit;
    }

    // Extract user information for display
    $firstName   = htmlspecialchars($user['first_name']);
    $lastName    = htmlspecialchars($user['last_name']);
    $fullName    = $firstName . ' ' . $lastName;
    $email       = htmlspecialchars($user['email']);
    $phone       = htmlspecialchars($user['phone']);
    $nationalId  = htmlspecialchars($user['national_id']);
    $dateOfBirth = $user['date_of_birth'];
    $gender      = $user['gender'];
    $province    = htmlspecialchars($user['province']);
    $district    = htmlspecialchars($user['district']);
    $sector      = htmlspecialchars($user['sector']);
    $cell        = htmlspecialchars($user['cell']);
    $location    = $cell . ', ' . $sector . ', ' . $district;
    $initials    = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

    // Get user statistics (attendance, sessions, fines)
    $stats = getUserStats($_SESSION['user_id']);

    // Handle form submission functions
    function handlePersonalInfoUpdate()
    {
        global $userModel, $_SESSION;

        try {
            $data = [
                'first_name'    => sanitize($_POST['first_name']),
                'last_name'     => sanitize($_POST['last_name']),
                'date_of_birth' => $_POST['date_of_birth'],
                'gender'        => $_POST['gender'],
            ];

            // Add optional fields if provided
            if (! empty($_POST['occupation'])) {
                $data['occupation'] = sanitize($_POST['occupation']);
            }
            if (! empty($_POST['education_level'])) {
                $data['education_level'] = $_POST['education_level'];
            }
            if (! empty($_POST['marital_status'])) {
                $data['marital_status'] = $_POST['marital_status'];
            }

            $result = $userModel->update($_SESSION['user_id'], $data);

            if ($result) {
                logActivity("User updated personal information", 'info');
                return ['success' => true, 'message' => 'Personal information updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update personal information'];
            }
        } catch (Exception $e) {
            error_log("Error updating personal info: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating information'];
        }
    }

    function handleContactUpdate()
    {
        global $userModel, $_SESSION;

        try {
            // Validate email
            if (! isValidEmail($_POST['email'])) {
                return ['success' => false, 'message' => 'Please enter a valid email address'];
            }

            $data = [
                'email'    => sanitize($_POST['email']),
                'phone'    => sanitize($_POST['phone']),
                'province' => sanitize($_POST['province']),
                'district' => sanitize($_POST['district']),
            ];

            // Add optional address field
            if (! empty($_POST['address'])) {
                $data['address'] = sanitize($_POST['address']);
            }

            $result = $userModel->update($_SESSION['user_id'], $data);

            if ($result) {
                logActivity("User updated contact information", 'info');
                return ['success' => true, 'message' => 'Contact details updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update contact details'];
            }
        } catch (Exception $e) {
            error_log("Error updating contact info: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating contact details'];
        }
    }

    function handlePreferencesUpdate()
    {
        global $userModel, $_SESSION;

        try {
            $preferences = [
                'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
                'sms_notifications'   => isset($_POST['sms_notifications']) ? 1 : 0,
                'push_notifications'  => isset($_POST['push_notifications']) ? 1 : 0,
                'language'            => $_POST['language'] ?? 'en',
                'timezone'            => $_POST['timezone'] ?? 'Africa/Kigali',
                'date_format'         => $_POST['date_format'] ?? 'dd/mm/yyyy',
            ];

            $result = $userModel->updatePreferences($_SESSION['user_id'], $preferences);

            if ($result) {
                logActivity("User updated preferences", 'info');
                return ['success' => true, 'message' => 'Preferences updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update preferences'];
            }
        } catch (Exception $e) {
            error_log("Error updating preferences: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating preferences'];
        }
    }

    function handlePasswordChange()
    {
        global $userModel, $_SESSION;

        try {
            $currentPassword = $_POST['current_password'];
            $newPassword     = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];

            // Verify current password using User model method
            if (! $userModel->verifyPassword($_SESSION['user_id'], $currentPassword)) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            // Validate new password
            if (strlen($newPassword) < 8) {
                return ['success' => false, 'message' => 'New password must be at least 8 characters long'];
            }

            if ($newPassword !== $confirmPassword) {
                return ['success' => false, 'message' => 'New passwords do not match'];
            }

            // Update password using User model method
            $result = $userModel->updatePassword($_SESSION['user_id'], $newPassword);

            if ($result) {
                logActivity("User changed password", 'info');
                return ['success' => true, 'message' => 'Password updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update password'];
            }
        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating password'];
        }
    }

    function getUserStats($userId)
    {
        // This would normally fetch from database
        // For now, return static data - should be replaced with actual queries
        return [
            'attendance_rate'   => 87,
            'sessions_attended' => 26,
            'outstanding_fines' => 2500,
        ];
    }

    // Get user preferences
    $userPreferences    = json_decode($user['preferences'] ?? '{}', true);
    $defaultPreferences = [
        'email_notifications' => true,
        'sms_notifications'   => true,
        'push_notifications'  => false,
        'language'            => 'en',
        'timezone'            => 'Africa/Kigali',
        'date_format'         => 'dd/mm/yyyy',
    ];
    $preferences = array_merge($defaultPreferences, $userPreferences);

?>

<!-- Header -->
<?php include __DIR__ . '/partials/header.php'; ?>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 font-sans">
    <div class="flex flex-col md:flex-row h-screen">
        <!-- Sidebar -->
        <?php include 'partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden md:ml-0">
            <!-- Top Navbar -->
            <?php include 'partials/top-nav.php'; ?>

            <!-- Main Content -->
            <main
                class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 p-4 md:p-6">

                <!-- Success/Error Notification -->
                <?php if (! empty($message)): ?>
                <div id="notification" class="max-w-4xl mx-auto mb-6">
                    <div class="<?php echo $messageType === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700'; ?> border-l-4 p-4 rounded-lg shadow-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <?php if ($messageType === 'success'): ?>
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <?php else: ?>
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <?php endif; ?>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium"><?php echo htmlspecialchars($message); ?></p>
                            </div>
                            <div class="ml-auto pl-3">
                                <button onclick="hideNotification()" class="inline-flex                                                                                        <?php echo $messageType === 'success' ? 'text-green-400 hover:text-green-600' : 'text-red-400 hover:text-red-600'; ?>">
                                    <span class="sr-only">Dismiss</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="max-w-4xl mx-auto space-y-6">

                    <!-- Profile Header Card -->
                    <div
                        class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-8 text-white">
                            <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6">
                                <div class="relative">
                                    <div
                                        class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center text-3xl font-bold">
                                        <?php echo $initials; ?>
                                    </div>
                                    <button
                                        class="absolute bottom-0 right-0 bg-white text-primary-600 rounded-full p-2 shadow-lg hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="text-center md:text-left flex-1">
                                    <h1 class="text-2xl font-bold"><?php echo $fullName; ?></h1>
                                    <p class="text-primary-100 mb-2">Resident ID:                                                                                  <?php echo $nationalId; ?></p>
                                    <div class="flex flex-wrap justify-center md:justify-start gap-4 text-sm">
                                        <span class="bg-white/20 px-3 py-1 rounded-full">Sector:                                                                                                 <?php echo $sector; ?></span>
                                        <span class="bg-white/20 px-3 py-1 rounded-full">Cell:                                                                                               <?php echo $cell; ?></span>
                                        <span class="bg-success-500 px-3 py-1 rounded-full"><?php echo ucfirst($user['status']); ?> Member</span>
                                    </div>
                                </div>
                                <div class="text-center md:text-right">
                                    <div class="bg-white/20 rounded-lg p-3">
                                        <p class="text-sm text-primary-100">Member Since</p>
                                        <p class="text-lg font-semibold"><?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Tabs -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                                <button onclick="switchTab('personal')"
                                    class="tab-btn active border-primary-500 text-primary-600 py-4 px-1 border-b-2 font-medium text-sm">
                                    Personal Information
                                </button>
                                <button onclick="switchTab('contact')"
                                    class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-4 px-1 border-b-2 font-medium text-sm">
                                    Contact Details
                                </button>
                                <button onclick="switchTab('preferences')"
                                    class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-4 px-1 border-b-2 font-medium text-sm">
                                    Preferences
                                </button>
                                <button onclick="switchTab('security')"
                                    class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-4 px-1 border-b-2 font-medium text-sm">
                                    Security
                                </button>
                            </nav>
                        </div>

                        <!-- Personal Information Tab -->
                        <div id="personal-tab" class="tab-content p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Personal Information</h3>
                            <form method="POST" class="space-y-6">
                                <input type="hidden" name="action" value="update_personal">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                        <input type="text" name="first_name" value="<?php echo $firstName; ?>" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                        <input type="text" name="last_name" value="<?php echo $lastName; ?>" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">National ID</label>
                                        <input type="text" value="<?php echo $nationalId; ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                            readonly>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                                        <input type="date" name="date_of_birth" value="<?php echo $dateOfBirth; ?>" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                        <select name="gender" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                            <option value="male"                                                                 <?php echo $gender === 'male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="female"                                                                   <?php echo $gender === 'female' ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                                        <select name="marital_status"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                            <option value="">Select Status</option>
                                            <option value="single"                                                                   <?php echo($user['marital_status'] ?? '') === 'single' ? 'selected' : ''; ?>>Single</option>
                                            <option value="married"                                                                    <?php echo($user['marital_status'] ?? '') === 'married' ? 'selected' : ''; ?>>Married</option>
                                            <option value="divorced"                                                                     <?php echo($user['marital_status'] ?? '') === 'divorced' ? 'selected' : ''; ?>>Divorced</option>
                                            <option value="widowed"                                                                    <?php echo($user['marital_status'] ?? '') === 'widowed' ? 'selected' : ''; ?>>Widowed</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <button type="button"
                                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Contact Details Tab -->
                        <div id="contact-tab" class="tab-content p-6 hidden">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Contact Details</h3>
                            <form method="POST" class="space-y-6">
                                <input type="hidden" name="action" value="update_contact">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                        <input type="email" name="email" value="<?php echo $email; ?>" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                        <input type="tel" name="phone" value="<?php echo $phone; ?>" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-md font-medium text-gray-900 mb-4">Address Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                                            <select name="province" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                                <option value="Kigali"                                                                       <?php echo $province === 'Kigali' ? 'selected' : ''; ?>>Kigali</option>
                                                <option value="Northern Province"                                                                                  <?php echo $province === 'Northern Province' ? 'selected' : ''; ?>>Northern Province</option>
                                                <option value="Southern Province"                                                                                  <?php echo $province === 'Southern Province' ? 'selected' : ''; ?>>Southern Province</option>
                                                <option value="Eastern Province"                                                                                 <?php echo $province === 'Eastern Province' ? 'selected' : ''; ?>>Eastern Province</option>
                                                <option value="Western Province"                                                                                 <?php echo $province === 'Western Province' ? 'selected' : ''; ?>>Western Province</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">District</label>
                                            <select name="district" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                                <option value="Nyarugenge"                                                                           <?php echo $district === 'Nyarugenge' ? 'selected' : ''; ?>>Nyarugenge</option>
                                                <option value="Gasabo"                                                                       <?php echo $district === 'Gasabo' ? 'selected' : ''; ?>>Gasabo</option>
                                                <option value="Kicukiro"                                                                         <?php echo $district === 'Kicukiro' ? 'selected' : ''; ?>>Kicukiro</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Sector</label>
                                            <input type="text" value="<?php echo $sector; ?>"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                                readonly>
                                            <p class="text-xs text-gray-500 mt-1">Contact admin to change sector</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Cell</label>
                                            <input type="text" value="<?php echo $cell; ?>"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                                readonly>
                                            <p class="text-xs text-gray-500 mt-1">Contact admin to change cell</p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Village/Street Address</label>
                                            <textarea name="address" rows="3"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                                placeholder="Enter your detailed address..."><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end space-x-3">
                                    <button type="button"
                                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Preferences Tab -->
                        <div id="preferences-tab" class="tab-content p-6 hidden">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Notification Preferences</h3>
                            <form method="POST" class="space-y-6">
                                <input type="hidden" name="action" value="update_preferences">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-md font-medium text-gray-900 mb-4">Communication Preferences</h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <label class="text-sm font-medium text-gray-700">Email Notifications</label>
                                                <p class="text-sm text-gray-500">Receive updates about Umuganda schedules and notices</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="email_notifications" value="1"
                                                       <?php echo $preferences['email_notifications'] ? 'checked' : ''; ?>
                                                       class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                            </label>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <label class="text-sm font-medium text-gray-700">SMS Notifications</label>
                                                <p class="text-sm text-gray-500">Get text messages for urgent announcements</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="sms_notifications" value="1"
                                                       <?php echo $preferences['sms_notifications'] ? 'checked' : ''; ?>
                                                       class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                            </label>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <label class="text-sm font-medium text-gray-700">Push Notifications</label>
                                                <p class="text-sm text-gray-500">Browser notifications for real-time updates</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="push_notifications" value="1"
                                                       <?php echo $preferences['push_notifications'] ? 'checked' : ''; ?>
                                                       class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-md font-medium text-gray-900 mb-4">Display Preferences</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                                            <select name="language"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                                <option value="en"                                                                   <?php echo $preferences['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                                                <option value="rw"                                                                   <?php echo $preferences['language'] === 'rw' ? 'selected' : ''; ?>>Kinyarwanda</option>
                                                <option value="fr"                                                                   <?php echo $preferences['language'] === 'fr' ? 'selected' : ''; ?>>French</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Time Zone</label>
                                            <select name="timezone"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                                <option value="Africa/Kigali" <?php echo $preferences['timezone'] === 'Africa/Kigali' ? 'selected' : ''; ?>>Africa/Kigali (CAT)</option>
                                                <option value="UTC" <?php echo $preferences['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Format</label>
                                            <select name="date_format"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                                <option value="dd/mm/yyyy" <?php echo $preferences['date_format'] === 'dd/mm/yyyy' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                                <option value="mm/dd/yyyy" <?php echo $preferences['date_format'] === 'mm/dd/yyyy' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                                <option value="yyyy-mm-dd" <?php echo $preferences['date_format'] === 'yyyy-mm-dd' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end space-x-3">
                                    <button type="button"
                                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                        Reset to Default
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                        Save Preferences
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Security Tab -->
                        <div id="security-tab" class="tab-content p-6 hidden">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Security Settings</h3>

                            <!-- Password Change Form -->
                            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                                <h4 class="text-md font-medium text-gray-900 mb-4">Change Password</h4>
                                <form method="POST" class="space-y-4">
                                    <input type="hidden" name="action" value="change_password">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                        <input type="password" name="current_password" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                        <input type="password" name="new_password" required minlength="8" id="new_password"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <p class="text-sm text-gray-500 mt-1">Password must be at least 8 characters long</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                        <input type="password" name="confirm_password" required id="confirm_password"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit"
                                            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                            Change Password
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Account Information -->
                            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                                <h4 class="text-md font-medium text-gray-900 mb-4">Account Information</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-700">Account Created</span>
                                        <span class="text-sm text-gray-600"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-700">Last Login</span>
                                        <span class="text-sm text-gray-600"><?php echo isset($user['last_login']) ? date('F j, Y \a\t g:i A', strtotime($user['last_login'])) : 'N/A'; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-700">Account Status</span>
                                        <span class="inline-flex px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                            <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Session Management -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-900 mb-4">Session Management</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">Active Sessions</p>
                                            <p class="text-sm text-gray-500">You're currently logged in on this device</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="location.href='../../../logout.php'"
                                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                                Logout
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-danger-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Account</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Are you sure you want to delete your account? This
                                    action cannot be undone and you will lose all your data.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button onclick="deleteAccount()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-danger-600 text-base font-medium text-white hover:bg-danger-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Delete Account
                    </button>
                    <button onclick="closeConfirmModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>

        // Tab switching functionality
        function switchTab(tabName) {
            // Remove active class from all tab buttons
            const tabBtns = document.querySelectorAll('.tab-btn');
            tabBtns.forEach(btn => {
                btn.classList.remove('border-primary-500', 'text-primary-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });

            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.add('hidden'));

            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.remove('hidden');

            // Add active class to clicked tab button
            event.target.classList.remove('border-transparent', 'text-gray-500');
            event.target.classList.add('border-primary-500', 'text-primary-600');
        }

        // Modal functionality
        function confirmAccountDeletion() {
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        function deleteAccount() {
            // Here you would implement the actual account deletion logic
            alert('Account deletion functionality would be implemented here.');
            closeConfirmModal();
        }

        // Password confirmation validation
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        if (newPasswordInput && confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== newPasswordInput.value) {
                    this.setCustomValidity("Passwords don't match");
                } else {
                    this.setCustomValidity('');
                }
            });

            newPasswordInput.addEventListener('input', function() {
                if (confirmPasswordInput.value !== '') {
                    if (confirmPasswordInput.value !== this.value) {
                        confirmPasswordInput.setCustomValidity("Passwords don't match");
                    } else {
                        confirmPasswordInput.setCustomValidity('');
                    }
                }
            });
        }

        // Form submission confirmation for password change
        const passwordForm = document.querySelector('form[action*="change_password"]');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                const currentPassword = this.querySelector('input[name="current_password"]').value;
                const newPassword = this.querySelector('input[name="new_password"]').value;
                const confirmPassword = this.querySelector('input[name="confirm_password"]').value;

                if (!currentPassword || !newPassword || !confirmPassword) {
                    e.preventDefault();
                    showNotification('All password fields are required', 'error');
                    return;
                }

                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    showNotification("New passwords don't match", 'error');
                    return;
                }

                if (newPassword.length < 8) {
                    e.preventDefault();
                    showNotification('New password must be at least 8 characters long', 'error');
                    return;
                }
            });
        }

        // Toggle switches
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                // Here you would save the preference change
                console.log('Preference changed:', this.checked);
            });
        });

        // Show notification function
        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg text-white ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            notification.textContent = message;

            document.body.appendChild(notification);

            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Hide notification function
        function hideNotification() {
            const notification = document.getElementById('notification');
            if (notification) {
                notification.style.display = 'none';
            }
        }
    </script>


<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>