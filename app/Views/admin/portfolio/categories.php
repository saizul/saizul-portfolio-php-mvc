<?php
$csrfToken = \App\Core\Auth::csrfToken();
$pageTitle = 'Portfolio Categories';
$GLOBALS['pageTitle'] = $pageTitle;
?>
<div class="flex items-center justify-between mb-8">
  <h1 class="text-2xl font-bold text-white">Portfolio Categories</h1>
  <a href="/admin/portfolio" class="btn-secondary">← Portfolio</a>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
  <div class="admin-card p-6">
    <h2 class="font-semibold text-white mb-5">Add Category</h2>
    <form method="POST" action="/admin/portfolio-categories/store" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
      <div><label class="form-label">Name</label><input type="text" name="name" class="form-input" required></div>
      <div><label class="form-label">Description</label><textarea name="description" rows="2" class="form-input"></textarea></div>
      <div><label class="form-label">Color</label><input type="color" name="color" value="#c0392b" class="h-10 w-full rounded cursor-pointer bg-transparent"></div>
      <button type="submit" class="btn-primary w-full">Add Category</button>
    </form>
  </div>
  <div class="lg:col-span-2 admin-card p-6">
    <h2 class="font-semibold text-white mb-5">Categories</h2>
    <div class="space-y-2">
      <?php foreach ($categories as $cat): ?>
      <div class="flex items-center gap-4 bg-slate-800/50 rounded-xl px-4 py-3">
        <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:<?= htmlspecialchars($cat['color']) ?>"></span>
        <div class="flex-1"><div class="font-medium text-white text-sm"><?= htmlspecialchars($cat['name']) ?></div><div class="text-xs text-slate-400">/portfolio/<?= htmlspecialchars($cat['slug']) ?></div></div>
        <form method="POST" action="/admin/portfolio-categories/<?= $cat['id'] ?>/delete" onsubmit="return confirm('Delete?')">
          <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
          <button class="text-red-400 text-xs">✕</button>
        </form>
      </div>
      <?php endforeach; ?>
      <?php if (!$categories): ?><p class="text-slate-500 text-sm">No categories yet.</p><?php endif; ?>
    </div>
  </div>
</div>
