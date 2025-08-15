<?php
/**
 * Router for PHP Development Server
 * Handles routing when using `php -S` command
 */

// Load environment variables
require_once __DIR__ . '/src/helpers/env.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Handle API routes
if (strpos($uri, '/api/') === 0) {
    // Set the path for the API router to process
    $_SERVER['REQUEST_URI'] = $uri;
    require_once __DIR__ . '/routes/api.php';
    return true;
}

// Handle public directory files
if (strpos($uri, '/public/') === 0) {
    $public_file = __DIR__ . $uri;
    if (file_exists($public_file)) {
        return false; // Let the server handle the file
    }
}

// Handle static files in the root that should be in public
$static_extensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico'];
$extension         = pathinfo($uri, PATHINFO_EXTENSION);
if (in_array('.' . $extension, $static_extensions)) {
    $public_file = __DIR__ . '/public' . $uri;
    if (file_exists($public_file)) {
        return false;
    }
}

// Handle main routes
switch ($uri) {
    case '/':
        require_once __DIR__ . '/public/index.php';
        break;

    case '/login':
        require_once __DIR__ . '/public/login.php';
        break;

    case '/register':
        require_once __DIR__ . '/public/register.php';
        break;

    case '/logout':
        require_once __DIR__ . '/public/logout.php';
        break;

    default:
        // Check if file exists in public directory
        $public_file = __DIR__ . '/public' . $uri;
        if (file_exists($public_file)) {
            require_once $public_file;
        } else {
            // Include main router
            require_once __DIR__ . '/index.php';
        }
        break;
}

return true;
