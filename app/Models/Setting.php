<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    protected string $table = 'settings';

    public function get(string $key): string|null
    {
        $row = $this->first('key', $key);
        return $row ? $row['value'] : null;
    }

    public function set(string $key, string $value): bool
    {
        $existing = $this->first('key', $key);
        if ($existing) {
            return $this->update($existing['id'], ['value' => $value]);
        }
        $this->create(['key' => $key, 'value' => $value]);
        return true;
    }

    public function group(string $group): array
    {
        return $this->where('group', $group);
    }

    public function groupAsMap(string $group): array
    {
        $rows = $this->group($group);
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['key']] = $row['value'];
        }
        return $map;
    }
}
