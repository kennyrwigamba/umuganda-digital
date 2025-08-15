<?php
// Debug the API routing logic
$request_uri = '/umuganda-digital/api/auth/register';
$path        = parse_url($request_uri, PHP_URL_PATH);

echo "Original REQUEST_URI: $request_uri\n";
echo "Parsed path: $path\n";

// Test the path processing logic
$api_bases = ['/umuganda-digital/api', '/api'];
foreach ($api_bases as $api_base) {
    echo "Testing api_base: $api_base\n";
    echo "strpos(\$path, \$api_base): " . strpos($path, $api_base) . "\n";
    if (strpos($path, $api_base) === 0) {
        $path = substr($path, strlen($api_base));
        echo "Match found! New path: $path\n";
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
    echo "Controller: $controller_name\n";
    echo "Action: $action\n";
} else {
    echo "No segments found!\n";
}
