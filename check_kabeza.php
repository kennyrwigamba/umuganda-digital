<?php
require_once 'config/db.php';

echo "=== Checking Kabeza ===\n";
$stmt = $pdo->query("
    SELECT c.name as cell_name, s.name as sector_name, d.name as district_name
    FROM cells c
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    WHERE c.name LIKE '%Kabeza%' OR s.name LIKE '%Kabeza%'
");

$found = false;
while ($row = $stmt->fetch()) {
    echo "{$row['district_name']} > {$row['sector_name']} > {$row['cell_name']}\n";
    $found = true;
}

if (! $found) {
    echo "Kabeza not found in database\n";
}

echo "\n=== All Kicukiro Sectors ===\n";
$stmt = $pdo->query("
    SELECT s.name
    FROM sectors s
    JOIN districts d ON s.district_id = d.id
    WHERE d.name = 'Kicukiro'
    ORDER BY s.name
");
while ($row = $stmt->fetch()) {
    echo "- {$row['name']}\n";
}

echo "\n=== Potential Alternative for Kabeza Users ===\n";
echo "Since Kabeza sector doesn't exist, we could:\n";
echo "1. Add Kabeza as a new sector in Kicukiro district\n";
echo "2. Map Kabeza users to an existing Kicukiro sector\n";
echo "3. Map them to the first available cell in any Kicukiro sector\n";
