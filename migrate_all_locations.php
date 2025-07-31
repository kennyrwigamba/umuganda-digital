<?php
/**
 * Complete Location Migration Script
 * Migrates users, notices, and events from string-based to ID-based location hierarchy
 */

require_once 'config/db.php';
require_once 'src/models/LocationManager.php';
require_once 'src/models/LocationMigrationHelper.php';

// Set up error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Complete Location Migration Script</h1>\n";
echo "<pre>\n";

try {
    $locationManager = new LocationManager($pdo);
    $migrationHelper = new LocationMigrationHelper($pdo, $locationManager);

    echo "=== Overall Migration Status ===\n";

    // Check user migration status
    $userReport = $migrationHelper->generateMigrationReport();
    echo "Users - Total: " . ($userReport['users_migrated'] + $userReport['users_to_migrate']) .
        " | Migrated: " . $userReport['users_migrated'] .
        " | Pending: " . $userReport['users_to_migrate'] . "\n";

    // Check notices status
    $stmt             = $pdo->query("SELECT COUNT(*) as total, COUNT(cell_id) as migrated FROM notices WHERE cell IS NOT NULL AND cell != ''");
    $noticeStats      = $stmt->fetch();
    $noticesToMigrate = $noticeStats['total'] - $noticeStats['migrated'];
    echo "Notices - Total: " . $noticeStats['total'] .
        " | Migrated: " . $noticeStats['migrated'] .
        " | Pending: " . $noticesToMigrate . "\n";

    // Check events status
    $stmt            = $pdo->query("SELECT COUNT(*) as total, COUNT(cell_id) as migrated FROM umuganda_events WHERE cell IS NOT NULL AND cell != ''");
    $eventStats      = $stmt->fetch();
    $eventsTOMigrate = $eventStats['total'] - $eventStats['migrated'];
    echo "Events - Total: " . $eventStats['total'] .
        " | Migrated: " . $eventStats['migrated'] .
        " | Pending: " . $eventsTOMigrate . "\n\n";

    $totalPending = $userReport['users_to_migrate'] + $noticesToMigrate + $eventsTOMigrate;

    if ($totalPending > 0) {
        echo "=== Items Needing Migration ===\n";

        if ($userReport['users_to_migrate'] > 0) {
            echo "👤 $userReport[users_to_migrate] users need migration\n";
        }

        if ($noticesToMigrate > 0) {
            echo "📢 $noticesToMigrate notices need migration\n";
        }

        if ($eventsTOMigrate > 0) {
            echo "📅 $eventsTOMigrate events need migration\n";
        }

        // Ask for confirmation to proceed with migration
        if (php_sapi_name() === 'cli') {
            echo "\nDo you want to proceed with complete migration? (y/N): ";
            $handle = fopen("php://stdin", "r");
            $line   = fgets($handle);
            fclose($handle);
            $proceed = trim(strtolower($line)) === 'y';
        } else {
            // For web interface
            $proceed = isset($_GET['migrate']) && $_GET['migrate'] === 'yes';
            if (! $proceed) {
                echo "\n<a href='?migrate=yes' style='background: #007cba; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>Click here to proceed with complete migration</a>\n";
            }
        }

        if ($proceed) {
            echo "\n=== Starting Complete Migration ===\n";

            // 1. Migrate Users (if needed)
            if ($userReport['users_to_migrate'] > 0) {
                echo "\n--- Migrating Users ---\n";
                $userResult = $migrationHelper->migrateAllUsers();
                echo "Users processed: {$userResult['total']} | Migrated: {$userResult['migrated']} | Errors: {$userResult['errors']}\n";
            }

            // 2. Migrate Notices
            if ($noticesToMigrate > 0) {
                echo "\n--- Migrating Notices ---\n";
                $noticeResult = $migrationHelper->migrateNotices();
                echo "Notices processed: {$noticeResult['total']} | Migrated: {$noticeResult['migrated']} | Errors: {$noticeResult['errors']}\n";
            }

            // 3. Migrate Events
            if ($eventsTOMigrate > 0) {
                echo "\n--- Migrating Events ---\n";
                $eventResult = $migrationHelper->migrateEvents();
                echo "Events processed: {$eventResult['total']} | Migrated: {$eventResult['migrated']} | Errors: {$eventResult['errors']}\n";
            }

            // 4. Fix inconsistent data
            echo "\n--- Fixing Inconsistent Data ---\n";
            $userFixed   = $migrationHelper->fixInconsistentData();
            $noticeFixed = $migrationHelper->fixNoticeInconsistentData();
            $eventFixed  = $migrationHelper->fixEventInconsistentData();
            echo "Fixed inconsistencies - Users: $userFixed | Notices: $noticeFixed | Events: $eventFixed\n";

            // 5. Final validation
            echo "\n--- Final Validation ---\n";
            $validation = $migrationHelper->validateMigration();

            if (empty($validation['inconsistent_data']) && $validation['missing_ids_count'] == 0) {
                echo "✅ All user location data is consistent and complete.\n";
            } else {
                echo "⚠️  Found {$validation['missing_ids_count']} users with missing IDs and " . count($validation['inconsistent_data']) . " inconsistent records.\n";
            }

            // Check notices and events
            $stmt               = $pdo->query("SELECT COUNT(*) FROM notices WHERE cell_id IS NULL AND cell IS NOT NULL AND cell != ''");
            $unmigrated_notices = $stmt->fetchColumn();

            $stmt              = $pdo->query("SELECT COUNT(*) FROM umuganda_events WHERE cell_id IS NULL AND cell IS NOT NULL AND cell != ''");
            $unmigrated_events = $stmt->fetchColumn();

            if ($unmigrated_notices == 0 && $unmigrated_events == 0) {
                echo "✅ All notices and events have been migrated successfully.\n";
            } else {
                echo "⚠️  $unmigrated_notices notices and $unmigrated_events events still need migration.\n";
            }

            echo "\n=== Migration Complete ===\n";
        }
    } else {
        echo "✅ All data has already been migrated to the new location hierarchy!\n";

        // Still run consistency check
        echo "\n=== Running Consistency Check ===\n";
        $userFixed   = $migrationHelper->fixInconsistentData();
        $noticeFixed = $migrationHelper->fixNoticeInconsistentData();
        $eventFixed  = $migrationHelper->fixEventInconsistentData();

        if ($userFixed + $noticeFixed + $eventFixed > 0) {
            echo "Fixed inconsistencies - Users: $userFixed | Notices: $noticeFixed | Events: $eventFixed\n";
        } else {
            echo "All location data is consistent.\n";
        }
    }

    // Show final statistics
    echo "\n=== Final Statistics ===\n";
    $finalUserReport = $migrationHelper->generateMigrationReport();

    $stmt             = $pdo->query("SELECT COUNT(*) as total, COUNT(cell_id) as migrated FROM notices WHERE cell IS NOT NULL AND cell != ''");
    $finalNoticeStats = $stmt->fetch();

    $stmt            = $pdo->query("SELECT COUNT(*) as total, COUNT(cell_id) as migrated FROM umuganda_events WHERE cell IS NOT NULL AND cell != ''");
    $finalEventStats = $stmt->fetch();

    echo "Users: {$finalUserReport['users_migrated']} migrated out of " . ($finalUserReport['users_migrated'] + $finalUserReport['users_to_migrate']) . " total\n";
    echo "Notices: {$finalNoticeStats['migrated']} migrated out of {$finalNoticeStats['total']} total\n";
    echo "Events: {$finalEventStats['migrated']} migrated out of {$finalEventStats['total']} total\n";

    $totalMigrated = $finalUserReport['users_migrated'] + $finalNoticeStats['migrated'] + $finalEventStats['migrated'];
    $totalItems    = ($finalUserReport['users_migrated'] + $finalUserReport['users_to_migrate']) + $finalNoticeStats['total'] + $finalEventStats['total'];

    if ($totalItems > 0) {
        $completionRate = round(($totalMigrated / $totalItems) * 100, 1);
        echo "\n🎯 Overall completion rate: $completionRate% ($totalMigrated/$totalItems items migrated)\n";
    }

} catch (Exception $e) {
    echo "❌ Error during migration: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>\n";

if (php_sapi_name() !== 'cli') {
    echo "<p><a href='?' style='background: #007cba; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>Refresh Report</a></p>\n";
}
