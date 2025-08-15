<?php
namespace UmugandaDigital\Channels;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * EmailChannel
 * Handles email notifications using PHPMailer
 */
class EmailChannel
{
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUsername;
    private string $smtpPassword;
    private string $fromEmail;
    private string $fromName;
    private bool $smtpAuth;
    private string $smtpSecure;

    public function __construct()
    {
        // Load environment variables from .env file if not already loaded
        if (! isset($_ENV['SMTP_HOST'])) {
            $envPath = __DIR__ . '/../helpers/env.php';
            if (file_exists($envPath)) {
                require_once $envPath;
            }
        }

        // Load SMTP configuration from environment or defaults
        $this->smtpHost     = $_ENV['SMTP_HOST'] ?? 'localhost';
        $this->smtpPort     = (int) ($_ENV['SMTP_PORT'] ?? 587);
        $this->smtpUsername = $_ENV['SMTP_USERNAME'] ?? '';
        $this->smtpPassword = $_ENV['SMTP_PASSWORD'] ?? '';
        $this->fromEmail    = $_ENV['SMTP_FROM_EMAIL'] ?? 'ludiflextutorials@gmail.com';
        $this->fromName     = $_ENV['SMTP_FROM_NAME'] ?? 'Umuganda Digital';
        $this->smtpAuth     = ! empty($this->smtpUsername);
        $this->smtpSecure   = $_ENV['SMTP_SECURE'] ?? 'tls';
    }

