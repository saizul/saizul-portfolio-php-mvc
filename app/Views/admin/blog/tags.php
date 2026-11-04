<?php $csrfToken=\App\Core\Auth::csrfToken(); $pageTitle='Post Tags'; $GLOBALS['pageTitle']=$pageTitle; ?>
<div class="flex items-center justify-between mb-8">
  <h1 class="text-2xl font-bold text-white">Post Tags</h1>
  <div class="flex gap-3"><a href="/admin/post-categories" class="btn-secondary">Categories</a><a href="/admin/posts" class="btn-secondary">← Posts</a></div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
  <div class="admin-card p-6">
    <h2 class="font-semibold text-white mb-5">Add Tag</h2>
    <form method="POST" action="/admin/post-tags/store" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
      <div><label class="form-label">Tag Name</label><input type="text" name="name" class="form-input" required></div>
      <button type="submit" class="btn-primary w-full">Add Tag</button>
    </form>
  </div>
  <div class="lg:col-span-2 admin-card p-6">
    <h2 class="font-semibold text-white mb-5">Tags (<?= count($tags) ?>)</h2>
    <div class="flex flex-wrap gap-2">
      <?php foreach ($tags as $t): ?>
      <div class="flex items-center gap-2 bg-slate-800 rounded-full px-4 py-1.5">
        <span class="text-sm text-white"><?= htmlspecialchars($t['name']) ?></span>
        <form method="POST" action="/admin/post-tags/<?= $t['id'] ?>/delete" onsubmit="return confirm('Delete?')"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>"><button class="text-red-400 text-xs hover:text-red-300">✕</button></form>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
