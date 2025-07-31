<?php
/**
 * Location Migration Script
 * Run this script to migrate from string-based locations to ID-based location hierarchy
 */

require_once 'config/db.php';
require_once 'src/models/LocationManager.php';
require_once 'src/models/LocationMigrationHelper.php';

// Set up error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Location Migration Script</h1>\n";
echo "<pre>\n";

try {
    $locationManager = new LocationManager($pdo);
    $migrationHelper = new LocationMigrationHelper($pdo, $locationManager);

    echo "=== Location Migration Status ===\n";

    // Generate migration report
    $report = $migrationHelper->generateMigrationReport();

    echo "Users to migrate: " . $report['users_to_migrate'] . "\n";
    echo "Users already migrated: " . $report['users_migrated'] . "\n";
    echo "Unique string-based locations: " . count($report['unique_locations']) . "\n\n";

    if ($report['users_to_migrate'] > 0) {
        echo "=== Unique Locations to Map ===\n";
        foreach ($report['unique_locations'] as $location) {
            echo "- {$location['province']} > {$location['district']} > {$location['sector']} > {$location['cell']} ({$location['user_count']} users)\n";
        }
        echo "\n";

        // Show mapping suggestions
        echo "=== Location Mapping Suggestions ===\n";
        $suggestions = $migrationHelper->createLocationMappingSuggestions();

        foreach ($suggestions as $suggestion) {
            $orig = $suggestion['original'];
            echo "Original: {$orig['province']} > {$orig['district']} > {$orig['sector']} > {$orig['cell']} ({$orig['user_count']} users)\n";

            if (! empty($suggestion['suggestions'])) {
                echo "Suggestions:\n";
                foreach ($suggestion['suggestions'] as $match) {
                    if (is_array($match)) {
                        echo "  - {$match['province_name']} > {$match['district_name']} > {$match['sector_name']} > {$match['cell_name']} ({$match['match_type']})\n";
                    }
                }
            } else {
                echo "  No matches found - manual mapping required\n";
            }
            echo "\n";
        }

        // Ask for confirmation to proceed with migration
        if (php_sapi_name() === 'cli') {
            echo "Do you want to proceed with automatic migration? (y/N): ";
            $handle = fopen("php://stdin", "r");
            $line   = fgets($handle);
            fclose($handle);
            $proceed = trim(strtolower($line)) === 'y';
        } else {
            // For web interface, you might want to add a form here
            $proceed = isset($_GET['migrate']) && $_GET['migrate'] === 'yes';
            if (! $proceed) {
                echo "<a href='?migrate=yes' style='background: #007cba; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>Click here to proceed with migration</a>\n";
            }
        }

        if ($proceed) {
            echo "=== Starting Migration ===\n";
            $result = $migrationHelper->migrateAllUsers();

            echo "Total users processed: " . $result['total'] . "\n";
            echo "Successfully migrated: " . $result['migrated'] . "\n";
            echo "Errors: " . $result['errors'] . "\n\n";

            if ($result['errors'] > 0) {
                echo "Some users could not be migrated automatically.\n";
                echo "Check the error log for details.\n\n";
            }

            // Fix any inconsistent data
            echo "=== Fixing Inconsistent Data ===\n";
            $fixed = $migrationHelper->fixInconsistentData();
            echo "Fixed $fixed records with inconsistent location data.\n\n";

            // Validate migration
            echo "=== Validation ===\n";
            $validation = $migrationHelper->validateMigration();

            if (! empty($validation['inconsistent_data'])) {
                echo "Found " . count($validation['inconsistent_data']) . " records with inconsistent data:\n";
                foreach ($validation['inconsistent_data'] as $record) {
                    echo "  User ID {$record['id']}: String='{$record['cell']}' vs ID='{$record['cell_name']}'\n";
                }
            } else {
                echo "No inconsistent data found.\n";
            }

            if ($validation['missing_ids_count'] > 0) {
                echo "Found {$validation['missing_ids_count']} users still missing location IDs.\n";
            } else {
                echo "All users have location IDs assigned.\n";
            }

            echo "\n=== Migration Complete ===\n";
        }
    } else {
        echo "No users need migration. All users already have location IDs assigned.\n";

        // Still run validation
        echo "\n=== Validation ===\n";
        $validation = $migrationHelper->validateMigration();

        if (! empty($validation['inconsistent_data'])) {
            echo "Found " . count($validation['inconsistent_data']) . " records with inconsistent data.\n";
            echo "Running fix...\n";
            $fixed = $migrationHelper->fixInconsistentData();
            echo "Fixed $fixed records.\n";
        } else {
            echo "All location data is consistent.\n";
        }
    }

    // Show final statistics
    echo "\n=== Final Statistics ===\n";
    $finalReport = $migrationHelper->generateMigrationReport();
    echo "Total users with location IDs: " . $finalReport['users_migrated'] . "\n";
    echo "Total users needing migration: " . $finalReport['users_to_migrate'] . "\n";

    if ($finalReport['users_to_migrate'] == 0) {
        echo "\n✅ All users have been successfully migrated to the new location hierarchy!\n";
    } else {
        echo "\n⚠️  Some users still need manual migration.\n";
    }

} catch (Exception $e) {
    echo "❌ Error during migration: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>\n";

if (php_sapi_name() !== 'cli') {
    echo "<p><a href='?' style='background: #007cba; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>Refresh Report</a></p>\n";
}
