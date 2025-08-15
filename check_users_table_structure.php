<?php
try {
    $pdo  = new PDO('mysql:host=localhost;dbname=umuganda_digital', 'root', '');
    $stmt = $pdo->query('DESCRIBE users');
    echo "Users table structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
