<?php
$csrfToken = \App\Core\Auth::csrfToken();
$isEdit    = !empty($project);
$pageTitle = $isEdit ? 'Edit AI Project' : 'Add AI Project';
$GLOBALS['pageTitle'] = $pageTitle;
$p = $project ?? [];
$existingImages = json_decode($p['images'] ?? '[]', true) ?: [];
?>
<div class="mb-6"><a href="/admin/ai-projects" class="text-slate-400 hover:text-white text-sm">← Back</a><h1 class="text-2xl font-bold text-white mt-3"><?= $pageTitle ?></h1></div>

<form method="POST" action="<?= $action ?>" class="space-y-6">
  <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-6">

      <div class="admin-card p-6 space-y-5">
        <h2 class="font-semibold text-white">Project Overview</h2>
        <div><label class="form-label">Project Name *</label><input type="text" name="title" value="<?= htmlspecialchars($p['title']??'') ?>" class="form-input" required></div>
        <div><label class="form-label">Problem Statement</label><textarea name="problem" rows="5" class="form-input"><?= htmlspecialchars($p['problem']??'') ?></textarea></div>
        <div><label class="form-label">Solution</label><textarea name="solution" rows="5" class="form-input"><?= htmlspecialchars($p['solution']??'') ?></textarea></div>
        <div><label class="form-label">Technology Stack</label><input type="text" name="tech_stack" value="<?= htmlspecialchars($p['tech_stack']??'') ?>" class="form-input" placeholder="Python, LangChain, GPT-4, FastAPI…"></div>
      </div>

      <div class="admin-card p-6 space-y-5">
        <h2 class="font-semibold text-white">Architecture & Workflow</h2>
        <div><label class="form-label">Workflow Diagram URL / Path</label><input type="text" name="workflow_diagram" value="<?= htmlspecialchars($p['workflow_diagram']??'') ?>" class="form-input"></div>
        <div><label class="form-label">Architecture Notes</label><textarea name="architecture_notes" rows="8" class="form-input font-mono text-xs"><?= htmlspecialchars($p['architecture_notes']??'') ?></textarea></div>
        <div><label class="form-label">Results / Outcomes</label><textarea name="results" rows="4" class="form-input"><?= htmlspecialchars($p['results']??'') ?></textarea></div>
        <div><label class="form-label">Video URL</label><input type="url" name="video_url" value="<?= htmlspecialchars($p['video_url']??'') ?>" class="form-input"></div>
      </div>

      <div class="admin-card p-6 space-y-5">
        <h2 class="font-semibold text-white">Project Images</h2>
        <div id="images-list" class="space-y-2">
          <?php foreach ($existingImages as $imgPath): ?>
          <div class="flex items-center gap-3">
            <img src="<?= htmlspecialchars($imgPath) ?>" class="w-16 h-12 rounded object-cover">
            <input type="text" name="image_paths[]" value="<?= htmlspecialchars($imgPath) ?>" class="form-input flex-1 text-xs">
            <button type="button" onclick="this.closest('div').remove()" class="text-red-400 text-sm">✕</button>
          </div>
          <?php endforeach; ?>
        </div>
        <button type="button" onclick="addImageRow()" class="btn-secondary text-sm">+ Add Image Path</button>
      </div>

      <div class="admin-card p-6 space-y-5">
        <h2 class="font-semibold text-white">SEO</h2>
        <div><label class="form-label">Meta Title</label><input type="text" name="meta_title" value="<?= htmlspecialchars($p['meta_title']??'') ?>" class="form-input" maxlength="60"></div>
        <div><label class="form-label">Meta Description</label><textarea name="meta_description" rows="3" class="form-input" maxlength="160"><?= htmlspecialchars($p['meta_description']??'') ?></textarea></div>
      </div>
    </div>

    <div class="space-y-6">
      <div class="admin-card p-6 space-y-4">
        <h2 class="font-semibold text-white">Publish</h2>
        <div><label class="form-label">Status</label>
          <select name="status" class="form-input">
            <option value="published" <?= ($p['status']??'published')==='published'?'selected':'' ?>>Published</option>
            <option value="draft" <?= ($p['status']??'')==='draft'?'selected':'' ?>>Draft</option>
          </select>
        </div>
        <div><label class="form-label">Order</label><input type="number" name="order_col" value="<?= (int)($p['order_col']??0) ?>" class="form-input w-24"></div>
      </div>
    </div>
  </div>

  <div class="flex gap-4">
    <button type="submit" class="btn-primary px-8">Save AI Project</button>
    <a href="/admin/ai-projects" class="btn-secondary">Cancel</a>
  </div>
</form>
<script>
function addImageRow(){
  const list=document.getElementById('images-list');
  const div=document.createElement('div');
  div.className='flex items-center gap-3';
  div.innerHTML='<input type="text" name="image_paths[]" class="form-input flex-1 text-xs" placeholder="/uploads/..."><button type="button" onclick="this.closest(\'div\').remove()" class="text-red-400 text-sm">✕</button>';
  list.appendChild(div);
}
</script>
