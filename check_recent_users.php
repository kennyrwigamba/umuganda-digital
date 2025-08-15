<?php
try {
    $pdo  = new PDO('mysql:host=localhost;dbname=umuganda_digital', 'root', '');
    $stmt = $pdo->prepare('SELECT id, first_name, last_name, email, province_id, district_id, sector_id, cell_id FROM users WHERE id IN (23, 24) ORDER BY id');
    $stmt->execute();
    echo "Recent registrations:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID {$row['id']}: {$row['first_name']} {$row['last_name']} ({$row['email']}) - Location: P{$row['province_id']}/D{$row['district_id']}/S{$row['sector_id']}/C{$row['cell_id']}\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
