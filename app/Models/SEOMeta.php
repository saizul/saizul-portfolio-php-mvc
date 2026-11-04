<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class SEOMeta extends Model
{
    protected string $table = 'seo_meta';

    public function forPage(string $slug): array|false
    {
        return $this->first('page_slug', $slug);
    }

    public function upsert(string $slug, array $data): bool
    {
        $existing = $this->forPage($slug);
        if ($existing) {
            return $this->update($existing['id'], $data);
        }
        $data['page_slug'] = $slug;
        $this->create($data);
        return true;
    }
}
