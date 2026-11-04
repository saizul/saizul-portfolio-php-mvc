<?php
/** @var array $settings */
$s = $settings;
?>
<?php $pageTitle = 'Theme Settings'; ?>
<?php ob_start(); ?>

<div class="flex gap-6 h-full" x-data="themeEditor()">

  <!-- ============================================================
       LEFT PANEL — Controls
  ============================================================ -->
  <div class="flex-1 min-w-0 overflow-y-auto">

    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl font-bold text-white">Theme Settings</h1>
        <p class="text-slate-400 text-sm mt-1">Customize colors, fonts, animations and layout.</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="resetDefaults()" type="button"
          class="px-4 py-2 rounded-lg border border-slate-600 text-slate-300 text-sm hover:border-slate-400 transition">
          Reset Defaults
        </button>
        <button @click="saveTheme()" type="button"
          :disabled="saving"
          class="px-5 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition disabled:opacity-50">
          <span x-show="!saving">Save Theme</span>
          <span x-show="saving">Saving…</span>
        </button>
      </div>
    </div>

    <!-- Save message -->
    <div x-show="message" x-transition
      class="mb-6 p-4 rounded-lg text-sm font-medium"
      :class="messageType === 'success' ? 'bg-emerald-900/40 text-emerald-300 border border-emerald-700' : 'bg-red-900/40 text-red-300 border border-red-700'"
      x-text="message"></div>

    <form id="themeForm">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

      <!-- --------------------------------------------------------
           TAB NAV
      -------------------------------------------------------- -->
      <div class="flex gap-1 mb-8 p-1 bg-slate-900 rounded-xl border border-slate-800 w-fit">
        <?php foreach (['colors' => '🎨 Colors', 'typography' => '✏️ Typography', 'animations' => '✨ Animations', 'layout' => '📐 Layout', 'custom' => '💻 Custom CSS'] as $tab => $label): ?>
        <button type="button" @click="activeTab = '<?= $tab ?>'"
          class="px-4 py-2 text-sm rounded-lg transition font-medium"
          :class="activeTab === '<?= $tab ?>' ? 'bg-red-600 text-white' : 'text-slate-400 hover:text-white'">
          <?= $label ?>
        </button>
        <?php endforeach; ?>
      </div>

      <!-- --------------------------------------------------------
           TAB: COLORS
      -------------------------------------------------------- -->
      <div x-show="activeTab === 'colors'" x-transition>
        <div class="grid grid-cols-2 gap-6">
          <?php $colorFields = [
            'color_crimson'      => ['Accent / Crimson',   'Primary brand accent color'],
            'color_teal'         => ['Accent / Teal',      'Secondary accent color'],
            'color_bg_dark'      => ['Background Dark',    'Main page background'],
            'color_bg_card'      => ['Background Card',    'Card / panel background'],
            'color_text_primary' => ['Text Primary',       'Main text color'],
            'color_text_muted'   => ['Text Muted',         'Secondary / muted text'],
            'color_border'       => ['Border Color',       'Dividers and borders'],
          ]; ?>
          <?php foreach ($colorFields as $key => [$label, $hint]): ?>
          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-1"><?= $label ?></label>
            <p class="text-xs text-slate-500 mb-3"><?= $hint ?></p>
            <div class="flex items-center gap-3">
              <input type="color" name="<?= $key ?>" value="<?= htmlspecialchars($s[$key] ?? '') ?>"
                class="w-12 h-10 rounded-lg cursor-pointer border-0 bg-transparent"
                @change="preview()">
              <input type="text" name="<?= $key ?>_text" value="<?= htmlspecialchars($s[$key] ?? '') ?>"
                placeholder="#c0392b"
                class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white font-mono"
                @input="syncColorText(this)">
            </div>
          </div>
          <?php endforeach; ?>

          <!-- Nav BG (rgba) -->
          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-1">Nav Background</label>
            <p class="text-xs text-slate-500 mb-3">Supports rgba() for transparency</p>
            <input type="text" name="color_nav_bg"
              value="<?= htmlspecialchars($s['color_nav_bg'] ?? 'rgba(10,10,10,0.85)') ?>"
              class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white font-mono"
              @input="preview()">
          </div>
        </div>

        <!-- Color Presets -->
        <div class="mt-8">
          <h3 class="text-sm font-semibold text-slate-300 mb-4">Color Presets</h3>
          <div class="flex flex-wrap gap-3">
            <?php $presets = [
              'Crimson Dark'  => ['color_crimson'=>'#c0392b','color_teal'=>'#6ee7b7','color_bg_dark'=>'#0a0a0a'],
              'Ocean Blue'    => ['color_crimson'=>'#3b82f6','color_teal'=>'#34d399','color_bg_dark'=>'#050d1a'],
              'Purple Haze'   => ['color_crimson'=>'#8b5cf6','color_teal'=>'#f472b6','color_bg_dark'=>'#0d0a1a'],
              'Gold Rush'     => ['color_crimson'=>'#f59e0b','color_teal'=>'#10b981','color_bg_dark'=>'#0c0a00'],
              'Arctic White'  => ['color_crimson'=>'#ef4444','color_teal'=>'#06b6d4','color_bg_dark'=>'#f8fafc'],
            ]; ?>
            <?php foreach ($presets as $name => $vals): ?>
            <button type="button"
              @click="applyPreset(<?= htmlspecialchars(json_encode($vals)) ?>)"
              class="flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg text-sm text-slate-300 transition">
              <span class="w-4 h-4 rounded-full border-2 border-white/20"
                style="background:<?= $vals['color_crimson'] ?>"></span>
              <span class="w-4 h-4 rounded-full border-2 border-white/20"
                style="background:<?= $vals['color_teal'] ?>"></span>
              <?= $name ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- --------------------------------------------------------
           TAB: TYPOGRAPHY
      -------------------------------------------------------- -->
      <div x-show="activeTab === 'typography'" x-transition>
        <div class="grid grid-cols-2 gap-6">

          <?php $googleFonts = ['Inter','Manrope','DM Sans','Plus Jakarta Sans','Outfit','Sora','Space Grotesk','Geist','Raleway','Poppins']; ?>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-3">Heading Font</label>
            <select name="font_heading" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" @change="preview()">
              <?php foreach ($googleFonts as $f): ?>
              <option value="<?= $f ?>" <?= ($s['font_heading'] ?? 'Inter') === $f ? 'selected' : '' ?>><?= $f ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-3">Body Font</label>
            <select name="font_body" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" @change="preview()">
              <?php foreach ($googleFonts as $f): ?>
              <option value="<?= $f ?>" <?= ($s['font_body'] ?? 'Inter') === $f ? 'selected' : '' ?>><?= $f ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-1">Monospace Font</label>
            <select name="font_mono" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" @change="preview()">
              <?php foreach (['JetBrains Mono','Fira Code','Source Code Pro','IBM Plex Mono','Cascadia Code'] as $f): ?>
              <option value="<?= $f ?>" <?= ($s['font_mono'] ?? 'JetBrains Mono') === $f ? 'selected' : '' ?>><?= $f ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-1">Base Font Size: <span x-text="fontSizePx" class="text-red-400"></span>px</label>
            <input type="range" name="font_size_base" min="14" max="20" step="1"
              value="<?= (int)($s['font_size_base'] ?? 16) ?>"
              class="w-full accent-red-600 mt-3"
              @input="fontSizePx = $event.target.value; preview()">
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-3">Heading Font Weight</label>
            <select name="font_weight_heading" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
              <?php foreach (['400'=>'Regular (400)','500'=>'Medium (500)','600'=>'SemiBold (600)','700'=>'Bold (700)','800'=>'ExtraBold (800)','900'=>'Black (900)'] as $w => $l): ?>
              <option value="<?= $w ?>" <?= ($s['font_weight_heading'] ?? '700') === $w ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-1">Heading Letter Spacing: <span x-text="letterSpacing" class="text-red-400"></span>em</label>
            <input type="range" name="letter_spacing_heading" min="-0.06" max="0.1" step="0.005"
              value="<?= $s['letter_spacing_heading'] ?? '-0.03' ?>"
              class="w-full accent-red-600 mt-3"
              @input="letterSpacing = parseFloat($event.target.value).toFixed(3); preview()">
          </div>

        </div>

        <!-- Font Preview -->
        <div class="mt-8 bg-slate-900 border border-slate-800 rounded-xl p-6">
          <p class="text-xs text-slate-500 mb-3 uppercase tracking-widest">Font Preview</p>
          <p class="text-4xl font-bold text-white mb-2" id="font-preview-heading"
            :style="`font-family: var(--font-heading); font-weight: var(--font-weight-heading); letter-spacing: var(--letter-spacing-heading)`">
            Saizul Amin
          </p>
          <p class="text-slate-400" id="font-preview-body"
            :style="`font-family: var(--font-body); font-size: var(--font-size-base)`">
            Building intelligent systems at the intersection of AI, data, and human experience.
          </p>
          <code class="text-xs text-emerald-400 mt-3 block" id="font-preview-mono"
            :style="`font-family: var(--font-mono)`">
            const ai = await model.generate({ prompt, temperature: 0.7 });
          </code>
        </div>
      </div>

      <!-- --------------------------------------------------------
           TAB: ANIMATIONS
      -------------------------------------------------------- -->
      <div x-show="activeTab === 'animations'" x-transition>
        <div class="grid grid-cols-2 gap-6">

          <?php $toggles = [
            'enable_3d'           => ['Enable 3D Scenes', 'Three.js environments on each page'],
            'enable_particles'    => ['Enable Particles', 'Particle systems (galaxy, constellation)'],
            'enable_smooth_scroll'=> ['Smooth Scroll (Lenis)', 'Inertia-based smooth scrolling'],
          ]; ?>
          <?php foreach ($toggles as $key => [$label, $desc]): ?>
          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-300"><?= $label ?></p>
              <p class="text-xs text-slate-500 mt-0.5"><?= $desc ?></p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" name="<?= $key ?>" value="1"
                <?= ($s[$key] ?? '1') === '1' ? 'checked' : '' ?>
                class="sr-only peer">
              <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer
                peer-checked:after:translate-x-full peer-checked:after:border-white after:content-['']
                after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300
                after:border after:rounded-full after:h-5 after:w-5 after:transition-all
                peer-checked:bg-red-600"></div>
            </label>
          </div>
          <?php endforeach; ?>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-1">Animation Speed: <span x-text="animSpeed + 'x'" class="text-red-400"></span></label>
            <p class="text-xs text-slate-500 mb-3">1.0 = normal, 0.5 = slower, 2.0 = faster</p>
            <input type="range" name="animation_speed" min="0.25" max="3" step="0.25"
              value="<?= $s['animation_speed'] ?? '1.0' ?>"
              class="w-full accent-red-600"
              @input="animSpeed = $event.target.value">
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-1">Particle Count: <span x-text="particleCount" class="text-red-400"></span></label>
            <p class="text-xs text-slate-500 mb-3">Higher = denser but slower on mobile</p>
            <input type="range" name="particle_count" min="500" max="8000" step="500"
              value="<?= (int)($s['particle_count'] ?? 2000) ?>"
              class="w-full accent-red-600"
              @input="particleCount = $event.target.value">
          </div>

        </div>

        <div class="mt-6 bg-amber-900/20 border border-amber-700/40 rounded-xl p-4 text-sm text-amber-300">
          <strong>Note:</strong> Disabling 3D scenes shows static gradient fallbacks that respect
          <code>prefers-reduced-motion</code>. Recommended for accessibility and performance on low-end devices.
        </div>
      </div>

      <!-- --------------------------------------------------------
           TAB: LAYOUT
      -------------------------------------------------------- -->
      <div x-show="activeTab === 'layout'" x-transition>
        <div class="grid grid-cols-2 gap-6">

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-3">Navigation Style</label>
            <div class="grid grid-cols-3 gap-2">
              <?php foreach (['floating'=>'Floating','full-width'=>'Full Width','minimal'=>'Minimal'] as $v => $l): ?>
              <label class="flex flex-col items-center gap-2 p-3 rounded-lg border-2 cursor-pointer transition"
                :class="navStyle === '<?= $v ?>' ? 'border-red-600 bg-red-900/20' : 'border-slate-700 hover:border-slate-500'">
                <input type="radio" name="nav_style" value="<?= $v ?>"
                  <?= ($s['nav_style'] ?? 'floating') === $v ? 'checked' : '' ?>
                  @change="navStyle = '<?= $v ?>'; preview()" class="sr-only">
                <span class="text-sm text-slate-300"><?= $l ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-3">Footer Style</label>
            <div class="grid grid-cols-3 gap-2">
              <?php foreach (['detailed'=>'Detailed','minimal'=>'Minimal','hidden'=>'Hidden'] as $v => $l): ?>
              <label class="flex flex-col items-center gap-2 p-3 rounded-lg border-2 cursor-pointer transition"
                :class="footerStyle === '<?= $v ?>' ? 'border-red-600 bg-red-900/20' : 'border-slate-700 hover:border-slate-500'">
                <input type="radio" name="footer_style" value="<?= $v ?>"
                  <?= ($s['footer_style'] ?? 'detailed') === $v ? 'checked' : '' ?>
                  @change="footerStyle = '<?= $v ?>'" class="sr-only">
                <span class="text-sm text-slate-300"><?= $l ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-3">Hero Layout</label>
            <div class="grid grid-cols-3 gap-2">
              <?php foreach (['center'=>'Centered','left'=>'Left Align','split'=>'Split'] as $v => $l): ?>
              <label class="flex flex-col items-center gap-2 p-3 rounded-lg border-2 cursor-pointer transition"
                :class="heroLayout === '<?= $v ?>' ? 'border-red-600 bg-red-900/20' : 'border-slate-700 hover:border-slate-500'">
                <input type="radio" name="hero_layout" value="<?= $v ?>"
                  <?= ($s['hero_layout'] ?? 'center') === $v ? 'checked' : '' ?>
                  @change="heroLayout = '<?= $v ?>'" class="sr-only">
                <span class="text-sm text-slate-300"><?= $l ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-3">Card Style</label>
            <div class="grid grid-cols-3 gap-2">
              <?php foreach (['glass'=>'Glass','solid'=>'Solid','outline'=>'Outline'] as $v => $l): ?>
              <label class="flex flex-col items-center gap-2 p-3 rounded-lg border-2 cursor-pointer transition"
                :class="cardStyle === '<?= $v ?>' ? 'border-red-600 bg-red-900/20' : 'border-slate-700 hover:border-slate-500'">
                <input type="radio" name="card_style" value="<?= $v ?>"
                  <?= ($s['card_style'] ?? 'glass') === $v ? 'checked' : '' ?>
                  @change="cardStyle = '<?= $v ?>'" class="sr-only">
                <span class="text-sm text-slate-300"><?= $l ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <label class="block text-sm font-medium text-slate-300 mb-1">Border Radius: <span x-text="borderRadius + 'px'" class="text-red-400"></span></label>
            <input type="range" name="border_radius" min="0" max="24" step="2"
              value="<?= (int)($s['border_radius'] ?? 12) ?>"
              class="w-full accent-red-600 mt-3"
              @input="borderRadius = $event.target.value">
            <div class="flex justify-between text-xs text-slate-500 mt-1">
              <span>Sharp</span><span>Rounded</span><span>Pill</span>
            </div>
          </div>

        </div>
      </div>

      <!-- --------------------------------------------------------
           TAB: CUSTOM CSS
      -------------------------------------------------------- -->
      <div x-show="activeTab === 'custom'" x-transition>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
          <label class="block text-sm font-medium text-slate-300 mb-2">Custom CSS</label>
          <p class="text-xs text-slate-500 mb-4">Injected after theme variables. Use CSS custom properties (--color-crimson, etc.) for consistency.</p>
          <textarea name="custom_css" rows="20"
            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-sm text-emerald-300 font-mono resize-y focus:outline-none focus:border-red-500"
            placeholder="/* Example */&#10;.hero-title { text-shadow: 0 0 40px var(--color-crimson); }"><?= htmlspecialchars($s['custom_css'] ?? '') ?></textarea>
        </div>
      </div>

    </form>
  </div>

  <!-- ============================================================
       RIGHT PANEL — Live Preview
  ============================================================ -->
  <div class="w-[420px] flex-shrink-0">
    <div class="sticky top-0">
      <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-800">
          <span class="text-sm font-medium text-slate-300">Live Preview</span>
          <div class="flex items-center gap-2">
            <button @click="previewDevice = 'desktop'" type="button"
              :class="previewDevice === 'desktop' ? 'text-red-400' : 'text-slate-500'"
              class="text-xs px-2 py-1 rounded hover:text-white transition">Desktop</button>
            <button @click="previewDevice = 'mobile'" type="button"
              :class="previewDevice === 'mobile' ? 'text-red-400' : 'text-slate-500'"
              class="text-xs px-2 py-1 rounded hover:text-white transition">Mobile</button>
            <a href="/admin/theme/preview" target="_blank"
              class="text-xs px-3 py-1 bg-slate-700 hover:bg-slate-600 rounded text-slate-300 transition">Open</a>
          </div>
        </div>
        <!-- Preview area with live CSS injection -->
        <div class="relative bg-slate-950" :style="previewDevice === 'mobile' ? 'height:700px' : 'height:500px'">
          <div id="theme-preview-panel"
            class="w-full h-full overflow-hidden flex flex-col items-center justify-center p-6 transition-all"
            :style="previewStyles">
            <!-- Simulated page preview -->
            <div class="w-full max-w-sm">
              <!-- Nav mock -->
              <div class="rounded-xl px-5 py-3 mb-6 flex items-center justify-between"
                :style="`background: var(--color-nav-bg); border: 1px solid var(--color-border); border-radius: var(--radius);`">
                <span class="font-bold text-sm" :style="`color: var(--color-crimson); font-family: var(--font-heading)`">SA</span>
                <div class="flex gap-4">
                  <span class="text-xs" :style="`color: var(--color-text-muted); font-family: var(--font-body)`">About</span>
                  <span class="text-xs" :style="`color: var(--color-text-muted)`">Work</span>
                  <span class="text-xs px-3 py-1 rounded-full text-white text-xs" :style="`background: var(--color-crimson); border-radius: var(--radius)`">Hire</span>
                </div>
              </div>
              <!-- Hero mock -->
              <div class="text-center mb-6">
                <h1 class="text-3xl font-bold mb-2 leading-tight"
                  :style="`color: var(--color-text-primary); font-family: var(--font-heading); font-weight: var(--font-weight-heading); letter-spacing: var(--letter-spacing-heading)`">
                  Saizul Amin
                </h1>
                <p class="text-sm mb-4" :style="`color: var(--color-teal); font-family: var(--font-body)`">
                  AI Engineer &amp; Data Strategist
                </p>
                <p class="text-xs leading-relaxed" :style="`color: var(--color-text-muted)`">
                  Building intelligent systems at the intersection of AI, data, and experience.
                </p>
              </div>
              <!-- Card mock -->
              <div class="p-4 mb-4"
                :style="`background: var(--color-bg-card); border: 1px solid var(--color-border); border-radius: var(--radius)`">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center"
                    :style="`background: var(--color-crimson)`">
                    <span class="text-white text-xs">AI</span>
                  </div>
                  <span class="text-sm font-semibold" :style="`color: var(--color-text-primary); font-family: var(--font-heading)`">Neural Network</span>
                </div>
                <p class="text-xs" :style="`color: var(--color-text-muted); font-family: var(--font-body)`">
                  Deep learning model for natural language understanding.
                </p>
              </div>
              <!-- Button mock -->
              <button type="button" class="w-full py-3 font-semibold text-sm text-white transition"
                :style="`background: var(--color-crimson); border-radius: var(--radius); font-family: var(--font-heading)`">
                View My Work →
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
function themeEditor() {
  return {
    activeTab: 'colors',
    saving: false,
    message: '',
    messageType: 'success',
    previewDevice: 'desktop',

    // Reactive values for sliders/toggles
    fontSizePx: <?= (int)($s['font_size_base'] ?? 16) ?>,
    letterSpacing: '<?= $s['letter_spacing_heading'] ?? '-0.030' ?>',
    animSpeed: <?= (float)($s['animation_speed'] ?? 1.0) ?>,
    particleCount: <?= (int)($s['particle_count'] ?? 2000) ?>,
    borderRadius: <?= (int)($s['border_radius'] ?? 12) ?>,
    navStyle: '<?= htmlspecialchars($s['nav_style'] ?? 'floating') ?>',
    footerStyle: '<?= htmlspecialchars($s['footer_style'] ?? 'detailed') ?>',
    heroLayout: '<?= htmlspecialchars($s['hero_layout'] ?? 'center') ?>',
    cardStyle: '<?= htmlspecialchars($s['card_style'] ?? 'glass') ?>',

    get previewStyles() {
      return `background: var(--color-bg-dark);`;
    },

    preview() {
      // Collect all form values and inject as CSS variables in the preview panel
      const form = document.getElementById('themeForm');
      const data = new FormData(form);
      let css = ':root{';
      const colorKeys = ['color_crimson','color_teal','color_bg_dark','color_bg_card',
        'color_text_primary','color_text_muted','color_border'];
      colorKeys.forEach(k => {
        const txt = form.querySelector(`[name="${k}_text"]`);
        const val = txt ? txt.value : data.get(k);
        if (val) css += `--color-${k.replace('color_','').replace(/_/g,'-')}: ${val};`;
      });
      css += `--color-nav-bg: ${data.get('color_nav_bg') || 'rgba(10,10,10,0.85)'};`;
      css += `--font-heading: '${data.get('font_heading')||'Inter'}', sans-serif;`;
      css += `--font-body: '${data.get('font_body')||'Inter'}', sans-serif;`;
      css += `--font-size-base: ${data.get('font_size_base')||16}px;`;
      css += `--font-weight-heading: ${data.get('font_weight_heading')||700};`;
      css += `--letter-spacing-heading: ${data.get('letter_spacing_heading')||-0.03}em;`;
      css += `--radius: ${data.get('border_radius')||12}px;`;
      css += `--anim-speed: ${data.get('animation_speed')||1};`;
      css += '}';

      let styleEl = document.getElementById('theme-preview-vars');
      if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = 'theme-preview-vars';
        document.head.appendChild(styleEl);
      }
      styleEl.textContent = css;
    },

    syncColorText(input) {
      // Sync text input to adjacent color picker
      const name = input.name.replace('_text', '');
      const colorPicker = document.querySelector(`input[type="color"][name="${name}"]`);
      if (colorPicker && /^#[0-9a-f]{6}$/i.test(input.value)) {
        colorPicker.value = input.value;
      }
      this.preview();
    },

    applyPreset(values) {
      Object.entries(values).forEach(([k, v]) => {
        const colorInput = document.querySelector(`input[type="color"][name="${k}"]`);
        const textInput  = document.querySelector(`input[name="${k}_text"]`);
        if (colorInput) colorInput.value = v;
        if (textInput)  textInput.value  = v;
      });
      this.preview();
    },

    async saveTheme() {
      this.saving = true;
      this.message = '';

      // Sync color text inputs → color picker values
      document.querySelectorAll('[name$="_text"]').forEach(input => {
        const name = input.name.replace('_text', '');
        const picker = document.querySelector(`input[type="color"][name="${name}"]`);
        if (picker) picker.value = input.value;
      });

      const form = document.getElementById('themeForm');
      const formData = new FormData(form);

      try {
        const res = await fetch('/admin/theme/save', { method: 'POST', body: formData });
        const json = await res.json();
        this.messageType = json.ok ? 'success' : 'error';
        this.message = json.message || (json.ok ? 'Theme saved!' : 'Save failed.');
      } catch (e) {
        this.messageType = 'error';
        this.message = 'Network error. Please try again.';
      } finally {
        this.saving = false;
        setTimeout(() => this.message = '', 4000);
      }
    },

    async resetDefaults() {
      if (!confirm('Reset all theme settings to defaults?')) return;
      // Submit with special flag
      const form = document.getElementById('themeForm');
      const fd = new FormData();
      fd.append('csrf_token', form.querySelector('[name="csrf_token"]').value);
      fd.append('_reset', '1');
      const res = await fetch('/admin/theme/save', { method: 'POST', body: fd });
      if (res.ok) window.location.reload();
    },

    init() {
      this.preview();
      // Sync color pickers → text inputs on change
      document.querySelectorAll('input[type="color"]').forEach(picker => {
        picker.addEventListener('input', () => {
          const textInput = document.querySelector(`input[name="${picker.name}_text"]`);
          if (textInput) textInput.value = picker.value;
          this.preview();
        });
      });
    }
  };
}
</script>

<?php $content = ob_get_clean(); ?>
<?php require BASE_PATH . '/app/Views/layouts/admin.php'; ?>
