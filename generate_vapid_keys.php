<?php
/**
 * VAPID Key Generator for Web Push Notifications
 *
 * VAPID (Voluntary Application server Identification) keys are required
 * for sending web push notifications. This script generates a key pair.
 *
 * Run: php generate_vapid_keys.php
 */

require_once 'vendor/autoload.php';

use Minishlink\WebPush\VAPID;

echo "=== Generating VAPID Keys for Web Push ===\n\n";

try {
    // Generate VAPID key pair
    $vapidKeys = VAPID::createVapidKeys();

    echo "✅ VAPID keys generated successfully!\n\n";

    echo "📋 Copy these values to your config/vapid.php file:\n\n";
    echo "<?php\n";
    echo "// VAPID Configuration for Web Push Notifications\n";
    echo "// Generated on: " . date('Y-m-d H:i:s') . "\n\n";
    echo "\$_ENV['VAPID_PUBLIC_KEY'] = '{$vapidKeys['publicKey']}';\n";
    echo "\$_ENV['VAPID_PRIVATE_KEY'] = '{$vapidKeys['privateKey']}';\n";
    echo "\$_ENV['VAPID_SUBJECT'] = 'mailto:admin@umuganda.rw'; // Change to your admin email\n";
    echo "?>\n\n";

    echo "🔑 Public Key (for frontend):\n";
    echo $vapidKeys['publicKey'] . "\n\n";

    echo "🔒 Private Key (for backend only - keep secure!):\n";
    echo $vapidKeys['privateKey'] . "\n\n";

    echo "📝 Next Steps:\n";
    echo "1. Create config/vapid.php with the above configuration\n";
    echo "2. Add the public key to your frontend JavaScript\n";
    echo "3. Implement push subscription in your web app\n";
    echo "4. Test push notifications\n\n";

    // Auto-create the config file
    $configContent = "<?php\n";
    $configContent .= "/**\n";
    $configContent .= " * VAPID Configuration for Web Push Notifications\n";
    $configContent .= " * Generated on: " . date('Y-m-d H:i:s') . "\n";
    $configContent .= " * \n";
    $configContent .= " * VAPID (Voluntary Application server Identification) keys are used\n";
    $configContent .= " * to identify your application server to push services.\n";
    $configContent .= " */\n\n";
    $configContent .= "\$_ENV['VAPID_PUBLIC_KEY'] = '{$vapidKeys['publicKey']}';\n";
    $configContent .= "\$_ENV['VAPID_PRIVATE_KEY'] = '{$vapidKeys['privateKey']}';\n";
    $configContent .= "\$_ENV['VAPID_SUBJECT'] = 'mailto:admin@umuganda.rw'; // Change to your admin email\n";
    $configContent .= "\n";
    $configContent .= "// Application URL for notifications\n";
    $configContent .= "\$_ENV['APP_URL'] = \$_ENV['APP_URL'] ?? 'http://localhost';\n";
    $configContent .= "?>\n";

    file_put_contents('config/vapid.php', $configContent);
    echo "✅ Config file created at config/vapid.php\n\n";

} catch (Exception $e) {
    echo "❌ Error generating VAPID keys: " . $e->getMessage() . "\n";
    exit(1);
}
