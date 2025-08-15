<?php
// Test what $_GET contains when the API router runs
echo "=== Testing \$_GET parameters in API context ===\n";

// Set up the environment exactly like the API router would see it
$_SERVER['REQUEST_URI']    = '/umuganda-digital/api/locations?action=provinces';
$_SERVER['REQUEST_METHOD'] = 'GET';

// The API router parses the URL, but $_GET should contain the query parameters
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";

// Parse the query string manually to simulate what should be in $_GET
$query_string = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
echo "Query string: " . ($query_string ?? 'null') . "\n";

if ($query_string) {
    parse_str($query_string, $get_params);
    echo "\$_GET should contain: " . print_r($get_params, true) . "\n";

    // Simulate $_GET
    $_GET = $get_params;
}

echo "Current \$_GET: " . print_r($_GET, true) . "\n";

// Test the action parameter directly
$action = $_GET['action'] ?? 'not found';
echo "Action parameter: '$action'\n";

// Test if LocationController would work with this
echo "\n=== Testing LocationController logic ===\n";

switch ($action) {
    case 'provinces':
        echo "Would call getProvinces()\n";
        break;
    case 'districts':
        echo "Would call getDistricts()\n";
        break;
    case 'sectors':
        echo "Would call getSectors()\n";
        break;
    case 'cells':
        echo "Would call getCells()\n";
        break;
    default:
        echo "Invalid action: '$action'\n";
}
