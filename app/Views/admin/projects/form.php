<?php
$isEdit    = !empty($row);
$pageTitle = $isEdit ? 'Edit Project' : 'New Project';
$action    = $isEdit ? url('/admin/projects/' . $row['id'] . '/update') : url('/admin/projects/store');
?>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-xl font-bold"><?= $pageTitle ?></h1>
  <a href="<?= url('/admin/projects') ?>" class="text-sm text-gray-400 hover:text-white transition">← All projects</a>
</div>

<form method="POST" action="<?= $action ?>">
  <?= \App\Core\Auth::csrfField() ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

      <div class="bg-gray-900 border border-white/5 rounded-2xl p-5 space-y-4">
        <div>
          <label class="block text-xs text-gray-400 mb-1.5 uppercase tracking-wide">Project Title *</label>
          <input type="text" name="title" required
                 value="<?= e($row['title'] ?? '') ?>"
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white
                        focus:outline-none focus:border-white/30 placeholder-gray-600"
                 placeholder="AI Document Analyzer">
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5 uppercase tracking-wide">Slug</label>
          <input type="text" name="slug"
                 value="<?= e($row['slug'] ?? '') ?>"
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm font-mono text-gray-300
                        focus:outline-none focus:border-white/30">
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5 uppercase tracking-wide">Short Excerpt</label>
          <textarea name="excerpt" rows="2"
                    class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-gray-200
                           focus:outline-none focus:border-white/30 resize-none placeholder-gray-600"
                    placeholder="One-line description shown on cards…"><?= e($row['excerpt'] ?? '') ?></textarea>
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5 uppercase tracking-wide">Full Description (HTML)</label>
          <textarea name="description" rows="8"
                    class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-gray-200
                           font-mono focus:outline-none focus:border-white/30 resize-y placeholder-gray-600"
                    placeholder="<p>Detailed project description…</p>"><?= e($row['description'] ?? '') ?></textarea>
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5 uppercase tracking-wide">Tech Stack (comma-separated)</label>
          <input type="text" name="tech_stack"
                 value="<?= e($row['tech_stack'] ?? '') ?>"
                 placeholder="Python, LangChain, React, PostgreSQL"
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-gray-300
                        focus:outline-none focus:border-white/30">
        </div>
      </div>

      <div class="bg-gray-900 border border-white/5 rounded-2xl p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs text-gray-400 mb-1.5 uppercase tracking-wide">Demo URL</label>
          <input type="url" name="demo_url" value="<?= e($row['demo_url'] ?? '') ?>"
                 placeholder="https://demo.example.com"
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-gray-300
                        focus:outline-none focus:border-white/30">
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5 uppercase tracking-wide">GitHub URL</label>
          <input type="url" name="github_url" value="<?= e($row['github_url'] ?? '') ?>"
                 placeholder="https://github.com/…"
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-gray-300
                        focus:outline-none focus:border-white/30">
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5 uppercase tracking-wide">Started</label>
          <input type="date" name="started_at" value="<?= e($row['started_at'] ?? '') ?>"
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-gray-300
                        focus:outline-none focus:border-white/30">
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5 uppercase tracking-wide">Completed</label>
          <input type="date" name="completed_at" value="<?= e($row['completed_at'] ?? '') ?>"
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-gray-300
                        focus:outline-none focus:border-white/30">
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-5">
      <div class="bg-gray-900 border border-white/5 rounded-2xl p-5 space-y-4">
        <h3 class="text-xs text-gray-400 uppercase tracking-wide">Settings</h3>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5">Project Type</label>
          <select name="project_type"
                  class="w-full bg-gray-800 border border-white/10 rounded-xl px-3 py-2.5 text-sm text-gray-200
                         focus:outline-none focus:border-white/30">
            <?php foreach (['ai','portfolio','open-source','client'] as $t): ?>
            <option value="<?= $t ?>" <?= ($row['project_type'] ?? 'portfolio') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5">Status</label>
          <select name="status"
                  class="w-full bg-gray-800 border border-white/10 rounded-xl px-3 py-2.5 text-sm text-gray-200
                         focus:outline-none focus:border-white/30">
            <?php foreach (['active','draft','inactive'] as $s): ?>
            <option value="<?= $s ?>" <?= ($row['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5">Sort Order</label>
          <input type="number" name="order" min="0" value="<?= (int)($row['order'] ?? 0) ?>"
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-3 py-2.5 text-sm text-gray-200
                        focus:outline-none focus:border-white/30">
        </div>
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_featured" value="1"
                 <?= !empty($row['is_featured']) ? 'checked' : '' ?>
                 class="w-4 h-4 rounded bg-gray-800 border-gray-600 accent-white">
          <span class="text-sm text-gray-300">Featured on homepage</span>
        </label>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5">Thumbnail URL</label>
          <input type="url" name="thumbnail" value="<?= e($row['thumbnail_id'] ?? '') ?>"
                 placeholder="/uploads/project-thumb.jpg"
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-3 py-2.5 text-sm text-gray-300
                        focus:outline-none focus:border-white/30">
        </div>
        <button type="submit"
                class="w-full bg-white text-black text-sm font-semibold py-2.5 rounded-xl
                       hover:bg-gray-200 active:scale-95 transition-all">
          <?= $isEdit ? 'Save Changes' : 'Create Project' ?>
        </button>
      </div>
    </div>
  </div>
</form>
