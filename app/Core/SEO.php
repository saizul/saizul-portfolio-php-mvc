<?php

declare(strict_types=1);

namespace App\Core;

/**
 * SEO — Advanced SEO tag builder
 *
 * Handles: meta title/description, Open Graph, Twitter Cards,
 * JSON-LD schema (Person, WebSite, Article, SoftwareApplication,
 * BreadcrumbList), canonical, robots directives.
 *
 * Usage:
 *   SEO::init('home');
 *   SEO::setTitle('Custom Title');
 *   SEO::setBreadcrumbs([['Home','https://saizulamin.com'],['Blog',null]]);
 *   echo SEO::render();   // in <head>
 */
class SEO
{
    /* ── Defaults ─────────────────────────────────────────────── */
    private static array $defaults = [
        'site_name'        => 'Saizul Amin',
        'base_url'         => '',
        'default_title'    => 'Saizul Amin — AI Engineer & Full-Stack Developer',
        'default_desc'     => 'Building intelligent AI systems and scalable full-stack applications. 5+ years experience in Python, PyTorch, LangChain, PHP, React and cloud-native architecture.',
        'og_image'         => '/assets/images/og-default.jpg',
        'twitter_handle'   => '@saizulamin',
        'locale'           => 'en_MY',
    ];

    /* ── State ─────────────────────────────────────────────────── */
    private static array $meta     = [];
    private static array $schemas  = [];
    private static array $breadcrumbs = [];

