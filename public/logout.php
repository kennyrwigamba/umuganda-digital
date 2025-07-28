<?php
/**
 * Logout Page
 * Handles user logout and redirects to login page
 */

session_start();

// Check if user is logged in
if (! isset($_SESSION['user_id'])) {
    // Already logged out, redirect to login
    header('Location: login.php?message=already_logged_out');
    exit;
}

// Include helper functions
require_once __DIR__ . '/../src/helpers/functions.php';

// Get user info before destroying session
$user_email = $_SESSION['user_email'] ?? 'unknown';
$user_id    = $_SESSION['user_id'] ?? null;

// Clear session
session_unset();
session_destroy();

// Log the logout activity
if ($user_id) {
    logActivity("User logged out: $user_email (ID: $user_id)", 'info');
}

// Redirect to login page with success message
header('Location: login.php?message=logged_out');
exit;
