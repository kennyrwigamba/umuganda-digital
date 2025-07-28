<?php
/**
 * Login Debug Test
 * Quick test to debug login issues
 */

session_start();

// Include required files
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/src/helpers/functions.php';
require_once __DIR__ . '/src/models/User.php';
require_once __DIR__ . '/src/controllers/AuthController.php';

echo "<h1>Login Debug Test</h1>";

// Test database connection
try {
    global $db;
    $connection = $db->getConnection();
    echo "✅ Database connection: SUCCESS<br>";
} catch (Exception $e) {
    echo "❌ Database connection: FAILED - " . $e->getMessage() . "<br>";
}

// Test if users table exists and has data
try {
    $query  = "SELECT COUNT(*) as count FROM users";
    $result = $db->fetchOne($query);
    echo "✅ Users table accessible: " . $result['count'] . " users found<br>";
} catch (Exception $e) {
    echo "❌ Users table access: FAILED - " . $e->getMessage() . "<br>";
}

// Test User model
try {
    $userModel = new User();
    echo "✅ User model: SUCCESS<br>";
} catch (Exception $e) {
    echo "❌ User model: FAILED - " . $e->getMessage() . "<br>";
}

// Test if there's at least one user to test with
try {
    $query    = "SELECT email, role FROM users LIMIT 1";
    $testUser = $db->fetchOne($query);
    if ($testUser) {
        echo "✅ Test user available: " . $testUser['email'] . " (role: " . $testUser['role'] . ")<br>";
    } else {
        echo "❌ No test users found. Creating a test user...<br>";

        // Create a test user
        $testPassword = password_hash('password123', PASSWORD_BCRYPT);
        $query        = "INSERT INTO users (national_id, first_name, last_name, email, phone, password, cell, sector, district, province, date_of_birth, gender, role)
                  VALUES ('1234567890123456', 'Test', 'User', 'test@test.com', '0781234567', ?, 'Test Cell', 'Test Sector', 'Test District', 'Test Province', '1990-01-01', 'male', 'resident')";
        $db->execute($query, [$testPassword], 's');
        echo "✅ Test user created: test@test.com / password123<br>";
    }
} catch (Exception $e) {
    echo "❌ Test user check: FAILED - " . $e->getMessage() . "<br>";
}

// Test API routing simulation
echo "<h2>API Test</h2>";
echo "Testing direct AuthController login...<br>";

try {
    // Simulate API request
    $_SERVER['REQUEST_METHOD']        = 'POST';
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

    // Test data
    $testData = [
        'email'    => 'admin@umuganda-digital.rw',
        'password' => 'password',
    ];

    // Simulate JSON input
    $jsonInput = json_encode($testData);

    // Create a temporary file to simulate php://input
    $tempFile = tmpfile();
    fwrite($tempFile, $jsonInput);
    rewind($tempFile);

    // Test AuthController
    $controller = new AuthController();

    echo "Testing authentication logic...<br>";

    // Test user authentication directly
    $userModel = new User();
    $user      = $userModel->authenticate('admin@umuganda-digital.rw', 'password');

    if ($user) {
        echo "✅ Direct authentication: SUCCESS for user " . $user['email'] . "<br>";
    } else {
        echo "❌ Direct authentication: FAILED<br>";
    }

} catch (Exception $e) {
    echo "❌ API test: FAILED - " . $e->getMessage() . "<br>";
}

echo "<h2>Session Test</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session data: " . print_r($_SESSION, true) . "<br>";
