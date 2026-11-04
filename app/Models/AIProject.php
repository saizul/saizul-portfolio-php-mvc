<?php declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class AIProject extends Model {
    protected string $table = 'ai_projects';

    public function published(): array {
        return $this->db()->fetchAll(
            "SELECT * FROM ai_projects WHERE status='published' ORDER BY order_col ASC, created_at DESC"
        );
    }

    public function findBySlug(string $slug): ?array {
        return $this->db()->fetchOne("SELECT * FROM ai_projects WHERE slug=?", [$slug]) ?: null;
    }

    public function all(): array {
        return $this->db()->fetchAll("SELECT * FROM ai_projects ORDER BY order_col ASC, created_at DESC");
    }

    public function generateSlug(string $title): string {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
        $slug = trim($slug, '-');
        $base = $slug; $i = 1;
        while ($this->db()->fetchOne("SELECT id FROM ai_projects WHERE slug=?", [$slug])) {
            $slug = "$base-" . $i++;
        }
        return $slug;
    }
}
