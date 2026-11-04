<?php
use App\Core\Auth;
use App\Core\SEOScorer;

// Run SEO score on existing data
$scoreData = SEOScorer::analyse($seo ?? null, $slug ?? '');
$score     = $scoreData['score'];
$grade     = $scoreData['grade'];
$gradeColor = match(true) {
    $score >= 90 => '#6ee7b7',
    $score >= 70 => '#fbbf24',
    $score >= 50 => '#f97316',
    default      => '#f87171',
};

$title = ($seo['meta_title'] ?? '') ?: ucwords(str_replace(['-','_'], ' ', $slug ?? ''));
?>
<div class="admin-page-header">
    <div>
        <div class="admin-breadcrumb">
            <a href="/admin/seo">SEO Manager</a> <span>/</span>
            <span><?= htmlspecialchars(ucwords(str_replace('-', ' ', $slug ?? ''))) ?></span>
        </div>
        <h1 class="admin-page-title">Edit SEO: <?= htmlspecialchars($title) ?></h1>
    </div>
    <!-- SEO Score Badge -->
    <div class="seo-score-badge" style="--score-color:<?= $gradeColor ?>">
        <div class="seo-score-ring">
            <svg viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="24" r="20" stroke="rgba(255,255,255,0.1)" stroke-width="4"/>
                <circle cx="24" cy="24" r="20"
                    stroke="<?= $gradeColor ?>" stroke-width="4"
                    stroke-dasharray="<?= round(125.66 * $score / 100, 1) ?> 125.66"
                    stroke-linecap="round"
                    transform="rotate(-90 24 24)"/>
            </svg>
            <div class="seo-score-inner">
                <span class="seo-score-num"><?= $score ?></span>
                <span class="seo-score-grade"><?= $grade ?></span>
            </div>
        </div>
        <span class="seo-score-label">SEO Score</span>
    </div>
</div>

<!-- Tabs -->
<div class="admin-tabs" id="seoTabs">
    <button class="admin-tab active" data-tab="meta">Meta Tags</button>
    <button class="admin-tab" data-tab="og">Open Graph</button>
    <button class="admin-tab" data-tab="twitter">Twitter Card</button>
    <button class="admin-tab" data-tab="schema">Schema / JSON-LD</button>
    <button class="admin-tab" data-tab="audit">SEO Audit</button>
</div>

<form method="POST" action="/admin/seo/<?= htmlspecialchars($slug ?? '') ?>/save" id="seoForm">
    <?= Auth::csrfField() ?>

<!-- ─── META TAGS TAB ─── -->
<div class="tab-panel active" id="tab-meta">
    <div class="admin-card">
        <div class="admin-card-header"><h3>Meta Tags</h3></div>
        <div class="admin-card-body" style="display:grid;gap:24px;">

            <div class="form-group">
                <label class="form-label">Meta Title
                    <span class="form-counter" id="titleCounter">0 / 60</span>
                </label>
                <input type="text" name="meta_title" id="metaTitle" class="form-input"
                    value="<?= htmlspecialchars($seo['meta_title'] ?? '') ?>"
                    maxlength="80"
                    placeholder="Saizul Amin — AI Engineer & Full-Stack Developer">
                <div class="serp-preview" id="serpTitle"></div>
                <p class="form-hint">Optimal: 50–60 characters. Shown in browser tab and Google results.</p>
            </div>

            <div class="form-group">
                <label class="form-label">Meta Description
                    <span class="form-counter" id="descCounter">0 / 160</span>
                </label>
                <textarea name="meta_description" id="metaDesc" class="form-textarea" rows="3"
                    maxlength="200"
                    placeholder="Building intelligent AI systems and scalable applications..."><?= htmlspecialchars($seo['meta_description'] ?? '') ?></textarea>
                <p class="form-hint">Optimal: 120–160 characters. Shown in Google search results.</p>
            </div>

            <div class="form-group-row">
                <div class="form-group">
                    <label class="form-label">Canonical URL</label>
                    <input type="url" name="canonical_url" class="form-input"
                        value="<?= htmlspecialchars($seo['canonical_url'] ?? '') ?>"
                        placeholder="https://saizulamin.com/<?= htmlspecialchars($slug ?? '') ?>">
                    <p class="form-hint">Leave blank to auto-generate.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Robots Directive</label>
                    <select name="robots_directive" class="form-select">
                        <?php
                        $robotsOptions = ['index, follow','noindex, follow','index, nofollow','noindex, nofollow'];
                        $current = $seo['robots_directive'] ?? 'index, follow';
                        foreach ($robotsOptions as $opt):
                        ?>
                        <option value="<?= $opt ?>" <?= $current === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

        </div>
    </div>

    <!-- SERP Preview -->
    <div class="admin-card">
        <div class="admin-card-header"><h3>SERP Preview</h3></div>
        <div class="admin-card-body">
            <div class="serp-preview-box" id="serpPreview">
                <div class="serp-url">saizulamin.com › <?= htmlspecialchars($slug ?? '') ?></div>
                <div class="serp-title" id="serpPreviewTitle"><?= htmlspecialchars($seo['meta_title'] ?? $title) ?></div>
                <div class="serp-desc" id="serpPreviewDesc"><?= htmlspecialchars($seo['meta_description'] ?? '') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- ─── OPEN GRAPH TAB ─── -->
