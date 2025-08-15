<?php
// Check if mod_rewrite is enabled
echo "=== Apache Module Check ===\n";

if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "✅ mod_rewrite is enabled\n";
    } else {
        echo "❌ mod_rewrite is NOT enabled\n";
    }

    echo "\nAll Apache modules:\n";
    foreach ($modules as $module) {
        echo "  - $module\n";
    }
} else {
    echo "❌ apache_get_modules() function not available\n";
    echo "This might mean PHP is not running under Apache or info is restricted\n";
}

echo "\n=== .htaccess Test ===\n";
echo "Current directory: " . __DIR__ . "\n";
echo ".htaccess file exists: " . (file_exists(__DIR__ . '/.htaccess') ? '✅ Yes' : '❌ No') . "\n";

if (file_exists(__DIR__ . '/.htaccess')) {
    echo "\n.htaccess content:\n";
    echo file_get_contents(__DIR__ . '/.htaccess');
}
