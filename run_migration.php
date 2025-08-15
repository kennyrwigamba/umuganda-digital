<?php
/**
 * Run notification system migration
 */

require_once __DIR__ . '/config/db.php';

try {
    global $db;
    $connection = $db->getConnection();

    echo "Starting notification system migration...\n";

    $sql = file_get_contents(__DIR__ . '/migrations/2025_08_15_0001_notification_tables.sql');

    if ($connection->multi_query($sql)) {
        do {
            if ($result = $connection->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($row['message'])) {
                        echo $row['message'] . "\n";
                    }
                }
                $result->free();
            }
        } while ($connection->next_result());

        echo "Migration completed successfully!\n";

        // Verify tables were created
        $tables = ['notifications', 'notification_channels', 'user_notification_preferences', 'push_subscriptions', 'notification_reads'];
        echo "\nVerifying tables:\n";

        foreach ($tables as $table) {
            $result = $connection->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "✓ Table '$table' created\n";
            } else {
                echo "✗ Table '$table' missing\n";
            }
        }

        // Check user preferences backfill
        $result = $connection->query("SELECT COUNT(*) as count FROM user_notification_preferences");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "\n✓ User notification preferences backfilled: {$row['count']} records\n";
        }

    } else {
        echo "Migration failed: " . $connection->error . "\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
