<?php
$csrfToken = \App\Core\Auth::csrfToken();
$pageTitle = 'Portfolio';
$GLOBALS['pageTitle'] = $pageTitle;
?>
<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-white">Portfolio</h1>
    <p class="text-slate-400 text-sm mt-1"><?= count($projects) ?> items</p>
  </div>
  <div class="flex gap-3">
    <a href="/admin/portfolio-categories" class="btn-secondary">Categories</a>
    <a href="/admin/portfolio/create" class="btn-primary">+ Add Item</a>
  </div>
</div>

<div class="admin-card overflow-hidden">
  <table class="w-full text-sm">
    <thead>
      <tr class="border-b border-slate-700/50">
        <th class="text-left p-4 text-slate-400 font-medium">Project</th>
        <th class="text-left p-4 text-slate-400 font-medium hidden md:table-cell">Client</th>
        <th class="text-left p-4 text-slate-400 font-medium hidden lg:table-cell">Type</th>
        <th class="text-left p-4 text-slate-400 font-medium">Status</th>
        <th class="text-left p-4 text-slate-400 font-medium">Actions</th>
      </tr>
    </thead>
    <tbody id="portfolio-sortable">
      <?php foreach ($projects as $p): ?>
      <tr class="border-b border-slate-700/30 hover:bg-slate-800/30 transition" data-id="<?= $p['id'] ?>">
        <td class="p-4">
          <div class="flex items-center gap-3">
            <span class="text-slate-600 cursor-grab text-lg select-none">⠿</span>
            <?php if ($p['thumbnail']): ?>
            <img src="<?= htmlspecialchars($p['thumbnail']) ?>" alt="" class="w-10 h-10 rounded-lg object-cover">
            <?php else: ?>
            <div class="w-10 h-10 rounded-lg bg-slate-700 flex items-center justify-center text-slate-500 text-xs">IMG</div>
            <?php endif; ?>
            <div>
              <div class="font-medium text-white"><?= htmlspecialchars($p['title']) ?></div>
              <?php if ($p['live_url'] ?? $p['demo_url']): ?>
              <a href="<?= htmlspecialchars($p['live_url'] ?? $p['demo_url']) ?>" target="_blank" class="text-xs text-crimson hover:underline">↗ View</a>
              <?php endif; ?>
            </div>
          </div>
        </td>
        <td class="p-4 text-slate-300 hidden md:table-cell"><?= htmlspecialchars($p['client_name'] ?? '—') ?></td>
        <td class="p-4 hidden lg:table-cell">
          <span class="badge badge-blue"><?= htmlspecialchars($p['project_type'] ?? $p['category']) ?></span>
        </td>
        <td class="p-4">
          <span class="badge <?= $p['status']==='published' ? 'badge-green' : 'badge-gray' ?>">
            <?= $p['status'] ?>
          </span>
        </td>
        <td class="p-4">
          <div class="flex gap-2">
            <a href="/admin/portfolio/<?= $p['id'] ?>/edit" class="btn-xs">Edit</a>
            <form method="POST" action="/admin/portfolio/<?= $p['id'] ?>/delete" onsubmit="return confirm('Delete?')">
              <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
              <button class="btn-xs btn-danger">Del</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (!$projects): ?>
  <div class="p-12 text-center text-slate-500">No portfolio items yet. <a href="/admin/portfolio/create" class="text-crimson">Add your first item →</a></div>
  <?php endif; ?>
</div>

<script>
(function(){
  const tbody = document.getElementById('portfolio-sortable');
  if (!tbody) return;
  let drag = null;
  tbody.querySelectorAll('tr').forEach(row => {
    row.setAttribute('draggable','true');
    row.addEventListener('dragstart', () => { drag = row; row.style.opacity='0.4'; });
    row.addEventListener('dragend',   () => { drag = null; row.style.opacity='1'; save(); });
    row.addEventListener('dragover',  e => { e.preventDefault(); if (drag && drag!==row) { const r=row.getBoundingClientRect(); tbody.insertBefore(drag, e.clientY<r.top+r.height/2?row:row.nextSibling); }});
  });
  function save() {
    const ids = Array.from(tbody.querySelectorAll('tr')).map(r=>r.dataset.id);
    fetch('/admin/portfolio/reorder',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:'_csrf=<?= $csrfToken ?>&ids='+JSON.stringify(ids)});
  }
})();
</script>
