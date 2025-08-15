<?php
require_once 'config/db.php';

echo "=== Failed Notice Analysis ===\n";
$stmt = $pdo->query("SELECT id, title, cell, sector, district, province FROM notices WHERE cell_id IS NULL AND cell IS NOT NULL AND cell != ''");
while ($row = $stmt->fetch()) {
    echo "Notice {$row['id']}: '{$row['title']}' | Location: '{$row['province']}' > '{$row['district']}' > '{$row['sector']}' > '{$row['cell']}'\n";
}

echo "\n=== All Notices Status ===\n";
$stmt = $pdo->query("SELECT id, title, cell, sector, district, province, cell_id FROM notices ORDER BY id");
while ($row = $stmt->fetch()) {
    $status = $row['cell_id'] ? '✅ Migrated' : ($row['cell'] ? '❌ Failed' : '➖ No location');
    echo "Notice {$row['id']}: '{$row['title']}' | $status\n";
    if ($row['cell']) {
        echo "  Location: '{$row['province']}' > '{$row['district']}' > '{$row['sector']}' > '{$row['cell']}'\n";
    }
}
