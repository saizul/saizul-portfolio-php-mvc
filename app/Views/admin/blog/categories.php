<?php $csrfToken=\App\Core\Auth::csrfToken(); $pageTitle='Post Categories'; $GLOBALS['pageTitle']=$pageTitle; ?>
<div class="flex items-center justify-between mb-8">
  <h1 class="text-2xl font-bold text-white">Post Categories</h1>
  <div class="flex gap-3"><a href="/admin/post-tags" class="btn-secondary">Tags</a><a href="/admin/posts" class="btn-secondary">← Posts</a></div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
  <div class="admin-card p-6">
    <h2 class="font-semibold text-white mb-5">Add Category</h2>
    <form method="POST" action="/admin/post-categories/store" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
      <div><label class="form-label">Name</label><input type="text" name="name" class="form-input" required></div>
      <div><label class="form-label">Description</label><textarea name="description" rows="2" class="form-input"></textarea></div>
      <div><label class="form-label">Color</label><input type="color" name="color" value="#6ee7b7" class="h-10 w-full rounded cursor-pointer bg-transparent"></div>
      <button type="submit" class="btn-primary w-full">Add</button>
    </form>
  </div>
  <div class="lg:col-span-2 admin-card p-6">
    <h2 class="font-semibold text-white mb-5">Categories (<?= count($categories) ?>)</h2>
    <div class="space-y-2">
      <?php foreach ($categories as $c): ?>
      <div class="flex items-center gap-4 bg-slate-800/50 rounded-xl px-4 py-3">
        <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:<?= htmlspecialchars($c['color']) ?>"></span>
        <div class="flex-1"><div class="font-medium text-white text-sm"><?= htmlspecialchars($c['name']) ?></div><div class="text-xs text-slate-400">/blog/category/<?= htmlspecialchars($c['slug']) ?></div></div>
        <form method="POST" action="/admin/post-categories/<?= $c['id'] ?>/delete" onsubmit="return confirm('Delete?')"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>"><button class="text-red-400 text-xs">✕</button></form>
      </div>
      <?php endforeach; ?>
      <?php if(!$categories): ?><p class="text-slate-500 text-sm">No categories yet.</p><?php endif; ?>
    </div>
  </div>
</div>
