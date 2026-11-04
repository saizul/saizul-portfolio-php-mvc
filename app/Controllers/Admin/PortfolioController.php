<?php declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Controller, Auth};
use App\Models\{Project, PortfolioCategory, Media};

class PortfolioController extends Controller {
    private Project $model;
    private PortfolioCategory $catModel;

    public function __construct() {
        Auth::requireAdmin();
        $this->model    = new Project();
        $this->catModel = new PortfolioCategory();
    }

    public function index(): void {
        $projects   = $this->model->allForAdmin();
        $categories = $this->catModel->all();
        $this->render('admin.portfolio.index', compact('projects','categories'), 'admin');
    }

    public function create(): void {
        $categories = $this->catModel->all();
        $mediaItems = (new Media())->all();
        $this->render('admin.portfolio.form', [
            'project'    => null,
            'categories' => $categories,
            'mediaItems' => $mediaItems,
            'action'     => '/admin/portfolio/store',
        ], 'admin');
    }

    public function store(): void {
        Auth::verifyCsrf();
        $data = $this->sanitizeInput();
        if (!$data['title']) { $this->redirect('/admin/portfolio/create', 'Title required.', 'error'); return; }
        $data['slug'] = $this->model->generateSlug($data['title']);
        $id = $this->model->create($data);
        $this->saveImages((int)$id, $_POST['image_ids'] ?? []);
        $this->redirect('/admin/portfolio', 'Portfolio item created.');
    }

    public function edit(int $id): void {
        $project    = $this->model->find($id);
        if (!$project) { $this->redirect('/admin/portfolio', 'Not found.', 'error'); return; }
        $categories = $this->catModel->all();
        $mediaItems = (new Media())->all();
        $images     = $this->model->db()->fetchAll("SELECT * FROM portfolio_images WHERE project_id=? ORDER BY order_col", [$id]);
        $this->render('admin.portfolio.form', [
            'project'    => $project,
            'categories' => $categories,
            'mediaItems' => $mediaItems,
            'images'     => $images,
            'action'     => "/admin/portfolio/{$id}/update",
        ], 'admin');
    }

    public function update(int $id): void {
        Auth::verifyCsrf();
        $data = $this->sanitizeInput();
        $this->model->update($id, $data);
        $this->saveImages($id, $_POST['image_ids'] ?? []);
        $this->redirect('/admin/portfolio', 'Portfolio item updated.');
    }

    public function delete(int $id): void {
        Auth::verifyCsrf();
        $this->model->db()->execute("DELETE FROM portfolio_images WHERE project_id=?", [$id]);
        $this->model->delete($id);
        $this->redirect('/admin/portfolio', 'Item deleted.');
    }

    public function reorder(): void {
        Auth::verifyCsrf();
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        if (is_array($ids)) {
            foreach ($ids as $pos => $id) {
                $this->model->update((int)$id, ['order_col' => $pos]);
            }
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit;
        }
        $this->redirect('/admin/portfolio');
    }

    private function sanitizeInput(): array {
        return [
            'title'             => trim($_POST['title']           ?? ''),
            'client_name'       => trim($_POST['client_name']     ?? ''),
            'country'           => trim($_POST['country']         ?? ''),
            'project_type'      => trim($_POST['project_type']    ?? ''),
            'description'       => trim($_POST['description']     ?? ''),
            'case_study'        => trim($_POST['case_study']      ?? ''),
            'results'           => trim($_POST['results']         ?? ''),
            'tech_stack'        => trim($_POST['tech_stack']      ?? ''),
            'live_url'          => trim($_POST['live_url']        ?? ''),
            'demo_url'          => trim($_POST['demo_url']        ?? ''),
            'github_url'        => trim($_POST['github_url']      ?? ''),
            'video_url'         => trim($_POST['video_url']       ?? ''),
            'portfolio_cat_id'  => $_POST['portfolio_cat_id'] ? (int)$_POST['portfolio_cat_id'] : null,
            'category'          => $_POST['category'] ?? 'portfolio',
            'featured'          => isset($_POST['featured']) ? 1 : 0,
            'status'            => $_POST['status'] ?? 'published',
            'thumbnail'         => trim($_POST['thumbnail']       ?? ''),
            'meta_title'        => trim($_POST['meta_title']      ?? ''),
            'meta_description'  => trim($_POST['meta_description']?? ''),
        ];
    }

    private function saveImages(int $projectId, array $imageIds): void {
        $this->model->db()->execute("DELETE FROM portfolio_images WHERE project_id=?", [$projectId]);
        foreach ($imageIds as $pos => $mediaId) {
            $media = (new Media())->find((int)$mediaId);
            if (!$media) continue;
            $this->model->db()->execute(
                "INSERT INTO portfolio_images (project_id, filepath, alt_text, order_col) VALUES (?,?,?,?)",
                [$projectId, $media['filepath'], $media['alt_text'] ?? '', $pos]
            );
        }
    }
}
