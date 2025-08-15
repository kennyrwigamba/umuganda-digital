<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=umuganda_digital', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking table structures...\n\n";

    // Check attendance table
    $stmt = $pdo->query('DESCRIBE attendance');
    echo "ATTENDANCE TABLE:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }

    echo "\n";

    // Check fines table
    $stmt = $pdo->query('DESCRIBE fines');
    echo "FINES TABLE:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }

    echo "\n";

    // Check users table for admin sector info
    $stmt = $pdo->query('DESCRIBE users');
    echo "USERS TABLE:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
