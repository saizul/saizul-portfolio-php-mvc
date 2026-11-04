<?php declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class FooterBlock extends Model {
    protected string $table = 'footer_blocks';

    public function allActive(): array {
        return $this->db()->fetchAll("SELECT * FROM footer_blocks WHERE is_active=1 ORDER BY order_col ASC");
    }

    public function all(): array {
        return $this->db()->fetchAll("SELECT * FROM footer_blocks ORDER BY order_col ASC");
    }

    public function findByKey(string $key): ?array {
        return $this->db()->fetchOne("SELECT * FROM footer_blocks WHERE block_key=?", [$key]) ?: null;
    }

    public function upsert(string $key, array $data): void {
        $exists = $this->findByKey($key);
        if ($exists) {
            $sets = implode(',', array_map(fn($k) => "`$k`=?", array_keys($data)));
            $vals = array_values($data);
            $vals[]= $key;
            $this->db()->execute("UPDATE footer_blocks SET $sets WHERE block_key=?", $vals);
        } else {
            $data['block_key'] = $key;
            $cols = implode(',', array_map(fn($k) => "`$k`", array_keys($data)));
            $phs  = implode(',', array_fill(0, count($data), '?'));
            $this->db()->execute("INSERT INTO footer_blocks ($cols) VALUES ($phs)", array_values($data));
        }
    }
}
