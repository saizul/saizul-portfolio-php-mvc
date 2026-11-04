<?php
$csrfToken   = \App\Core\Auth::csrfToken();
$pageTitle   = 'Media Library';
$GLOBALS['pageTitle'] = $pageTitle;
$folders = ['all','portfolio','projects','blog','profile','general'];
$currentFolder = $folder ?? 'all';
?>
<div class="flex items-center justify-between mb-6">
  <div><h1 class="text-2xl font-bold text-white">Media Library</h1><p class="text-slate-400 text-sm mt-1"><?= count($items) ?> files</p></div>
</div>

<!-- Upload zone -->
<div id="drop-zone" class="border-2 border-dashed border-slate-600 hover:border-crimson rounded-2xl p-8 mb-8 text-center transition cursor-pointer bg-slate-800/30">
  <div class="text-4xl mb-3">📁</div>
  <p class="text-white font-semibold mb-1">Drop files here or click to upload</p>
  <p class="text-slate-400 text-sm mb-4">JPG, PNG, WebP, GIF, PDF, GLB · Max 10MB each</p>
  <div class="flex items-center justify-center gap-4 flex-wrap">
    <label class="btn-primary cursor-pointer">
      <span>Choose Files</span>
      <input type="file" id="file-input" multiple accept="image/*,application/pdf,.glb" class="hidden">
    </label>
    <select id="upload-folder" class="form-input w-40 text-sm">
      <?php foreach (array_slice($folders,1) as $f): ?>
      <option value="<?= $f ?>" <?= $currentFolder===$f?'selected':'' ?>><?= ucfirst($f) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div id="upload-progress" class="hidden mt-4">
    <div class="w-full bg-slate-700 rounded-full h-2"><div id="upload-bar" class="bg-crimson h-2 rounded-full transition-all" style="width:0%"></div></div>
    <p id="upload-status" class="text-sm text-slate-400 mt-2">Uploading…</p>
  </div>
</div>

<!-- Folder tabs -->
<div class="flex gap-2 flex-wrap mb-6">
  <?php foreach ($folders as $f): ?>
  <a href="<?= $f==='all'?'/admin/media':"/admin/media/folder/{$f}" ?>"
     class="px-4 py-1.5 rounded-full text-sm font-medium transition <?= $currentFolder===$f?'bg-crimson text-white':'bg-slate-700/50 text-slate-300 hover:bg-slate-700' ?>">
    <?= ucfirst($f) ?>
  </a>
  <?php endforeach; ?>
</div>

<!-- Search -->
<div class="mb-6">
  <input type="text" id="media-search" placeholder="Search files…" class="form-input max-w-sm">
</div>

<!-- Bulk actions -->
<div id="bulk-actions" class="hidden flex items-center gap-4 mb-4 p-3 bg-slate-800 rounded-xl">
  <span id="selected-count" class="text-sm text-slate-300">0 selected</span>
  <button id="bulk-delete-btn" class="btn-xs btn-danger">Delete Selected</button>
  <button id="deselect-all" class="btn-xs">Deselect All</button>
</div>

<!-- Grid -->
<div id="media-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
  <?php foreach ($items as $item): ?>
  <div class="media-item group relative rounded-xl overflow-hidden bg-slate-800 border border-slate-700/50 hover:border-crimson/50 transition cursor-pointer"
       data-id="<?= $item['id'] ?>"
       data-name="<?= htmlspecialchars(strtolower($item['filename'].' '.($item['alt_text']??''))) ?>">

    <!-- Checkbox -->
    <input type="checkbox" class="media-checkbox absolute top-2 left-2 z-10 w-4 h-4 accent-crimson opacity-0 group-hover:opacity-100 transition">

    <?php if (str_starts_with($item['mime_type']??'','image/')): ?>
    <img src="<?= htmlspecialchars($item['webp_path'] ?: $item['filepath']) ?>"
         alt="<?= htmlspecialchars($item['alt_text']??'') ?>"
         class="w-full h-28 object-cover"
         loading="lazy">
    <?php else: ?>
    <div class="w-full h-28 flex items-center justify-center bg-slate-700 text-slate-400 text-3xl">
      <?= str_ends_with($item['mime_type']??'','pdf') ? '📄' : '📦' ?>
    </div>
    <?php endif; ?>

    <div class="p-2">
      <p class="text-xs text-slate-300 truncate font-medium"><?= htmlspecialchars($item['title'] ?: $item['filename']) ?></p>
      <p class="text-xs text-slate-500"><?= $item['width']&&$item['height'] ? "{$item['width']}×{$item['height']}" : strtoupper(pathinfo($item['filename'],PATHINFO_EXTENSION)) ?></p>
    </div>

    <!-- Hover overlay -->
    <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition flex flex-col items-center justify-center gap-2 p-2">
      <button onclick="copyPath('<?= htmlspecialchars($item['filepath']) ?>')" class="btn-xs w-full">Copy Path</button>
      <?php if ($item['webp_path']): ?>
      <button onclick="copyPath('<?= htmlspecialchars($item['webp_path']) ?>')" class="btn-xs w-full text-emerald-300">Copy WebP</button>
      <?php endif; ?>
      <button onclick="openEditModal(<?= htmlspecialchars(json_encode($item)) ?>)" class="btn-xs w-full">Edit Info</button>
      <form method="POST" action="/admin/media/<?= $item['id'] ?>/delete" class="w-full" onsubmit="return confirm('Delete?')">
        <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
        <button class="btn-xs btn-danger w-full">Delete</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (!$items): ?>
  <div class="col-span-full py-20 text-center text-slate-500">No files yet. Upload your first file above.</div>
  <?php endif; ?>
