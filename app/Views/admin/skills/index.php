<?php
$pageTitle = 'Skills';
$categories = [];
foreach ($rows as $r) {
    $categories[$r['category']][] = $r;
}
?>
<div class="space-y-6">

  <!-- Header -->
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-lg font-semibold">Skills</h2>
      <p class="text-xs text-gray-500 mt-0.5"><?= count($rows) ?> skills across <?= count($categories) ?> categories</p>
    </div>
    <button onclick="toggleAddForm()"
            class="flex items-center gap-2 bg-white text-black px-4 py-2 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      Add Skill
    </button>
  </div>

  <!-- Add form (hidden by default) -->
  <div id="add-form" class="hidden bg-gray-900 border border-white/10 rounded-2xl p-6">
    <h3 class="font-semibold text-sm mb-4">New Skill</h3>
    <form method="POST" action="<?= url('/admin/skills/store') ?>">
      <?= \App\Core\Auth::csrfField() ?>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
        <div class="sm:col-span-2">
          <label class="block text-xs text-gray-400 mb-2">Skill Name</label>
          <input type="text" name="name" required
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm
                        focus:outline-none focus:border-white/30 text-white"
                 placeholder="e.g. Python">
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-2">Category</label>
          <input type="text" name="category" required
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm
                        focus:outline-none focus:border-white/30 text-white"
                 placeholder="e.g. Backend"
                 list="cat-list">
          <datalist id="cat-list">
            <?php foreach (array_keys($categories) as $cat): ?>
              <option value="<?= e($cat) ?>">
            <?php endforeach; ?>
          </datalist>
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-2">Proficiency (0–100)</label>
          <input type="number" name="proficiency" min="0" max="100" value="80"
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-2.5 text-sm
                        focus:outline-none focus:border-white/30 text-white">
        </div>
      </div>
      <div class="flex gap-3">
        <button type="submit"
                class="bg-white text-black px-5 py-2 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
          Add Skill
        </button>
        <button type="button" onclick="toggleAddForm()"
                class="px-5 py-2 rounded-xl text-sm text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 transition">
          Cancel
        </button>
      </div>
    </form>
  </div>

  <!-- Skills by category -->
  <?php if (empty($rows)): ?>
    <div class="text-center py-20 text-gray-500">
      <p class="text-sm">No skills yet. Add your first skill above.</p>
    </div>
  <?php else: ?>
    <?php foreach ($categories as $cat => $skills): ?>
    <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">
      <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
        <h3 class="font-semibold text-sm"><?= e($cat) ?></h3>
        <span class="text-xs text-gray-500"><?= count($skills) ?> skills</span>
      </div>
      <div class="divide-y divide-white/5">
        <?php foreach ($skills as $s): ?>
        <div class="flex items-center gap-4 px-6 py-3 group" id="skill-<?= $s['id'] ?>">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium"><?= e($s['name']) ?></p>
          </div>
          <!-- Proficiency bar -->
          <div class="hidden sm:flex items-center gap-3 w-48">
            <div class="flex-1 h-1.5 bg-gray-800 rounded-full overflow-hidden">
              <div class="h-full bg-white rounded-full"
                   style="width:<?= (int)$s['proficiency'] ?>%"></div>
            </div>
            <span class="text-xs text-gray-400 w-8 text-right"><?= (int)$s['proficiency'] ?>%</span>
          </div>
          <!-- Inline edit -->
          <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition">
            <button onclick="editSkill(<?= $s['id'] ?>, '<?= e(addslashes($s['name'])) ?>', '<?= e(addslashes($s['category'])) ?>', <?= (int)$s['proficiency'] ?>)"
                    class="text-gray-500 hover:text-white transition p-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </button>
            <form method="POST" action="<?= url('/admin/skills/' . $s['id'] . '/delete') ?>"
                  onsubmit="return confirm('Delete this skill?')">
              <?= \App\Core\Auth::csrfField() ?>
              <button type="submit" class="text-gray-600 hover:text-red-400 transition p-1">
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
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Edit modal -->
<div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
  <div class="bg-gray-900 border border-white/10 rounded-2xl p-6 w-full max-w-md mx-4 shadow-2xl">
    <h3 class="font-semibold mb-4">Edit Skill</h3>
    <form id="edit-form" method="POST">
      <?= \App\Core\Auth::csrfField() ?>
      <div class="space-y-4">
        <div>
          <label class="block text-xs text-gray-400 mb-2">Name</label>
          <input type="text" name="name" id="edit-name" required
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-sm
                        focus:outline-none focus:border-white/30 text-white">
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-2">Category</label>
          <input type="text" name="category" id="edit-category" required
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-sm
                        focus:outline-none focus:border-white/30 text-white">
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-2">Proficiency: <span id="prof-display">80</span>%</label>
          <input type="range" name="proficiency" id="edit-prof" min="0" max="100"
                 class="w-full accent-white"
                 oninput="document.getElementById('prof-display').textContent=this.value">
        </div>
      </div>
      <div class="flex gap-3 mt-5">
        <button type="submit"
                class="flex-1 bg-white text-black py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
          Update Skill
        </button>
        <button type="button" onclick="closeEditModal()"
                class="flex-1 bg-white/5 hover:bg-white/10 py-2.5 rounded-xl text-sm transition">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleAddForm() {
  const f = document.getElementById('add-form');
  f.classList.toggle('hidden');
}

function editSkill(id, name, category, proficiency) {
  document.getElementById('edit-name').value = name;
  document.getElementById('edit-category').value = category;
  document.getElementById('edit-prof').value = proficiency;
  document.getElementById('prof-display').textContent = proficiency;
  document.getElementById('edit-form').action = `<?= url('/admin/skills/') ?>${id}/update`;
  document.getElementById('edit-modal').classList.remove('hidden');
  document.getElementById('edit-modal').classList.add('flex');
}

function closeEditModal() {
  document.getElementById('edit-modal').classList.add('hidden');
  document.getElementById('edit-modal').classList.remove('flex');
}
</script>
