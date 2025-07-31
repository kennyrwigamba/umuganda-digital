<?php
require_once 'config/db.php';

echo "=== Migration Results ===\n";
$stmt = $pdo->query("
    SELECT u.id, u.email,
           p.name as province, d.name as district, s.name as sector, c.name as cell
    FROM users u
    LEFT JOIN cells c ON u.cell_id = c.id
    LEFT JOIN sectors s ON u.sector_id = s.id
    LEFT JOIN districts d ON u.district_id = d.id
    LEFT JOIN provinces p ON u.province_id = p.id
    WHERE u.cell_id IS NOT NULL
    ORDER BY u.id
");

while ($row = $stmt->fetch()) {
    echo "User {$row['id']} ({$row['email']}): {$row['province']} > {$row['district']} > {$row['sector']} > {$row['cell']}\n";
}

echo "\n=== Migration Statistics ===\n";
$total_users    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$migrated_users = $pdo->query("SELECT COUNT(*) FROM users WHERE cell_id IS NOT NULL")->fetchColumn();

echo "Total users: $total_users\n";
echo "Migrated users: $migrated_users\n";
echo "Migration completion: " . round(($migrated_users / $total_users) * 100, 1) . "%\n";
