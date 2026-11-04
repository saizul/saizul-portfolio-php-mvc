<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\SEO;
use App\Models\Page;

/**
 * PageController — renders any custom CMS-built page by slug.
 * Matched by router after all named routes fail.
 */
class PageController extends Controller
{
    public function show(string $slug): void
    {
        $page = (new Page())->findBySlug($slug);

        if (!$page) {
            http_response_code(404);
            $this->render('errors.404', [], 'main');
            return;
        }

        SEO::init($slug);
        if (!empty($page['title'])) SEO::setTitle($page['title'] . ' — Saizul Amin');
        SEO::setBreadcrumbs([['Home', '/'], [$page['title'], null]]);

        $blocks = json_decode($page['content_json'] ?? '[]', true) ?: [];

        $this->render('pages.dynamic', compact('page', 'blocks'), 'main');
    }
}
