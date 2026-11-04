<?php declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class PostTag extends Model {
    protected string $table = 'post_tags';
    public function all(): array { return $this->db()->fetchAll("SELECT * FROM post_tags ORDER BY name ASC"); }
    public function findBySlug(string $s): ?array { return $this->db()->fetchOne("SELECT * FROM post_tags WHERE slug=?",[$s])?:null; }
    public function forPost(int $postId): array {
        return $this->db()->fetchAll(
            "SELECT t.* FROM post_tags t JOIN post_tag_pivot p ON p.tag_id=t.id WHERE p.post_id=? ORDER BY t.name",
            [$postId]
        );
    }
    public function syncPost(int $postId, array $tagIds): void {
        $this->db()->execute("DELETE FROM post_tag_pivot WHERE post_id=?", [$postId]);
        foreach ($tagIds as $tid) {
            $this->db()->execute("INSERT IGNORE INTO post_tag_pivot (post_id,tag_id) VALUES (?,?)", [$postId,(int)$tid]);
        }
    }
}
