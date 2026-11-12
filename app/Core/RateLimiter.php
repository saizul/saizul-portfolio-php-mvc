<?php

declare(strict_types=1);

namespace App\Core;

/**
 * RateLimiter — file-based sliding-window rate limiter.
 *
 * Works on shared hosting without Redis or extra DB tables: each key gets a
 * small JSON file in storage/ratelimit/ holding the timestamps of recent hits.
 * Keys are hashed, so raw IPs or emails never appear in file names.
 *
 * Usage:
 *   $limiter = new RateLimiter();
 *   $key = 'contact:' . $ip;
 *   if ($limiter->tooManyAttempts($key, 3, 600)) {
 *       $wait = $limiter->availableIn($key, 3, 600);
 *       ...
 *   }
 *   $limiter->hit($key, 600);
 *
 * If the storage directory cannot be created or written, the limiter fails
 * open (allows the request) and logs the problem, so a permissions issue
 * never takes the public site down.
 */
final class RateLimiter
{
    private string $path;

    /** @var \Closure(): int */
    private \Closure $clock;

    /**
     * @param string|null        $path  Storage directory (default: BASE_PATH/storage/ratelimit)
     * @param (\Closure(): int)|null $clock Returns the current Unix time; injectable for tests
     */
    public function __construct(?string $path = null, ?\Closure $clock = null)
    {
        $this->path  = rtrim($path ?? BASE_PATH . '/storage/ratelimit', '/');
        $this->clock = $clock ?? static fn(): int => time();
    }

    /** Has $key reached $maxAttempts within the last $decaySeconds? */
    public function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        return $this->attempts($key, $decaySeconds) >= $maxAttempts;
    }

    /** Number of hits for $key inside the current window. */
    public function attempts(string $key, int $decaySeconds): int
    {
        return count($this->recentHits($this->read($key), $decaySeconds));
    }

    /**
     * Record one hit for $key and return the number of hits in the window
     * (including this one).
     */
    public function hit(string $key, int $decaySeconds): int
    {
        $file = $this->file($key);
        if (!$this->ensureDirectory()) {
            return 0;
        }

        $handle = @fopen($file, 'c+');
        if ($handle === false) {
            error_log("[RateLimiter] Cannot open {$file} for writing.");
            return 0;
        }

        try {
            flock($handle, LOCK_EX);
            $hits   = $this->recentHits($this->decode((string) stream_get_contents($handle)), $decaySeconds);
            $hits[] = $this->now();

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, (string) json_encode($hits));
            fflush($handle);

            return count($hits);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * Seconds until $key drops below $maxAttempts again (0 if not limited).
     */
    public function availableIn(string $key, int $maxAttempts, int $decaySeconds): int
    {
        $hits = $this->recentHits($this->read($key), $decaySeconds);
        if (count($hits) < $maxAttempts) {
            return 0;
        }

        sort($hits);
        // The window frees up once enough of the oldest hits have expired.
        $releasing = $hits[count($hits) - $maxAttempts];
        return max(0, $releasing + $decaySeconds - $this->now());
    }

    /** Forget every hit recorded for $key. */
    public function clear(string $key): void
    {
        $file = $this->file($key);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    private function now(): int
    {
        return ($this->clock)();
    }

    private function file(string $key): string
    {
        return $this->path . '/' . hash('sha256', $key) . '.json';
    }

    /** @return list<int> */
    private function read(string $key): array
    {
        $file = $this->file($key);
        if (!is_file($file)) {
            return [];
        }

        $raw = @file_get_contents($file);
        return $raw === false ? [] : $this->decode($raw);
    }

    /** @return list<int> */
    private function decode(string $raw): array
    {
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return [];
        }
        return array_values(array_filter($data, 'is_int'));
    }

    /**
     * @param  list<int> $hits
     * @return list<int>
     */
    private function recentHits(array $hits, int $decaySeconds): array
    {
        $cutoff = $this->now() - $decaySeconds;
        return array_values(array_filter($hits, fn(int $t) => $t > $cutoff));
    }

    private function ensureDirectory(): bool
    {
        if (is_dir($this->path)) {
            return true;
        }
        if (@mkdir($this->path, 0755, true) || is_dir($this->path)) {
            return true;
        }
        error_log("[RateLimiter] Cannot create storage directory {$this->path}.");
        return false;
    }
}
