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
    require_once __DIR__ . '/../../../src/models/Fine.php';
    require_once __DIR__ . '/../../../src/models/UmugandaEvent.php';
    require_once __DIR__ . '/../../../src/helpers/functions.php';

    // Get user data
    $userModel = new User();
    $user      = $userModel->findByIdWithLocation($_SESSION['user_id']);

    if (! $user) {
        // User not found, logout and redirect
        session_destroy();
        header('Location: ../../login.php?message=session_expired');
        exit;
    }

    // Extract user information for display
    $firstName  = htmlspecialchars($user['first_name']);
    $lastName   = htmlspecialchars($user['last_name']);
    $fullName   = $firstName . ' ' . $lastName;
    $email      = htmlspecialchars($user['email']);
    $phone      = htmlspecialchars($user['phone']);
    $nationalId = htmlspecialchars($user['national_id']);
    $location   = htmlspecialchars($user['cell_name'] . ', ' . $user['sector_name'] . ', ' . $user['district_name']);
    $initials   = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

    // Initialize models
    $fineModel  = new Fine();
    $eventModel = new UmugandaEvent();

    // Get filter parameters
    $selectedYear = $_GET['year'] ?? date('Y');

    // Get fine statistics
    $fineStats = $fineModel->getUserFineStats($_SESSION['user_id']);

    // Calculate totals
    $totalFines        = $fineStats['total_fines'] ?? 0;
    $outstandingAmount = $fineStats['total_outstanding'] ?? 0;
    $paidAmount        = $fineStats['total_paid'] ?? 0;
    $totalAmount       = $outstandingAmount + $paidAmount;
    $paymentRate       = $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100) : 0;

    // Get outstanding fines
    $outstandingFines = $fineModel->getUserOutstandingFines($_SESSION['user_id']);

    // Get all fines for history (with filtering)
    $filters = ['user_id' => $_SESSION['user_id']];
    if ($selectedYear !== 'all') {
        $filters['year'] = $selectedYear;
    }

    $allFines = $fineModel->getUserFines($_SESSION['user_id'], $filters);

    // Get available years for filter
    $availableYears = [];
    $currentYear    = date('Y');
    for ($year = $currentYear; $year >= 2020; $year--) {
        $availableYears[] = $year;
    }

?>

<!-- Header -->
<?php include __DIR__ . '/partials/header.php'; ?>

<style>
    .payment-fields {
        transition: all 0.3s ease-in-out;
    }

    .payment-fields.hidden {
        display: none;
    }

    input[type="radio"]:checked + div {
        background-color: #f0f9ff;
        border-color: #0ea5e9;
    }

    .modal-backdrop {
        backdrop-filter: blur(4px);
    }
