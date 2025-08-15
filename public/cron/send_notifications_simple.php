<?php
/**
 * Simple Notification Worker
 * Processes pending notification channels
 */

require_once __DIR__ . '/../../src/helpers/env.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use UmugandaDigital\Channels\EmailChannel;

echo "🔄 Starting notification worker...\n";

$connection = $db->getConnection();

// Get pending notification channels with their notification data
$query = "
    SELECT 
        nc.id as channel_id,
        nc.notification_id,
        nc.channel,
        nc.status as channel_status,
        nc.attempts,
        n.user_id,
        n.title,
        n.body,
        n.type,
        n.category,
        n.data,
        u.email as user_email,
        u.first_name,
        u.last_name
    FROM notification_channels nc
    JOIN notifications n ON n.id = nc.notification_id
    LEFT JOIN users u ON u.id = n.user_id
    WHERE nc.status = 'pending'
    ORDER BY nc.id ASC
    LIMIT 10
";

$result = $connection->query($query);
$processed = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "\n📧 Processing channel {$row['channel']} for notification {$row['notification_id']}\n";
        
        $success = false;
        $error = null;
        
        try {
            switch ($row['channel']) {
                case 'email':
                    if ($row['user_email']) {
                        $emailChannel = new EmailChannel();
                        
                        // Prepare notification data for email channel
                        $notification = [
                            'user_id' => $row['user_id'],
                            'title' => $row['title'],
                            'body' => $row['body'],
                            'type' => $row['type'],
                            'category' => $row['category'],
                            'data' => $row['data'],
                            'user_email' => $row['user_email'],
                            'user_name' => $row['first_name'] . ' ' . $row['last_name']
                        ];
                        
                        $channelRow = ['id' => $row['channel_id']];
                        $emailResult = $emailChannel->send($notification, $channelRow);
                        
                        $success = $emailResult['success'];
                        $error = $emailResult['error'] ?? null;
                        
                        if ($success) {
                            echo "   ✅ Email sent successfully to {$row['user_email']}\n";
                        } else {
                            echo "   ❌ Email failed: $error\n";
                        }
                    } else {
                        $error = "No email address for user";
                        echo "   ❌ $error\n";
                    }
                    break;
                    
                case 'inapp':
                    // In-app notifications are just stored in DB, so mark as sent immediately
                    $success = true;
                    echo "   ✅ In-app notification stored\n";
                    break;
                    
                case 'push':
                    // Push notifications not implemented yet
                    $error = "Push notifications not implemented";
                    echo "   ⚠️  $error\n";
                    break;
                    
                default:
                    $error = "Unknown channel type: {$row['channel']}";
                    echo "   ❌ $error\n";
            }
            
        } catch (Exception $e) {
            $success = false;
            $error = $e->getMessage();
            echo "   ❌ Exception: $error\n";
        }
        
        // Update channel status
        $newStatus = $success ? 'sent' : 'failed';
        $updateQuery = "
            UPDATE notification_channels 
            SET status = ?, 
                attempts = attempts + 1,
                last_error = ?,
                attempted_at = NOW(),
                sent_at = IF(? = 'sent', NOW(), sent_at)
            WHERE id = ?
        ";
        
        $stmt = $connection->prepare($updateQuery);
        $stmt->bind_param('sssi', $newStatus, $error, $newStatus, $row['channel_id']);
        $stmt->execute();
        $stmt->close();
        
        $processed++;
    }
    
    echo "\n✅ Processed $processed notification channels\n";
} else {
    echo "📭 No pending notifications to process\n";
}

// Update notification status based on channel results
$updateNotificationQuery = "
    UPDATE notifications n
    SET status = CASE 
        WHEN EXISTS(
            SELECT 1 FROM notification_channels nc 
            WHERE nc.notification_id = n.id AND nc.status = 'sent'
        ) THEN 'sent'
        WHEN NOT EXISTS(
            SELECT 1 FROM notification_channels nc 
            WHERE nc.notification_id = n.id AND nc.status = 'pending'
        ) THEN 'failed'
        ELSE n.status
    END,
    sent_at = CASE 
        WHEN EXISTS(
            SELECT 1 FROM notification_channels nc 
            WHERE nc.notification_id = n.id AND nc.status = 'sent'
        ) AND n.sent_at IS NULL THEN NOW()
        ELSE n.sent_at
    END
    WHERE n.status = 'pending'
";

$connection->query($updateNotificationQuery);

echo "🔄 Notification worker completed\n";
?>
