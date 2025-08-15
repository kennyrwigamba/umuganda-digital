<?php
/**
 * Debug current session state
 */
session_start();

echo "<h2>Current Session Debug</h2>\n";
echo "Session ID: " . session_id() . "<br>\n";
echo "Session data:<br>\n";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['user_id'])) {
    require_once 'config/db.php';
    global $db;
    $connection = $db->getConnection();

    $adminId = $_SESSION['user_id'];

    // Check if this admin exists
    $adminQuery = "SELECT id, first_name, last_name, role, status FROM users WHERE id = ?";
    $adminStmt  = $connection->prepare($adminQuery);
    $adminStmt->bind_param('i', $adminId);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    $admin       = $adminResult->fetch_assoc();

    if ($admin) {
        echo "<h3>Current Session User:</h3>\n";
        echo "ID: {$admin['id']}<br>\n";
        echo "Name: {$admin['first_name']} {$admin['last_name']}<br>\n";
        echo "Role: {$admin['role']}<br>\n";
        echo "Status: {$admin['status']}<br>\n";

        // Check admin settings
        $settingsQuery = "SELECT * FROM admin_settings WHERE admin_id = ?";
        $settingsStmt  = $connection->prepare($settingsQuery);
        $settingsStmt->bind_param('i', $adminId);
        $settingsStmt->execute();
        $settingsResult = $settingsStmt->get_result();
        $settings       = $settingsResult->fetch_assoc();

        if ($settings) {
            echo "<h3>Admin Settings:</h3>\n";
            echo "Default fine amount: {$settings['default_fine_amount']}<br>\n";
        } else {
            echo "<h3>No admin settings found for this user</h3>\n";
        }
    } else {
        echo "<h3>Session user not found in database!</h3>\n";
    }
} else {
    echo "<h3>No user_id in session</h3>\n";
}
