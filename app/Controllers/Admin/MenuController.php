<?php declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Controller, Auth};
use App\Models\MenuItem;

class MenuController extends Controller {
    private MenuItem $model;
    public function __construct() { Auth::requireAdmin(); $this->model = new MenuItem(); }

    public function index(): void {
        $items = $this->model->all();
        $this->render('admin.menu.index', compact('items'), 'admin');
    }

    public function store(): void {
        Auth::verifyCsrf();
        $label  = trim($_POST['label'] ?? '');
        $url    = trim($_POST['url']   ?? '');
        if (!$label || !$url) { $this->redirect('/admin/menu', 'Label and URL required.', 'error'); return; }
        $this->model->create([
            'label'     => $label,
            'url'       => $url,
            'target'    => $_POST['target']    ?? '_self',
            'parent_id' => $_POST['parent_id'] ? (int)$_POST['parent_id'] : null,
            'order_col' => (int)($_POST['order_col'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        $this->redirect('/admin/menu', 'Menu item added.');
    }

    public function update(int $id): void {
        Auth::verifyCsrf();
        $this->model->update($id, [
            'label'     => trim($_POST['label'] ?? ''),
            'url'       => trim($_POST['url']   ?? ''),
            'target'    => $_POST['target']    ?? '_self',
            'parent_id' => $_POST['parent_id'] ? (int)$_POST['parent_id'] : null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        $this->redirect('/admin/menu', 'Menu item updated.');
    }

    public function delete(int $id): void {
        Auth::verifyCsrf();
        $this->model->delete($id);
        $this->redirect('/admin/menu', 'Item deleted.');
    }

    public function reorder(): void {
        Auth::verifyCsrf();
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        if (is_array($ids)) $this->model->reorder(array_map('intval', $ids));
        if ($this->isAjax()) { header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit; }
        $this->redirect('/admin/menu');
    }

    private function isAjax(): bool { return !empty($_SERVER['HTTP_X_REQUESTED_WITH']); }
}
