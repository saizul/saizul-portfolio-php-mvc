<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Robots — Dynamic robots.txt generator
 *
 * Generates robots.txt content from DB settings (robots_rules key)
 * with sensible defaults and automatic sitemap injection.
 *
 * Usage:
 *   Robots::serve();   // sends HTTP response
 *   Robots::write();   // writes to public/robots.txt
 *   Robots::build();   // returns string
 */
class Robots
{
    public static function build(): string
    {
        // Load custom rules from settings table
        $custom = '';
        try {
            $custom = (new \App\Models\Setting())->get('robots_rules') ?? '';
        } catch (\Throwable) {}

        $base    = self::baseUrl();
        $sitemap = $base . '/sitemap.xml';

        if (!empty(trim($custom))) {
            // User-defined rules — append sitemap only if missing
            $content = trim($custom);
            if (!str_contains($content, 'Sitemap:')) {
                $content .= "\n\nSitemap: {$sitemap}";
            }
            return $content . "\n";
        }

        // Default rules
        return <<<ROBOTS
User-agent: *
Allow: /

Disallow: /admin
Disallow: /admin/
Disallow: /storage/
Disallow: /config/
Disallow: /app/
Disallow: /.env
Disallow: /database/
Disallow: /uploads/tmp/

# Allow specific assets
Allow: /assets/
Allow: /uploads/

Crawl-delay: 2

Sitemap: {$sitemap}
ROBOTS;
    }

    public static function write(): void
    {
        $path = dirname(__DIR__, 2) . '/public/robots.txt';
        file_put_contents($path, self::build());
    }

    public static function serve(): void
    {
        header('Content-Type: text/plain; charset=UTF-8');
        header('Cache-Control: public, max-age=86400');
        echo self::build();
    }

    private static function baseUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'saizulamin.com';
        return $scheme . '://' . $host;
    }
}
