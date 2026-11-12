<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\SEO;
use App\Core\Database;
use App\Core\RateLimiter;
use App\Core\Validator;

class ContactController extends Controller
{
    /** Topics offered in the contact form's subject dropdown. */
    public const SUBJECTS = [
        'AI Project Development',
        'Full-Stack Development',
        'Technical Consulting',
        'Speaking / Writing',
        'Collaboration / Partnership',
        'Something else',
    ];

    /** Accepted submissions per client IP inside RATE_LIMIT_WINDOW. */
    public const RATE_LIMIT_MAX = 3;

    /** Rate-limit window in seconds (10 minutes). */
    public const RATE_LIMIT_WINDOW = 600;

    /** Submissions faster than this after the form was rendered are treated as bots. */
    public const MIN_FILL_SECONDS = 3;

    private const SESSION_FORM = 'contact_form';
    private const SESSION_RENDERED_AT = 'contact_form_rendered_at';

    /**
     * Validation rules for a contact submission.
     * Limits match the contact_submissions column sizes.
     *
     * @return array<string, string>
     */
    public static function rules(): array
    {
        return [
            'name'    => 'required|max:150|single_line',
            'email'   => 'required|email|max:150',
            'subject' => 'single_line|in:' . implode(',', self::SUBJECTS),
            'message' => 'required|min:10|max:5000',
        ];
    }

    public function index(): void
    {
        SEO::init('contact');

        $form = $_SESSION[self::SESSION_FORM] ?? [];
        unset($_SESSION[self::SESSION_FORM]);
        $_SESSION[self::SESSION_RENDERED_AT] = time();

        $this->render('pages.contact', [
            'flash'    => $this->getFlash(),
            'errors'   => $form['errors'] ?? [],
            'old'      => $form['old'] ?? [],
            'subjects' => self::SUBJECTS,
        ], 'main');
    }

    public function send(): void
    {
        $this->verifyCsrf();

        // Bot traps first: answer like a success so bots learn nothing.
        if ($this->looksLikeBot()) {
            $this->flash('success', 'Message sent! I will get back to you soon.');
            $this->redirect(url('/contact'));
        }

        $limiter = new RateLimiter();
        $key     = 'contact:' . $this->clientIp();

        if ($limiter->tooManyAttempts($key, self::RATE_LIMIT_MAX, self::RATE_LIMIT_WINDOW)) {
            $wait    = $limiter->availableIn($key, self::RATE_LIMIT_MAX, self::RATE_LIMIT_WINDOW);
            $minutes = max(1, (int) ceil($wait / 60));
            $this->flash('error', "You have sent several messages in a short time. Please try again in {$minutes} minute(s).");
            $this->redirect(url('/contact'));
        }

        $validator = Validator::make($_POST, self::rules());

        if ($validator->fails()) {
            $_SESSION[self::SESSION_FORM] = [
                'errors' => $validator->errors(),
                'old'    => $this->oldInput(),
            ];
            $this->flash('error', 'Please fix the errors and try again.');
            $this->redirect(url('/contact'));
        }

        $limiter->hit($key, self::RATE_LIMIT_WINDOW);

        $data = $validator->validated();

        // Store in DB (HTML-escaped, as before, so admin views stay safe)
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO contact_submissions (name, email, subject, message, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            sanitizeString($data['name']),
            $data['email'],
            $data['subject'] === '' ? null : sanitizeString($data['subject']),
            sanitizeString($data['message']),
            $this->clientIp(),
            mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
        ]);

        $this->notify($data);

        unset($_SESSION[self::SESSION_RENDERED_AT]);
        $this->flash('success', 'Message sent! I will get back to you soon.');
        $this->redirect(url('/contact'));
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Honeypot field filled in, or the form was posted implausibly fast.
     */
    private function looksLikeBot(): bool
    {
        if (!empty($_POST['website'])) {
            return true;
        }

        $renderedAt = $_SESSION[self::SESSION_RENDERED_AT] ?? null;
        return is_int($renderedAt) && (time() - $renderedAt) < self::MIN_FILL_SECONDS;
    }

    /**
     * Email the site owner. Only values that passed validation reach the
     * headers: From is our own address (so SPF/DMARC pass), the visitor goes
     * in Reply-To, and every header value is stripped of CR/LF.
     *
     * @param array<string, string> $data
     */
    private function notify(array $data): void
    {
        $to      = $_ENV['MAIL_TO'] ?? $_ENV['ADMIN_EMAIL'] ?? 'saizulamin@gmail.com';
        $from    = $_ENV['MAIL_FROM_ADDRESS'] ?? $_ENV['MAIL_FROM'] ?? 'noreply@saizulamin.com';
        $topic   = $data['subject'] !== '' ? $data['subject'] : 'General enquiry';
        $subject = self::headerSafe('New Contact: ' . $topic);
        $body    = "Name: {$data['name']}\nEmail: {$data['email']}\nSubject: {$topic}\n\n{$data['message']}";
        $headers = 'From: ' . self::headerSafe($from) . "\r\n"
                 . 'Reply-To: ' . self::headerSafe($data['email']) . "\r\n"
                 . "Content-Type: text/plain; charset=UTF-8";

        if (!@mail($to, $subject, $body, $headers)) {
            error_log('[Contact] mail() failed for submission from ' . $data['email']);
        }
    }

    /** Remove characters that could start a new mail header. */
    public static function headerSafe(string $value): string
    {
        return trim(str_replace(["\r", "\n", "\0"], ' ', $value));
    }

    /**
     * Previously submitted values to re-fill the form after a validation error.
     *
     * @return array<string, string>
     */
    private function oldInput(): array
    {
        $old = [];
        foreach (['name', 'email', 'subject', 'message'] as $field) {
            $value = $_POST[$field] ?? '';
            $old[$field] = is_string($value) ? mb_substr($value, 0, 5000) : '';
        }
        return $old;
    }

    /**
     * Client IP for rate limiting. Defaults to REMOTE_ADDR: X-Forwarded-For is
     * client-controlled and would let a spammer pick a fresh key per request.
     * Behind a proxy you control (e.g. Cloudflare), set TRUSTED_PROXY_HEADER
     * in .env to the header carrying the real IP (HTTP_CF_CONNECTING_IP).
     */
    private function clientIp(): string
    {
        $header = (string) ($_ENV['TRUSTED_PROXY_HEADER'] ?? '');
        $ip     = '';
        if ($header !== '' && !empty($_SERVER[$header])) {
            $ip = trim(explode(',', (string) $_SERVER[$header])[0]);
        }
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        }
        return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : '0.0.0.0';
    }
}
