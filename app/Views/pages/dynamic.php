<?php
/**
 * dynamic.php — Renders a CMS page built with the Page Builder.
 * $page   = page row from DB
 * $blocks = decoded array of block objects
 */
?>
<main class="dynamic-page">
<?php foreach ($blocks as $block): ?>
    <?= renderBlock($block) ?>
<?php endforeach; ?>
<?php if (empty($blocks)): ?>
<section style="padding:120px 40px;text-align:center;color:#475569;">
    <p>This page has no content yet. <a href="/admin/pages/<?= htmlspecialchars($page['slug']) ?>/edit" style="color:#6ee7b7;">Open Builder →</a></p>
</section>
<?php endif; ?>
</main>

<?php
function renderBlock(array $b): string
{
    $e = fn($s) => htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $id = 'block-' . ($b['id'] ?? uniqid());
    // Callable handles so the helpers below can be called inside heredoc/string interpolation: {$btnHtml(...)}
    $btnHtml     = 'btnHtml';
    $headingHtml = 'headingHtml';
    $captionHtml = 'captionHtml';

    switch ($b['type'] ?? '') {

        case 'hero':
            $align  = $b['align']  ?? 'center';
            $bg     = $b['bgColor'] ?? '';
            $style  = $bg ? "background:$bg;" : '';
            return <<<HTML
<section id="{$id}" class="hero section-hero" style="{$style}">
  <div class="container" style="text-align:{$align}">
    <p class="hero-eyebrow reveal">WELCOME</p>
    <h1 class="display-1 reveal">{$e($b['heading'] ?? '')}</h1>
    <p class="body-lg hero-tagline reveal" style="max-width:560px;margin:0 auto 40px;">{$e($b['subheading'] ?? '')}</p>
    <div class="hero-ctas reveal" style="display:flex;gap:16px;justify-content:{$align};flex-wrap:wrap;">
      {$btnHtml($b['btn1Text'] ?? '', $b['btn1Url'] ?? '#', 'btn btn-primary')}
      {$btnHtml($b['btn2Text'] ?? '', $b['btn2Url'] ?? '#', 'btn btn-outline')}
    </div>
  </div>
</section>
HTML;

        case 'text':
            $tag   = in_array($b['headingSize'] ?? 'h2', ['h1','h2','h3','h4'], true) ? ($b['headingSize'] ?? 'h2') : 'h2';
            $align = $b['align'] ?? 'left';
            $heading = $b['heading'] ?? '';
            $body    = $b['body']    ?? '';
            $bodyMargin = $align === 'center' ? 'margin:0 auto' : '';
            return <<<HTML
<section id="{$id}" class="section-text" style="padding:var(--section-py,80px) 0;">
  <div class="container" style="text-align:{$align}">
    {$headingHtml($tag, $heading, $e)}
    <p class="body-lg reveal" style="max-width:720px;color:var(--grey-300,#94a3b8);{$bodyMargin}">{$e($body)}</p>
  </div>
</section>
HTML;

        case 'image':
            $src     = $b['src']     ?? '';
            $alt     = $b['alt']     ?? '';
            $caption = $b['caption'] ?? '';
            if (!$src) return '';
            return <<<HTML
<section id="{$id}" class="section-image" style="padding:40px 0;">
  <div class="container">
    <figure class="reveal">
      <img src="{$e($src)}" alt="{$e($alt)}" style="width:100%;border-radius:12px;">
      {$captionHtml($caption, $e)}
    </figure>
  </div>
</section>
HTML;

        case 'gallery':
            $cols   = max(1, min(4, (int)($b['cols'] ?? 3)));
            $images = $b['images'] ?? [];
            $gap    = (int)($b['gap'] ?? 12);
            if (empty($images)) return '';
            $items  = implode('', array_map(fn($img) =>
                "<div><img src=\"{$e($img)}\" alt=\"\" style=\"width:100%;border-radius:8px;aspect-ratio:1;object-fit:cover;\"></div>",
                $images
            ));
            return <<<HTML
<section id="{$id}" class="section-gallery" style="padding:40px 0;">
  <div class="container reveal">
    <div style="display:grid;grid-template-columns:repeat({$cols},1fr);gap:{$gap}px;">{$items}</div>
  </div>
</section>
HTML;

        case 'cta':
            $align = $b['align']   ?? 'center';
            $bg    = $b['bgColor'] ?? '';
            $style = $bg ? "background:$bg;" : '';
            $bodyMargin = $align === 'center' ? 'margin-left:auto;margin-right:auto' : '';
            return <<<HTML
<section id="{$id}" class="section-cta" style="{$style}padding:var(--section-py,80px) 0;">
  <div class="container reveal" style="text-align:{$align}">
    <h2 class="heading-1" style="margin-bottom:16px;">{$e($b['heading'] ?? '')}</h2>
    <p class="body-lg" style="color:#94a3b8;margin-bottom:32px;max-width:480px;{$bodyMargin}">{$e($b['body'] ?? '')}</p>
    {$btnHtml($b['btnText'] ?? 'Get in Touch', $b['btnUrl'] ?? '/contact', 'btn btn-primary')}
  </div>
</section>
HTML;

        case 'cards':
            $cols  = max(1, min(4, (int)($b['cols'] ?? 3)));
            $items = $b['items'] ?? [];
            $cards = implode('', array_map(fn($item) =>
                "<div class=\"card reveal\"><div style=\"font-size:1.75rem;margin-bottom:12px;\">{$e($item['icon'] ?? '')}</div>"
                ."<h3 class=\"heading-2\" style=\"margin-bottom:8px;\">{$e($item['title'] ?? '')}</h3>"
                ."<p style=\"color:#94a3b8;font-size:0.9375rem;\">{$e($item['body'] ?? '')}</p></div>",
                $items
            ));
            return <<<HTML
<section id="{$id}" class="section-cards" style="padding:var(--section-py,80px) 0;">
  <div class="container">
    {$headingHtml('h2', $b['heading'] ?? '', $e, 'text-align:center;margin-bottom:48px;')}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:24px;">{$cards}</div>
  </div>
</section>
HTML;

        case 'skills':
            $items = $b['items'] ?? [];
            $bars  = implode('', array_map(fn($s) =>
                "<div class=\"reveal\" style=\"margin-bottom:16px;\">"
                ."<div style=\"display:flex;justify-content:space-between;font-size:0.875rem;margin-bottom:6px;\"><span>{$e($s['name']??'')}</span><span style=\"color:#6ee7b7;\">{$e($s['pct']??0)}%</span></div>"
                ."<div class=\"skill-bar\"><div class=\"skill-bar-fill\" data-pct=\"{$e($s['pct']??0)}\"></div></div></div>",
                $items
            ));
            return <<<HTML
<section id="{$id}" class="section-skills" style="padding:var(--section-py,80px) 0;">
  <div class="container" style="max-width:720px;">
    {$headingHtml('h2', $b['heading'] ?? '', $e, 'margin-bottom:40px;')}
    {$bars}
  </div>
</section>
HTML;

        case 'timeline':
            $items = $b['items'] ?? [];
            $entries = implode('', array_map(fn($item) =>
                "<div class=\"timeline-item reveal\">"
                ."<div class=\"timeline-date\">{$e($item['date']??'')}</div>"
                ."<h3 class=\"timeline-role\">{$e($item['role']??'')}".(!empty($item['company']) ? ' — '.$e($item['company']) : '')."</h3>"
                ."<p class=\"timeline-desc\">{$e($item['desc']??'')}</p></div>",
                $items
            ));
            return <<<HTML
<section id="{$id}" class="section-timeline" style="padding:var(--section-py,80px) 0;">
  <div class="container" style="max-width:760px;">
    {$headingHtml('h2', $b['heading'] ?? '', $e, 'margin-bottom:48px;')}
    <div class="timeline">{$entries}</div>
  </div>
</section>
HTML;

        case 'divider':
            $sp = match($b['spacing'] ?? 'md') { 'sm' => '16px', 'lg' => '64px', default => '40px' };
            return "<div id=\"{$id}\" style=\"padding:{$sp} 0;\"><div class=\"container\"><hr style=\"border:none;border-top:1px solid rgba(255,255,255,0.08);\"></div></div>";

        case 'two-col':
            $lw = max(20, min(80, (int)($b['leftWidth'] ?? 50)));
            $rw = 100 - $lw;
            return <<<HTML
<section id="{$id}" class="section-twocol" style="padding:var(--section-py,80px) 0;">
  <div class="container">
    <div style="display:grid;grid-template-columns:{$lw}fr {$rw}fr;gap:48px;align-items:start;" class="reveal">
      <div><p style="color:#94a3b8;line-height:1.8;">{$e($b['left'] ?? '')}</p></div>
      <div><p style="color:#94a3b8;line-height:1.8;">{$e($b['right'] ?? '')}</p></div>
    </div>
  </div>
</section>
HTML;

        case 'video':
            $src = $b['src'] ?? '';
            if (!$src) return '';
            // Convert YouTube watch URL to embed
            $src = preg_replace('/youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)/', 'youtube.com/embed/$1', $src);
            $src = preg_replace('/youtu\.be\/([a-zA-Z0-9_\-]+)/', 'youtube.com/embed/$1', $src);
            $src = preg_replace('/vimeo\.com\/(\d+)/', 'player.vimeo.com/video/$1', $src);
            return <<<HTML
<section id="{$id}" class="section-video" style="padding:40px 0;">
  <div class="container reveal" style="max-width:800px;">
    <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px;">
      <iframe src="{$e($src)}" style="position:absolute;inset:0;width:100%;height:100%;border:none;" allow="autoplay;fullscreen" loading="lazy"></iframe>
    </div>
    {$captionHtml($b['caption'] ?? '', $e)}
  </div>
</section>
HTML;

        case 'form':
            // Renders the contact form (action posts to /contact)
            return <<<HTML
<section id="{$id}" class="section-form" style="padding:var(--section-py,80px) 0;">
  <div class="container reveal" style="max-width:600px;">
    {$headingHtml('h2', $b['heading'] ?? 'Send a Message', $e, 'margin-bottom:32px;')}
    <form method="POST" action="/contact">
      <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::csrfToken() ?>">
      <div style="display:grid;gap:16px;">
        <input type="text"  name="name"    class="form-input" placeholder="Your Name" required>
        <input type="email" name="email"   class="form-input" placeholder="Your Email" required>
        <input type="text"  name="subject" class="form-input" placeholder="Subject">
        <textarea name="message" class="form-textarea" rows="5" placeholder="Your Message" required></textarea>
        <button type="submit" class="btn btn-primary">{$e($b['btnText'] ?? 'Send Message')}</button>
      </div>
    </form>
  </div>
</section>
HTML;

        default:
            return '';
    }
}

/* ── Inline helpers used in closure context ── */
function btnHtml(string $text, string $url, string $cls): string {
    if (!$text) return '';
    $et = htmlspecialchars($text, ENT_QUOTES); $eu = htmlspecialchars($url, ENT_QUOTES);
    return "<a href=\"{$eu}\" class=\"{$cls}\">{$et}</a>";
}
function headingHtml(string $tag, string $text, callable $e, string $style = ''): string {
    if (!$text) return '';
    return "<{$tag} class=\"heading-1 reveal\" style=\"{$style}\">{$e($text)}</{$tag}>";
}
function captionHtml(string $caption, callable $e): string {
    if (!$caption) return '';
    return "<figcaption style=\"text-align:center;font-size:0.8125rem;color:#64748b;margin-top:10px;\">{$e($caption)}</figcaption>";
}
?>