</style>

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
                <div id="notification" class="hidden fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg border-l-4 border-green-500">
                    <div class="p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg id="notificationIcon" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p id="notificationMessage" class="text-sm font-medium text-gray-900"></p>
                            </div>
                            <div class="ml-auto pl-3">
                                <div class="-mx-1.5 -my-1.5">
                                    <button onclick="hideNotification()" class="inline-flex bg-white rounded-md p-1.5 text-gray-400 hover:text-gray-500">
                                        <span class="sr-only">Dismiss</span>
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="max-w-7xl mx-auto space-y-6">

                    <!-- Outstanding Fines Alert -->
                    <?php if ($outstandingAmount > 0): ?>
                    <div
                        class="bg-gradient-to-r from-danger-50 to-red-50 border-l-4 border-danger-500 rounded-xl shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-danger-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-lg font-medium text-danger-800">Outstanding Fines</h3>
                                <p class="text-sm text-danger-700 mt-1">You have unpaid fines totaling <span
                                        class="font-bold"><?php echo number_format($outstandingAmount); ?> RWF</span>. Please settle your payments to avoid
                                    additional penalties.</p>
                            </div>
                            <div class="ml-4">
                                <button onclick="payAllFines()"
                                    class="bg-gradient-to-r from-danger-600 to-red-600 hover:from-danger-700 hover:to-red-700 text-white px-6 py-2 rounded-lg font-medium transition-all duration-200 shadow-lg hover:shadow-xl">
                                    Pay All Fines
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div
                        class="bg-gradient-to-r from-success-50 to-emerald-50 border-l-4 border-success-500 rounded-xl shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-lg font-medium text-success-800">No Outstanding Fines</h3>
                                <p class="text-sm text-success-700 mt-1">Great job! You have no unpaid fines at this time. Keep attending community work sessions to maintain this status.</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Total Fines -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-danger-500 to-danger-600 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($totalAmount); ?> RWF</div>
                                    <div class="text-sm text-gray-600">Total Fines</div>
                                </div>
                            </div>
                        </div>

                        <!-- Outstanding -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-warning-500 to-warning-600 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($outstandingAmount); ?> RWF</div>
                                    <div class="text-sm text-gray-600">Outstanding</div>
                                </div>
                            </div>
                        </div>

                        <!-- Paid -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-success-500 to-success-600 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($paidAmount); ?> RWF</div>
                                    <div class="text-sm text-gray-600">Paid</div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Rate -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900"><?php echo $paymentRate; ?>%</div>
                                    <div class="text-sm text-gray-600">Payment Rate</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Outstanding Fines Section -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-danger-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                                Unpaid Fines
                            </h3>
                            <span class="bg-danger-100 text-danger-800 text-sm font-medium px-3 py-1 rounded-full">
                                <?php echo count($outstandingFines); ?> Pending
                            </span>
                        </div>

                        <div class="space-y-4">
                            <?php if (! empty($outstandingFines)): ?>
<?php foreach ($outstandingFines as $fine):
        $eventDate    = new DateTime($fine['event_date']);
        $dueDate      = $fine['due_date'] ? new DateTime($fine['due_date']) : null;
        $today        = new DateTime();
        $isOverdue    = $dueDate && $dueDate < $today;
        $daysOverdue  = $isOverdue ? $today->diff($dueDate)->days : 0;
        $daysUntilDue = ! $isOverdue && $dueDate ? $today->diff($dueDate)->days : 0;

        $statusClass = $isOverdue ? 'from-danger-50 to-red-50 border-danger-200' : 'from-warning-50 to-amber-50 border-warning-200';
        $statusBadge = $isOverdue ? 'bg-danger-100 text-danger-800' : 'bg-warning-100 text-warning-800';
        $statusText  = $isOverdue ? 'OVERDUE' : 'DUE SOON';
        $buttonClass = $isOverdue ? 'from-danger-600 to-red-600 hover:from-danger-700 hover:to-red-700' : 'from-warning-600 to-amber-600 hover:from-warning-700 hover:to-amber-700';
    ?>
			                            <div class="bg-gradient-to-r<?php echo $statusClass; ?> rounded-lg p-4">
			                                <div class="flex items-center justify-between">
			                                    <div class="flex-1">
			                                        <div class="flex items-center space-x-2 mb-2">
			                                            <span class="<?php echo $statusBadge; ?> text-xs font-medium px-2 py-1 rounded-full">
			                                                <?php echo $statusText; ?>
			                                            </span>
			                                            <span class="text-sm text-gray-600"><?php echo $eventDate->format('M j, Y'); ?></span>
			                                        </div>
			                                        <h4 class="font-medium text-gray-900 mb-1">
			                                            <?php echo htmlspecialchars($fine['event_title']); ?>
			                                        </h4>
			                                        <p class="text-sm text-gray-600 mb-2">
			                                            <?php echo htmlspecialchars($fine['reason'] ?? 'Missed mandatory community work session'); ?>
			                                        </p>
			                                        <div class="flex items-center space-x-4 text-sm">
			                                            <span class="text-gray-500">Fine Amount:
			                                                <span class="font-medium			                                                                        		                                                                        	                                                                         <?php echo $isOverdue ? 'text-danger-600' : 'text-warning-600'; ?>">
			                                                    <?php echo number_format($fine['amount']); ?> RWF
			                                                </span>
			                                            </span>
			                                            <?php if ($dueDate): ?>
			                                            <span class="text-gray-500">Due:<?php echo $dueDate->format('M j, Y'); ?></span>
			                                            <?php endif; ?>
