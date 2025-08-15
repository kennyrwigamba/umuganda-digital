<?php
/**
 * Test registration API response
 */

// Simulate a web request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST                     = [
    'first_name'       => 'API',
    'last_name'        => 'Test',
    'email'            => 'apitest' . time() . '@example.com',
    'phone'            => '0781234567',
    'national_id'      => '1234' . str_pad(substr(time(), -12), 12, '0', STR_PAD_LEFT),
    'password'         => 'testpassword123',
    'confirm_password' => 'testpassword123',
    'province_id'      => '1',
    'district_id'      => '1',
    'sector_id'        => '1',
    'cell_id'          => '1',
];

// Initialize session
session_start();

// Capture the output
ob_start();

try {
    require_once 'src/controllers/AuthController.php';
    require_once 'src/helpers/functions.php';

    $controller = new AuthController();
    $controller->postRegister();

} catch (Exception $e) {
    errorResponse($e->getMessage(), 500);
}

$output = ob_get_clean();

// Check if the output is valid JSON
echo "Raw output:\n";
echo "-----\n";
echo $output;
echo "\n-----\n\n";

$decoded = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ Valid JSON response!\n";
    echo "Success: " . ($decoded['success'] ? 'true' : 'false') . "\n";
    echo "Message: " . ($decoded['message'] ?? 'none') . "\n";
    if (isset($decoded['data']['user_id'])) {
        echo "User ID: " . $decoded['data']['user_id'] . "\n";
    }
} else {
    echo "❌ Invalid JSON response\n";
    echo "JSON Error: " . json_last_error_msg() . "\n";
}
