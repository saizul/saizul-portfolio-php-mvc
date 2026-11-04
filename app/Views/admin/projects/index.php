<?php $pageTitle = 'Projects'; ?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h1 class="text-xl font-bold">Projects</h1>
    <p class="text-sm text-gray-400 mt-0.5"><?= count($rows) ?> project<?= count($rows) !== 1 ? 's' : '' ?></p>
  </div>
  <a href="<?= url('/admin/projects/create') ?>"
     class="inline-flex items-center gap-2 bg-white text-black text-sm font-semibold
            px-4 py-2 rounded-xl hover:bg-gray-200 transition shrink-0">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    New Project
  </a>
</div>

<div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">
  <?php if (empty($rows)): ?>
    <div class="py-16 text-center text-gray-500 text-sm">No projects yet. <a href="<?= url('/admin/projects/create') ?>" class="text-white underline">Add one →</a></div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-white/5 text-left text-xs text-gray-500 uppercase tracking-wider">
          <th class="px-6 py-3 font-medium">Title</th>
          <th class="px-4 py-3 font-medium hidden sm:table-cell">Type</th>
          <th class="px-4 py-3 font-medium hidden md:table-cell">Status</th>
          <th class="px-4 py-3 font-medium hidden lg:table-cell">Featured</th>
          <th class="px-4 py-3 font-medium text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-white/5" id="projects-tbody">
        <?php foreach ($rows as $proj): ?>
        <tr data-id="<?= $proj['id'] ?>" class="hover:bg-white/2 transition cursor-grab active:cursor-grabbing">
          <td class="px-6 py-4">
            <p class="font-medium text-white"><?= e($proj['title']) ?></p>
            <p class="text-xs text-gray-500 mt-0.5 font-mono"><?= e($proj['slug']) ?></p>
          </td>
          <td class="px-4 py-4 hidden sm:table-cell">
            <span class="text-xs px-2.5 py-1 rounded-full
              <?= $proj['project_type'] === 'ai' ? 'bg-violet-500/15 text-violet-400' : 'bg-blue-500/15 text-blue-400' ?>">
              <?= e($proj['project_type']) ?>
            </span>
          </td>
          <td class="px-4 py-4 hidden md:table-cell">
            <span class="text-xs px-2.5 py-1 rounded-full
              <?= $proj['status'] === 'active' ? 'bg-green-500/15 text-green-400' : 'bg-gray-700 text-gray-400' ?>">
              <?= e($proj['status']) ?>
            </span>
          </td>
          <td class="px-4 py-4 hidden lg:table-cell">
            <span class="<?= $proj['is_featured'] ? 'text-amber-400' : 'text-gray-600' ?>">
              <?= $proj['is_featured'] ? '★' : '☆' ?>
            </span>
          </td>
          <td class="px-4 py-4">
            <div class="flex items-center justify-end gap-2">
              <a href="<?= url('/admin/projects/' . $proj['id'] . '/edit') ?>"
                 class="text-gray-500 hover:text-white transition p-1.5 rounded-lg hover:bg-white/10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </a>
              <form method="POST" action="<?= url('/admin/projects/' . $proj['id'] . '/delete') ?>"
                    onsubmit="return confirm('Delete this project?')">
                <?= \App\Core\Auth::csrfField() ?>
                <button type="submit" class="text-gray-600 hover:text-red-400 transition p-1.5 rounded-lg hover:bg-red-500/10">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