<?php if ($isOverdue): ?>
                                            <span class="text-danger-600 font-medium"><?php echo $daysOverdue; ?> day<?php echo $daysOverdue != 1 ? 's' : ''; ?> overdue</span>
                                            <?php elseif ($daysUntilDue > 0): ?>
                                            <span class="text-warning-600 font-medium"><?php echo $daysUntilDue; ?> day<?php echo $daysUntilDue != 1 ? 's' : ''; ?> remaining</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <button onclick="payFine('<?php echo $fine['id']; ?>', '<?php echo $fine['amount']; ?>')"
                                            class="bg-gradient-to-r                                                                                                                                                                                                          <?php echo $buttonClass; ?> text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 shadow-lg hover:shadow-xl">
                                            Pay Now
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
<?php else: ?>
                            <div class="text-center py-8">
                                <div class="p-4 bg-success-100 rounded-full w-16 h-16 mx-auto mb-4">
                                    <svg class="w-8 h-8 text-success-600 mx-auto mt-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-600 font-medium">No outstanding fines</p>
                                <p class="text-sm text-gray-500 mt-1">All your fines have been paid</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Payment History Section -->
                    <div
                        class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-success-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    Payment History
                                </h3>
                                <div class="flex space-x-2">
                                    <select id="yearFilter" onchange="applyYearFilter()"
                                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="all"                                                                                                                                                                                  <?php echo $selectedYear == 'all' ? 'selected' : ''; ?>>All Years</option>
                                        <?php foreach ($availableYears as $year): ?>
                                        <option value="<?php echo $year; ?>"<?php echo $year == $selectedYear ? 'selected' : ''; ?>>
                                            <?php echo $year; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button
                                        class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm hover:bg-primary-700 transition-colors">
                                        Export PDF
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop Table View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Method</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Receipt</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (! empty($allFines)): ?>
<?php foreach ($allFines as $fine):
        $fineDate    = new DateTime($fine['created_at']);
        $statusClass = '';
        $statusText  = '';

        switch ($fine['status']) {
            case 'paid':
                $statusClass = 'bg-success-100 text-success-800';
                $statusText  = 'Paid';
                break;
            case 'pending':
                $statusClass = 'bg-warning-100 text-warning-800';
                $statusText  = 'Pending';
                break;
            case 'overdue':
                $statusClass = 'bg-danger-100 text-danger-800';
                $statusText  = 'Overdue';
                break;
            default:
                $statusClass = 'bg-gray-100 text-gray-800';
                $statusText  = ucfirst($fine['status']);
        }
    ?>
			                                    <tr class="hover:bg-gray-50 transition-colors">
			                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
			                                            <?php echo $fine['paid_date'] ? (new DateTime($fine['paid_date']))->format('M j, Y') : $fineDate->format('M j, Y'); ?>
			                                        </td>
			                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
			                                            <?php echo htmlspecialchars($fine['event_title'] ?? 'Fine'); ?>
<?php if ($fine['reason']): ?>
			                                                <br><span class="text-xs text-gray-400"><?php echo htmlspecialchars($fine['reason']); ?></span>
			                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            <?php echo number_format($fine['amount']); ?> RWF
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $fine['payment_method'] ?: '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium                                                                                                                                                                                                                                                                                                                                                                                                 <?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($fine['status'] == 'paid' && $fine['payment_reference']): ?>
                                            <button onclick="downloadReceipt('<?php echo $fine['id']; ?>')"
                                                class="text-primary-600 hover:text-primary-900 text-sm font-medium">Download</button>
                                            <?php else: ?>
                                            <span class="text-gray-400 text-sm">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
<?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <div class="text-gray-500">
                                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                                <p class="text-lg font-medium">No fine records found</p>
                                                <p class="text-sm">No fines have been issued for the selected period</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="md:hidden space-y-4 p-4">
                            <?php if (! empty($allFines)): ?>
