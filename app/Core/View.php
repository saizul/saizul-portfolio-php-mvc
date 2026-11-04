<?php

declare(strict_types=1);

namespace App\Core;

/**
 * View — template renderer.
 * Resolves dot-notation view names to PHP files under app/Views/.
 */
class View
{
    /**
     * Render a view wrapped in a layout.
     *
     * @param string $view    e.g. 'pages.home', 'admin.dashboard'
     * @param array  $data    Variables to extract into the view scope
     * @param string $layout  'main' or 'admin'
     */
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewPath   = self::resolve($view);
        $layoutPath = BASE_PATH . "/app/Views/layouts/{$layout}.php";

        if (!file_exists($viewPath)) {
            error_log("[View] View not found: {$viewPath}");
            http_response_code(500);
            die("View [{$view}] not found.");
        }

        if (!file_exists($layoutPath)) {
            error_log("[View] Layout not found: {$layoutPath}");
            http_response_code(500);
            die("Layout [{$layout}] not found.");
        }

        // Capture view content into $content variable
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Render layout with $content available
        require $layoutPath;
    }

    /**
     * Render a view without any layout (useful for AJAX partials).
     */
    public static function renderPartial(string $view, array $data = []): void
    {
        $viewPath = self::resolve($view);

        if (!file_exists($viewPath)) {
            error_log("[View] Partial not found: {$viewPath}");
            return;
        }

        extract($data, EXTR_SKIP);
        require $viewPath;
    }

    /**
     * Render a view to string (useful for email bodies, caching).
     */
    public static function renderToString(string $view, array $data = []): string
    {
        ob_start();
        self::renderPartial($view, $data);
        return ob_get_clean();
    }

    // ------------------------------------------------------------------
    // Internal
    // ------------------------------------------------------------------

    private static function resolve(string $view): string
    {
        // Convert dot notation to directory separators
        $path = str_replace('.', DIRECTORY_SEPARATOR, $view);
        return BASE_PATH . "/app/Views/{$path}.php";
    }
}
