<?php
$csrfToken  = \App\Core\Auth::csrfToken();
$isEdit     = !empty($project);
$pageTitle  = $isEdit ? 'Edit Portfolio Item' : 'Add Portfolio Item';
$GLOBALS['pageTitle'] = $pageTitle;
$p = $project ?? [];
?>
<div class="mb-6">
  <a href="/admin/portfolio" class="text-slate-400 hover:text-white text-sm">← Back to Portfolio</a>
  <h1 class="text-2xl font-bold text-white mt-3"><?= $pageTitle ?></h1>
</div>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data" class="space-y-8">
  <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Main content -->
    <div class="lg:col-span-2 space-y-6">

      <div class="admin-card p-6 space-y-5">
        <h2 class="font-semibold text-white">Project Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label">Project Name *</label>
            <input type="text" name="title" value="<?= htmlspecialchars($p['title'] ?? '') ?>" class="form-input" required>
          </div>
          <div>
            <label class="form-label">Client Name</label>
            <input type="text" name="client_name" value="<?= htmlspecialchars($p['client_name'] ?? '') ?>" class="form-input">
          </div>
          <div>
            <label class="form-label">Country</label>
            <input type="text" name="country" value="<?= htmlspecialchars($p['country'] ?? '') ?>" class="form-input" placeholder="UK, Bangladesh, USA…">
          </div>
          <div>
            <label class="form-label">Project Type</label>
            <input type="text" name="project_type" value="<?= htmlspecialchars($p['project_type'] ?? '') ?>" class="form-input" placeholder="Web App, AI System…">
          </div>
        </div>
        <div>
          <label class="form-label">Description</label>
          <textarea name="description" rows="4" class="form-input"><?= htmlspecialchars($p['description'] ?? '') ?></textarea>
        </div>
        <div>
          <label class="form-label">Technologies Used</label>
          <input type="text" name="tech_stack" value="<?= htmlspecialchars($p['tech_stack'] ?? '') ?>" class="form-input" placeholder="PHP, Python, Three.js, MySQL…">
        </div>
        <div>
          <label class="form-label">Results / Impact</label>
          <textarea name="results" rows="3" class="form-input"><?= htmlspecialchars($p['results'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="admin-card p-6 space-y-5">
        <h2 class="font-semibold text-white">Case Study</h2>
        <div>
          <label class="form-label">Case Study (HTML / Markdown)</label>
          <textarea id="case-study-editor" name="case_study" rows="12" class="form-input font-mono text-sm"><?= htmlspecialchars($p['case_study'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="admin-card p-6 space-y-5">
        <h2 class="font-semibold text-white">Links</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label">Live URL</label>
            <input type="url" name="live_url" value="<?= htmlspecialchars($p['live_url'] ?? '') ?>" class="form-input">
          </div>
          <div>
            <label class="form-label">Demo URL</label>
            <input type="url" name="demo_url" value="<?= htmlspecialchars($p['demo_url'] ?? '') ?>" class="form-input">
          </div>
          <div>
            <label class="form-label">GitHub URL</label>
            <input type="url" name="github_url" value="<?= htmlspecialchars($p['github_url'] ?? '') ?>" class="form-input">
          </div>
          <div>
            <label class="form-label">Video URL</label>
            <input type="url" name="video_url" value="<?= htmlspecialchars($p['video_url'] ?? '') ?>" class="form-input">
          </div>
        </div>
      </div>

      <div class="admin-card p-6 space-y-5">
        <h2 class="font-semibold text-white">SEO</h2>
        <div>
          <label class="form-label">Meta Title</label>
          <input type="text" name="meta_title" value="<?= htmlspecialchars($p['meta_title'] ?? '') ?>" class="form-input" maxlength="60">
          <div class="text-xs text-slate-500 mt-1" id="meta-title-count">0 / 60</div>
        </div>
        <div>
          <label class="form-label">Meta Description</label>
          <textarea name="meta_description" rows="3" class="form-input" maxlength="160"><?= htmlspecialchars($p['meta_description'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">

      <div class="admin-card p-6 space-y-4">
        <h2 class="font-semibold text-white">Publish</h2>
        <div>
          <label class="form-label">Status</label>
          <select name="status" class="form-input">
            <option value="published" <?= ($p['status']??'published')==='published'?'selected':'' ?>>Published</option>
            <option value="draft"     <?= ($p['status']??'')==='draft'?'selected':'' ?>>Draft</option>
          </select>
        </div>
        <div>
          <label class="form-label">Category</label>
          <select name="category" class="form-input">
            <option value="portfolio" <?= ($p['category']??'')==='portfolio'?'selected':'' ?>>Portfolio</option>
            <option value="ai"        <?= ($p['category']??'')==='ai'?'selected':'' ?>>AI</option>
          </select>
        </div>
        <div>
          <label class="form-label">Portfolio Category</label>
          <select name="portfolio_cat_id" class="form-input">
            <option value="">— None —</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= ($p['portfolio_cat_id']??'')==$cat['id']?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="featured" value="1" <?= ($p['featured']??0)?'checked':'' ?> class="w-4 h-4 accent-crimson">
          <span class="text-sm text-slate-300">Featured</span>
        </label>
      </div>

      <div class="admin-card p-6 space-y-4">
        <h2 class="font-semibold text-white">Thumbnail</h2>
        <div>
          <label class="form-label">Thumbnail URL</label>
          <input type="text" name="thumbnail" id="thumbnail-input" value="<?= htmlspecialchars($p['thumbnail'] ?? '') ?>" class="form-input text-xs" placeholder="/uploads/...">
        </div>
        <?php if ($p['thumbnail'] ?? ''): ?>
        <img src="<?= htmlspecialchars($p['thumbnail']) ?>" class="w-full rounded-lg object-cover h-36" alt="">
        <?php endif; ?>
        <p class="text-xs text-slate-500">Select from <a href="/admin/media" target="_blank" class="text-crimson hover:underline">Media Library</a></p>
      </div>

      <div class="admin-card p-6 space-y-4">
        <h2 class="font-semibold text-white">Project Images</h2>
        <div id="image-gallery" class="grid grid-cols-2 gap-2">
          <?php foreach ($images ?? [] as $img): ?>
          <div class="relative group">
            <img src="<?= htmlspecialchars($img['filepath']) ?>" class="w-full h-20 rounded object-cover" alt="">
            <input type="hidden" name="image_ids[]" value="<?= $img['id'] ?>">
            <button type="button" onclick="this.closest('div').remove()" class="absolute top-1 right-1 w-5 h-5 bg-red-600 text-white rounded-full text-xs opacity-0 group-hover:opacity-100">×</button>
          </div>
          <?php endforeach; ?>
        </div>
        <select id="add-image-select" class="form-input text-xs">
          <option value="">— Pick from media library —</option>
          <?php foreach ($mediaItems as $m): if (!str_starts_with($m['mime_type']??'','image/')) continue; ?>
          <option value="<?= $m['id'] ?>" data-src="<?= htmlspecialchars($m['filepath']) ?>"><?= htmlspecialchars($m['filename']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="button" onclick="addImage()" class="btn-secondary w-full text-sm">+ Add Image</button>
      </div>

    </div>
  </div>

  <div class="flex gap-4">
    <button type="submit" class="btn-primary px-8">Save Portfolio Item</button>
    <a href="/admin/portfolio" class="btn-secondary">Cancel</a>
  </div>
</form>

<script>
// Meta title counter
const mt = document.querySelector('[name=meta_title]');
const mc = document.getElementById('meta-title-count');
if (mt && mc) { const upd = () => mc.textContent = mt.value.length+' / 60'; mt.addEventListener('input', upd); upd(); }

// Add image
function addImage() {
  const sel = document.getElementById('add-image-select');
  const id  = sel.value;
  const src = sel.options[sel.selectedIndex]?.dataset?.src;
  if (!id || !src) return;
  const gallery = document.getElementById('image-gallery');
  const div = document.createElement('div');
  div.className = 'relative group';
  div.innerHTML = `<img src="${src}" class="w-full h-20 rounded object-cover" alt=""><input type="hidden" name="image_ids[]" value="${id}"><button type="button" onclick="this.closest('div').remove()" class="absolute top-1 right-1 w-5 h-5 bg-red-600 text-white rounded-full text-xs opacity-0 group-hover:opacity-100">×</button>`;
  gallery.appendChild(div);
  sel.value = '';
}
</script>
