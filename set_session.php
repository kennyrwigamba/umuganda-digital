<?php
session_start();

// Manually set session for testing
$_SESSION['user_id']   = 2; // Admin user ID for admin@example.com
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Marie Uwamahoro';

echo "Session set for testing:<br>";
echo "User ID: " . $_SESSION['user_id'] . "<br>";
echo "User role: " . $_SESSION['user_role'] . "<br>";
echo "User name: " . $_SESSION['user_name'] . "<br>";

echo "<p><a href='public/dashboard/admin/attendance-marking.php'>Go to Attendance Marking Page</a></p>";
echo "<p><a href='debug_session.php'>Check Session Status</a></p>";
