<?php
session_start();
$_SESSION['user_id']   = 2; // Using admin ID 2 who has assignments
$_SESSION['user_role'] = 'admin';

require_once __DIR__ . '/../../../config/db.php';

try {
    $database = new Database();
    $db       = $database->getConnection();

    $admin_id = $_SESSION['user_id'];
    echo "Testing with admin ID: $admin_id\n\n";

    // Test 1: Check admin assignment
    echo "=== Admin Assignment Test ===\n";
    $stmt = $db->prepare("SELECT aa.*, s.name as sector_name, d.name as district_name
                         FROM admin_assignments aa
                         JOIN sectors s ON aa.sector_id = s.id
                         JOIN districts d ON s.district_id = d.id
                         WHERE aa.admin_id = ? AND aa.is_active = 1");
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($assignment = $result->fetch_assoc()) {
        echo "✓ Admin assigned to: {$assignment['sector_name']}, {$assignment['district_name']}\n";
        $sector_id = $assignment['sector_id'];
    } else {
        echo "✗ No admin assignment found\n";
        exit;
    }
    $stmt->close();

    // Test 2: Residents count in assigned sector
    echo "\n=== Residents Count Test ===\n";
    $stmt = $db->prepare("SELECT COUNT(*) as total_residents,
                          COUNT(CASE WHEN status = 'active' THEN 1 END) as active_residents,
                          COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_residents,
                          COUNT(CASE WHEN status = 'suspended' THEN 1 END) as suspended_residents
                          FROM users WHERE sector_id = ? AND role = 'resident'");
    $stmt->bind_param('i', $sector_id);
    $stmt->execute();
    $result    = $stmt->get_result();
    $residents = $result->fetch_assoc();
    $stmt->close();

    echo "Total residents: {$residents['total_residents']}\n";
    echo "Active: {$residents['active_residents']}\n";
    echo "Inactive: {$residents['inactive_residents']}\n";
    echo "Suspended: {$residents['suspended_residents']}\n";

    // Test 3: Events count
    echo "\n=== Events Test ===\n";
    $stmt = $db->prepare("SELECT COUNT(*) as total_events,
                          COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_events,
                          COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_events
                          FROM umuganda_events WHERE sector_id = ?");
    $stmt->bind_param('i', $sector_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $events = $result->fetch_assoc();
    $stmt->close();

    echo "Total events: {$events['total_events']}\n";
    echo "Completed: {$events['completed_events']}\n";
    echo "Scheduled: {$events['scheduled_events']}\n";

    // Test 4: Fines data
    echo "\n=== Fines Test ===\n";
    $stmt = $db->prepare("SELECT
                          COUNT(*) as total_fines,
                          COUNT(CASE WHEN f.status = 'pending' THEN 1 END) as pending_fines,
                          COUNT(CASE WHEN f.status = 'paid' THEN 1 END) as paid_fines,
                          COALESCE(SUM(CASE WHEN f.status = 'pending' THEN f.amount END), 0) as pending_amount,
                          COALESCE(SUM(CASE WHEN f.status = 'paid' THEN f.amount END), 0) as collected_amount
                          FROM fines f
                          JOIN users u ON f.user_id = u.id
                          WHERE u.sector_id = ?");
    $stmt->bind_param('i', $sector_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fines  = $result->fetch_assoc();
    $stmt->close();

    echo "Total fines: {$fines['total_fines']}\n";
    echo "Pending: {$fines['pending_fines']} (Amount: {$fines['pending_amount']} RWF)\n";
    echo "Paid: {$fines['paid_fines']} (Amount: {$fines['collected_amount']} RWF)\n";

    echo "\n=== Test completed successfully! ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
