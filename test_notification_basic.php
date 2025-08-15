<?php
/**
 * Test basic notification system functionality
 */

require_once 'src/helpers/env.php';
require_once 'config/db.php';

// Test database connection
echo "Testing database connection...\n";
$connection = $db->getConnection();
if ($connection) {
    echo "✅ Database connected successfully\n";
} else {
    echo "❌ Database connection failed\n";
    exit(1);
}

// Test if notification tables exist and have correct structure
echo "\nTesting notification tables...\n";
$tables = [
    'notifications',
    'notification_channels', 
    'user_notification_preferences',
    'push_subscriptions',
    'notification_reads'
];

foreach ($tables as $table) {
    $result = $connection->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ Table '$table' exists\n";
    } else {
        echo "❌ Table '$table' missing\n";
    }
}

// Test a simple notification insert
echo "\nTesting direct notification creation...\n";
try {
    $stmt = $connection->prepare(
        "INSERT INTO notifications (user_id, title, body, type, category, priority, status, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
    );
    
    $userId = 1; // Assuming user 1 exists
    $title = "Test Notification";
    $body = "This is a test notification to verify the system works";
    $type = "system_test";
    $category = "system";
    $priority = "normal";
    $status = "pending";
    
    $stmt->bind_param('issssss', $userId, $title, $body, $type, $category, $priority, $status);
    
    if ($stmt->execute()) {
        $notificationId = $connection->insert_id;
        echo "✅ Test notification created successfully (ID: $notificationId)\n";
        
        // Test notification channel creation
        $stmt2 = $connection->prepare(
            "INSERT INTO notification_channels (notification_id, channel, status, created_at) 
             VALUES (?, ?, ?, NOW())"
        );
        
        $channel = "inapp";
        $channelStatus = "pending";
        $stmt2->bind_param('iss', $notificationId, $channel, $channelStatus);
        
        if ($stmt2->execute()) {
            echo "✅ Test notification channel created successfully\n";
        } else {
            echo "❌ Failed to create notification channel: " . $connection->error . "\n";
        }
        $stmt2->close();
        
    } else {
        echo "❌ Failed to create test notification: " . $connection->error . "\n";
    }
    $stmt->close();
    
} catch (Exception $e) {
    echo "❌ Exception during notification test: " . $e->getMessage() . "\n";
}

echo "\nBasic notification system test completed.\n";
?>
