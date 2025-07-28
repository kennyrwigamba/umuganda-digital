<?php
/**
 * Main Application Router
 * Handles routing and bootstrapping
 */

session_start();

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Set error reporting based on environment
if ($_ENV['APP_DEBUG'] === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Include database configuration
require_once __DIR__ . '/config/db.php';

// Include helper functions
require_once __DIR__ . '/src/helpers/functions.php';

// Basic routing
$request_uri = $_SERVER['REQUEST_URI'];
$path        = parse_url($request_uri, PHP_URL_PATH);

// Normalize path - ensure it starts with /
if (empty($path) || $path === '/') {
    $path = '/';
}

// Route handling
switch ($path) {
    case '/':
    case '/index.php':
        header('Location: /public/index.php');
        exit;

    case '/login':
        header('Location: /public/login.php');
        exit;

    case '/dashboard':
        // Check if user is logged in
        if (! isset($_SESSION['user_id'])) {
            header('Location: /public/login.php');
            exit;
        }

        // Redirect based on user role
        if ($_SESSION['user_role'] === 'admin') {
            header('Location: /public/dashboard/admin.php');
        } else {
            header('Location: /public/dashboard/resident.php');
        }
        exit;

    default:
        // Check if it's an API route
        if (strpos($path, '/api/') === 0) {
            require_once __DIR__ . '/routes/api.php';
        } else {
            // 404 Not Found
            http_response_code(404);
            echo "Page not found";
        }
        break;
}
