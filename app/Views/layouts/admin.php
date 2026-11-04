<?php
use App\Core\Auth;
$user     = Auth::user();
$siteName = config('app.name', 'Saizul Amin');
$current  = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

$nav = [
    ['href' => '/admin',           'label' => 'Dashboard',    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    ['href' => '/admin/posts',     'label' => 'Blog Posts',   'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
    ['href' => '/admin/projects',  'label' => 'Projects',     'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
    ['href' => '/admin/pages',     'label' => 'Page Builder', 'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
    ['href' => '/admin/skills',    'label' => 'Skills',       'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
    ['href' => '/admin/experience','label' => 'Experience',   'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
    ['href' => '/admin/media',     'label' => 'Media',        'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ['href' => '/admin/seo',       'label' => 'SEO',          'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
    ['href' => '/admin/settings',  'label' => 'Settings',     'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
];

// Flash message
if (session_status() === PHP_SESSION_NONE) session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' — Admin' : 'Admin — ' . e($siteName) ?></title>
  <meta name="robots" content="noindex,nofollow">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: { brand: { DEFAULT: '#ffffff' } }
        }
      }
    }
  </script>
  <style>
    [x-cloak] { display: none !important; }
    .nav-active { background: rgba(255,255,255,.08); color: #fff; }
    .nav-item   { transition: background .15s, color .15s; }
    .nav-item:hover { background: rgba(255,255,255,.05); color: #fff; }
  </style>
</head>
<body class="dark bg-gray-950 text-gray-100 h-full">
<div class="flex h-full min-h-screen">

  <!-- ── Sidebar ──────────────────────────────────────────── -->
  <aside id="sidebar"
         class="fixed inset-y-0 left-0 z-30 w-60 bg-gray-900 border-r border-white/5
                flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-200">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 h-16 border-b border-white/5 shrink-0">
      <div class="w-7 h-7 bg-white rounded flex items-center justify-center">
        <span class="text-black text-xs font-black">S</span>
      </div>
      <div>
        <p class="font-bold text-sm leading-none"><?= e($siteName) ?></p>
        <p class="text-xs text-gray-500 mt-0.5">CMS</p>
      </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 px-2 py-4 overflow-y-auto">
      <?php foreach ($nav as $item):
        $href   = $item['href'];
        $active = ($current === $href) || (str_starts_with($current, $href . '/') && $href !== '/admin');
        $exact  = $href === '/admin' && $current === '/admin';
        $isActive = $href === '/admin' ? $exact : $active;
      ?>
      <a href="<?= url($href) ?>"
         class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-0.5
                <?= $isActive ? 'nav-active font-medium' : 'text-gray-400' ?>">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="<?= $item['icon'] ?>"/>
        </svg>
        <?= $item['label'] ?>
        <?php if ($isActive): ?>
          <span class="ml-auto w-1.5 h-1.5 rounded-full bg-white"></span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </nav>

    <!-- User -->
    <div class="px-4 py-4 border-t border-white/5 shrink-0">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-gray-600 to-gray-800
                    flex items-center justify-center text-xs font-bold shrink-0">
          <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium truncate"><?= e($user['name'] ?? '') ?></p>
          <p class="text-xs text-gray-500 truncate"><?= e($user['role'] ?? '') ?></p>
        </div>
        <a href="<?= url('/admin/logout') ?>"
           title="Sign out"
           class="text-gray-500 hover:text-white transition p-1 rounded">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
        </a>
      </div>
    </div>
  </aside>

  <!-- Sidebar overlay (mobile) -->
  <div id="sidebar-overlay"
       class="fixed inset-0 z-20 bg-black/50 hidden md:hidden"
       onclick="toggleSidebar()"></div>

  <!-- ── Main content ─────────────────────────────────────── -->
  <div class="flex-1 flex flex-col md:pl-60 min-h-screen">

    <!-- Topbar -->
    <header class="sticky top-0 z-10 h-16 bg-gray-950/80 backdrop-blur border-b border-white/5
                   flex items-center gap-4 px-4 sm:px-6 shrink-0">

      <!-- Mobile menu toggle -->
      <button onclick="toggleSidebar()"
              class="md:hidden text-gray-400 hover:text-white p-1 rounded">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <!-- Breadcrumb / page title -->
      <h1 class="text-sm font-semibold text-white flex-1 truncate">
        <?= isset($pageTitle) ? e($pageTitle) : 'Dashboard' ?>
      </h1>

      <!-- View site link -->
      <a href="<?= url('/') ?>" target="_blank"
         class="hidden sm:flex items-center gap-1.5 text-xs text-gray-400 hover:text-white transition px-3 py-1.5 rounded-lg border border-white/10 hover:border-white/25">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
        </svg>
        View site
      </a>
    </header>

    <!-- Flash message -->
    <?php if ($flash): ?>
    <?php $fc = $flash['type'] === 'success'
          ? 'bg-green-500/10 border-green-500/30 text-green-300'
          : 'bg-red-500/10 border-red-500/30 text-red-300'; ?>
    <div class="mx-4 sm:mx-6 mt-4 px-4 py-3 rounded-xl border text-sm flex items-center gap-3 <?= $fc ?>"
         id="flash-msg">
      <?php if ($flash['type'] === 'success'): ?>
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
      <?php else: ?>
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      <?php endif; ?>
      <?= e($flash['message']) ?>
      <button onclick="this.parentElement.remove()" class="ml-auto opacity-60 hover:opacity-100">✕</button>
    </div>
    <?php endif; ?>

    <!-- Page content -->
    <main class="flex-1 p-4 sm:p-6">
      <?= $content ?>
    </main>

    <footer class="px-6 py-4 border-t border-white/5 text-xs text-gray-600">
      <?= e($siteName) ?> CMS · PHP 8.3 · <?= date('Y') ?>
    </footer>
  </div>
</div>

<script>
function toggleSidebar() {
  const s = document.getElementById('sidebar');
  const o = document.getElementById('sidebar-overlay');
  s.classList.toggle('-translate-x-full');
  o.classList.toggle('hidden');
}
// Auto-dismiss flash after 5s
setTimeout(() => {
  const f = document.getElementById('flash-msg');
  if (f) f.style.opacity = '0', f.style.transition = 'opacity .4s', setTimeout(() => f.remove(), 400);
}, 5000);
</script>
</body>
</html>
