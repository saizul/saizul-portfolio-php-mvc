<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Sitemap — Dynamic XML sitemap generator
 *
 * Builds sitemap.xml from static pages + DB posts + DB projects.
 * Output cached to storage/sitemaps/sitemap.xml and served as
 * public/sitemap.xml via symlink or direct route.
 *
 * Usage:
 *   Sitemap::generate();         // returns XML string
 *   Sitemap::writeAndServe();    // writes file + sends response
 */
class Sitemap
{
    private static array $urls = [];

    /* Page priority / changefreq config */
    private static array $staticPages = [
        ['loc' => '/',            'priority' => '1.0',  'changefreq' => 'weekly'],
        ['loc' => '/about',       'priority' => '0.9',  'changefreq' => 'monthly'],
        ['loc' => '/ai-projects', 'priority' => '0.9',  'changefreq' => 'weekly'],
        ['loc' => '/experience',  'priority' => '0.8',  'changefreq' => 'monthly'],
        ['loc' => '/skills',      'priority' => '0.8',  'changefreq' => 'monthly'],
        ['loc' => '/portfolio',   'priority' => '0.85', 'changefreq' => 'weekly'],
        ['loc' => '/contact',     'priority' => '0.7',  'changefreq' => 'yearly'],
        ['loc' => '/blog',        'priority' => '0.85', 'changefreq' => 'daily'],
    ];

    /* ═══════════════════════════════════════════════════════════
       GENERATE — build and return XML string
    ═══════════════════════════════════════════════════════════ */
    public static function generate(): string
    {
        self::$urls = [];
        $base = self::baseUrl();

        /* ── Static pages ── */
        foreach (self::$staticPages as $page) {
            self::addUrl(
                $base . $page['loc'],
                date('Y-m-d'),
                $page['changefreq'],
                $page['priority']
            );
        }

        /* ── Blog posts ── */
        try {
            $posts = (new \App\Models\Post())->published();
            foreach ($posts as $post) {
                self::addUrl(
                    $base . '/blog/' . $post['slug'],
                    substr($post['published_at'] ?? $post['created_at'] ?? date('Y-m-d'), 0, 10),
                    'monthly',
                    '0.75'
                );
            }
        } catch (\Throwable) {}

        /* ── Projects ── */
        try {
            $projects = (new \App\Models\Project())->allActive();
            foreach ($projects as $project) {
                $loc = $base . '/ai-projects/' . $project['slug'];
                self::addUrl($loc, date('Y-m-d'), 'monthly', '0.7');
            }
        } catch (\Throwable) {}

        return self::buildXml();
    }

    /* ═══════════════════════════════════════════════════════════
       WRITE — save to storage and copy to public
    ═══════════════════════════════════════════════════════════ */
    public static function write(): string
    {
        $xml      = self::generate();
        $storage  = dirname(__DIR__, 2) . '/storage/sitemaps/sitemap.xml';
        $public   = dirname(__DIR__, 2) . '/public/sitemap.xml';

        @mkdir(dirname($storage), 0755, true);
        file_put_contents($storage, $xml);
        file_put_contents($public, $xml);

        return $xml;
    }

    /* Serve HTTP response */
    public static function serve(): void
    {
        // Serve cached file if fresh (< 6 hours old)
        $public = dirname(__DIR__, 2) . '/public/sitemap.xml';
        if (file_exists($public) && (time() - filemtime($public)) < 21600) {
            $xml = file_get_contents($public);
        } else {
            $xml = self::write();
        }
        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex');
        echo $xml;
    }

    /* ═══════════════════════════════════════════════════════════
       HELPERS
    ═══════════════════════════════════════════════════════════ */
    private static function addUrl(string $loc, string $lastmod, string $changefreq, string $priority): void
    {
        self::$urls[] = compact('loc', 'lastmod', 'changefreq', 'priority');
    }

    private static function buildXml(): string
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
                 . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

        foreach (self::$urls as $u) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'        . htmlspecialchars($u['loc'])        . '</loc>';
            $lines[] = '    <lastmod>'    . $u['lastmod']                      . '</lastmod>';
            $lines[] = '    <changefreq>' . $u['changefreq']                   . '</changefreq>';
            $lines[] = '    <priority>'   . $u['priority']                     . '</priority>';
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';
        return implode("\n", $lines);
    }

    private static function baseUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'saizulamin.com';
        return $scheme . '://' . $host;
    }
}
