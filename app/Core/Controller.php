<?php

declare(strict_types=1);

namespace App\Core;

// Gate is in same namespace — no import needed.

/**
 * Base Controller.
 * All public and admin controllers extend this class.
 */
abstract class Controller
{
    // ------------------------------------------------------------------
    // View rendering
    // ------------------------------------------------------------------

    /**
     * Render a view inside a layout.
     *
     * @param string $view    Dot-notation path: 'pages.home' or 'admin.dashboard'
     * @param array  $data    Variables extracted into the view
     * @param string $layout  Layout file: 'main' or 'admin'
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    /**
     * Render a view without any layout wrapper.
     */
    protected function renderPartial(string $view, array $data = []): void
    {
        View::renderPartial($view, $data);
    }

    // ------------------------------------------------------------------
    // Redirects
    // ------------------------------------------------------------------

    protected function redirect(string $url, int $code = 302): never
    {
        http_response_code($code);
        header('Location: ' . $url);
        exit;
    }

    protected function redirectBack(): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? url('/');
        $this->redirect($ref);
    }

    // ------------------------------------------------------------------
    // JSON responses
    // ------------------------------------------------------------------

    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // ------------------------------------------------------------------
    // Request helpers
    // ------------------------------------------------------------------

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    // ------------------------------------------------------------------
    // Flash messages
    // ------------------------------------------------------------------

    protected function flash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlash(): array|null
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    // ------------------------------------------------------------------
    // Auth + role guards — call in admin controllers
    // ------------------------------------------------------------------

    /** Require any authenticated session. */
    protected function requireAuth(): void
    {
        Gate::allow('editor');
    }

    /** Require admin role specifically. */
    protected function requireAdmin(): void
    {
        Gate::allow('admin');
    }

    /** Require editor-or-above role. */
    protected function requireEditor(): void
    {
        Gate::allow('editor');
    }

    // ------------------------------------------------------------------
    // CSRF
    // ------------------------------------------------------------------

    protected function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!Auth::verifyCsrfToken($token)) {
            http_response_code(403);
            die('CSRF token mismatch.');
        }
    }

    // ------------------------------------------------------------------
    // Abort helpers
    // ------------------------------------------------------------------

    protected function abort(int $code = 404, string $message = 'Not Found'): never
    {
        http_response_code($code);
        $view = BASE_PATH . "/app/Views/errors/{$code}.php";
        if (file_exists($view)) {
            require $view;
        } else {
            echo "<h1>{$code} — {$message}</h1>";
        }
        exit;
    }
}
