<?php
$csrfToken = \App\Core\Auth::csrfToken();
$pageTitle = 'Menu Manager';
?>
<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-white">Menu Manager</h1>
    <p class="text-slate-400 text-sm mt-1">Drag to reorder · toggle visibility · add sub-items</p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
  <!-- Add item -->
  <div class="lg:col-span-1">
    <div class="admin-card p-6">
      <h2 class="font-semibold text-white mb-5">Add Menu Item</h2>
      <form method="POST" action="/admin/menu/store">
        <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
        <div class="space-y-4">
          <div>
            <label class="form-label">Label</label>
            <input type="text" name="label" class="form-input" required>
          </div>
          <div>
            <label class="form-label">URL</label>
            <input type="text" name="url" class="form-input" placeholder="/about" required>
          </div>
          <div>
            <label class="form-label">Target</label>
            <select name="target" class="form-input">
              <option value="_self">Same tab</option>
              <option value="_blank">New tab</option>
            </select>
          </div>
          <div>
            <label class="form-label">Parent (for dropdown)</label>
            <select name="parent_id" class="form-input">
              <option value="">— Top level —</option>
              <?php foreach ($items as $it): if ($it['parent_id']) continue; ?>
              <option value="<?= $it['id'] ?>"><?= htmlspecialchars($it['label']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 accent-crimson">
            <span class="text-sm text-slate-300">Active</span>
          </label>
          <button type="submit" class="btn-primary w-full">Add Item</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Items list -->
  <div class="lg:col-span-2">
    <div class="admin-card p-6">
      <h2 class="font-semibold text-white mb-5">Current Menu <span class="text-slate-500 text-sm">(drag to reorder)</span></h2>
      <ul id="menu-sortable" class="space-y-2">
        <?php foreach ($items as $item): ?>
        <li class="flex items-center gap-3 bg-slate-800/50 rounded-lg px-4 py-3 cursor-grab" data-id="<?= $item['id'] ?>">
          <span class="text-slate-500 text-lg">⠿</span>
          <span class="flex-1 text-white text-sm font-medium"><?= htmlspecialchars($item['label']) ?></span>
          <span class="text-slate-400 text-xs"><?= htmlspecialchars($item['url']) ?></span>
          <?php if ($item['parent_id']): ?>
          <span class="text-xs text-purple-400 bg-purple-400/10 px-2 py-0.5 rounded">sub</span>
          <?php endif; ?>
          <span class="w-2 h-2 rounded-full <?= $item['is_active'] ? 'bg-emerald-400' : 'bg-red-500' ?>"></span>
          <form method="POST" action="/admin/menu/<?= $item['id'] ?>/delete" class="inline"
                onsubmit="return confirm('Delete?')">
            <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
            <button class="text-red-400 hover:text-red-300 text-xs ml-2">✕</button>
          </form>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<script>
// Simple drag-and-drop reorder using pointer events
(function(){
  const list = document.getElementById('menu-sortable');
  if (!list) return;
  let dragging = null;

  list.querySelectorAll('li').forEach(li => {
    li.addEventListener('dragstart', e => { dragging = li; li.style.opacity = '0.4'; });
    li.addEventListener('dragend',   e => { dragging = null; li.style.opacity = '1'; saveOrder(); });
    li.addEventListener('dragover',  e => { e.preventDefault(); const rect = li.getBoundingClientRect(); const mid = rect.top + rect.height/2; if (dragging && dragging !== li) li.parentNode.insertBefore(dragging, e.clientY < mid ? li : li.nextSibling); });
    li.setAttribute('draggable','true');
  });

  function saveOrder() {
    const ids = Array.from(list.querySelectorAll('li')).map(li => li.dataset.id);
    fetch('/admin/menu/reorder', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'}, body:'_csrf=<?= $csrfToken ?>&ids='+JSON.stringify(ids) });
  }
})();
</script>
