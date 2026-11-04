<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Page;

class PageBuilderController extends Controller
{
    private array $allowedSlugs = [
        'home','about','ai-projects','experience','skills','portfolio','contact','blog',
    ];

    /* GET /admin/pages */
    public function index(): void
    {
        Auth::check();
        $model = new Page();
        $pages = [];
        try { $pages = $model->allCustom(); } catch (\Throwable) {}
        $this->render('admin.page-builder.index', compact('pages'), 'admin');
    }

    /* GET /admin/pages/{slug}/edit */
    public function edit(string $slug): void
    {
        Auth::check();
        $model = new Page();
        $page  = null;

        // Built-in page?
        if (in_array($slug, $this->allowedSlugs, true)) {
            try { $page = $model->findBySlug($slug); } catch (\Throwable) {}
            $page ??= ['slug' => $slug, 'title' => ucwords(str_replace('-',' ',$slug)), 'content_json' => '[]'];
        } else {
            // Custom page
            try { $page = $model->findBySlug($slug); } catch (\Throwable) {}
            if (!$page) { $this->redirect('/admin/pages'); return; }
        }

        // Builder is a standalone HTML page (no admin layout wrap needed)
        $this->renderRaw('admin.page-builder.edit', compact('page'));
    }

    /* POST /admin/pages/{slug}/save */
    public function save(string $slug): void
    {
        Auth::check();
        Auth::verifyCsrf();

        $json  = $_POST['content_json'] ?? '[]';
        $title = $_POST['title']        ?? ucwords(str_replace('-',' ',$slug));

        // Validate JSON
        json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) $json = '[]';

        // Sanitize title
        $title = htmlspecialchars(trim($title), ENT_QUOTES);

        $model = new Page();
        $model->upsertBySlug($slug, ['title' => $title, 'content_json' => $json]);

        // Return JSON for AJAX save or redirect for form submit
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            exit;
        }

        $this->redirect('/admin/pages?saved=1');
    }

    /* POST /admin/pages/create */
    public function create(): void
    {
        Auth::check();
        Auth::verifyCsrf();

        $title    = htmlspecialchars(trim($_POST['title']    ?? ''), ENT_QUOTES);
        $slug     = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_POST['slug'] ?? '')));
        $template = $_POST['template'] ?? 'blank';

        if (!$title || !$slug) { $this->redirect('/admin/pages?error=invalid'); return; }

        // Conflict check
        if (in_array($slug, $this->allowedSlugs, true)) {
            $this->redirect('/admin/pages?error=reserved');
            return;
        }

        $startBlocks = $this->templateBlocks($template, $title);
        $model = new Page();
        $model->upsertBySlug($slug, ['title' => $title, 'content_json' => json_encode($startBlocks)]);

        $this->redirect('/admin/pages/' . $slug . '/edit');
    }

    /* POST /admin/pages/{slug}/delete */
    public function delete(string $slug): void
    {
        Auth::check();
        Auth::verifyCsrf();

        if (in_array($slug, $this->allowedSlugs, true)) {
            $this->redirect('/admin/pages?error=cannot-delete-builtin');
            return;
        }

        (new Page())->deleteBySlug($slug);
        $this->redirect('/admin/pages?deleted=1');
    }

    /* ── Render without layout (builder is standalone) ── */
    private function renderRaw(string $view, array $data = []): void
    {
        extract($data);
        $path = str_replace('.', DIRECTORY_SEPARATOR, $view);
        $file = dirname(__DIR__, 2) . '/Views/' . $path . '.php';
        if (file_exists($file)) {
            require $file;
        }
        exit;
    }

    /* ── Starter block templates ── */
    private function templateBlocks(string $template, string $title): array
    {
        $uid = fn() => 'blk_' . uniqid('', true);
        return match ($template) {
            'landing' => [
                ['id' => $uid(), 'type' => 'hero', 'heading' => $title, 'subheading' => 'A compelling tagline for your page.', 'btn1Text' => 'Get Started', 'btn1Url' => '#', 'btn2Text' => 'Learn More', 'btn2Url' => '#', 'align' => 'center'],
                ['id' => $uid(), 'type' => 'text', 'heading' => 'About This Page', 'body' => 'Add your content here.', 'align' => 'left', 'headingSize' => 'h2'],
                ['id' => $uid(), 'type' => 'cta',  'heading' => 'Ready to get started?', 'body' => 'Take action now.', 'btnText' => 'Contact Me', 'btnUrl' => '/contact', 'align' => 'center'],
            ],
            'content' => [
                ['id' => $uid(), 'type' => 'text', 'heading' => $title, 'body' => 'Start writing your content here.', 'align' => 'left', 'headingSize' => 'h1'],
                ['id' => $uid(), 'type' => 'divider', 'style' => 'line', 'spacing' => 'md'],
                ['id' => $uid(), 'type' => 'text', 'heading' => '', 'body' => 'Continue with more content below.', 'align' => 'left', 'headingSize' => 'h2'],
            ],
            default => [], // blank
        };
    }
}
