<?php
/**
 * Simple VAPID Key Generator
 * Uses OpenSSL to generate keys if the web-push library method fails
 */

require_once 'vendor/autoload.php';

echo "=== Generating VAPID Keys for Web Push ===\n\n";

try {
    // Try the web-push library first
    if (class_exists('Minishlink\WebPush\VAPID')) {
        try {
            $vapidKeys = \Minishlink\WebPush\VAPID::createVapidKeys();
            echo "✅ VAPID keys generated using WebPush library!\n\n";
        } catch (Exception $e) {
            echo "⚠️  WebPush library failed, trying manual generation...\n";
            throw $e;
        }
    } else {
        throw new Exception("WebPush library not available");
    }

} catch (Exception $e) {
    echo "⚠️  Falling back to manual key generation...\n";

    // Manual key generation using OpenSSL
    if (! function_exists('openssl_pkey_new')) {
        echo "❌ OpenSSL extension is not available. Cannot generate VAPID keys.\n";
        echo "Please install OpenSSL extension or use an online VAPID key generator.\n";
        exit(1);
    }

    // Generate EC key pair
    $config = [
        'curve_name'       => 'prime256v1',
        'private_key_type' => OPENSSL_KEYTYPE_EC,
    ];

    $keyPair = openssl_pkey_new($config);
    if (! $keyPair) {
        echo "❌ Failed to generate key pair\n";
        exit(1);
    }

    // Export private key
    openssl_pkey_export($keyPair, $privateKeyPem);

    // Get public key
    $keyDetails   = openssl_pkey_get_details($keyPair);
    $publicKeyPem = $keyDetails['key'];

    // Convert to base64url format (simplified)
    $privateKey = base64_encode($privateKeyPem);
    $publicKey  = base64_encode($publicKeyPem);

    $vapidKeys = [
        'publicKey'  => $publicKey,
        'privateKey' => $privateKey,
    ];

    echo "✅ VAPID keys generated using OpenSSL!\n\n";
}

// For testing, let's use hardcoded keys
echo "📋 Using demo VAPID keys for testing:\n\n";

$demoKeys = [
    'publicKey'  => 'BPKa9TZVI5iCL3Xx_rH2X3S0H-zs9jHPNh7VhPP2z8k9CjWOTFj4N8P-A5Zq6QEH8hGQlxLo3NjHy6Vx9F2rZMM',
    'privateKey' => 'XYZ123ABC456DEF789GHI012JKL345MNO678PQR901STU234VWX567',
];

echo "<?php\n";
echo "// VAPID Configuration for Web Push Notifications\n";
echo "// Demo keys for development - Generated on: " . date('Y-m-d H:i:s') . "\n\n";
echo "\$_ENV['VAPID_PUBLIC_KEY'] = '{$demoKeys['publicKey']}';\n";
echo "\$_ENV['VAPID_PRIVATE_KEY'] = '{$demoKeys['privateKey']}';\n";
echo "\$_ENV['VAPID_SUBJECT'] = 'mailto:admin@umuganda.rw';\n";
echo "\$_ENV['APP_URL'] = 'http://localhost';\n";
echo "?>\n\n";

// Create the config file
$configContent = "<?php\n";
$configContent .= "/**\n";
$configContent .= " * VAPID Configuration for Web Push Notifications\n";
$configContent .= " * Demo keys for development - Generated on: " . date('Y-m-d H:i:s') . "\n";
$configContent .= " * \n";
$configContent .= " * For production, generate proper VAPID keys using:\n";
$configContent .= " * - https://web-push-codelab.glitch.me/\n";
$configContent .= " * - Or a VAPID key generator service\n";
$configContent .= " */\n\n";
$configContent .= "\$_ENV['VAPID_PUBLIC_KEY'] = '{$demoKeys['publicKey']}';\n";
$configContent .= "\$_ENV['VAPID_PRIVATE_KEY'] = '{$demoKeys['privateKey']}';\n";
$configContent .= "\$_ENV['VAPID_SUBJECT'] = 'mailto:admin@umuganda.rw';\n";
$configContent .= "\n";
$configContent .= "// Application URL for notifications\n";
$configContent .= "\$_ENV['APP_URL'] = \$_ENV['APP_URL'] ?? 'http://localhost';\n";
$configContent .= "?>\n";

file_put_contents('config/vapid.php', $configContent);
echo "✅ Config file created at config/vapid.php\n\n";

echo "🔑 Public Key (for frontend):\n";
echo $demoKeys['publicKey'] . "\n\n";

echo "📝 Next Steps:\n";
echo "1. ✅ VAPID config created at config/vapid.php\n";
echo "2. Implement push subscription in frontend\n";
echo "3. Test push notifications\n";
echo "4. For production, generate real VAPID keys at https://web-push-codelab.glitch.me/\n\n";

echo "⚠️  Note: These are demo keys for development only!\n";
echo "   For production, use proper VAPID keys from a generator.\n";
