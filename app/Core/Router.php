<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Router — front-controller route dispatcher.
 *
 * Usage in config/routes.php:
 *   $router->get('/', 'HomeController@index');
 *   $router->post('/contact', 'ContactController@send');
 *   $router->get('/blog/{slug}', 'BlogController@single');
 */
class Router
{
    private array $routes = [];

    // ------------------------------------------------------------------
    // Route registration
    // ------------------------------------------------------------------

    public function get(string $uri, string $handler): void
    {
        $this->add('GET', $uri, $handler);
    }

    public function post(string $uri, string $handler): void
    {
        $this->add('POST', $uri, $handler);
    }

    public function put(string $uri, string $handler): void
    {
        $this->add('PUT', $uri, $handler);
    }

    public function delete(string $uri, string $handler): void
    {
        $this->add('DELETE', $uri, $handler);
    }

    private function add(string $method, string $uri, string $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'uri'     => $uri,
            'handler' => $handler,
            'pattern' => $this->compilePattern($uri),
        ];
    }

    // ------------------------------------------------------------------
    // Dispatch
    // ------------------------------------------------------------------

    public function dispatch(string $requestUri, string $requestMethod): void
    {
        // Strip query string
        // (parse_url() returns false/null for malformed URIs such as "//")
        $path = parse_url($requestUri, PHP_URL_PATH);
        $path = '/' . trim(is_string($path) ? $path : '', '/');

        // Support method override via POST _method field (PUT/DELETE only,
        // so a form can never be "overridden" into a GET)
        $requestMethod = strtoupper($requestMethod);
        if ($requestMethod === 'POST' && isset($_POST['_method']) && is_string($_POST['_method'])) {
            $override = strtoupper($_POST['_method']);
            if (in_array($override, ['PUT', 'DELETE'], true)) {
                $requestMethod = $override;
            }
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                // Remove numeric keys from matches
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->callHandler($route['handler'], array_values($params));
                return;
            }
        }

        // No route matched
        $this->notFound();
    }

    // ------------------------------------------------------------------
    // Handler resolution
    // ------------------------------------------------------------------

    private function callHandler(string $handler, array $params = []): void
    {
        [$class, $method] = explode('@', $handler);

        // Support Admin\ sub-namespace
        if (str_contains($class, '\\')) {
            $fqn = 'App\\Controllers\\' . str_replace('/', '\\', $class);
        } else {
            $fqn = 'App\\Controllers\\' . $class;
        }

        if (!class_exists($fqn)) {
            error_log("[Router] Controller not found: {$fqn}");
            $this->notFound();
            return;
        }

        $controller = new $fqn();

        if (!method_exists($controller, $method)) {
            error_log("[Router] Method not found: {$fqn}::{$method}");
            $this->notFound();
            return;
        }

        $controller->$method(...$params);
    }

    // ------------------------------------------------------------------
    // Pattern compiler: /blog/{slug} → named capture group regex
    // ------------------------------------------------------------------

    private function compilePattern(string $uri): string
    {
        // Quote the literal parts so "." in /sitemap.xml matches only a dot.
        $parts   = preg_split('/(\{[a-zA-Z_]+\})/', $uri, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
        $pattern = '';
        foreach ($parts as $part) {
            if (preg_match('/^\{([a-zA-Z_]+)\}$/', $part, $m)) {
                $pattern .= '(?P<' . $m[1] . '>[^/]+)';
            } else {
                $pattern .= preg_quote($part, '#');
            }
        }
        return '#^' . $pattern . '$#';
    }

    // ------------------------------------------------------------------
    // 404
    // ------------------------------------------------------------------

    private function notFound(): void
    {
        http_response_code(404);
        $view = BASE_PATH . '/app/Views/errors/404.php';
        if (file_exists($view)) {
            require $view;
        } else {
            echo '<h1>404 — Page Not Found</h1>';
        }
    }
}
