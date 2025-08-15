<?php
/**
 * Quick web test for registration with email
 */

// Start output buffering to prevent header issues
ob_start();

require_once 'src/controllers/AuthController.php';
require_once 'src/helpers/functions.php';

// Mock the POST data for web context
$_POST = [
    'first_name'       => 'Web',
    'last_name'        => 'Test',
    'email'            => 'webtest' . time() . '@example.com',
    'phone'            => '0781234567',
    'national_id'      => '1234' . str_pad(substr(time(), -12), 12, '0', STR_PAD_LEFT),
    'password'         => 'testpassword123',
    'confirm_password' => 'testpassword123',
    'province_id'      => '1',
    'district_id'      => '1',
    'sector_id'        => '1',
    'cell_id'          => '1',
];

// Initialize session (required for functions)
session_start();

echo "Testing web registration with email notification...\n";
echo "Email: " . $_POST['email'] . "\n";
echo "National ID: " . $_POST['national_id'] . "\n\n";

try {
    $controller = new AuthController();
    $controller->postRegister();

    $output = ob_get_clean();
    echo "Web registration successful!\n";
    echo "Response: " . $output . "\n";

} catch (Exception $e) {
    $output = ob_get_clean();
    echo "Error: " . $e->getMessage() . "\n";
    echo "Output: " . $output . "\n";
}
