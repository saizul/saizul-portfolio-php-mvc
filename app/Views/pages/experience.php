<?php $pageScript = 'experience'; ?>

<!-- ── Hero ──────────────────────────────────────────────── -->
<section class="hero" style="min-height:70svh;">
  <div class="canvas-container">
    <canvas id="canvas-experience"></canvas>
  </div>
  <div class="absolute inset-x-0 bottom-0 h-64 bg-gradient-to-t from-black to-transparent z-[1]"></div>

  <div class="container hero-content relative z-10">
    <div class="max-w-3xl">
      <div class="hero-eyebrow"><span class="dot"></span>Career Journey</div>
      <h1 class="display-2 mb-6">
        5+ Years of<br>
        <span class="gradient-text">Building & Shipping.</span>
      </h1>
      <p class="body-lg max-w-xl" style="color:rgba(255,255,255,0.5)">
        From junior engineer to AI specialist — a journey shaped by curiosity,
        hard problems, and an unrelenting drive to ship things that matter.
      </p>
    </div>
  </div>
</section>

<!-- ── Work Experience ───────────────────────────────────── -->
<section class="section-py">
  <div class="container">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
      <div class="lg:col-span-8">
        <div class="section-header">
          <p class="section-label">Career</p>
          <h2 class="heading-1">Work Experience</h2>
        </div>

        <?php if (!empty($work)): ?>
        <div class="timeline">
          <?php foreach ($work as $i => $job): ?>
          <div class="timeline-item reveal" style="animation-delay:<?= $i * 100 ?>ms">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
              <div>
                <h3 class="font-semibold text-lg"><?= e($job['role']) ?></h3>
                <p class="text-gray-400"><?= e($job['company']) ?></p>
              </div>
              <div class="text-right">
                <p class="text-sm text-gray-500">
                  <?= e($job['start_date']) ?> –
                  <?php if ($job['is_current']): ?>
                  <span style="color:var(--accent)">Present</span>
                  <?php else: ?>
                  <?= e($job['end_date'] ?? '') ?>
                  <?php endif; ?>
                </p>
                <?php if ($job['is_current']): ?>
                <span class="tag mt-1" style="color:var(--accent);border-color:rgba(110,231,183,0.2)">Current</span>
                <?php endif; ?>
              </div>
            </div>
            <?php if ($job['description']): ?>
            <p class="text-gray-500 text-sm leading-relaxed"><?= nl2br(e($job['description'])) ?></p>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <!-- Static fallback -->
        <div class="timeline">
          <?php
          $jobs = [
            ['AI Developer & Tech Lead', 'Freelance / Remote', '2022', null, true, 'Leading end-to-end development of AI-powered products for clients across Southeast Asia, Europe, and North America. Architected RAG systems, fine-tuned LLMs, and built production ML pipelines handling millions of requests daily. Managed small engineering teams and provided technical direction for AI strategy.'],
            ['Full-Stack Developer', 'Tech Startup', '2020', '2022', false, 'Built and scaled core platform features serving 50,000+ users. Led migration from monolith to microservices architecture, reducing infrastructure costs by 35%. Implemented real-time features using WebSockets, Redis pub/sub, and event-driven design patterns.'],
            ['Junior Software Engineer', 'Digital Agency', '2019', '2020', false, 'Developed bespoke web applications for enterprise clients. Gained deep experience in PHP, MySQL, and React. Contributed to internal tooling that reduced project delivery time by 20%.'],
          ];
          foreach ($jobs as $i => [$role, $company, $start, $end, $current, $desc]):
          ?>
          <div class="timeline-item reveal" style="animation-delay:<?= $i * 100 ?>ms">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
              <div>
                <h3 class="font-semibold text-lg"><?= $role ?></h3>
                <p class="text-gray-400"><?= $company ?></p>
              </div>
              <div class="text-right">
                <p class="text-sm text-gray-500"><?= $start ?> – <?= $current ? '<span style="color:var(--accent)">Present</span>' : $end ?></p>
                <?php if ($current): ?><span class="tag mt-1" style="color:var(--accent);border-color:rgba(110,231,183,0.2)">Current</span><?php endif; ?>
              </div>
            </div>
            <p class="text-gray-500 text-sm leading-relaxed"><?= $desc ?></p>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Education sidebar -->
      <div class="lg:col-span-4">
        <div class="section-header">
          <p class="section-label">Learning</p>
          <h2 class="heading-2">Education</h2>
        </div>

        <?php if (!empty($education)): ?>
        <div class="space-y-6">
          <?php foreach ($education as $edu): ?>
          <div class="card p-5 reveal">
            <h3 class="font-semibold text-sm"><?= e($edu['role']) ?></h3>
            <p class="text-sm text-gray-400 mt-0.5"><?= e($edu['company']) ?></p>
            <p class="text-xs text-gray-600 mt-2">
              <?= e($edu['start_date']) ?> – <?= e($edu['end_date'] ?? 'Present') ?>
            </p>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="space-y-6">
          <?php
          $edu_items = [
            ['BSc Computer Science', 'University', '2016 – 2019', 'Specialized in Machine Learning and Distributed Systems.'],
            ['AI & Deep Learning Specialization', 'Coursera / deeplearning.ai', '2020', '5-course series covering CNNs, RNNs, and transformer architectures.'],
            ['AWS Certified Solutions Architect', 'Amazon Web Services', '2021', 'Professional level certification in cloud architecture.'],
          ];
          foreach ($edu_items as $i => [$degree, $institution, $period, $desc]):
          ?>
          <div class="card p-5 reveal" style="animation-delay:<?= $i * 80 ?>ms">
            <h3 class="font-semibold text-sm"><?= $degree ?></h3>
            <p class="text-sm text-gray-400 mt-0.5"><?= $institution ?></p>
            <p class="text-xs text-gray-600 mt-1"><?= $period ?></p>
            <p class="text-xs text-gray-500 mt-2 leading-relaxed"><?= $desc ?></p>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Key achievements -->
        <div class="mt-8">
          <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Key Numbers</h3>
          <div class="space-y-4">
            <?php
            $metrics = [
              ['5+',   'Years in software development'],
              ['30+',  'Projects delivered end-to-end'],
              ['10+',  'AI systems in production'],
              ['3+',   'Countries worked across'],
            ];
            foreach ($metrics as [$n, $l]):
            ?>
            <div class="flex items-center justify-between border-b border-white/5 pb-4">
              <p class="text-sm text-gray-400"><?= $l ?></p>
              <p class="font-bold text-lg"><?= $n ?></p>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ───────────────────────────────────────────────── -->
<section class="section-py border-t border-white/5">
  <div class="container text-center max-w-xl mx-auto">
    <h2 class="heading-1 mb-5">Want to work together?</h2>
    <p class="body-lg mb-8">
      I'm always open to interesting projects, technical challenges,
      and the right full-time opportunity.
    </p>
    <div class="flex flex-wrap justify-center gap-4">
      <a href="<?= url('/contact') ?>" class="btn btn-primary">Contact Me</a>
      <a href="<?= url('/portfolio') ?>" class="btn btn-outline">See My Work</a>
    </div>
  </div>
</section>
