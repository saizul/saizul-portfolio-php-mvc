<?php
use App\Core\SEOScorer;

$pages = [
    ['slug'=>'home',        'label'=>'Home',         'icon'=>'🏠'],
    ['slug'=>'about',       'label'=>'About',         'icon'=>'👤'],
    ['slug'=>'ai-projects', 'label'=>'AI Projects',   'icon'=>'🤖'],
    ['slug'=>'experience',  'label'=>'Experience',    'icon'=>'💼'],
    ['slug'=>'skills',      'label'=>'Skills',        'icon'=>'⚡'],
    ['slug'=>'portfolio',   'label'=>'Portfolio',     'icon'=>'🗂️'],
    ['slug'=>'contact',     'label'=>'Contact',       'icon'=>'✉️'],
    ['slug'=>'blog',        'label'=>'Blog',          'icon'=>'📝'],
];

// Load seo_meta rows for all pages
$seoModel = new \App\Models\SEOMeta();
$seoData  = [];
try {
    foreach ($pages as $p) {
        $seoData[$p['slug']] = $seoModel->findBySlug($p['slug']);
    }
} catch (\Throwable) {}

// Score all pages
$scores = [];
foreach ($pages as $p) {
    $scores[$p['slug']] = SEOScorer::analyse($seoData[$p['slug']] ?? null, $p['slug']);
}
$avgScore = (int) round(array_sum(array_column($scores, 'score')) / count($scores));

// Alerts from redirects
$alert = $_GET['sitemap'] ?? ($_GET['robots'] ?? null);
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">SEO Manager</h1>
        <p class="admin-page-subtitle">Manage meta tags, Open Graph, Twitter Cards, and schema for every page.</p>
    </div>
    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
        <div class="seo-avg-score">
            <span class="seo-avg-num" style="color:<?= $avgScore >= 80 ? '#6ee7b7' : ($avgScore >= 60 ? '#fbbf24' : '#f87171') ?>"><?= $avgScore ?></span>
            <span class="seo-avg-label">Avg. SEO Score</span>
        </div>
    </div>
</div>

<?php if ($alert === 'regenerated'): ?>
<div class="admin-alert success">✓ Sitemap regenerated successfully.</div>
<?php elseif ($alert === 'saved'): ?>
<div class="admin-alert success">✓ Robots.txt saved successfully.</div>
<?php endif; ?>

<!-- Page SEO Cards Grid -->
<div class="seo-page-grid">
    <?php foreach ($pages as $p):
        $s     = $scores[$p['slug']];
        $score = $s['score'];
        $grade = $s['grade'];
        $failCount = count(array_filter($s['checks'], fn($c) => !$c['pass']));
        $color = $score >= 90 ? '#6ee7b7' : ($score >= 70 ? '#fbbf24' : ($score >= 50 ? '#f97316' : '#f87171'));
        $circumference = 125.66;
        $dash = round($circumference * $score / 100, 1);
    ?>
    <a href="/admin/seo/<?= $p['slug'] ?>" class="seo-page-card">
        <div class="seo-page-card-top">
            <div class="seo-page-icon"><?= $p['icon'] ?></div>
            <div class="seo-page-info">
                <div class="seo-page-name"><?= htmlspecialchars($p['label']) ?></div>
                <div class="seo-page-url">/<?= htmlspecialchars($p['slug'] === 'home' ? '' : $p['slug']) ?></div>
            </div>
            <div class="seo-ring-wrap">
                <svg viewBox="0 0 48 48" width="52" height="52">
                    <circle cx="24" cy="24" r="20" stroke="rgba(255,255,255,0.08)" stroke-width="4" fill="none"/>
                    <circle cx="24" cy="24" r="20"
                        stroke="<?= $color ?>" stroke-width="4" fill="none"
                        stroke-dasharray="<?= $dash ?> <?= $circumference ?>"
                        stroke-linecap="round"
                        transform="rotate(-90 24 24)"/>
                </svg>
                <div class="seo-ring-text">
                    <span style="color:<?= $color ?>;font-weight:800;font-size:11px;"><?= $score ?></span>
                </div>
            </div>
        </div>
        <div class="seo-page-card-bottom">
            <span class="seo-grade-badge" style="background:<?= $color ?>22;color:<?= $color ?>">Grade <?= $grade ?></span>
            <?php if ($failCount > 0): ?>
            <span class="seo-issues"><?= $failCount ?> issue<?= $failCount !== 1 ? 's' : '' ?></span>
            <?php else: ?>
            <span class="seo-ok">✓ All checks pass</span>
            <?php endif; ?>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<!-- Robots.txt editor -->
