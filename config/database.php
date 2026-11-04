<?php

declare(strict_types=1);

/**
 * Database configuration.
 * All values read from .env — never hardcode credentials here.
 */
return [
    'host'     => $_ENV['DB_HOST']     ?? '127.0.0.1',
    'port'     => $_ENV['DB_PORT']     ?? '3306',
    'database' => $_ENV['DB_DATABASE'] ?? '',
    'username' => $_ENV['DB_USERNAME'] ?? '',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset'  => 'utf8mb4',
    'collation'=> 'utf8mb4_unicode_ci',
];
