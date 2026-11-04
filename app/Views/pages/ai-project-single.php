<?php $pageScript = 'projects'; ?>
<article class="pt-32 pb-20">
  <div class="container max-w-4xl">

    <!-- Breadcrumb -->
    <?= \App\Core\SEO::breadcrumbHtml() ?>

    <!-- Hero -->
    <div class="mb-12">
      <div class="hero-eyebrow mb-4"><span class="dot"></span>AI Project</div>
      <h1 class="display-2 mb-6"><?= htmlspecialchars($project['title']) ?></h1>
      <?php if ($project['tech_stack']): ?>
      <div class="flex flex-wrap gap-2 mb-6">
        <?php foreach (explode(',', $project['tech_stack']) as $tech): ?>
        <span class="badge badge-blue"><?= htmlspecialchars(trim($tech)) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Images gallery -->
    <?php if ($images): ?>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-12">
      <?php foreach ($images as $img): ?>
      <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($project['title']) ?>"
           class="w-full h-48 object-cover rounded-xl" loading="lazy">
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Problem / Solution -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
      <?php if ($project['problem']): ?>
      <div class="admin-card p-6">
        <h2 class="text-lg font-bold text-crimson mb-3">Problem</h2>
        <p class="text-slate-300 leading-relaxed"><?= nl2br(htmlspecialchars($project['problem'])) ?></p>
      </div>
      <?php endif; ?>
      <?php if ($project['solution']): ?>
      <div class="admin-card p-6">
        <h2 class="text-lg font-bold text-emerald-400 mb-3">Solution</h2>
        <p class="text-slate-300 leading-relaxed"><?= nl2br(htmlspecialchars($project['solution'])) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <!-- Workflow diagram -->
    <?php if ($project['workflow_diagram']): ?>
    <div class="mb-12">
      <h2 class="text-xl font-bold text-white mb-4">Workflow Diagram</h2>
      <img src="<?= htmlspecialchars($project['workflow_diagram']) ?>" alt="Workflow Diagram"
           class="w-full rounded-xl border border-white/10" loading="lazy">
    </div>
    <?php endif; ?>

    <!-- Architecture notes -->
    <?php if ($project['architecture_notes']): ?>
    <div class="mb-12">
      <h2 class="text-xl font-bold text-white mb-4">Architecture Notes</h2>
      <div class="prose prose-invert max-w-none bg-slate-900/50 rounded-xl p-6 border border-white/5 font-mono text-sm text-slate-300 whitespace-pre-wrap leading-relaxed">
        <?= htmlspecialchars($project['architecture_notes']) ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Video -->
    <?php if ($project['video_url']): ?>
    <div class="mb-12">
      <h2 class="text-xl font-bold text-white mb-4">Demo Video</h2>
      <?php
      $videoUrl = $project['video_url'];
      if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrl, $m)) {
          $videoUrl = "https://www.youtube.com/embed/{$m[1]}";
      } elseif (preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $m)) {
          $videoUrl = "https://player.vimeo.com/video/{$m[1]}";
      }
      ?>
      <div class="aspect-video rounded-xl overflow-hidden border border-white/10">
        <iframe src="<?= htmlspecialchars($videoUrl) ?>" class="w-full h-full"
                allowfullscreen loading="lazy" title="Project Demo"></iframe>
      </div>
    </div>
    <?php endif; ?>

    <!-- Results -->
    <?php if ($project['results']): ?>
    <div class="mb-12 p-6 rounded-xl border border-emerald-400/20 bg-emerald-400/5">
      <h2 class="text-xl font-bold text-emerald-400 mb-3">Results</h2>
      <p class="text-slate-300 leading-relaxed"><?= nl2br(htmlspecialchars($project['results'])) ?></p>
    </div>
    <?php endif; ?>

    <!-- Back -->
    <a href="/ai-projects" class="btn-secondary">← Back to AI Projects</a>
  </div>
</article>

<!-- 3D canvas background (same neural network scene) -->
<canvas id="canvas-projects" class="canvas-bg" aria-hidden="true"></canvas>
