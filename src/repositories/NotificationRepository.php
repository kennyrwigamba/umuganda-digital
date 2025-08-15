<?php
namespace UmugandaDigital\Repositories;

use mysqli;

/**
 * NotificationRepository
 * Handles CRUD operations for notifications and channels
 */
class NotificationRepository
{
    private mysqli $connection;

    public function __construct(mysqli $c    /**
     * Get user notifications with pagination and filters
     */
    public function getUserNotifications(int $userId, int $page = 1, int $limit = 20, ?string $type = null, ?string $category = null, bool $unreadOnly = false): array
    {nnection)
    {
        $this->connection = $connection;
    }

    /**
     * Create a new notification
     */
    public function createNotification(
        ?int $userId,
        string $title,
        string $body,
        string $type,
        string $category,
        string $priority = 'normal',
        ?array $data = null
    ): int {
        $dataJson = $data ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;

        $stmt = $this->connection->prepare(
            "INSERT INTO notifications (user_id, title, body, type, category, priority, data, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())"
        );

        $stmt->bind_param('issssss', $userId, $title, $body, $type, $category, $priority, $dataJson);
        $stmt->execute();

        $notificationId = $this->connection->insert_id;
        $stmt->close();

        return $notificationId;
    }

    /**
     * Create notification channel records
     */
    public function createNotificationChannels(int $notificationId, array $channels): bool
    {
        if (empty($channels)) {
            return true;
        }

        $placeholders = str_repeat('(?,?),', count($channels));
        $placeholders = rtrim($placeholders, ',');

        $stmt = $this->connection->prepare(
            "INSERT INTO notification_channels (notification_id, channel) VALUES $placeholders"
        );

        $types  = str_repeat('is', count($channels));
        $values = [];

        foreach ($channels as $channel) {
            $values[] = $notificationId;
            $values[] = $channel;
        }

        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Get pending notification channels for worker
     */
    public function getPendingChannels(int $limit = 100): array
    {
        $stmt = $this->connection->prepare(
            "SELECT nc.*, n.* FROM notification_channels nc
             JOIN notifications n ON n.id = nc.notification_id
             WHERE nc.status = 'pending'
             ORDER BY nc.id ASC
             LIMIT ?"
        );

        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $channels = [];
        while ($row = $result->fetch_assoc()) {
            $channels[] = $row;
        }

        $stmt->close();
        return $channels;
    }

    /**
     * Update notification channel status
     */
    public function updateChannelStatus(
        int $channelId,
        string $status,
        ?string $error = null
    ): bool {
        $stmt = $this->connection->prepare(
            "UPDATE notification_channels
             SET status = ?, attempts = attempts + 1, last_error = ?,
                 attempted_at = NOW(), sent_at = IF(? = 'sent', NOW(), sent_at)
             WHERE id = ?"
        );

        $stmt->bind_param('sssi', $status, $error, $status, $channelId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Update notification aggregate status
     */
    public function updateNotificationStatus(int $notificationId): bool
    {
        $stmt = $this->connection->prepare(
            "UPDATE notifications n
             JOIN (
                 SELECT notification_id,
                     SUM(status='sent') sent_count,
                     SUM(status='failed') failed_count,
                     COUNT(*) total
                 FROM notification_channels
                 WHERE notification_id = ?
                 GROUP BY notification_id
             ) agg ON n.id = agg.notification_id
             SET n.status = CASE
                     WHEN agg.sent_count > 0 THEN 'sent'
                     WHEN agg.failed_count = agg.total THEN 'failed'
                     ELSE n.status
                 END,
                 n.error_message = CASE
                     WHEN agg.failed_count > 0 AND agg.sent_count = 0 THEN 'All channel attempts failed'
                     ELSE n.error_message
                 END,
                 n.sent_at = CASE
                     WHEN agg.sent_count > 0 AND n.sent_at IS NULL THEN NOW()
                     ELSE n.sent_at
                 END
             WHERE n.id = ?"
        );

        $stmt->bind_param('ii', $notificationId, $notificationId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Get notifications for a user (with read status)
     */
    public function getUserNotifications(
        int $userId,
        string $status = 'all',
        int $page = 1,
        int $perPage = 20
    ): array {
        $offset = ($page - 1) * $perPage;

        $whereClause = $status === 'unread'
        ? "AND nr.id IS NULL"
        : "";

        $stmt = $this->connection->prepare(
            "SELECT n.*, (nr.id IS NOT NULL) AS is_read
             FROM notifications n
             LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = ?
             WHERE (n.user_id = ? OR n.user_id IS NULL) $whereClause
             ORDER BY n.created_at DESC
             LIMIT ? OFFSET ?"
        );

        $stmt->bind_param('iiii', $userId, $userId, $perPage, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }

        $stmt->close();
        return $notifications;
    }

    /**
     * Mark notifications as read
     */
    public function markAsRead(int $userId, array $notificationIds): bool
    {
        if (empty($notificationIds)) {
            return true;
        }

        $placeholders = str_repeat('?,', count($notificationIds));
        $placeholders = rtrim($placeholders, ',');

        $stmt = $this->connection->prepare(
            "INSERT IGNORE INTO notification_reads (notification_id, user_id, read_at)
             SELECT id, ?, NOW() FROM notifications
             WHERE id IN ($placeholders) AND (user_id = ? OR user_id IS NULL)"
        );

        $types  = 'i' . str_repeat('i', count($notificationIds)) . 'i';
        $params = array_merge([$userId], $notificationIds, [$userId]);

        $stmt->bind_param($types, ...$params);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Mark single notification as read
     */
    public function markSingleAsRead(int $notificationId, int $userId): bool
    {
        $stmt = $this->connection->prepare(
            "INSERT IGNORE INTO notification_reads (notification_id, user_id, read_at)
             VALUES (?, ?, NOW())"
        );

        $stmt->bind_param('ii', $notificationId, $userId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(int $userId): int
    {
    $stmt = $this->connection->prepare(
        "SELECT COUNT(*) as count
             FROM notifications n
             LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = ?
             WHERE (n.user_id = ? OR n.user_id IS NULL) AND nr.id IS NULL"
    );

    $stmt->bind_param('ii', $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row    = $result->fetch_assoc();
    $stmt->close();

    return (int) $row['count'];
}

/**
 * Get user notifications with pagination and filters
 */
function(public getUserNotificationsint $userId, int $page = 1, int $limit = 20, ?string $type = null, ?string $category = null, bool $unreadOnly = false): array
{
    $offset = ($page - 1) * $limit;

    $whereConditions = ["(n.user_id = ? OR n.user_id IS NULL)"];
    $params          = [$userId];
    $paramTypes      = 'i';

    if ($type) {
        $whereConditions[] = "n.type = ?";
        $params[]          = $type;
        $paramTypes .= 's';
    }

    if ($category) {
        $whereConditions[] = "n.category = ?";
        $params[]          = $category;
        $paramTypes .= 's';
    }

    if ($unreadOnly) {
        $whereConditions[] = "nr.id IS NULL";
    }

    $whereClause = implode(' AND ', $whereConditions);

    $sql = "SELECT n.*,
                       nr.read_at,
                       CASE WHEN nr.id IS NULL THEN 0 ELSE 1 END as is_read
                FROM notifications n
                LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = ?
                WHERE $whereClause
                ORDER BY n.created_at DESC
                LIMIT ? OFFSET ?";

    $stmt = $this->connection->prepare($sql);

    // Add userId for the JOIN and limit/offset
    array_unshift($params, $userId);
    $params[]   = $limit;
    $params[]   = $offset;
    $paramTypes = 'i' . $paramTypes . 'ii';

    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    $stmt->close();
    return $notifications;
}

/**
 * Get user notification count with filters
 */
function(public getUserNotificationCountint $userId, ?string $type = null, ?string $category = null, bool $unreadOnly = false): int
{
    $whereConditions = ["(n.user_id = ? OR n.user_id IS NULL)"];
    $params          = [$userId];
    $paramTypes      = 'i';

    if ($type) {
        $whereConditions[] = "n.type = ?";
        $params[]          = $type;
        $paramTypes .= 's';
    }

    if ($category) {
        $whereConditions[] = "n.category = ?";
        $params[]          = $category;
        $paramTypes .= 's';
    }

    if ($unreadOnly) {
        $whereConditions[] = "nr.id IS NULL";
    }

    $whereClause = implode(' AND ', $whereConditions);

    $sql = "SELECT COUNT(*) as count
                FROM notifications n
                LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = ?
                WHERE $whereClause";

    $stmt = $this->connection->prepare($sql);

    // Add userId for the JOIN
    array_unshift($params, $userId);
    $paramTypes = 'i' . $paramTypes;

    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row    = $result->fetch_assoc();
    $stmt->close();

    return (int) $row['count'];
}

/**
 * Get unread notification count for user
 */
function(public getUnreadNotificationCountint $userId): int
{
    return $this->getUserNotificationCount($userId, null, null, true);
}

/**
 * Mark all notifications as read for a user
 */
function(public markAllAsReadint $userId): int
{
    // Get all unread notifications for the user
    $stmt = $this->connection->prepare(
        "SELECT n.id
             FROM notifications n
             LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = ?
             WHERE (n.user_id = ? OR n.user_id IS NULL) AND nr.id IS NULL"
    );

    $stmt->bind_param('ii', $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $this->markAsRead($row['id'], $userId);
        $count++;
    }

    $stmt->close();
    return $count;
}

/**
 * Save push subscription for user
 */
function(public savePushSubscriptionint $userId, string $endpoint, string $p256dh, string $auth): bool
{
    // First, deactivate any existing subscriptions for this endpoint
    $stmt = $this->connection->prepare(
        "UPDATE push_subscriptions SET is_active = 0 WHERE endpoint = ?"
    );
    $stmt->bind_param('s', $endpoint);
    $stmt->execute();
    $stmt->close();

    // Insert or update the subscription
    $stmt = $this->connection->prepare(
        "INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth, is_active)
             VALUES (?, ?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE
                p256dh = VALUES(p256dh),
                auth = VALUES(auth),
                is_active = 1,
                updated_at = CURRENT_TIMESTAMP"
    );

    $result  = $stmt->bind_param('isss', $userId, $endpoint, $p256dh, $auth);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}
};
