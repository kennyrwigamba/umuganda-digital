<?php
/**
 * Notification Worker Script
 * Processes pending notification channels
 *
 * Run via: php public/cron/send_notifications.php
 * Or schedule via Task Scheduler every minute
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/smtp.php';
require_once __DIR__ . '/../../config/vapid.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use UmugandaDigital\Channels\EmailChannel;
use UmugandaDigital\Channels\InAppChannel;
use UmugandaDigital\Channels\PushChannel;
use UmugandaDigital\Repositories\NotificationRepository;

class NotificationWorker
{
    private $connection;
    private $notificationRepo;
    private $lockFile;

    private const MAX_RUNTIME = 45; // seconds
    private const BATCH_SIZE  = 100;

    public function __construct()
    {
        global $db;
        $this->connection       = $db->getConnection();
        $this->notificationRepo = new NotificationRepository($this->connection);
        $this->lockFile         = __DIR__ . '/notifications.lock';
    }

    /**
     * Main worker process
     */
    public function run(): void
    {
        $startTime = time();

        echo "[" . date('Y-m-d H:i:s') . "] Starting notification worker...\n";

        if (! $this->acquireLock()) {
            echo "[" . date('Y-m-d H:i:s') . "] Another worker is already running. Exiting.\n";
            return;
        }

        try {
            $totalProcessed = 0;

            while ((time() - $startTime) < self::MAX_RUNTIME) {
                $pendingChannels = $this->notificationRepo->getPendingChannels(self::BATCH_SIZE);

                if (empty($pendingChannels)) {
                    echo "[" . date('Y-m-d H:i:s') . "] No pending channels. Worker idle.\n";
                    break;
                }

                echo "[" . date('Y-m-d H:i:s') . "] Processing " . count($pendingChannels) . " channels...\n";

                foreach ($pendingChannels as $channelData) {
                    $this->processChannel($channelData);
                    $totalProcessed++;
                }

                                // Small delay between batches
                usleep(100000); // 0.1 second
            }

            echo "[" . date('Y-m-d H:i:s') . "] Worker completed. Processed $totalProcessed channels.\n";

        } finally {
            $this->releaseLock();
        }
    }

    /**
     * Process a single notification channel
     */
    private function processChannel(array $channelData): void
    {
        $channelId      = $channelData['id'];
        $channel        = $channelData['channel'];
        $notificationId = $channelData['notification_id'];

        echo "[" . date('Y-m-d H:i:s') . "] Processing channel $channelId ($channel) for notification $notificationId\n";

        try {
            $result = $this->sendChannel($channel, $channelData);

            $status = $result['success'] ? 'sent' : 'failed';
            $error  = $result['error'];

            // Update channel status
            $this->notificationRepo->updateChannelStatus($channelId, $status, $error);

            // Update parent notification status
            $this->notificationRepo->updateNotificationStatus($notificationId);

            if ($result['success']) {
                echo "[" . date('Y-m-d H:i:s') . "] ✓ Channel $channelId sent successfully\n";
            } else {
                echo "[" . date('Y-m-d H:i:s') . "] ✗ Channel $channelId failed: " . ($error ?: 'Unknown error') . "\n";
            }

        } catch (Exception $e) {
            echo "[" . date('Y-m-d H:i:s') . "] ✗ Channel $channelId exception: " . $e->getMessage() . "\n";

            $this->notificationRepo->updateChannelStatus($channelId, 'failed', $e->getMessage());
            $this->notificationRepo->updateNotificationStatus($notificationId);
        }
    }

    /**
     * Send notification via specific channel
     */
    private function sendChannel(string $channel, array $channelData): array
    {
        switch ($channel) {
            case 'inapp':
                $sender = new InAppChannel();
                return $sender->send($channelData, $channelData);

            case 'email':
                $sender = new EmailChannel();
                return $sender->send($channelData, $channelData);

            case 'push':
                $sender = new PushChannel();
                return $sender->send($channelData, $channelData);

            default:
                return [
                    'success' => false,
                    'error'   => "Unknown channel: $channel",
                ];
        }
    }

    /**
     * Acquire file lock to prevent multiple workers
     */
    private function acquireLock(): bool
    {
        if (file_exists($this->lockFile)) {
            // Check if lock is stale (older than 5 minutes)
            if (time() - filemtime($this->lockFile) > 300) {
                unlink($this->lockFile);
            } else {
                return false;
            }
        }

        return file_put_contents($this->lockFile, getmypid()) !== false;
    }

    /**
     * Release file lock
     */
    private function releaseLock(): void
    {
        if (file_exists($this->lockFile)) {
            unlink($this->lockFile);
        }
    }
}

// Run the worker
try {
    $worker = new NotificationWorker();
    $worker->run();
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Worker fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