    /**
     * Send email notification
     */
    public function send(array $notification, array $channelRow): array
    {
        try {
            // Get user email address
            $userEmail = $this->getUserEmail($notification['user_id']);
            if (! $userEmail) {
                return [
                    'success' => false,
                    'error'   => 'User email address not found',
                ];
            }

            // Create PHPMailer instance
            $mail = new PHPMailer(true);

            // Configure SMTP
            $this->configureSMTP($mail);

            // Set recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($userEmail);

            // Set email content
            $mail->isHTML(true);
            $mail->Subject = $this->formatSubject($notification);
            $mail->Body    = $this->formatBody($notification);
            $mail->AltBody = strip_tags($mail->Body);

            // Send email
            $result = $mail->send();

            return [
                'success' => true,
                'error'   => null,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error'   => 'Email send failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Configure SMTP settings for PHPMailer
     */
    private function configureSMTP(PHPMailer $mail): void
    {
        $mail->isSMTP();
        $mail->Host     = $this->smtpHost;
        $mail->SMTPAuth = $this->smtpAuth;

        if ($this->smtpAuth) {
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
        }

        $mail->SMTPSecure = $this->smtpSecure === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $this->smtpPort;

        // Disable debug output to prevent JSON response corruption
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
    }

    /**
     * Get user email address from database
     */
    private function getUserEmail(int $userId): ?string
    {
        require_once __DIR__ . '/../../config/db.php';
        $database   = new \Database();
        $connection = $database->getConnection();

        $stmt = $connection->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        return $user['email'] ?? null;
    }

    /**
     * Format email subject based on notification type
     */
    private function formatSubject(array $notification): string
    {
        $typeSubjects = [
            'user_registered'     => 'Welcome to Umuganda Digital Platform',
            'attendance_recorded' => 'Attendance Recorded - Umuganda Digital',
            'event_reminder'      => 'Upcoming Event Reminder - Umuganda Digital',
            'event_cancelled'     => 'Event Cancelled - Umuganda Digital',
            'fine_issued'         => 'Fine Notification - Umuganda Digital',
            'payment_received'    => 'Payment Confirmation - Umuganda Digital',
            'announcement'        => 'Community Announcement - Umuganda Digital',
            'emergency_alert'     => 'URGENT: Emergency Alert - Umuganda Digital',
            'suspicious_login'    => 'Security Alert - Umuganda Digital',
            'system_maintenance'  => 'System Maintenance Notice - Umuganda Digital',
            'report_generated'    => 'Report Available - Umuganda Digital',
        ];

        return $typeSubjects[$notification['type']] ?? $notification['title'] . ' - Umuganda Digital';
    }

    /**
     * Format email body with proper HTML template
     */
    private function formatBody(array $notification): string
    {
        $data = json_decode($notification['data'], true) ?? [];

        $template = $this->getEmailTemplate($notification['type']);

        // Replace placeholders in template
        $body = str_replace([
            '{{title}}',
            '{{body}}',
            '{{type}}',
            '{{category}}',
            '{{created_at}}',
            '{{platform_name}}',
            '{{platform_url}}',
        ], [
            htmlspecialchars($notification['title']),
            nl2br(htmlspecialchars($notification['body'])),
            htmlspecialchars($notification['type']),
            htmlspecialchars($notification['category']),
            $notification['created_at'],
            'Umuganda Digital',
            $_ENV['APP_URL'] ?? 'https://umuganda.rw',
        ], $template);

        // Add any notification-specific data
        foreach ($data as $key => $value) {
            $body = str_replace('{{' . $key . '}}', htmlspecialchars((string) $value), $body);
        }

        return $body;
    }

    /**
     * Get email template for notification type
     */
    private function getEmailTemplate(string $type): string
    {
        // Define templates for different notification types
        $templates = [
            'user_registered'     => $this->getRegistrationTemplate(),
            'attendance_recorded' => $this->getAttendanceTemplate(),
            'event_reminder'      => $this->getEventReminderTemplate(),
            'fine_issued'         => $this->getFineTemplate(),
            'emergency_alert'     => $this->getEmergencyTemplate(),
            'default'             => $this->getDefaultTemplate(),
        ];

        return $templates[$type] ?? $templates['default'];
    }

    /**
     * Default email template
     */
    private function getDefaultTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{title}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #2E8B57; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px; }
        .content { padding: 20px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9em; color: #666; }
        .btn { display: inline-block; padding: 12px 25px; background: #2E8B57; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{platform_name}}</h1>
        </div>
        <div class="content">
            <h2>{{title}}</h2>
            <p>{{body}}</p>
            <p><strong>Category:</strong> {{category}}</p>
            <p><strong>Date:</strong> {{created_at}}</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {{platform_name}}.</p>
            <p>Please do not reply to this email.</p>
            <p><a href="{{platform_url}}">Visit {{platform_name}}</a></p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Attendance notification template
     */
    private function getAttendanceTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{title}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #2E8B57; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px; }
        .content { padding: 20px 0; }
        .attendance-badge { display: inline-block; padding: 8px 16px; background: #28a745; color: white; border-radius: 20px; font-weight: bold; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{platform_name}}</h1>
            <p>Attendance Confirmation</p>
        </div>
        <div class="content">
            <h2>{{title}}</h2>
            <div class="attendance-badge">✓ PRESENT</div>
            <p>{{body}}</p>
            <p><strong>Event Date:</strong> {{created_at}}</p>
        </div>
        <div class="footer">
            <p>This is an automated attendance confirmation from {{platform_name}}.</p>
            <p><a href="{{platform_url}}">View your attendance history</a></p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Event reminder template
     */
    private function getEventReminderTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{title}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px; }
        .content { padding: 20px 0; }
        .event-details { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{platform_name}}</h1>
            <p>📅 Event Reminder</p>
        </div>
        <div class="content">
            <h2>{{title}}</h2>
            <p>{{body}}</p>
            <div class="event-details">
                <p><strong>Event Date:</strong> {{event_date}}</p>
                <p><strong>Event Time:</strong> {{event_time}}</p>
                <p><strong>Location:</strong> {{location}}</p>
            </div>
        </div>
        <div class="footer">
            <p>Don\'t forget to attend this important community event!</p>
            <p><a href="{{platform_url}}">View event details</a></p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Fine notification template
     */
    private function getFineTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{title}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px; }
        .content { padding: 20px 0; }
        .fine-amount { font-size: 1.5em; font-weight: bold; color: #dc3545; text-align: center; margin: 20px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{platform_name}}</h1>
            <p>💳 Fine Notification</p>
        </div>
        <div class="content">
            <h2>{{title}}</h2>
            <p>{{body}}</p>
            <div class="fine-amount">
                Amount: {{amount}} RWF
            </div>
            <p><strong>Reason:</strong> {{reason}}</p>
            <p><strong>Due Date:</strong> {{due_date}}</p>
        </div>
        <div class="footer">
            <p>Please settle this fine promptly to avoid additional charges.</p>
            <p><a href="{{platform_url}}">Pay online now</a></p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Emergency alert template
     */
    private function getEmergencyTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{title}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #fff3cd; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 3px solid #ffc107; }
        .header { background: #ffc107; color: #333; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px; }
        .content { padding: 20px 0; }
        .alert-banner { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 5px solid #dc3545; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{platform_name}}</h1>
            <p>🚨 EMERGENCY ALERT</p>
        </div>
        <div class="content">
            <div class="alert-banner">
                <h2>{{title}}</h2>
            </div>
            <p><strong>{{body}}</strong></p>
            <p><strong>Time:</strong> {{created_at}}</p>
            <p><strong>Action Required:</strong> {{action_required}}</p>
        </div>
        <div class="footer">
            <p>This is an urgent community alert. Please take immediate action as required.</p>
            <p><a href="{{platform_url}}">View latest updates</a></p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Registration welcome email template
     */
    private function getRegistrationTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{platform_name}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #2E8B57, #3CB371); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px; }
        .welcome-icon { font-size: 48px; margin-bottom: 10px; }
        .content { padding: 20px 0; }
        .welcome-message { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #2E8B57; }
        .btn { display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #2E8B57, #3CB371); color: white; text-decoration: none; border-radius: 25px; margin: 20px 0; text-align: center; font-weight: bold; }
        .features { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .feature-item { margin: 10px 0; padding-left: 20px; position: relative; }
        .feature-item:before { content: "✓"; position: absolute; left: 0; color: #2E8B57; font-weight: bold; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9em; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="welcome-icon">🎉</div>
            <h1>Welcome to {{platform_name}}!</h1>
            <p>Your account has been successfully created</p>
        </div>
        <div class="content">
            <div class="welcome-message">
                <h2>Hello {{user_name}}!</h2>
                <p>Thank you for joining the {{platform_name}} community. Your account has been successfully created and you can now participate in all community activities.</p>
            </div>

            <h3>What you can do now:</h3>
            <div class="features">
                <div class="feature-item">Join upcoming Umuganda events</div>
                <div class="feature-item">Check attendance records</div>
                <div class="feature-item">View community notices</div>
                <div class="feature-item">Update your profile information</div>
                <div class="feature-item">Receive important community alerts</div>
            </div>

            <div style="text-align: center;">
                <a href="{{platform_url}}" class="btn">Get Started Now</a>
            </div>

            <p><strong>Account Details:</strong></p>
            <ul>
                <li><strong>Email:</strong> {{user_email}}</li>
                <li><strong>Registration Date:</strong> {{created_at}}</li>
                <li><strong>User ID:</strong> {{user_id}}</li>
            </ul>
        </div>
        <div class="footer">
            <p>Welcome to the community!</p>
            <p>If you have any questions, please contact our support team.</p>
            <p><a href="{{platform_url}}">Visit {{platform_name}}</a></p>
        </div>
    </div>
</body>
</html>';
    }
}
