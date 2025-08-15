<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=umuganda_digital', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== USERS IN DATABASE ===\n";
    $stmt = $pdo->query('SELECT id, first_name, last_name, role, sector_id FROM users LIMIT 10');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Name: {$row['first_name']} {$row['last_name']}, Role: {$row['role']}, Sector: {$row['sector_id']}\n";
    }

    echo "\n=== ADMIN USERS ===\n";
    $stmt = $pdo->query('SELECT id, first_name, last_name, sector_id FROM users WHERE role = "admin"');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Admin ID: {$row['id']}, Name: {$row['first_name']} {$row['last_name']}, Sector: {$row['sector_id']}\n";
    }

    echo "\n=== SAMPLE DATA COUNTS ===\n";
    $stmt   = $pdo->query('SELECT COUNT(*) as count FROM attendance');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total attendance records: " . $result['count'] . "\n";

    $stmt   = $pdo->query('SELECT COUNT(*) as count FROM fines');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total fines records: " . $result['count'] . "\n";

    $stmt   = $pdo->query('SELECT COUNT(*) as count FROM umuganda_events');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total events records: " . $result['count'] . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
