<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Cache — file-based output cache.
 * Stores serialized values in storage/cache/.
 */
class Cache
{
    private static string $path = '';

    private static function path(): string
    {
        if (self::$path === '') {
            self::$path = BASE_PATH . '/storage/cache';
        }
        return self::$path;
    }

    private static function filepath(string $key): string
    {
        return self::path() . '/' . md5($key) . '.cache';
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    public static function get(string $key): mixed
    {
        $file = self::filepath($key);

        if (!file_exists($file)) {
            return null;
        }

        $raw = file_get_contents($file);
        if ($raw === false) return null;

        $data = unserialize($raw);

        // Check TTL
        if ($data['expires_at'] !== null && time() > $data['expires_at']) {
            @unlink($file);
            return null;
        }

        return $data['value'];
    }

    public static function set(string $key, mixed $value, int $ttl = 300): bool
    {
        $file = self::filepath($key);

        $data = [
            'expires_at' => $ttl > 0 ? time() + $ttl : null,
            'value'      => $value,
        ];

        return (bool) file_put_contents($file, serialize($data), LOCK_EX);
    }

    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) return $cached;

        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    public static function forget(string $key): bool
    {
        $file = self::filepath($key);
        return file_exists($file) ? unlink($file) : true;
    }

    public static function flush(): void
    {
        $files = glob(self::path() . '/*.cache');
        if ($files) {
            array_map('unlink', $files);
        }
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }
}
