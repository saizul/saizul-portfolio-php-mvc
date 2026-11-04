<?php declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class HomepageSection extends Model {
    protected string $table = 'homepage_sections';

    public function allOrdered(): array {
        return $this->db()->fetchAll("SELECT * FROM homepage_sections ORDER BY order_col ASC");
    }

    public function enabled(): array {
        return $this->db()->fetchAll("SELECT * FROM homepage_sections WHERE is_enabled=1 ORDER BY order_col ASC");
    }

    public function findByKey(string $key): ?array {
        return $this->db()->fetchOne("SELECT * FROM homepage_sections WHERE section_key=?", [$key]) ?: null;
    }

    public function upsert(string $key, array $data): void {
        $exists = $this->findByKey($key);
        if ($exists) {
            $sets  = implode(',', array_map(fn($k) => "`$k`=?", array_keys($data)));
            $vals  = array_values($data);
            $vals[]= $key;
            $this->db()->execute("UPDATE homepage_sections SET $sets WHERE section_key=?", $vals);
        } else {
            $data['section_key'] = $key;
            $cols = implode(',', array_map(fn($k) => "`$k`", array_keys($data)));
            $phs  = implode(',', array_fill(0, count($data), '?'));
            $this->db()->execute("INSERT INTO homepage_sections ($cols) VALUES ($phs)", array_values($data));
        }
    }

    public function reorder(array $keys): void {
        foreach ($keys as $pos => $key) {
            $this->db()->execute("UPDATE homepage_sections SET order_col=? WHERE section_key=?", [$pos, $key]);
        }
    }

    public function toggle(int $id, int $enabled): void {
        $this->db()->execute("UPDATE homepage_sections SET is_enabled=? WHERE id=?", [$enabled, $id]);
    }
}
