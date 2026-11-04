<?php
$pageScript = 'portfolio';
?>

<!-- ── Hero ──────────────────────────────────────────────── -->
<section class="hero" style="min-height:70svh;">
  <div class="canvas-container">
    <canvas id="canvas-portfolio"></canvas>
  </div>
  <div class="absolute inset-x-0 bottom-0 h-64 bg-gradient-to-t from-black to-transparent z-[1]"></div>

  <div class="container hero-content relative z-10">
    <div class="max-w-3xl">
      <div class="hero-eyebrow"><span class="dot"></span>Selected Work</div>
      <h1 class="display-2 mb-6">
        Products that<br>
        <span class="gradient-text">Ship & Scale.</span>
      </h1>
      <p class="body-lg max-w-xl" style="color:rgba(255,255,255,0.5)">
        A curated selection of production-ready products, open-source contributions,
        and client work built with obsessive attention to craft.
      </p>
    </div>
  </div>
</section>

<!-- ── Featured work ─────────────────────────────────────── -->
<section class="section-py">
  <div class="container">
    <div class="section-header">
      <p class="section-label">Portfolio</p>
      <h2 class="heading-1">Featured Work</h2>
    </div>

    <?php if (!empty($projects)): ?>
    <div class="space-y-6">
      <?php foreach ($projects as $i => $proj): ?>
      <article class="card p-0 overflow-hidden reveal" style="animation-delay:<?= $i * 80 ?>ms">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
          <!-- Image -->
          <div class="aspect-video md:aspect-auto min-h-64 bg-gradient-to-br from-gray-900 to-gray-800 flex items-center justify-center
                      <?= $i % 2 === 1 ? 'md:order-last' : '' ?>">
            <?php if (!empty($proj['thumbnail'])): ?>
            <img src="<?= url($proj['thumbnail']) ?>" alt="<?= e($proj['title']) ?>"
                 class="w-full h-full object-cover" loading="lazy">
            <?php else: ?>
            <span class="text-5xl opacity-30"><?= $i === 0 ? '🤖' : ($i === 1 ? '🧠' : '⚡') ?></span>
            <?php endif; ?>
          </div>
          <!-- Content -->
          <div class="p-8 flex flex-col justify-center">
            <span class="tag mb-4"><?= ucfirst(e($proj['project_type'] ?? 'project')) ?></span>
            <h3 class="heading-2 mb-3"><?= e($proj['title']) ?></h3>
            <p class="text-gray-500 mb-6 leading-relaxed"><?= e($proj['description']) ?></p>
            <?php if (!empty($proj['tech_stack'])): ?>
            <div class="flex flex-wrap gap-1.5 mb-6">
              <?php foreach (explode(',', $proj['tech_stack']) as $t): ?>
              <span class="tag"><?= e(trim($t)) ?></span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="flex gap-3">
              <?php if (!empty($proj['demo_url'])): ?>
              <a href="<?= e($proj['demo_url']) ?>" target="_blank" rel="noopener" class="btn btn-primary text-sm">
                Live Demo
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M7 17L17 7M17 7H7M17 7v10"/>
                </svg>
              </a>
              <?php endif; ?>
              <?php if (!empty($proj['github_url'])): ?>
              <a href="<?= e($proj['github_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline text-sm">
                GitHub
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <?php else: ?>
    <!-- Static fallback -->
    <?php
    $portfolioItems = [
      ['🤖', 'AI-Powered CRM Intelligence', 'Integrated AI layer for a SaaS CRM — automated lead scoring, email drafting, and meeting summarization. Reduced sales team admin time by 4 hours/week per rep.', 'OpenAI, Python, React, PostgreSQL', '#', '#'],
      ['📊', 'Real-Time Analytics Platform', 'Full-stack analytics dashboard processing 2M+ events/day. Custom query builder, AI-powered insights, and white-label white label export capabilities.', 'Python, Kafka, ClickHouse, Next.js, D3.js', '#', '#'],
      ['🔐', 'Zero-Trust API Gateway', 'Security-first API gateway with ML-based anomaly detection, rate limiting, and automatic threat classification. Reduced security incidents by 73%.', 'Go, Redis, PostgreSQL, Docker, ML Anomaly Detection', '#', null],
      ['🧬', 'Document Intelligence API', 'Vision + NLP pipeline for automated document processing — extracts structured data from invoices, contracts, and forms with 96% accuracy.', 'Python, PyTorch, Hugging Face, FastAPI, AWS', null, '#'],
    ];
    ?>
    <div class="space-y-6">
      <?php foreach ($portfolioItems as $i => [$icon, $title, $desc, $stack, $demo, $github]): ?>
      <article class="card p-0 overflow-hidden reveal" style="animation-delay:<?= $i * 80 ?>ms">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
          <div class="aspect-video md:aspect-auto min-h-64 bg-gradient-to-br from-gray-900 to-gray-800 flex items-center justify-center
                      <?= $i % 2 === 1 ? 'md:order-last' : '' ?>">
            <span class="text-6xl opacity-20"><?= $icon ?></span>
          </div>
          <div class="p-8 flex flex-col justify-center">
            <span class="tag mb-4">Case Study</span>
            <h3 class="heading-2 mb-3"><?= $title ?></h3>
            <p class="text-gray-500 mb-6 leading-relaxed text-sm"><?= $desc ?></p>
            <div class="flex flex-wrap gap-1.5 mb-6">
              <?php foreach (explode(', ', $stack) as $t): ?>
              <span class="tag"><?= $t ?></span>
              <?php endforeach; ?>
            </div>
            <div class="flex gap-3">
              <?php if ($demo): ?>
              <a href="<?= $demo ?>" class="btn btn-primary text-sm">View Case Study</a>
              <?php endif; ?>
              <?php if ($github): ?>
              <a href="<?= $github ?>" class="btn btn-outline text-sm">GitHub</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── Open source ───────────────────────────────────────── -->
