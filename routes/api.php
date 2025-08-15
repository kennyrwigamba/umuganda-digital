<?php
/**
 * API Router
 * Maps API endpoints to controller methods
 */

// Load environment variables first
require_once __DIR__ . '/../src/helpers/env.php';

// Suppress notices and warnings in production API responses
if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
}

// Start session for authentication (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/helpers/functions.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get the API path
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path        = parse_url($request_uri, PHP_URL_PATH);

// Remove base paths - handle custom domain setup
$api_bases = ['/api'];
foreach ($api_bases as $api_base) {
    if (strpos($path, $api_base) === 0) {
        $path = substr($path, strlen($api_base));
        break;
    }
}

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Parse path segments
$segments = array_filter(explode('/', $path));
$segments = array_values($segments);

try {
    // Route to appropriate controller
    if (count($segments) >= 1) {
        $controller_name = $segments[0];
        $action          = $segments[1] ?? 'index';
        $id              = $segments[2] ?? null;

        // Map controller names to files
        $controllers = [
            'auth'          => 'AuthController.php',
            'attendance'    => 'AttendanceController.php',
            'qr-attendance' => 'qr-attendance-handler.php',
            'fines'         => 'FineController.php',
            'events'        => 'EventController.php',
            'residents'     => 'ResidentController.php',
            'dashboard'     => 'DashboardController.php',
            'locations'     => 'LocationController.php',
        ];

        if (! isset($controllers[$controller_name])) {
            errorResponse('Invalid API endpoint', 404);
        }

        $controller_file = __DIR__ . '/../src/controllers/' . $controllers[$controller_name];

        if (! file_exists($controller_file)) {
            errorResponse('Controller not found', 404);
        }

        require_once $controller_file;

        // Instantiate controller
        $controller_class = ucfirst($controller_name) . 'Controller';

        // Special case for locations -> LocationController
        if ($controller_name === 'locations') {
            $controller_class = 'LocationController';
        }

        if (! class_exists($controller_class)) {
            errorResponse('Controller class not found: ' . $controller_class, 500);
        }

        $controller = new $controller_class();

        // Check if method exists
        $method_name = strtolower($method) . ucfirst($action);
        if (! method_exists($controller, $method_name)) {
            // Try generic action method
            if (method_exists($controller, $action)) {
                $method_name = $action;
            } else {
                errorResponse('Method not found', 404);
            }
        }

        // Call the controller method
        if ($id !== null) {
            $controller->$method_name($id);
        } else {
            $controller->$method_name();
        }

    } else {
        errorResponse('No API endpoint specified', 400);
    }

} catch (Exception $e) {
    // Log error
    logActivity('API Error: ' . $e->getMessage(), 'error');

    // Return error response
    if ($_ENV['APP_DEBUG'] === 'true') {
        errorResponse('Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 500);
    } else {
        errorResponse('Internal server error', 500);
    }
}
