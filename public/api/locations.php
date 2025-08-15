<?php
/**
 * Locations API endpoint for public directory
 * This provides a direct path for the frontend to access location data
 */

// Start session for authentication
session_start();

// Include required files (go up one level from public/api to project root)
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/helpers/functions.php';
require_once __DIR__ . '/../../src/controllers/LocationController.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Create LocationController instance
    $controller = new LocationController();
    
    // Call the index method which handles the action parameter
    $controller->index();
    
} catch (Exception $e) {
    // Log error
    logActivity('Location API Error: ' . $e->getMessage(), 'error');
    
    // Return error response
    errorResponse('Internal server error: ' . $e->getMessage(), 500);
}
?>
