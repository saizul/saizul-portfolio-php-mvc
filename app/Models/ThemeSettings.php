<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ThemeSettings extends Model
{
    // Default theme configuration
    private const DEFAULTS = [
        // Colors
        'color_crimson'      => '#c0392b',
        'color_teal'         => '#6ee7b7',
        'color_bg_dark'      => '#0a0a0a',
        'color_bg_card'      => '#111111',
        'color_text_primary' => '#f1f5f9',
        'color_text_muted'   => '#94a3b8',
        'color_border'       => '#1e293b',
        'color_nav_bg'       => 'rgba(10,10,10,0.85)',

        // Typography
        'font_heading'       => 'Inter',
        'font_body'          => 'Inter',
        'font_mono'          => 'JetBrains Mono',
        'font_size_base'     => '16',
        'font_weight_heading'=> '700',
        'letter_spacing_heading' => '-0.03',

        // Animations
        'enable_3d'          => '1',
        'enable_particles'   => '1',
        'enable_smooth_scroll' => '1',
        'animation_speed'    => '1.0',
        'particle_count'     => '2000',

        // Layout
        'nav_style'          => 'floating',   // floating | full-width | minimal
        'footer_style'       => 'detailed',   // detailed | minimal | hidden
        'hero_layout'        => 'center',     // center | left | split
        'card_style'         => 'glass',      // glass | solid | outline
        'border_radius'      => '12',         // px

        // Homepage sections order/visibility
        'homepage_sections'  => '["hero","about","skills","projects","portfolio","blog","contact"]',

        // Custom CSS
        'custom_css'         => '',
    ];

    public function all(): array
    {
        $rows = $this->db()->fetchAll(
            "SELECT `key`, `value` FROM theme_settings",
            []
        );
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        return array_merge(self::DEFAULTS, $settings);
    }

    public function get(string $key): string
    {
        $row = $this->db()->fetch(
            "SELECT `value` FROM theme_settings WHERE `key` = ?",
            [$key]
        );
        return $row ? $row['value'] : (self::DEFAULTS[$key] ?? '');
    }

    public function set(string $key, string $value): void
    {
        $this->db()->execute(
            "INSERT INTO theme_settings (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
            [$key, $value]
        );
    }

    public function saveMany(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->set($key, (string) $value);
        }
    }

    public function toCssVariables(array $settings): string
    {
        $font_size = (int) ($settings['font_size_base'] ?? 16);
        $radius    = (int) ($settings['border_radius'] ?? 12);
        $ls        = (float) ($settings['letter_spacing_heading'] ?? -0.03);
        $speed     = (float) ($settings['animation_speed'] ?? 1.0);

        return ":root{
--color-crimson:{$settings['color_crimson']};
--color-teal:{$settings['color_teal']};
--color-bg-dark:{$settings['color_bg_dark']};
--color-bg-card:{$settings['color_bg_card']};
--color-text-primary:{$settings['color_text_primary']};
--color-text-muted:{$settings['color_text_muted']};
--color-border:{$settings['color_border']};
--color-nav-bg:{$settings['color_nav_bg']};
--font-heading:'{$settings['font_heading']}',sans-serif;
--font-body:'{$settings['font_body']}',sans-serif;
--font-mono:'{$settings['font_mono']}',monospace;
--font-size-base:{$font_size}px;
--font-weight-heading:{$settings['font_weight_heading']};
--letter-spacing-heading:{$ls}em;
--radius:{$radius}px;
--anim-speed:{$speed};
}";
    }

    public function defaults(): array
    {
        return self::DEFAULTS;
    }
}
