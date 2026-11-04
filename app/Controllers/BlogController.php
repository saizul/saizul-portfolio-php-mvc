<?php declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Controller, SEO, Cache};
use App\Models\{Post, PostCategory, PostTag};

class BlogController extends Controller {
    private Post $model;
    public function __construct() { $this->model = new Post(); }

    public function index(): void {
        $page    = max(1,(int)($_GET['page']??1));
        $perPage = 12;

        // Cache blog index per page — 30 min TTL
        $cacheKey = "blog_index_p{$page}";
        $cached = Cache::remember($cacheKey, 1800, function() use ($page, $perPage) {
            return [
                'total' => $this->model->countPublished(),
                'posts' => $this->model->published($perPage, ($page-1)*$perPage),
                'cats'  => (new PostCategory())->all(),
            ];
        });
        ['total' => $total, 'posts' => $posts, 'cats' => $cats] = $cached;
        SEO::init('blog');
        SEO::setTitle('Blog — Saizul Amin');
        SEO::setDescription('Insights on AI, web development, SEO, and technology by Saizul Amin.');
        SEO::setBreadcrumbs([['Home','/'],['Blog',null]]);
        $this->render('pages.blog.index', compact('posts','cats','page','perPage','total'), 'main');
    }

    public function single(string $slug): void {
        $post = $this->model->findBySlug($slug);
        if (!$post) { http_response_code(404); $this->render('errors.404',[],'main'); return; }
        $related = $this->model->related($post['id'], $post['category_id'] ?? null);
        $tags    = (new PostTag())->forPost($post['id']);
        SEO::init('blog/' . $slug);
        SEO::setTitle(($post['meta_title'] ?: $post['title']) . ' — Blog');
        if ($post['meta_description']) SEO::setDescription($post['meta_description']);
        if ($post['og_image'] ?? '') SEO::setOgImage($post['og_image']);
        SEO::setBreadcrumbs([['Home','/'],['Blog','/blog'],[$post['title'],null]]);
        SEO::addArticleSchema(['title'=>$post['title'],'description'=>$post['excerpt']??'','url'=>url('/blog/'.$slug),'image'=>$post['featured_image']??'','published'=>$post['published_at']??'','modified'=>$post['updated_at']??'']);
        $this->render('pages.blog.single', compact('post','related','tags'), 'main');
    }

    public function category(string $slug): void {
        $cat = (new PostCategory())->findBySlug($slug);
        if (!$cat) { http_response_code(404); $this->render('errors.404',[],'main'); return; }
        $posts = $this->model->byCategory($cat['id']);
        $cats  = (new PostCategory())->all();
        SEO::init('blog/category/'.$slug);
        SEO::setTitle($cat['name'] . ' — Blog Category');
        SEO::setBreadcrumbs([['Home','/'],['Blog','/blog'],[$cat['name'],null]]);
        $this->render('pages.blog.index', ['posts'=>$posts,'cats'=>$cats,'page'=>1,'perPage'=>20,'total'=>count($posts),'activeCat'=>$cat], 'main');
    }

    public function tag(string $slug): void {
        $tag = (new PostTag())->findBySlug($slug);
        if (!$tag) { http_response_code(404); $this->render('errors.404',[],'main'); return; }
        $posts = $this->model->byTag($tag['id']);
        $cats  = (new PostCategory())->all();
        SEO::init('blog/tag/'.$slug);
        SEO::setTitle('Tag: ' . $tag['name'] . ' — Blog');
        SEO::setBreadcrumbs([['Home','/'],['Blog','/blog'],['#'.$tag['name'],null]]);
        $this->render('pages.blog.index', ['posts'=>$posts,'cats'=>$cats,'page'=>1,'perPage'=>20,'total'=>count($posts),'activeTag'=>$tag], 'main');
    }
}
