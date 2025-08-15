<?php
namespace UmugandaDigital\Channels;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * PushChannel
 * Handles web push notifications using the Web Push Protocol
 */
class PushChannel
{
    private string $vapidPublicKey;
    private string $vapidPrivateKey;
    private string $vapidSubject;

    public function __construct()
    {
        // Load VAPID configuration from environment
        $this->vapidPublicKey  = $_ENV['VAPID_PUBLIC_KEY'] ?? '';
        $this->vapidPrivateKey = $_ENV['VAPID_PRIVATE_KEY'] ?? '';
        $this->vapidSubject    = $_ENV['VAPID_SUBJECT'] ?? 'mailto:admin@umuganda.rw';

        // Validate VAPID keys are set
        if (empty($this->vapidPublicKey) || empty($this->vapidPrivateKey)) {
            throw new \Exception('VAPID keys are not configured. Run php generate_vapid_keys.php to generate them.');
        }
    }

    /**
     * Send push notification
     */
    public function send(array $notification, array $channelRow): array
    {
        try {
            // Get user's push subscriptions
            $subscriptions = $this->getUserPushSubscriptions($notification['user_id']);

            if (empty($subscriptions)) {
                return [
                    'success' => false,
                    'error'   => 'No push subscriptions found for user',
                ];
            }

            // Create WebPush instance
            $webPush = new WebPush([
                'VAPID' => [
                    'subject'    => $this->vapidSubject,
                    'publicKey'  => $this->vapidPublicKey,
                    'privateKey' => $this->vapidPrivateKey,
                ],
            ]);

            // Prepare push payload
            $payload = $this->createPushPayload($notification);

            $sentCount = 0;
            $errors    = [];

            // Send to all user's subscriptions
            foreach ($subscriptions as $subscription) {
                try {
                    $pushSubscription = Subscription::create([
                        'endpoint' => $subscription['endpoint'],
                        'keys'     => [
                            'p256dh' => $subscription['p256dh'],
                            'auth'   => $subscription['auth'],
                        ],
                    ]);

                    $result = $webPush->sendOneNotification(
                        $pushSubscription,
                        json_encode($payload)
                    );

                    if ($result->isSuccess()) {
                        $sentCount++;
                    } else {
                        $errors[] = "Subscription {$subscription['id']}: " . $result->getReason();

                        // Remove invalid subscriptions
                        if ($result->isSubscriptionExpired()) {
                            $this->removeExpiredSubscription($subscription['id']);
                        }
                    }

                } catch (\Exception $e) {
                    $errors[] = "Subscription {$subscription['id']}: " . $e->getMessage();
                }
            }

            if ($sentCount > 0) {
                return [
                    'success' => true,
                    'error'   => null,
                    'details' => "Sent to $sentCount/" . count($subscriptions) . " subscriptions",
                ];
            } else {
                return [
                    'success' => false,
                    'error'   => 'Failed to send to any subscriptions: ' . implode('; ', $errors),
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => 'Push send failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get user's push subscriptions from database
     */
    private function getUserPushSubscriptions(int $userId): array
    {
        require_once __DIR__ . '/../../config/db.php';
        $database   = new \Database();
        $connection = $database->getConnection();

        $stmt = $connection->prepare(
            "SELECT * FROM push_subscriptions
             WHERE user_id = ? AND is_active = 1"
        );

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $subscriptions = [];
        while ($row = $result->fetch_assoc()) {
            $subscriptions[] = $row;
        }

        $stmt->close();
        return $subscriptions;
    }

    /**
     * Create push notification payload
     */
    private function createPushPayload(array $notification): array
    {
        $data = json_decode($notification['data'], true) ?? [];

        return [
            'title'              => $notification['title'],
            'body'               => $notification['body'],
            'icon'               => $this->getNotificationIcon($notification['type']),
            'badge'              => ($_ENV['APP_URL'] ?? 'https://umuganda.rw') . '/assets/images/badge-96x96.png',
            'image'              => $this->getNotificationImage($notification['type']),
            'tag'                => $notification['type'] . '_' . $notification['id'],
            'data'               => [
                'notificationId' => $notification['id'],
                'type'           => $notification['type'],
                'category'       => $notification['category'],
                'url'            => $this->getNotificationUrl($notification['type'], $data),
                'timestamp'      => time(),
                'actions'        => $this->getNotificationActions($notification['type']),
            ],
            'actions'            => $this->getNotificationActions($notification['type']),
            'vibrate'            => $this->getVibrationPattern($notification['type']),
            'requireInteraction' => $this->requiresInteraction($notification['type']),
        ];
    }

    /**
     * Get notification icon based on type
     */
    private function getNotificationIcon(string $type): string
    {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';

        $icons = [
            'attendance_recorded' => '/assets/images/icons/attendance-icon.png',
            'event_reminder'      => '/assets/images/icons/event-icon.png',
            'event_cancelled'     => '/assets/images/icons/event-cancelled-icon.png',
            'fine_issued'         => '/assets/images/icons/fine-icon.png',
            'payment_received'    => '/assets/images/icons/payment-icon.png',
            'announcement'        => '/assets/images/icons/announcement-icon.png',
            'emergency_alert'     => '/assets/images/icons/emergency-icon.png',
            'suspicious_login'    => '/assets/images/icons/security-icon.png',
            'system_maintenance'  => '/assets/images/icons/maintenance-icon.png',
            'report_generated'    => '/assets/images/icons/report-icon.png',
        ];

        return $baseUrl . ($icons[$type] ?? '/assets/images/icons/default-icon.png');
    }

    /**
     * Get notification image for rich notifications
     */
    private function getNotificationImage(string $type): ?string
    {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';

        $images = [
            'emergency_alert' => '/assets/images/emergency-banner.jpg',
            'event_reminder'  => '/assets/images/event-banner.jpg',
            'announcement'    => '/assets/images/community-banner.jpg',
        ];

        return isset($images[$type]) ? $baseUrl . $images[$type] : null;
    }

    /**
     * Get notification URL for click action
     */
    private function getNotificationUrl(string $type, array $data): string
    {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';

        switch ($type) {
            case 'attendance_recorded':
                return $baseUrl . '/attendance';
            case 'event_reminder':
            case 'event_cancelled':
                return $baseUrl . '/events' . (isset($data['event_id']) ? '/' . $data['event_id'] : '');
            case 'fine_issued':
                return $baseUrl . '/fines' . (isset($data['fine_id']) ? '/' . $data['fine_id'] : '');
            case 'payment_received':
                return $baseUrl . '/payments';
            case 'announcement':
                return $baseUrl . '/announcements';
            case 'report_generated':
                return $baseUrl . '/reports';
            case 'suspicious_login':
                return $baseUrl . '/profile/security';
            default:
                return $baseUrl . '/notifications';
        }
    }

    /**
     * Get notification actions (buttons)
     */
    private function getNotificationActions(string $type): array
    {
        switch ($type) {
            case 'event_reminder':
                return [
                    ['action' => 'view', 'title' => 'View Event'],
                    ['action' => 'dismiss', 'title' => 'Dismiss'],
                ];
            case 'fine_issued':
                return [
                    ['action' => 'pay', 'title' => 'Pay Now'],
                    ['action' => 'view', 'title' => 'View Details'],
                ];
            case 'emergency_alert':
                return [
                    ['action' => 'acknowledge', 'title' => 'Acknowledge'],
                    ['action' => 'view', 'title' => 'View Details'],
                ];
            default:
                return [
                    ['action' => 'view', 'title' => 'View'],
                    ['action' => 'dismiss', 'title' => 'Dismiss'],
                ];
        }
    }

    /**
     * Get vibration pattern based on notification type
     */
    private function getVibrationPattern(string $type): array
    {
        switch ($type) {
            case 'emergency_alert':
                return [200, 100, 200, 100, 200]; // Urgent pattern
            case 'fine_issued':
                return [100, 50, 100]; // Attention pattern
            case 'event_reminder':
                return [200, 100, 200]; // Standard reminder
            default:
                return [200]; // Simple single vibration
        }
    }

    /**
     * Determine if notification requires user interaction
     */
    private function requiresInteraction(string $type): bool
    {
        return in_array($type, [
            'emergency_alert',
            'suspicious_login',
            'fine_issued',
        ]);
    }

    /**
     * Remove expired push subscription
     */
    private function removeExpiredSubscription(int $subscriptionId): void
    {
        require_once __DIR__ . '/../../config/db.php';
        $database   = new \Database();
        $connection = $database->getConnection();

        $stmt = $connection->prepare(
            "UPDATE push_subscriptions SET is_active = 0 WHERE id = ?"
        );
        $stmt->bind_param('i', $subscriptionId);
        $stmt->execute();
        $stmt->close();
    }
}
