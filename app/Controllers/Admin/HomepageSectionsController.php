<?php declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Controller, Auth};
use App\Models\HomepageSection;

class HomepageSectionsController extends Controller {
    private HomepageSection $model;
    public function __construct() { Auth::requireAdmin(); $this->model = new HomepageSection(); }

    public function index(): void {
        $sections = $this->model->allOrdered();
        $this->render('admin.homepage.index', compact('sections'), 'admin');
    }

    public function edit(string $key): void {
        $section = $this->model->findByKey($key) ?? ['section_key'=>$key,'title'=>'','subtitle'=>'','content_json'=>'[]','is_enabled'=>1];
        $this->render('admin.homepage.edit', compact('section'), 'admin');
    }

    public function save(string $key): void {
        Auth::verifyCsrf();
        $this->model->upsert($key, [
            'title'        => trim($_POST['title']    ?? ''),
            'subtitle'     => trim($_POST['subtitle'] ?? ''),
            'content_json' => $_POST['content_json'] ?? '[]',
            'is_enabled'   => isset($_POST['is_enabled']) ? 1 : 0,
        ]);
        $this->redirect('/admin/homepage', 'Section saved.');
    }

    public function toggle(): void {
        Auth::verifyCsrf();
        $id      = (int)($_POST['id']      ?? 0);
        $enabled = (int)($_POST['enabled'] ?? 0);
        $this->model->toggle($id, $enabled);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit;
        }
        $this->redirect('/admin/homepage');
    }

    public function reorder(): void {
        Auth::verifyCsrf();
        $keys = json_decode($_POST['keys'] ?? '[]', true);
        if (is_array($keys)) $this->model->reorder($keys);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit;
        }
        $this->redirect('/admin/homepage');
    }
}
