<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Umuganda Tracker - Community Work Management</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        'success': {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                        },
                        'warning': {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                        },
                        'danger': {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 font-sans">
    <!-- Navbar -->
    <nav class="bg-white/90 backdrop-blur-sm shadow-lg sticky py-2 top-0 z-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-18">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-white font-bold text-lg">U</span>
                    </div>
                    <div>
                        <span class="text-xl font-bold text-gray-800">Umuganda Tracker</span>
                        <p class="text-sm text-gray-600 hidden sm:block">Community Work Management</p>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#home"
                        class="text-gray-700 hover:text-primary-600 transition duration-200 font-medium">Home</a>
                    <a href="#features"
                        class="text-gray-700 hover:text-primary-600 transition duration-200 font-medium">Features</a>
                    <a href="#how-it-works"
                        class="text-gray-700 hover:text-primary-600 transition duration-200 font-medium">How It
                        Works</a>
                    <a href="#about"
                        class="text-gray-700 hover:text-primary-600 transition duration-200 font-medium">About</a>
                    <div class="flex space-x-3">
                        <a href="login.php"
                            class="bg-gradient-to-r from-primary-600 to-primary-700 text-white px-6 py-2 rounded-lg hover:shadow-lg transition duration-300 font-medium">
                            Login
                        </a>
                        <a href="register.php"
                            class="border border-primary-600 text-primary-600 px-6 py-2 rounded-lg hover:bg-primary-50 transition duration-300 font-medium">
                            Register
                        </a>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button"
                        class="text-gray-700 hover:text-primary-600 focus:outline-none p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="hidden md:hidden bg-white/95 backdrop-blur-sm border-t border-gray-200">
            <div class="px-4 pt-2 pb-3 space-y-1">
                <a href="#home"
                    class="block px-3 py-2 text-gray-700 hover:text-primary-600 font-medium rounded-lg hover:bg-gray-50 transition-colors">Home</a>
                <a href="#features"
                    class="block px-3 py-2 text-gray-700 hover:text-primary-600 font-medium rounded-lg hover:bg-gray-50 transition-colors">Features</a>
                <a href="#how-it-works"
                    class="block px-3 py-2 text-gray-700 hover:text-primary-600 font-medium rounded-lg hover:bg-gray-50 transition-colors">How
                    It Works</a>
                <a href="#about"
                    class="block px-3 py-2 text-gray-700 hover:text-primary-600 font-medium rounded-lg hover:bg-gray-50 transition-colors">About</a>
                <div class="flex flex-col space-y-2 pt-2">
                    <a href="login.html"
                        class="bg-gradient-to-r from-primary-600 to-primary-700 text-white px-3 py-2 rounded-lg hover:shadow-lg transition duration-300 font-medium text-center">
                        Login
                    </a>
                    <a href="register.html"
                        class="border border-primary-600 text-primary-600 px-3 py-2 rounded-lg hover:bg-primary-50 transition duration-300 font-medium text-center">
                        Register
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="relative min-h-screen flex items-center justify-center overflow-hidden"
        style="background-image: url('images/umuganda-hero.png'); background-position: center; background-repeat: no-repeat; background-size: cover;">
        <div class="absolute inset-0 bg-gradient-to-br from-black/70 via-black/50 to-primary-900/60"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-8">
                <span
                    class="inline-block bg-primary-100 text-primary-800 text-sm font-semibold px-4 py-2 rounded-full mb-4 animate-fade-in">
                    🇷🇼 Empowering Rwanda's Communities
                </span>
            </div>
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 animate-fade-in leading-tight">
                Transform Your <span class="text-primary-400">Umuganda</span><br>
                <span class="bg-gradient-to-r from-primary-400 to-success-400 bg-clip-text text-transparent">Community
                    Work</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-200 mb-8 max-w-3xl mx-auto animate-slide-up leading-relaxed">
                Track attendance, manage fines, and strengthen community participation through our comprehensive digital
                platform designed for Rwanda's monthly community work.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center animate-slide-up mb-12"
                style="animation-delay: 0.2s;">
                <a href="register.php"
                    class="bg-gradient-to-r from-primary-600 to-primary-700 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:shadow-xl transform hover:-translate-y-1 transition duration-300 flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <span>Get Started</span>
                </a>
                <button onclick="scrollToSection('features')"
                    class="border-2 border-white/50 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-white/10 transition duration-300 flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Learn More</span>
                </button>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto animate-slide-up"
                style="animation-delay: 0.4s;">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
                    <div class="text-3xl font-bold text-white mb-2">500+</div>
                    <div class="text-gray-300">Communities Served</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
                    <div class="text-3xl font-bold text-white mb-2">15K+</div>
                    <div class="text-gray-300">Active Residents</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
                    <div class="text-3xl font-bold text-white mb-2">98%</div>
                    <div class="text-gray-300">Attendance Tracked</div>
                </div>
            </div>
        </div>

        <!-- Enhanced decorative elements -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0V120Z"
                    fill="white" />
            </svg>
        </div>

        <!-- Floating elements -->
        <div class="absolute top-20 left-10 w-20 h-20 bg-primary-400/20 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute bottom-40 right-10 w-32 h-32 bg-success-400/20 rounded-full blur-xl animate-pulse"
            style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-warning-400/20 rounded-full blur-xl animate-pulse"
            style="animation-delay: 2s;"></div>
    </section>

    <!-- Mission Statement Section -->
    <section id="about" class="py-16 md:py-24 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span
                    class="inline-block bg-primary-100 text-primary-800 text-sm font-semibold px-4 py-2 rounded-full mb-4">
                    Our Mission
                </span>
                <h2 class="text-3xl md:text-5xl font-bold text-gray-900 mb-8 leading-tight">
                    Why We Built <span class="text-primary-600">Umuganda Tracker</span>
                </h2>
                <p class="text-lg text-gray-700 leading-relaxed max-w-4xl mx-auto">
                    Umuganda is a powerful tool for national unity and development that brings communities together
                    every last Saturday of the month. However, tracking attendance and ensuring fair participation can
                    be challenging for local leaders. Our platform empowers communities to monitor participation
                    transparently, automate fine management, and celebrate commitment to collective development.
                </p>
            </div>

            <!-- Mission Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-primary-500 to-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Strengthen Unity</h3>
                    <p class="text-gray-600">Build stronger communities through transparent participation tracking and
                        collective accountability.</p>
                </div>
                <div class="text-center p-6">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-success-500 to-success-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Ensure Fairness</h3>
                    <p class="text-gray-600">Create equitable systems where every community member's contribution is
                        recognized and recorded.</p>
                </div>
                <div class="text-center p-6">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-warning-500 to-warning-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Drive Development</h3>
                    <p class="text-gray-600">Accelerate community development through organized participation and
                        measurable outcomes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 md:py-24 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span
                    class="inline-block bg-primary-100 text-primary-800 text-sm font-semibold px-4 py-2 rounded-full mb-4">
                    Features
                </span>
                <h2 class="text-3xl md:text-5xl font-bold text-gray-900 mb-8 leading-tight">
                    Everything You Need for<br><span class="text-primary-600">Effective Community Management</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Our comprehensive platform provides all the tools necessary to manage, track, and improve your
                    community's Umuganda participation.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Track Attendance Card -->
                <div
                    class="bg-white rounded-2xl shadow-xl p-8 hover:shadow-2xl transform hover:-translate-y-2 transition duration-300 border border-gray-100">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-8 h-8 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Digital Attendance Tracking</h3>
                    <p class="text-gray-700 mb-6 leading-relaxed">
                        Record attendance digitally in real-time with QR codes, mobile check-ins, and bulk import
                        options for efficient community management.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            QR Code Check-ins
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Real-time Updates
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Bulk Operations
                        </li>
                    </ul>
                </div>

                <!-- Manage Fines Card -->
                <div
                    class="bg-white rounded-2xl shadow-xl p-8 hover:shadow-2xl transform hover:-translate-y-2 transition duration-300 border border-gray-100">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-warning-500 to-warning-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="text-white w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Automated Fine Management</h3>
                    <p class="text-gray-700 mb-6 leading-relaxed">
                        Automatically calculate and track fines for absences with flexible payment options and
                        transparent record keeping.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Auto-calculation
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Payment Tracking
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Receipt Generation
                        </li>
                    </ul>
                </div>

                <!-- Community Reports Card -->
                <div
                    class="bg-white rounded-2xl shadow-xl p-8 hover:shadow-2xl transform hover:-translate-y-2 transition duration-300 border border-gray-100">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-success-500 to-success-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="text-white w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Comprehensive Analytics</h3>
                    <p class="text-gray-700 mb-6 leading-relaxed">
                        Generate detailed reports with attendance trends, participation metrics, and community insights
                        for informed decision-making.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Monthly Reports
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Trend Analysis
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Export Options
                        </li>
                    </ul>
                </div>

                <!-- Mobile App Card -->
                <div
                    class="bg-white rounded-2xl shadow-xl p-8 hover:shadow-2xl transform hover:-translate-y-2 transition duration-300 border border-gray-100">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="text-white w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Mobile Accessibility</h3>
                    <p class="text-gray-700 mb-6 leading-relaxed">
                        Access the platform from any device with responsive design optimized for smartphones and
                        tablets.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Cross-platform
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Offline Support
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Fast Loading
                        </li>
                    </ul>
                </div>

                <!-- Notifications Card -->
                <div
                    class="bg-white rounded-2xl shadow-xl p-8 hover:shadow-2xl transform hover:-translate-y-2 transition duration-300 border border-gray-100">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="text-white w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Smart Notifications</h3>
                    <p class="text-gray-700 mb-6 leading-relaxed">
                        Stay informed with automated reminders, schedule updates, and important community announcements.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Event Reminders
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            SMS & Email
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Custom Alerts
                        </li>
                    </ul>
                </div>

                <!-- Multi-language Card -->
                <div
                    class="bg-white rounded-2xl shadow-xl p-8 hover:shadow-2xl transform hover:-translate-y-2 transition duration-300 border border-gray-100">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-pink-500 to-pink-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="text-white w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Multi-language Support</h3>
                    <p class="text-gray-700 mb-6 leading-relaxed">
                        Available in Kinyarwanda, English, and French to ensure accessibility for all community members.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Kinyarwanda
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            English
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-success-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Français
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-16 md:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span
                    class="inline-block bg-success-100 text-success-800 text-sm font-semibold px-4 py-2 rounded-full mb-4">
                    How It Works
                </span>
                <h2 class="text-3xl md:text-5xl font-bold text-gray-900 mb-8 leading-tight">
                    Simple Steps to <span class="text-success-600">Digital Community Management</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Get started with our intuitive platform in just a few minutes. From registration to full community
                    engagement.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
                <!-- Step 1 -->
                <div class="text-center group">
                    <div class="relative mb-6">
                        <div
                            class="w-20 h-20 bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg group-hover:shadow-xl transition duration-300">
                            <span class="text-2xl font-bold text-white">1</span>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Register & Setup</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Create your account and set up your community profile with basic information about your cell or
                        sector.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center group">
                    <div class="relative mb-6">
                        <div
                            class="w-20 h-20 bg-gradient-to-r from-success-500 to-success-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg group-hover:shadow-xl transition duration-300">
                            <span class="text-2xl font-bold text-white">2</span>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Add Members</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Import your community members or add them individually with their contact information and
                        details.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center group">
                    <div class="relative mb-6">
                        <div
                            class="w-20 h-20 bg-gradient-to-r from-warning-500 to-warning-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg group-hover:shadow-xl transition duration-300">
                            <span class="text-2xl font-bold text-white">3</span>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Track Attendance</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Use QR codes or manual check-ins to record attendance during Umuganda events in real-time.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="text-center group">
                    <div class="relative mb-6">
                        <div
                            class="w-20 h-20 bg-gradient-to-r from-danger-500 to-danger-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg group-hover:shadow-xl transition duration-300">
                            <span class="text-2xl font-bold text-white">4</span>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Generate Reports</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Access comprehensive analytics, generate reports, and track community participation trends over
                        time.
                    </p>
                </div>
            </div>

            <!-- Process Visualization -->
            <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-3xl p-8 md:p-12">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">
                            Streamlined Digital Process
                        </h3>
                        <div class="space-y-6">
                            <div class="flex items-start space-x-4">
                                <div
                                    class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-2">Quick Setup</h4>
                                    <p class="text-gray-600">Get your community online in under 10 minutes with our
                                        guided setup process.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div
                                    class="w-8 h-8 bg-success-500 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-2">Real-time Tracking</h4>
                                    <p class="text-gray-600">Monitor attendance and participation as it happens with
                                        live updates.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div
                                    class="w-8 h-8 bg-warning-500 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-2">Automated Management</h4>
                                    <p class="text-gray-600">Let the system handle fine calculations, notifications, and
                                        record keeping automatically.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="bg-white rounded-2xl shadow-xl p-8 inline-block">
                            <div
                                class="w-32 h-32 bg-gradient-to-r from-primary-500 to-success-500 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-white">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">Ready to Use</h4>
                            <p class="text-gray-600">Start managing your community digitally today</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <!-- Company Info -->
                <div class="md:col-span-2">
                    <div class="flex items-center mb-6">
                        <div
                            class="w-10 h-10 bg-gradient-to-r from-primary-500 to-success-500 rounded-xl flex items-center justify-center mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-6 h-6 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                        </div>
                        <h3
                            class="text-2xl font-bold bg-gradient-to-r from-primary-400 to-success-400 bg-clip-text text-transparent">
                            Umuganda Digital
                        </h3>
                    </div>
                    <p class="text-gray-300 mb-6 leading-relaxed max-w-md">
                        Empowering Rwandan communities through digital innovation. Making Umuganda participation
                        tracking
                        simple, transparent, and accessible for everyone.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-primary-400 transition duration-200">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary-400 transition duration-200">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z" />
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary-400 transition duration-200">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-6">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="#features"
                                class="text-gray-300 hover:text-primary-400 transition duration-200">Features</a></li>
                        <li><a href="#how-it-works"
                                class="text-gray-300 hover:text-primary-400 transition duration-200">How It Works</a>
                        </li>
                        <li><a href="login.html"
                                class="text-gray-300 hover:text-primary-400 transition duration-200">Login</a></li>
                        <li><a href="register.html"
                                class="text-gray-300 hover:text-primary-400 transition duration-200">Register</a></li>
                        <li><a href="#"
                                class="text-gray-300 hover:text-primary-400 transition duration-200">Documentation</a>
                        </li>
                        <li><a href="#" class="text-gray-300 hover:text-primary-400 transition duration-200">API</a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold mb-6">Contact</h4>
                    <div class="space-y-3">
                        <div class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 mr-3 text-primary-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>info@umuganda-digital.rw</span>
                        </div>
                        <div class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 mr-3 text-primary-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>+250 788 123 456</span>
                        </div>
                        <div class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 mr-3 text-primary-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Kigali, Rwanda</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-gray-800 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-gray-400 text-sm mb-4 md:mb-0">
                        © 2024 Umuganda Digital. All rights reserved. Built with ❤️ for Rwanda.
                    </div>
                    <div class="flex space-x-6 text-sm">
                        <a href="#" class="text-gray-400 hover:text-primary-400 transition duration-200">Privacy
                            Policy</a>
                        <a href="#" class="text-gray-400 hover:text-primary-400 transition duration-200">Terms of
                            Service</a>
                        <a href="#" class="text-gray-400 hover:text-primary-400 transition duration-200">Support</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 1s ease-out;
        }

        .animate-slide-up {
            animation: slide-up 1s ease-out;
        }
    </style>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Smooth scroll animation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>

</html>