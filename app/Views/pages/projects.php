<?php
$pageScript = 'projects';
// Group projects by category
$byType = [];
foreach ($projects as $p) { $byType[$p['project_type'] ?? 'ai'][] = $p; }
?>

<!-- ── Hero ──────────────────────────────────────────────── -->
<section class="hero" style="min-height:75svh;">
  <div class="canvas-container">
    <canvas id="canvas-projects"></canvas>
  </div>
  <div class="absolute inset-x-0 bottom-0 h-64 bg-gradient-to-t from-black to-transparent z-[1]"></div>

  <div class="container hero-content relative z-10">
    <div class="max-w-3xl">
      <div class="hero-eyebrow">
        <span class="dot"></span>
        <?= count($projects) ?: '15+' ?> Projects Built
      </div>
      <h1 class="display-2 mb-6">
        AI Projects &<br>
        <span class="gradient-text">Research Work.</span>
      </h1>
      <p class="body-lg max-w-xl" style="color:rgba(255,255,255,0.5)">
        From large language model applications to autonomous agent systems — real
        projects solving real problems with production-grade AI.
      </p>
    </div>
  </div>
</section>

<!-- ── Projects grid ─────────────────────────────────────── -->
<section class="section-py">
  <div class="container">

    <?php if (!empty($projects)): ?>
    <!-- Filter tabs -->
    <div class="flex flex-wrap gap-2 mb-10" id="filter-tabs">
      <button class="filter-tab active tag px-4 py-2 cursor-pointer transition hover:border-white/30" data-filter="all">
        All Projects
      </button>
      <?php foreach (array_unique(array_column($projects, 'project_type')) as $type): ?>
      <button class="filter-tab tag px-4 py-2 cursor-pointer transition hover:border-white/30" data-filter="<?= e($type) ?>">
        <?= ucfirst(e($type)) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="projects-grid">
      <?php foreach ($projects as $i => $project): ?>
      <article class="project-card card p-6 flex flex-col reveal"
               data-type="<?= e($project['project_type'] ?? 'ai') ?>"
               style="animation-delay:<?= ($i % 6) * 80 ?>ms">
        <div class="flex items-start justify-between mb-5">
          <span class="tag"><?= ucfirst(e($project['project_type'] ?? 'ai')) ?></span>
          <div class="flex gap-2">
            <?php if (!empty($project['github_url'])): ?>
            <a href="<?= e($project['github_url']) ?>" target="_blank" rel="noopener"
               title="GitHub" class="text-gray-600 hover:text-white transition p-1">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
              </svg>
            </a>
            <?php endif; ?>
            <?php if (!empty($project['demo_url'])): ?>
            <a href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener"
               title="Live demo" class="text-gray-600 hover:text-white transition p-1">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
            </a>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($project['thumbnail'])): ?>
        <div class="aspect-video rounded-xl overflow-hidden bg-gray-900 mb-5">
          <img src="<?= url($project['thumbnail']) ?>" alt="<?= e($project['title']) ?>"
               class="w-full h-full object-cover" loading="lazy">
        </div>
        <?php endif; ?>

        <h3 class="font-semibold mb-2"><?= e($project['title']) ?></h3>
        <p class="text-sm text-gray-500 line-clamp-3 mb-5 flex-1"><?= e($project['description']) ?></p>

        <?php if (!empty($project['tech_stack'])): ?>
        <div class="flex flex-wrap gap-1.5">
          <?php foreach (array_slice(explode(',', $project['tech_stack']), 0, 5) as $tech): ?>
          <span class="tag"><?= e(trim($tech)) ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>

    <?php else: ?>
    <!-- Static showcase while DB empty -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      $showcase = [
        ['ai', '🤖', 'Conversational AI Platform', 'Production-grade chatbot framework with persistent memory, RAG pipeline, tool calling, and multi-modal support. Handles 50K+ conversations/day.', 'Python, FastAPI, LangChain, OpenAI, PostgreSQL, Redis'],
        ['ai', '🧠', 'Predictive Analytics Engine', 'Real-time ML pipeline for e-commerce demand forecasting. Reduced inventory costs by 28% with 94% prediction accuracy across 1M+ SKUs.', 'Python, TensorFlow, Kafka, Airflow, BigQuery'],
        ['ai', '👁️', 'Computer Vision Pipeline', 'End-to-end document intelligence system using Vision Transformers for layout analysis, OCR, and structured data extraction at scale.', 'Python, PyTorch, Hugging Face, FastAPI, Docker'],
        ['ai', '⚡', 'Autonomous Agent Framework', 'Multi-agent orchestration for complex task automation. Agents coordinate research, execution, and verification with minimal human intervention.', 'Python, LangGraph, OpenAI, Redis, Celery'],
        ['ai', '📊', 'NLP Analytics Dashboard', 'Real-time sentiment analysis and topic modelling across social media streams. Processes 500K+ posts/hour with 91% classification accuracy.', 'Python, spaCy, BERTopic, React, Elasticsearch'],
        ['ai', '🔐', 'AI Security Monitor', 'Anomaly detection system for cybersecurity using unsupervised learning. Reduced false positives by 67% vs rule-based systems.', 'Python, scikit-learn, Kafka, Grafana, PostgreSQL'],
      ];
      foreach ($showcase as $i => [$type, $icon, $title, $desc, $stack]):
      ?>
      <article class="card p-6 flex flex-col reveal" style="animation-delay:<?= ($i % 3) * 80 ?>ms">
        <div class="flex items-center justify-between mb-5">
          <span class="tag">AI</span>
          <span class="text-2xl"><?= $icon ?></span>
        </div>
        <h3 class="font-semibold mb-2"><?= $title ?></h3>
        <p class="text-sm text-gray-500 line-clamp-3 mb-5 flex-1"><?= $desc ?></p>
        <div class="flex flex-wrap gap-1.5">
          <?php foreach (array_slice(explode(', ', $stack), 0, 4) as $t): ?>
          <span class="tag"><?= $t ?></span>
          <?php endforeach; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── Tech stack ────────────────────────────────────────── -->
<section class="section-py border-t border-white/5">
  <div class="container">
    <div class="section-header text-center">
      <p class="section-label justify-center">Toolbox</p>
      <h2 class="heading-1">Technologies I Work With</h2>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <?php
      $techs = [
        'Python','PyTorch','TensorFlow','LangChain','OpenAI',
        'FastAPI','React','TypeScript','PHP','PostgreSQL','Redis','Docker',
      ];
      foreach ($techs as $i => $tech):
      ?>
      <div class="card p-4 text-center reveal" style="animation-delay:<?= ($i % 6) * 50 ?>ms">
        <p class="text-sm font-medium text-gray-300"><?= $tech ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
// Filter tabs
document.querySelectorAll('.filter-tab').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filter = btn.dataset.filter;
    document.querySelectorAll('.project-card').forEach(card => {
      const show = filter === 'all' || card.dataset.type === filter;
      card.style.display = show ? '' : 'none';
    });
  });
});
</script>

<style>
.filter-tab.active {
  background: rgba(255,255,255,0.12);
  border-color: rgba(255,255,255,0.25);
  color: white;
}
</style>
