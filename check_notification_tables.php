<?php
require_once 'src/helpers/env.php';
require_once 'config/db.php';

$conn = $db->getConnection();

$tables = ['notifications', 'notification_channels', 'user_notification_preferences', 'push_subscriptions', 'notification_reads'];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    echo $table . ': ' . ($result->num_rows > 0 ? 'EXISTS' : 'NOT EXISTS') . PHP_EOL;
}
?>
