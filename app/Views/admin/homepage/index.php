<?php
$csrfToken = \App\Core\Auth::csrfToken();
$pageTitle = 'Homepage Sections';
?>
<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-white">Homepage Sections</h1>
    <p class="text-slate-400 text-sm mt-1">Enable/disable · drag to reorder · click to edit content</p>
  </div>
</div>

<div class="admin-card p-6">
  <ul id="sections-sortable" class="space-y-3">
    <?php foreach ($sections as $s): ?>
    <li class="flex items-center gap-4 bg-slate-800/50 rounded-xl px-5 py-4 cursor-grab" data-key="<?= htmlspecialchars($s['section_key']) ?>">
      <span class="text-slate-500 text-xl select-none">⠿</span>
      <div class="flex-1">
        <div class="font-semibold text-white text-sm"><?= htmlspecialchars($s['title'] ?: ucfirst(str_replace('_',' ',$s['section_key']))) ?></div>
        <div class="text-xs text-slate-400 mt-0.5">Key: <code class="text-purple-300"><?= htmlspecialchars($s['section_key']) ?></code></div>
      </div>
      <!-- Toggle -->
      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" class="sr-only peer section-toggle"
               data-id="<?= $s['id'] ?>" <?= $s['is_enabled'] ? 'checked' : '' ?>>
        <div class="w-10 h-5 bg-slate-600 peer-checked:bg-emerald-500 rounded-full transition after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition peer-checked:after:translate-x-5"></div>
      </label>
      <a href="/admin/homepage/<?= urlencode($s['section_key']) ?>/edit"
         class="btn-secondary text-xs px-4 py-1.5">Edit</a>
    </li>
    <?php endforeach; ?>
  </ul>
</div>

<script>
// Toggle
document.querySelectorAll('.section-toggle').forEach(cb => {
  cb.addEventListener('change', function() {
    fetch('/admin/homepage/toggle', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
      body: '_csrf=<?= $csrfToken ?>&id='+this.dataset.id+'&enabled='+(this.checked?1:0)
    });
  });
});
// Drag reorder
(function(){
  const list = document.getElementById('sections-sortable');
  let drag = null;
  list.querySelectorAll('li').forEach(li => {
    li.setAttribute('draggable','true');
    li.addEventListener('dragstart', () => { drag = li; li.style.opacity='0.4'; });
    li.addEventListener('dragend',   () => { drag = null; li.style.opacity='1'; saveOrder(); });
    li.addEventListener('dragover',  e => { e.preventDefault(); if (drag && drag!==li) { const r=li.getBoundingClientRect(); li.parentNode.insertBefore(drag, e.clientY<r.top+r.height/2?li:li.nextSibling); } });
  });
  function saveOrder() {
    const keys = Array.from(list.querySelectorAll('li')).map(li => li.dataset.key);
    fetch('/admin/homepage/reorder', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'}, body:'_csrf=<?= $csrfToken ?>&keys='+JSON.stringify(keys) });
  }
})();
</script>
