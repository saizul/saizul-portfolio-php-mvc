<?php declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Controller,Auth};
use App\Models\PostCategory;

class PostCategoryController extends Controller {
    private PostCategory $model;
    public function __construct() { Auth::requireAdmin(); $this->model = new PostCategory(); }

    public function index(): void {
        $categories = $this->model->all();
        $this->render('admin.blog.categories', compact('categories'), 'admin');
    }

    public function store(): void {
        Auth::verifyCsrf();
        $name = trim($_POST['name'] ?? '');
        if (!$name) { $this->redirect('/admin/post-categories','Name required.','error'); return; }
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',$name));
        $this->model->create(['name'=>$name,'slug'=>$slug,'description'=>trim($_POST['description']??''),'color'=>trim($_POST['color']??'#6ee7b7')]);
        $this->redirect('/admin/post-categories','Category created.');
    }

    public function delete(int $id): void {
        Auth::verifyCsrf();
        $this->model->delete($id);
        $this->redirect('/admin/post-categories','Category deleted.');
    }
}
