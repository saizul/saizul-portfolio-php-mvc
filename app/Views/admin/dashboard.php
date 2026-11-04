<?php
$pageTitle = 'Dashboard';

// Stat card helper
$card = fn(string $label, int $value, string $icon, string $href, string $color) =>
    compact('label', 'value', 'icon', 'href', 'color');

$cards = [
    $card('Blog Posts',    $stats['posts'],       'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z', '/admin/posts',    'from-violet-500/20 to-violet-500/5 border-violet-500/20 text-violet-400'),
    $card('Projects',      $stats['projects'],    'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',                                                                                                     '/admin/projects', 'from-blue-500/20 to-blue-500/5 border-blue-500/20 text-blue-400'),
    $card('Media Files',   $stats['media'],       'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', '/admin/media', 'from-emerald-500/20 to-emerald-500/5 border-emerald-500/20 text-emerald-400'),
    $card('Unread Messages',$stats['unread_msgs'],'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', '/admin/settings', 'from-amber-500/20 to-amber-500/5 border-amber-500/20 text-amber-400'),
];
?>

<!-- Stats grid -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
<?php foreach ($cards as $c): ?>
  <a href="<?= url($c['href']) ?>"
     class="group relative bg-gradient-to-br <?= $c['color'] ?> border rounded-2xl p-5
            hover:scale-[1.02] transition-all duration-200 overflow-hidden">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-xs text-gray-400 mb-1"><?= $c['label'] ?></p>
        <p class="text-3xl font-bold text-white"><?= number_format($c['value']) ?></p>
      </div>
      <div class="p-2 rounded-xl bg-white/5">
        <svg class="w-5 h-5 <?= explode(' ', $c['color'])[3] ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?= $c['icon'] ?>"/>
        </svg>
      </div>
    </div>
    <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
  </a>
<?php endforeach; ?>
</div>

<!-- Content grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- Recent Posts -->
  <div class="lg:col-span-2 bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
      <h2 class="font-semibold text-sm">Recent Posts</h2>
      <a href="<?= url('/admin/posts/create') ?>"
         class="text-xs bg-white text-black px-3 py-1.5 rounded-lg font-semibold hover:bg-gray-200 transition">
        + New Post
      </a>
    </div>
    <div class="divide-y divide-white/5">
      <?php if (empty($recent_posts)): ?>
        <p class="px-6 py-8 text-sm text-gray-500 text-center">No posts yet. <a href="<?= url('/admin/posts/create') ?>" class="text-white underline">Create one →</a></p>
      <?php else: ?>
        <?php foreach ($recent_posts as $post): ?>
        <div class="flex items-center gap-4 px-6 py-3 hover:bg-white/2 transition">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate"><?= e($post['title']) ?></p>
            <p class="text-xs text-gray-500 mt-0.5">
              <?= e($post['status']) ?>
              <?php if ($post['published_at']): ?> · <?= date('M j, Y', strtotime($post['published_at'])) ?><?php endif; ?>
            </p>
          </div>
          <span class="shrink-0 text-xs px-2 py-0.5 rounded-full
            <?= $post['status'] === 'published' ? 'bg-green-500/15 text-green-400' : 'bg-gray-700 text-gray-400' ?>">
            <?= e($post['status']) ?>
          </span>
          <a href="<?= url('/admin/posts/' . $post['id'] . '/edit') ?>"
             class="shrink-0 text-gray-500 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </a>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div class="px-6 py-3 border-t border-white/5">
      <a href="<?= url('/admin/posts') ?>" class="text-xs text-gray-400 hover:text-white transition">
        View all posts →
      </a>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="space-y-4">
    <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
      <h2 class="font-semibold text-sm mb-4">Quick Actions</h2>
      <div class="space-y-2">
        <?php
        $actions = [
            ['/admin/posts/create',    '+ New Blog Post'],
            ['/admin/projects/create', '+ New Project'],
            ['/admin/media',           '↑ Upload Media'],
            ['/admin/seo',             '⚙ SEO Settings'],
            ['/admin/sitemap/generate','↻ Regenerate Sitemap', 'POST'],
        ];
        foreach ($actions as $a):
          $isPost = ($a[2] ?? 'GET') === 'POST';
        ?>
          <?php if ($isPost): ?>
          <form method="POST" action="<?= url($a[0]) ?>">
            <?= \App\Core\Auth::csrfField() ?>
            <button type="submit"
                    class="w-full text-left text-sm px-4 py-2.5 rounded-xl bg-white/5
                           hover:bg-white/10 text-gray-300 hover:text-white transition">
              <?= $a[1] ?>
            </button>
          </form>
          <?php else: ?>
          <a href="<?= url($a[0]) ?>"
             class="block text-sm px-4 py-2.5 rounded-xl bg-white/5
                    hover:bg-white/10 text-gray-300 hover:text-white transition">
            <?= $a[1] ?>
          </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- System info -->
    <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
      <h2 class="font-semibold text-sm mb-3">System</h2>
      <dl class="space-y-2 text-xs">
        <?php $info = [
          'PHP'       => PHP_VERSION,
          'ENV'       => config('app.env', 'production'),
          'Timezone'  => config('app.timezone', 'UTC'),
          'Date'      => date('M j, Y'),
        ]; ?>
        <?php foreach ($info as $k => $v): ?>
        <div class="flex justify-between">
          <dt class="text-gray-500"><?= $k ?></dt>
          <dd class="text-gray-300 font-mono"><?= e($v) ?></dd>
        </div>
        <?php endforeach; ?>
      </dl>
    </div>
  </div>

</div>
