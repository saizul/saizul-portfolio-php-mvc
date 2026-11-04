<?php declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class MenuItem extends Model {
    protected string $table = 'menu_items';

    public function allActive(): array {
        return $this->db()->fetchAll(
            "SELECT * FROM menu_items WHERE is_active=1 ORDER BY parent_id ASC, order_col ASC"
        );
    }

    public function tree(): array {
        $all   = $this->allActive();
        $tree  = [];
        $index = [];
        foreach ($all as $item) { $index[$item['id']] = $item + ['children' => []]; }
        foreach ($index as $id => $item) {
            if ($item['parent_id']) $index[$item['parent_id']]['children'][] = &$index[$id];
            else $tree[] = &$index[$id];
        }
        return $tree;
    }

    public function all(): array {
        return $this->db()->fetchAll("SELECT * FROM menu_items ORDER BY parent_id ASC, order_col ASC");
    }

    public function reorder(array $ids): void {
        foreach ($ids as $pos => $id) {
            $this->db()->execute("UPDATE menu_items SET order_col=? WHERE id=?", [$pos, $id]);
        }
    }
}
