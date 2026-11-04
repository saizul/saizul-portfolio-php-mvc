<?php
$pageScript = 'home';
?>

<!-- ── Hero ──────────────────────────────────────────────── -->
<section class="hero" style="min-height: 100svh;">
  <div class="canvas-container" id="canvas-home-wrap">
    <canvas id="canvas-home"></canvas>
  </div>

  <!-- Gradient overlays -->
  <div class="absolute inset-x-0 bottom-0 h-64 bg-gradient-to-t from-black to-transparent z-[1]"></div>
  <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-black/50 to-transparent z-[1]"></div>

  <div class="container hero-content relative z-10">
    <div class="max-w-4xl">
      <div class="hero-eyebrow">
        <span class="dot"></span>
        Available for projects &amp; collaborations
      </div>

      <h1 class="display-1 mb-6">
        <span class="block text-white">I Build</span>
        <span class="block gradient-text">Intelligent</span>
        <span class="block text-white">Experiences.</span>
      </h1>

      <p class="body-lg max-w-xl mb-10" style="color: rgba(255,255,255,0.5);">
        AI developer, full-stack engineer, and creative technologist. I design and build
        products that sit at the intersection of artificial intelligence and exceptional
        user experience.
      </p>

      <div class="flex flex-wrap items-center gap-4">
        <a href="<?= url('/ai-projects') ?>" class="btn btn-primary">
          View My Work
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M7 17L17 7M17 7H7M17 7v10"/>
          </svg>
        </a>
        <a href="<?= url('/about') ?>" class="btn btn-outline">
          About Me
        </a>
      </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-0 right-0 hidden lg:flex flex-col items-center gap-2 text-gray-600 pb-2">
      <div style="width:1px;height:60px;background:linear-gradient(to bottom,transparent,rgba(255,255,255,0.15))"></div>
      <span class="text-xs tracking-widest" style="writing-mode:vertical-rl">SCROLL</span>
    </div>
  </div>
</section>

<!-- ── Stats bar ─────────────────────────────────────────── -->
<section class="border-y border-white/5 py-10">
  <div class="container">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
      <?php
      $stats = [
        ['5+',    'Years Experience'],
        ['30+',   'Projects Delivered'],
        ['15+',   'AI Models Built'],
        ['100%',  'Client Satisfaction'],
      ];
      foreach ($stats as [$num, $label]):
      ?>
      <div class="reveal">
        <p class="text-3xl font-bold tracking-tight mb-1"><?= $num ?></p>
        <p class="text-xs text-gray-500 uppercase tracking-wider"><?= $label ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Featured Projects ─────────────────────────────────── -->
<section class="section-py">
  <div class="container">
    <div class="section-header flex items-end justify-between gap-4 flex-wrap">
      <div>
        <p class="section-label">Selected Work</p>
        <h2 class="heading-1">Featured Projects</h2>
      </div>
      <a href="<?= url('/ai-projects') ?>" class="btn btn-ghost">
        View all projects <span class="arrow">→</span>
      </a>
    </div>

    <?php if (!empty($featured_projects)): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 stagger">
      <?php foreach ($featured_projects as $i => $project): ?>
      <article class="card p-6 flex flex-col reveal" style="animation-delay:<?= $i * 80 ?>ms">
        <div class="flex items-start justify-between mb-5">
          <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center border border-white/8 text-lg">
            <?= $i === 0 ? '🤖' : ($i === 1 ? '🧠' : '⚡') ?>
          </div>
          <div class="flex gap-2">
            <?php if (!empty($project['github_url'])): ?>
            <a href="<?= e($project['github_url']) ?>" target="_blank" rel="noopener"
               class="text-gray-600 hover:text-white transition p-1">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
              </svg>
            </a>
            <?php endif; ?>
            <?php if (!empty($project['demo_url'])): ?>
            <a href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener"
               class="text-gray-600 hover:text-white transition p-1">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
            </a>
            <?php endif; ?>
          </div>
        </div>

        <h3 class="font-semibold mb-2"><?= e($project['title']) ?></h3>
        <p class="text-sm text-gray-500 line-clamp-2 mb-5 flex-1"><?= e($project['description']) ?></p>

        <?php if (!empty($project['tech_stack'])): ?>
        <div class="flex flex-wrap gap-1.5 mt-auto">
          <?php foreach (array_slice(explode(',', $project['tech_stack']), 0, 4) as $tech): ?>
          <span class="tag"><?= e(trim($tech)) ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- Static fallback projects while DB is empty -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      $fallback = [
        ['🤖', 'AI Chat Assistant', 'A context-aware conversational AI built on GPT-4 with memory, RAG pipeline, and multi-modal input support.', 'Python, FastAPI, LangChain, React'],
        ['🧠', 'Predictive Analytics Engine', 'Real-time ML pipeline for business intelligence — processes 1M+ events/day with 94% prediction accuracy.', 'Python, TensorFlow, Kafka, PostgreSQL'],
        ['⚡', 'Autonomous Agent Framework', 'Multi-agent orchestration system for complex task automation, built with LangGraph and custom tool chains.', 'Python, LangGraph, Redis, Docker'],
      ];
      foreach ($fallback as [$icon, $title, $desc, $stack]):
      ?>
      <article class="card p-6 flex flex-col reveal">
        <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center border border-white/8 text-lg mb-5"><?= $icon ?></div>
        <h3 class="font-semibold mb-2"><?= $title ?></h3>
        <p class="text-sm text-gray-500 line-clamp-3 mb-5 flex-1"><?= $desc ?></p>
        <div class="flex flex-wrap gap-1.5">
          <?php foreach (explode(', ', $stack) as $t): ?>
          <span class="tag"><?= $t ?></span>
          <?php endforeach; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── About strip ───────────────────────────────────────── -->
