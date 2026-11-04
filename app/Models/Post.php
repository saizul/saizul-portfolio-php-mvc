<?php declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class Post extends Model {
    protected string $table = 'posts';

    public function published(int $limit = 20, int $offset = 0): array {
        return $this->db()->fetchAll(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.color AS category_color
             FROM posts p
             LEFT JOIN post_categories c ON c.id = p.category_id
             WHERE p.status='published'
             ORDER BY p.published_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function byCategory(int $catId, int $limit = 20): array {
        return $this->db()->fetchAll(
            "SELECT p.*, c.name AS category_name FROM posts p
             LEFT JOIN post_categories c ON c.id=p.category_id
             WHERE p.category_id=? AND p.status='published' ORDER BY p.published_at DESC LIMIT ?",
            [$catId, $limit]
        );
    }

    public function byTag(int $tagId, int $limit = 20): array {
        return $this->db()->fetchAll(
            "SELECT p.* FROM posts p
             JOIN post_tag_pivot pt ON pt.post_id=p.id
             WHERE pt.tag_id=? AND p.status='published' ORDER BY p.published_at DESC LIMIT ?",
            [$tagId, $limit]
        );
    }

    public function related(int $postId, ?int $catId, int $limit = 3): array {
        if ($catId) {
            return $this->db()->fetchAll(
                "SELECT * FROM posts WHERE status='published' AND category_id=? AND id!=? ORDER BY RAND() LIMIT ?",
                [$catId, $postId, $limit]
            );
        }
        return $this->db()->fetchAll(
            "SELECT * FROM posts WHERE status='published' AND id!=? ORDER BY RAND() LIMIT ?",
            [$postId, $limit]
        );
    }

    public function findBySlug(string $slug): ?array {
        return $this->db()->fetchOne(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.color AS category_color
             FROM posts p LEFT JOIN post_categories c ON c.id=p.category_id
             WHERE p.slug=? AND p.status='published'",
            [$slug]
        ) ?: null;
    }

    public function allForAdmin(): array {
        return $this->db()->fetchAll(
            "SELECT p.*, c.name AS category_name FROM posts p
             LEFT JOIN post_categories c ON c.id=p.category_id
             ORDER BY p.created_at DESC"
        );
    }

    public function generateSlug(string $title): string {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i','-',$title),'-'));
        $base = $slug; $i = 1;
        while ($this->db()->fetchOne("SELECT id FROM posts WHERE slug=?",[$slug])) $slug = "$base-".$i++;
        return $slug;
    }

    public function estimateReadingTime(string $body): int {
        $words = str_word_count(strip_tags($body));
        return max(1, (int)ceil($words / 200));
    }

    public function countPublished(): int {
        return (int)($this->db()->fetchOne("SELECT COUNT(*) AS n FROM posts WHERE status='published'")['n'] ?? 0);
    }
}
