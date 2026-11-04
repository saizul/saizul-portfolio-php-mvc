<?php $pageTitle = 'Settings'; ?>
<div class="max-w-3xl space-y-6">

  <div>
    <h2 class="text-lg font-semibold">Site Settings</h2>
    <p class="text-xs text-gray-500 mt-0.5">Global configuration for your portfolio</p>
  </div>

  <form method="POST" action="<?= url('/admin/settings/save') ?>" class="space-y-6">
    <?= \App\Core\Auth::csrfField() ?>

    <!-- General -->
    <div class="bg-gray-900 border border-white/5 rounded-2xl p-6 space-y-5">
      <div class="flex items-center gap-2 mb-1">
        <div class="w-2 h-2 rounded-full bg-blue-400"></div>
        <h3 class="font-semibold text-sm">General</h3>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs text-gray-400 mb-2">Site Name</label>
          <input type="text" name="site_name"
                 value="<?= e($general['site_name'] ?? 'Saizul Amin') ?>"
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-sm
                        focus:outline-none focus:border-white/30 text-white">
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-2">Site Tagline</label>
          <input type="text" name="site_tagline"
                 value="<?= e($general['site_tagline'] ?? '') ?>"
                 class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-sm
                        focus:outline-none focus:border-white/30 text-white"
                 placeholder="AI Developer & Builder">
        </div>
      </div>

      <div>
        <label class="block text-xs text-gray-400 mb-2">Contact Email</label>
        <input type="email" name="contact_email"
               value="<?= e($general['contact_email'] ?? '') ?>"
               class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-sm
                      focus:outline-none focus:border-white/30 text-white"
               placeholder="your@email.com">
      </div>
    </div>

    <!-- Social links -->
    <div class="bg-gray-900 border border-white/5 rounded-2xl p-6 space-y-5">
      <div class="flex items-center gap-2 mb-1">
        <div class="w-2 h-2 rounded-full bg-purple-400"></div>
        <h3 class="font-semibold text-sm">Social Links</h3>
      </div>

      <?php
      $socials = [
          'social_github'   => ['GitHub',   'https://github.com/username',   'M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504...'],
          'social_linkedin' => ['LinkedIn',  'https://linkedin.com/in/username', ''],
          'social_twitter'  => ['Twitter/X', 'https://twitter.com/username',  ''],
      ];
      foreach ($socials as $key => [$label, $placeholder, $icon]):
      ?>
      <div>
        <label class="block text-xs text-gray-400 mb-2"><?= $label ?></label>
        <input type="url" name="<?= $key ?>"
               value="<?= e($social[$key] ?? '') ?>"
               class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-sm
                      focus:outline-none focus:border-white/30 text-white"
               placeholder="<?= $placeholder ?>">
      </div>
      <?php endforeach; ?>
    </div>

    <!-- SEO defaults -->
    <div class="bg-gray-900 border border-white/5 rounded-2xl p-6 space-y-5">
      <div class="flex items-center gap-2 mb-1">
        <div class="w-2 h-2 rounded-full bg-green-400"></div>
        <h3 class="font-semibold text-sm">Default SEO</h3>
      </div>

      <div>
        <label class="block text-xs text-gray-400 mb-2">Default Meta Title</label>
        <input type="text" name="seo_default_title"
               value="<?= e($seo['seo_default_title'] ?? '') ?>"
               class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-sm
                      focus:outline-none focus:border-white/30 text-white"
               placeholder="Saizul Amin — AI Developer & Builder">
      </div>

      <div>
        <label class="block text-xs text-gray-400 mb-2">Default Meta Description</label>
        <textarea name="seo_default_description" rows="3"
                  class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-sm
                         focus:outline-none focus:border-white/30 text-gray-300 resize-none"
                  placeholder="Personal portfolio of Saizul Amin…"><?= e($seo['seo_default_description'] ?? '') ?></textarea>
      </div>

      <div>
        <label class="block text-xs text-gray-400 mb-2">Default OG Image</label>
        <div class="flex gap-2">
          <input type="text" name="seo_og_image"
                 value="<?= e($seo['seo_og_image'] ?? '') ?>"
                 class="flex-1 bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-sm
                        focus:outline-none focus:border-white/30 text-white"
                 placeholder="/uploads/og-default.jpg">
          <a href="<?= url('/admin/media') ?>" target="_blank"
             class="px-4 py-3 bg-gray-800 border border-white/10 rounded-xl text-sm text-gray-400 hover:text-white hover:border-white/25 transition whitespace-nowrap">
            Browse
          </a>
        </div>
        <p class="text-xs text-gray-600 mt-1">Used when no page-specific OG image is set. Recommended: 1200×630px</p>
      </div>
    </div>

    <!-- Robots.txt -->
    <div class="bg-gray-900 border border-white/5 rounded-2xl p-6 space-y-4">
      <div class="flex items-center gap-2 mb-1">
        <div class="w-2 h-2 rounded-full bg-orange-400"></div>
        <h3 class="font-semibold text-sm">robots.txt Rules</h3>
      </div>
      <textarea name="robots_rules" rows="5"
                class="w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-sm font-mono
                       focus:outline-none focus:border-white/30 text-gray-300 resize-none"
                placeholder="User-agent: *&#10;Allow: /&#10;Disallow: /admin/"><?= e($general['robots_rules'] ?? "User-agent: *\nAllow: /\nDisallow: /admin/") ?></textarea>
    </div>

    <!-- Save -->
    <div class="flex items-center gap-3 pb-6">
      <button type="submit"
              class="bg-white text-black px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
        Save Settings
      </button>
    </div>
  </form>
</div>
