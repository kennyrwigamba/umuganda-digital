<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Umuganda Digital</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    <div class="min-h-screen flex">
        <!-- Left Column - Register Form -->
        <div
            class="flex-none w-full lg:w-2/5 flex items-center justify-center px-4 py-8 sm:px-6 lg:px-8 bg-white/80 backdrop-blur-sm">
            <div class="max-w-md w-full space-y-8 animate-slide-up">
                <!-- Logo Section -->
                <div class="text-center">
                    <div
                        class="mx-auto h-16 w-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg animate-float">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h2 class="mt-6 text-3xl font-bold text-gray-900">Create Account</h2>
                    <p class="mt-2 text-sm text-gray-600">Join us and start your journey today</p>
                </div>

                <!-- Alert Container -->
                <div id="alert-container" class="mb-4"></div>

                <!-- Register Form -->
                <form id="registerForm" class="mt-8 space-y-6" onsubmit="handleRegister(event)">
                    <div class="space-y-4">
                        <!-- First Name Input -->
                        <div class="group">
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                </div>
                                <input id="first_name" name="first_name" type="text" required
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                                    placeholder="Enter your first name">
                            </div>
                        </div>

                        <!-- Last Name Input -->
                        <div class="group">
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                </div>
                                <input id="last_name" name="last_name" type="text" required
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                                    placeholder="Enter your last name">
                            </div>
                        </div>

                        <!-- Email Input -->
                        <div class="group">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email
                                address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                                        </path>
                                    </svg>
                                </div>
                                <input id="email" name="email" type="email" required
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                                    placeholder="Enter your email">
                            </div>
                        </div>

                        <!-- Phone Number Input -->
                        <div class="group">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                        </path>
                                    </svg>
                                </div>
                                <input id="phone" name="phone" type="tel" required
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                                    placeholder="Enter your phone number">
                            </div>
                        </div>

                        <!-- National ID Input -->
                        <div class="group">
                            <label for="national_id" class="block text-sm font-medium text-gray-700 mb-2">National ID</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 114 0v2m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2">
                                        </path>
                                    </svg>
                                </div>
                                <input id="national_id" name="national_id" type="text" required maxlength="16" minlength="16"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                                    placeholder="Enter your 16-digit national ID">
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="group">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                        </path>
                                    </svg>
                                </div>
                                <input id="password" name="password" type="password" required minlength="6"
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                                    placeholder="Create a password (min 6 characters)">
                                <button type="button" onclick="togglePassword('password')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg id="eye-icon-password"
                                        class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm Password Input -->
                        <div class="group">
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm
                                Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <input id="confirm_password" name="confirm_password" type="password" required minlength="6"
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                                    placeholder="Confirm your password">
                                <button type="button" onclick="togglePassword('confirm_password')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg id="eye-icon-confirm_password"
                                        class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="flex items-center">
                        <input id="terms" name="terms" type="checkbox" required
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors">
                        <label for="terms" class="ml-2 block text-sm text-gray-700">
                            I agree to the
                            <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">Terms
                                and Conditions</a>
                            and
                            <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">Privacy
                                Policy</a>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" id="submitBtn"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-blue-300 group-hover:text-blue-200 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                                    </path>
                                </svg>
                            </span>
                            <span id="submitText">Create Account</span>
                        </button>
                    </div>

                    <!-- Sign in link -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            Already have an account?
                            <a href="login.php"
                                class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column - Image -->
        <div class="hidden relative lg:flex lg:w-3/5 bg-cover bg-center bg-no-repeat"
            style="background-image: url('images/login-bg.jpg');">
            <!-- Dark overlay for better text readability -->
            <div class="absolute inset-0 bg-black bg-opacity-40"></div>

            <!-- Centered text content -->
            <div class="relative z-10 flex items-center justify-center w-full h-full p-8">
                <div class="text-center max-w-md text-white">
                    <h3 class="text-3xl font-bold mb-6 text-white">Start your journey</h3>
                    <p class="text-gray-100 text-lg leading-relaxed">
                        Join our community and unlock access to powerful features designed to enhance your experience.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(`eye-icon-${inputId}`);

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

        function showAlert(message, type = 'error') {
            const alertContainer = document.getElementById('alert-container');
            const alertClass = type === 'success' ?
                'bg-green-50 border-green-200 text-green-800' :
                'bg-red-50 border-red-200 text-red-800';

            alertContainer.innerHTML = `
                <div class="rounded-xl border p-4 ${alertClass}">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                ${type === 'success' ?
                                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>' :
                                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.73-.833-2.5 0L3.732 16.5c-.77.833-.192 2.5 1.732 2.5z"></path>'
                                }
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">${message}</p>
                        </div>
                    </div>
                </div>
            `;
        }

        function validateForm(formData) {
            // Check if passwords match
            if (formData.get('password') !== formData.get('confirm_password')) {
                showAlert('Passwords do not match');
                return false;
            }

            // Check password length
            if (formData.get('password').length < 6) {
                showAlert('Password must be at least 6 characters long');
                return false;
            }

            // Check national ID length
            if (formData.get('national_id').length !== 16) {
                showAlert('National ID must be exactly 16 digits');
                return false;
            }

            // Check if national ID contains only numbers
            if (!/^\d{16}$/.test(formData.get('national_id'))) {
                showAlert('National ID must contain only numbers');
                return false;
            }

            return true;
        }

        async function handleRegister(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');

            // Validate form
            if (!validateForm(formData)) {
                return;
            }

            // Disable submit button
            submitBtn.disabled = true;
            submitText.textContent = 'Creating Account...';

            try {
                const response = await fetch('/api/auth/register', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('Account created successfully! Redirecting to login...', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    showAlert(data.error || data.message || 'Registration failed. Please try again.');
                }
            } catch (error) {
                console.error('Registration error:', error);
                showAlert('Network error. Please check your connection and try again.');
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitText.textContent = 'Create Account';
            }
        }
    </script>
</body>

</html>
