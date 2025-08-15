<?php
/**
 * Test .env loading
 */

echo "Testing .env loading...\n";

// Try to load .env directly
$envPath = '.env';
if (file_exists($envPath)) {
    echo "✅ .env file exists at: " . realpath($envPath) . "\n";

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "✅ .env contains " . count($lines) . " lines\n";

    foreach ($lines as $line) {
        if (strpos(trim($line), 'SMTP_') === 0) {
            echo "   📧 " . trim($line) . "\n";
        }
    }
} else {
    echo "❌ .env file not found\n";
}

echo "\nLoading environment helper...\n";
require_once 'src/helpers/env.php';

echo "\nChecking \$_ENV variables:\n";
echo "- SMTP_HOST: " . (isset($_ENV['SMTP_HOST']) ? $_ENV['SMTP_HOST'] : 'not set') . "\n";
echo "- SMTP_PORT: " . (isset($_ENV['SMTP_PORT']) ? $_ENV['SMTP_PORT'] : 'not set') . "\n";
echo "- SMTP_USERNAME: " . (isset($_ENV['SMTP_USERNAME']) ? $_ENV['SMTP_USERNAME'] : 'not set') . "\n";
echo "- SMTP_FROM_EMAIL: " . (isset($_ENV['SMTP_FROM_EMAIL']) ? $_ENV['SMTP_FROM_EMAIL'] : 'not set') . "\n";

echo "\nTesting EmailChannel with .env...\n";
require_once 'src/channels/EmailChannel.php';

use UmugandaDigital\Channels\EmailChannel;

try {
    $channel = new EmailChannel();
    echo "✅ EmailChannel created successfully\n";
} catch (Exception $e) {
    echo "❌ EmailChannel creation failed: " . $e->getMessage() . "\n";
}
