<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Gate — declarative role-based access control.
 *
 * Usage in any controller:
 *   Gate::allow('admin');          // Only admins
 *   Gate::allow('editor');         // Admins + Editors
 *   Gate::denyIf(!Auth::check());  // Any unauthenticated user
 */
class Gate
{
    private const HIERARCHY = [
        'editor' => 1,
        'admin'  => 2,
    ];

    /**
     * Require at minimum the given role.
     * Redirects to login if unauthenticated.
     * Returns 403 view if authenticated but insufficient role.
     */
    public static function allow(string $minRole): void
    {
        if (!Auth::check()) {
            // Store intended URL for post-login redirect
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['intended_url'] = url($_SERVER['REQUEST_URI'] ?? '/admin');
            }
            http_response_code(302);
            header('Location: ' . url('/admin/login'));
            exit;
        }

        $userLevel     = self::HIERARCHY[Auth::role()] ?? 0;
        $requiredLevel = self::HIERARCHY[$minRole]      ?? 999;

        if ($userLevel < $requiredLevel) {
            http_response_code(403);
            $view = BASE_PATH . '/app/Views/errors/403.php';
            file_exists($view) ? require $view : self::bare403();
            exit;
        }
    }

    /**
     * Abort immediately if condition is true.
     * Gate::denyIf(!Auth::isAdmin(), 403);
     */
    public static function denyIf(bool $condition, int $code = 403): void
    {
        if ($condition) {
            http_response_code($code);
            $view = BASE_PATH . "/app/Views/errors/{$code}.php";
            file_exists($view) ? require $view : self::bare403();
            exit;
        }
    }

    /** Quick check without aborting — returns bool. */
    public static function can(string $minRole): bool
    {
        if (!Auth::check()) return false;
        $userLevel     = self::HIERARCHY[Auth::role()] ?? 0;
        $requiredLevel = self::HIERARCHY[$minRole]      ?? 999;
        return $userLevel >= $requiredLevel;
    }

    private static function bare403(): void
    {
        echo '<h1 style="font-family:sans-serif">403 — Forbidden</h1>
              <p>You do not have permission to access this page.</p>';
    }
}
