<?php $pageTitle = 'Blog Posts'; ?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h1 class="text-xl font-bold">Blog Posts</h1>
    <p class="text-sm text-gray-400 mt-0.5">
      <?= number_format($paginated['total']) ?> post<?= $paginated['total'] !== 1 ? 's' : '' ?>
    </p>
  </div>
  <a href="<?= url('/admin/posts/create') ?>"
     class="inline-flex items-center gap-2 bg-white text-black text-sm font-semibold
            px-4 py-2 rounded-xl hover:bg-gray-200 transition shrink-0">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    New Post
  </a>
</div>

<div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">
  <?php if (empty($paginated['data'])): ?>
    <div class="py-16 text-center text-gray-500">
      <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
      </svg>
      <p class="text-sm">No posts yet.</p>
      <a href="<?= url('/admin/posts/create') ?>" class="text-white underline text-sm mt-1 inline-block">Write your first post →</a>
    </div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-white/5 text-left text-xs text-gray-500 uppercase tracking-wider">
          <th class="px-6 py-3 font-medium">Title</th>
          <th class="px-4 py-3 font-medium hidden sm:table-cell">Status</th>
          <th class="px-4 py-3 font-medium hidden md:table-cell">Published</th>
          <th class="px-4 py-3 font-medium text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-white/5">
        <?php foreach ($paginated['data'] as $post): ?>
        <tr class="hover:bg-white/2 transition group">
          <td class="px-6 py-4">
            <p class="font-medium text-white truncate max-w-xs"><?= e($post['title']) ?></p>
            <p class="text-xs text-gray-500 mt-0.5 font-mono">/blog/<?= e($post['slug']) ?></p>
          </td>
          <td class="px-4 py-4 hidden sm:table-cell">
            <span class="text-xs px-2.5 py-1 rounded-full font-medium
              <?= $post['status'] === 'published' ? 'bg-green-500/15 text-green-400' : ($post['status'] === 'archived' ? 'bg-gray-700 text-gray-400' : 'bg-amber-500/15 text-amber-400') ?>">
              <?= e($post['status']) ?>
            </span>
          </td>
          <td class="px-4 py-4 text-gray-400 text-xs hidden md:table-cell">
            <?= $post['published_at'] ? date('M j, Y', strtotime($post['published_at'])) : '—' ?>
          </td>
          <td class="px-4 py-4">
            <div class="flex items-center justify-end gap-2">
              <?php if ($post['status'] === 'published'): ?>
              <a href="<?= url('/blog/' . $post['slug']) ?>" target="_blank"
                 class="text-gray-500 hover:text-white transition p-1.5 rounded-lg hover:bg-white/10"
                 title="View">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
              </a>
              <?php endif; ?>
              <a href="<?= url('/admin/posts/' . $post['id'] . '/edit') ?>"
                 class="text-gray-500 hover:text-white transition p-1.5 rounded-lg hover:bg-white/10"
                 title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </a>
              <form method="POST" action="<?= url('/admin/posts/' . $post['id'] . '/delete') ?>"
                    onsubmit="return confirm('Delete this post? This cannot be undone.')">
                <?= \App\Core\Auth::csrfField() ?>
                <button type="submit"
                        class="text-gray-600 hover:text-red-400 transition p-1.5 rounded-lg hover:bg-red-500/10"
                        title="Delete">
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

  <!-- Pagination -->
  <?php if ($paginated['last_page'] > 1): ?>
  <div class="flex items-center justify-between px-6 py-4 border-t border-white/5 text-sm">
    <p class="text-gray-500 text-xs">
      Page <?= $paginated['current_page'] ?> of <?= $paginated['last_page'] ?>
    </p>
    <div class="flex gap-2">
      <?php for ($p = 1; $p <= $paginated['last_page']; $p++): ?>
      <a href="?page=<?= $p ?>"
         class="w-8 h-8 flex items-center justify-center rounded-lg text-xs
                <?= $p === $paginated['current_page'] ? 'bg-white text-black font-bold' : 'bg-white/5 text-gray-400 hover:bg-white/10' ?>">
        <?= $p ?>
      </a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>