<section class="section-py border-t border-white/5">
  <div class="container">
    <div class="section-header">
      <p class="section-label">Open Source</p>
      <h2 class="heading-1">Public Contributions</h2>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php
      $oss = [
        ['🔧', 'langchain-tools-php', 'PHP client and tool wrappers for LangChain-compatible agents.', 'PHP · 340 stars'],
        ['📦', 'rag-pipeline', 'Modular RAG pipeline with support for multiple vector stores and LLM providers.', 'Python · 820 stars'],
        ['⚡', 'fastapi-ai-template', 'Production-ready FastAPI template with auth, caching, and LLM integration.', 'Python · 560 stars'],
      ];
      foreach ($oss as $i => [$icon, $repo, $desc, $meta]):
      ?>
      <div class="card p-5 reveal" style="animation-delay:<?= $i * 80 ?>ms">
        <div class="flex items-start justify-between mb-3">
          <span class="text-xl"><?= $icon ?></span>
          <svg class="w-4 h-4 text-gray-600" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
          </svg>
        </div>
        <p class="font-mono text-sm font-medium text-gray-200 mb-2"><?= $repo ?></p>
        <p class="text-xs text-gray-500 leading-relaxed mb-3"><?= $desc ?></p>
        <p class="text-xs text-gray-600"><?= $meta ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Hire CTA ───────────────────────────────────────────── -->
<section class="section-py border-t border-white/5">
  <div class="container text-center max-w-2xl mx-auto">
    <p class="section-label justify-center mb-4">Available for hire</p>
    <h2 class="heading-1 mb-5">Have a project in mind?</h2>
    <p class="body-lg mb-8">
      I take on a small number of projects each quarter to ensure every
      client gets my full focus and commitment.
    </p>
    <a href="<?= url('/contact') ?>" class="btn btn-primary">
      Start a Project
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M7 17L17 7M17 7H7M17 7v10"/>
      </svg>
    </a>
  </div>
</section>
