<?php $pageScript = 'contact'; ?>

<!-- ── Hero ──────────────────────────────────────────────── -->
<section class="hero" style="min-height:65svh;">
  <div class="canvas-container">
    <canvas id="canvas-contact"></canvas>
  </div>
  <div class="absolute inset-x-0 bottom-0 h-64 bg-gradient-to-t from-black to-transparent z-[1]"></div>

  <div class="container hero-content relative z-10">
    <div class="max-w-3xl">
      <div class="hero-eyebrow"><span class="dot"></span>Let's connect</div>
      <h1 class="display-2 mb-6">
        Start a<br>
        <span class="gradient-text">Conversation.</span>
      </h1>
      <p class="body-lg max-w-xl" style="color:rgba(255,255,255,0.5)">
        Whether you have a project in mind, a technical question, or just want
        to connect — I respond to every message personally.
      </p>
    </div>
  </div>
</section>

<!-- ── Contact form + info ────────────────────────────────── -->
<section class="section-py">
  <div class="container">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">

      <!-- Form -->
      <div class="lg:col-span-7 reveal-left">
        <?php if (!empty($flash)): ?>
        <?php $fc = $flash['type'] === 'success'
          ? 'bg-emerald-500/10 border-emerald-500/25 text-emerald-300'
          : 'bg-red-500/10 border-red-500/25 text-red-300'; ?>
        <div class="mb-6 px-5 py-4 rounded-xl border text-sm <?= $fc ?>">
          <?php if ($flash['type'] === 'success'): ?>✓ <?php else: ?>✗ <?php endif; ?>
          <?= e($flash['message']) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/contact') ?>" class="space-y-5" id="contact-form">
          <?= \App\Core\Auth::csrfField() ?>
          <!-- Honeypot -->
          <input type="text" name="website" tabindex="-1" autocomplete="off"
                 class="absolute opacity-0 pointer-events-none" aria-hidden="true">

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
              <label for="name" class="block text-xs text-gray-400 mb-2 uppercase tracking-wider">Name *</label>
              <input type="text" name="name" id="name" required autocomplete="name"
                     class="w-full bg-white/3 border border-white/10 rounded-xl px-4 py-3 text-sm text-white
                            focus:outline-none focus:border-white/30 transition placeholder-gray-600"
                     placeholder="Your name">
            </div>
            <div>
              <label for="email" class="block text-xs text-gray-400 mb-2 uppercase tracking-wider">Email *</label>
              <input type="email" name="email" id="email" required autocomplete="email"
                     class="w-full bg-white/3 border border-white/10 rounded-xl px-4 py-3 text-sm text-white
                            focus:outline-none focus:border-white/30 transition placeholder-gray-600"
                     placeholder="you@company.com">
            </div>
          </div>

          <div>
            <label for="subject" class="block text-xs text-gray-400 mb-2 uppercase tracking-wider">Subject</label>
            <select name="subject" id="subject"
                    class="w-full bg-white/3 border border-white/10 rounded-xl px-4 py-3 text-sm text-white
                           focus:outline-none focus:border-white/30 transition appearance-none"
                    style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E\");background-repeat:no-repeat;background-position:right 1rem center">
              <option value="">Select a topic…</option>
              <option>AI Project Development</option>
              <option>Full-Stack Development</option>
              <option>Technical Consulting</option>
              <option>Speaking / Writing</option>
              <option>Collaboration / Partnership</option>
              <option>Something else</option>
            </select>
          </div>

          <div>
            <label for="message" class="block text-xs text-gray-400 mb-2 uppercase tracking-wider">Message *</label>
            <textarea name="message" id="message" required rows="6"
                      class="w-full bg-white/3 border border-white/10 rounded-xl px-4 py-3 text-sm text-white
                             focus:outline-none focus:border-white/30 transition placeholder-gray-600 resize-none"
                      placeholder="Tell me about your project, what you're trying to build, or what challenge you're facing…"></textarea>
          </div>

          <button type="submit" id="submit-btn"
                  class="btn btn-primary w-full justify-center text-sm">
            <span id="btn-text">Send Message</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M7 17L17 7M17 7H7M17 7v10"/>
            </svg>
          </button>

          <p class="text-xs text-gray-600 text-center">
            I typically respond within 24 hours on business days.
          </p>
        </form>
      </div>

      <!-- Contact info -->
      <div class="lg:col-span-5 space-y-8 reveal">
        <!-- Direct contact -->
        <div>
          <p class="section-label mb-5">Direct Contact</p>
          <div class="space-y-4">
            <a href="mailto:saizulamin@gmail.com"
               class="flex items-center gap-4 card p-4 hover:no-underline group">
              <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center shrink-0 group-hover:bg-white/10 transition">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 mb-0.5">Email</p>
                <p class="text-sm font-medium">saizulamin@gmail.com</p>
              </div>
            </a>
          </div>
        </div>

        <!-- Social links -->
        <div>
          <p class="section-label mb-5">Find Me Online</p>
          <div class="space-y-3">
            <?php
            $socials = [
              ['GitHub',   'https://github.com/saizulamin',    'M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z', 'Open source & code'],
              ['LinkedIn', 'https://linkedin.com/in/saizulamin', 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z', 'Professional network'],
              ['Twitter/X', 'https://twitter.com/saizulamin', 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z', 'Thoughts & updates'],
            ];
            foreach ($socials as [$name, $url, $path, $desc]):
            ?>
            <a href="<?= $url ?>" target="_blank" rel="noopener"
               class="flex items-center gap-4 card p-4 group hover:no-underline">
              <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center shrink-0 group-hover:bg-white/10 transition">
                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                  <path d="<?= $path ?>"/>
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium"><?= $name ?></p>
                <p class="text-xs text-gray-500"><?= $desc ?></p>
              </div>
              <svg class="w-3.5 h-3.5 text-gray-600 group-hover:text-gray-400 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Availability -->
        <div class="card p-5">
          <div class="flex items-center gap-2 mb-3">
            <div class="w-2 h-2 rounded-full bg-emerald-400" style="box-shadow:0 0 8px rgba(110,231,183,0.6)"></div>
            <p class="text-sm font-medium">Currently Available</p>
          </div>
          <p class="text-xs text-gray-500 leading-relaxed">
            Open to new projects, consulting engagements, and full-time opportunities.
            Response time: typically within 24 hours.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
document.getElementById('contact-form').addEventListener('submit', function() {
  const btn = document.getElementById('submit-btn');
  const txt = document.getElementById('btn-text');
  btn.disabled = true;
  txt.textContent = 'Sending…';
});
</script>
