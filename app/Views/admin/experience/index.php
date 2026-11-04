<?php $pageTitle = 'Experience'; ?>
<div class="space-y-6">

  <!-- Header -->
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-lg font-semibold">Experience</h2>
      <p class="text-xs text-gray-500 mt-0.5"><?= count($rows) ?> entries</p>
    </div>
    <button onclick="openCreateModal()"
            class="flex items-center gap-2 bg-white text-black px-4 py-2 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      Add Entry
    </button>
  </div>

  <!-- Timeline -->
  <?php if (empty($rows)): ?>
    <div class="text-center py-20 text-gray-500">
      <p class="text-sm">No experience entries yet.</p>
    </div>
  <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($rows as $r): ?>
      <div class="group bg-gray-900 border border-white/5 rounded-2xl p-5 flex items-start gap-4">
        <!-- Type badge -->
        <div class="shrink-0 mt-0.5">
          <span class="text-xs px-2 py-1 rounded-lg font-medium
            <?= $r['type'] === 'work' ? 'bg-blue-500/15 text-blue-400' : 'bg-purple-500/15 text-purple-400' ?>">
            <?= e($r['type']) ?>
          </span>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="font-semibold text-sm"><?= e($r['role']) ?></p>
              <p class="text-sm text-gray-400"><?= e($r['company']) ?></p>
            </div>
            <p class="text-xs text-gray-500 shrink-0">
              <?= e($r['start_date']) ?> —
              <?= $r['is_current'] ? '<span class="text-green-400">Present</span>' : e($r['end_date'] ?? '') ?>
            </p>
          </div>
          <?php if ($r['description']): ?>
          <p class="text-xs text-gray-500 mt-2 line-clamp-2"><?= e($r['description']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="shrink-0 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition">
          <button onclick="openEditModal(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)"
                  class="text-gray-500 hover:text-white transition p-1.5 rounded-lg hover:bg-white/5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </button>
          <form method="POST" action="<?= url('/admin/experience/' . $r['id'] . '/delete') ?>"
                onsubmit="return confirm('Delete this entry?')">
            <?= \App\Core\Auth::csrfField() ?>
            <button type="submit" class="text-gray-600 hover:text-red-400 transition p-1.5 rounded-lg hover:bg-white/5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Create/Edit Modal -->
<div id="exp-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
  <div class="bg-gray-900 border border-white/10 rounded-2xl p-6 w-full max-w-lg shadow-2xl max-h-[90vh] overflow-y-auto">
    <h3 id="modal-title" class="font-semibold mb-5">Add Experience</h3>
    <form id="exp-form" method="POST">
      <?= \App\Core\Auth::csrfField() ?>
      <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-gray-400 mb-2">Role / Title *</label>
            <input type="text" name="role" id="f-role" required
                   class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:border-white/30 text-white"
                   placeholder="Senior Developer">
          </div>
          <div>
            <label class="block text-xs text-gray-400 mb-2">Company / Institution *</label>
            <input type="text" name="company" id="f-company" required
                   class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:border-white/30 text-white"
                   placeholder="Acme Corp">
          </div>
        </div>

        <div>
          <label class="block text-xs text-gray-400 mb-2">Description</label>
          <textarea name="description" id="f-description" rows="3"
                    class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm
                           focus:outline-none focus:border-white/30 text-gray-300 resize-none"
                    placeholder="What did you do?"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-gray-400 mb-2">Start Date *</label>
            <input type="month" name="start_date" id="f-start" required
                   class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:border-white/30 text-white">
          </div>
          <div>
            <label class="block text-xs text-gray-400 mb-2">End Date</label>
            <input type="month" name="end_date" id="f-end"
                   class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:border-white/30 text-white">
          </div>
        </div>

        <div class="flex items-center gap-6">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_current" id="f-current" value="1" class="rounded accent-white"
                   onchange="document.getElementById('f-end').disabled=this.checked">
            <span class="text-sm text-gray-300">Currently working here</span>
          </label>
          <div>
            <select name="type" id="f-type"
                    class="bg-gray-800 border border-white/10 rounded-xl px-3 py-2 text-sm
                           focus:outline-none focus:border-white/30 text-white">
              <option value="work">Work</option>
              <option value="education">Education</option>
            </select>
          </div>
        </div>
      </div>

      <div class="flex gap-3 mt-6">
        <button type="submit"
                class="flex-1 bg-white text-black py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
          Save Entry
        </button>
        <button type="button" onclick="closeExpModal()"
                class="flex-1 bg-white/5 hover:bg-white/10 py-2.5 rounded-xl text-sm transition">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openCreateModal() {
  document.getElementById('modal-title').textContent = 'Add Experience';
  document.getElementById('exp-form').action = '<?= url('/admin/experience/store') ?>';
  ['role','company','description','start','end'].forEach(k => {
    const el = document.getElementById('f-' + k);
    if (el) el.value = '';
  });
  document.getElementById('f-current').checked = false;
  document.getElementById('f-type').value = 'work';
  document.getElementById('f-end').disabled = false;
  showExpModal();
}

function openEditModal(row) {
  document.getElementById('modal-title').textContent = 'Edit Experience';
  document.getElementById('exp-form').action = `<?= url('/admin/experience/') ?>${row.id}/update`;
  document.getElementById('f-role').value = row.role || '';
  document.getElementById('f-company').value = row.company || '';
  document.getElementById('f-description').value = row.description || '';
  document.getElementById('f-start').value = (row.start_date || '').substring(0, 7);
  document.getElementById('f-end').value = (row.end_date || '').substring(0, 7);
  document.getElementById('f-current').checked = !!parseInt(row.is_current);
  document.getElementById('f-end').disabled = !!parseInt(row.is_current);
  document.getElementById('f-type').value = row.type || 'work';
  showExpModal();
}

function showExpModal() {
  document.getElementById('exp-modal').classList.remove('hidden');
  document.getElementById('exp-modal').classList.add('flex');
}

function closeExpModal() {
  document.getElementById('exp-modal').classList.add('hidden');
  document.getElementById('exp-modal').classList.remove('flex');
}
</script>
