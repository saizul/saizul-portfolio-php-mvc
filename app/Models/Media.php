<?php declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class Media extends Model {
    protected string $table = 'media';

    public function all(): array {
        return $this->db()->fetchAll("SELECT * FROM media ORDER BY uploaded_at DESC");
    }

    public function byFolder(string $folder): array {
        return $this->db()->fetchAll("SELECT * FROM media WHERE folder=? ORDER BY uploaded_at DESC", [$folder]);
    }

    public function images(): array {
        return $this->db()->fetchAll(
            "SELECT * FROM media WHERE mime_type LIKE 'image/%' ORDER BY uploaded_at DESC"
        );
    }

    public function search(string $q): array {
        $like = "%{$q}%";
        return $this->db()->fetchAll(
            "SELECT * FROM media WHERE filename LIKE ? OR alt_text LIKE ? OR title LIKE ? ORDER BY uploaded_at DESC",
            [$like, $like, $like]
        );
    }
}
