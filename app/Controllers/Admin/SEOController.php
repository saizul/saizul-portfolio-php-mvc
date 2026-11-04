<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Sitemap;
use App\Core\Robots;
use App\Models\SEOMeta;
use App\Models\Setting;

class SEOController extends Controller
{
    private static array $pages = [
        'home','about','ai-projects','experience','skills','portfolio','contact','blog',
    ];

    public function index(): void
    {
        $this->requireAuth();
        $this->render('admin.seo.index', ['pages' => self::$pages], 'admin');
    }

    public function edit(string $slug): void
    {
        $this->requireAuth();
        $meta = (new SEOMeta())->forPage($slug) ?: [];
        $this->render('admin.seo.form', ['slug' => $slug, 'meta' => $meta], 'admin');
    }

    public function save(string $slug): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        (new SEOMeta())->upsert($slug, [
            'meta_title'       => sanitizeString($this->post('meta_title', '')),
            'meta_description' => sanitizeString($this->post('meta_description', '')),
            'og_title'         => sanitizeString($this->post('og_title', '')),
            'og_description'   => sanitizeString($this->post('og_description', '')),
            'og_image'         => sanitizeString($this->post('og_image', '')),
            'canonical_url'    => sanitizeUrl($this->post('canonical_url', '')) ?: '',
            'robots_directive' => sanitizeString($this->post('robots_directive', 'index, follow')),
            'schema_json'      => $this->post('schema_json', ''),
        ]);

        $this->flash('success', 'SEO settings saved.');
        $this->redirect(url("/admin/seo/{$slug}"));
    }

    public function generateSitemap(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        Sitemap::generate();
        $this->flash('success', 'Sitemap regenerated.');
        $this->redirect(url('/admin/seo'));
    }

    public function saveRobots(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        (new Setting())->set('robots_rules', $this->post('robots_rules', ''));
        Robots::write();
        $this->flash('success', 'robots.txt updated.');
        $this->redirect(url('/admin/settings'));
    }
}
