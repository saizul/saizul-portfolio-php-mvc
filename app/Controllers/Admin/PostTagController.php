<?php declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Controller,Auth};
use App\Models\PostTag;

class PostTagController extends Controller {
    private PostTag $model;
    public function __construct() { Auth::requireAdmin(); $this->model = new PostTag(); }

    public function index(): void {
        $tags = $this->model->all();
        $this->render('admin.blog.tags', compact('tags'), 'admin');
    }

    public function store(): void {
        Auth::verifyCsrf();
        $name = trim($_POST['name'] ?? '');
        if (!$name) { $this->redirect('/admin/post-tags','Name required.','error'); return; }
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',$name));
        $this->model->create(['name'=>$name,'slug'=>$slug]);
        $this->redirect('/admin/post-tags','Tag created.');
    }

    public function delete(int $id): void {
        Auth::verifyCsrf();
        $this->model->db()->execute("DELETE FROM post_tag_pivot WHERE tag_id=?",[$id]);
        $this->model->delete($id);
        $this->redirect('/admin/post-tags','Tag deleted.');
    }
}
