<?php
    session_start();

    // Redirect if already logged in
    if (isset($_SESSION['user_id'])) {
        switch ($_SESSION['user_role']) {
            case 'superadmin':
                $redirect_url = 'dashboard/superadmin/index.php';
                break;
            case 'admin':
                $redirect_url = 'dashboard/admin/index.php';
                break;
            case 'resident':
            default:
                $redirect_url = 'dashboard/resident/index.php';
                break;
        }
        header("Location: $redirect_url");
        exit;
    }

    // Include helper functions
    require_once __DIR__ . '/../src/helpers/functions.php';

    // Generate CSRF token
    $csrf_token = generateCSRFToken();

    // Handle logout messages
    $message      = '';
    $message_type = '';
    if (isset($_GET['message'])) {
        switch ($_GET['message']) {
            case 'logged_out':
                $message      = 'You have been successfully logged out.';
                $message_type = 'success';
                break;
            case 'already_logged_out':
                $message      = 'You were already logged out.';
                $message_type = 'info';
                break;
            case 'session_expired':
                $message      = 'Your session has expired. Please log in again.';
                $message_type = 'warning';
                break;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Umuganda Digital</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    <div class="min-h-screen flex">
        <!-- Left Column - Login Form -->
        <div class="flex-none w-full lg:w-2/5 flex items-center justify-center px-4 py-8 sm:px-6 lg:px-8 bg-white/80 backdrop-blur-sm">
            <div class="max-w-md w-full space-y-8 animate-slide-up">
                <!-- Logo Section -->
                <div class="text-center">
                    <div class="mx-auto h-16 w-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg animate-float">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h2 class="mt-6 text-3xl font-bold text-gray-900">Welcome back</h2>
                    <p class="mt-2 text-sm text-gray-600">Sign in to your account to continue</p>
                <!-- Alert Container -->
                <div id="alert-container" class="mb-4">
                    <?php if ($message): ?>
                        <div class="border mt-4 px-4 py-3 rounded-xl
                            <?php
                                echo $message_type === 'success' ? 'bg-green-100 border-green-400 text-green-700' :
                            ($message_type === 'warning' ? 'bg-yellow-100 border-yellow-400 text-yellow-700' : 'bg-blue-100 border-blue-400 text-blue-700');
                            ?> flex items-center">
                            <i class="fas
                                <?php
                                    echo $message_type === 'success' ? 'fa-check-circle' :
                                ($message_type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle');
                                ?> mr-2"></i>
                            <span><?php echo htmlspecialchars($message); ?></span>
                            <button class="ml-auto text-lg leading-none" onclick="this.parentElement.remove()">
                                &times;
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Login Form -->
                <form id="loginForm" class="mt-8 space-y-6" onsubmit="handleLogin(event)">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="space-y-4">
                        <!-- Email Input -->
                        <div class="group">
                            <label for="email" class="block text-sm text-left font-medium text-gray-700 mb-2">Email address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                    </svg>
                                </div>
                                <input id="email" name="email" type="email" required
                                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                                       placeholder="Enter your email">
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="group flex flex-col">
                            <label for="password" class="block text-sm text-left font-medium text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input id="password" name="password" type="password" required
                                       class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                                       placeholder="Enter your password">

                                <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg id="eye-icon" class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Remember me and Forgot password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox"
                                   class="h-    4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-700">Remember me</label>
                        </div>
                        <div class="text-sm">
                            <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                                Forgot your password?
                            </a>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-blue-300 group-hover:text-blue-200 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                            </span>
                            Sign in
                        </button>
                    </div>

                    <!-- Sign up link -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            Don't have an account?
                            <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                                Sign up now
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
        </div>

        <!-- Right Column - Image -->
        <div class="hidden relative lg:flex lg:flex-1 bg-cover bg-center bg-no-repeat" style="background-image: url('images/login-bg.jpg');">
          <!-- Dark overlay for better text readability -->
          <div class="absolute inset-0 bg-black bg-opacity-40"></div>

          <!-- Centered text content -->
          <div class="relative z-10 flex items-center justify-center w-full h-full p-8">
              <div class="text-center max-w-md text-white">
                  <h3 class="text-3xl font-bold mb-6 text-white">Join thousands of users</h3>
                  <p class="text-gray-100 text-lg leading-relaxed">
                      Experience the next generation of our platform with enhanced security and seamless user experience.
                  </p>
              </div>
          </div>
        </div>
    </div>

    <!-- Include API helper -->
    <script src="js/api.js"></script>
    <script>
        // Show alert function
        function showAlert(message, type = 'error') {
            const alertContainer = document.getElementById('alert-container');
            if (!alertContainer) return;

            const alertClass = type === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700';
            const iconClass = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';

            alertContainer.innerHTML = `
                <div class="border mt-4 px-4 py-3 rounded-xl ${alertClass} flex items-center">
                    <i class="fas ${iconClass} mr-2"></i>
                    <span>${message}</span>
                    <button class="ml-auto text-lg leading-none" onclick="this.parentElement.remove()">
                        &times;
                    </button>
                </div>
            `;

            // Auto-hide after 5 seconds
            setTimeout(() => {
                if (alertContainer.firstElementChild) {
                    alertContainer.firstElementChild.remove();
                }
            }, 5000);
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }

        // Handle login form submission
        async function handleLogin(event) {
            event.preventDefault();

            const form = document.getElementById('loginForm');
            const submitBtn = form.querySelector('button[type="submit"]');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Set loading state
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing In...';

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showAlert(result.message || 'Login successful!', 'success');

                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = result.data.redirect;
                    }, 1000);
                } else {
                    showAlert(result.error || 'Login failed');
                }
            } catch (error) {
                console.error('Login error:', error);
                showAlert('Network error. Please try again.');
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }

        // Auto-focus on email field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>