<?php declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class PostCategory extends Model {
    protected string $table = 'post_categories';
    public function all(): array { return $this->db()->fetchAll("SELECT * FROM post_categories ORDER BY name ASC"); }
    public function findBySlug(string $s): ?array { return $this->db()->fetchOne("SELECT * FROM post_categories WHERE slug=?",[$s])?:null; }
}
