<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?= \App\Core\SEO::render() ?>
  <?php
  // Inject theme CSS variables (from cache or DB)
  $themeCssFile = BASE_PATH . '/storage/cache/theme.css';
  if (is_readable($themeCssFile)) {
      echo '<style id="theme-vars">' . file_get_contents($themeCssFile) . '</style>';
  } else {
      try {
          $themeModel = new \App\Models\ThemeSettings();
          $themeSettings = $themeModel->all();
          echo '<style id="theme-vars">' . $themeModel->toCssVariables($themeSettings) . '</style>';
          // Cache it for next request
          @file_put_contents($themeCssFile, $themeModel->toCssVariables($themeSettings));
      } catch (\Throwable $e) {
          // Fallback minimal variables
          echo '<style id="theme-vars">:root{--color-crimson:#c0392b;--color-teal:#6ee7b7;--color-bg-dark:#0a0a0a;--color-bg-card:#111111;--color-text-primary:#f1f5f9;--color-text-muted:#94a3b8;--color-border:#1e293b;--radius:12px;--anim-speed:1;}</style>';
      }
  }
  // Load Google Fonts based on theme settings
  $headingFont = isset($themeSettings) ? ($themeSettings['font_heading'] ?? 'Inter') : 'Inter';
  $bodyFont    = isset($themeSettings) ? ($themeSettings['font_body']    ?? 'Inter') : 'Inter';
  $monoFont    = isset($themeSettings) ? ($themeSettings['font_mono']    ?? 'JetBrains Mono') : 'JetBrains Mono';
  $fontsToLoad = array_unique([$headingFont, $bodyFont]);
  $googleQuery = implode('&family=', array_map(fn($f) => urlencode($f) . ':wght@400;500;600;700', $fontsToLoad));
  ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=<?= $googleQuery ?>&family=<?= urlencode($monoFont) ?>&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class', theme: { extend: {} } }
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js" defer></script>
  <script src="https://unpkg.com/lenis@1.1.10/dist/lenis.min.js" defer></script>
  <script src="<?= asset('js/core.js') ?>" defer type="module"></script>
  <?php if (($pageScript ?? '') === 'home'): ?>
  <!-- Preload hero portrait for LCP — fetchpriority ensures browser fetches it early -->
  <link rel="preload" as="image" href="<?= asset('images/portrait.webp') ?>" fetchpriority="high" type="image/webp">
  <?php endif; ?>
</head>
<body class="dark bg-black text-white antialiased overflow-x-hidden" data-page="<?= htmlspecialchars($pageScript ?? '', ENT_QUOTES) ?>">

<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[200] bg-white text-black px-4 py-2 rounded-lg font-medium">
  Skip to content
</a>

<!-- ── Navigation ─────────────────────────────────────── -->
<?php
$currentPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

// Pull menu from DB, fall back to static list
try {
    $menuModel = new \App\Models\MenuItem();
    $menuTree  = $menuModel->tree();
} catch (\Throwable $e) {
    $menuTree = [];
}
if (empty($menuTree)) {
    $menuTree = [
        ['id'=>0,'label'=>'About',       'url'=>'/about',       'target'=>'_self','children'=>[]],
        ['id'=>0,'label'=>'AI Projects', 'url'=>'/ai-projects', 'target'=>'_self','children'=>[]],
        ['id'=>0,'label'=>'Experience',  'url'=>'/experience',  'target'=>'_self','children'=>[]],
        ['id'=>0,'label'=>'Skills',      'url'=>'/skills',      'target'=>'_self','children'=>[]],
        ['id'=>0,'label'=>'Portfolio',   'url'=>'/portfolio',   'target'=>'_self','children'=>[]],
        ['id'=>0,'label'=>'Blog',        'url'=>'/blog',        'target'=>'_self','children'=>[]],
    ];
}

// Pull footer blocks from DB
try {
    $footerModel  = new \App\Models\FooterBlock();
    $footerBlocks = $footerModel->allActive();
} catch (\Throwable $e) { $footerBlocks = []; }

