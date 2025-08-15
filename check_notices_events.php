<?php
require_once 'config/db.php';

echo "=== Notices Data Analysis ===\n";
$stmt = $pdo->query("SELECT COUNT(*) as total, COUNT(cell_id) as with_ids, COUNT(cell) as with_strings FROM notices");
$row  = $stmt->fetch();
echo "Total notices: {$row['total']}\n";
echo "With location IDs: {$row['with_ids']}\n";
echo "With string locations: {$row['with_strings']}\n";

if ($row['total'] > 0) {
    echo "\nSample notices data:\n";
    $stmt = $pdo->query("SELECT id, title, cell, sector, district, province, cell_id FROM notices LIMIT 5");
    while ($notice = $stmt->fetch()) {
        echo "Notice {$notice['id']}: '{$notice['title']}' | Location: {$notice['province']} > {$notice['district']} > {$notice['sector']} > {$notice['cell']} | cell_id: {$notice['cell_id']}\n";
    }
}

echo "\n=== Umuganda Events Data Analysis ===\n";
$stmt = $pdo->query("SELECT COUNT(*) as total, COUNT(cell_id) as with_ids, COUNT(cell) as with_strings FROM umuganda_events");
$row  = $stmt->fetch();
echo "Total events: {$row['total']}\n";
echo "With location IDs: {$row['with_ids']}\n";
echo "With string locations: {$row['with_strings']}\n";

if ($row['total'] > 0) {
    echo "\nSample events data:\n";
    $stmt = $pdo->query("SELECT id, title, cell, sector, district, province, cell_id FROM umuganda_events LIMIT 5");
    while ($event = $stmt->fetch()) {
        echo "Event {$event['id']}: '{$event['title']}' | Location: {$event['province']} > {$event['district']} > {$event['sector']} > {$event['cell']} | cell_id: {$event['cell_id']}\n";
    }
}

echo "\n=== Migration Candidates ===\n";
// Check notices that need migration
$stmt               = $pdo->query("SELECT COUNT(*) FROM notices WHERE cell_id IS NULL AND cell IS NOT NULL AND cell != ''");
$notices_to_migrate = $stmt->fetchColumn();
echo "Notices needing migration: $notices_to_migrate\n";

// Check events that need migration
$stmt              = $pdo->query("SELECT COUNT(*) FROM umuganda_events WHERE cell_id IS NULL AND cell IS NOT NULL AND cell != ''");
$events_to_migrate = $stmt->fetchColumn();
echo "Events needing migration: $events_to_migrate\n";
