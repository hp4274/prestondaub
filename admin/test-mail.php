<?php
/**
 * Test Script for PHPMailer with Gmail SMTP
 * Usage (CLI): php admin/test-mail.php [recipient@example.com]
 * Usage (Web): http://yourdomain/admin/test-mail.php?to=recipient@example.com
 */

require_once __DIR__ . '/includes/Mailer.php';

// Detect if running from command line or web browser
$isCli = (php_sapi_name() === 'cli');
if (!$isCli) {
    header('Content-Type: text/plain; charset=utf-8');
}

$config = require __DIR__ . '/config/mail.php';
$recipient = $argv[1] ?? $_GET['to'] ?? $config['admin_email'] ?? 'harshlpatel.4274@gmail.com';

echo "========================================================\n";
echo "Testing PHPMailer via Gmail SMTP\n";
echo "========================================================\n";
echo "Driver:     " . $config['driver'] . "\n";
echo "Host:       " . $config['host'] . ":" . $config['port'] . "\n";
echo "Username:   " . $config['username'] . "\n";
echo "Encryption: " . strtoupper($config['encryption']) . "\n";
echo "From:       " . $config['from_email'] . "\n";
echo "To:         " . $recipient . "\n";
echo "--------------------------------------------------------\n";

if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "ERROR: PHPMailer class not found!\n\n";
    echo "Please install PHPMailer by running:\n";
    echo "  composer require phpmailer/phpmailer\n\n";
    echo "Or download PHPMailer manually and extract its 'src' folder into:\n";
    echo "  c:/xampp/htdocs/prestondaub/admin/includes/PHPMailer/src/\n";
    exit(1);
}

try {
    $mailer = new Mailer();
    $subject = "✨ Preston Daub & Co. | SMTP Verification Success (" . date('M j, Y') . ")";
    
    $innerContent = <<<CONTENT
<div style="margin-bottom: 24px;">
  <p style="margin: 0 0 12px 0; font-size: 16px; color: #1E293B; line-height: 1.6;">
    Greetings <strong>Harsh</strong>,
  </p>
  <p style="margin: 0 0 16px 0; font-size: 15px; color: #475569; line-height: 1.6;">
    Your custom <strong>PHPMailer + Gmail SMTP</strong> pipeline has been successfully configured and verified for the <span style="color: #0A1A3B; font-weight: 700;">Preston Daub &amp; Co.</span> web application.
  </p>
  <div style="background-color: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 8px; padding: 16px; color: #065F46; font-size: 14px; font-weight: 600; display: flex; align-items: center;">
    🎉 All automated lead notifications and client inquiry alerts will now render with this exact luxury brand styling.
  </div>
</div>

<!-- System Status Table -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #0A1A3B; color: #FFFFFF;">
      <th colspan="2" style="padding: 12px 16px; text-align: left; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">
        SMTP Connection Diagnostic Summary
      </th>
    </tr>
  </thead>
  <tbody>
    <tr style="border-bottom: 1px solid #E2E8F0; background-color: #F8FAFC;">
      <td style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #475569; width: 40%;">Mail Driver</td>
      <td style="padding: 14px 16px; font-size: 14px; color: #0F172A; font-weight: 600;">{$config['driver']} (SMTP Authentication)</td>
    </tr>
    <tr style="border-bottom: 1px solid #E2E8F0; background-color: #FFFFFF;">
      <td style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #475569;">SMTP Gateway Host</td>
      <td style="padding: 14px 16px; font-size: 14px; color: #0F172A;">{$config['host']}:{$config['port']}</td>
    </tr>
    <tr style="border-bottom: 1px solid #E2E8F0; background-color: #F8FAFC;">
      <td style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #475569;">Security Encryption</td>
      <td style="padding: 14px 16px; font-size: 14px; color: #0F172A;"><span style="background: #E0F2FE; color: #0369A1; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 700;">STARTTLS / TLS</span></td>
    </tr>
    <tr style="border-bottom: 1px solid #E2E8F0; background-color: #FFFFFF;">
      <td style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #475569;">Authenticated Account</td>
      <td style="padding: 14px 16px; font-size: 14px; color: #0F172A; font-family: monospace;">{$config['username']}</td>
    </tr>
    <tr style="background-color: #F8FAFC;">
      <td style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #475569;">Status &amp; Integrity</td>
      <td style="padding: 14px 16px; font-size: 14px; color: #10B981; font-weight: 700;">&check; Operational &amp; Secure</td>
    </tr>
  </tbody>
</table>

<div style="background: #F1F5F9; border-radius: 8px; padding: 16px; text-align: center;">
  <p style="margin: 0 0 8px 0; font-size: 13px; color: #334155; font-weight: 600;">Ready to test real web form submissions?</p>
  <a href="https://prestondaub.com/applynow.html" style="display: inline-block; background-color: #C0392B; color: #FFFFFF; font-size: 13px; font-weight: 700; text-decoration: none; padding: 10px 20px; border-radius: 20px;">
    Visit Online Financing Form &rarr;
  </a>
</div>
CONTENT;

    $body = $mailer->getBrandEmailTemplate(
        "SMTP Configuration Verified",
        "Your custom email notification system is fully active and ready.",
        $innerContent,
        "System Verification"
    );

    echo "Connecting to smtp.gmail.com...\n";
    $sent = $mailer->send($recipient, $subject, $body);

    if ($sent) {
        echo "\nSUCCESS: Test email has been sent to {$recipient}!\n";
        echo "Check your inbox (or spam folder) to verify receipt.\n";
    }
} catch (Exception $e) {
    echo "\nFAILED: " . $e->getMessage() . "\n\n";
    echo "Troubleshooting Tips for Gmail SMTP:\n";
    echo "1. Verify your 16-character Google App Password (tslh mqrf zfgt lgqj) is correct.\n";
    echo "2. Ensure 2-Step Verification is enabled on your Google Account.\n";
    echo "3. If running on AWS EC2 or cloud hosting, ensure outbound port 587 is not blocked by firewall rules.\n";
}
?>
