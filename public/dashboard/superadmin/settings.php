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
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 ml-4 lg:ml-0">System Settings</h1>
                            <p class="text-gray-600 mt-1 ml-4 lg:ml-0">Configure system preferences and administrative
                                controls</p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                            <button
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Export Settings
                            </button>
                            <button id="saveSettingsBtn"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg text-sm font-medium hover:from-primary-700 hover:to-primary-800 shadow-sm transition-all">
                                <i class="fas fa-save mr-2"></i>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Settings Navigation Tabs -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-8">
                    <div class="flex flex-wrap gap-2">
                        <button class="settings-tab active px-4 py-2 text-sm font-medium rounded-lg transition-all"
                            data-tab="general">
                            <i class="fas fa-cog mr-2"></i>General
                        </button>
                        <button
                            class="settings-tab px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-all"
                            data-tab="notifications">
                            <i class="fas fa-bell mr-2"></i>Notifications
                        </button>
                        <button
                            class="settings-tab px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-all"
                            data-tab="security">
                            <i class="fas fa-shield-alt mr-2"></i>Security
                        </button>
                        <button
                            class="settings-tab px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-all"
                            data-tab="users">
                            <i class="fas fa-users mr-2"></i>User Management
                        </button>
                        <button
                            class="settings-tab px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-all"
                            data-tab="integrations">
                            <i class="fas fa-plug mr-2"></i>Integrations
                        </button>
                        <button
                            class="settings-tab px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-all"
                            data-tab="maintenance">
                            <i class="fas fa-tools mr-2"></i>Maintenance
                        </button>
                    </div>
                </div>

                <!-- General Settings Tab -->
                <div id="general-tab" class="tab-content">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- System Configuration -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-cogs text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">System Configuration</h3>
                                    <p class="text-sm text-gray-600">Basic system settings and preferences</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">System Name</label>
                                    <input type="text" value="Umuganda Digital Tracker"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Language</label>
                                    <select
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="en">English</option>
                                        <option value="rw" selected>Kinyarwanda</option>
                                        <option value="fr">French</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Time Zone</label>
                                    <select
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="Africa/Kigali" selected>Africa/Kigali (CAT)</option>
                                        <option value="UTC">UTC</option>
                                        <option value="Africa/Nairobi">Africa/Nairobi (EAT)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Format</label>
                                    <select
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="DD/MM/YYYY" selected>DD/MM/YYYY</option>
                                        <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                                        <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Umuganda Settings -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-calendar-check text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Umuganda Configuration</h3>
                                    <p class="text-sm text-gray-600">Configure Umuganda-specific settings</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Umuganda
                                        Day</label>
                                    <select
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="saturday" selected>Saturday</option>
                                        <option value="sunday">Sunday</option>
                                        <option value="friday">Friday</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Session Duration
                                        (hours)</label>
                                    <input type="number" value="3" min="1" max="8"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Fine Amount
                                        (RWF)</label>
                                    <input type="number" value="5000" min="1000" max="50000" step="500"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Attendance Grace Period
                                        (minutes)</label>
                                    <input type="number" value="15" min="0" max="60"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- Feature Toggles -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-toggle-on text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Feature Toggles</h3>
                                    <p class="text-sm text-gray-600">Enable or disable system features</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">SMS Notifications</span>
                                        <p class="text-xs text-gray-500">Send SMS alerts to residents</p>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="sms"></div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Email Notifications</span>
                                        <p class="text-xs text-gray-500">Send email alerts to residents</p>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="email"></div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Automatic Fine Collection</span>
                                        <p class="text-xs text-gray-500">Auto-generate fines for absences</p>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="auto-fines"></div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Mobile App Integration</span>
                                        <p class="text-xs text-gray-500">Enable mobile app features</p>
                                    </div>
                                    <div class="toggle-switch" data-toggle="mobile-app"></div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Advanced Analytics</span>
                                        <p class="text-xs text-gray-500">Enable detailed reporting features</p>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="analytics"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Backup & Data -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-database text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Backup & Data</h3>
                                    <p class="text-sm text-gray-600">Data backup and storage settings</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Automatic Backup
                                        Frequency</label>
                                    <select
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="daily" selected>Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="manual">Manual Only</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Retention Period
                                        (months)</label>
                                    <input type="number" value="24" min="6" max="120"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>

                                <div class="flex space-x-3">
                                    <button
                                        class="flex-1 bg-primary-100 text-primary-700 py-2 px-4 rounded-lg font-medium hover:bg-primary-200 transition-colors">
                                        <i class="fas fa-download mr-2"></i>
                                        Create Backup
                                    </button>
                                    <button
                                        class="flex-1 bg-green-100 text-green-700 py-2 px-4 rounded-lg font-medium hover:bg-green-200 transition-colors">
                                        <i class="fas fa-upload mr-2"></i>
                                        Restore Data
                                    </button>
                                </div>

                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Last Backup:</span>
                                        <span class="font-medium text-gray-900">July 25, 2025 at 02:00 AM</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm mt-1">
                                        <span class="text-gray-600">Backup Size:</span>
                                        <span class="font-medium text-gray-900">124.5 MB</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Tab (hidden by default) -->
                <div id="notifications-tab" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Email Settings -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-envelope text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Email Configuration</h3>
                                    <p class="text-sm text-gray-600">Configure email delivery settings</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Server</label>
                                    <input type="text" value="smtp.gmail.com"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Port</label>
                                        <input type="number" value="587"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
                                        <select
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                            <option value="tls" selected>TLS</option>
                                            <option value="ssl">SSL</option>
                                            <option value="none">None</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">From Email</label>
                                    <input type="email" value="noreply@umuganda.gov.rw"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>

                                <button
                                    class="w-full bg-primary-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Test Email Configuration
                                </button>
                            </div>
                        </div>

                        <!-- SMS Settings -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-sms text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">SMS Configuration</h3>
                                    <p class="text-sm text-gray-600">Configure SMS delivery settings</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">SMS Provider</label>
                                    <select
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="twilio">Twilio</option>
                                        <option value="africastalking" selected>Africa's Talking</option>
                                        <option value="nexmo">Vonage (Nexmo)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                                    <input type="password" value="••••••••••••••••"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sender ID</label>
                                    <input type="text" value="UMUGANDA"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>

                                <div class="bg-yellow-50 p-3 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                                        <span class="text-sm text-yellow-800">SMS Credit Balance: 2,450 credits</span>
                                    </div>
                                </div>

                                <button
                                    class="w-full bg-green-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors">
                                    <i class="fas fa-mobile-alt mr-2"></i>
                                    Test SMS Configuration
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Tab (hidden by default) -->
                <div id="security-tab" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Access Control -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-shield-alt text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Access Control</h3>
                                    <p class="text-sm text-gray-600">Security and access settings</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Two-Factor Authentication</span>
                                        <p class="text-xs text-gray-500">Require 2FA for admin users</p>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="2fa"></div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Session Timeout</span>
                                        <p class="text-xs text-gray-500">Auto-logout after inactivity</p>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="session-timeout"></div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Policy</label>
                                    <select
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="basic">Basic (8+ characters)</option>
                                        <option value="medium" selected>Medium (8+ chars, mixed case, numbers)</option>
                                        <option value="strong">Strong (12+ chars, mixed case, numbers, symbols)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Session Duration
                                        (hours)</label>
                                    <input type="number" value="8" min="1" max="24"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Login
                                        Attempts</label>
                                    <input type="number" value="5" min="3" max="10"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- API Security -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-key text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">API Security</h3>
                                    <p class="text-sm text-gray-600">API access and security settings</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">API Access</span>
                                        <p class="text-xs text-gray-500">Enable external API access</p>
                                    </div>
                                    <div class="toggle-switch" data-toggle="api-access"></div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">API Rate Limit
                                        (requests/hour)</label>
                                    <input type="number" value="1000" min="100" max="10000" step="100"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Active API Keys</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600">Mobile App Integration</span>
                                            <button class="text-red-600 hover:text-red-900">Revoke</button>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600">Analytics Dashboard</span>
                                            <button class="text-red-600 hover:text-red-900">Revoke</button>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    class="w-full bg-purple-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-purple-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Generate New API Key
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Management Tab (hidden by default) -->
                <div id="users-tab" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Admin Users -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-user-shield text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Admin Users</h3>
                                    <p class="text-sm text-gray-600">Manage administrative accounts</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm font-medium">AD</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Admin User</p>
                                            <p class="text-xs text-gray-500">admin@umuganda.gov.rw</p>
                                        </div>
                                    </div>
                                    <span
                                        class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">Active</span>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm font-medium">JM</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Jean Mukamana</p>
                                            <p class="text-xs text-gray-500">jean.mukamana@umuganda.gov.rw</p>
                                        </div>
                                    </div>
                                    <span
                                        class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">Active</span>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-8 h-8 bg-gradient-to-br from-orange-500 to-orange-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm font-medium">PK</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Paul Kagame</p>
                                            <p class="text-xs text-gray-500">paul.kagame@umuganda.gov.rw</p>
                                        </div>
                                    </div>
                                    <span
                                        class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full font-medium">Pending</span>
                                </div>

                                <button
                                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add New Admin User
                                </button>
                            </div>
                        </div>

                        <!-- User Permissions -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-user-lock text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Default Permissions</h3>
                                    <p class="text-sm text-gray-600">Configure default user permissions</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">View Attendance Records</span>
                                        <p class="text-xs text-gray-500">Allow users to view attendance data</p>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="view-attendance"></div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Edit Personal Profile</span>
                                        <p class="text-xs text-gray-500">Allow users to update their profiles</p>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="edit-profile"></div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">View Community Notices</span>
                                        <p class="text-xs text-gray-500">Allow users to read community notices</p>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="view-notices"></div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Submit Feedback</span>
                                        <p class="text-xs text-gray-500">Allow users to submit feedback</p>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="submit-feedback"></div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Export Data</span>
                                        <p class="text-xs text-gray-500">Allow users to export their data</p>
                                    </div>
                                    <div class="toggle-switch" data-toggle="export-data"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Integrations Tab (hidden by default) -->
                <div id="integrations-tab" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- External Services -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-plug text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">External Services</h3>
                                    <p class="text-sm text-gray-600">Manage third-party integrations</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fab fa-google text-blue-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Google Maps</p>
                                            <p class="text-xs text-gray-500">Location services integration</p>
                                        </div>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="google-maps"></div>
                                </div>

                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                            <i class="fab fa-whatsapp text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">WhatsApp Business</p>
                                            <p class="text-xs text-gray-500">WhatsApp notifications</p>
                                        </div>
                                    </div>
                                    <div class="toggle-switch" data-toggle="whatsapp"></div>
                                </div>

                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-credit-card text-purple-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Mobile Money</p>
                                            <p class="text-xs text-gray-500">MTN/Airtel Money integration</p>
                                        </div>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="mobile-money"></div>
                                </div>

                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-id-card text-red-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">National ID System</p>
                                            <p class="text-xs text-gray-500">NIDA integration for verification</p>
                                        </div>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="nida"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Sync -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-sync-alt text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Data Synchronization</h3>
                                    <p class="text-sm text-gray-600">Configure data sync settings</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sync Frequency</label>
                                    <select
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="realtime">Real-time</option>
                                        <option value="hourly" selected>Every Hour</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                    </select>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Auto Sync</span>
                                        <p class="text-xs text-gray-500">Automatically sync data changes</p>
                                    </div>
                                    <div class="toggle-switch active" data-toggle="auto-sync"></div>
                                </div>

                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="flex items-center justify-between text-sm mb-2">
                                        <span class="text-gray-600">Last Sync:</span>
                                        <span class="font-medium text-gray-900">July 25, 2025 at 10:30 AM</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Sync Status:</span>
                                        <span class="text-green-600 font-medium">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Successful
                                        </span>
                                    </div>
                                </div>

                                <button
                                    class="w-full bg-cyan-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-cyan-700 transition-colors">
                                    <i class="fas fa-sync-alt mr-2"></i>
                                    Force Sync Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Tab (hidden by default) -->
                <div id="maintenance-tab" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- System Maintenance -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-tools text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">System Maintenance</h3>
                                    <p class="text-sm text-gray-600">Perform system maintenance tasks</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <button
                                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-broom mr-2"></i>
                                    Clear System Cache
                                </button>

                                <button
                                    class="w-full bg-green-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors">
                                    <i class="fas fa-database mr-2"></i>
                                    Optimize Database
                                </button>

                                <button
                                    class="w-full bg-purple-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-purple-700 transition-colors">
                                    <i class="fas fa-file-alt mr-2"></i>
                                    Generate System Report
                                </button>

                                <button
                                    class="w-full bg-yellow-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-yellow-700 transition-colors">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Enable Maintenance Mode
                                </button>

                                <div class="bg-yellow-50 p-3 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                                        <span class="text-sm text-yellow-800">Last maintenance: July 20, 2025</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Logs -->
                        <div class="setting-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-gray-500 to-gray-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-file-alt text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">System Logs</h3>
                                    <p class="text-sm text-gray-600">View and manage system logs</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Log Level</label>
                                    <select
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="error">Error Only</option>
                                        <option value="warning">Warning & Error</option>
                                        <option value="info" selected>Info, Warning & Error</option>
                                        <option value="debug">All (Debug Mode)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Log Retention
                                        (days)</label>
                                    <input type="number" value="30" min="7" max="365"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>

                                <div class="space-y-2">
                                    <button
                                        class="w-full bg-gray-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-gray-700 transition-colors">
                                        <i class="fas fa-eye mr-2"></i>
                                        View System Logs
                                    </button>
                                    <button
                                        class="w-full bg-red-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-red-700 transition-colors">
                                        <i class="fas fa-trash mr-2"></i>
                                        Clear Old Logs
                                    </button>
                                </div>

                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Log File Size:</span>
                                        <span class="font-medium text-gray-900">12.3 MB</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm mt-1">
                                        <span class="text-gray-600">Recent Errors:</span>
                                        <span class="font-medium text-gray-900">2 in last 24h</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Status Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
                    <!-- System Status -->
                    <div
                        class="bg-gradient-to-br from-white to-green-50 rounded-xl shadow-sm p-6 border border-green-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-green-600 uppercase tracking-wide">System Status
                                </p>
                                <p class="text-2xl font-black text-gray-900 mt-2">Online</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-success-600 font-semibold bg-success-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-check-circle text-xs mr-1"></i>
                                        Operational
                                    </span>
                                </div>
                            </div>
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-success-500 to-success-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-server text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Database -->
                    <div
                        class="bg-gradient-to-br from-white to-blue-50 rounded-xl shadow-sm p-6 border border-blue-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Database</p>
                                <p class="text-2xl font-black text-gray-900 mt-2">124.5 MB</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-primary-600 font-semibold bg-primary-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-database text-xs mr-1"></i>
                                        Healthy
                                    </span>
                                </div>
                            </div>
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-database text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Active Users -->
                    <div
                        class="bg-gradient-to-br from-white to-purple-50 rounded-xl shadow-sm p-6 border border-purple-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-purple-600 uppercase tracking-wide">Active Users
                                </p>
                                <p class="text-2xl font-black text-gray-900 mt-2">1,247</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-purple-600 font-semibold bg-purple-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-users text-xs mr-1"></i>
                                        Connected
                                    </span>
                                </div>
                            </div>
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-users text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Security -->
                    <div
                        class="bg-gradient-to-br from-white to-yellow-50 rounded-xl shadow-sm p-6 border border-yellow-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-yellow-600 uppercase tracking-wide">Security</p>
                                <p class="text-2xl font-black text-gray-900 mt-2">98.5%</p>
                                <div class="flex items-center mt-3">
                                    <span
                                        class="inline-flex items-center text-sm text-warning-600 font-semibold bg-warning-50 px-2 py-1 rounded-full">
                                        <i class="fas fa-shield-alt text-xs mr-1"></i>
                                        Secure
                                    </span>
                                </div>
                            </div>
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-shield-alt text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        // Settings Tab functionality
        const settingsTabs = document.querySelectorAll('.settings-tab');
        const tabContents = document.querySelectorAll('.tab-content');

        settingsTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetTab = tab.dataset.tab;

                // Remove active class from all tabs
                settingsTabs.forEach(t => {
                    t.classList.remove('active');
                    t.classList.add('text-gray-700', 'hover:bg-gray-100');
                });

                // Add active class to clicked tab
                tab.classList.add('active');
                tab.classList.remove('text-gray-700', 'hover:bg-gray-100');

                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });

                // Show target tab content
                const targetContent = document.getElementById(`${targetTab}-tab`);
                if (targetContent) {
                    targetContent.classList.remove('hidden');
                }
            });
        });

        // Toggle switch functionality
        const toggleSwitches = document.querySelectorAll('.toggle-switch');

        toggleSwitches.forEach(toggle => {
            toggle.addEventListener('click', () => {
                toggle.classList.toggle('active');
            });
        });

        // Save settings functionality
        const saveSettingsBtn = document.getElementById('saveSettingsBtn');

        saveSettingsBtn.addEventListener('click', () => {
            // Show success message (you can replace this with actual save functionality)
            const originalText = saveSettingsBtn.innerHTML;
            saveSettingsBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Settings Saved!';
            saveSettingsBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            saveSettingsBtn.classList.remove('bg-gradient-to-r', 'from-primary-600', 'to-primary-700', 'hover:from-primary-700', 'hover:to-primary-800');

            setTimeout(() => {
                saveSettingsBtn.innerHTML = originalText;
                saveSettingsBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                saveSettingsBtn.classList.add('bg-gradient-to-r', 'from-primary-600', 'to-primary-700', 'hover:from-primary-700', 'hover:to-primary-800');
            }, 2000);
        });

    </script>


<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>