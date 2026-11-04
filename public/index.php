<?php

declare(strict_types=1);

/**
 * Front Controller — all HTTP requests are routed here via .htaccess.
 *
 * Boot order:
 *  1. Constants
 *  2. .env loader
 *  3. PHP settings (error handling, timezone)
 *  4. Autoloader
 *  5. Helpers
 *  6. Session
 *  7. Router dispatch
 */

// ------------------------------------------------------------------
// 1. Constants
// ------------------------------------------------------------------

define('BASE_PATH', dirname(__DIR__));
define('START_TIME', microtime(true));

// ------------------------------------------------------------------
// 2. Load .env
// ------------------------------------------------------------------

$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (!str_contains($line, '=')) continue;

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\"'");

        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }
}

// ------------------------------------------------------------------
// 3. PHP runtime settings
// ------------------------------------------------------------------

$isProduction = ($_ENV['APP_ENV'] ?? 'production') === 'production';

error_reporting($isProduction ? 0 : E_ALL);
ini_set('display_errors', $isProduction ? '0' : '1');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/error.log');

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// ------------------------------------------------------------------
// 4. PSR-4 Autoloader — App\ namespace maps to app/
// ------------------------------------------------------------------

spl_autoload_register(function (string $class): void {
    // App\Core\Router → app/Core/Router.php
    if (str_starts_with($class, 'App\\')) {
        $relative = str_replace('App\\', '', $class);
        $path = BASE_PATH . '/app/' . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
});

// ------------------------------------------------------------------
// 5. Helpers
// ------------------------------------------------------------------

require_once BASE_PATH . '/app/Helpers/functions.php';
require_once BASE_PATH . '/app/Helpers/sanitize.php';

// ------------------------------------------------------------------
// 6. Session (hardened cookie params)
// ------------------------------------------------------------------

// Set secure session cookie parameters before session_start()
session_set_cookie_params([
    'lifetime' => 0,                                   // session cookie (expires on browser close)
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,                                // inaccessible to JavaScript
    'samesite' => 'Strict',                            // CSRF mitigation
]);

use App\Core\Auth;
Auth::start();

// ------------------------------------------------------------------
// 7. Route dispatch
// ------------------------------------------------------------------

use App\Core\Router;

$router = new Router();
require BASE_PATH . '/config/routes.php';

$router->dispatch(
    $_SERVER['REQUEST_URI'],
    $_SERVER['REQUEST_METHOD']
);
