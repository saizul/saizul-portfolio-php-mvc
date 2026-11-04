<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Sitemap;

class SitemapController extends Controller
{
    public function index(): void
    {
        Sitemap::serve();
    }

    /** Called by admin on publish to regenerate the cached file */
    public function regenerate(): void
    {
        \App\Core\Auth::check();
        Sitemap::write();
        $this->redirect('/admin/seo?sitemap=regenerated');
    }
}
