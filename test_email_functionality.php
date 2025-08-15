<?php
/**
 * Test Email Functionality
 */

require_once 'src/helpers/env.php';
require_once 'src/channels/EmailChannel.php';

use UmugandaDigital\Channels\EmailChannel;

echo "Testing email functionality...\n";
echo "SMTP Configuration:\n";
echo "- Host: " . ($_ENV['SMTP_HOST'] ?? 'not set') . "\n";
echo "- Port: " . ($_ENV['SMTP_PORT'] ?? 'not set') . "\n";
echo "- Username: " . ($_ENV['SMTP_USERNAME'] ?? 'not set') . "\n";
echo "- From Email: " . ($_ENV['SMTP_FROM_EMAIL'] ?? 'ludiflextutorials@gmail.com') . "\n";
echo "- From Name: " . ($_ENV['SMTP_FROM_NAME'] ?? 'Umuganda Digital') . "\n\n";

try {
    $emailChannel = new EmailChannel();

    // Create test notification
    $notification = [
        'user_id'    => 1, // Assuming user ID 1 exists
        'type'       => 'user_registered',
        'title'      => 'Welcome to Umuganda Digital Platform - Test',
        'body'       => 'This is a test welcome email to verify the email functionality is working correctly.',
        'category'   => 'registration',
        'created_at' => date('Y-m-d H:i:s'),
        'data'       => json_encode([
            'user_name'     => 'Test User',
            'user_email'    => 'test@example.com',
            'user_id'       => 1,
            'platform_name' => 'Umuganda Digital Platform',
            'platform_url'  => 'http://umuganda.local',
        ]),
    ];

    // Test channel row (empty)
    $channelRow = [];

    echo "Sending test email...\n";
    $result = $emailChannel->send($notification, $channelRow);

    if ($result['success']) {
        echo "✅ SUCCESS: Email sent successfully!\n";
        echo "The registration email functionality is working correctly.\n";
    } else {
        echo "❌ FAILED: " . $result['error'] . "\n";
        echo "Email sending failed. Check SMTP configuration.\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Exception occurred during email testing.\n";
}

echo "\nTest completed.\n";
