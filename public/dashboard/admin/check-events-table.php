<?php
require_once __DIR__ . '/../../../config/db.php';

try {
    $database = new Database();
    $db       = $database->getConnection();

    // Check umuganda_events table structure
    echo "umuganda_events table structure:\n";
    $result = $db->query("DESCRIBE umuganda_events");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['Field']} ({$row['Type']})\n";
        }
    }

    echo "\nevents_with_location table structure:\n";
    $result = $db->query("DESCRIBE events_with_location");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['Field']} ({$row['Type']})\n";
        }
    }

    // Test join with umuganda_events
    echo "\nTest join with umuganda_events:\n";
    $result = $db->query("SELECT f.id, f.event_id, e.title, e.event_date FROM fines f LEFT JOIN umuganda_events e ON f.event_id = e.id LIMIT 3");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
