<?php

declare(strict_types=1);

namespace App\Core;

/**
 * SEOScorer — On-page SEO audit engine
 *
 * Analyses a page's SEO metadata and returns a score 0–100
 * with per-check results. Used by the admin SEO dashboard.
 *
 * Usage:
 *   $result = SEOScorer::analyse($seoRow, $pageSlug);
 *   // $result = ['score' => 87, 'grade' => 'A', 'checks' => [...]]
 */
class SEOScorer
{
    /* Check definitions: [id, label, weight, group] */
    private static array $checks = [
        /* Title */
        ['id' => 'title_exists',       'label' => 'Meta title is set',                        'weight' => 10, 'group' => 'Title'],
        ['id' => 'title_length',       'label' => 'Title length 50–60 characters',             'weight' => 8,  'group' => 'Title'],
        ['id' => 'title_keyword',      'label' => 'Title contains brand or keyword',           'weight' => 5,  'group' => 'Title'],

        /* Description */
        ['id' => 'desc_exists',        'label' => 'Meta description is set',                  'weight' => 10, 'group' => 'Description'],
        ['id' => 'desc_length',        'label' => 'Description length 120–160 characters',    'weight' => 7,  'group' => 'Description'],
        ['id' => 'desc_unique',        'label' => 'Description differs from title',           'weight' => 4,  'group' => 'Description'],

        /* Open Graph */
        ['id' => 'og_title',           'label' => 'OG title is set',                          'weight' => 6,  'group' => 'Open Graph'],
        ['id' => 'og_description',     'label' => 'OG description is set',                    'weight' => 5,  'group' => 'Open Graph'],
        ['id' => 'og_image',           'label' => 'OG image is set',                          'weight' => 7,  'group' => 'Open Graph'],
        ['id' => 'og_image_size',      'label' => 'OG image path looks valid',                'weight' => 3,  'group' => 'Open Graph'],

        /* Twitter */
        ['id' => 'twitter_title',      'label' => 'Twitter title/OG title available',         'weight' => 4,  'group' => 'Twitter Card'],
        ['id' => 'twitter_image',      'label' => 'Twitter image available',                  'weight' => 4,  'group' => 'Twitter Card'],

        /* Technical */
        ['id' => 'canonical_set',      'label' => 'Canonical URL is set',                     'weight' => 8,  'group' => 'Technical'],
        ['id' => 'robots_indexable',   'label' => 'Page is indexable (not noindex)',           'weight' => 6,  'group' => 'Technical'],
        ['id' => 'schema_set',         'label' => 'Custom schema JSON-LD is set',             'weight' => 4,  'group' => 'Schema'],
        ['id' => 'schema_valid',       'label' => 'Schema JSON is valid JSON',                'weight' => 4,  'group' => 'Schema'],

        /* Content hints (metadata-based proxies) */
        ['id' => 'slug_clean',         'label' => 'Page slug is SEO-friendly',                'weight' => 3,  'group' => 'URL'],
        ['id' => 'no_special_chars',   'label' => 'Title has no special HTML characters',     'weight' => 2,  'group' => 'Quality'],
    ];

    /* ═══════════════════════════════════════════════════════════
       MAIN ENTRY
    ═══════════════════════════════════════════════════════════ */
    /**
     * @param array|null $seoRow  Row from seo_meta table (or null if not set)
     * @param string     $slug    The page slug
     * @return array{score:int, grade:string, max:int, checks:array, groups:array}
     */
    public static function analyse(?array $seoRow, string $slug = ''): array
    {
        $d = $seoRow ?? [];
        $results = [];
        $earned  = 0;
        $total   = 0;

        foreach (self::$checks as $check) {
            $pass    = self::runCheck($check['id'], $d, $slug);
            $earned += $pass ? $check['weight'] : 0;
            $total  += $check['weight'];

            $results[] = [
                'id'      => $check['id'],
                'label'   => $check['label'],
                'weight'  => $check['weight'],
                'group'   => $check['group'],
                'pass'    => $pass,
                'tip'     => $pass ? null : self::tip($check['id']),
            ];
        }

        $score = $total > 0 ? (int) round(($earned / $total) * 100) : 0;

        // Group results
        $groups = [];
        foreach ($results as $r) {
            $groups[$r['group']][] = $r;
        }

        return [
            'score'  => $score,
            'grade'  => self::grade($score),
            'earned' => $earned,
            'max'    => $total,
            'checks' => $results,
            'groups' => $groups,
        ];
    }

