<?php $pageScript = null; ?>
<div class="pt-32 pb-20">
  <div class="container max-w-4xl">
    <div class="mb-12">
      <h1 class="display-3 mb-4">Search Results</h1>
      <form method="GET" action="/search" class="flex gap-3">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search everything…"
               class="form-input flex-1 text-lg" autofocus>
        <button type="submit" class="btn-primary px-8">Search</button>
      </form>
    </div>

    <?php if ($q): ?>
    <p class="text-slate-400 mb-8"><?= count($results) ?> result<?= count($results)!==1?'s':'' ?> for "<strong class="text-white"><?= htmlspecialchars($q) ?></strong>"</p>
    <?php endif; ?>

    <?php if ($results): ?>
    <div class="space-y-4">
      <?php foreach ($results as $r): ?>
      <a href="<?= htmlspecialchars($r['url']) ?>" class="block admin-card p-5 hover:border-crimson/40 transition group">
        <div class="flex items-start gap-4">
          <?php if ($r['image']): ?>
          <img src="<?= htmlspecialchars($r['image']) ?>" alt="" class="w-16 h-14 rounded-lg object-cover flex-shrink-0">
          <?php endif; ?>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <span class="badge badge-blue text-xs"><?= htmlspecialchars($r['section']) ?></span>
            </div>
            <h3 class="font-semibold text-white group-hover:text-crimson transition"><?= htmlspecialchars($r['title']) ?></h3>
            <?php if ($r['snippet']): ?>
            <p class="text-slate-400 text-sm mt-1 line-clamp-2"><?= htmlspecialchars(strip_tags(substr($r['snippet'],0,200))) ?></p>
            <?php endif; ?>
            <div class="text-xs text-crimson mt-2"><?= htmlspecialchars($r['url']) ?></div>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php elseif ($q): ?>
    <div class="text-center py-20">
      <div class="text-5xl mb-4">🔍</div>
      <p class="text-xl text-white mb-2">No results found</p>
      <p class="text-slate-400">Try different keywords or browse the <a href="/blog" class="text-crimson hover:underline">blog</a>.</p>
    </div>
    <?php endif; ?>
  </div>
</div>
