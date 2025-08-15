<?php
require_once __DIR__ . '/config/db.php';

global $db;
$connection = $db->getConnection();

echo "<h2>Users Table Structure:</h2>\n";
$result = $connection->query('DESCRIBE users');
while ($row = $result->fetch_assoc()) {
    echo "<p>{$row['Field']} - {$row['Type']}</p>\n";
}
