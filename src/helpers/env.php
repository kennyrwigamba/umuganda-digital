<?php
/**
 * Environment configuration loader
 * Loads environment variables from .env file
 */

/**
 * Load environment variables from .env file
 */
function loadEnv($path = null)
{
    if ($path === null) {
        $path = __DIR__ . '/../../.env';
    }

    if (! file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse key=value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key               = trim($key);
            $value             = trim($value);

            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }

            // Set in $_ENV if not already set
            if (! isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Auto-load .env file when this file is included
loadEnv();
