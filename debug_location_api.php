<?php
/**
 * Debug Location API
 * Test the locations API endpoint directly
 */

echo "<h1>Debug Location API</h1>\n";

// Test if we can include the required files
try {
    require_once 'config/db.php';
    echo "✅ Database config loaded successfully<br>\n";
} catch (Exception $e) {
    echo "❌ Error loading database config: " . $e->getMessage() . "<br>\n";
    exit;
}

try {
    require_once 'src/models/LocationManager.php';
    echo "✅ LocationManager class loaded successfully<br>\n";
} catch (Exception $e) {
    echo "❌ Error loading LocationManager: " . $e->getMessage() . "<br>\n";
    exit;
}

// Test database connection
try {
    if (isset($pdo)) {
        echo "✅ PDO database connection available<br>\n";

        // Test database query
        $stmt   = $pdo->query("SELECT COUNT(*) as count FROM provinces");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Provinces table contains " . $result['count'] . " records<br>\n";
    } else {
        echo "❌ PDO variable not available<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>\n";
}

// Test LocationManager
try {
    $locationManager = new LocationManager($pdo);
    echo "✅ LocationManager instantiated successfully<br>\n";

    $provinces = $locationManager->getProvinces();
    echo "✅ getProvinces() returned " . count($provinces) . " provinces<br>\n";

    if (! empty($provinces)) {
        echo "<h2>First Province:</h2>\n";
        echo "<pre>" . print_r($provinces[0], true) . "</pre>\n";
    }

} catch (Exception $e) {
    echo "❌ LocationManager error: " . $e->getMessage() . "<br>\n";
}

// Test the full API endpoint simulation
echo "<h2>Simulating API Call</h2>\n";
try {
    $_GET['action'] = 'provinces';

    $locationManager = new LocationManager($pdo);
    $provinces       = $locationManager->getProvinces();
    $response        = [
        'success' => true,
        'data'    => $provinces,
    ];

    echo "✅ API Response:<br>\n";
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>\n";

} catch (Exception $e) {
    echo "❌ API Simulation Error: " . $e->getMessage() . "<br>\n";
}
