<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Project extends Model
{
    protected string $table = 'projects';

    public function byCategory(string $category): array
    {
        return $this->query(
            "SELECT * FROM `projects` WHERE category = ? AND status = 'published' ORDER BY `order_col` ASC",
            [$category]
        )->fetchAll();
    }

    public function ordered(): array
    {
        return $this->query(
            "SELECT * FROM `projects` WHERE status = 'published' ORDER BY `order_col` ASC",
            []
        )->fetchAll();
    }

    public function findBySlug(string $slug): array|false
    {
        return $this->first('slug', $slug);
    }

    public function featured(int $limit = 3): array
    {
        return $this->query(
            "SELECT * FROM `projects` WHERE status = 'published' ORDER BY `order_col` ASC LIMIT {$limit}",
            []
        )->fetchAll();
    }
}
