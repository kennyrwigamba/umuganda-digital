<?php
    /**
     * Resident QR Code Page
     * Generates QR code for the logged-in resident for attendance scanning
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
    require_once __DIR__ . '/../../../config/db.php';
    require_once __DIR__ . '/../../../src/models/User.php';
    require_once __DIR__ . '/../../../src/helpers/functions.php';

    global $db;
    $connection = $db->getConnection();
    $userModel  = new User();

    // Get current user info
    $userId = $_SESSION['user_id'];
    $user   = $userModel->findById($userId);

    if (! $user) {
        session_destroy();
        header('Location: ../../login.php?message=session_expired');
        exit;
    }

    // Get user's location information
    $locationQuery = "
    SELECT
        u.first_name, u.last_name, u.email, u.national_id, u.phone,
        c.name as cell_name, s.name as sector_name, d.name as district_name
    FROM users u
    LEFT JOIN cells c ON u.cell_id = c.id
    LEFT JOIN sectors s ON u.sector_id = s.id
    LEFT JOIN districts d ON u.district_id = d.id
    WHERE u.id = ?";

    $stmt = $connection->prepare($locationQuery);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $userDetails = $stmt->get_result()->fetch_assoc();

    if (! $userDetails) {
        die('Error: Could not fetch user details.');
    }

    // Generate QR code data - JSON format for resident
    $qrData = json_encode([
        'type'         => 'umuganda_resident',
        'id'           => $userId,
        'name'         => $userDetails['first_name'] . ' ' . $userDetails['last_name'],
        'cell'         => $userDetails['cell_name'] ?? 'N/A',
        'sector'       => $userDetails['sector_name'] ?? 'N/A',
        'district'     => $userDetails['district_name'] ?? 'N/A',
        'email'        => $userDetails['email'] ?? '',
        'phone'        => $userDetails['phone'] ?? '',
        'generated_at' => date('Y-m-d H:i:s'),
    ]);

    // Generate QR code URL using online service
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrData);

    // Get user initials for avatar
    $firstName = $userDetails['first_name'] ?? '';
    $lastName  = $userDetails['last_name'] ?? '';
    $initials  = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
    $fullName  = trim($firstName . ' ' . $lastName);
    $district    = htmlspecialchars($userDetails['district_name']);
    $sector      = htmlspecialchars($userDetails['sector_name']);
    $cell        = htmlspecialchars($userDetails['cell_name']);
    $location    = $cell . ', ' . $sector . ', ' . $district;

    $message     = '';
    $messageType = '';

    // Handle QR code regeneration
    if (isset($_POST['regenerate_qr'])) {
        // Simply refresh the page to generate a new timestamp
        header('Location: qr-code.php');
        exit;
    }

?>

<!-- Header -->
<?php include __DIR__ . '/partials/header.php'; ?>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 font-sans">
    <div class="flex flex-col md:flex-row h-screen">
        <!-- Sidebar -->
        <?php include 'partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow md:ml-0">
            <!-- Top Navbar -->
            <?php include 'partials/top-nav.php'; ?>

            <!-- Main Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 p-4 md:p-6">

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

                    <!-- QR Code Header Card -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-8 text-white">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h1 class="text-2xl font-bold">My QR Code</h1>
                                    <p class="text-primary-100 mt-1">For Umuganda Attendance Scanning</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Display Card -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-8">
                        <div class="text-center">
                            <div class="mb-6">
                                <h2 class="text-xl font-semibold text-gray-800 mb-2">Your Personal QR Code</h2>
                                <p class="text-gray-600">Show this QR code to mark your attendance at Umuganda events</p>
                            </div>

                            <!-- QR Code Display -->
                            <div class="flex justify-center mb-6">
                                <div class="bg-white p-4 rounded-2xl shadow-lg border border-gray-200">
                                    <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code for<?php echo htmlspecialchars($fullName); ?>"
                                         class="w-64 h-64 object-contain" id="qrCodeImage">
                                </div>
                            </div>

                            <!-- User Information -->
                            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div class="text-left">
                                        <span class="font-medium text-gray-700">Name:</span>
                                        <span class="text-gray-600 ml-2"><?php echo htmlspecialchars($fullName); ?></span>
                                    </div>
                                    <div class="text-left">
                                        <span class="font-medium text-gray-700">Cell:</span>
                                        <span class="text-gray-600 ml-2"><?php echo htmlspecialchars($userDetails['cell_name'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="text-left">
                                        <span class="font-medium text-gray-700">Sector:</span>
                                        <span class="text-gray-600 ml-2"><?php echo htmlspecialchars($userDetails['sector_name'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="text-left">
                                        <span class="font-medium text-gray-700">Generated:</span>
                                        <span class="text-gray-600 ml-2"><?php echo date('M j, Y g:i A'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <form method="POST" class="inline">
                                    <button type="submit" name="regenerate_qr"
                                            class="bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all duration-200 font-medium">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Regenerate QR Code
                                    </button>
                                </form>

                                <button onclick="downloadQR()"
                                        class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200 font-medium">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download QR Code
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions Card -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            How to Use Your QR Code
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-bold mt-0.5">1</div>
                                <p class="text-gray-700">Save or screenshot this QR code on your mobile device for easy access</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-bold mt-0.5">2</div>
                                <p class="text-gray-700">Show your QR code to the attendance scanner at Umuganda events</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-bold mt-0.5">3</div>
                                <p class="text-gray-700">Wait for confirmation that your attendance has been recorded</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-bold mt-0.5">4</div>
                                <p class="text-gray-700">You can regenerate your QR code anytime if needed</p>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script>
        // Hide notification after 5 seconds
        function hideNotification() {
            const notification = document.getElementById('notification');
            if (notification) {
                notification.style.display = 'none';
            }
        }

        // Auto-hide notification
        setTimeout(hideNotification, 5000);

        // Download QR Code function
        function downloadQR() {
            const qrImage = document.getElementById('qrCodeImage');
            const link = document.createElement('a');
            link.href = qrImage.src;
            link.download = 'my-umuganda-qr-code.png';
            link.click();
        }
    </script>
</body>
</html>
