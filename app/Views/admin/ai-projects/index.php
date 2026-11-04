<?php
$csrfToken = \App\Core\Auth::csrfToken();
$pageTitle = 'AI Projects';
$GLOBALS['pageTitle'] = $pageTitle;
?>
<div class="flex items-center justify-between mb-8">
  <div><h1 class="text-2xl font-bold text-white">AI Projects</h1><p class="text-slate-400 text-sm mt-1"><?= count($projects) ?> projects</p></div>
  <a href="/admin/ai-projects/create" class="btn-primary">+ Add AI Project</a>
</div>
<div class="admin-card overflow-hidden">
  <table class="w-full text-sm">
    <thead><tr class="border-b border-slate-700/50">
      <th class="text-left p-4 text-slate-400 font-medium">Project</th>
      <th class="text-left p-4 text-slate-400 font-medium hidden md:table-cell">Tech Stack</th>
      <th class="text-left p-4 text-slate-400 font-medium">Status</th>
      <th class="text-left p-4 text-slate-400 font-medium">Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($projects as $p): ?>
      <tr class="border-b border-slate-700/30 hover:bg-slate-800/30">
        <td class="p-4"><div class="font-medium text-white"><?= htmlspecialchars($p['title']) ?></div><div class="text-xs text-slate-400 mt-0.5">/ai-projects/<?= htmlspecialchars($p['slug']) ?></div></td>
        <td class="p-4 text-slate-300 text-xs hidden md:table-cell"><?= htmlspecialchars(substr($p['tech_stack']??'',0,60)) ?></td>
        <td class="p-4"><span class="badge <?= $p['status']==='published'?'badge-green':'badge-gray' ?>"><?= $p['status'] ?></span></td>
        <td class="p-4"><div class="flex gap-2">
          <a href="/admin/ai-projects/<?= $p['id'] ?>/edit" class="btn-xs">Edit</a>
          <form method="POST" action="/admin/ai-projects/<?= $p['id'] ?>/delete" onsubmit="return confirm('Delete?')">
            <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
            <button class="btn-xs btn-danger">Del</button>
          </form>
        </div></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (!$projects): ?><div class="p-12 text-center text-slate-500">No AI projects yet. <a href="/admin/ai-projects/create" class="text-crimson">Add first →</a></div><?php endif; ?>
</div>
