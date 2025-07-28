<?php
    // Simple test page to test logout functionality
    session_start();

    // Simulate logged in user for testing
    if (! isset($_SESSION['user_id'])) {
        $_SESSION['user_id']    = 1;
        $_SESSION['user_email'] = 'test@example.com';
        $_SESSION['user_role']  = 'resident';
        $_SESSION['user_name']  = 'Test User';
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Logout Test Page</h1>

        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            <h2 class="font-semibold text-blue-800 mb-2">Current Session:</h2>
            <p class="text-sm text-blue-700">User ID:                                                      <?php echo $_SESSION['user_id']; ?></p>
            <p class="text-sm text-blue-700">Email:                                                    <?php echo $_SESSION['user_email']; ?></p>
            <p class="text-sm text-blue-700">Role:                                                   <?php echo $_SESSION['user_role']; ?></p>
        </div>

        <div class="space-y-4">
            <!-- API Logout Button -->
            <button onclick="logout()" data-logout-btn
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-xl transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Logout via API
            </button>

            <!-- Direct Logout Link -->
            <a href="logout.php"
               class="block w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-4 rounded-xl transition-colors text-center">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Logout via Direct Link
            </a>
        </div>

        <div class="mt-6 text-center">
            <a href="login.php" class="text-blue-600 hover:text-blue-700 text-sm">
                Go to Login Page
            </a>
        </div>
    </div>

    <!-- Alert Container for notifications -->
    <div id="alert-container" class="fixed top-4 right-4 z-50"></div>

    <!-- Include logout script -->
    <script src="js/logout.js"></script>

    <script>
        // Simple alert function for this test page
        function showAlert(message, type = 'error') {
            const alertContainer = document.getElementById('alert-container');
            if (!alertContainer) return;

            const alertClass = type === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700';
            const iconClass = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';

            const alertDiv = document.createElement('div');
            alertDiv.innerHTML = `
                <div class="border px-4 py-3 rounded-xl ${alertClass} flex items-center max-w-sm">
                    <i class="fas ${iconClass} mr-2"></i>
                    <span>${message}</span>
                    <button class="ml-auto text-lg leading-none" onclick="this.parentElement.parentElement.remove()">
                        &times;
                    </button>
                </div>
            `;

            alertContainer.appendChild(alertDiv);

            // Auto-hide after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Make showAlert available globally
        window.showAlert = showAlert;
    </script>
</body>
</html>
