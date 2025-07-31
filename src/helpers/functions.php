<?php
/**
 * Helper Functions
 * Utility functions used across the application
 */

/**
 * Sanitize input data
 */
function sanitize($data)
{
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }

    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken()
{
    if (! isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token']      = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token)
{
    if (! isset($_SESSION['csrf_token']) || ! isset($_SESSION['csrf_token_time'])) {
        return false;
    }

    // Check if token has expired
    $lifetime = $_ENV['CSRF_TOKEN_LIFETIME'] ?? 3600;
    if (time() - $_SESSION['csrf_token_time'] > $lifetime) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'Y-m-d H:i:s')
{
    if (empty($date)) {
        return '';
    }

    try {
        $dateTime = new DateTime($date);
        return $dateTime->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Format date for display (human readable)
 */
function formatDateHuman($date)
{
    if (empty($date)) {
        return '';
    }

    try {
        $dateTime = new DateTime($date);
        return $dateTime->format('F j, Y \a\t g:i A');
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Calculate age from birthdate
 */
function calculateAge($birthdate)
{
    if (empty($birthdate)) {
        return null;
    }

    try {
        $birth = new DateTime($birthdate);
        $today = new DateTime();
        return $birth->diff($today)->y;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Send JSON response
 */
function jsonResponse($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Send error response
 */
function errorResponse($message, $status = 400)
{
    jsonResponse([
        'success' => false,
        'error'   => $message,
    ], $status);
}

/**
 * Send success response
 */
function successResponse($data = [], $message = 'Success')
{
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data'    => $data,
    ]);
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && ! empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin()
{
    return isLoggedIn() && in_array($_SESSION['user_role'], ['admin', 'superadmin']);
}

/**
 * Check if user is superadmin
 */
function isSuperAdmin()
{
    return isLoggedIn() && $_SESSION['user_role'] === 'superadmin';
}

/**
 * Check if user is regular admin (not superadmin)
 */
function isRegularAdmin()
{
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

/**
 * Require login
 */
function requireLogin()
{
    if (! isLoggedIn()) {
        if (isAjaxRequest()) {
            errorResponse('Authentication required', 401);
        } else {
            header('Location: /umuganda-app/public/login.php');
            exit;
        }
    }
}

/**
 * Require admin privileges
 */
function requireAdmin()
{
    requireLogin();
    if (! isAdmin()) {
        if (isAjaxRequest()) {
            errorResponse('Admin privileges required', 403);
        } else {
            header('Location: /umuganda-app/public/dashboard/resident.php');
            exit;
        }
    }
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest()
{
    return ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Generate random string
 */
function generateRandomString($length = 10)
{
    return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
}

/**
 * Hash password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Log activity
 */
function logActivity($message, $type = 'info')
{
    $log_file = __DIR__ . '/../logs/app.log';

    // Create logs directory if it doesn't exist
    $logs_dir = dirname($log_file);
    if (! is_dir($logs_dir)) {
        mkdir($logs_dir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $user_id   = $_SESSION['user_id'] ?? 'guest';
    $ip        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $log_entry = "[{$timestamp}] [{$type}] [User: {$user_id}] [IP: {$ip}] {$message}" . PHP_EOL;

    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Get time ago from a datetime string
 */
function getTimeAgo($datetime)
{
    $time = time() - strtotime($datetime);

    if ($time < 1) {
        return 'just now';
    }

    $periods = [
        'year'   => 31556926,
        'month'  => 2629746,
        'week'   => 604800,
        'day'    => 86400,
        'hour'   => 3600,
        'minute' => 60,
        'second' => 1,
    ];

    foreach ($periods as $period => $seconds) {
        if ($time >= $seconds) {
            $count  = floor($time / $seconds);
            $suffix = $count > 1 ? 's' : '';
            return "{$count} {$period}{$suffix} ago";
        }
    }

    return 'just now';
}
