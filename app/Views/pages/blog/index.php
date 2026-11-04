<?php $pageScript = 'blog'; ?>

<!-- 3D background -->
<canvas id="canvas-blog" class="canvas-bg" aria-hidden="true"></canvas>

<!-- ── Hero ──────────────────────────────────────────────── -->
<section class="pt-40 pb-20 border-b border-white/5">
  <div class="container">
    <div class="max-w-2xl">
      <div class="hero-eyebrow mb-6"><span class="dot"></span>Writing & Research</div>
      <h1 class="display-2 mb-6">
        Ideas worth<br>
        <span class="gradient-text">thinking about.</span>
      </h1>
      <p class="body-lg" style="color:rgba(255,255,255,0.5)">
        Practical insights on AI engineering, software architecture, and building
        products that last. No fluff — just things I've actually learned by shipping.
      </p>
    </div>
  </div>
</section>

<!-- ── Posts ─────────────────────────────────────────────── -->
<section class="section-py">
  <div class="container">
    <?php if (!empty($posts)): ?>

    <!-- Featured post -->
    <?php $featured = $posts[0]; ?>
    <article class="card p-0 overflow-hidden mb-8 reveal">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
        <div class="aspect-video md:aspect-auto min-h-56 bg-gradient-to-br from-gray-900 to-gray-800">
          <?php if (!empty($featured['featured_image'])): ?>
          <img src="<?= url($featured['featured_image']) ?>" alt="<?= e($featured['title']) ?>"
               class="w-full h-full object-cover" loading="lazy">
          <?php endif; ?>
        </div>
        <div class="p-8 flex flex-col justify-center">
          <div class="flex items-center gap-3 mb-4">
            <span class="tag">Latest</span>
            <?php if ($featured['published_at']): ?>
            <span class="text-xs text-gray-600"><?= date('M j, Y', strtotime($featured['published_at'])) ?></span>
            <?php endif; ?>
          </div>
          <h2 class="heading-2 mb-3">
            <a href="<?= url('/blog/' . $featured['slug']) ?>" class="hover:text-gray-300 transition">
              <?= e($featured['title']) ?>
            </a>
          </h2>
          <p class="text-gray-500 text-sm leading-relaxed mb-6 line-clamp-3"><?= e($featured['excerpt'] ?? '') ?></p>
          <a href="<?= url('/blog/' . $featured['slug']) ?>" class="btn btn-primary text-sm self-start">
            Read Article
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M7 17L17 7M17 7H7M17 7v10"/>
            </svg>
          </a>
        </div>
      </div>
    </article>

    <!-- Rest of posts -->
    <?php if (count($posts) > 1): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach (array_slice($posts, 1) as $i => $post): ?>
      <article class="card p-6 flex flex-col reveal" style="animation-delay:<?= ($i % 3) * 80 ?>ms">
        <?php if (!empty($post['featured_image'])): ?>
        <div class="aspect-video rounded-xl overflow-hidden bg-gray-900 mb-5">
          <img src="<?= url($post['featured_image']) ?>" alt="<?= e($post['title']) ?>"
               class="w-full h-full object-cover" loading="lazy">
        </div>
        <?php endif; ?>

        <?php if ($post['published_at']): ?>
        <p class="text-xs text-gray-600 mb-2"><?= date('M j, Y', strtotime($post['published_at'])) ?></p>
        <?php endif; ?>

        <h3 class="font-semibold mb-2 flex-1">
          <a href="<?= url('/blog/' . $post['slug']) ?>" class="hover:text-gray-300 transition line-clamp-2">
            <?= e($post['title']) ?>
          </a>
        </h3>
        <p class="text-sm text-gray-500 line-clamp-3 mb-4"><?= e($post['excerpt'] ?? '') ?></p>
        <a href="<?= url('/blog/' . $post['slug']) ?>" class="text-xs text-gray-400 hover:text-white transition">
          Read more →
        </a>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($pagination['last_page'] > 1): ?>
    <div class="flex items-center justify-center gap-2 mt-12">
      <?php if ($pagination['current_page'] > 1): ?>
      <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="btn btn-outline text-sm px-4 py-2">← Prev</a>
      <?php endif; ?>
      <?php for ($p = max(1, $pagination['current_page'] - 2); $p <= min($pagination['last_page'], $pagination['current_page'] + 2); $p++): ?>
      <a href="?page=<?= $p ?>"
         class="w-9 h-9 flex items-center justify-center rounded-xl text-sm font-medium transition
                <?= $p === $pagination['current_page'] ? 'bg-white text-black' : 'bg-white/5 text-gray-400 hover:bg-white/10 hover:text-white' ?>">
        <?= $p ?>
      </a>
      <?php endfor; ?>
      <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
      <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="btn btn-outline text-sm px-4 py-2">Next →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Empty state -->
    <div class="text-center py-24">
      <div class="text-5xl mb-6">✍️</div>
      <h2 class="heading-2 mb-4">No posts yet.</h2>
      <p class="body-lg mb-8 max-w-md mx-auto">
        I'm working on some articles. Check back soon — or subscribe to be notified
        when the first post goes live.
      </p>
      <a href="<?= url('/contact') ?>" class="btn btn-outline">
        Get notified
      </a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── Newsletter ─────────────────────────────────────────── -->
<section class="section-py border-t border-white/5">
  <div class="container max-w-xl mx-auto text-center">
    <p class="section-label justify-center mb-4">Stay sharp</p>
    <h2 class="heading-1 mb-5">Get articles in your inbox.</h2>
    <p class="body-lg mb-8">
      No spam. Just practical AI engineering insights, shipped
      whenever I have something worth saying.
    </p>
    <a href="<?= url('/contact') ?>" class="btn btn-primary">
      Subscribe via Email
    </a>
  </div>
</section>
