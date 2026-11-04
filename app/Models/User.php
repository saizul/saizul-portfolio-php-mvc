<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';

    // ------------------------------------------------------------------
    // Lookups
    // ------------------------------------------------------------------

    public function findByEmail(string $email): array|false
    {
        return $this->first('email', $email);
    }

    public function findActive(int $id): array|false
    {
        $stmt = $this->query(
            "SELECT * FROM `users` WHERE `id` = ? AND `is_active` = 1 LIMIT 1",
            [$id]
        );
        return $stmt->fetch();
    }

    // ------------------------------------------------------------------
    // Role checks
    // ------------------------------------------------------------------

    public function isAdmin(array $user): bool
    {
        return ($user['role'] ?? '') === 'admin';
    }

    public function isEditor(array $user): bool
    {
        return in_array($user['role'] ?? '', ['admin', 'editor'], true);
    }

    // ------------------------------------------------------------------
    // Password
    // ------------------------------------------------------------------

    public function updatePassword(int $id, string $newPassword): bool
    {
        return $this->update($id, [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateLastLogin(int $id): void
    {
        $this->update($id, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    // ------------------------------------------------------------------
    // Admin list
    // ------------------------------------------------------------------

    public function allUsers(): array
    {
        return $this->query(
            "SELECT `id`, `name`, `email`, `role`, `is_active`, `last_login_at`, `created_at`
             FROM `users` ORDER BY `created_at` DESC",
            []
        )->fetchAll();
    }
}
