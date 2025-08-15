<?php
namespace UmugandaDigital\Services;

use UmugandaDigital\Repositories\NotificationRepository;
use UmugandaDigital\Repositories\PreferenceRepository;

/**
 * NotificationService
 * Main service for creating and managing notifications
 */
class NotificationService
{
    private NotificationRepository $notificationRepo;
    private PreferenceRepository $preferenceRepo;

    // Valid notification types mapped to categories
    private const NOTIFICATION_TYPES = [
        // Registration & Onboarding
        'user_registered'                => 'system',
        'registration_pending_approval'  => 'system',
        'registration_approved'          => 'system',
        'registration_rejected'          => 'system',
        'email_verification_required'    => 'system',
        'welcome_message'                => 'system',

        // Account & Security
        'password_changed'               => 'system',
        'password_change_failed'         => 'system',
        'suspicious_login'               => 'system',
        'two_factor_enabled'             => 'system',
        'two_factor_disabled'            => 'system',
        'profile_updated'                => 'system',

        // Attendance & Events
        'attendance_recorded'            => 'attendance',
        'attendance_correction'          => 'attendance',
        'attendance_missed'              => 'attendance',
        'monthly_attendance_summary'     => 'attendance',
        'event_created'                  => 'event',
        'event_updated'                  => 'event',
        'event_cancelled'                => 'event',
        'event_reminder_24h'             => 'event',
        'event_reminder_1h'              => 'event',
        'event_started'                  => 'event',
        'event_feedback_request'         => 'event',

        // Fines & Payments
        'fine_issued'                    => 'fine',
        'fine_updated'                   => 'fine',
        'fine_overdue_reminder'          => 'fine',
        'fine_waived'                    => 'fine',
        'payment_initiated'              => 'payment',
        'payment_success'                => 'payment',
        'payment_failed'                 => 'payment',
        'payment_refunded'               => 'payment',
        'payment_receipt'                => 'payment',

        // Notices & Announcements
        'new_notice'                     => 'announcement',
        'notice_updated'                 => 'announcement',
        'notice_expiring_soon'           => 'announcement',
        'system_announcement'            => 'announcement',
        'emergency_alert'                => 'announcement',

        // Location & Administration
        'location_reassigned'            => 'system',
        'new_local_admin_assigned'       => 'system',
        'admin_role_granted'             => 'system',
        'admin_role_revoked'             => 'system',

        // QR Code & Identity
        'qr_code_generated'              => 'system',
        'qr_code_regenerated'            => 'system',
        'qr_code_expiring'               => 'system',

        // Data & Reports
        'monthly_report_ready'           => 'report',
        'export_ready'                   => 'report',
        'export_failed'                  => 'report',

        // Preferences & Communication
        'preferences_updated'            => 'system',
        'channel_disabled_due_to_errors' => 'system',
        'reenable_channel_confirmation'  => 'system',
        'unsubscribe_confirmation'       => 'system',

        // Push Subscription
        'push_subscription_confirmed'    => 'system',
        'push_subscription_expired'      => 'system',

        // Maintenance
        'scheduled_maintenance'          => 'system',
        'maintenance_started'            => 'system',
        'maintenance_completed'          => 'system',
        'feature_update'                 => 'system',

        // Misc
        'feedback_request'               => 'system',
        'feedback_thanks'                => 'system',
        'generic_info'                   => 'other',
        'action_required'                => 'other',
    ];

    // Critical types that bypass some user preferences
    private const CRITICAL_TYPES = [
        'emergency_alert',
        'suspicious_login',
        'event_cancelled',
        'email_verification_required',
    ];

    public function __construct(
        NotificationRepository $notificationRepo,
        PreferenceRepository $preferenceRepo
    ) {
        $this->notificationRepo = $notificationRepo;
        $this->preferenceRepo   = $preferenceRepo;
    }

    /**
     * Create notification for a specific user
     */
    public function notifyUser(
        int $userId,
        string $type,
        string $title,
        string $body,
        array $data = [],
        string $priority = 'normal'
    ): int {
        // Validate notification type
        if (! isset(self::NOTIFICATION_TYPES[$type])) {
            throw new \InvalidArgumentException("Invalid notification type: $type");
        }

        $category = self::NOTIFICATION_TYPES[$type];

        // Create notification record
        $notificationId = $this->notificationRepo->createNotification(
            $userId,
            $title,
            $body,
            $type,
            $category,
            $priority,
            $data
        );

        // Determine enabled channels
        $channels = $this->resolveChannels($userId, $category, $type, $priority);

        // Create channel records
        if (! empty($channels)) {
            $this->notificationRepo->createNotificationChannels($notificationId, $channels);
        }

        return $notificationId;
    }

    /**
     * Create notifications for multiple users
     */
    public function notifyMultiple(
        array $userIds,
        string $type,
        string $title,
        string $body,
        array $data = [],
        string $priority = 'normal'
    ): array {
        $notificationIds = [];

        foreach ($userIds as $userId) {
            $notificationIds[] = $this->notifyUser($userId, $type, $title, $body, $data, $priority);
        }

        return $notificationIds;
    }

    /**
     * Create broadcast notification (all users)
     */
    public function notifyAll(
        string $type,
        string $title,
        string $body,
        array $data = [],
        string $priority = 'normal',
        int $batchSize = 100
    ): array {
        // For simplicity, we'll create individual notifications per user
        // This avoids complex fan-out logic in the worker

        global $db;
        $connection = $db->getConnection();

        $result  = $connection->query("SELECT id FROM users WHERE status = 'active'");
        $userIds = [];

        while ($row = $result->fetch_assoc()) {
            $userIds[] = (int) $row['id'];
        }

        // Process in batches to avoid memory issues
        $notificationIds = [];
        $batches         = array_chunk($userIds, $batchSize);

        foreach ($batches as $batch) {
            $batchIds        = $this->notifyMultiple($batch, $type, $title, $body, $data, $priority);
            $notificationIds = array_merge($notificationIds, $batchIds);
        }

        return $notificationIds;
    }

    /**
     * Mark notifications as read
     */
    public function markAsRead(int $userId, array $notificationIds): bool
    {
        return $this->notificationRepo->markAsRead($userId, $notificationIds);
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(
        int $userId,
        string $status = 'all',
        int $page = 1,
        int $perPage = 20
    ): array {
        return $this->notificationRepo->getUserNotifications($userId, $status, $page, $perPage);
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->notificationRepo->getUnreadCount($userId);
    }

    /**
     * Resolve which channels should be used for a notification
     */
    private function resolveChannels(int $userId, string $category, string $type, string $priority): array
    {
        $channels = [];

        // Always include in-app channel
        $channels[] = 'inapp';

        // Check if this is a critical notification that bypasses preferences
        $isCritical = in_array($type, self::CRITICAL_TYPES) || $priority === 'critical';

        if ($isCritical) {
            // For critical notifications, use all channels
            $channels[] = 'email';
            $channels[] = 'push';
        } else {
            // Check user preferences for other channels
            $enabledChannels = $this->preferenceRepo->getEnabledChannels($userId, $category);

            foreach ($enabledChannels as $channel) {
                if ($channel !== 'inapp' && ! in_array($channel, $channels)) {
                    $channels[] = $channel;
                }
            }
        }

        return array_unique($channels);
    }
}
