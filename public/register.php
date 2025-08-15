<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Umuganda Digital</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .loading-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M21 12a9 9 0 11-6.219-8.56'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    <div class="min-h-screen flex justify-center">
        <!-- Left Column - Register Form -->
        <div
            class="w-full lg:w-3/5 flex items-center justify-center px-4 py-8 sm:px-6 lg:px-8 backdrop-blur-sm">
            <div class="max-w-4xl w-full space-y-8 animate-slide-up">
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

                <!-- Tab Navigation -->
                <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg mb-6">
                    <button id="tab-personal" onclick="switchTab('personal')"
                        class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors bg-white text-blue-600 shadow-sm">
                        Personal Info
                    </button>
                    <button id="tab-location" onclick="switchTab('location')" disabled
                        class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors text-gray-400 cursor-not-allowed disabled:opacity-50">
                        Location Details
                        <span id="lock-icon" class="ml-2">🔒</span>
                    </button>
                </div>

                <!-- Register Form -->
                <form id="registerForm" class="space-y-6" onsubmit="handleRegister(event)">
                    <!-- Personal Information Tab -->
                    <div id="personal-tab" class="tab-content active">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email address</label>
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

                            <!-- Date of Birth Input -->
                            <div class="group">
                                <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <input id="date_of_birth" name="date_of_birth" type="date" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400">
                                </div>
                            </div>

                            <!-- Gender Input -->
                            <div class="group">
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                    <select id="gender" name="gender" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400">
                                        <option value="">Select gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
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
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
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

                        <!-- Next Button -->
                        <div class="flex justify-end">
                            <button type="button" onclick="nextTab()"
                                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                Next: Location Details
                                <svg class="inline-block ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Location Details Tab -->
                    <div id="location-tab" class="tab-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Province -->
                            <div class="group">
                                <label for="province_id" class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <select id="province_id" name="province_id" required onchange="loadDistricts()"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400">
                                        <option value="">Select Province</option>
                                    </select>
                                </div>
                            </div>

                            <!-- District -->
                            <div class="group">
                                <label for="district_id" class="block text-sm font-medium text-gray-700 mb-2">District</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <select id="district_id" name="district_id" required onchange="loadSectors()" disabled
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 disabled:bg-gray-100">
                                        <option value="">Select District</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Sector -->
                            <div class="group">
                                <label for="sector_id" class="block text-sm font-medium text-gray-700 mb-2">Sector</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7.01 14.94V16.92a2 2 0 01-2 2H3.96a2 2 0 01-2-2v-1.98"></path>
                                        </svg>
                                    </div>
                                    <select id="sector_id" name="sector_id" required onchange="loadCells()" disabled
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 disabled:bg-gray-100">
                                        <option value="">Select Sector</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Cell -->
                            <div class="group">
                                <label for="cell_id" class="block text-sm font-medium text-gray-700 mb-2">Cell</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v2z"></path>
                                        </svg>
                                    </div>
                                    <select id="cell_id" name="cell_id" required disabled
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 disabled:bg-gray-100">
                                        <option value="">Select Cell</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="flex items-center mt-6">
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

                        <!-- Action Buttons -->
                        <div class="flex justify-between mt-6">
                            <button type="button" onclick="prevTab()"
                                class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                <svg class="inline-block mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Back
                            </button>

                            <button type="submit" id="submitBtn"
                                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                                <span id="submitText">Create Account</span>
                            </button>
                        </div>
                    </div>

                    <!-- Sign in link -->
                    <div class="text-center mt-6">
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
    </div>

    <script>
        let currentTab = 'personal';
        let personalInfoCompleted = false;

        // Tab switching functions
        function switchTab(tab) {
            // Prevent switching to location tab if personal info is not completed
            if (tab === 'location' && !personalInfoCompleted) {
                showAlert('Please complete your personal information first');
                return;
            }

            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.remove('active');
            });

            // Remove active state from all tab buttons
            document.querySelectorAll('[id^="tab-"]').forEach(el => {
                el.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
                el.classList.add('text-gray-600', 'hover:text-gray-900');
            });

            // Show selected tab
            document.getElementById(tab + '-tab').classList.add('active');

            // Add active state to selected tab button
            const tabButton = document.getElementById('tab-' + tab);
            tabButton.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
            tabButton.classList.remove('text-gray-600', 'hover:text-gray-900');

            currentTab = tab;

            // Load provinces when switching to location tab
            if (tab === 'location' && document.getElementById('province_id').children.length === 1) {
                loadProvinces();
            }
        }

        function nextTab() {
            if (currentTab === 'personal') {
                // Validate personal info first
                if (validatePersonalInfo()) {
                    personalInfoCompleted = true;
                    enableLocationTab();
                    switchTab('location');
                }
            }
        }

        function prevTab() {
            if (currentTab === 'location') {
                switchTab('personal');
            }
        }

        function enableLocationTab() {
            const locationTab = document.getElementById('tab-location');
            const lockIcon = document.getElementById('lock-icon');

            locationTab.disabled = false;
            locationTab.classList.remove('text-gray-400', 'cursor-not-allowed', 'disabled:opacity-50');
            locationTab.classList.add('text-gray-600', 'hover:text-gray-900');
            lockIcon.textContent = '✓';
            lockIcon.style.color = 'green';
        }

        function validatePersonalInfo() {
            const requiredFields = ['first_name', 'last_name', 'email', 'phone', 'national_id', 'date_of_birth', 'gender', 'password', 'confirm_password'];
            let isValid = true;
            let firstErrorField = null;

            // Clear any existing field error styles
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                input.classList.remove('border-red-500', 'ring-red-500');
            });

            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    showAlert(`Please fill in the ${field.replace('_', ' ')} field`);
                    input.classList.add('border-red-500', 'ring-red-500');
                    if (!firstErrorField) {
                        firstErrorField = input;
                    }
                    isValid = false;
                }
            });

            if (!isValid && firstErrorField) {
                firstErrorField.focus();
                return false;
            }

            // Check passwords match
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                showAlert('Passwords do not match');
                document.getElementById('confirm_password').classList.add('border-red-500', 'ring-red-500');
                document.getElementById('confirm_password').focus();
                return false;
            }

            // Check password length
            if (password.length < 6) {
                showAlert('Password must be at least 6 characters long');
                document.getElementById('password').classList.add('border-red-500', 'ring-red-500');
                document.getElementById('password').focus();
                return false;
            }

            // Check national ID length and format
            const nationalId = document.getElementById('national_id').value;
            if (nationalId.length !== 16) {
                showAlert('National ID must be exactly 16 digits');
                document.getElementById('national_id').classList.add('border-red-500', 'ring-red-500');
                document.getElementById('national_id').focus();
                return false;
            }

            // Check if national ID contains only numbers
            if (!/^\d{16}$/.test(nationalId)) {
                showAlert('National ID must contain only numbers');
                document.getElementById('national_id').classList.add('border-red-500', 'ring-red-500');
                document.getElementById('national_id').focus();
                return false;
            }

            // Validate email format
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('Please enter a valid email address');
                document.getElementById('email').classList.add('border-red-500', 'ring-red-500');
                document.getElementById('email').focus();
                return false;
            }

            // Validate phone format (Rwanda format)
            const phone = document.getElementById('phone').value;
            const phoneRegex = /^(\+250|250|0)?[7][0-9]{8}$/;
            if (!phoneRegex.test(phone.replace(/\s/g, ''))) {
                showAlert('Please enter a valid Rwandan phone number (e.g., 0788123456)');
                document.getElementById('phone').classList.add('border-red-500', 'ring-red-500');
                document.getElementById('phone').focus();
                return false;
            }

            return true;
        }

        // Location loading functions
        async function loadProvinces() {
            const provinceSelect = document.getElementById('province_id');
            provinceSelect.innerHTML = '<option value="">Loading provinces...</option>';
            provinceSelect.classList.add('loading-select');

            try {
                console.log('Fetching provinces from: /api/locations?action=provinces');
                const response = await fetch('/api/locations?action=provinces');

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Provinces data:', data);

                provinceSelect.innerHTML = '<option value="">Select Province</option>';

                if (data.success) {
                    data.data.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.id;
                        option.textContent = province.name;
                        provinceSelect.appendChild(option);
                    });
                } else {
                    provinceSelect.innerHTML = '<option value="">Error loading provinces</option>';
                    showAlert('Failed to load provinces: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error loading provinces:', error);
                provinceSelect.innerHTML = '<option value="">Error loading provinces</option>';
                showAlert('Error loading provinces: ' + error.message);
            } finally {
                provinceSelect.classList.remove('loading-select');
            }
        }

        async function loadDistricts() {
            const provinceId = document.getElementById('province_id').value;
            const districtSelect = document.getElementById('district_id');
            const sectorSelect = document.getElementById('sector_id');
            const cellSelect = document.getElementById('cell_id');

            // Reset dependent dropdowns
            districtSelect.innerHTML = '<option value="">Select District</option>';
            sectorSelect.innerHTML = '<option value="">Select Sector</option>';
            cellSelect.innerHTML = '<option value="">Select Cell</option>';
            sectorSelect.disabled = true;
            cellSelect.disabled = true;

            if (!provinceId) {
                districtSelect.disabled = true;
                return;
            }

            districtSelect.innerHTML = '<option value="">Loading districts...</option>';
            districtSelect.classList.add('loading-select');
            districtSelect.disabled = true;

            try {
                const response = await fetch(`/api/locations?action=districts&province_id=${provinceId}`);
                const data = await response.json();

                districtSelect.innerHTML = '<option value="">Select District</option>';

                if (data.success) {
                    data.data.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.name;
                        districtSelect.appendChild(option);
                    });
                    districtSelect.disabled = false;
                } else {
                    districtSelect.innerHTML = '<option value="">Error loading districts</option>';
                    showAlert('Failed to load districts. Please try again.');
                }
            } catch (error) {
                console.error('Error loading districts:', error);
                districtSelect.innerHTML = '<option value="">Error loading districts</option>';
                showAlert('Error loading districts. Please check your connection.');
            } finally {
                districtSelect.classList.remove('loading-select');
            }
        }

        async function loadSectors() {
            const districtId = document.getElementById('district_id').value;
            const sectorSelect = document.getElementById('sector_id');
            const cellSelect = document.getElementById('cell_id');

            // Reset dependent dropdowns
            sectorSelect.innerHTML = '<option value="">Select Sector</option>';
            cellSelect.innerHTML = '<option value="">Select Cell</option>';
            cellSelect.disabled = true;

            if (!districtId) {
                sectorSelect.disabled = true;
                return;
            }

            sectorSelect.innerHTML = '<option value="">Loading sectors...</option>';
            sectorSelect.classList.add('loading-select');
            sectorSelect.disabled = true;

            try {
                const response = await fetch(`/api/locations?action=sectors&district_id=${districtId}`);
                const data = await response.json();

                sectorSelect.innerHTML = '<option value="">Select Sector</option>';

                if (data.success) {
                    data.data.forEach(sector => {
                        const option = document.createElement('option');
                        option.value = sector.id;
                        option.textContent = sector.name;
                        sectorSelect.appendChild(option);
                    });
                    sectorSelect.disabled = false;
                } else {
                    sectorSelect.innerHTML = '<option value="">Error loading sectors</option>';
                    showAlert('Failed to load sectors. Please try again.');
                }
            } catch (error) {
                console.error('Error loading sectors:', error);
                sectorSelect.innerHTML = '<option value="">Error loading sectors</option>';
                showAlert('Error loading sectors. Please check your connection.');
            } finally {
                sectorSelect.classList.remove('loading-select');
            }
        }

        async function loadCells() {
            const sectorId = document.getElementById('sector_id').value;
            const cellSelect = document.getElementById('cell_id');

            cellSelect.innerHTML = '<option value="">Select Cell</option>';

            if (!sectorId) {
                cellSelect.disabled = true;
                return;
            }

            cellSelect.innerHTML = '<option value="">Loading cells...</option>';
            cellSelect.classList.add('loading-select');
            cellSelect.disabled = true;

            try {
                const response = await fetch(`/api/locations?action=cells&sector_id=${sectorId}`);
                const data = await response.json();

                cellSelect.innerHTML = '<option value="">Select Cell</option>';

                if (data.success) {
                    data.data.forEach(cell => {
                        const option = document.createElement('option');
                        option.value = cell.id;
                        option.textContent = cell.name;
                        cellSelect.appendChild(option);
                    });
                    cellSelect.disabled = false;
                } else {
                    cellSelect.innerHTML = '<option value="">Error loading cells</option>';
                    showAlert('Failed to load cells. Please try again.');
                }
            } catch (error) {
                console.error('Error loading cells:', error);
                cellSelect.innerHTML = '<option value="">Error loading cells</option>';
                showAlert('Error loading cells. Please check your connection.');
            } finally {
                cellSelect.classList.remove('loading-select');
            }
        }        function togglePassword(inputId) {
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

        async function handleRegister(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');

            // Validate location fields
            const requiredLocationFields = ['province_id', 'district_id', 'sector_id', 'cell_id'];
            for (const field of requiredLocationFields) {
                if (!formData.get(field)) {
                    showAlert(`Please select a ${field.replace('_', ' ').replace('id', '')}`);
                    return;
                }
            }

            // Check terms acceptance
            if (!formData.get('terms')) {
                showAlert('Please accept the terms and conditions');
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

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Set default tab
            switchTab('personal');

            // Add input event listeners to clear error styles
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.classList.remove('border-red-500', 'ring-red-500');
                });
            });

            // Auto-format phone number
            const phoneInput = document.getElementById('phone');
            phoneInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.startsWith('250')) {
                    value = '+' + value;
                } else if (value.length === 9 && value.startsWith('7')) {
                    value = '+250' + value;
                } else if (value.length === 10 && value.startsWith('07')) {
                    value = '+250' + value.substring(1);
                }
                this.value = value;
            });

            // Auto-format national ID (numbers only)
            const nationalIdInput = document.getElementById('national_id');
            nationalIdInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').substring(0, 16);
            });
        });
    </script>
</body>

</html>