<?php foreach ($allFines as $fine):
        $fineDate    = new DateTime($fine['created_at']);
        $statusClass = '';
        $statusText  = '';
        $cardClass   = '';

        switch ($fine['status']) {
            case 'paid':
                $statusClass = 'bg-success-100 text-success-800';
                $statusText  = 'Paid';
                $cardClass   = 'bg-gradient-to-r from-success-50 to-emerald-50 border border-success-200';
                break;
            case 'pending':
                $statusClass = 'bg-warning-100 text-warning-800';
                $statusText  = 'Pending';
                $cardClass   = 'bg-gradient-to-r from-warning-50 to-amber-50 border border-warning-200';
                break;
            case 'overdue':
                $statusClass = 'bg-danger-100 text-danger-800';
                $statusText  = 'Overdue';
                $cardClass   = 'bg-gradient-to-r from-danger-50 to-red-50 border border-danger-200';
                break;
            default:
                $statusClass = 'bg-gray-100 text-gray-800';
                $statusText  = ucfirst($fine['status']);
                $cardClass   = 'bg-gradient-to-r from-gray-50 to-slate-50 border border-gray-200';
        }
    ?>
			                            <div class="<?php echo $cardClass; ?> rounded-lg p-4">
			                                <div class="flex items-center justify-between mb-2">
			                                    <h4 class="font-medium text-gray-900">
			                                        <?php echo $fine['paid_date'] ? (new DateTime($fine['paid_date']))->format('M j, Y') : $fineDate->format('M j, Y'); ?>
			                                    </h4>
			                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium			                                                                                                                        		                                                                                                                        	                                                                                                                         <?php echo $statusClass; ?>">
			                                        <?php echo $statusText; ?>
			                                    </span>
			                                </div>
			                                <div class="text-sm text-gray-600 space-y-1">
			                                    <p><span class="font-medium">Description:</span>			                                                                                    		                                                                                    	                                                                                     <?php echo htmlspecialchars($fine['event_title'] ?? 'Fine'); ?></p>
			                                    <?php if ($fine['reason']): ?>
			                                    <p><span class="font-medium">Reason:</span><?php echo htmlspecialchars($fine['reason']); ?></p>
			                                    <?php endif; ?>
                                    <p><span class="font-medium">Amount:</span><?php echo number_format($fine['amount']); ?> RWF</p>
                                    <?php if ($fine['payment_method']): ?>
                                    <p><span class="font-medium">Method:</span><?php echo htmlspecialchars($fine['payment_method']); ?></p>
                                    <?php endif; ?>
<?php if ($fine['status'] == 'overdue'): ?>
<?php
    $dueDate     = $fine['due_date'] ? new DateTime($fine['due_date']) : null;
    $today       = new DateTime();
    $daysOverdue = $dueDate ? $today->diff($dueDate)->days : 0;
