<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class PasswordReset extends Model
{
    protected string $table = 'password_resets';

    private const TTL_MINUTES = 60;

    // ------------------------------------------------------------------
    // Create a new reset token for an email.
    // Returns the raw (unhashed) token to send to the user.
    // ------------------------------------------------------------------
    public function createToken(string $email): string
    {
        // Invalidate any existing tokens for this email
        $this->query(
            "DELETE FROM `password_resets` WHERE `email` = ?",
            [$email]
        );

        $rawToken  = bin2hex(random_bytes(32)); // 64-char hex string
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::TTL_MINUTES . ' minutes'));

        $this->create([
            'email'      => $email,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);

        return $rawToken;
    }

    // ------------------------------------------------------------------
    // Find a valid (unexpired, unused) reset row by raw token.
    // ------------------------------------------------------------------
    public function findValidToken(string $rawToken): array|false
    {
        $tokenHash = hash('sha256', $rawToken);

        $stmt = $this->query(
            "SELECT * FROM `password_resets`
             WHERE `token_hash` = ?
               AND `expires_at` > NOW()
               AND `used_at` IS NULL
             LIMIT 1",
            [$tokenHash]
        );

        return $stmt->fetch();
    }

    // ------------------------------------------------------------------
    // Mark token as consumed.
    // ------------------------------------------------------------------
    public function consume(int $id): void
    {
        $this->update($id, ['used_at' => date('Y-m-d H:i:s')]);
    }

    // ------------------------------------------------------------------
    // Purge expired/used tokens (call from a maintenance task).
    // ------------------------------------------------------------------
    public function purgeExpired(): void
    {
        $this->query(
            "DELETE FROM `password_resets`
             WHERE `expires_at` < NOW() OR `used_at` IS NOT NULL",
            []
        );
    }
}
