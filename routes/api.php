<?php
/**
 * API Router
 * Maps API endpoints to controller methods
 */

// Start session for authentication
session_start();

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
$request_uri = $_SERVER['REQUEST_URI'];
$path        = parse_url($request_uri, PHP_URL_PATH);

// Remove base API path
$api_base = '/api';
if (strpos($path, $api_base) === 0) {
    $path = substr($path, strlen($api_base));
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
            'auth'       => 'AuthController.php',
            'attendance' => 'AttendanceController.php',
            'fines'      => 'FineController.php',
            'events'     => 'EventController.php',
            'residents'  => 'ResidentController.php',
            'dashboard'  => 'DashboardController.php',
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
        if (! class_exists($controller_class)) {
            errorResponse('Controller class not found', 500);
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
