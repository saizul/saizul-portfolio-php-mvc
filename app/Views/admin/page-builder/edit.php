<?php
use App\Core\Auth;
$pageData   = $page ?? [];
$pageTitle  = htmlspecialchars($pageData['title'] ?? 'Untitled Page');
$pageSlug   = $pageData['slug'] ?? '';
$contentJson = $pageData['content_json'] ?? '[]';
// Parse existing blocks
$blocks = json_decode($contentJson, true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Builder — <?= $pageTitle ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <style>
/* ═══════════════════════════════════════════════════════════
   PAGE BUILDER SHELL
═══════════════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
body { background:#080c10; color:#e2e8f0; font-family:'Inter',sans-serif; overflow:hidden; height:100vh; display:flex; flex-direction:column; }

/* Top toolbar */
.pb-toolbar {
    height:56px; background:#0d1b2a; border-bottom:1px solid #1e3040;
    display:flex; align-items:center; gap:12px; padding:0 20px; flex-shrink:0; z-index:200;
}
.pb-toolbar-title { font-size:0.9375rem; font-weight:600; color:#e2e8f0; flex:1; }
.pb-toolbar-slug  { font-size:0.75rem; color:#475569; font-family:'JetBrains Mono',monospace; }
.pb-device-btns   { display:flex; gap:4px; background:#080c10; border-radius:8px; padding:3px; }
.pb-device-btn    { background:none; border:none; color:#475569; padding:5px 9px; border-radius:6px; cursor:pointer; transition:all 0.15s; }
.pb-device-btn.active { background:#1e3040; color:#e2e8f0; }
.pb-saving { font-size:0.75rem; color:#6ee7b7; opacity:0; transition:opacity 0.3s; }
.pb-saving.show { opacity:1; }

/* Main layout: sidebar + canvas */
.pb-body { display:flex; flex:1; overflow:hidden; }

/* Left sidebar: block library */
.pb-sidebar {
    width:260px; background:#0a1628; border-right:1px solid #1e3040;
    display:flex; flex-direction:column; overflow-y:auto; flex-shrink:0;
}
.pb-sidebar-section { padding:16px; border-bottom:1px solid #1e3040; }
.pb-sidebar-section h4 { font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#475569; margin-bottom:12px; }
.pb-block-btn {
    display:flex; align-items:center; gap:10px; width:100%; padding:9px 12px;
    background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06);
    border-radius:8px; color:#94a3b8; font-size:0.8125rem; font-weight:500;
    cursor:grab; transition:all 0.15s; margin-bottom:6px; text-align:left;
    user-select:none;
}
.pb-block-btn:hover { background:rgba(192,57,43,0.08); border-color:rgba(192,57,43,0.3); color:#e2e8f0; }
.pb-block-btn:active { cursor:grabbing; }
.pb-block-icon { width:28px; height:28px; border-radius:6px; background:rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }

/* Canvas area */
.pb-canvas-wrap {
    flex:1; overflow-y:auto; background:#0e1c2e;
    display:flex; justify-content:center; padding:24px;
    position:relative;
}
.pb-canvas {
    width:100%; max-width:100%; background:#080c10;
    border-radius:12px; border:1px solid #1e3040; min-height:600px;
    position:relative; transition:max-width 0.3s ease;
}
.pb-canvas.preview-tablet { max-width:768px; }
.pb-canvas.preview-mobile { max-width:390px; }

/* Drop zone */
.pb-dropzone {
    min-height:60px; border:2px dashed transparent; border-radius:8px;
    transition:all 0.2s; padding:4px; margin:4px;
}
.pb-dropzone.drag-over { border-color:rgba(192,57,43,0.5); background:rgba(192,57,43,0.05); }

/* Block wrapper */
.pb-block {
    position:relative; margin:0; border-radius:8px;
    border:2px solid transparent; transition:border-color 0.2s;
}
.pb-block:hover { border-color:rgba(255,255,255,0.1); }
.pb-block.selected { border-color:#c0392b; }

/* Block toolbar (shows on hover) */
.pb-block-toolbar {
    position:absolute; top:-38px; left:0; z-index:50;
    display:none; gap:4px; align-items:center;
    background:#0d1b2a; border:1px solid #1e3040;
    border-radius:8px; padding:4px; box-shadow:0 4px 16px rgba(0,0,0,0.4);
}
.pb-block:hover .pb-block-toolbar,
.pb-block.selected .pb-block-toolbar { display:flex; }
.pb-block-toolbar-btn {
    background:none; border:none; color:#94a3b8; padding:4px 7px;
    border-radius:5px; cursor:pointer; font-size:11px; display:flex; align-items:center; gap:4px;
    transition:all 0.15s;
}
.pb-block-toolbar-btn:hover { background:rgba(255,255,255,0.06); color:#e2e8f0; }
.pb-block-toolbar-btn.danger:hover { background:rgba(248,113,113,0.12); color:#f87171; }
.pb-block-drag-handle { cursor:grab; color:#475569; padding:4px 6px; }
.pb-block-drag-handle:active { cursor:grabbing; }

/* Empty canvas hint */
.pb-empty {
    position:absolute; inset:0; display:flex; flex-direction:column;
    align-items:center; justify-content:center; gap:12px; color:#475569;
    pointer-events:none;
}
.pb-empty svg { opacity:0.3; }
.pb-empty p { font-size:0.875rem; text-align:center; line-height:1.6; }

/* Right panel: block settings */
.pb-panel {
    width:300px; background:#0a1628; border-left:1px solid #1e3040;
    overflow-y:auto; flex-shrink:0; display:flex; flex-direction:column;
}
.pb-panel-header { padding:16px; border-bottom:1px solid #1e3040; display:flex; align-items:center; justify-content:space-between; }
.pb-panel-title  { font-size:0.875rem; font-weight:600; color:#e2e8f0; }
.pb-panel-body   { padding:16px; flex:1; overflow-y:auto; }
.pb-panel-empty  { padding:32px 16px; text-align:center; color:#475569; font-size:0.875rem; }
.pb-panel-field  { margin-bottom:16px; }
.pb-panel-label  { display:block; font-size:0.75rem; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px; }
.pb-panel input,
.pb-panel textarea,
.pb-panel select {
    width:100%; background:#080c10; border:1px solid #1e3040; color:#e2e8f0;
    padding:8px 10px; border-radius:6px; font-size:0.8125rem; font-family:inherit;
    transition:border-color 0.15s;
}
.pb-panel input:focus,
.pb-panel textarea:focus,
.pb-panel select:focus { outline:none; border-color:#c0392b; }
.pb-panel textarea { resize:vertical; min-height:80px; line-height:1.5; }
.pb-panel .color-row { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.pb-panel .align-btns { display:flex; gap:4px; }
.pb-panel .align-btn { flex:1; background:rgba(255,255,255,0.04); border:1px solid #1e3040; color:#94a3b8; padding:6px; border-radius:5px; cursor:pointer; font-size:11px; transition:all 0.15s; }
.pb-panel .align-btn.active,
.pb-panel .align-btn:hover { background:rgba(192,57,43,0.1); border-color:rgba(192,57,43,0.3); color:#e2e8f0; }

/* ─── Block Preview Styles ─── */
.pb-block-hero { padding:80px 40px; background:linear-gradient(135deg,#0d1b2a,#1a2f35); text-align:center; border-radius:10px; }
.pb-block-hero h1 { font-size:3rem; font-weight:800; color:#fff; margin-bottom:16px; letter-spacing:-0.03em; }
.pb-block-hero p  { font-size:1.125rem; color:#94a3b8; max-width:520px; margin:0 auto 32px; }
.pb-block-hero .hero-btns { display:flex; gap:12px; justify-content:center; flex-wrap:wrap; }
.pb-block-hero .btn-primary-preview  { background:#c0392b; color:#fff; padding:12px 28px; border-radius:8px; font-weight:600; font-size:0.9375rem; }
.pb-block-hero .btn-outline-preview  { border:1px solid rgba(255,255,255,0.2); color:#fff; padding:12px 28px; border-radius:8px; font-weight:600; font-size:0.9375rem; }
.pb-block-text  { padding:40px; }
.pb-block-text h2 { font-size:1.75rem; font-weight:700; margin-bottom:12px; }
.pb-block-text p  { color:#94a3b8; line-height:1.8; }
.pb-block-image   { padding:20px; }
.pb-block-image img { width:100%; border-radius:10px; max-height:400px; object-fit:cover; }
.pb-block-image .img-placeholder { background:#1a2f35; border-radius:10px; height:250px; display:flex; align-items:center; justify-content:center; color:#475569; font-size:0.875rem; }
.pb-block-gallery { padding:20px; display:grid; gap:12px; }
.pb-block-gallery.cols-2 { grid-template-columns:repeat(2,1fr); }
.pb-block-gallery.cols-3 { grid-template-columns:repeat(3,1fr); }
.pb-block-gallery.cols-4 { grid-template-columns:repeat(4,1fr); }
.pb-block-gallery .gal-item { background:#1a2f35; border-radius:8px; aspect-ratio:1; display:flex; align-items:center; justify-content:center; color:#475569; font-size:12px; }
.pb-block-cta { padding:64px 40px; text-align:center; background:#1a2f35; border-radius:10px; }
.pb-block-cta h2 { font-size:2rem; font-weight:800; margin-bottom:12px; }
.pb-block-cta p  { color:#94a3b8; margin-bottom:28px; }
.pb-block-cards  { padding:40px 20px; }
.pb-block-cards h2 { text-align:center; font-size:1.75rem; font-weight:700; margin-bottom:28px; }
.pb-block-cards-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
.pb-block-cards-grid .card-item { background:#1a2f35; border-radius:12px; padding:24px; }
.pb-block-cards-grid .card-item h3 { font-size:1rem; font-weight:600; margin-bottom:8px; }
.pb-block-cards-grid .card-item p  { font-size:0.875rem; color:#94a3b8; }
.pb-block-divider { padding:20px 40px; display:flex; align-items:center; justify-content:center; }
.pb-block-divider hr { width:100%; border:none; border-top:1px solid #1e3040; }
.pb-block-embed { padding:20px; }
.pb-block-embed .embed-placeholder { background:#1a2f35; border-radius:10px; height:200px; display:flex; align-items:center; justify-content:center; color:#475569; font-size:0.875rem; text-align:center; }

/* Drag ghost */
.pb-drag-ghost { opacity:0.5; pointer-events:none; }

/* Save button states */
.btn-save { min-width:100px; }
.btn-save.saving { background:#1a2f35 !important; color:#6ee7b7; }
</style>
</head>
<body>

<!-- Toolbar -->
<div class="pb-toolbar">
    <a href="/admin/pages" class="btn btn-ghost btn-sm" style="flex-shrink:0;">← Pages</a>
    <div>
        <div class="pb-toolbar-title"><?= $pageTitle ?></div>
        <div class="pb-toolbar-slug">/<?= htmlspecialchars($pageSlug === 'home' ? '' : $pageSlug) ?></div>
    </div>
    <div class="pb-device-btns">
        <button class="pb-device-btn active" data-device="desktop" title="Desktop" onclick="setDevice('desktop',this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        </button>
        <button class="pb-device-btn" data-device="tablet" title="Tablet" onclick="setDevice('tablet',this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="18" r="1"/></svg>
        </button>
        <button class="pb-device-btn" data-device="mobile" title="Mobile" onclick="setDevice('mobile',this)">
            <svg width="14" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><circle cx="12" cy="18" r="1"/></svg>
        </button>
    </div>
    <span class="pb-saving" id="savingMsg">Saved ✓</span>
    <a href="/<?= htmlspecialchars($pageSlug === 'home' ? '' : $pageSlug) ?>" target="_blank" class="btn btn-ghost btn-sm">Preview ↗</a>
    <button class="btn btn-primary btn-sm btn-save" id="saveBtn" onclick="savePage()">Save</button>
</div>

<!-- Body -->
<div class="pb-body">

    <!-- Left Sidebar: Block Library -->
    <div class="pb-sidebar">
        <div style="padding:12px 16px;border-bottom:1px solid #1e3040;">
            <input type="text" class="form-input" id="blockSearch" placeholder="Search blocks…" style="font-size:0.8125rem;" oninput="filterBlocks(this.value)">
        </div>

        <div class="pb-sidebar-section" id="blockLibrary">
            <h4>Layout</h4>
            <button class="pb-block-btn" data-type="hero" draggable="true">
                <span class="pb-block-icon">🦸</span>Hero Section
            </button>
            <button class="pb-block-btn" data-type="text" draggable="true">
                <span class="pb-block-icon">📝</span>Text / Heading
            </button>
            <button class="pb-block-btn" data-type="two-col" draggable="true">
                <span class="pb-block-icon">⬜</span>Two Columns
            </button>
            <button class="pb-block-btn" data-type="divider" draggable="true">
                <span class="pb-block-icon">➖</span>Divider
            </button>

            <h4 style="margin-top:16px;">Media</h4>
            <button class="pb-block-btn" data-type="image" draggable="true">
                <span class="pb-block-icon">🖼️</span>Image
            </button>
            <button class="pb-block-btn" data-type="gallery" draggable="true">
                <span class="pb-block-icon">🗃️</span>Gallery
            </button>
            <button class="pb-block-btn" data-type="video" draggable="true">
                <span class="pb-block-icon">▶️</span>Video / Embed
            </button>

            <h4 style="margin-top:16px;">Conversion</h4>
            <button class="pb-block-btn" data-type="cta" draggable="true">
                <span class="pb-block-icon">📣</span>Call to Action
            </button>
            <button class="pb-block-btn" data-type="cards" draggable="true">
                <span class="pb-block-icon">🃏</span>Cards Grid
            </button>
            <button class="pb-block-btn" data-type="skills" draggable="true">
                <span class="pb-block-icon">⚡</span>Skills Grid
            </button>
            <button class="pb-block-btn" data-type="timeline" draggable="true">
                <span class="pb-block-icon">📅</span>Timeline
            </button>
            <button class="pb-block-btn" data-type="form" draggable="true">
                <span class="pb-block-icon">📋</span>Contact Form
            </button>
        </div>
    </div>

    <!-- Canvas -->
    <div class="pb-canvas-wrap">
        <div class="pb-canvas" id="pbCanvas">
            <div class="pb-dropzone" id="rootDropzone"></div>
            <div id="blockList">
                <!-- Blocks rendered here by JS -->
            </div>
            <div class="pb-dropzone" id="bottomDropzone"></div>

            <div class="pb-empty" id="emptyHint">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                <p>Drag blocks from the left panel<br>to start building your page.</p>
            </div>
        </div>
    </div>

    <!-- Right Panel: Block Settings -->
    <div class="pb-panel" id="pbPanel">
        <div class="pb-panel-header">
            <span class="pb-panel-title" id="panelTitle">Block Settings</span>
            <button class="btn-icon" onclick="deselectBlock()" title="Close">×</button>
        </div>
        <div id="panelBody">
            <div class="pb-panel-empty">Select a block to edit its settings.</div>
        </div>
    </div>

</div>

<!-- Hidden form for save -->
<form method="POST" action="/admin/pages/<?= htmlspecialchars($pageSlug) ?>/save" id="saveForm" style="display:none;">
    <?= Auth::csrfField() ?>
    <input type="hidden" name="content_json" id="contentJsonInput">
    <input type="hidden" name="title" value="<?= htmlspecialchars($pageData['title'] ?? '') ?>">
</form>

<script>
/* ═══════════════════════════════════════════════════════════════
   PAGE BUILDER ENGINE
═══════════════════════════════════════════════════════════════ */

/* ── State ── */
let blocks = <?= json_encode($blocks, JSON_UNESCAPED_UNICODE) ?>;
let selectedId = null;
let dragSource = null;
let dragType   = null;  // 'new' | 'reorder'

/* ── Unique ID generator ── */
const uid = () => 'blk_' + Date.now().toString(36) + Math.random().toString(36).slice(2,6);

/* ── Default block data ── */
function defaultBlock(type) {
    const id = uid();
    const defaults = {
        hero:    { id, type:'hero',    heading:'Your Headline Here', subheading:'A compelling tagline that converts visitors.', btn1Text:'Get Started', btn1Url:'#', btn2Text:'Learn More', btn2Url:'#', align:'center', bgColor:'', overlay:true },
        text:    { id, type:'text',    heading:'Section Title', body:'Add your content here. Click to edit this text block.', align:'left', headingSize:'h2' },
        image:   { id, type:'image',   src:'', alt:'', caption:'', fullWidth:false },
        gallery: { id, type:'gallery', images:[], cols:3, gap:12 },
        cta:     { id, type:'cta',     heading:'Ready to work together?', body:'Let\'s build something amazing.', btnText:'Get in Touch', btnUrl:'/contact', align:'center', bgColor:'' },
        cards:   { id, type:'cards',   heading:'', items:[{title:'Card One',body:'Description for card one.',icon:'⚡'},{title:'Card Two',body:'Description for card two.',icon:'🚀'},{title:'Card Three',body:'Description for card three.',icon:'🎯'}], cols:3 },
        skills:  { id, type:'skills',  heading:'Skills', items:[{name:'Python',pct:90},{name:'React',pct:80},{name:'PHP',pct:85}] },
        timeline:{ id, type:'timeline',heading:'Experience', items:[{date:'2023–Present',role:'AI Engineer',company:'Company Name',desc:'Description of role.'}] },
        divider: { id, type:'divider', style:'line', spacing:'md' },
        'two-col':{ id, type:'two-col', left:'Left column content.', right:'Right column content.', leftWidth:50 },
        video:   { id, type:'video',   src:'', caption:'' },
        form:    { id, type:'form',    heading:'Send a Message', fields:['name','email','message'], btnText:'Send Message' },
    };
    return defaults[type] || { id, type, heading:'New Block' };
}

/* ═══════════════════════════════════════════════════════════════
   RENDER ENGINE
═══════════════════════════════════════════════════════════════ */
function renderAll() {
    const container = document.getElementById('blockList');
    container.innerHTML = '';
    blocks.forEach((b, idx) => container.appendChild(renderBlock(b, idx)));
    document.getElementById('emptyHint').style.display = blocks.length ? 'none' : 'flex';
    syncJson();
}

function renderBlock(b, idx) {
    const wrap = document.createElement('div');
    wrap.className = 'pb-block' + (selectedId === b.id ? ' selected' : '');
    wrap.dataset.id  = b.id;
    wrap.dataset.idx = idx;

    // Toolbar
    wrap.innerHTML = `
    <div class="pb-block-toolbar">
        <span class="pb-block-drag-handle" title="Drag to reorder">⠿</span>
        <button class="pb-block-toolbar-btn" onclick="moveBlock('${b.id}',-1)" title="Move Up">↑</button>
        <button class="pb-block-toolbar-btn" onclick="moveBlock('${b.id}',1)" title="Move Down">↓</button>
        <button class="pb-block-toolbar-btn" onclick="duplicateBlock('${b.id}')" title="Duplicate">⧉</button>
        <span style="color:#1e3040;font-size:11px;">|</span>
        <span style="font-size:11px;color:#64748b;padding:0 4px;">${b.type}</span>
        <span style="color:#1e3040;font-size:11px;">|</span>
        <button class="pb-block-toolbar-btn danger" onclick="deleteBlock('${b.id}')" title="Delete">✕</button>
    </div>
    ${buildBlockPreview(b)}`;

    // Select on click
    wrap.addEventListener('click', (e) => {
        if (e.target.closest('.pb-block-toolbar')) return;
        selectBlock(b.id);
    });

    // Drag to reorder
    const handle = wrap.querySelector('.pb-block-drag-handle');
    if (handle) {
        handle.addEventListener('mousedown', () => { wrap.draggable = true; });
        handle.addEventListener('mouseup',   () => { wrap.draggable = false; });
    }
    wrap.addEventListener('dragstart', e => {
        dragSource = b.id; dragType = 'reorder';
        e.dataTransfer.effectAllowed = 'move';
        setTimeout(() => wrap.classList.add('pb-drag-ghost'), 0);
    });
    wrap.addEventListener('dragend', () => { wrap.draggable=false; wrap.classList.remove('pb-drag-ghost'); });
    wrap.addEventListener('dragover', e => { e.preventDefault(); e.dataTransfer.dropEffect='move'; });
    wrap.addEventListener('drop', e => {
        e.stopPropagation(); e.preventDefault();
        if (dragType === 'reorder' && dragSource && dragSource !== b.id) {
            reorderBlocks(dragSource, b.id);
        } else if (dragType === 'new' && dragSource) {
            const newBlock = defaultBlock(dragSource);
            const targetIdx = blocks.findIndex(x => x.id === b.id);
            blocks.splice(targetIdx, 0, newBlock);
            renderAll();
            selectBlock(newBlock.id);
            dragSource = null; dragType = null;
        }
    });

    return wrap;
}

function buildBlockPreview(b) {
    switch (b.type) {
        case 'hero':
            return `<div class="pb-block-hero" style="text-align:${b.align||'center'};${b.bgColor?'background:'+b.bgColor:''}">
                <h1>${esc(b.heading)}</h1>
                <p>${esc(b.subheading)}</p>
                <div class="hero-btns">
                    ${b.btn1Text?`<span class="btn-primary-preview">${esc(b.btn1Text)}</span>`:''}
                    ${b.btn2Text?`<span class="btn-outline-preview">${esc(b.btn2Text)}</span>`:''}
                </div>
            </div>`;
        case 'text':
            const tag = b.headingSize || 'h2';
            return `<div class="pb-block-text" style="text-align:${b.align||'left'}">
                ${b.heading?`<${tag}>${esc(b.heading)}</${tag}>`:''}
                <p>${esc(b.body||'')}</p>
            </div>`;
        case 'image':
            return `<div class="pb-block-image">
                ${b.src ? `<img src="${esc(b.src)}" alt="${esc(b.alt||'')}">` : '<div class="img-placeholder">📷 No image selected</div>'}
                ${b.caption?`<p style="text-align:center;font-size:0.8125rem;color:#64748b;margin-top:8px;">${esc(b.caption)}</p>`:''}
            </div>`;
        case 'gallery':
            const cols = Math.min(Math.max(parseInt(b.cols)||3,1),4);
            const items = (b.images||[]).length || cols;
            return `<div class="pb-block-gallery cols-${cols}" style="gap:${b.gap||12}px">
                ${Array.from({length:Math.max(items,cols)}).map((_,i)=>
                    b.images&&b.images[i]
                    ? `<img src="${esc(b.images[i])}" style="width:100%;border-radius:8px;aspect-ratio:1;object-fit:cover;">`
                    : `<div class="gal-item">📷 ${i+1}</div>`
                ).join('')}
            </div>`;
        case 'cta':
            return `<div class="pb-block-cta" style="text-align:${b.align||'center'};${b.bgColor?'background:'+b.bgColor:''}">
                <h2>${esc(b.heading)}</h2>
                <p>${esc(b.body||'')}</p>
                ${b.btnText?`<span class="btn-primary-preview">${esc(b.btnText)}</span>`:''}
            </div>`;
        case 'cards':
            return `<div class="pb-block-cards">
                ${b.heading?`<h2>${esc(b.heading)}</h2>`:''}
                <div class="pb-block-cards-grid" style="grid-template-columns:repeat(${Math.min(parseInt(b.cols)||3,4)},1fr)">
                    ${(b.items||[]).map(item=>`<div class="card-item">
                        ${item.icon?`<div style="font-size:1.5rem;margin-bottom:8px;">${esc(item.icon)}</div>`:''}
                        <h3>${esc(item.title||'')}</h3><p>${esc(item.body||'')}</p>
                    </div>`).join('')}
                </div>
            </div>`;
        case 'skills':
            return `<div style="padding:32px 40px;">
                ${b.heading?`<h2 style="font-size:1.75rem;font-weight:700;margin-bottom:24px;">${esc(b.heading)}</h2>`:''}
                ${(b.items||[]).map(s=>`
                <div style="margin-bottom:14px;">
                    <div style="display:flex;justify-content:space-between;font-size:0.875rem;margin-bottom:5px;">
                        <span>${esc(s.name||'')}</span><span style="color:#6ee7b7;">${s.pct||0}%</span>
                    </div>
                    <div style="height:4px;background:#1e3040;border-radius:4px;">
                        <div style="height:100%;width:${s.pct||0}%;background:linear-gradient(90deg,#c0392b,#6ee7b7);border-radius:4px;"></div>
                    </div>
                </div>`).join('')}
            </div>`;
        case 'timeline':
            return `<div style="padding:32px 40px;">
                ${b.heading?`<h2 style="font-size:1.75rem;font-weight:700;margin-bottom:24px;">${esc(b.heading)}</h2>`:''}
                ${(b.items||[]).map(item=>`<div style="display:flex;gap:20px;margin-bottom:24px;">
                    <div style="width:3px;background:#c0392b;border-radius:2px;flex-shrink:0;"></div>
                    <div>
                        <div style="font-size:0.75rem;color:#6ee7b7;font-weight:600;margin-bottom:2px;">${esc(item.date||'')}</div>
                        <div style="font-size:1rem;font-weight:600;margin-bottom:2px;">${esc(item.role||'')} ${item.company?'— '+esc(item.company):''}</div>
                        <div style="font-size:0.875rem;color:#94a3b8;">${esc(item.desc||'')}</div>
                    </div>
                </div>`).join('')}
            </div>`;
        case 'divider':
            const sp = {sm:'8px',md:'24px',lg:'48px'}[b.spacing||'md'];
            return `<div class="pb-block-divider" style="padding:${sp} 40px;"><hr></div>`;
        case 'two-col':
            const lw = parseInt(b.leftWidth)||50;
            return `<div style="display:grid;grid-template-columns:${lw}fr ${100-lw}fr;gap:32px;padding:40px;">
                <div><p style="color:#94a3b8;">${esc(b.left||'')}</p></div>
                <div><p style="color:#94a3b8;">${esc(b.right||'')}</p></div>
            </div>`;
        case 'video':
            return `<div class="pb-block-embed">
                ${b.src ? `<div class="embed-placeholder">▶ ${esc(b.src)}</div>` : '<div class="embed-placeholder">▶ Paste a YouTube / Vimeo URL in settings</div>'}
            </div>`;
        case 'form':
            return `<div style="padding:40px;max-width:560px;margin:0 auto;">
                ${b.heading?`<h2 style="font-size:1.5rem;font-weight:700;margin-bottom:20px;">${esc(b.heading)}</h2>`:''}
                ${(b.fields||['name','email','message']).map(f=>`
                <div style="margin-bottom:12px;"><label style="font-size:0.75rem;color:#64748b;text-transform:capitalize;">${esc(f)}</label>
                ${f==='message'?`<div style="height:80px;background:#1a2f35;border-radius:6px;margin-top:4px;"></div>`:`<div style="height:36px;background:#1a2f35;border-radius:6px;margin-top:4px;"></div>`}
                </div>`).join('')}
                <span class="btn-primary-preview" style="display:inline-block;margin-top:8px;">${esc(b.btnText||'Submit')}</span>
            </div>`;
        default:
            return `<div style="padding:20px;color:#64748b;">Unknown block type: ${esc(b.type)}</div>`;
    }
}

/* ── Helper: escape HTML ── */
function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ═══════════════════════════════════════════════════════════════
   SELECTION + SETTINGS PANEL
═══════════════════════════════════════════════════════════════ */
function selectBlock(id) {
    selectedId = id;
    document.querySelectorAll('.pb-block').forEach(el => {
        el.classList.toggle('selected', el.dataset.id === id);
    });
    const b = blocks.find(x => x.id === id);
    if (b) renderPanel(b);
}

function deselectBlock() {
    selectedId = null;
    document.querySelectorAll('.pb-block').forEach(el => el.classList.remove('selected'));
    document.getElementById('panelBody').innerHTML = '<div class="pb-panel-empty">Select a block to edit its settings.</div>';
    document.getElementById('panelTitle').textContent = 'Block Settings';
}

function renderPanel(b) {
    document.getElementById('panelTitle').textContent = b.type.charAt(0).toUpperCase() + b.type.slice(1) + ' Settings';
    const body = document.getElementById('panelBody');
    body.innerHTML = buildPanelFields(b);
    // Bind live updates
    body.querySelectorAll('[data-field]').forEach(input => {
        const ev = input.tagName === 'TEXTAREA' || input.type === 'text' || input.type === 'url' || input.type === 'number' ? 'input' : 'change';
        input.addEventListener(ev, () => {
            updateBlock(b.id, input.dataset.field, input.type === 'checkbox' ? input.checked : input.value);
        });
    });
    body.querySelectorAll('.align-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            body.querySelectorAll('.align-btn').forEach(x=>x.classList.remove('active'));
            btn.classList.add('active');
            updateBlock(b.id, 'align', btn.dataset.align);
        });
    });
}

function buildPanelFields(b) {
    const alignBtns = (current) => `
    <div class="align-btns">
        <button type="button" class="align-btn${current==='left'?' active':''}" data-align="left">Left</button>
        <button type="button" class="align-btn${current==='center'?' active':''}" data-align="center">Center</button>
        <button type="button" class="align-btn${(current||'right')==='right'?' active':''}" data-align="right">Right</button>
    </div>`;
    const field = (label, html) => `<div class="pb-panel-field"><label class="pb-panel-label">${label}</label>${html}</div>`;
    const input = (key, val, type='text', extra='') => `<input type="${type}" data-field="${key}" value="${esc(String(val??''))}" ${extra}>`;
    const textarea = (key, val) => `<textarea data-field="${key}" rows="4">${esc(String(val??''))}</textarea>`;
    const select = (key, val, opts) => `<select data-field="${key}">${opts.map(([v,l])=>`<option value="${v}"${v==val?' selected':''}>${l}</option>`).join('')}</select>`;

    switch (b.type) {
        case 'hero':
            return field('Heading', input('heading', b.heading))
                + field('Subheading', textarea('subheading', b.subheading))
                + field('Button 1 Text', input('btn1Text', b.btn1Text))
                + field('Button 1 URL', input('btn1Url', b.btn1Url, 'url'))
                + field('Button 2 Text', input('btn2Text', b.btn2Text))
                + field('Button 2 URL', input('btn2Url', b.btn2Url, 'url'))
                + field('Text Align', alignBtns(b.align))
                + field('Background Color', input('bgColor', b.bgColor, 'color'));
        case 'text':
            return field('Heading', input('heading', b.heading))
                + field('Heading Size', select('headingSize', b.headingSize||'h2', [['h1','H1 Display'],['h2','H2 Section'],['h3','H3 Subsection'],['h4','H4 Label']]))
                + field('Body Text', textarea('body', b.body))
                + field('Align', alignBtns(b.align));
        case 'image':
            return field('Image URL', input('src', b.src, 'url'))
                + field('Alt Text', input('alt', b.alt))
                + field('Caption', input('caption', b.caption));
        case 'gallery':
            return field('Columns', select('cols', b.cols||3, [['2','2 Columns'],['3','3 Columns'],['4','4 Columns']]))
                + field('Gap (px)', input('gap', b.gap||12, 'number'))
                + field('Image URLs (one per line)',
                    `<textarea data-field="images_raw" rows="8" placeholder="https://...">${esc((b.images||[]).join('\n'))}</textarea>`);
        case 'cta':
            return field('Heading', input('heading', b.heading))
                + field('Body', textarea('body', b.body))
                + field('Button Text', input('btnText', b.btnText))
                + field('Button URL', input('btnUrl', b.btnUrl, 'url'))
                + field('Align', alignBtns(b.align))
                + field('Background Color', input('bgColor', b.bgColor, 'color'));
        case 'cards':
            return field('Section Heading', input('heading', b.heading))
                + field('Columns', select('cols', b.cols||3, [['1','1 Column'],['2','2 Columns'],['3','3 Columns'],['4','4 Columns']]))
                + field('Cards (JSON)', `<textarea data-field="items_json" rows="10">${esc(JSON.stringify(b.items||[], null, 2))}</textarea>`);
        case 'skills':
            return field('Section Heading', input('heading', b.heading))
                + field('Skills (JSON)', `<textarea data-field="items_json" rows="10">${esc(JSON.stringify(b.items||[], null, 2))}</textarea>`);
        case 'timeline':
            return field('Section Heading', input('heading', b.heading))
                + field('Items (JSON)', `<textarea data-field="items_json" rows="12">${esc(JSON.stringify(b.items||[], null, 2))}</textarea>`);
        case 'divider':
            return field('Spacing', select('spacing', b.spacing||'md', [['sm','Small'],['md','Medium'],['lg','Large']]));
        case 'two-col':
            return field('Left Content', textarea('left', b.left))
                + field('Right Content', textarea('right', b.right))
                + field('Left Width %', input('leftWidth', b.leftWidth||50, 'number', 'min="20" max="80"'));
        case 'video':
            return field('YouTube / Vimeo URL', input('src', b.src, 'url'))
                + field('Caption', input('caption', b.caption));
        case 'form':
            return field('Form Heading', input('heading', b.heading))
                + field('Button Text', input('btnText', b.btnText));
        default:
            return '<div class="pb-panel-empty">No settings available for this block type.</div>';
    }
}

/* ── Apply field update to block data ── */
function updateBlock(id, field, value) {
    const b = blocks.find(x => x.id === id);
    if (!b) return;

    if (field === 'images_raw') {
        b.images = value.split('\n').map(s=>s.trim()).filter(Boolean);
    } else if (field === 'items_json') {
        try { b.items = JSON.parse(value); } catch(e) { return; }
    } else {
        b[field] = value;
    }
    // Re-render just this block's preview
    const wrap = document.querySelector(`.pb-block[data-id="${id}"]`);
    if (wrap) {
        const toolbar = wrap.querySelector('.pb-block-toolbar').outerHTML;
        wrap.innerHTML = toolbar + buildBlockPreview(b);
        // Re-bind toolbar buttons
        bindToolbarBtn(wrap, id);
    }
    syncJson();
}

function bindToolbarBtn(wrap, id) {
    wrap.querySelectorAll('.pb-block-toolbar-btn').forEach(btn => {
        // Already wired via onclick attributes in renderBlock
    });
}

/* ═══════════════════════════════════════════════════════════════
   BLOCK OPERATIONS
═══════════════════════════════════════════════════════════════ */
function deleteBlock(id) {
    blocks = blocks.filter(b => b.id !== id);
    if (selectedId === id) deselectBlock();
    renderAll();
}

function duplicateBlock(id) {
    const idx = blocks.findIndex(b => b.id === id);
    if (idx < 0) return;
    const clone = JSON.parse(JSON.stringify(blocks[idx]));
    clone.id = uid();
    blocks.splice(idx + 1, 0, clone);
    renderAll();
    selectBlock(clone.id);
}

function moveBlock(id, dir) {
    const idx = blocks.findIndex(b => b.id === id);
    const newIdx = idx + dir;
    if (newIdx < 0 || newIdx >= blocks.length) return;
    [blocks[idx], blocks[newIdx]] = [blocks[newIdx], blocks[idx]];
    renderAll();
    selectBlock(id);
}

function reorderBlocks(fromId, toId) {
    const fromIdx = blocks.findIndex(b => b.id === fromId);
    const toIdx   = blocks.findIndex(b => b.id === toId);
    if (fromIdx < 0 || toIdx < 0) return;
    const [item] = blocks.splice(fromIdx, 1);
    blocks.splice(toIdx, 0, item);
    renderAll();
    selectBlock(fromId);
}

/* ═══════════════════════════════════════════════════════════════
   DRAG FROM SIDEBAR
═══════════════════════════════════════════════════════════════ */
document.querySelectorAll('.pb-block-btn[draggable]').forEach(btn => {
    btn.addEventListener('dragstart', e => {
        dragSource = btn.dataset.type;
        dragType = 'new';
        e.dataTransfer.effectAllowed = 'copy';
    });
    btn.addEventListener('dragend', () => { dragSource = null; dragType = null; });
    // Click to add at bottom
    btn.addEventListener('click', () => {
        const b = defaultBlock(btn.dataset.type);
        blocks.push(b);
        renderAll();
        selectBlock(b.id);
        document.getElementById('pbCanvas').scrollIntoView({behavior:'smooth',block:'end'});
    });
});

/* Drop on canvas root zones */
['rootDropzone','bottomDropzone'].forEach(zId => {
    const zone = document.getElementById(zId);
    if (!zone) return;
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.classList.remove('drag-over');
        if (dragType === 'new' && dragSource) {
            const b = defaultBlock(dragSource);
            if (zId === 'rootDropzone') blocks.unshift(b); else blocks.push(b);
            renderAll();
            selectBlock(b.id);
        }
        dragSource = null; dragType = null;
    });
});

/* ═══════════════════════════════════════════════════════════════
   SAVE
═══════════════════════════════════════════════════════════════ */
function syncJson() {
    document.getElementById('contentJsonInput').value = JSON.stringify(blocks);
}

async function savePage() {
    syncJson();
    const btn = document.getElementById('saveBtn');
    btn.textContent = 'Saving…'; btn.classList.add('saving');
    try {
        const fd = new FormData(document.getElementById('saveForm'));
        const res = await fetch(document.getElementById('saveForm').action, { method:'POST', body:fd });
        if (res.ok || res.redirected) {
            btn.textContent = 'Saved ✓'; btn.classList.remove('saving');
            document.getElementById('savingMsg').classList.add('show');
            setTimeout(() => { btn.textContent='Save'; document.getElementById('savingMsg').classList.remove('show'); }, 2000);
        } else throw new Error('save failed');
    } catch {
        btn.textContent = 'Save'; btn.classList.remove('saving');
        document.getElementById('saveForm').submit(); // fallback
    }
}

// Keyboard shortcut: Ctrl/Cmd+S to save
document.addEventListener('keydown', e => { if ((e.ctrlKey||e.metaKey) && e.key==='s') { e.preventDefault(); savePage(); } });

/* ─── Device preview ─── */
function setDevice(device, btn) {
    document.querySelectorAll('.pb-device-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    const canvas = document.getElementById('pbCanvas');
    canvas.className = 'pb-canvas' + (device==='tablet'?' preview-tablet':device==='mobile'?' preview-mobile':'');
}

/* ─── Block search ─── */
function filterBlocks(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.pb-block-btn').forEach(btn => {
        btn.style.display = btn.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

/* ── Initial render ── */
renderAll();
</script>
</body>
</html>
