<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\RateLimiter;
use PHPUnit\Framework\TestCase;

final class RateLimiterTest extends TestCase
{
    private string $dir;
    private int $now = 1_700_000_000;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/ratelimiter-test-' . bin2hex(random_bytes(6));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }
    }

    private function limiter(): RateLimiter
    {
        return new RateLimiter($this->dir, fn(): int => $this->now);
    }

    public function testFreshKeyIsNotLimited(): void
    {
        $limiter = $this->limiter();

        self::assertSame(0, $limiter->attempts('k', 60));
        self::assertFalse($limiter->tooManyAttempts('k', 1, 60));
        self::assertSame(0, $limiter->availableIn('k', 1, 60));
    }

    public function testHitsAccumulateUntilLimit(): void
    {
        $limiter = $this->limiter();

        self::assertSame(1, $limiter->hit('k', 60));
        self::assertSame(2, $limiter->hit('k', 60));
        self::assertFalse($limiter->tooManyAttempts('k', 3, 60));

        self::assertSame(3, $limiter->hit('k', 60));
        self::assertTrue($limiter->tooManyAttempts('k', 3, 60));
    }

    public function testStorageDirectoryIsCreatedOnFirstHit(): void
    {
        self::assertDirectoryDoesNotExist($this->dir);
        $this->limiter()->hit('k', 60);
        self::assertDirectoryExists($this->dir);
    }

    public function testKeysAreIndependentAndHashedOnDisk(): void
    {
        $limiter = $this->limiter();
        $limiter->hit('contact:203.0.113.5', 60);

        self::assertSame(0, $limiter->attempts('contact:198.51.100.7', 60));

        $files = glob($this->dir . '/*') ?: [];
        self::assertCount(1, $files);
        self::assertStringNotContainsString('203.0.113.5', basename($files[0]));
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}\.json$/', basename($files[0]));
    }

    public function testWindowSlides(): void
    {
        $limiter = $this->limiter();

        $limiter->hit('k', 600);           // t = 0
        $this->now += 200;
        $limiter->hit('k', 600);           // t = 200
        $this->now += 200;
        $limiter->hit('k', 600);           // t = 400

        self::assertTrue($limiter->tooManyAttempts('k', 3, 600));
        // The oldest hit (t = 0) expires at t = 600, i.e. 200 s from now.
        self::assertSame(200, $limiter->availableIn('k', 3, 600));

        $this->now += 200;                 // t = 600: first hit has expired
        self::assertSame(2, $limiter->attempts('k', 600));
        self::assertFalse($limiter->tooManyAttempts('k', 3, 600));
        self::assertSame(0, $limiter->availableIn('k', 3, 600));
    }

    public function testAvailableInWhenOverTheLimit(): void
    {
        $limiter = $this->limiter();

        // Four hits at t = 0, 10, 20, 30 with a limit of 2: two must expire,
        // so the key frees up when the hit at t = 20 leaves the window.
        foreach ([0, 10, 10, 10] as $step) {
            $this->now += $step;
            $limiter->hit('k', 100);
        }

        self::assertSame(90, $limiter->availableIn('k', 2, 100));
    }

    public function testExpiredHitsArePrunedOnWrite(): void
    {
        $limiter = $this->limiter();
        $limiter->hit('k', 60);
        $limiter->hit('k', 60);

        $this->now += 61;
        self::assertSame(1, $limiter->hit('k', 60));

        $files = glob($this->dir . '/*') ?: [];
        self::assertSame([$this->now], json_decode((string) file_get_contents($files[0]), true));
    }

    public function testClear(): void
    {
        $limiter = $this->limiter();
        $limiter->hit('k', 60);
        $limiter->clear('k');

        self::assertSame(0, $limiter->attempts('k', 60));
        $limiter->clear('never-used'); // no error
    }

    public function testCorruptFileIsTreatedAsEmpty(): void
    {
        $limiter = $this->limiter();
        $limiter->hit('k', 60);

        $files = glob($this->dir . '/*') ?: [];
        file_put_contents($files[0], 'not json{');

        self::assertSame(0, $limiter->attempts('k', 60));
        self::assertSame(1, $limiter->hit('k', 60));
    }

    public function testFailsOpenWhenStorageIsUnusable(): void
    {
        // A regular file where the directory should be: mkdir() must fail.
        $blocker = $this->dir;
        file_put_contents($blocker, '');

        $errorLog = ini_set('error_log', '/dev/null');
        try {
            $limiter = new RateLimiter($blocker . '/sub', fn(): int => $this->now);
            self::assertSame(0, $limiter->hit('k', 60));
            self::assertFalse($limiter->tooManyAttempts('k', 1, 60));
        } finally {
            ini_set('error_log', (string) $errorLog);
            unlink($blocker);
        }
    }
}
