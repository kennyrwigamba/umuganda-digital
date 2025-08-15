<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=umuganda_digital', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "NOTICES TABLE STRUCTURE:\n";
    $stmt = $pdo->query('DESCRIBE notices');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }

    echo "\nSAMPLE NOTICES DATA:\n";
    $stmt   = $pdo->query('SELECT COUNT(*) as count FROM notices');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total notices: " . $result['count'] . "\n";

    // Check if table exists and get some sample data
    $stmt = $pdo->query('SELECT id, title, type, status, sector_id FROM notices LIMIT 5');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Title: {$row['title']}, Type: {$row['type']}, Status: {$row['status']}, Sector: {$row['sector_id']}\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
