<?php
require_once 'src/helpers/env.php';
require_once 'config/db.php';

$conn = $db->getConnection();

echo "Recent notifications:\n";
$result = $conn->query('SELECT id, user_id, title, body, type, status, created_at FROM notifications ORDER BY id DESC LIMIT 5');
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, User: {$row['user_id']}, Type: {$row['type']}, Title: {$row['title']}, Status: {$row['status']}, Created: {$row['created_at']}\n";
}

echo "\nRecent notification channels:\n";
$result = $conn->query('SELECT nc.id, nc.notification_id, nc.channel, nc.status, nc.created_at FROM notification_channels nc ORDER BY nc.id DESC LIMIT 5');
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, Notification: {$row['notification_id']}, Channel: {$row['channel']}, Status: {$row['status']}, Created: {$row['created_at']}\n";
}
?>
