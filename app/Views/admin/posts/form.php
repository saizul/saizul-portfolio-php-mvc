<?php
$csrfToken = \App\Core\Auth::csrfToken();
$isEdit    = !empty($post);
$pageTitle = $isEdit ? 'Edit Post' : 'New Post';
$GLOBALS['pageTitle'] = $pageTitle;
$p = $post ?? [];
$postTagIds = array_column($postTags??[], 'id');
?>
<div class="mb-6"><a href="/admin/posts" class="text-slate-400 hover:text-white text-sm">← Back to Posts</a><h1 class="text-2xl font-bold text-white mt-3"><?= $pageTitle ?></h1></div>

<!-- TipTap CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tiptap/2.1.11/tiptap.umd.min.js" defer></script>

<form method="POST" action="<?= $action ?? "/admin/posts/{$p['id']}/update" ?>" class="space-y-6">
  <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-6">

      <div class="admin-card p-6 space-y-5">
        <div><label class="form-label">Title *</label><input type="text" name="title" value="<?= htmlspecialchars($p['title']??'') ?>" class="form-input text-lg font-semibold" required></div>
        <div><label class="form-label">Excerpt</label><textarea name="excerpt" rows="3" class="form-input"><?= htmlspecialchars($p['excerpt']??'') ?></textarea></div>
      </div>

      <!-- Rich text editor -->
      <div class="admin-card p-6">
        <label class="form-label mb-3">Content</label>
        <!-- Toolbar -->
        <div id="editor-toolbar" class="flex flex-wrap gap-1 mb-3 p-2 bg-slate-700/50 rounded-lg">
          <?php foreach (['bold','italic','strike','h2','h3','ul','ol','blockquote','code','link','undo','redo'] as $cmd): ?>
          <button type="button" data-cmd="<?= $cmd ?>" class="toolbar-btn px-2.5 py-1 text-xs rounded bg-slate-600 hover:bg-slate-500 text-white font-mono transition"><?= strtoupper($cmd) ?></button>
          <?php endforeach; ?>
        </div>
        <div id="editor-area" class="min-h-64 bg-slate-900/60 rounded-xl border border-slate-700 p-4 text-white focus:outline-none prose prose-invert max-w-none text-sm" contenteditable="true"><?= $p['body'] ?? '' ?></div>
        <textarea name="body" id="body-hidden" class="hidden"><?= htmlspecialchars($p['body']??'') ?></textarea>
      </div>

      <div class="admin-card p-6 space-y-5">
        <h2 class="font-semibold text-white">SEO</h2>
        <div><label class="form-label">Meta Title <span id="mt-count" class="text-slate-500 font-normal text-xs"></span></label><input type="text" name="meta_title" id="meta-title" value="<?= htmlspecialchars($p['meta_title']??'') ?>" class="form-input" maxlength="60"></div>
        <div><label class="form-label">Meta Description</label><textarea name="meta_description" rows="3" class="form-input" maxlength="160"><?= htmlspecialchars($p['meta_description']??'') ?></textarea></div>
        <div><label class="form-label">OG Image URL</label><input type="text" name="og_image" value="<?= htmlspecialchars($p['og_image']??'') ?>" class="form-input text-xs"></div>
      </div>
    </div>

    <div class="space-y-6">
      <div class="admin-card p-6 space-y-4">
        <h2 class="font-semibold text-white">Publish</h2>
        <div><label class="form-label">Status</label>
          <select name="status" class="form-input">
            <option value="draft" <?= ($p['status']??'draft')==='draft'?'selected':'' ?>>Draft</option>
            <option value="published" <?= ($p['status']??'')==='published'?'selected':'' ?>>Published</option>
          </select>
        </div>
        <div><label class="form-label">Publish Date</label><input type="datetime-local" name="published_at" value="<?= htmlspecialchars(str_replace(' ','T',$p['published_at']??'')) ?>" class="form-input"></div>
        <button type="submit" class="btn-primary w-full">Save Post</button>
      </div>

      <div class="admin-card p-6 space-y-4">
        <h2 class="font-semibold text-white">Category</h2>
        <select name="category_id" class="form-input">
          <option value="">— None —</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= ($p['category_id']??'')==$cat['id']?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="admin-card p-6 space-y-3">
        <h2 class="font-semibold text-white">Tags</h2>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($tags as $tag): ?>
          <label class="flex items-center gap-1.5 cursor-pointer">
            <input type="checkbox" name="tag_ids[]" value="<?= $tag['id'] ?>"
                   <?= in_array($tag['id'],$postTagIds)?'checked':'' ?>
                   class="w-3.5 h-3.5 accent-crimson">
            <span class="text-sm text-slate-300"><?= htmlspecialchars($tag['name']) ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="admin-card p-6 space-y-3">
        <h2 class="font-semibold text-white">Featured Image</h2>
        <div><input type="text" name="featured_image" id="feat-img" value="<?= htmlspecialchars($p['featured_image']??'') ?>" class="form-input text-xs" placeholder="/uploads/..."></div>
        <?php if ($p['featured_image']??''): ?>
        <img src="<?= htmlspecialchars($p['featured_image']) ?>" class="w-full h-36 rounded-lg object-cover" alt="">
        <?php endif; ?>
        <select onchange="if(this.value)document.getElementById('feat-img').value=this.value;this.value='';" class="form-input text-xs">
          <option value="">— Pick from media —</option>
          <?php foreach ($media as $m): ?>
          <option value="<?= htmlspecialchars($m['filepath']) ?>"><?= htmlspecialchars($m['filename']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>
</form>

<script>
// Sync editor to hidden textarea
const editor = document.getElementById('editor-area');
const hidden  = document.getElementById('body-hidden');
editor.addEventListener('input', () => { hidden.value = editor.innerHTML; });

// Toolbar commands
document.querySelectorAll('.toolbar-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const cmd = btn.dataset.cmd;
    const cmds = {
      bold:'bold', italic:'italic', strike:'strikeThrough',
      h2: () => document.execCommand('formatBlock',false,'<h2>'),
      h3: () => document.execCommand('formatBlock',false,'<h3>'),
      ul:'insertUnorderedList', ol:'insertOrderedList',
      blockquote: () => document.execCommand('formatBlock',false,'<blockquote>'),
      code: () => {
        const sel = window.getSelection(); if(!sel.rangeCount) return;
        const range = sel.getRangeAt(0); const code = document.createElement('code');
        try { range.surroundContents(code); } catch(e) {}
      },
      link: () => { const url=prompt('URL:'); if(url) document.execCommand('createLink',false,url); },
      undo: 'undo', redo: 'redo',
    };
    const c = cmds[cmd];
    if (typeof c === 'function') c();
    else if (typeof c === 'string') document.execCommand(c);
    editor.focus();
    hidden.value = editor.innerHTML;
  });
});

// Ctrl+S
document.addEventListener('keydown', e => {
  if ((e.ctrlKey||e.metaKey) && e.key==='s') {
    e.preventDefault();
    hidden.value = editor.innerHTML;
    e.target.closest('form')?.submit();
  }
});

// Meta title counter
const mt = document.getElementById('meta-title');
const mc = document.getElementById('mt-count');
if (mt&&mc) { const u=()=>mc.textContent=mt.value.length+'/60'; mt.addEventListener('input',u); u(); }
</script>
