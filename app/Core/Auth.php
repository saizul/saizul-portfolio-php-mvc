<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;
use App\Models\LoginAttempt;

/**
 * Auth — session-based authentication with:
 *   - Brute-force protection (rate limiting via LoginAttempt model)
 *   - Session fingerprinting (user-agent + IP binding)
 *   - Session ID regeneration on privilege escalation
 *   - CSRF double-submit token
 *   - Role-based access checks (admin, editor)
 *   - Secure session cookie configuration
 */
class Auth
{
    private const SESSION_KEY  = 'auth_user';
    private const CSRF_KEY     = 'csrf_token';
    private const FINGERPRINT  = 'session_fingerprint';

    // ------------------------------------------------------------------
    // Bootstrap — call once per request before any auth check
    // ------------------------------------------------------------------

    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) return;

        // Secure session cookie settings
        session_set_cookie_params([
            'lifetime' => 0,           // Expire on browser close
            'path'     => '/',
            'domain'   => '',          // Current domain only
            'secure'   => self::isHttps(),
            'httponly' => true,        // No JS access
            'samesite' => 'Lax',
        ]);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_start();

        // Validate session fingerprint on every request
        if (!empty($_SESSION[self::SESSION_KEY])) {
            self::validateFingerprint();
        }
    }

    // ------------------------------------------------------------------
    // Login
    // Returns: true on success | 'blocked' | 'invalid'
    // ------------------------------------------------------------------

    public static function login(string $email, string $password): true|string
    {
        self::start();

        $ip          = self::clientIp();
        $attemptModel = new LoginAttempt();

        // Brute-force check
        if ($attemptModel->isBlocked($email, $ip)) {
            return 'blocked';
        }

        $userModel = new User();
        $user      = $userModel->findByEmail($email);

        if (!$user || !$user['is_active'] || !password_verify($password, $user['password_hash'])) {
            $attemptModel->record($email, $ip);
            return 'invalid';
        }

        // Successful login
        $attemptModel->clearFor($email);
        $userModel->updateLastLogin($user['id']);

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION[self::SESSION_KEY] = [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];

        // Store fingerprint
        $_SESSION[self::FINGERPRINT] = self::generateFingerprint();

        // Rehash password if bcrypt cost changed
        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $userModel->updatePassword((int) $user['id'], $password);
        }

        return true;
    }

    // ------------------------------------------------------------------
    // Logout
    // ------------------------------------------------------------------

    public static function logout(): void
    {
        self::start();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
    }

    // ------------------------------------------------------------------
    // Guard — check authentication
    // ------------------------------------------------------------------

    /** Returns true if a user session is active and fingerprint matches. */
    public static function check(): bool
    {
        self::start();
        return !empty($_SESSION[self::SESSION_KEY]);
    }

    /** Returns current user array or null. */
    public static function user(): array|null
    {
        self::start();
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function id(): int|null
    {
        return self::user() ? (int) self::user()['id'] : null;
    }

    // ------------------------------------------------------------------
    // Role checks
    // ------------------------------------------------------------------

    public static function role(): string
    {
        return self::user()['role'] ?? '';
    }

    /** Admin only. */
    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    /** Admin or Editor. */
    public static function isEditor(): bool
    {
        return in_array(self::role(), ['admin', 'editor'], true);
    }

    /**
     * Require a minimum role. Aborts with 403 if not met.
     * Usage: Auth::requireRole('admin');
     */
    public static function requireRole(string $minRole): void
    {
        if (!self::check()) {
            http_response_code(302);
            header('Location: ' . url('/admin/login'));
            exit;
        }

        $hierarchy = ['editor' => 1, 'admin' => 2];
        $userLevel = $hierarchy[self::role()] ?? 0;
        $required  = $hierarchy[$minRole] ?? 999;

        if ($userLevel < $required) {
            http_response_code(403);
            require BASE_PATH . '/app/Views/errors/403.php';
            exit;
        }
    }

    // ------------------------------------------------------------------
    // CSRF
    // ------------------------------------------------------------------

    public static function generateCsrfToken(): string
    {
        self::start();

        if (empty($_SESSION[self::CSRF_KEY])) {
            $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::CSRF_KEY];
    }

    public static function verifyCsrfToken(string $token): bool
    {
        self::start();
        $stored = $_SESSION[self::CSRF_KEY] ?? '';

        if (empty($stored) || empty($token)) return false;

        return hash_equals($stored, $token);
    }

    /** Rotate the CSRF token (call after a verified POST to prevent re-use). */
    public static function rotateCsrfToken(): void
    {
        self::start();
        $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));
    }

    /** Output a hidden CSRF input field. */
    public static function csrfField(): string
    {
        return sprintf(
            '<input type="hidden" name="_csrf" value="%s">',
            htmlspecialchars(self::generateCsrfToken(), ENT_QUOTES, 'UTF-8')
        );
    }

    // ------------------------------------------------------------------
    // Session fingerprinting — anti session-hijacking
    // ------------------------------------------------------------------

    private static function generateFingerprint(): string
    {
        // Bind session to: User-Agent + last octet of IP (handles NAT)
        $ua      = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $ipBlock = implode('.', array_slice(explode('.', self::clientIp()), 0, 3));
        return hash('sha256', $ua . '|' . $ipBlock . '|' . config('app.name'));
    }

    private static function validateFingerprint(): void
    {
        $stored  = $_SESSION[self::FINGERPRINT] ?? null;
        $current = self::generateFingerprint();

        if ($stored !== null && !hash_equals($stored, $current)) {
            // Fingerprint mismatch — destroy the session
            error_log('[Auth] Session fingerprint mismatch. Possible hijack attempt from IP: ' . self::clientIp());
            self::logout();
        }
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private static function clientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                // Take first IP if comma-separated (proxy chains)
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return '0.0.0.0';
    }

    private static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
    }
}
