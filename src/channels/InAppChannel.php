<?php
namespace UmugandaDigital\Channels;

/**
 * InAppChannel
 * Handles in-app notifications (stored in database only)
 */
class InAppChannel
{
    /**
     * Send in-app notification
     * For in-app notifications, we just mark as sent immediately
     * since the notification is already stored in the database
     */
    public function send(array $notification, array $channelRow): array
    {
        // In-app notifications don't require external delivery
        // The notification is already in the database and will be shown in UI

        return [
            'success' => true,
            'error'   => null,
        ];
    }
}
