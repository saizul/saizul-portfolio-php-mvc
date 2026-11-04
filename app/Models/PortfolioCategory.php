<?php declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class PortfolioCategory extends Model {
    protected string $table = 'portfolio_categories';
    public function all(): array {
        return $this->db()->fetchAll("SELECT * FROM portfolio_categories ORDER BY order_col ASC, name ASC");
    }
    public function findBySlug(string $slug): ?array {
        return $this->db()->fetchOne("SELECT * FROM portfolio_categories WHERE slug=?", [$slug]) ?: null;
    }
}
