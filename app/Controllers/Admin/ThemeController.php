<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\ThemeSettings;

class ThemeController extends Controller
{
    private ThemeSettings $model;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->model = new ThemeSettings();
    }

    public function index(): void
    {
        $settings = $this->model->all();
        $this->view('admin/theme/index', [
            'pageTitle' => 'Theme Settings',
            'settings'  => $settings,
        ]);
    }

    public function save(): void
    {
        Auth::verifyCsrf();

        $fields = [
            // Colors
            'color_crimson', 'color_teal', 'color_bg_dark', 'color_bg_card',
            'color_text_primary', 'color_text_muted', 'color_border', 'color_nav_bg',
            // Typography
            'font_heading', 'font_body', 'font_mono', 'font_size_base',
            'font_weight_heading', 'letter_spacing_heading',
            // Animations
            'enable_3d', 'enable_particles', 'enable_smooth_scroll',
            'animation_speed', 'particle_count',
            // Layout
            'nav_style', 'footer_style', 'hero_layout', 'card_style', 'border_radius',
            // Custom
            'custom_css',
        ];

        $data = [];
        foreach ($fields as $field) {
            $val = $_POST[$field] ?? null;
            if ($val !== null) {
                $data[$field] = $val;
            }
        }

        // Checkboxes: if unchecked they won't appear in POST
        foreach (['enable_3d', 'enable_particles', 'enable_smooth_scroll'] as $toggle) {
            $data[$toggle] = isset($_POST[$toggle]) ? '1' : '0';
        }

        $this->model->saveMany($data);

        // Regenerate the cached CSS file
        $this->_regenerateCssCache();

        $this->json(['ok' => true, 'message' => 'Theme saved successfully.']);
    }

    public function preview(): void
    {
        // Render homepage in preview mode — injects theme CSS inline
        $settings = $this->model->all();
        $css = $this->model->toCssVariables($settings);

        header('Content-Type: text/html; charset=UTF-8');
        // Proxy the homepage with injected variables
        echo '<!DOCTYPE html><html><head>';
        echo '<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<style>' . $css . '</style>';
        echo '<style>body{pointer-events:none !important;}</style>'; // disable interaction in preview
        echo '</head><body>';
        echo '<iframe src="/" style="width:100%;height:100vh;border:none;pointer-events:none;" sandbox="allow-same-origin allow-scripts"></iframe>';
        echo '</body></html>';
        exit;
    }

    public function css(): void
    {
        // Public endpoint: /admin/theme/css — outputs CSS variables
        // Also used by main.php to inject variables server-side
        $settings = $this->model->all();
        $css = $this->model->toCssVariables($settings);

        if (!empty($settings['custom_css'])) {
            $css .= "\n" . $settings['custom_css'];
        }

        header('Content-Type: text/css; charset=UTF-8');
        header('Cache-Control: public, max-age=3600');
        echo $css;
        exit;
    }

    // ----------------------------------------------------------------
    // Private helpers
    // ----------------------------------------------------------------

    private function _regenerateCssCache(): void
    {
        $settings = $this->model->all();
        $css = $this->model->toCssVariables($settings);
        if (!empty($settings['custom_css'])) {
            $css .= "\n" . $settings['custom_css'];
        }

        $cachePath = BASE_PATH . '/storage/cache/theme.css';
        @file_put_contents($cachePath, $css);
    }
}