// Pull homepage sections (available to home view)
try {
    $hpModel      = new \App\Models\HomepageSection();
    $hpSections   = $hpModel->enabled();
    $hpByKey      = [];
    foreach ($hpSections as $s) $hpByKey[$s['section_key']] = $s;
} catch (\Throwable $e) { $hpSections = []; $hpByKey = []; }

// Legacy alias for any view that still uses $navLinks
$navLinks = [];
foreach ($menuTree as $item) $navLinks[$item['url']] = $item['label'];
?>
<nav class="nav" id="nav">
  <div class="nav-inner">
    <a href="<?= url('/') ?>" class="nav-logo">
      Saizul<span>.</span>
    </a>

    <ul class="nav-links">
      <?php foreach ($menuTree as $item): ?>
      <li class="<?= !empty($item['children']) ? 'has-dropdown' : '' ?>">
        <a href="<?= url($item['url']) ?>"
           target="<?= htmlspecialchars($item['target'] ?? '_self') ?>"
           class="<?= $currentPath === $item['url'] ? 'active' : '' ?>">
          <?= htmlspecialchars($item['label']) ?>
          <?php if (!empty($item['children'])): ?><span class="dropdown-arrow">▾</span><?php endif; ?>
        </a>
        <?php if (!empty($item['children'])): ?>
        <ul class="dropdown-menu">
          <?php foreach ($item['children'] as $child): ?>
          <li><a href="<?= url($child['url']) ?>" target="<?= htmlspecialchars($child['target'] ?? '_self') ?>"><?= htmlspecialchars($child['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>

    <a href="<?= url('/contact') ?>" class="nav-cta hidden md:inline-flex">
      Let's Talk
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M7 17L17 7M17 7H7M17 7v10"/>
      </svg>
    </a>

    <button class="nav-hamburger" id="nav-hamburger" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- Mobile overlay menu -->
<div class="nav-mobile" id="nav-mobile">
  <?php foreach (array_merge(['/'] + array_keys($navLinks), ['/contact']) as $href):
    $labels = array_merge(['/' => 'Home'], $navLinks, ['/contact' => 'Contact']);
    $label = $labels[$href] ?? '';
    if (!$label) continue;
  ?>
  <a href="<?= url($href) ?>" onclick="closeMobileMenu()"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<!-- ── Page content ───────────────────────────────────── -->
<main id="main-content">
  <?= $content ?>
</main>

<!-- ── Footer ─────────────────────────────────────────── -->
<footer class="footer">
  <div class="container">
    <div class="footer-inner">
      <!-- Brand col -->
      <div class="footer-brand">
        <a href="<?= url('/') ?>" class="nav-logo">Saizul<span>.</span></a>
        <?php
        $aboutBlock = null;
        foreach ($footerBlocks as $fb) { if ($fb['block_key'] === 'about') { $aboutBlock = $fb; break; } }
        ?>
        <p class="footer-tagline">
          <?= $aboutBlock ? nl2br(htmlspecialchars($aboutBlock['content'])) : 'AI Engineer &amp; Full Stack Developer building intelligent systems for global impact.' ?>
        </p>
        <div class="footer-social">
          <?php
          $socialBlock = null;
          foreach ($footerBlocks as $fb) { if ($fb['block_key'] === 'social') { $socialBlock = $fb; break; } }
          if ($socialBlock && $socialBlock['content']): echo strip_tags($socialBlock['content'], '<a><svg><path><span><i><img><br>');
          else: ?>
          <a href="https://github.com/saizulamin" target="_blank" rel="noopener" aria-label="GitHub">GH</a>
          <a href="https://linkedin.com/in/saizulamin" target="_blank" rel="noopener" aria-label="LinkedIn">LI</a>
          <a href="https://twitter.com/saizulamin" target="_blank" rel="noopener" aria-label="Twitter">TW</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Dynamic footer blocks (excluding reserved keys) -->
      <?php
      $reserved = ['about','social'];
      $dynBlocks = array_filter($footerBlocks, fn($b) => !in_array($b['block_key'], $reserved));
      foreach ($dynBlocks as $block): ?>
      <div class="footer-col">
        <?php if ($block['title']): ?>
        <h3 class="footer-col-title"><?= htmlspecialchars($block['title']) ?></h3>
        <?php endif; ?>
        <div class="footer-col-content">
          <?= $block['content'] /* intentionally unescaped — admin-controlled HTML */ ?>
        </div>
      </div>
      <?php endforeach; ?>

      <!-- Fallback nav links if no footer blocks configured -->
      <?php if (empty($dynBlocks)): ?>
      <div class="footer-col">
        <h3 class="footer-col-title">Navigation</h3>
        <ul class="footer-links">
          <?php foreach ($menuTree as $item): ?>
          <li><a href="<?= url($item['url']) ?>"><?= htmlspecialchars($item['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="footer-col">
        <h3 class="footer-col-title">Contact</h3>
        <ul class="footer-links">
          <li><a href="<?= url('/contact') ?>">Get in Touch</a></li>
          <li><a href="mailto:saizulamin@gmail.com">saizulamin@gmail.com</a></li>
        </ul>
      </div>
      <?php endif; ?>
    </div>

    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> Saizul Amin. All rights reserved.</p>
      <p class="footer-legal">
        <a href="<?= url('/privacy') ?>">Privacy</a>
        <a href="<?= url('/sitemap.xml') ?>">Sitemap</a>
      </p>
    </div>
  </div>
</footer>

<script>
// Nav scroll effect
const nav = document.getElementById('nav');
let lastY = 0;
window.addEventListener('scroll', () => {
  const y = window.scrollY;
  nav.classList.toggle('scrolled', y > 40);
  lastY = y;
}, { passive: true });

// Mobile menu
const hamburger = document.getElementById('nav-hamburger');
const mobileMenu = document.getElementById('nav-mobile');

function closeMobileMenu() {
  hamburger.classList.remove('open');
  mobileMenu.classList.remove('open');
  document.body.style.overflow = '';
}

hamburger.addEventListener('click', () => {
  const open = hamburger.classList.toggle('open');
  mobileMenu.classList.toggle('open', open);
  document.body.style.overflow = open ? 'hidden' : '';
});

// Reveal on scroll
const revealEls = document.querySelectorAll('.reveal, .reveal-left');
if (revealEls.length && 'IntersectionObserver' in window) {
  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
  revealEls.forEach(el => io.observe(el));
}

// Skill bar animation
const skillBars = document.querySelectorAll('.skill-bar-fill[data-pct]');
if (skillBars.length && 'IntersectionObserver' in window) {
  const sio = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.width = e.target.dataset.pct + '%';
        sio.unobserve(e.target);
      }
    });
  }, { threshold: 0.3 });
  skillBars.forEach(el => sio.observe(el));
}
</script>
</body>
</html>
<script>
// Live search
(function(){
  const input   = document.getElementById('nav-search');
  const results = document.getElementById('nav-search-results');
  if (!input || !results) return;
  let timer;
  input.addEventListener('input', function(){
    clearTimeout(timer);
    const q = this.value.trim();
    if (q.length < 2) { results.classList.add('hidden'); return; }
    timer = setTimeout(() => {
      fetch('/search/ajax?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
          if (!data.length) { results.classList.add('hidden'); return; }
          results.innerHTML = data.slice(0,6).map(r => `
            <a href="${r.url}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 transition border-b border-slate-800 last:border-0">
              ${r.image ? `<img src="${r.image}" class="w-10 h-8 rounded object-cover flex-shrink-0" alt="">` : '<div class="w-10 h-8 rounded bg-slate-700 flex-shrink-0"></div>'}
              <div class="flex-1 min-w-0">
                <div class="text-white text-sm font-medium truncate">${r.title}</div>
                <div class="text-slate-400 text-xs">${r.section}</div>
              </div>
            </a>`).join('')
            + `<a href="/search?q=${encodeURIComponent(q)}" class="block px-4 py-2 text-center text-crimson text-xs hover:bg-slate-800">View all results →</a>`;
          results.classList.remove('hidden');
        });
    }, 280);
  });
  document.addEventListener('click', e => {
    if (!input.contains(e.target) && !results.contains(e.target)) results.classList.add('hidden');
  });
  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); window.location = '/search?q=' + encodeURIComponent(input.value.trim()); }
  });
})();
</script>
