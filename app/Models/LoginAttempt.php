<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class LoginAttempt extends Model
{
    protected string $table = 'login_attempts';

    private const MAX_ATTEMPTS = 5;
    private const WINDOW_MINUTES = 15;

    // ------------------------------------------------------------------
    // Record a failed login attempt.
    // ------------------------------------------------------------------
    public function record(string $email, string $ip): void
    {
        $this->create([
            'email'        => $email,
            'ip'           => $ip,
            'attempted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ------------------------------------------------------------------
    // Check if email OR IP is currently rate-limited.
    // ------------------------------------------------------------------
    public function isBlocked(string $email, string $ip): bool
    {
        $window = date('Y-m-d H:i:s', strtotime('-' . self::WINDOW_MINUTES . ' minutes'));

        $byEmail = (int) $this->query(
            "SELECT COUNT(*) FROM `login_attempts`
             WHERE `email` = ? AND `attempted_at` >= ?",
            [$email, $window]
        )->fetchColumn();

        if ($byEmail >= self::MAX_ATTEMPTS) return true;

        $byIp = (int) $this->query(
            "SELECT COUNT(*) FROM `login_attempts`
             WHERE `ip` = ? AND `attempted_at` >= ?",
            [$ip, $window]
        )->fetchColumn();

        return $byIp >= self::MAX_ATTEMPTS;
    }

    // ------------------------------------------------------------------
    // How many seconds until the block lifts (for UX messaging).
    // ------------------------------------------------------------------
    public function secondsUntilUnblocked(string $email): int
    {
        $window = date('Y-m-d H:i:s', strtotime('-' . self::WINDOW_MINUTES . ' minutes'));

        $oldest = $this->query(
            "SELECT `attempted_at` FROM `login_attempts`
             WHERE `email` = ? AND `attempted_at` >= ?
             ORDER BY `attempted_at` ASC LIMIT 1",
            [$email, $window]
        )->fetchColumn();

        if (!$oldest) return 0;

        $unblockAt = strtotime($oldest) + (self::WINDOW_MINUTES * 60);
        return max(0, $unblockAt - time());
    }

    // ------------------------------------------------------------------
    // Clear attempts after a successful login.
    // ------------------------------------------------------------------
    public function clearFor(string $email): void
    {
        $this->query(
            "DELETE FROM `login_attempts` WHERE `email` = ?",
            [$email]
        );
    }

    // ------------------------------------------------------------------
    // Purge old attempts (run periodically).
    // ------------------------------------------------------------------
    public function purgeExpired(): void
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . self::WINDOW_MINUTES . ' minutes'));
        $this->query(
            "DELETE FROM `login_attempts` WHERE `attempted_at` < ?",
            [$cutoff]
        );
    }
}