<div class="admin-card" style="margin-top:32px;">
    <div class="admin-card-header">
        <h3>Robots.txt</h3>
        <div style="display:flex;gap:8px;">
            <a href="/robots.txt" target="_blank" class="btn btn-ghost btn-sm">View Live</a>
        </div>
    </div>
    <div class="admin-card-body">
        <form method="POST" action="/admin/robots/save">
            <?= \App\Core\Auth::csrfField() ?>
            <div class="form-group">
                <label class="form-label">Robots Rules</label>
                <textarea name="robots_rules" class="form-textarea form-mono" rows="12"
                    placeholder="User-agent: *&#10;Allow: /&#10;Disallow: /admin&#10;&#10;Sitemap: https://saizulamin.com/sitemap.xml"><?= htmlspecialchars((new \App\Models\Setting())->get('robots_rules') ?? '') ?></textarea>
                <p class="form-hint">The <code>Sitemap:</code> line is appended automatically if missing.</p>
            </div>
            <div style="display:flex;gap:12px;margin-top:16px;">
                <button type="submit" class="btn btn-primary">Save & Write robots.txt</button>
            </div>
        </form>
    </div>
</div>

<!-- Sitemap section -->
<div class="admin-card" style="margin-top:24px;">
    <div class="admin-card-header">
        <h3>XML Sitemap</h3>
        <div style="display:flex;gap:8px;">
            <a href="/sitemap.xml" target="_blank" class="btn btn-ghost btn-sm">View Live</a>
            <form method="POST" action="/admin/sitemap/regenerate" style="display:inline;">
                <?= \App\Core\Auth::csrfField() ?>
                <button type="submit" class="btn btn-outline btn-sm">↺ Regenerate</button>
            </form>
        </div>
    </div>
    <div class="admin-card-body">
        <p style="color:#94a3b8;font-size:0.875rem;">
            The sitemap is auto-generated from all public pages, blog posts, and projects.
            It is regenerated on each content publish, or manually here.
        </p>
    </div>
</div>

<style>
.seo-avg-score { text-align:right; }
.seo-avg-num   { font-size:2.5rem;font-weight:800;line-height:1; }
.seo-avg-label { display:block;font-size:0.75rem;color:#64748b;text-transform:uppercase;letter-spacing:0.08em; }

.seo-page-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-top:8px; }
.seo-page-card { display:flex;flex-direction:column;gap:12px;background:#0d1b2a;border:1px solid #1e3040;border-radius:14px;padding:18px 20px;text-decoration:none;transition:all 0.25s;cursor:pointer; }
.seo-page-card:hover { border-color:#c0392b44;box-shadow:0 8px 32px rgba(192,57,43,0.1);transform:translateY(-2px); }
.seo-page-card-top   { display:flex;align-items:center;gap:12px; }
.seo-page-icon  { font-size:1.4rem;width:36px;text-align:center; }
.seo-page-info  { flex:1;min-width:0; }
.seo-page-name  { font-size:0.9375rem;font-weight:600;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.seo-page-url   { font-size:0.75rem;color:#475569; }
.seo-ring-wrap  { position:relative;width:52px;height:52px;flex-shrink:0; }
.seo-ring-text  { position:absolute;inset:0;display:flex;align-items:center;justify-content:center; }
.seo-page-card-bottom { display:flex;align-items:center;justify-content:space-between; }
.seo-grade-badge { font-size:0.7rem;font-weight:700;padding:3px 8px;border-radius:99px; }
.seo-issues { font-size:0.75rem;color:#f87171; }
.seo-ok     { font-size:0.75rem;color:#6ee7b7; }
.admin-alert.success { background:rgba(110,231,183,0.1);border:1px solid rgba(110,231,183,0.3);color:#6ee7b7;padding:12px 16px;border-radius:8px;margin-bottom:20px; }
.form-mono { font-family:'JetBrains Mono','Courier New',monospace;font-size:0.8125rem; }
</style>
