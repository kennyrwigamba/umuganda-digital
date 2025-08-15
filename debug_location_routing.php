<?php
// Debug the path parsing for the location API
$request_uri = '/umuganda-digital/api/locations?action=provinces';
$path        = parse_url($request_uri, PHP_URL_PATH);

echo "Original REQUEST_URI: $request_uri\n";
echo "Parsed path: $path\n";

// Remove base paths - handle both development and production scenarios
$api_bases = ['/umuganda-digital/api', '/api'];
foreach ($api_bases as $api_base) {
    echo "Testing api_base: $api_base\n";
    echo "strpos(\$path, \$api_base): " . strpos($path, $api_base) . "\n";
    if (strpos($path, $api_base) === 0) {
        $path = substr($path, strlen($api_base));
        echo "Match found! New path: '$path'\n";
        break;
    }
}

// Parse path segments
$segments = array_filter(explode('/', $path));
$segments = array_values($segments);

echo "Path segments: " . print_r($segments, true) . "\n";

if (count($segments) >= 1) {
    $controller_name = $segments[0];
    $action          = $segments[1] ?? 'index';
    $id              = $segments[2] ?? null;

    echo "Controller: '$controller_name'\n";
    echo "Action: '$action'\n";
    echo "ID: '$id'\n";

    $method      = 'GET';
    $method_name = strtolower($method) . ucfirst($action);
    echo "Method name: '$method_name'\n";

    // Check what the router would try to call
    echo "Router would try to call: \$controller->$method_name()\n";

} else {
    echo "No segments found!\n";
}
