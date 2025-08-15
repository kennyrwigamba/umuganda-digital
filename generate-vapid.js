const webpush = require("web-push");
const fs = require("fs");

console.log("Generating VAPID keys...");

const vapidKeys = webpush.generateVAPIDKeys();

console.log("✓ VAPID keys generated successfully!");
console.log("\nPublic Key:", vapidKeys.publicKey);
console.log("Private Key:", vapidKeys.privateKey);

// Create PHP config file
const phpConfig = `<?php
// VAPID keys for Web Push notifications
// Generated: ${new Date().toISOString()}

// Load these keys into environment variables
$_ENV['VAPID_PUBLIC_KEY'] = '${vapidKeys.publicKey}';
$_ENV['VAPID_PRIVATE_KEY'] = '${vapidKeys.privateKey}';
$_ENV['VAPID_SUBJECT'] = 'mailto:admin@umuganda.rw';

// Alternative: Use putenv() for older PHP versions
putenv('VAPID_PUBLIC_KEY=${vapidKeys.publicKey}');
putenv('VAPID_PRIVATE_KEY=${vapidKeys.privateKey}');
putenv('VAPID_SUBJECT=mailto:admin@umuganda.rw');
?>`;

fs.writeFileSync("config/vapid.php", phpConfig);

console.log("\n✓ VAPID configuration saved to config/vapid.php");
console.log("\nNext steps:");
console.log("1. Update your frontend with the public key");
console.log("2. Test push notifications");
console.log("3. Keep the private key secure!");
