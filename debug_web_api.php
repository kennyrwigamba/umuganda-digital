<?php
/**
 * Test web registration API to debug output issues
 */

// Start clean output buffering
ob_start();

// Simulate web environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI']    = '/api/auth/register';
$_SERVER['CONTENT_TYPE']   = 'application/x-www-form-urlencoded';
$_POST                     = [
    'first_name'       => 'Web',
    'last_name'        => 'User',
    'email'            => 'webuser' . time() . '@example.com',
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

// Include the API file directly like a web request would
try {
    include 'routes/api.php';
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$output = ob_get_clean();

echo "=== OUTPUT ANALYSIS ===\n";
echo "Output length: " . strlen($output) . " bytes\n";
echo "Raw output:\n";
echo "'" . $output . "'\n\n";

// Try to decode as JSON
$json = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ VALID JSON\n";
    print_r($json);
} else {
    echo "❌ INVALID JSON\n";
    echo "JSON Error: " . json_last_error_msg() . "\n";

    // Show character codes for debugging
    echo "\nFirst 200 characters (with codes):\n";
    for ($i = 0; $i < min(200, strlen($output)); $i++) {
        $char = $output[$i];
        $code = ord($char);
        if ($code < 32 || $code > 126) {
            echo "[{$code}]";
        } else {
            echo $char;
        }
    }
    echo "\n";
}
