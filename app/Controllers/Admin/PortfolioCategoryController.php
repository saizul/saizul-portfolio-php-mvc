<?php declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Controller, Auth};
use App\Models\PortfolioCategory;

class PortfolioCategoryController extends Controller {
    private PortfolioCategory $model;
    public function __construct() { Auth::requireAdmin(); $this->model = new PortfolioCategory(); }

    public function index(): void {
        $categories = $this->model->all();
        $this->render('admin.portfolio.categories', compact('categories'), 'admin');
    }

    public function store(): void {
        Auth::verifyCsrf();
        $name = trim($_POST['name'] ?? '');
        if (!$name) { $this->redirect('/admin/portfolio-categories', 'Name required.', 'error'); return; }
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $this->model->create([
            'name'        => $name,
            'slug'        => $slug,
            'description' => trim($_POST['description'] ?? ''),
            'color'       => trim($_POST['color'] ?? '#c0392b'),
        ]);
        $this->redirect('/admin/portfolio-categories', 'Category created.');
    }

    public function delete(int $id): void {
        Auth::verifyCsrf();
        $this->model->delete($id);
        $this->redirect('/admin/portfolio-categories', 'Category deleted.');
    }
}
