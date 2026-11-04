<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Experience extends Model
{
    protected string $table = 'experience';

    public function ordered(): array
    {
        return $this->query(
            "SELECT * FROM `experience` ORDER BY `order_col` ASC",
            []
        )->fetchAll();
    }

    public function workOnly(): array
    {
        return $this->query(
            "SELECT * FROM `experience` WHERE type = 'work' ORDER BY `order_col` ASC",
            []
        )->fetchAll();
    }

    public function educationOnly(): array
    {
        return $this->query(
            "SELECT * FROM `experience` WHERE type = 'education' ORDER BY `order_col` ASC",
            []
        )->fetchAll();
    }
}
