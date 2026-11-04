<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Skill extends Model
{
    protected string $table = 'skills';

    public function byCategory(): array
    {
        $rows = $this->query(
            "SELECT * FROM `skills` ORDER BY category, `order_col` ASC",
            []
        )->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['category']][] = $row;
        }
        return $grouped;
    }

    public function ordered(): array
    {
        return $this->query(
            "SELECT * FROM `skills` ORDER BY `order_col` ASC",
            []
        )->fetchAll();
    }
}
