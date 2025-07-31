<?php
session_start();

// Manually set session for testing
$_SESSION['user_id']   = 2; // Admin user ID for admin@example.com
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Marie Uwamahoro';

echo "Session set for testing:\n";
echo "User ID: " . $_SESSION['user_id'] . "\n";
echo "User role: " . $_SESSION['user_role'] . "\n";
echo "User name: " . $_SESSION['user_name'] . "\n";

echo "<p><a href='dashboard/admin/notices.php'>Go to Notices Page</a></p>";
