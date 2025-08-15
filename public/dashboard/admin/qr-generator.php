<?php
    /**
     * QR Code Generator for Residents
     * Generates QR codes containing resident user IDs for attendance scanning
     */

    session_start();

    // Check if user is logged in and is admin/superadmin
    if (! isset($_SESSION['user_id']) || ! in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
        header('Location: ../../login.php');
        exit;
    }

    require_once __DIR__ . '/../../../config/db.php';
    require_once __DIR__ . '/../../../src/models/User.php';

    // Use QR Code library - we'll use a PHP QR Code library
    // For now, we'll use a simple online service or implement a basic solution

    global $db;
    $connection = $db->getConnection();
    $user       = new User();

    // Get current admin info
    $adminId   = $_SESSION['user_id'];
    $adminInfo = $user->findById($adminId);

    if (! $adminInfo) {
        session_destroy();
        header('Location: ../../login.php?message=session_expired');
        exit;
    }

    // Get admin's sector assignment
    $adminSectorQuery = "
    SELECT aa.sector_id, s.name as sector_name, s.code as sector_code
    FROM admin_assignments aa
    JOIN sectors s ON aa.sector_id = s.id
    WHERE aa.admin_id = ? AND aa.is_active = 1
    LIMIT 1";

    $stmt = $connection->prepare($adminSectorQuery);
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $adminSector = $stmt->get_result()->fetch_assoc();

    if (! $adminSector) {
        die('Error: Admin is not assigned to any sector.');
    }

    $sectorId   = $adminSector['sector_id'];
    $sectorName = $adminSector['sector_name'];

    // Get residents in the sector
    $residentsQuery = "
    SELECT u.id as user_id, u.first_name, u.last_name, u.email, u.national_id, c.name as cell_name
    FROM users u
    LEFT JOIN cells c ON u.cell_id = c.id
    WHERE u.sector_id = ? AND u.role = 'resident' AND u.status = 'active'
    ORDER BY u.first_name, u.last_name";

    $stmt = $connection->prepare($residentsQuery);
    $stmt->bind_param('i', $sectorId);
    $stmt->execute();
    $residents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Handle QR code generation request
    if (isset($_GET['generate']) && $_GET['user_id']) {
        $userId = (int) $_GET['user_id'];

        // Find the resident
        $resident = null;
        foreach ($residents as $r) {
            if ($r['user_id'] == $userId) {
                $resident = $r;
                break;
            }
        }

        if ($resident) {
            // Generate QR code data - JSON format for resident
            $qrData = json_encode([
                'type'         => 'umuganda_resident',
                'id'           => $userId,
                'name'         => $resident['first_name'] . ' ' . $resident['last_name'],
                'cell'         => $resident['cell_name'] ?? 'N/A',
                'sector'       => $sectorName,
                'email'        => $resident['email'] ?? '',
                'generated_at' => date('Y-m-d H:i:s'),
            ]);

            // Use online QR code generator (temporary solution)
            $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);

            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode([
                'success'     => true,
                'qr_code_url' => $qrCodeUrl,
                'qr_data'     => $qrData,
                'resident'    => $resident,
            ]);
            exit;
        }
    }

    // Extract user information for display
    $firstName = htmlspecialchars($adminInfo['first_name']);
    $lastName  = htmlspecialchars($adminInfo['last_name']);
    $fullName  = $firstName . ' ' . $lastName;
    $initials  = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
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

        <!-- QR Generator Content -->
        <main class="p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center mb-2">
                                <button onclick="window.location.href='attendance-marking.php'"
                                    class="mr-3 p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-arrow-left"></i>
                                </button>
                                <h1 class="text-2xl font-bold text-gray-900">QR Code Generator</h1>
                            </div>
                            <p class="text-gray-600">
                                Generate QR codes for residents in                                                                                                                                                                                                       <?php echo htmlspecialchars($sectorName); ?> Sector
                            </p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex gap-3">
                            <button onclick="generateAllQRCodes()"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                                <i class="fas fa-qrcode mr-2"></i>
                                Generate All QR Codes
                            </button>
                            <button onclick="printQRCodes()"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                                <i class="fas fa-print mr-2"></i>
                                Print QR Codes
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Residents Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php foreach ($residents as $resident): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all duration-300">
                            <!-- Resident Info -->
                            <div class="text-center mb-4">
                                <div class="w-16 h-16 mx-auto bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center mb-3">
                                    <span class="text-white text-lg font-semibold">
                                        <?php echo strtoupper(substr($resident['first_name'], 0, 1) . substr($resident['last_name'], 0, 1)); ?>
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?>
                                </h3>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($resident['cell_name'] ?: 'N/A'); ?></p>
                                <p class="text-xs text-gray-500">ID:                                                                                                                                                                                                             <?php echo $resident['user_id']; ?></p>
                            </div>

                            <!-- QR Code Area -->
                            <div class="qr-code-container mb-4" id="qr-container-<?php echo $resident['user_id']; ?>">
                                <div class="w-32 h-32 mx-auto bg-gray-100 rounded-lg flex items-center justify-center">
                                    <div class="text-center">
                                        <i class="fas fa-qrcode text-2xl text-gray-400 mb-2"></i>
                                        <p class="text-xs text-gray-500">Click Generate</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <button onclick="generateQRCode(<?php echo $resident['user_id']; ?>, this)"
                                    class="flex-1 bg-primary-600 text-white py-2 px-3 rounded-lg text-sm hover:bg-primary-700 transition-colors">
                                    <i class="fas fa-qrcode mr-1"></i>
                                    Generate
                                </button>
                                <button onclick="downloadQRCode(<?php echo $resident['user_id']; ?>)"
                                    class="bg-gray-100 text-gray-700 py-2 px-3 rounded-lg text-sm hover:bg-gray-200 transition-colors"
                                    disabled id="download-btn-<?php echo $resident['user_id']; ?>">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Generate QR code for a specific resident using production API
        async function generateQRCode(userId, buttonElement) {
            try {
                const button = buttonElement;
                const originalHtml = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Generating...';

                // Try production API first, fallback to simple method
                try {
                    const response = await fetch(`/api/qr-generator.php?user_id=${userId}`);
                    const data = await response.json();

                    if (data.success) {
                        const container = document.getElementById(`qr-container-${userId}`);
                        container.innerHTML = `
                            <img src="${data.qr_code_url}"
                                 alt="QR Code for ${data.resident.first_name}"
                                 class="w-32 h-32 mx-auto rounded-lg border"
                                 data-user-id="${userId}">
                            ${data.cached ? '<div class="text-xs text-blue-600 mt-1">Cached</div>' : '<div class="text-xs text-green-600 mt-1">New</div>'}
                        `;

                        // Enable download button
                        document.getElementById(`download-btn-${userId}`).disabled = false;

                        button.innerHTML = '<i class="fas fa-check mr-1"></i> Generated';
                        button.className = 'flex-1 bg-green-600 text-white py-2 px-3 rounded-lg text-sm';
                        return;
                    }
                } catch (apiError) {
                    console.log('Production API failed, using fallback:', apiError);
                }

                // Fallback to simple QR generation
                const fallbackResponse = await fetch(`qr-generator.php?generate=1&user_id=${userId}`);
                const fallbackData = await fallbackResponse.json();

                if (fallbackData.success) {
                    const container = document.getElementById(`qr-container-${userId}`);
                    container.innerHTML = `
                        <img src="${fallbackData.qr_code_url}"
                             alt="QR Code for ${fallbackData.resident.first_name}"
                             class="w-32 h-32 mx-auto rounded-lg border"
                             data-user-id="${userId}">
                        <div class="text-xs text-orange-600 mt-1">Fallback</div>
                    `;

                    // Enable download button
                    document.getElementById(`download-btn-${userId}`).disabled = false;

                    button.innerHTML = '<i class="fas fa-check mr-1"></i> Generated';
                    button.className = 'flex-1 bg-green-600 text-white py-2 px-3 rounded-lg text-sm';
                } else {
                    throw new Error(fallbackData.message || 'Failed to generate QR code');
                }

            } catch (error) {
                console.error('Error generating QR code:', error);
                alert(`Error: ${error.message}`);

                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        }

        // Generate all QR codes using batch API
        async function generateAllQRCodes() {
            try {
                const sectorId =                                                                 <?php echo $sectorId; ?>;
                const button = event.target;
                const originalHtml = button.innerHTML;

                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating All...';

                const response = await fetch(`/api/qr-generator.php?batch=1&sector_id=${sectorId}`);
                const data = await response.json();

                if (data.success) {
                    // Update UI for each generated QR code
                    data.results.forEach(result => {
                        if (result.success) {
                            const container = document.getElementById(`qr-container-${result.user_id}`);
                            if (container) {
                                container.innerHTML = `
                                    <img src="${result.url}"
                                         alt="QR Code for ${result.name}"
                                         class="w-32 h-32 mx-auto rounded-lg border"
                                         data-user-id="${result.user_id}">
                                    ${result.cached ? '<div class="text-xs text-blue-600 mt-1">Cached</div>' : '<div class="text-xs text-green-600 mt-1">New</div>'}
                                `;

                                // Enable download button
                                document.getElementById(`download-btn-${result.user_id}`).disabled = false;

                                // Update generate button
                                const genBtn = container.parentElement.querySelector('button[onclick*="generateQRCode"]');
                                if (genBtn) {
                                    genBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Generated';
                                    genBtn.className = 'flex-1 bg-green-600 text-white py-2 px-3 rounded-lg text-sm';
                                }
                            }
                        }
                    });

                    alert(`Batch generation complete!\nSuccessful: ${data.successful}\nFailed: ${data.failed}\nTotal: ${data.total}`);
                } else {
                    throw new Error(data.message || 'Batch generation failed');
                }
            } catch (error) {
                console.error('Error in batch generation:', error);
                alert(`Error: ${error.message}`);
            } finally {
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        }

        // Download QR code
        function downloadQRCode(userId) {
            const img = document.querySelector(`#qr-container-${userId} img`);
            if (img) {
                const link = document.createElement('a');
                link.href = img.src;
                link.download = `qr-code-resident-${userId}.png`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } else {
                alert('Please generate the QR code first');
            }
        }

        // Print QR codes - Enhanced for production
        function printQRCodes() {
            const printWindow = window.open('', '_blank');
            const qrCodes = document.querySelectorAll('.qr-code-container img');

            if (qrCodes.length === 0) {
                alert('Please generate QR codes first');
                return;
            }

            let htmlContent = `
                <html>
                <head>
                    <title>QR Codes -                                                                           <?php echo htmlspecialchars($sectorName); ?> Sector</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 0;
                            padding: 20px;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 30px;
                            border-bottom: 2px solid #3B82F6;
                            padding-bottom: 15px;
                        }
                        .qr-grid {
                            display: grid;
                            grid-template-columns: repeat(3, 1fr);
                            gap: 25px;
                            padding: 20px 0;
                        }
                        .qr-item {
                            text-align: center;
                            page-break-inside: avoid;
                            border: 1px solid #E5E7EB;
                            border-radius: 8px;
                            padding: 15px;
                            background: #F9FAFB;
                        }
                        .qr-item img {
                            width: 120px;
                            height: 120px;
                            border: 1px solid #D1D5DB;
                            border-radius: 4px;
                        }
                        .qr-item h3 {
                            margin: 10px 0 5px 0;
                            font-size: 14px;
                            font-weight: bold;
                            color: #1F2937;
                        }
                        .qr-item p {
                            margin: 2px 0;
                            font-size: 12px;
                            color: #6B7280;
                        }
                        .footer {
                            text-align: center;
                            margin-top: 30px;
                            font-size: 10px;
                            color: #9CA3AF;
                        }
                        @media print {
                            .qr-grid { grid-template-columns: repeat(2, 1fr); }
                            body { padding: 10px; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Umuganda Digital - QR Codes</h1>
                        <h2><?php echo htmlspecialchars($sectorName); ?> Sector</h2>
                        <p>Generated on: ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</p>
                    </div>
                    <div class="qr-grid">
            `;

            qrCodes.forEach(img => {
                const userId = img.getAttribute('data-user-id');
                const container = img.closest('.bg-white');
                const name = container.querySelector('h3').textContent;
                const cell = container.querySelector('.text-sm.text-gray-600').textContent;

                htmlContent += `
                    <div class="qr-item">
                        <img src="${img.src}" alt="QR Code for ${name}">
                        <h3>${name}</h3>
                        <p>Cell: ${cell}</p>
                        <p>Resident ID: ${userId}</p>
                    </div>
                `;
            });

            htmlContent += `
                    </div>
                    <div class="footer">
                        <p>Scan QR codes with the Umuganda Digital mobile app for quick attendance marking</p>
                    </div>
                </body>
                </html>
            `;

            printWindow.document.write(htmlContent);
            printWindow.document.close();

            // Wait for images to load before printing
            setTimeout(() => {
                printWindow.print();
            }, 1000);
        }

        // Auto-generate QR codes on page load for better UX
        document.addEventListener('DOMContentLoaded', function() {
            // Optional: Auto-generate first few QR codes
            const residents =                                                           <?php echo json_encode(array_slice(array_column($residents, 'user_id'), 0, 5)); ?>;
            setTimeout(() => {
                residents.forEach((userId, index) => {
                    setTimeout(() => {
                        const button = document.querySelector(`button[onclick*="generateQRCode(${userId}"]`);
                        if (button) {
                            generateQRCode(userId, button);
                        }
                    }, index * 200);
                });
            }, 500);
        });
    </script>

    <!-- Footer -->
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
