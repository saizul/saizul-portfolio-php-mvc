<?php
$csrfToken = \App\Core\Auth::csrfToken();
$pageTitle = 'Footer Manager';
?>
<div class="flex items-center justify-between mb-8">
  <h1 class="text-2xl font-bold text-white">Footer Manager</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
  <div class="lg:col-span-1">
    <div class="admin-card p-6">
      <h2 class="font-semibold text-white mb-5">Add / Update Block</h2>
      <form method="POST" action="/admin/footer/save" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
        <div>
          <label class="form-label">Block Key <span class="text-slate-500 text-xs">(e.g. about, links, social)</span></label>
          <input type="text" name="block_key" class="form-input" required>
        </div>
        <div>
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-input">
        </div>
        <div>
          <label class="form-label">Content (HTML allowed)</label>
          <textarea name="content" rows="5" class="form-input font-mono text-xs"></textarea>
        </div>
        <div>
          <label class="form-label">Order</label>
          <input type="number" name="order_col" value="0" class="form-input w-24">
        </div>
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 accent-crimson">
          <span class="text-sm text-slate-300">Active</span>
        </label>
        <button type="submit" class="btn-primary w-full">Save Block</button>
      </form>
    </div>
  </div>
  <div class="lg:col-span-2">
    <div class="admin-card p-6">
      <h2 class="font-semibold text-white mb-5">Footer Blocks</h2>
      <div class="space-y-3">
        <?php foreach ($blocks as $b): ?>
        <div class="flex items-start gap-4 bg-slate-800/50 rounded-xl p-4">
          <div class="flex-1">
            <div class="font-semibold text-white text-sm"><?= htmlspecialchars($b['title'] ?: $b['block_key']) ?></div>
            <div class="text-xs text-purple-300 mb-1"><code><?= htmlspecialchars($b['block_key']) ?></code></div>
            <div class="text-xs text-slate-400 line-clamp-2"><?= strip_tags($b['content'] ?? '') ?></div>
          </div>
          <span class="w-2 h-2 rounded-full mt-1 <?= $b['is_active'] ? 'bg-emerald-400' : 'bg-red-500' ?> flex-shrink-0"></span>
          <form method="POST" action="/admin/footer/<?= urlencode($b['block_key']) ?>/delete" onsubmit="return confirm('Delete?')">
            <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
            <button class="text-red-400 hover:text-red-300 text-sm">✕</button>
          </form>
        </div>
        <?php endforeach; ?>
        <?php if (!$blocks): ?>
        <p class="text-slate-500 text-sm">No blocks yet. Add your first footer block.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
