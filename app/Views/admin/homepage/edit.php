<?php
$csrfToken = \App\Core\Auth::csrfToken();
$key       = $section['section_key'] ?? '';
$pageTitle = 'Edit Section: ' . ucfirst(str_replace('_', ' ', $key));
?>
<div class="mb-8">
  <a href="/admin/homepage" class="text-slate-400 hover:text-white text-sm">← Back to sections</a>
  <h1 class="text-2xl font-bold text-white mt-3"><?= htmlspecialchars($pageTitle) ?></h1>
</div>

<form method="POST" action="/admin/homepage/<?= urlencode($key) ?>/save" class="max-w-3xl space-y-6">
  <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">

  <div class="admin-card p-6 space-y-5">
    <div>
      <label class="form-label">Section Title</label>
      <input type="text" name="title" value="<?= htmlspecialchars($section['title'] ?? '') ?>" class="form-input">
    </div>
    <div>
      <label class="form-label">Subtitle / Description</label>
      <textarea name="subtitle" rows="3" class="form-input"><?= htmlspecialchars($section['subtitle'] ?? '') ?></textarea>
    </div>
    <div>
      <label class="form-label">Content (JSON blocks)</label>
      <textarea name="content_json" rows="8" class="form-input font-mono text-xs"><?= htmlspecialchars($section['content_json'] ?? '[]') ?></textarea>
      <p class="text-xs text-slate-500 mt-1">JSON array of content blocks. Use the Page Builder for visual editing.</p>
    </div>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="is_enabled" value="1" <?= ($section['is_enabled'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 accent-crimson">
      <span class="text-sm text-slate-300">Section enabled (visible on homepage)</span>
    </label>
  </div>

  <div class="flex gap-4">
    <button type="submit" class="btn-primary">Save Section</button>
    <a href="/admin/homepage" class="btn-secondary">Cancel</a>
  </div>
</form>
