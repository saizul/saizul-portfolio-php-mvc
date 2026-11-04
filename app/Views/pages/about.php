<?php $pageScript = 'about'; ?>

<!-- ── Hero ──────────────────────────────────────────────── -->
<section class="hero" style="min-height:85svh;">
  <div class="canvas-container">
    <canvas id="canvas-about"></canvas>
  </div>
  <div class="absolute inset-x-0 bottom-0 h-64 bg-gradient-to-t from-black to-transparent z-[1]"></div>

  <div class="container hero-content relative z-10">
    <div class="max-w-3xl">
      <div class="hero-eyebrow">
        <span class="dot"></span>
        AI Developer · Full-Stack Engineer · Builder
      </div>
      <h1 class="display-2 mb-6">
        Driven by curiosity.<br>
        <span class="gradient-text">Defined by craft.</span>
      </h1>
      <p class="body-lg max-w-2xl" style="color:rgba(255,255,255,0.5)">
        I'm Saizul Amin — a developer who believes the best software feels inevitable.
        Every system I build starts with a deep question and ends with something
        that genuinely changes how people work.
      </p>
    </div>
  </div>
</section>

<!-- ── Bio ───────────────────────────────────────────────── -->
<section class="section-py border-t border-white/5">
  <div class="container">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-start">
      <div class="lg:col-span-7 reveal-left">
        <p class="section-label">My Story</p>
        <h2 class="heading-1 mb-8">From code to intelligence.</h2>

        <div class="space-y-5 body-lg">
          <p>
            My journey into software began with a simple obsession: understanding how things
            work from first principles. That curiosity led me from writing basic scripts to
            architecting production AI systems that process millions of requests daily.
          </p>
          <p>
            Over the past 5+ years, I've worked across the full spectrum of the software
            stack — from training transformer models and building RAG pipelines, to designing
            microservice architectures and crafting pixel-perfect user interfaces. I believe
            technical depth and design sensibility aren't mutually exclusive.
          </p>
          <p>
            Today, I specialize in building AI-powered products — systems that learn from data,
            adapt to context, and deliver experiences that feel genuinely intelligent. Whether
            it's a conversational agent, a predictive analytics engine, or a computer vision
            pipeline, I approach every problem with the same rigour: understand deeply,
            design deliberately, ship confidently.
          </p>
          <p>
            When I'm not building, I'm writing about AI, contributing to open source, or
            exploring the philosophical implications of the systems we're creating.
          </p>
        </div>
      </div>

      <!-- Quick facts -->
      <div class="lg:col-span-5 reveal">
        <div class="card p-8 space-y-6">
          <h3 class="font-semibold text-sm text-gray-400 uppercase tracking-wider">Quick Facts</h3>
          <dl class="space-y-5">
            <?php
            $facts = [
              ['📍', 'Location',    'Available remotely worldwide'],
              ['💼', 'Open to',     'Full-time, Freelance, Advisory'],
              ['🎓', 'Education',   'Computer Science & AI'],
              ['🌐', 'Languages',   'English, Malay, Bahasa Indonesia'],
              ['⚡', 'Stack',       'Python, PHP, TypeScript, Go'],
              ['🤖', 'Specialty',   'LLMs, RAG, ML Systems, APIs'],
            ];
            foreach ($facts as [$icon, $key, $val]):
            ?>
            <div class="flex items-start gap-4">
              <span class="text-lg shrink-0 mt-0.5"><?= $icon ?></span>
              <div>
                <dt class="text-xs text-gray-600 uppercase tracking-wider mb-0.5"><?= $key ?></dt>
                <dd class="text-sm text-gray-200"><?= $val ?></dd>
              </div>
            </div>
            <?php endforeach; ?>
          </dl>

          <hr class="border-white/6">

          <a href="<?= url('/contact') ?>" class="btn btn-primary w-full justify-center text-sm">
            Work With Me
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Values ────────────────────────────────────────────── -->
<section class="section-py border-t border-white/5">
  <div class="container">
    <div class="section-header text-center">
      <p class="section-label justify-center">What drives me</p>
      <h2 class="heading-1">Core Values</h2>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <?php
      $values = [
        ['🔬', 'First Principles', 'I question assumptions and rebuild from the ground up when necessary. No cargo-culting.'],
        ['🎯', 'Purposeful Precision', 'Every decision is intentional. I build what\'s needed, not what\'s impressive.'],
        ['🚀', 'Continuous Growth', 'The field moves fast. I dedicate time daily to learning, experimenting, and updating my mental models.'],
        ['🤝', 'Radical Transparency', 'Clear communication, honest timelines, and proactive problem-solving define how I work.'],
      ];
      foreach ($values as $i => [$icon, $title, $desc]):
      ?>
      <div class="card p-6 reveal" style="animation-delay:<?= $i * 80 ?>ms">
        <div class="text-3xl mb-4"><?= $icon ?></div>
        <h3 class="font-semibold mb-2"><?= $title ?></h3>
        <p class="text-sm text-gray-500 leading-relaxed"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Experience timeline preview ──────────────────────── -->
<?php if (!empty($experience)): ?>
<section class="section-py border-t border-white/5">
  <div class="container">
    <div class="section-header flex items-end justify-between gap-4 flex-wrap">
      <div>
        <p class="section-label">Career</p>
        <h2 class="heading-1">Experience Highlights</h2>
      </div>
      <a href="<?= url('/experience') ?>" class="btn btn-ghost">Full timeline <span class="arrow">→</span></a>
    </div>

    <div class="max-w-3xl timeline">
      <?php foreach (array_slice($experience, 0, 4) as $i => $exp): ?>
      <div class="timeline-item reveal" style="animation-delay:<?= $i * 100 ?>ms">
        <div class="flex items-start justify-between gap-4 flex-wrap">
          <div>
            <span class="tag mb-2"><?= e($exp['type'] === 'work' ? 'Work' : 'Education') ?></span>
            <h3 class="font-semibold mt-1"><?= e($exp['role']) ?></h3>
            <p class="text-sm text-gray-400"><?= e($exp['company']) ?></p>
          </div>
          <p class="text-xs text-gray-600 shrink-0 mt-1">
            <?= e($exp['start_date']) ?> — <?= $exp['is_current'] ? '<span style="color:var(--accent)">Present</span>' : e($exp['end_date'] ?? '') ?>
          </p>
        </div>
        <?php if ($exp['description']): ?>
        <p class="text-sm text-gray-500 mt-3 leading-relaxed"><?= e($exp['description']) ?></p>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── CTA ───────────────────────────────────────────────── -->
<section class="section-py border-t border-white/5">
  <div class="container text-center max-w-2xl mx-auto">
    <h2 class="heading-1 mb-5">Ready to build together?</h2>
    <p class="body-lg mb-8">
      I'm selective about the projects I take on — because I give everything I have
      to the ones I do. If your problem is interesting, let's talk.
    </p>
    <a href="<?= url('/contact') ?>" class="btn btn-primary">
      Get in Touch
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M7 17L17 7M17 7H7M17 7v10"/>
      </svg>
    </a>
  </div>
</section>
