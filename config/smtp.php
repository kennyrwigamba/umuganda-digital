<?php
/**
 * SMTP Configuration for Umuganda Digital
 *
 * For development/testing, you can use:
 * - Mailtrap (https://mailtrap.io)
 * - Mailhog (local SMTP server)
 * - Gmail SMTP (with app password)
 *
 * For production, use a reliable service like:
 * - SendGrid
 * - Mailgun
 * - Amazon SES
 * - Postmark
 */

// Development SMTP Settings (Mailtrap example)
// Sign up at https://mailtrap.io to get free test credentials
// $_ENV['SMTP_HOST']       = $_ENV['SMTP_HOST'] ?? 'sandbox.smtp.mailtrap.io';
// $_ENV['SMTP_PORT']       = $_ENV['SMTP_PORT'] ?? '2525';
// $_ENV['SMTP_USERNAME']   = $_ENV['SMTP_USERNAME'] ?? 'your_mailtrap_username'; // Replace with actual Mailtrap username
// $_ENV['SMTP_PASSWORD']   = $_ENV['SMTP_PASSWORD'] ?? 'your_mailtrap_password'; // Replace with actual Mailtrap password
// $_ENV['SMTP_SECURE']     = $_ENV['SMTP_SECURE'] ?? 'tls';
// $_ENV['SMTP_FROM_EMAIL'] = $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@umuganda.rw';
// $_ENV['SMTP_FROM_NAME']  = $_ENV['SMTP_FROM_NAME'] ?? 'Umuganda Digital';
$_ENV['APP_URL']         = $_ENV['APP_URL'] ?? 'https://localhost';
$_ENV['APP_ENV']         = $_ENV['APP_ENV'] ?? 'development';


$_ENV['SMTP_HOST'] = 'smtp.gmail.com';
$_ENV['SMTP_PORT'] = '587';
$_ENV['SMTP_USERNAME'] = 'ludiflextutorials@gmail.com';
$_ENV['SMTP_PASSWORD'] = 'vbbm skej uwid jaym'; // Use App Password, not regular password
$_ENV['SMTP_SECURE'] = 'tls';


/* 
 * SendGrid SMTP Configuration Example:
 * $_ENV['SMTP_HOST'] = 'smtp.sendgrid.net';
 * $_ENV['SMTP_PORT'] = '587';
 * $_ENV['SMTP_USERNAME'] = 'apikey';
 * $_ENV['SMTP_PASSWORD'] = 'your_sendgrid_api_key';
 * $_ENV['SMTP_SECURE'] = 'tls';
 */
