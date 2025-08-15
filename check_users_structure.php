<?php
require_once __DIR__ . '/config/db.php';

global $db;
$result = $db->getConnection()->query('DESCRIBE users');

echo "Users table structure:\n";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ')' . "\n";
}