<div class="tab-panel" id="tab-og">
    <div class="admin-card">
        <div class="admin-card-header"><h3>Open Graph</h3><p class="admin-card-subtitle">Controls how this page appears when shared on Facebook, LinkedIn, WhatsApp etc.</p></div>
        <div class="admin-card-body" style="display:grid;gap:24px;">
            <div class="form-group">
                <label class="form-label">OG Title</label>
                <input type="text" name="og_title" class="form-input" value="<?= htmlspecialchars($seo['og_title'] ?? '') ?>" placeholder="Inherits from Meta Title">
            </div>
            <div class="form-group">
                <label class="form-label">OG Description</label>
                <textarea name="og_description" class="form-textarea" rows="3" placeholder="Inherits from Meta Description"><?= htmlspecialchars($seo['og_description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">OG Image URL</label>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input type="text" name="og_image" id="ogImageInput" class="form-input" value="<?= htmlspecialchars($seo['og_image'] ?? '') ?>" placeholder="/assets/images/og-default.jpg">
                    <a href="/admin/media" target="_blank" class="btn btn-ghost btn-sm" style="white-space:nowrap;">Browse Media</a>
                </div>
                <p class="form-hint">Recommended: 1200 × 630px, JPG or PNG.</p>
                <div id="ogImagePreview" style="margin-top:12px;<?= empty($seo['og_image']) ? 'display:none' : '' ?>">
                    <img src="<?= htmlspecialchars($seo['og_image'] ?? '') ?>" alt="OG preview" style="max-width:400px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);">
                </div>
            </div>
        </div>
    </div>
    <!-- OG Social Preview -->
    <div class="admin-card">
        <div class="admin-card-header"><h3>Social Card Preview</h3></div>
        <div class="admin-card-body">
            <div class="og-preview-card" id="ogPreview">
                <div class="og-preview-image" id="ogPreviewImg" style="<?= !empty($seo['og_image']) ? 'background-image:url('.htmlspecialchars($seo['og_image'] ?? '').')' : '' ?>"></div>
                <div class="og-preview-body">
                    <div class="og-preview-domain">saizulamin.com</div>
                    <div class="og-preview-title" id="ogPreviewTitle"><?= htmlspecialchars($seo['og_title'] ?? $title) ?></div>
                    <div class="og-preview-desc" id="ogPreviewDesc"><?= htmlspecialchars($seo['og_description'] ?? '') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ─── TWITTER CARD TAB ─── -->
<div class="tab-panel" id="tab-twitter">
    <div class="admin-card">
        <div class="admin-card-header"><h3>Twitter Card</h3><p class="admin-card-subtitle">Twitter automatically uses OG tags if no Twitter-specific tags are set.</p></div>
        <div class="admin-card-body">
            <div class="admin-info-banner">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Twitter Cards inherit your OG title, description and image automatically.
                The site-wide Twitter handle (<code>@saizulamin</code>) is injected globally.
                No extra fields needed unless you want to override specifically for Twitter.
            </div>
            <div style="margin-top:20px;">
                <div class="twitter-card-preview" id="twitterPreview">
                    <div class="twitter-card-image" id="twImgPreview" style="<?= !empty($seo['og_image']) ? 'background-image:url('.htmlspecialchars($seo['og_image'] ?? '').')' : '' ?>"></div>
                    <div class="twitter-card-body">
                        <div class="twitter-card-title" id="twTitlePreview"><?= htmlspecialchars($seo['og_title'] ?? $title) ?></div>
                        <div class="twitter-card-desc" id="twDescPreview"><?= htmlspecialchars($seo['og_description'] ?? '') ?></div>
                        <div class="twitter-card-domain">saizulamin.com</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ─── SCHEMA TAB ─── -->
<div class="tab-panel" id="tab-schema">
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Schema / JSON-LD</h3>
            <div style="display:flex;gap:8px;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="loadSchema('person')">Person</button>
                <button type="button" class="btn btn-ghost btn-sm" onclick="loadSchema('article')">Article</button>
                <button type="button" class="btn btn-ghost btn-sm" onclick="loadSchema('software')">SoftwareApp</button>
                <button type="button" class="btn btn-ghost btn-sm" onclick="loadSchema('faq')">FAQ</button>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="form-group">
                <label class="form-label">Custom JSON-LD
                    <span id="schemaValidBadge" style="margin-left:8px;font-size:11px;padding:2px 8px;border-radius:99px;background:rgba(110,231,183,0.12);color:#6ee7b7;">✓ Valid JSON</span>
                </label>
                <textarea name="schema_json" id="schemaJson" class="form-textarea form-mono" rows="18"
                    placeholder='{ "@context": "https://schema.org", "@type": "WebPage", ... }'><?= htmlspecialchars($seo['schema_json'] ?? '') ?></textarea>
                <p class="form-hint">This will be injected as a <code>&lt;script type="application/ld+json"&gt;</code> in the page head. Person and WebSite schemas are injected globally.</p>
            </div>
        </div>
    </div>
</div>

<!-- ─── AUDIT TAB ─── -->
<div class="tab-panel" id="tab-audit">
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>SEO Audit</h3>
            <div class="seo-score-summary">
                <span style="color:<?= $gradeColor ?>;font-size:2rem;font-weight:800;"><?= $score ?></span>
                <span style="color:#94a3b8;font-size:0.875rem;margin-left:4px;">/ 100 · Grade <?= $grade ?></span>
            </div>
        </div>
        <div class="admin-card-body">
            <?php foreach ($scoreData['groups'] as $groupName => $checks): ?>
            <div class="audit-group">
                <div class="audit-group-header">
                    <?php
                    $pass  = count(array_filter($checks, fn($c) => $c['pass']));
                    $total = count($checks);
                    $pct   = $total > 0 ? round($pass/$total*100) : 0;
                    ?>
                    <span class="audit-group-name"><?= htmlspecialchars($groupName) ?></span>
                    <span class="audit-group-score" style="color:<?= $pct >= 80 ? '#6ee7b7' : ($pct >= 50 ? '#fbbf24' : '#f87171') ?>">
                        <?= $pass ?>/<?= $total ?>
                    </span>
                </div>
                <div class="audit-checks">
                    <?php foreach ($checks as $check): ?>
                    <div class="audit-check <?= $check['pass'] ? 'pass' : 'fail' ?>">
                        <span class="audit-icon"><?= $check['pass'] ? '✓' : '✗' ?></span>
                        <div class="audit-check-content">
                            <span class="audit-check-label"><?= htmlspecialchars($check['label']) ?></span>
                            <?php if (!$check['pass'] && $check['tip']): ?>
                            <span class="audit-tip"><?= htmlspecialchars($check['tip']) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="audit-weight">+<?= $check['weight'] ?>pts</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Save bar -->
<div class="admin-save-bar">
    <a href="/admin/seo" class="btn btn-ghost">← Back to SEO</a>
    <button type="submit" class="btn btn-primary">Save SEO Settings</button>
</div>

</form>

<style>
/* SEO Score badge */
.seo-score-badge { display:flex;flex-direction:column;align-items:center;gap:4px; }
.seo-score-ring  { position:relative;width:64px;height:64px; }
.seo-score-ring svg { width:100%;height:100%;transform:rotate(0); }
.seo-score-inner { position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center; }
.seo-score-num   { font-size:1rem;font-weight:800;color:#fff;line-height:1; }
.seo-score-grade { font-size:0.6rem;color:var(--score-color);font-weight:700; }
.seo-score-label { font-size:0.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em; }

/* SERP preview */
.serp-preview-box { background:#1e293b;border-radius:8px;padding:16px 20px;max-width:600px;font-family:'Arial',sans-serif; }
.serp-url   { font-size:12px;color:#4ade80;margin-bottom:2px; }
.serp-title { font-size:18px;color:#4e9cf5;margin-bottom:4px;cursor:pointer; }
.serp-title:hover { text-decoration:underline; }
.serp-desc  { font-size:13px;color:#94a3b8;line-height:1.5; }

/* OG preview */
.og-preview-card { max-width:480px;border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,0.1); }
.og-preview-image { height:220px;background:#1a2f35 center/cover no-repeat; }
.og-preview-body  { padding:12px 16px;background:#0f1923; }
.og-preview-domain{ font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px; }
.og-preview-title { font-size:15px;font-weight:700;color:#fff;margin-bottom:4px; }
.og-preview-desc  { font-size:13px;color:#94a3b8; }

/* Twitter card preview */
.twitter-card-preview { max-width:480px;border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.1); }
.twitter-card-image { height:240px;background:#1a2f35 center/cover no-repeat; }
.twitter-card-body  { padding:12px 16px;background:#0f1923; }
.twitter-card-title { font-size:15px;font-weight:700;color:#fff;margin-bottom:4px; }
.twitter-card-desc  { font-size:13px;color:#8b98a5;margin-bottom:4px; }
.twitter-card-domain{ font-size:12px;color:#64748b; }

/* Audit */
.audit-group { margin-bottom:24px; }
.audit-group-header { display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.08);margin-bottom:8px; }
.audit-group-name   { font-size:0.8125rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:#64748b; }
.audit-group-score  { font-size:0.875rem;font-weight:700; }
.audit-checks { display:grid;gap:6px; }
.audit-check { display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border-radius:8px;background:rgba(255,255,255,0.02); }
.audit-check.pass { border-left:3px solid #6ee7b7; }
.audit-check.fail { border-left:3px solid #f87171; }
.audit-icon  { font-size:0.875rem;margin-top:1px; }
.audit-check.pass .audit-icon { color:#6ee7b7; }
.audit-check.fail .audit-icon { color:#f87171; }
.audit-check-content{ flex:1;display:flex;flex-direction:column;gap:2px; }
.audit-check-label  { font-size:0.875rem;color:#e2e8f0; }
.audit-tip   { font-size:0.75rem;color:#94a3b8; }
.audit-weight{ font-size:0.7rem;color:#475569;white-space:nowrap;align-self:center; }

.admin-info-banner { background:rgba(96,165,250,0.08);border:1px solid rgba(96,165,250,0.2);border-radius:8px;padding:12px 16px;display:flex;gap:10px;align-items:flex-start;font-size:0.875rem;color:#94a3b8; }
.form-mono { font-family:'JetBrains Mono','Courier New',monospace;font-size:0.8125rem; }
.form-counter { float:right;font-size:0.75rem;color:#64748b; }
.admin-tabs { display:flex;gap:2px;margin-bottom:24px;border-bottom:1px solid rgba(255,255,255,0.08);padding-bottom:0; }
.admin-tab  { background:none;border:none;color:#64748b;padding:10px 16px;cursor:pointer;font-size:0.875rem;font-weight:500;border-bottom:2px solid transparent;transition:all 0.2s; }
.admin-tab:hover { color:#e2e8f0; }
.admin-tab.active{ color:#e2e8f0;border-bottom-color:#c0392b; }
.tab-panel { display:none; }
.tab-panel.active { display:block; }
.admin-save-bar { display:flex;justify-content:space-between;align-items:center;padding:20px 0;margin-top:32px;border-top:1px solid rgba(255,255,255,0.08); }
.form-group-row { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
@media(max-width:640px){ .form-group-row{grid-template-columns:1fr;} }
</style>

<script>
/* ── Tabs ── */
document.querySelectorAll('.admin-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.admin-tab,.tab-panel').forEach(el => el.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('tab-' + tab.dataset.tab)?.classList.add('active');
    });
});

/* ── Character counters ── */
function countChars(inputId, counterId, max) {
    const el = document.getElementById(inputId);
    const ct = document.getElementById(counterId);
    if (!el || !ct) return;
    const update = () => {
        const len = el.value.length;
        ct.textContent = `${len} / ${max}`;
        ct.style.color = len > max ? '#f87171' : len >= max * 0.85 ? '#fbbf24' : '#64748b';
    };
    el.addEventListener('input', update);
    update();
}
countChars('metaTitle', 'titleCounter', 60);
countChars('metaDesc',  'descCounter',  160);

/* ── Live SERP preview ── */
const metaTitle = document.getElementById('metaTitle');
const metaDesc  = document.getElementById('metaDesc');
const serpTitle = document.getElementById('serpPreviewTitle');
const serpDesc  = document.getElementById('serpPreviewDesc');
if (metaTitle && serpTitle) {
    metaTitle.addEventListener('input', () => { serpTitle.textContent = metaTitle.value || '(no title set)'; });
    metaDesc.addEventListener('input',  () => { serpDesc.textContent  = metaDesc.value  || '(no description set)'; });
}

/* ── Live OG preview ── */
const ogImageInput = document.getElementById('ogImageInput');
if (ogImageInput) {
    ogImageInput.addEventListener('input', () => {
        const val = ogImageInput.value.trim();
        const preview = document.getElementById('ogPreviewImg');
        const imgBlock = document.getElementById('ogImagePreview');
        if (preview) preview.style.backgroundImage = val ? `url(${val})` : '';
        if (imgBlock) {
            imgBlock.style.display = val ? '' : 'none';
            const img = imgBlock.querySelector('img');
            if (img) img.src = val;
        }
        // Also update twitter preview
        const twImg = document.getElementById('twImgPreview');
        if (twImg) twImg.style.backgroundImage = val ? `url(${val})` : '';
    });
}

/* ── JSON-LD validation ── */
const schemaJson = document.getElementById('schemaJson');
const schemaBadge = document.getElementById('schemaValidBadge');
if (schemaJson) {
    schemaJson.addEventListener('input', () => {
        const v = schemaJson.value.trim();
        if (!v) { schemaBadge.style.display='none'; return; }
        try {
            JSON.parse(v);
            schemaBadge.textContent='✓ Valid JSON';
            schemaBadge.style.background='rgba(110,231,183,0.12)';
            schemaBadge.style.color='#6ee7b7';
        } catch {
            schemaBadge.textContent='✗ Invalid JSON';
            schemaBadge.style.background='rgba(248,113,113,0.12)';
            schemaBadge.style.color='#f87171';
        }
        schemaBadge.style.display='';
    });
}

/* ── Schema templates ── */
const templates = {
    person: {
        "@context":"https://schema.org","@type":"Person","name":"Saizul Amin",
        "url":"https://saizulamin.com","jobTitle":"AI Engineer & Full-Stack Developer",
        "sameAs":["https://github.com/saizulamin","https://linkedin.com/in/saizulamin"]
    },
    article: {
        "@context":"https://schema.org","@type":"BlogPosting",
        "headline":"Article Title","datePublished":"2024-01-01","dateModified":"2024-01-01",
        "author":{"@type":"Person","name":"Saizul Amin"},
        "publisher":{"@type":"Person","name":"Saizul Amin"}
    },
    software: {
        "@context":"https://schema.org","@type":"SoftwareApplication",
        "name":"Project Name","applicationCategory":"DeveloperApplication",
        "operatingSystem":"Web","author":{"@type":"Person","name":"Saizul Amin"}
    },
    faq: {
        "@context":"https://schema.org","@type":"FAQPage",
        "mainEntity":[{"@type":"Question","name":"Question here?","acceptedAnswer":{"@type":"Answer","text":"Answer here."}}]
    }
};
function loadSchema(type) {
    if (schemaJson && templates[type]) {
        schemaJson.value = JSON.stringify(templates[type], null, 2);
        schemaJson.dispatchEvent(new Event('input'));
    }
}
</script>
