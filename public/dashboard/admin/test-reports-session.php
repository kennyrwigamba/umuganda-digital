<?php
session_start();

// Simulate login for testing - you can remove this after testing
if (! isset($_SESSION['user_id'])) {
    $_SESSION['user_id']   = 2; // Admin with assignments
    $_SESSION['user_role'] = 'admin';
    echo "DEBUG: Set test session for admin ID 2\n";
}

echo "Current session:\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "\n";
echo "User Role: " . ($_SESSION['user_role'] ?? 'Not set') . "\n\n";

// Test accessing the reports page logic
require_once __DIR__ . '/../../../config/db.php';

try {
    $database   = new Database();
    $connection = $database->getConnection();
    $adminId    = $_SESSION['user_id'];

    // Check if admin has sector assignment - exactly like reports.php does
    $adminSectorQuery = "
    SELECT aa.sector_id, s.name as sector_name, s.code as sector_code,
           d.name as district_name, d.id as district_id
    FROM admin_assignments aa
    JOIN sectors s ON aa.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    WHERE aa.admin_id = ? AND aa.is_active = 1
    LIMIT 1";

    $stmt = $connection->prepare($adminSectorQuery);
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $adminSector = $stmt->get_result()->fetch_assoc();

    if (! $adminSector) {
        echo "ERROR: Admin is not assigned to any sector. Please contact super admin.\n";
        exit;
    }

    echo "✓ Admin sector assignment found:\n";
    echo "Sector: {$adminSector['sector_name']} ({$adminSector['sector_code']})\n";
    echo "District: {$adminSector['district_name']}\n";
    echo "Sector ID: {$adminSector['sector_id']}\n\n";

    // Test some basic stats
    $sectorId  = $adminSector['sector_id'];
    $startDate = date('Y-m-d', strtotime('-30 days'));
    $endDate   = date('Y-m-d');

    // Test fines stats
    $fineQuery = "
    SELECT
        COUNT(*) as total_fines,
        SUM(CASE WHEN f.status = 'paid' THEN f.amount ELSE 0 END) as total_collected,
        SUM(CASE WHEN f.status = 'pending' THEN f.amount ELSE 0 END) as total_pending,
        SUM(f.amount) as total_amount,
        ROUND(SUM(CASE WHEN f.status = 'paid' THEN f.amount ELSE 0 END) / SUM(f.amount) * 100, 1) as collection_rate
    FROM fines f
    JOIN users u ON f.user_id = u.id
    WHERE u.sector_id = ? AND f.created_at BETWEEN ? AND ?";

    $stmt = $connection->prepare($fineQuery);
    $stmt->bind_param('iss', $sectorId, $startDate, $endDate);
    $stmt->execute();
    $fineStats = $stmt->get_result()->fetch_assoc();

    echo "Sample stats (last 30 days):\n";
    echo "Total fines: {$fineStats['total_fines']}\n";
    echo "Total collected: {$fineStats['total_collected']} RWF\n";
    echo "Collection rate: {$fineStats['collection_rate']}%\n\n";

    echo "✓ Reports page should work correctly!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
