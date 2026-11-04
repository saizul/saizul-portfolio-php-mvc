<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class Page extends Model
{
    protected string $table = 'pages';

    /** All custom (non built-in) pages */
    public function allCustom(): array
    {
        return $this->db()->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY updated_at DESC"
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db()->fetchOne(
            "SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1",
            [':slug' => $slug]
        ) ?: null;
    }

    public function upsertBySlug(string $slug, array $data): bool
    {
        $existing = $this->findBySlug($slug);
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($existing) {
            $sets  = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
            $params = array_combine(
                array_map(fn($k) => ":$k", array_keys($data)),
                array_values($data)
            );
            $params[':slug'] = $slug;
            return $this->db()->execute(
                "UPDATE {$this->table} SET {$sets} WHERE slug = :slug",
                $params
            );
        }

        $data['slug']       = $slug;
        $data['created_at'] = $data['updated_at'];
        $cols   = implode(', ', array_keys($data));
        $phs    = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
        $params = array_combine(
            array_map(fn($k) => ":$k", array_keys($data)),
            array_values($data)
        );
        return $this->db()->execute(
            "INSERT INTO {$this->table} ({$cols}) VALUES ({$phs})",
            $params
        );
    }

    public function deleteBySlug(string $slug): bool
    {
        return $this->db()->execute(
            "DELETE FROM {$this->table} WHERE slug = :slug",
            [':slug' => $slug]
        );
    }
}
