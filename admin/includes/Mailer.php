<?php
/**
 * Mailer Helper Class using PHPMailer with Gmail SMTP
 */

// Attempt to load PHPMailer via Composer autoloader or manual inclusion
$composerPaths = [
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php'
];

$autoloaderFound = false;
foreach ($composerPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloaderFound = true;
        break;
    }
}

// If Composer autoloader not found, check for manual PHPMailer directory inside includes/PHPMailer/
if (!$autoloaderFound && file_exists(__DIR__ . '/PHPMailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $config;

    public function __construct() {
        $configFile = __DIR__ . '/../config/mail.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        } else {
            $this->config = [
                'driver'     => 'smtp',
                'host'       => 'smtp.gmail.com',
                'port'       => 587,
                'smtp_auth'  => true,
                'username'   => 'harshlpatel.4274@gmail.com',
                'password'   => str_replace(' ', '', 'tslh mqrf zfgt lgqj'),
                'encryption' => 'tls',
                'from_email' => 'harshlpatel.4274@gmail.com',
                'from_name'  => 'Preston Daub & Co. Website',
                'admin_email'=> 'harshlpatel.4274@gmail.com'
            ];
        }
    }

    /**
     * Send an email using PHPMailer
     *
     * @param string|array $to Recipient email address or ['email' => 'a@b.com', 'name' => 'Name']
     * @param string $subject Email subject
     * @param string $htmlBody HTML content of the email
     * @param string|null $plainTextBody Optional plain text fallback
     * @param array $attachments Optional array of file paths to attach
     * @return bool True if sent successfully
     * @throws Exception if sending fails
     */
    public function send($to, string $subject, string $htmlBody, ?string $plainTextBody = null, array $attachments = []): bool {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            throw new Exception("PHPMailer is not installed or could not be loaded. Run 'composer require phpmailer/phpmailer' in your project root, or download PHPMailer into admin/includes/PHPMailer.");
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            if (($this->config['driver'] ?? 'smtp') === 'smtp') {
                $mail->isSMTP();
                $mail->Host       = $this->config['host'] ?? 'smtp.gmail.com';
                $mail->SMTPAuth   = (bool)($this->config['smtp_auth'] ?? true);
                $mail->Username   = $this->config['username'] ?? '';
                $mail->Password   = $this->config['password'] ?? '';
                $mail->Port       = (int)($this->config['port'] ?? 587);

                $encryption = strtolower($this->config['encryption'] ?? 'tls');
                if ($encryption === 'ssl' || $mail->Port === 465) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($encryption === 'tls' || $mail->Port === 587) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } else {
                    $mail->SMTPSecure = false;
                    $mail->SMTPAutoTLS = false;
                }
            } elseif (($this->config['driver'] ?? '') === 'sendmail') {
                $mail->isSendmail();
            } else {
                $mail->isMail();
            }

            $mail->CharSet = 'UTF-8';

            // Sender settings
            $fromEmail = $this->config['from_email'] ?? 'harshlpatel.4274@gmail.com';
            $fromName  = $this->config['from_name'] ?? 'Preston Daub & Co.';
            $mail->setFrom($fromEmail, $fromName);

            // Add Recipient(s)
            if (is_array($to)) {
                if (isset($to['email'])) {
                    $mail->addAddress($to['email'], $to['name'] ?? '');
                } else {
                    foreach ($to as $recipient) {
                        if (is_array($recipient)) {
                            $mail->addAddress($recipient['email'], $recipient['name'] ?? '');
                        } else {
                            $mail->addAddress($recipient);
                        }
                    }
                }
            } else {
                $mail->addAddress($to);
            }

            // Set Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $plainTextBody ?? strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlBody));

            // Attachments
            foreach ($attachments as $filePath) {
                if (file_exists($filePath)) {
                    $mail->addAttachment($filePath);
                }
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}");
            throw new Exception("Mail sending failed: {$mail->ErrorInfo}");
        }
    }

    /**
     * Generate a beautiful, responsive HTML email template matching the Preston Daub website brand aesthetic.
     * Brand Colors: Midnight Blue (#0A1A3B), Crimson Accent (#C0392B), Clean Slate/White Cards (#FFFFFF / #F8FAFC).
     */
    public function getBrandEmailTemplate(string $title, string $subtitle, string $contentHtml, ?string $badgeText = null): string {
        $year = date('Y');
        $badgeHtml = $badgeText 
            ? "<span style='background: #C0392B; color: #ffffff; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; display: inline-block; margin-bottom: 12px;'>{$badgeText}</span>" 
            : "";

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F1F5F9; font-family: 'DM Sans', 'Kanit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #1E293B;">

<!-- Outer Background Wrapper -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F1F5F9; padding: 40px 15px;">
  <tr>
    <td align="center">
      <!-- Main Card Container (max 640px) -->
      <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 640px; background-color: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(10, 26, 59, 0.08); border: 1px solid #E2E8F0;">
        
        <!-- Top Crimson Brand Bar -->
        <tr>
          <td height="4" style="background: linear-gradient(90deg, #C0392B 0%, #E74C3C 50%, #0A1A3B 100%); line-height: 4px; font-size: 4px;">&nbsp;</td>
        </tr>

        <!-- Header Section (Deep Midnight Navy #0A1A3B) -->
        <tr>
          <td style="background-color: #0A1A3B; padding: 36px 32px; text-align: center; border-bottom: 3px solid #1E293B;">
            <!-- Brand Logo Text / Monogram -->
            <div style="font-size: 26px; font-weight: 800; color: #FFFFFF; letter-spacing: 3px; text-transform: uppercase; margin: 0 0 4px 0; font-family: 'Space Grotesk', Arial, sans-serif;">
              PRESTON <span style="color: #E2E8F0; font-weight: 300;">DAUB</span>
            </div>
            <div style="font-size: 11px; font-weight: 600; color: #94A3B8; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 24px;">
              Business Financing &bull; Prospera Advisory &bull; Sports Capital
            </div>
            
            {$badgeHtml}
            
            <h1 style="margin: 0; font-size: 24px; font-weight: 700; color: #FFFFFF; line-height: 1.3;">
              {$title}
            </h1>
            <p style="margin: 8px 0 0 0; font-size: 14px; color: #CBD5E1; line-height: 1.5;">
              {$subtitle}
            </p>
          </td>
        </tr>

        <!-- Body Content Section -->
        <tr>
          <td style="padding: 36px 32px; background-color: #FFFFFF;">
            {$contentHtml}
          </td>
        </tr>

        <!-- Call to Action / Website Footer Banner -->
        <tr>
          <td style="background-color: #F8FAFC; padding: 24px 32px; border-top: 1px solid #E2E8F0; text-align: center;">
            <p style="margin: 0 0 16px 0; font-size: 13px; color: #64748b;">
              Explore our comprehensive business financing, capital advisory, and wealth strategies.
            </p>
            <a href="https://prestondaub.com/" style="display: inline-block; background-color: #0A1A3B; color: #FFFFFF; font-size: 13px; font-weight: 700; text-decoration: none; padding: 12px 24px; border-radius: 26px; letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(10, 26, 59, 0.15);">
              Visit Preston Daub &amp; Co. &rarr;
            </a>
          </td>
        </tr>

        <!-- Bottom Copyright / Footer -->
        <tr>
          <td style="background-color: #0F172A; padding: 24px 32px; text-align: center; font-size: 12px; color: #64748b; line-height: 1.6;">
            <p style="margin: 0; color: #94A3B8; font-weight: 600;">
              &copy; {$year} Preston Daub &amp; Co. All rights reserved.
            </p>
            <p style="margin: 6px 0 0 0;">
              Empowering Growth &bull; Elevating Performance &bull; Strategic Capital
            </p>
            <p style="margin: 6px 0 0 0; font-size: 11px; color: #475569;">
              This is an automated system notification sent from <span style="color: #CBD5E1;">prestondaub.com</span>.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
HTML;
    }

    /**
     * Helper to send notification alert when a form is submitted
     */
    public function sendAdminAlert(string $formType, array $formData): bool {
        $adminEmail = $this->config['admin_email'] ?? 'harshlpatel.4274@gmail.com';
        $formTitleClean = ucwords(str_replace('-', ' ', $formType));
        $subject = "New Submission: " . $formTitleClean . " (" . date('M j, Y') . ")";

        // Build data table rows with zebra striping
        $tableRows = "";
        $rowIndex = 0;
        $quickContactEmail = '';
        $quickContactPhone = '';
        $quickContactName = '';

        foreach ($formData as $key => $value) {
            if (in_array(strtolower($key), ['form_type', 'submit', 'action'])) continue;
            if (is_scalar($value) && trim((string)$value) !== '') {
                $cleanVal = trim((string)$value);
                if (strtolower($key) === 'email') $quickContactEmail = $cleanVal;
                if (in_array(strtolower($key), ['phone', 'phonenumber', 'phone_number'])) $quickContactPhone = $cleanVal;
                if (in_array(strtolower($key), ['name', 'fullname', 'first_name'])) $quickContactName = $cleanVal;

                $label = ucwords(str_replace('_', ' ', $key));
                $bgStyle = ($rowIndex % 2 === 0) ? "background-color: #F8FAFC;" : "background-color: #FFFFFF;";
                
                // Format multiline text nicely (like message or interests)
                if (strpos($cleanVal, "\n") !== false || strlen($cleanVal) > 80) {
                    $formattedVal = nl2br(htmlspecialchars($cleanVal));
                } else {
                    $formattedVal = htmlspecialchars($cleanVal);
                }

                $tableRows .= <<<ROW
<tr style="border-bottom: 1px solid #E2E8F0; {$bgStyle}">
  <td style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #475569; width: 38%; vertical-align: top; text-transform: capitalize;">
    {$label}
  </td>
  <td style="padding: 14px 16px; font-size: 14px; color: #0F172A; line-height: 1.5; vertical-align: top;">
    {$formattedVal}
  </td>
</tr>
ROW;
                $rowIndex++;
            }
        }

        // Build Quick Action bar if email/phone provided
        $quickActionBar = "";
        if ($quickContactEmail || $quickContactPhone) {
            $quickActionBar = "<div style='background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 16px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;'>";
            $quickActionBar .= "<div style='margin-bottom: 8px;'><strong style='color: #1E40AF; font-size: 13px; display: block;'>⚡ Quick Contact Actions:</strong>";
            if ($quickContactName) $quickActionBar .= "<span style='font-size: 14px; color: #1E293B;'>{$quickContactName}</span></div>";
            else $quickActionBar .= "</div>";
            
            $quickActionBar .= "<div style='margin-top: 8px;'>";
            if ($quickContactEmail) {
                $quickActionBar .= "<a href='mailto:{$quickContactEmail}?subject=Re: Your inquiry at Preston Daub' style='display: inline-block; background: #2563EB; color: #fff; text-decoration: none; font-size: 12px; font-weight: 700; padding: 8px 16px; border-radius: 6px; margin-right: 8px;'>Reply via Email &bull; {$quickContactEmail}</a>";
            }
            if ($quickContactPhone) {
                $quickActionBar .= "<a href='tel:{$quickContactPhone}' style='display: inline-block; background: #0F172A; color: #fff; text-decoration: none; font-size: 12px; font-weight: 700; padding: 8px 16px; border-radius: 6px;'>Call &bull; {$quickContactPhone}</a>";
            }
            $quickActionBar .= "</div></div>";
        }

        $contentHtml = <<<CONTENT
<div style="margin-bottom: 20px;">
  <p style="margin: 0 0 8px 0; font-size: 15px; color: #334155;">
    Hello <strong>Administrator</strong>,
  </p>
  <p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.6;">
    A visitor has just completed the <strong style="color: #0A1A3B;">{$formTitleClean}</strong> form on the Preston Daub website. Below are the complete submission details:
  </p>
</div>

{$quickActionBar}

<!-- Submission Details Table -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; margin-bottom: 12px;">
  <thead>
    <tr style="background-color: #0A1A3B; color: #FFFFFF;">
      <th colspan="2" style="padding: 12px 16px; text-align: left; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">
        Submission Data Record
      </th>
    </tr>
  </thead>
  <tbody>
    {$tableRows}
  </tbody>
</table>

<div style="font-size: 12px; color: #94A3B8; text-align: right; margin-top: 8px;">
  Received on: <strong>{date('l, F j, Y \a\t g:i A')}</strong>
</div>
CONTENT;

        $fullHtml = $this->getBrandEmailTemplate(
            $formTitleClean . " Application",
            "Submitted directly from your online web portal",
            $contentHtml,
            "New Website Lead"
        );

        return $this->send($adminEmail, $subject, $fullHtml);
    }

    /**
     * Helper to send confirmation / thank you email receipt to the user who submitted the form
     */
    public function sendUserConfirmation(string $formType, string $userEmail, string $userName = '', array $formData = []): bool {
        if (empty($userEmail) || !filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $formTitleClean = ucwords(str_replace('-', ' ', $formType));
        $subject = "Preston Daub & Co. | We received your " . $formTitleClean . " inquiry";
        $greetingName = trim($userName) !== '' ? htmlspecialchars(trim($userName)) : "Valued Client";

        // Build summary table rows of their submitted data
        $tableRows = "";
        $rowIndex = 0;
        foreach ($formData as $key => $value) {
            if (in_array(strtolower($key), ['form_type', 'submit', 'action', 'checkbox', 'privacy'])) continue;
            if (is_scalar($value) && trim((string)$value) !== '') {
                $cleanVal = trim((string)$value);
                $label = ucwords(str_replace('_', ' ', $key));
                $bgStyle = ($rowIndex % 2 === 0) ? "background-color: #F8FAFC;" : "background-color: #FFFFFF;";
                
                if (strpos($cleanVal, "\n") !== false || strlen($cleanVal) > 80) {
                    $formattedVal = nl2br(htmlspecialchars($cleanVal));
                } else {
                    $formattedVal = htmlspecialchars($cleanVal);
                }

                $tableRows .= <<<ROW
<tr style="border-bottom: 1px solid #E2E8F0; {$bgStyle}">
  <td style="padding: 12px 16px; font-size: 13px; font-weight: 700; color: #475569; width: 38%; vertical-align: top;">
    {$label}
  </td>
  <td style="padding: 12px 16px; font-size: 14px; color: #0F172A; line-height: 1.5; vertical-align: top;">
    {$formattedVal}
  </td>
</tr>
ROW;
                $rowIndex++;
            }
        }

        $summarySection = "";
        if (!empty($tableRows)) {
            $summarySection = <<<SUMMARY
<div style="margin-top: 28px; margin-bottom: 16px;">
  <h3 style="margin: 0 0 12px 0; font-size: 15px; font-weight: 700; color: #0A1A3B; border-left: 4px solid #C0392B; padding-left: 10px;">
    Your Application Summary
  </h3>
  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden;">
    <thead>
      <tr style="background-color: #1E293B; color: #FFFFFF;">
        <th colspan="2" style="padding: 10px 16px; text-align: left; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">
          Submitted on {date('M j, Y')}
        </th>
      </tr>
    </thead>
    <tbody>
      {$tableRows}
    </tbody>
  </table>
</div>
SUMMARY;
        }

        $contentHtml = <<<CONTENT
<div style="margin-bottom: 24px;">
  <p style="margin: 0 0 14px 0; font-size: 16px; color: #1E293B; line-height: 1.6;">
    Dear <strong>{$greetingName}</strong>,
  </p>
  <p style="margin: 0 0 16px 0; font-size: 15px; color: #475569; line-height: 1.6;">
    Thank you for contacting <strong style="color: #0A1A3B;">Preston Daub &amp; Co.</strong> We have successfully received your <strong>{$formTitleClean}</strong> application/inquiry and entered your details into our secure advisory workflow.
  </p>
</div>

<!-- What Happens Next Box -->
<div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
  <h3 style="margin: 0 0 16px 0; font-size: 15px; font-weight: 700; color: #0A1A3B; text-transform: uppercase; letter-spacing: 0.5px;">
    📋 What Happens Next?
  </h3>
  
  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin-bottom: 14px;">
    <tr>
      <td width="36" valign="top" style="padding-top: 1px; width: 36px; max-width: 36px;">
        <table border="0" cellpadding="0" cellspacing="0" width="26" height="26" style="border-collapse: separate; width: 26px; height: 26px; max-width: 26px; max-height: 26px;">
          <tr>
            <td align="center" valign="middle" width="26" height="26" style="width: 26px; height: 26px; background-color: #0A1A3B; color: #FFFFFF; font-size: 12px; font-weight: bold; line-height: 26px; text-align: center; border-radius: 13px; -webkit-border-radius: 13px; -moz-border-radius: 13px; mso-line-height-rule: exactly;">
              1
            </td>
          </tr>
        </table>
      </td>
      <td valign="top" style="font-size: 14px; color: #334155; line-height: 1.5;">
        <strong style="color: #0F172A;">Dedicated Profile Review:</strong> Our senior advisory desk reviews your financial profile, business scope, or capital requirements.
      </td>
    </tr>
  </table>

  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin-bottom: 14px;">
    <tr>
      <td width="36" valign="top" style="padding-top: 1px; width: 36px; max-width: 36px;">
        <table border="0" cellpadding="0" cellspacing="0" width="26" height="26" style="border-collapse: separate; width: 26px; height: 26px; max-width: 26px; max-height: 26px;">
          <tr>
            <td align="center" valign="middle" width="26" height="26" style="width: 26px; height: 26px; background-color: #0A1A3B; color: #FFFFFF; font-size: 12px; font-weight: bold; line-height: 26px; text-align: center; border-radius: 13px; -webkit-border-radius: 13px; -moz-border-radius: 13px; mso-line-height-rule: exactly;">
              2
            </td>
          </tr>
        </table>
      </td>
      <td valign="top" style="font-size: 14px; color: #334155; line-height: 1.5;">
        <strong style="color: #0F172A;">Strategic Alignment:</strong> We evaluate optimal financing structures, capital strategy, or advisory channels tailored specifically to your objectives.
      </td>
    </tr>
  </table>

  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
    <tr>
      <td width="36" valign="top" style="padding-top: 1px; width: 36px; max-width: 36px;">
        <table border="0" cellpadding="0" cellspacing="0" width="26" height="26" style="border-collapse: separate; width: 26px; height: 26px; max-width: 26px; max-height: 26px;">
          <tr>
            <td align="center" valign="middle" width="26" height="26" style="width: 26px; height: 26px; background-color: #C0392B; color: #FFFFFF; font-size: 12px; font-weight: bold; line-height: 26px; text-align: center; border-radius: 13px; -webkit-border-radius: 13px; -moz-border-radius: 13px; mso-line-height-rule: exactly;">
              3
            </td>
          </tr>
        </table>
      </td>
      <td valign="top" style="font-size: 14px; color: #334155; line-height: 1.5;">
        <strong style="color: #0F172A;">Personal Consultation:</strong> A dedicated capital advisor will reach out to you directly within <strong style="color: #C0392B;">24 business hours</strong> to discuss actionable next steps.
      </td>
    </tr>
  </table>
</div>

{$summarySection}

<div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 16px; margin-top: 24px; text-align: center;">
  <p style="margin: 0; font-size: 13px; color: #1E40AF; line-height: 1.5;">
    Have immediate questions or supplemental documents to share?<br>
    Simply reply to this email or contact our advisory desk directly at <strong style="color: #1D4ED8;">harshlpatel.4274@gmail.com</strong>.
  </p>
</div>
CONTENT;

        $fullHtml = $this->getBrandEmailTemplate(
            "Application Received",
            "Thank you for connecting with Preston Daub & Co.",
            $contentHtml,
            "Confirmation Receipt"
        );

        return $this->send($userEmail, $subject, $fullHtml);
    }
}
?>
