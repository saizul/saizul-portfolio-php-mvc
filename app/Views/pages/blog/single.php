<?php
$readTime = max(1, (int) round(str_word_count(strip_tags($post['body'] ?? '')) / 200));
?>

<!-- ── Article header ────────────────────────────────────── -->
<div class="pt-40 pb-16 border-b border-white/5">
  <div class="container max-w-3xl">
    <a href="<?= url('/blog') ?>"
       class="inline-flex items-center gap-2 text-xs text-gray-500 hover:text-white transition mb-10">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 12H5M12 5l-7 7 7 7"/>
      </svg>
      Back to Blog
    </a>

    <div class="reveal">
      <?php if ($post['published_at']): ?>
      <div class="flex flex-wrap items-center gap-4 mb-6 text-sm text-gray-500">
        <time datetime="<?= $post['published_at'] ?>"><?= date('F j, Y', strtotime($post['published_at'])) ?></time>
        <span>·</span>
        <span><?= $readTime ?> min read</span>
      </div>
      <?php endif; ?>

      <h1 class="display-2 mb-8"><?= e($post['title']) ?></h1>

      <?php if ($post['excerpt']): ?>
      <p class="body-lg text-xl mb-8" style="color:rgba(255,255,255,0.6)"><?= e($post['excerpt']) ?></p>
      <?php endif; ?>

      <!-- Author -->
      <div class="flex items-center gap-4 pt-6 border-t border-white/6">
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center font-bold text-sm">
          SA
        </div>
        <div>
          <p class="text-sm font-medium">Saizul Amin</p>
          <p class="text-xs text-gray-500">AI Developer & Engineer</p>
        </div>
        <div class="ml-auto flex gap-3">
          <a href="https://twitter.com/intent/tweet?text=<?= urlencode($post['title']) ?>&url=<?= urlencode(url('/blog/' . $post['slug'])) ?>"
             target="_blank" rel="noopener"
             class="text-gray-600 hover:text-white transition p-1" title="Share on Twitter">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
              <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
            </svg>
          </a>
          <button onclick="copyLink()" class="text-gray-600 hover:text-white transition p-1" title="Copy link">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Featured image ────────────────────────────────────── -->
<?php if (!empty($post['featured_image'])): ?>
<div class="container max-w-4xl py-10">
  <div class="aspect-video rounded-2xl overflow-hidden bg-gray-900">
    <img src="<?= url($post['featured_image']) ?>" alt="<?= e($post['title']) ?>"
         class="w-full h-full object-cover" loading="eager">
  </div>
</div>
<?php endif; ?>

<!-- ── Article body ──────────────────────────────────────── -->
<article class="container max-w-3xl py-12">
  <div class="post-body">
    <?= $post['body'] ?>
  </div>
</article>

<!-- ── Tags / Share ──────────────────────────────────────── -->
<div class="container max-w-3xl pb-16">
  <hr class="border-white/6 mb-8">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-2 text-sm text-gray-500">
      <span>Share:</span>
      <a href="https://twitter.com/intent/tweet?text=<?= urlencode($post['title']) ?>&url=<?= urlencode(url('/blog/' . $post['slug'])) ?>"
         target="_blank" rel="noopener"
         class="hover:text-white transition font-medium">Twitter/X</a>
      <span>·</span>
      <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(url('/blog/' . $post['slug'])) ?>"
         target="_blank" rel="noopener"
         class="hover:text-white transition font-medium">LinkedIn</a>
    </div>
    <a href="<?= url('/blog') ?>" class="btn btn-ghost text-sm">
      ← Back to Blog
    </a>
  </div>
</div>

<!-- ── Related posts ─────────────────────────────────────── -->
<?php if (!empty($related)): ?>
<div class="border-t border-white/5 section-py">
  <div class="container">
    <p class="section-label mb-8">Keep reading</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach ($related as $i => $rel): ?>
      <article class="card p-5 reveal" style="animation-delay:<?= $i * 80 ?>ms">
        <?php if ($rel['published_at']): ?>
        <p class="text-xs text-gray-600 mb-2"><?= date('M j, Y', strtotime($rel['published_at'])) ?></p>
        <?php endif; ?>
        <h3 class="font-semibold text-sm mb-2 line-clamp-2">
          <a href="<?= url('/blog/' . $rel['slug']) ?>" class="hover:text-gray-300 transition">
            <?= e($rel['title']) ?>
          </a>
        </h3>
        <p class="text-xs text-gray-500 line-clamp-2 mb-4"><?= e($rel['excerpt'] ?? '') ?></p>
        <a href="<?= url('/blog/' . $rel['slug']) ?>" class="text-xs text-gray-400 hover:text-white transition">
          Read →
        </a>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function copyLink() {
  navigator.clipboard.writeText(window.location.href).then(() => {
    const t = document.createElement('div');
    t.textContent = 'Link copied!';
    t.className = 'fixed bottom-6 right-6 bg-gray-800 text-white text-xs px-4 py-2 rounded-lg shadow-lg z-50';
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2000);
  });
}
</script>
