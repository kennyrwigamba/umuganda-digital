<?php
namespace UmugandaDigital\Repositories;

use mysqli;

/**
 * PreferenceRepository
 * Handles user notification preferences
 */
class PreferenceRepository
{
    private mysqli $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get user notification preferences
     */
    public function getUserPreferences(int $userId): ?array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM user_notification_preferences WHERE user_id = ?"
        );

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result      = $stmt->get_result();
        $preferences = $result->fetch_assoc();
        $stmt->close();

        return $preferences;
    }

    /**
     * Create default preferences for user if not exists
     */
    public function ensureUserPreferences(int $userId): array
    {
        $preferences = $this->getUserPreferences($userId);

        if (! $preferences) {
            $stmt = $this->connection->prepare(
                "INSERT INTO user_notification_preferences (user_id) VALUES (?)"
            );
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->close();

            $preferences = $this->getUserPreferences($userId);
        }

        return $preferences;
    }

    /**
     * Update user notification preferences
     */
    public function updateUserPreferences(int $userId, array $preferences): bool
    {
        // Whitelist allowed preference keys
        $allowedKeys = [
            'attendance_email', 'attendance_push', 'attendance_inapp',
            'event_email', 'event_push', 'event_inapp',
            'fine_email', 'fine_push', 'fine_inapp',
            'payment_email', 'payment_push', 'payment_inapp',
            'announcement_email', 'announcement_push', 'announcement_inapp',
            'system_email', 'system_push', 'system_inapp',
            'report_email', 'report_push', 'report_inapp',
            'other_email', 'other_push', 'other_inapp',
        ];

        $updateFields = [];
        $values       = [];
        $types        = '';

        foreach ($preferences as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $updateFields[] = "$key = ?";
                $values[]       = (int) $value; // Convert to 0 or 1
                $types .= 'i';
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $values[] = $userId;
        $types .= 'i';

        $stmt = $this->connection->prepare(
            "UPDATE user_notification_preferences
             SET " . implode(', ', $updateFields) . "
             WHERE user_id = ?"
        );

        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Get enabled channels for user and category
     */
    public function getEnabledChannels(int $userId, string $category): array
    {
        $preferences     = $this->ensureUserPreferences($userId);
        $enabledChannels = [];

        $channels = ['email', 'push', 'inapp'];

        foreach ($channels as $channel) {
            $key = $category . '_' . $channel;
            if (isset($preferences[$key]) && $preferences[$key]) {
                $enabledChannels[] = $channel;
            }
        }

        return $enabledChannels;
    }

    /**
     * Check if specific channel is enabled for user and category
     */
    public function isChannelEnabled(int $userId, string $category, string $channel): bool
    {
        $preferences = $this->ensureUserPreferences($userId);
        $key         = $category . '_' . $channel;

        return isset($preferences[$key]) && $preferences[$key];
    }
}