</div>

<!-- Edit modal -->
<div id="edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
  <div class="bg-slate-900 border border-slate-700 rounded-2xl p-8 w-full max-w-md shadow-2xl">
    <h3 class="font-bold text-white text-lg mb-5">Edit Media Info</h3>
    <form id="edit-form" method="POST" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
      <div><label class="form-label">Title</label><input type="text" name="title" id="edit-title" class="form-input"></div>
      <div><label class="form-label">Alt Text (SEO)</label><input type="text" name="alt_text" id="edit-alt" class="form-input"></div>
      <div><label class="form-label">Description</label><textarea name="description" id="edit-desc" rows="3" class="form-input"></textarea></div>
      <div class="flex gap-3">
        <button type="submit" class="btn-primary">Save</button>
        <button type="button" onclick="closeEditModal()" class="btn-secondary">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
const csrf = '<?= $csrfToken ?>';

// ── Drag & drop upload ──────────────────────────────────
const dropZone  = document.getElementById('drop-zone');
const fileInput = document.getElementById('file-input');
const progress  = document.getElementById('upload-progress');
const bar       = document.getElementById('upload-bar');
const status    = document.getElementById('upload-status');

dropZone.addEventListener('click', e => { if (!e.target.closest('select,button,label,input[type=file]')) fileInput.click(); });
dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('border-crimson'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-crimson'));
dropZone.addEventListener('drop', e => { e.preventDefault(); dropZone.classList.remove('border-crimson'); uploadFiles(e.dataTransfer.files); });
fileInput.addEventListener('change', () => uploadFiles(fileInput.files));

function uploadFiles(files) {
  if (!files.length) return;
  const folder = document.getElementById('upload-folder').value;
  const total  = files.length;
  let done     = 0;
  progress.classList.remove('hidden');
  bar.style.width = '0%';
  status.textContent = `Uploading 0 / ${total}…`;

  Array.from(files).forEach(file => {
    const fd = new FormData();
    fd.append('_csrf', csrf);
    fd.append('folder', folder);
    fd.append('file', file);
    fetch('/admin/media/upload', { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd })
      .then(r => r.json())
      .then(() => { done++; bar.style.width = (done/total*100)+'%'; status.textContent = `Uploading ${done} / ${total}…`; if (done===total) setTimeout(()=>location.reload(),400); })
      .catch(() => { done++; });
  });
}

// ── Search ──────────────────────────────────────────────
document.getElementById('media-search').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.media-item').forEach(el => {
    el.style.display = el.dataset.name.includes(q) ? '' : 'none';
  });
});

// ── Bulk select ─────────────────────────────────────────
const bulkActions   = document.getElementById('bulk-actions');
const selectedCount = document.getElementById('selected-count');

document.addEventListener('change', e => {
  if (!e.target.classList.contains('media-checkbox')) return;
  const checked = document.querySelectorAll('.media-checkbox:checked');
  selectedCount.textContent = checked.length + ' selected';
  bulkActions.classList.toggle('hidden', checked.length === 0);
});

document.getElementById('deselect-all').addEventListener('click', () => {
  document.querySelectorAll('.media-checkbox:checked').forEach(c => c.checked = false);
  bulkActions.classList.add('hidden');
});

document.getElementById('bulk-delete-btn').addEventListener('click', () => {
  if (!confirm('Delete selected files?')) return;
  const ids = Array.from(document.querySelectorAll('.media-checkbox:checked')).map(c => c.closest('.media-item').dataset.id);
  fetch('/admin/media/bulk-delete', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'}, body:`_csrf=${csrf}&ids=${JSON.stringify(ids)}` })
    .then(() => location.reload());
});

// ── Edit modal ──────────────────────────────────────────
function openEditModal(item) {
  document.getElementById('edit-title').value = item.title || '';
  document.getElementById('edit-alt').value   = item.alt_text || '';
  document.getElementById('edit-desc').value  = item.description || '';
  document.getElementById('edit-form').action = '/admin/media/' + item.id + '/update';
  document.getElementById('edit-modal').classList.remove('hidden');
}
function closeEditModal() {
  document.getElementById('edit-modal').classList.add('hidden');
}
document.getElementById('edit-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const fd = new FormData(this);
  fetch(this.action, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body: new URLSearchParams(fd) })
    .then(() => closeEditModal());
});

// ── Copy path ───────────────────────────────────────────
function copyPath(path) {
  navigator.clipboard?.writeText(path).then(() => {
    const n = document.createElement('div');
    n.textContent = 'Copied!';
    n.style.cssText = 'position:fixed;top:20px;right:20px;background:#6ee7b7;color:#000;padding:8px 16px;border-radius:8px;z-index:9999;font-size:13px;font-weight:600';
    document.body.appendChild(n); setTimeout(()=>n.remove(),1800);
  });
}
</script>
