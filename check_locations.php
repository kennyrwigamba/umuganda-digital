<?php
    require_once 'config/db.php';

    echo "=== Provinces in Database ===\n";
    $stmt = $pdo->query("SELECT id, name FROM provinces ORDER BY name");
    while ($row = $stmt->fetch()) {
        echo "{$row['id']}: {$row['name']}\n";
    }

    echo "\n=== Districts in Kigali City ===\n";
    $stmt = $pdo->query("
    SELECT d.id, d.name, p.name as province_name
    FROM districts d
    JOIN provinces p ON d.province_id = p.id
    WHERE p.name LIKE '%Kigali%'
    ORDER BY d.name
");
    while ($row = $stmt->fetch()) {
        echo "{$row['id']}: {$row['name']} (Province: {$row['province_name']})\n";
    }

    echo "\n=== Sample User Locations ===\n";
    $stmt = $pdo->query("SELECT DISTINCT province, district, sector, cell FROM users WHERE cell IS NOT NULL AND cell != 'Not Set' LIMIT 5");
    while ($row = $stmt->fetch()) {
        echo "User location: {$row['province']} > {$row['district']} > {$row['sector']} > {$row['cell']}\n";
    }

    echo "\n=== Sectors in Gasabo District ===\n";
    $stmt = $pdo->query("
    SELECT s.name
    FROM sectors s
    JOIN districts d ON s.district_id = d.id
    WHERE d.name = 'Gasabo'
    ORDER BY s.name
");
    while ($row = $stmt->fetch()) {
        echo "- {$row['name']}\n";
    }

    echo "\n=== Cells in User Data vs Database ===\n";
    echo "User data analysis:\n";
    $stmt = $pdo->query("SELECT DISTINCT sector, cell FROM users WHERE cell IS NOT NULL AND cell != 'Not Set'");
    while ($row = $stmt->fetch()) {
        echo "User has: sector='{$row['sector']}', cell='{$row['cell']}'\n";
    }

    echo "\nDatabase check - Are user 'cells' actually sectors?\n";
    $stmt = $pdo->query("
    SELECT s.name as sector_name, d.name as district_name
    FROM sectors s
    JOIN districts d ON s.district_id = d.id
    WHERE s.name IN ('Kimihurura', 'Remera', 'Nyamirambo', 'Gikondo', 'Kabeza', 'Muhima')
    ORDER BY d.name, s.name
");
    while ($row = $stmt->fetch()) {
        echo "✓ '{$row['sector_name']}' is a SECTOR in {$row['district_name']} district\n";
    }

    echo "\nSample cells in these sectors:\n";
    $stmt = $pdo->query("
    SELECT c.name as cell_name, s.name as sector_name, d.name as district_name
    FROM cells c
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    WHERE s.name IN ('Kimihurura', 'Remera', 'Nyamirambo')
    ORDER BY s.name, c.name
    LIMIT 10
");
    while ($row = $stmt->fetch()) {
        echo "- {$row['district_name']} > {$row['sector_name']} > {$row['cell_name']}\n";
    }

    echo "\n=== Check Missing Sectors ===\n";
    $missing_sectors = ['Kimihurura', 'Gikondo', 'Kabeza', 'Muhima'];
    foreach ($missing_sectors as $sector) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM sectors WHERE name = ?");
        $stmt->execute([$sector]);
        $count = $stmt->fetchColumn();
        echo "$sector: " . ($count > 0 ? 'EXISTS' : 'MISSING') . "\n";

        if ($count == 0) {
            // Check if it exists as a cell name instead
            $stmt = $pdo->prepare("SELECT s.name as sector_name, d.name as district_name FROM cells c JOIN sectors s ON c.sector_id = s.id JOIN districts d ON s.district_id = d.id WHERE c.name = ?");
            $stmt->execute([$sector]);
            $cell_match = $stmt->fetch();
            if ($cell_match) {
                echo "  -> Found as CELL in {$cell_match['district_name']} > {$cell_match['sector_name']}\n";
            }
        }
    }
?>
?>
