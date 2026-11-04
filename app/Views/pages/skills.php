<?php $pageScript = 'skills'; ?>

<!-- ── Hero ──────────────────────────────────────────────── -->
<section class="hero" style="min-height:70svh;">
  <div class="canvas-container">
    <canvas id="canvas-skills"></canvas>
  </div>
  <div class="absolute inset-x-0 bottom-0 h-64 bg-gradient-to-t from-black to-transparent z-[1]"></div>

  <div class="container hero-content relative z-10">
    <div class="max-w-3xl">
      <div class="hero-eyebrow"><span class="dot"></span>Technical Arsenal</div>
      <h1 class="display-2 mb-6">
        Skills &<br>
        <span class="gradient-text">Expertise.</span>
      </h1>
      <p class="body-lg max-w-xl" style="color:rgba(255,255,255,0.5)">
        Deep expertise across the AI stack — from research-grade model training
        to production engineering and elegant user interfaces.
      </p>
    </div>
  </div>
</section>

<!-- ── Skills grid ───────────────────────────────────────── -->
<section class="section-py">
  <div class="container">

    <?php if (!empty($skills_grouped)): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php foreach ($skills_grouped as $category => $skills): ?>
      <div class="reveal">
        <div class="flex items-center gap-2 mb-6">
          <p class="section-label" style="margin-bottom:0"><?= e($category) ?></p>
        </div>
        <div class="space-y-5">
          <?php foreach ($skills as $skill): ?>
          <div>
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm font-medium text-gray-200"><?= e($skill['name']) ?></span>
              <span class="text-xs text-gray-600 mono"><?= (int)$skill['proficiency'] ?>%</span>
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

    <?php else: ?>
    <!-- Static fallback -->
    <?php
    $skillsData = [
      'AI & Machine Learning' => [
        ['LLM Fine-tuning', 95], ['RAG Systems', 93], ['PyTorch', 90],
        ['LangChain / LangGraph', 92], ['Hugging Face', 88], ['Computer Vision', 82],
      ],
      'Backend Development' => [
        ['Python', 95], ['PHP 8', 90], ['FastAPI', 92],
        ['Node.js', 80], ['PostgreSQL', 88], ['Redis', 85],
      ],
      'Frontend & Full-Stack' => [
        ['React / Next.js', 85], ['TypeScript', 82], ['Tailwind CSS', 90],
        ['Three.js / WebGL', 75], ['GSAP', 80], ['REST / GraphQL', 88],
      ],
      'Infrastructure & DevOps' => [
        ['Docker / Kubernetes', 82], ['AWS / GCP', 80], ['CI/CD Pipelines', 83],
        ['Kafka / Redis', 78], ['Terraform', 70], ['Linux / Bash', 88],
      ],
    ];
    ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-12">
      <?php foreach ($skillsData as $category => $skills): ?>
      <div class="reveal">
        <p class="section-label mb-6"><?= $category ?></p>
        <div class="space-y-5">
          <?php foreach ($skills as [$name, $pct]): ?>
          <div>
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm font-medium text-gray-200"><?= $name ?></span>
              <span class="text-xs text-gray-600 mono"><?= $pct ?>%</span>
            </div>
            <div class="skill-bar">
              <div class="skill-bar-fill" data-pct="<?= $pct ?>"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── Tools & platforms ─────────────────────────────────── -->
<section class="section-py border-t border-white/5">
  <div class="container">
    <div class="section-header text-center">
      <p class="section-label justify-center">Ecosystem</p>
      <h2 class="heading-1">Tools & Platforms</h2>
    </div>
    <?php
    $toolGroups = [
      'AI / ML Frameworks' => ['OpenAI API', 'Anthropic', 'Hugging Face', 'LangChain', 'LangGraph', 'Ollama', 'FAISS', 'Pinecone'],
      'Data & Infrastructure' => ['PostgreSQL', 'MySQL', 'Redis', 'Kafka', 'Elasticsearch', 'Docker', 'Kubernetes', 'Terraform'],
      'Development & Build' => ['VS Code', 'Git', 'GitHub Actions', 'Postman', 'Jupyter', 'Linux', 'AWS', 'Cloudflare'],
    ];
    ?>
    <div class="space-y-8">
      <?php foreach ($toolGroups as $group => $tools): ?>
      <div class="reveal">
        <p class="text-xs text-gray-600 uppercase tracking-wider mb-4"><?= $group ?></p>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($tools as $tool): ?>
          <span class="tag text-gray-300"><?= $tool ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Currently learning ────────────────────────────────── -->
<section class="section-py border-t border-white/5">
  <div class="container">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
      <div class="reveal-left">
        <p class="section-label">Always Learning</p>
        <h2 class="heading-1 mb-5">Currently Exploring</h2>
        <p class="body-lg mb-8">
          The AI landscape evolves weekly. Here's what I'm actively researching
          and building with right now.
        </p>
        <a href="<?= url('/blog') ?>" class="btn btn-outline">
          Read my notes
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M7 17L17 7M17 7H7M17 7v10"/>
          </svg>
        </a>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 reveal">
        <?php
        $learning = [
          ['🧬', 'Multi-modal AI', 'Vision-language models and cross-modal reasoning systems'],
          ['🔗', 'Agent Workflows', 'Complex multi-agent coordination and planning algorithms'],
          ['⚡', 'Inference Optimization', 'Quantization, distillation, and edge deployment'],
          ['🌐', 'WebAssembly + AI', 'Running ML models directly in the browser'],
        ];
        foreach ($learning as [$icon, $title, $desc]):
        ?>
        <div class="card p-5">
          <div class="text-xl mb-3"><?= $icon ?></div>
          <p class="text-sm font-semibold mb-1.5"><?= $title ?></p>
          <p class="text-xs text-gray-500 leading-relaxed"><?= $desc ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
