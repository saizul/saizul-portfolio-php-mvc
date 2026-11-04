<?php
declare(strict_types=1);

// Load .env
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
        putenv(trim($key) . '=' . trim($val, " \t\n\r\0\x0B\"'"));
    }
}

$env = fn(string $key, mixed $default = null): mixed => $_ENV[$key] ?? getenv($key) ?: $default;

// ── Error reporting ──────────────────────────────────────────
if ($env('APP_DEBUG', 'false') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', dirname(__DIR__) . '/storage/logs/php-errors.log');
}

// ── Security ─────────────────────────────────────────────────
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime', '7200');
if ($env('APP_URL', '') !== '' && str_starts_with($env('APP_URL'), 'https')) {
    ini_set('session.cookie_secure', '1');
}

// ── Timezone ─────────────────────────────────────────────────
date_default_timezone_set('Asia/Kuala_Lumpur');

return [
    'name'     => $env('APP_NAME', 'Saizul Amin Portfolio'),
    'env'      => $env('APP_ENV', 'production'),
    'url'      => $env('APP_URL', 'https://saizulamin.com'),
    'key'      => $env('APP_KEY', ''),
    'debug'    => $env('APP_DEBUG', 'false') === 'true',
    'timezone' => 'Asia/Kuala_Lumpur',
    'locale'   => 'en_MY',
];
