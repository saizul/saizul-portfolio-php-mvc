<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Mailer — thin PHP mail() wrapper.
 * For production reliability, swap the send() body for PHPMailer/SMTP.
 * All methods return bool (sent/failed).
 */
class Mailer
{
    private string $from;
    private string $fromName;

    public function __construct()
    {
        $this->from     = $_ENV['MAIL_FROM']      ?? 'noreply@saizulamin.com';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? config('app.name', 'Saizul Amin');
    }

    // ------------------------------------------------------------------
    // Send password reset email
    // ------------------------------------------------------------------

    public function sendPasswordReset(string $toEmail, string $toName, string $resetUrl): bool
    {
        $subject = 'Reset your password — ' . config('app.name');

        $body = $this->template('Password Reset Request', [
            "Hi {$toName},",
            "We received a request to reset the password for your admin account.",
            "Click the button below to set a new password. This link expires in <strong>60 minutes</strong>.",
            "<a href=\"{$resetUrl}\" style=\"display:inline-block;background:#fff;color:#000;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:700;margin:16px 0\">Reset Password</a>",
            "If you did not request a password reset, ignore this email — your account is safe.",
            "Reset link: <a href=\"{$resetUrl}\">{$resetUrl}</a>",
        ]);

        return $this->send($toEmail, $subject, $body);
    }

    // ------------------------------------------------------------------
    // Internal send
    // ------------------------------------------------------------------

    private function send(string $to, string $subject, string $htmlBody): bool
    {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->from}>\r\n";
        $headers .= "Reply-To: {$this->from}\r\n";
        $headers .= "X-Mailer: PHP/" . PHP_VERSION . "\r\n";

        $result = @mail($to, $subject, $htmlBody, $headers);

        if (!$result) {
            error_log("[Mailer] Failed to send to {$to} — subject: {$subject}");
        }

        return $result;
    }

    // ------------------------------------------------------------------
    // HTML email template
    // ------------------------------------------------------------------

    private function template(string $title, array $lines): string
    {
        $content = implode('</p><p style="margin:0 0 16px">', $lines);
        $name    = htmlspecialchars(config('app.name'), ENT_QUOTES, 'UTF-8');
        $year    = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>{$title}</title></head>
<body style="margin:0;padding:0;background:#0a0a0a;font-family:system-ui,sans-serif">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr><td align="center" style="padding:48px 16px">
      <table width="560" cellpadding="0" cellspacing="0"
             style="background:#111;border-radius:12px;overflow:hidden">
        <!-- Header -->
        <tr>
          <td style="background:#fff;color:#000;padding:24px 32px;font-weight:800;font-size:18px">
            {$name}
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td style="padding:32px;color:#e5e5e5;font-size:15px;line-height:1.6">
            <p style="margin:0 0 16px">{$content}</p>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td style="padding:24px 32px;border-top:1px solid #222;color:#666;font-size:12px">
            © {$year} {$name} · This email was sent from an automated system.
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
    }
}
