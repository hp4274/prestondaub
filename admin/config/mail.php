<?php
/**
 * PHPMailer SMTP Configuration for Gmail
 */

return [
    // Driver: 'smtp'
    'driver'      => 'smtp',

    // Gmail SMTP Host & Port settings
    'host'        => 'smtp.gmail.com',
    'port'        => 587,              // 587 for TLS, or 465 for SSL/PHPMailer::ENCRYPTION_SMTPS
    'smtp_auth'   => true,
    'username'    => 'harshlpatel.4274@gmail.com',
    // Remove spaces from App Password automatically just in case, but stored as provided
    'password'    => str_replace(' ', '', 'tslh mqrf zfgt lgqj'),
    'encryption'  => 'tls',            // 'tls' for port 587, 'ssl' for port 465

    // Sender Identity (Gmail requires From to match the authenticated account or verified aliases)
    'from_email'  => 'harshlpatel.4274@gmail.com',
    'from_name'   => 'Preston Daub & Co. Website',

    // Admin / Notification Recipient (where form submissions are sent)
    'admin_email' => 'harshlpatel.4274@gmail.com',
    'admin_name'  => 'Site Administrator',
];
?>
