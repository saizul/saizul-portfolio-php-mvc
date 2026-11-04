<?php
/** Page Builder — Pages List (index) */
use App\Core\Auth;
?>
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Pages</h1>
        <p class="admin-page-subtitle">Build and manage all public pages with the drag-and-drop editor.</p>
    </div>
    <button class="btn btn-primary" onclick="openCreateModal()">+ New Page</button>
</div>

<?php if (isset($_GET['saved'])): ?>
<div class="admin-alert success">✓ Page saved successfully.</div>
<?php elseif (isset($_GET['deleted'])): ?>
<div class="admin-alert success">✓ Page deleted.</div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-body" style="padding:0;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Type</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Built-in pages
                $builtIn = [
                    ['slug'=>'home','title'=>'Home','type'=>'Built-in'],
                    ['slug'=>'about','title'=>'About','type'=>'Built-in'],
                    ['slug'=>'ai-projects','title'=>'AI Projects','type'=>'Built-in'],
                    ['slug'=>'experience','title'=>'Experience','type'=>'Built-in'],
                    ['slug'=>'skills','title'=>'Skills','type'=>'Built-in'],
                    ['slug'=>'portfolio','title'=>'Portfolio','type'=>'Built-in'],
                    ['slug'=>'contact','title'=>'Contact','type'=>'Built-in'],
                    ['slug'=>'blog','title'=>'Blog','type'=>'Built-in'],
                ];
                // Custom pages from DB
                $customPages = $pages ?? [];
                $allPages = array_merge($builtIn, array_map(fn($p)=>array_merge($p,['type'=>'Custom']), $customPages));
                foreach ($allPages as $page):
                    $updatedAt = $page['updated_at'] ?? $page['created_at'] ?? 'Never';
                ?>
                <tr>
                    <td style="font-weight:600;color:#e2e8f0;"><?= htmlspecialchars($page['title']) ?></td>
                    <td><code class="slug-code">/<?= htmlspecialchars($page['slug'] === 'home' ? '' : $page['slug']) ?></code></td>
                    <td><span class="badge <?= $page['type'] === 'Built-in' ? 'badge-blue' : 'badge-emerald' ?>"><?= $page['type'] ?></span></td>
                    <td style="color:#64748b;font-size:0.875rem;"><?= is_string($updatedAt) ? htmlspecialchars(substr($updatedAt,0,10)) : 'Never' ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="/admin/pages/<?= htmlspecialchars($page['slug']) ?>/edit" class="btn-icon" title="Edit in Builder">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <a href="/<?= htmlspecialchars($page['slug'] === 'home' ? '' : $page['slug']) ?>" target="_blank" class="btn-icon" title="View Public Page">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>
                            <?php if ($page['type'] === 'Custom'): ?>
                            <form method="POST" action="/admin/pages/<?= htmlspecialchars($page['slug']) ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this page?')">
                                <?= Auth::csrfField() ?>
                                <button type="submit" class="btn-icon danger" title="Delete">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create New Page Modal -->
<div class="modal-overlay" id="createModal" style="display:none;">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <h3>Create New Page</h3>
            <button class="modal-close" onclick="closeCreateModal()">×</button>
        </div>
        <form method="POST" action="/admin/pages/create">
            <?= Auth::csrfField() ?>
            <div class="modal-body" style="display:grid;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Page Title *</label>
                    <input type="text" name="title" id="newPageTitle" class="form-input" placeholder="My Custom Page" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Page Slug *
                        <span class="form-hint" style="font-weight:400;">yourdomain.com/<span id="slugPreview" style="color:#6ee7b7;">my-custom-page</span></span>
                    </label>
                    <input type="text" name="slug" id="newPageSlug" class="form-input" placeholder="my-custom-page" pattern="[a-z0-9\-]+" required>
                    <p class="form-hint">Lowercase letters, numbers, hyphens only.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Page Template</label>
                    <select name="template" class="form-select">
                        <option value="blank">Blank Page</option>
                        <option value="landing">Landing Page (Hero + Content + CTA)</option>
                        <option value="content">Content Page (Header + Body)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create & Open Builder</button>
            </div>
        </form>
    </div>
</div>

<style>
.slug-code { background:rgba(255,255,255,0.06);padding:2px 8px;border-radius:4px;font-size:0.8125rem;font-family:'JetBrains Mono','Courier New',monospace;color:#6ee7b7; }
</style>

<script>
function openCreateModal()  { document.getElementById('createModal').style.display='flex'; document.getElementById('newPageTitle').focus(); }
function closeCreateModal() { document.getElementById('createModal').style.display='none'; }

// Auto-slug from title
document.getElementById('newPageTitle')?.addEventListener('input', function() {
    const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
    document.getElementById('newPageSlug').value = slug;
    document.getElementById('slugPreview').textContent = slug || 'my-page';
});
document.getElementById('newPageSlug')?.addEventListener('input', function() {
    document.getElementById('slugPreview').textContent = this.value || 'my-page';
});

// Close modal on backdrop click
document.getElementById('createModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeCreateModal();
});
</script>