<section class="section-py border-t border-white/5">
  <div class="container">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
      <div class="reveal-left">
        <p class="section-label">Who I Am</p>
        <h2 class="heading-1 mb-6">Engineering intelligence,<br>one model at a time.</h2>
        <p class="body-lg mb-6">
          I'm Saizul Amin — an AI developer and full-stack engineer passionate about
          building systems that learn, adapt, and create real value. With deep expertise
          in machine learning, LLMs, and modern web technologies, I bridge the gap between
          cutting-edge AI research and production-ready software.
        </p>
        <p class="body-lg mb-8">
          My work spans conversational AI, predictive systems, computer vision pipelines,
          and high-performance web applications — all crafted with an obsession for clean
          architecture and exceptional user experience.
        </p>
        <a href="<?= url('/about') ?>" class="btn btn-outline">
          Read my story
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M7 17L17 7M17 7H7M17 7v10"/>
          </svg>
        </a>
      </div>

      <div class="grid grid-cols-2 gap-4 reveal">
        <?php
        $values = [
          ['🎯', 'Precision', 'Every line of code serves a purpose. I build with intent.'],
          ['🔬', 'Research-Led', 'Grounded in the latest AI/ML research, applied pragmatically.'],
          ['⚡', 'Performance', 'Fast, scalable systems that handle real-world load.'],
          ['🤝', 'Collaboration', 'Clear communication and transparent development process.'],
        ];
        foreach ($values as [$icon, $title, $desc]):
        ?>
        <div class="card p-5">
          <div class="text-2xl mb-3"><?= $icon ?></div>
          <p class="font-semibold text-sm mb-1.5"><?= $title ?></p>
          <p class="text-xs text-gray-500 leading-relaxed"><?= $desc ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── Skills snapshot ───────────────────────────────────── -->
<?php if (!empty($skills_grouped)): ?>
<section class="section-py border-t border-white/5">
  <div class="container">
    <div class="section-header">
      <p class="section-label">Expertise</p>
      <h2 class="heading-1">Core Skills</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php foreach (array_slice($skills_grouped, 0, 3, true) as $cat => $skills): ?>
      <div class="reveal">
        <h3 class="text-sm font-semibold text-gray-400 mb-4 uppercase tracking-wider"><?= e($cat) ?></h3>
        <div class="space-y-4">
          <?php foreach (array_slice($skills, 0, 5) as $skill): ?>
          <div>
            <div class="flex justify-between text-sm mb-1.5">
              <span class="text-gray-300"><?= e($skill['name']) ?></span>
              <span class="text-gray-600"><?= (int)$skill['proficiency'] ?>%</span>
            </div>
            <div class="skill-bar">
              <div class="skill-bar-fill" data-pct="<?= (int)$skill['proficiency'] ?>"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-10 text-center">
      <a href="<?= url('/skills') ?>" class="btn btn-ghost">
        See all skills <span class="arrow">→</span>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Latest Posts ──────────────────────────────────────── -->
<?php if (!empty($latest_posts)): ?>
<section class="section-py border-t border-white/5">
  <div class="container">
    <div class="section-header flex items-end justify-between gap-4 flex-wrap">
      <div>
        <p class="section-label">Insights</p>
        <h2 class="heading-1">Latest Writing</h2>
      </div>
      <a href="<?= url('/blog') ?>" class="btn btn-ghost">
        View all posts <span class="arrow">→</span>
      </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach ($latest_posts as $i => $post): ?>
      <article class="card p-6 reveal" style="animation-delay:<?= $i * 80 ?>ms">
        <?php if ($post['published_at']): ?>
        <p class="text-xs text-gray-600 mb-3"><?= date('M j, Y', strtotime($post['published_at'])) ?></p>
        <?php endif; ?>
        <h3 class="font-semibold mb-2 line-clamp-2">
          <a href="<?= url('/blog/' . $post['slug']) ?>" class="hover:text-gray-300 transition">
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
  </div>
</section>
<?php endif; ?>

<!-- ── CTA ───────────────────────────────────────────────── -->
<section class="section-py">
  <div class="container">
    <div class="relative overflow-hidden rounded-3xl border border-white/8 bg-gradient-to-br from-white/5 to-white/2 p-12 md:p-16 text-center glow-emerald">
      <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent pointer-events-none"></div>
      <div class="relative z-10">
        <p class="section-label justify-center mb-4">Ready to build?</p>
        <h2 class="display-2 mb-6">Let's create<br>something exceptional.</h2>
        <p class="body-lg max-w-xl mx-auto mb-10">
          Whether you're building an AI-powered product, need a technical co-founder,
          or want to transform your data into intelligence — I'd love to hear from you.
        </p>
        <div class="flex flex-wrap items-center justify-center gap-4">
          <a href="<?= url('/contact') ?>" class="btn btn-primary text-sm">
            Start a Conversation
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M7 17L17 7M17 7H7M17 7v10"/>
            </svg>
          </a>
          <a href="mailto:saizulamin@gmail.com" class="btn btn-ghost text-gray-400">
            saizulamin@gmail.com
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
