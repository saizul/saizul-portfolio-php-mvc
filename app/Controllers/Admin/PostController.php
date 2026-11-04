<?php declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Controller,Auth,Cache};
use App\Models\{Post,PostCategory,PostTag,Media};

class PostController extends Controller {
    private Post $model;
    public function __construct() { Auth::requireAdmin(); $this->model = new Post(); }

    public function index(): void {
        $posts = $this->model->allForAdmin();
        $this->render('admin.posts.index', compact('posts'), 'admin');
    }

    public function create(): void {
        $categories = (new PostCategory())->all();
        $tags       = (new PostTag())->all();
        $media      = (new Media())->images();
        $this->render('admin.posts.form', ['post'=>null,'categories'=>$categories,'tags'=>$tags,'media'=>$media,'action'=>'/admin/posts/store'], 'admin');
    }

    public function store(): void {
        Auth::verifyCsrf();
        $data = $this->sanitize();
        if (!$data['title']) { $this->redirect('/admin/posts/create','Title required.','error'); return; }
        $data['slug']            = $this->model->generateSlug($data['title']);
        $data['reading_time_min']= $this->model->estimateReadingTime($data['body']);
        $id = $this->model->create($data);
        (new PostTag())->syncPost((int)$id, array_map('intval', $_POST['tag_ids'] ?? []));
        Cache::flush(); // Invalidate blog/home caches
        $this->redirect('/admin/posts','Post created.');
    }

    public function edit(int $id): void {
        $post       = $this->model->find($id);
        if (!$post) { $this->redirect('/admin/posts','Not found.','error'); return; }
        $categories = (new PostCategory())->all();
        $tags       = (new PostTag())->all();
        $postTags   = (new PostTag())->forPost($id);
        $media      = (new Media())->images();
        $this->render('admin.posts.form', compact('post','categories','tags','postTags','media'), 'admin');
    }

    public function update(int $id): void {
        Auth::verifyCsrf();
        $data = $this->sanitize();
        $data['reading_time_min'] = $this->model->estimateReadingTime($data['body']);
        $this->model->update($id, $data);
        (new PostTag())->syncPost($id, array_map('intval', $_POST['tag_ids'] ?? []));
        Cache::flush();
        $this->redirect('/admin/posts','Post updated.');
    }

    public function delete(int $id): void {
        Auth::verifyCsrf();
        $this->model->db()->execute("DELETE FROM post_tag_pivot WHERE post_id=?",[$id]);
        $this->model->delete($id);
        Cache::flush();
        $this->redirect('/admin/posts','Post deleted.');
    }

    private function sanitize(): array {
        return [
            'title'            => trim($_POST['title']            ?? ''),
            'excerpt'          => trim($_POST['excerpt']          ?? ''),
            'body'             => $_POST['body']                  ?? '',
            'featured_image'   => trim($_POST['featured_image']   ?? ''),
            'status'           => $_POST['status']                ?? 'draft',
            'category_id'      => $_POST['category_id'] ? (int)$_POST['category_id'] : null,
            'published_at'     => $_POST['published_at']          ?: null,
            'meta_title'       => trim($_POST['meta_title']       ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'og_image'         => trim($_POST['og_image']         ?? ''),
        ];
    }
}