    /* ═══════════════════════════════════════════════════════════
       INITIALISATION — loads DB overrides for a given page slug
    ═══════════════════════════════════════════════════════════ */
    public static function init(string $pageSlug): void
    {
        // Resolve base URL once
        if (empty(self::$defaults['base_url'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'saizulamin.com';
            self::$defaults['base_url'] = $scheme . '://' . $host;
        }

        // Load from DB if available
        try {
            $seoMeta = (new \App\Models\SEOMeta())->findBySlug($pageSlug);
        } catch (\Throwable) {
            $seoMeta = null;
        }

        // Merge page-level overrides
        self::$meta = [
            'slug'              => $pageSlug,
            'title'             => $seoMeta['meta_title']       ?? self::$defaults['default_title'],
            'description'       => $seoMeta['meta_description'] ?? self::$defaults['default_desc'],
            'og_title'          => $seoMeta['og_title']         ?? ($seoMeta['meta_title'] ?? self::$defaults['default_title']),
            'og_description'    => $seoMeta['og_description']   ?? ($seoMeta['meta_description'] ?? self::$defaults['default_desc']),
            'og_image'          => $seoMeta['og_image']         ?? self::$defaults['og_image'],
            'og_type'           => 'website',
            'canonical'         => $seoMeta['canonical_url']    ?? self::buildCanonical($pageSlug),
            'robots'            => $seoMeta['robots_directive'] ?? 'index, follow',
            'schema_extra'      => $seoMeta['schema_json']      ?? null,
        ];

        // Always inject WebSite + Person schema on every page
        self::addWebSiteSchema();
        self::addPersonSchema();
    }

    /* ═══════════════════════════════════════════════════════════
       FLUENT SETTERS
    ═══════════════════════════════════════════════════════════ */
    public static function setTitle(string $title): void
    {
        self::$meta['title']       = $title;
        self::$meta['og_title']    = $title;
    }

    public static function setDescription(string $desc): void
    {
        self::$meta['description']    = $desc;
        self::$meta['og_description'] = $desc;
    }

    public static function setOgImage(string $url): void
    {
        self::$meta['og_image'] = self::absolute($url);
    }

    public static function setOgType(string $type): void
    {
        self::$meta['og_type'] = $type;
    }

    public static function setCanonical(string $url): void
    {
        self::$meta['canonical'] = $url;
    }

    public static function setRobots(string $directive): void
    {
        self::$meta['robots'] = $directive;
    }

    /** @param array<array{0:string,1:string|null}> $crumbs [[label, url], ...] */
    public static function setBreadcrumbs(array $crumbs): void
    {
        self::$breadcrumbs = $crumbs;
        self::addBreadcrumbSchema($crumbs);
    }

    /** Add raw JSON-LD schema block */
    public static function addSchema(array $schema): void
    {
        self::$schemas[] = $schema;
    }

    /* ═══════════════════════════════════════════════════════════
       PREDEFINED SCHEMA BUILDERS
    ═══════════════════════════════════════════════════════════ */
    public static function addPersonSchema(): void
    {
        self::$schemas['person'] = [
            '@context' => 'https://schema.org',
            '@type'    => 'Person',
            '@id'      => self::$defaults['base_url'] . '/#person',
            'name'     => 'Saizul Amin',
            'url'      => self::$defaults['base_url'],
            'image'    => self::absolute('/assets/images/saizul-portrait.jpg'),
            'jobTitle' => 'AI Engineer & Full-Stack Developer',
            'description' => self::$defaults['default_desc'],
            'email'    => 'saizulamin@gmail.com',
            'sameAs'   => [
                'https://github.com/saizulamin',
                'https://linkedin.com/in/saizulamin',
                'https://twitter.com/saizulamin',
            ],
            'knowsAbout' => [
                'Artificial Intelligence', 'Machine Learning', 'Python',
                'PyTorch', 'LangChain', 'FastAPI', 'PHP', 'React',
                'Full-Stack Development', 'Cloud Architecture',
            ],
        ];
    }

    public static function addWebSiteSchema(): void
    {
        self::$schemas['website'] = [
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            '@id'             => self::$defaults['base_url'] . '/#website',
            'name'            => self::$defaults['site_name'],
            'url'             => self::$defaults['base_url'],
            'description'     => self::$defaults['default_desc'],
            'author'          => ['@id' => self::$defaults['base_url'] . '/#person'],
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => self::$defaults['base_url'] . '/blog?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * @param array{title:string,url:string,description:string,published:string,modified:string,image:string,tags:string[]} $data
     */
    public static function addArticleSchema(array $data): void
    {
        self::$schemas['article'] = [
            '@context'         => 'https://schema.org',
            '@type'            => 'BlogPosting',
            'headline'         => $data['title'],
            'url'              => $data['url'],
            'description'      => $data['description'] ?? '',
            'datePublished'    => $data['published']   ?? '',
            'dateModified'     => $data['modified']    ?? ($data['published'] ?? ''),
            'image'            => self::absolute($data['image'] ?? self::$defaults['og_image']),
            'author'           => ['@id' => self::$defaults['base_url'] . '/#person'],
            'publisher'        => ['@id' => self::$defaults['base_url'] . '/#website'],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $data['url']],
            'keywords'         => implode(', ', $data['tags'] ?? []),
        ];
    }

    /**
     * @param array{name:string,url:string,description:string,github?:string,tech?:string[]} $data
     */
    public static function addSoftwareAppSchema(array $data): void
    {
        self::$schemas['software'] = [
            '@context'            => 'https://schema.org',
            '@type'               => 'SoftwareApplication',
            'name'                => $data['name'],
            'url'                 => $data['url'],
            'description'         => $data['description'] ?? '',
            'applicationCategory' => 'DeveloperApplication',
            'operatingSystem'     => 'Web',
            'author'              => ['@id' => self::$defaults['base_url'] . '/#person'],
            'codeRepository'      => $data['github'] ?? '',
            'programmingLanguage' => $data['tech']   ?? [],
        ];
    }

    /** @param array<array{0:string,1:string|null}> $crumbs */
    private static function addBreadcrumbSchema(array $crumbs): void
    {
        $items = [];
        foreach ($crumbs as $pos => [$name, $url]) {
            $item = [
                '@type'    => 'ListItem',
                'position' => $pos + 1,
                'name'     => $name,
            ];
            if ($url) $item['item'] = self::absolute($url);
            $items[] = $item;
        }
        self::$schemas['breadcrumb'] = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /* ═══════════════════════════════════════════════════════════
       RENDER — returns full <head> SEO block as HTML string
    ═══════════════════════════════════════════════════════════ */
    public static function render(): string
    {
        $m    = self::$meta;
        $base = self::$defaults['base_url'];
        $h    = [];   // output lines

        /* ── Basic meta ── */
        $h[] = '<meta charset="UTF-8">';
        $h[] = '<meta name="viewport" content="width=device-width, initial-scale=1">';
        $h[] = self::tag('title', self::e($m['title']));
        $h[] = self::metaTag('description',             $m['description']);
        $h[] = self::metaTag('robots',                  $m['robots']);
        $h[] = self::metaTag('author',                  'Saizul Amin');
        $h[] = self::metaTag('theme-color',             '#080c10');
        $h[] = self::linkTag('canonical',               $m['canonical']);

        /* ── Open Graph ── */
        $h[] = '';
        $h[] = '<!-- Open Graph -->';
        $h[] = self::ogTag('og:type',        $m['og_type']);
        $h[] = self::ogTag('og:url',         $m['canonical']);
        $h[] = self::ogTag('og:site_name',   self::$defaults['site_name']);
        $h[] = self::ogTag('og:locale',      self::$defaults['locale']);
        $h[] = self::ogTag('og:title',       $m['og_title']);
        $h[] = self::ogTag('og:description', $m['og_description']);
        $h[] = self::ogTag('og:image',       self::absolute($m['og_image']));
        $h[] = self::ogTag('og:image:alt',   $m['og_title']);
        $h[] = self::ogTag('og:image:width', '1200');
        $h[] = self::ogTag('og:image:height','630');

        /* ── Twitter Card ── */
        $h[] = '';
        $h[] = '<!-- Twitter Card -->';
        $h[] = self::twitterTag('twitter:card',        'summary_large_image');
        $h[] = self::twitterTag('twitter:site',        self::$defaults['twitter_handle']);
        $h[] = self::twitterTag('twitter:creator',     self::$defaults['twitter_handle']);
        $h[] = self::twitterTag('twitter:title',       $m['og_title']);
        $h[] = self::twitterTag('twitter:description', $m['og_description']);
        $h[] = self::twitterTag('twitter:image',       self::absolute($m['og_image']));
        $h[] = self::twitterTag('twitter:image:alt',   $m['og_title']);

        /* ── Extra meta ── */
        $h[] = '';
        $h[] = '<!-- Additional -->';
        $h[] = '<meta name="format-detection" content="telephone=no">';
        $h[] = '<meta name="mobile-web-app-capable" content="yes">';
        $h[] = '<meta name="apple-mobile-web-app-capable" content="yes">';
        $h[] = '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">';
        $h[] = '<link rel="icon" href="/assets/images/favicon.ico">';
        $h[] = '<link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon-32.png">';
        $h[] = '<link rel="apple-touch-icon" href="/assets/images/apple-touch-icon.png">';

        /* ── JSON-LD schemas ── */
        $h[] = '';
        $h[] = '<!-- Structured Data -->';

        // Extra raw schema from DB if available
        if (!empty($m['schema_extra'])) {
            $decoded = json_decode($m['schema_extra'], true);
            if ($decoded) self::$schemas['extra'] = $decoded;
        }

        foreach (self::$schemas as $schema) {
            $h[] = '<script type="application/ld+json">';
            $h[] = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $h[] = '</script>';
        }

        return implode("\n", $h);
    }

    /* ═══════════════════════════════════════════════════════════
       BREADCRUMB HTML (for visible breadcrumb nav in template)
    ═══════════════════════════════════════════════════════════ */
    public static function breadcrumbHtml(): string
    {
        if (empty(self::$breadcrumbs)) return '';
        $parts = [];
        $last  = count(self::$breadcrumbs) - 1;
        foreach (self::$breadcrumbs as $i => [$name, $url]) {
            if ($i === $last || !$url) {
                $parts[] = '<span aria-current="page">' . self::e($name) . '</span>';
            } else {
                $parts[] = '<a href="' . self::e($url) . '">' . self::e($name) . '</a>';
            }
        }
        return '<nav class="breadcrumb" aria-label="Breadcrumb"><ol>' .
               implode('<li aria-hidden="true">/</li>', array_map(fn($p) => "<li>$p</li>", $parts)) .
               '</ol></nav>';
    }

    /* ═══════════════════════════════════════════════════════════
       HELPERS
    ═══════════════════════════════════════════════════════════ */
    private static function buildCanonical(string $slug): string
    {
        $base = self::$defaults['base_url'];
        if ($slug === 'home' || $slug === '') return $base . '/';
        return $base . '/' . ltrim($slug, '/');
    }

    private static function absolute(string $url): string
    {
        if (str_starts_with($url, 'http')) return $url;
        return rtrim(self::$defaults['base_url'], '/') . '/' . ltrim($url, '/');
    }

    private static function e(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function tag(string $tag, string $content): string
    {
        return "<{$tag}>{$content}</{$tag}>";
    }

    private static function metaTag(string $name, string $content): string
    {
        return '<meta name="' . self::e($name) . '" content="' . self::e($content) . '">';
    }

    private static function ogTag(string $property, string $content): string
    {
        return '<meta property="' . self::e($property) . '" content="' . self::e($content) . '">';
    }

    private static function twitterTag(string $name, string $content): string
    {
        return '<meta name="' . self::e($name) . '" content="' . self::e($content) . '">';
    }

    private static function linkTag(string $rel, string $href): string
    {
        return '<link rel="' . self::e($rel) . '" href="' . self::e($href) . '">';
    }

    /* ── Convenience: get current meta for use in views ── */
    public static function get(string $key): string
    {
        return self::$meta[$key] ?? '';
    }
}
