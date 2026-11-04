<?php declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Controller, Auth};
use App\Models\FooterBlock;

class FooterController extends Controller {
    private FooterBlock $model;
    public function __construct() { Auth::requireAdmin(); $this->model = new FooterBlock(); }

    public function index(): void {
        $blocks = $this->model->all();
        $this->render('admin.footer.index', compact('blocks'), 'admin');
    }

    public function save(): void {
        Auth::verifyCsrf();
        $key = trim($_POST['block_key'] ?? '');
        if (!$key) { $this->redirect('/admin/footer', 'Key required.', 'error'); return; }
        $this->model->upsert($key, [
            'title'     => trim($_POST['title']   ?? ''),
            'content'   => trim($_POST['content'] ?? ''),
            'order_col' => (int)($_POST['order_col'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        $this->redirect('/admin/footer', 'Footer block saved.');
    }

    public function delete(string $key): void {
        Auth::verifyCsrf();
        $this->model->db()->execute("DELETE FROM footer_blocks WHERE block_key=?", [$key]);
        $this->redirect('/admin/footer', 'Block deleted.');
    }
}