    /* ═══════════════════════════════════════════════════════════
       CHECK RUNNERS
    ═══════════════════════════════════════════════════════════ */
    private static function runCheck(string $id, array $d, string $slug): bool
    {
        $title    = trim($d['meta_title']       ?? '');
        $desc     = trim($d['meta_description'] ?? '');
        $ogTitle  = trim($d['og_title']         ?? '');
        $ogDesc   = trim($d['og_description']   ?? '');
        $ogImage  = trim($d['og_image']         ?? '');
        $canon    = trim($d['canonical_url']    ?? '');
        $robots   = strtolower(trim($d['robots_directive'] ?? 'index, follow'));
        $schema   = trim($d['schema_json']      ?? '');

        return match ($id) {
            'title_exists'     => strlen($title) > 0,
            'title_length'     => strlen($title) >= 50 && strlen($title) <= 70,
            'title_keyword'    => str_contains(strtolower($title), 'saizul')
                                   || str_contains(strtolower($title), 'ai')
                                   || str_contains(strtolower($title), 'developer'),
            'desc_exists'      => strlen($desc) > 0,
            'desc_length'      => strlen($desc) >= 120 && strlen($desc) <= 165,
            'desc_unique'      => strlen($desc) > 0 && strtolower($desc) !== strtolower($title),
            'og_title'         => strlen($ogTitle) > 0 || strlen($title) > 0,
            'og_description'   => strlen($ogDesc) > 0 || strlen($desc) > 0,
            'og_image'         => strlen($ogImage) > 0,
            'og_image_size'    => strlen($ogImage) > 4 && (str_contains($ogImage, '.jpg') || str_contains($ogImage, '.png') || str_contains($ogImage, '.webp')),
            'twitter_title'    => strlen($ogTitle) > 0 || strlen($title) > 0,
            'twitter_image'    => strlen($ogImage) > 0,
            'canonical_set'    => strlen($canon) > 0,
            'robots_indexable' => !str_contains($robots, 'noindex'),
            'schema_set'       => strlen($schema) > 0,
            'schema_valid'     => strlen($schema) > 0 && json_decode($schema) !== null,
            'slug_clean'       => strlen($slug) > 0 && preg_match('/^[a-z0-9\-\/]+$/', $slug) === 1,
            'no_special_chars' => strlen($title) === 0 || !preg_match('/[<>&"\'{}]/', $title),
            default            => false,
        };
    }

    /* ═══════════════════════════════════════════════════════════
       IMPROVEMENT TIPS
    ═══════════════════════════════════════════════════════════ */
    private static function tip(string $id): string
    {
        return match ($id) {
            'title_exists'     => 'Add a meta title for this page in the SEO tab.',
            'title_length'     => 'Keep your title between 50–70 characters for best display in SERPs.',
            'title_keyword'    => 'Include your name or a primary keyword in the title.',
            'desc_exists'      => 'Add a meta description to improve click-through rates.',
            'desc_length'      => 'Aim for 120–165 characters — long enough to be informative, short enough to avoid truncation.',
            'desc_unique'      => 'Your description should differ from the title to add extra context.',
            'og_title'         => 'Set an Open Graph title to control how this page appears when shared.',
            'og_description'   => 'Add an OG description for social sharing.',
            'og_image'         => 'Upload an OG image (1200×630px recommended) for rich social previews.',
            'og_image_size'    => 'The OG image path should point to a .jpg, .png, or .webp file.',
            'twitter_title'    => 'Set an OG or Twitter title for Twitter card display.',
            'twitter_image'    => 'An OG image is used as the Twitter card image — add one.',
            'canonical_set'    => 'Set a canonical URL to avoid duplicate content issues.',
            'robots_indexable' => 'This page is set to noindex — search engines will ignore it.',
            'schema_set'       => 'Add JSON-LD schema markup to improve rich result eligibility.',
            'schema_valid'     => 'Your schema JSON is invalid — check syntax in the Schema tab.',
            'slug_clean'       => 'Use lowercase letters, numbers, and hyphens only in page slugs.',
            'no_special_chars' => 'Remove HTML special characters (<, >, &, " etc.) from the title.',
            default            => 'Review this item.',
        };
    }

    /* ═══════════════════════════════════════════════════════════
       GRADE
    ═══════════════════════════════════════════════════════════ */
    private static function grade(int $score): string
    {
        return match (true) {
            $score >= 90 => 'A+',
            $score >= 80 => 'A',
            $score >= 70 => 'B',
            $score >= 60 => 'C',
            $score >= 50 => 'D',
            default      => 'F',
        };
    }
}
