<?php

declare(strict_types=1);

/**
 * Global helper functions.
 * Loaded once by the autoloader bootstrap.
 */

// ------------------------------------------------------------------
// Output escaping
// ------------------------------------------------------------------

/**
 * HTML-escape a value for safe output.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ------------------------------------------------------------------
// URL helpers
// ------------------------------------------------------------------

/**
 * Generate an absolute URL from a relative path.
 * url('/blog/my-post') → https://saizulamin.com/blog/my-post
 */
function url(string $path = ''): string
{
    $base = rtrim(config('app.url', ''), '/');
    $path = '/' . ltrim($path, '/');
    return $base . $path;
}

/**
 * Generate a URL for a public asset.
 * asset('css/app.css') → https://saizulamin.com/assets/css/app.css
 */
function asset(string $path): string
{
    return url('/assets/' . ltrim($path, '/'));
}

/**
 * Generate a URL for an uploaded file.
 * upload('images/photo.jpg') → https://saizulamin.com/uploads/images/photo.jpg
 */
function upload(string $path): string
{
    return url('/uploads/' . ltrim($path, '/'));
}

// ------------------------------------------------------------------
// Config
// ------------------------------------------------------------------

/**
 * Read a config value using dot notation.
 * config('app.name') reads config/app.php → $config['name']
 */
function config(string $key, mixed $default = null): mixed
{
    static $cache = [];

    [$file, $subkey] = array_pad(explode('.', $key, 2), 2, null);

    if (!isset($cache[$file])) {
        $path = BASE_PATH . "/config/{$file}.php";
        $cache[$file] = file_exists($path) ? require $path : [];
    }

    if ($subkey === null) {
        return $cache[$file] ?? $default;
    }

    // Support nested dot notation
    $data = $cache[$file];
    foreach (explode('.', $subkey) as $segment) {
        if (!is_array($data) || !array_key_exists($segment, $data)) {
            return $default;
        }
        $data = $data[$segment];
    }

    return $data ?? $default;
}

// ------------------------------------------------------------------
// Redirect
// ------------------------------------------------------------------

function redirect(string $url, int $code = 302): never
{
    http_response_code($code);
    header('Location: ' . $url);
    exit;
}

// ------------------------------------------------------------------
// Debug (disabled in production)
// ------------------------------------------------------------------

function dd(mixed ...$vars): never
{
    if (config('app.env') === 'production') {
        http_response_code(500);
        exit;
    }

    echo '<pre style="background:#1e1e1e;color:#dcdcdc;padding:1rem;border-radius:6px;overflow:auto;font-size:13px">';
    foreach ($vars as $var) {
        var_dump($var);
        echo "\n";
    }
    echo '</pre>';
    exit;
}

// ------------------------------------------------------------------
// Pagination
// ------------------------------------------------------------------

/**
 * Build a pagination links array.
 * Returns an array of ['page' => n, 'url' => '...', 'active' => bool]
 */
function paginationLinks(int $currentPage, int $lastPage, string $baseUrl = ''): array
{
    $links = [];
    $base  = $baseUrl ?: strtok(url($_SERVER['REQUEST_URI']), '?');

    for ($i = 1; $i <= $lastPage; $i++) {
        $links[] = [
            'page'   => $i,
            'url'    => $base . '?page=' . $i,
            'active' => $i === $currentPage,
        ];
    }

    return $links;
}

// ------------------------------------------------------------------
// String helpers
// ------------------------------------------------------------------

function slugify(string $text): string
{
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function excerpt(string $html, int $length = 160): string
{
    $text = strip_tags($html);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '…';
}

function timeAgo(string $datetime): string
{
    $now  = new \DateTimeImmutable();
    $then = new \DateTimeImmutable($datetime);
    $diff = $now->diff($then);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

function formatDate(string $datetime, string $format = 'F j, Y'): string
{
    return (new \DateTimeImmutable($datetime))->format($format);
}