?>
                                    <p><span class="font-medium text-danger-600"><?php echo $daysOverdue; ?> days overdue</span></p>
                                    <?php endif; ?>
                                </div>
                                <?php if ($fine['status'] == 'paid' && $fine['payment_reference']): ?>
                                <button onclick="downloadReceipt('<?php echo $fine['id']; ?>')"
                                    class="mt-2 text-primary-600 hover:text-primary-900 text-sm font-medium">Download Receipt</button>
                                <?php elseif ($fine['status'] != 'paid'): ?>
                                <button onclick="payFine('<?php echo $fine['id']; ?>', '<?php echo $fine['amount']; ?>')"
                                    class="mt-2 bg-danger-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-danger-700 transition-colors">
                                    Pay Now
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
<?php else: ?>
                            <div class="text-center py-8">
                                <div class="text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No fine records found</p>
                                    <p class="text-sm">No fines have been issued for the selected period</p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Payment Methods Section -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                </path>
                            </svg>
                            Available Payment Methods
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div
                                class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 text-center">
                                <div
                                    class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <h4 class="font-medium text-gray-900 mb-1">Mobile Money</h4>
                                <p class="text-sm text-gray-600">MTN/Airtel Money</p>
                            </div>
                            <div
                                class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4 text-center">
                                <div
                                    class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                </div>
                                <h4 class="font-medium text-gray-900 mb-1">Bank Transfer</h4>
                                <p class="text-sm text-gray-600">Direct bank payment</p>
                            </div>
                            <div
                                class="bg-gradient-to-r from-purple-50 to-violet-50 border border-purple-200 rounded-lg p-4 text-center">
                                <div
                                    class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                    </svg>
                                </div>
                                <h4 class="font-medium text-gray-900 mb-1">Cash Payment</h4>
                                <p class="text-sm text-gray-600">Local office payment</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-backdrop"></div>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">

                <!-- Modal Header -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Pay Fine</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Amount: <span id="modalFineAmount" class="font-semibold text-primary-600"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-4 pb-4 sm:px-6">
                    <!-- Payment Method Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="paymentMethod" value="mobile_money" class="mr-3 text-primary-600" checked>
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium">Mobile Money (MTN/Airtel)</span>
                                </div>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="paymentMethod" value="bank_transfer" class="mr-3 text-primary-600">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium">Bank Transfer</span>
                                </div>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="paymentMethod" value="cash" class="mr-3 text-primary-600">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                            </path>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium">Cash Payment (Local Office)</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div id="paymentDetails" class="mb-4">
                        <!-- Mobile Money Fields -->
                        <div id="mobileMoneyFields" class="payment-fields">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" id="phoneNumber"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                placeholder="07XXXXXXXX" maxlength="10">
                            <p class="text-xs text-gray-500 mt-1">Enter your MTN or Airtel mobile money number</p>
                        </div>

                        <!-- Bank Transfer Fields -->
                        <div id="bankTransferFields" class="payment-fields hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bank Account Number</label>
                            <input type="text" id="bankAccount"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Enter your bank account number">
                            <p class="text-xs text-gray-500 mt-1">You will receive bank transfer details after confirmation</p>
                        </div>

                        <!-- Cash Payment Fields -->
                        <div id="cashFields" class="payment-fields hidden">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                                <p class="text-sm text-yellow-800">
                                    <strong>Cash Payment Instructions:</strong><br>
                                    Visit your local community office during business hours (8:00 AM - 5:00 PM) to make your payment.
                                    Bring a valid ID and reference this fine number.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div id="paymentLoading" class="hidden text-center py-4">
                        <div class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing payment...
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button id="payButton" onclick="processPayment()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Pay Now
                    </button>
                    <button onclick="closePaymentModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables for payment
        let currentFineId = null;
        let currentFineAmount = null;

        // Notification functions
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const icon = document.getElementById('notificationIcon');
            const messageEl = document.getElementById('notificationMessage');

            messageEl.textContent = message;

            // Update colors and icon based on type
            if (type === 'success') {
                notification.className = 'fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg border-l-4 border-green-500';
                icon.className = 'h-5 w-5 text-green-400';
                icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>';
            } else {
                notification.className = 'fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg border-l-4 border-red-500';
                icon.className = 'h-5 w-5 text-red-400';
                icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
            }

            notification.classList.remove('hidden');

            // Auto-hide after 5 seconds
            setTimeout(hideNotification, 5000);
        }

        function hideNotification() {
            document.getElementById('notification').classList.add('hidden');
        }

        // Payment functionality
        function payFine(fineId, amount) {
            // Set fine details for payment modal
            currentFineId = fineId;
            currentFineAmount = amount;

            // Update modal with fine amount
            document.getElementById('modalFineAmount').textContent = parseInt(amount).toLocaleString() + ' RWF';

            // Reset form
            resetPaymentForm();

            // Show modal
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function payAllFines() {
            // Get all outstanding fine IDs and total amount
            const outstandingFines =                                                                                                             <?php echo json_encode($outstandingFines); ?>;

            if (outstandingFines.length === 0) {
                showNotification('No outstanding fines to pay', 'error');
                return;
            }

            const totalAmount = outstandingFines.reduce((sum, fine) => sum + parseFloat(fine.amount), 0);

            if (confirm(`Pay all ${outstandingFines.length} outstanding fines for a total of ${totalAmount.toLocaleString()} RWF?`)) {
                // For now, show a message that this feature is coming soon
                showNotification('Multiple fine payment feature coming soon...', 'error');
            }
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
            resetPaymentForm();
        }

        function resetPaymentForm() {
            // Reset radio buttons
            document.querySelector('input[name="paymentMethod"][value="mobile_money"]').checked = true;

            // Clear input fields
            document.getElementById('phoneNumber').value = '';
            document.getElementById('bankAccount').value = '';

            // Show mobile money fields by default
            showPaymentFields('mobile_money');

            // Hide loading state
            document.getElementById('paymentLoading').classList.add('hidden');
            document.getElementById('payButton').classList.remove('hidden');
        }

        function showPaymentFields(method) {
            // Hide all payment fields
            document.querySelectorAll('.payment-fields').forEach(field => {
                field.classList.add('hidden');
            });

            // Show selected payment method fields
            switch(method) {
                case 'mobile_money':
                    document.getElementById('mobileMoneyFields').classList.remove('hidden');
                    break;
                case 'bank_transfer':
                    document.getElementById('bankTransferFields').classList.remove('hidden');
                    break;
                case 'cash':
                    document.getElementById('cashFields').classList.remove('hidden');
                    break;
            }
        }

        async function processPayment() {
            const selectedMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
            let paymentData = {
                fine_id: currentFineId,
                payment_method: selectedMethod
            };

            // Validate and add method-specific data
            if (selectedMethod === 'mobile_money') {
                const phoneNumber = document.getElementById('phoneNumber').value.trim();
                if (!phoneNumber || phoneNumber.length < 10) {
                    showNotification('Please enter a valid phone number (at least 10 digits)', 'error');
                    return;
                }
                paymentData.phone_number = phoneNumber;
            } else if (selectedMethod === 'bank_transfer') {
                const bankAccount = document.getElementById('bankAccount').value.trim();
                if (!bankAccount || bankAccount.length < 5) {
                    showNotification('Please enter a valid bank account number', 'error');
                    return;
                }
                paymentData.bank_account = bankAccount;
            }

            // Show loading state
            document.getElementById('paymentLoading').classList.remove('hidden');
            document.getElementById('payButton').classList.add('hidden');

            try {
                const response = await fetch('process_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(paymentData)
                });

                const result = await response.json();

                if (result.success) {
                    showNotification(`Payment successful! Reference: ${result.payment_reference}`, 'success');
                    closePaymentModal();
                    // Reload page to update the fine status after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showNotification(`Payment failed: ${result.message}`, 'error');
                }
            } catch (error) {
                console.error('Payment error:', error);
                showNotification('An error occurred while processing payment. Please try again.', 'error');
            } finally {
                // Hide loading state
                document.getElementById('paymentLoading').classList.add('hidden');
                document.getElementById('payButton').classList.remove('hidden');
            }
        }

        function downloadReceipt(fineId) {
            // Handle receipt download
            alert(`Downloading receipt for fine ID: ${fineId}`);
            // In a real implementation, this would trigger a PDF download
        }

        function applyYearFilter() {
            const yearFilter = document.getElementById('yearFilter');
            const year = yearFilter ? yearFilter.value : 'all';

            // Build URL with year filter
            const url = new URL(window.location.href);
            url.searchParams.set('year', year);

            // Redirect to filtered URL
            window.location.href = url.toString();
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize filter values on page load
            const yearFilter = document.getElementById('yearFilter');
            if (yearFilter) {
                yearFilter.value = '<?php echo $selectedYear; ?>';
            }

            // Add event listeners for payment method radio buttons
            document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    showPaymentFields(this.value);
                });
            });

            // Close modal when clicking outside
            document.getElementById('paymentModal').addEventListener('click', function (e) {
                if (e.target === this) {
                    closePaymentModal();
                }
            });

            // Format phone number input
            document.getElementById('phoneNumber').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
                if (value.length > 10) {
                    value = value.substring(0, 10);
                }
                e.target.value = value;
            });
        });
    </script>

<!-- Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>