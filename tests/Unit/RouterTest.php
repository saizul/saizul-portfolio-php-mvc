<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Fixtures\SpyController;
use App\Core\Router;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        SpyController::$calls = [];
        $_POST = [];
        http_response_code(200);

        $this->router = new Router();
        $this->router->get('/', 'Fixtures\SpyController@index');
        $this->router->get('/blog/{slug}', 'Fixtures\SpyController@show');
        $this->router->get('/portfolio/{category}/{slug}', 'Fixtures\SpyController@pair');
        $this->router->get('/sitemap.xml', 'Fixtures\SpyController@index');
        $this->router->post('/items', 'Fixtures\SpyController@store');
        $this->router->put('/items/{id}', 'Fixtures\SpyController@update');
        $this->router->delete('/items/{id}', 'Fixtures\SpyController@destroy');
        $this->router->get('/missing-controller', 'Fixtures\NoSuchController@index');
        $this->router->get('/missing-method', 'Fixtures\SpyController@nope');
    }

    protected function tearDown(): void
    {
        $_POST = [];
    }

    /** Dispatch and return whatever the router echoed (404 page, etc.). */
    private function dispatch(string $uri, string $method = 'GET'): string
    {
        ob_start();
        try {
            $this->router->dispatch($uri, $method);
        } finally {
            $output = (string) ob_get_clean();
        }
        return $output;
    }

    public function testRootRoute(): void
    {
        $this->dispatch('/');
        self::assertSame([['index', []]], SpyController::$calls);
    }

    public function testNamedParameterIsPassedToAction(): void
    {
        $this->dispatch('/blog/hello-world');
        self::assertSame([['show', ['hello-world']]], SpyController::$calls);
    }

    public function testMultipleParametersKeepTheirOrder(): void
    {
        $this->dispatch('/portfolio/ai/chat-bot');
        self::assertSame([['pair', ['ai', 'chat-bot']]], SpyController::$calls);
    }

    /** @return iterable<string, array{string}> */
    public static function equivalentRootUris(): iterable
    {
        yield 'query string'     => ['/blog/post?utm_source=x'];
        yield 'trailing slash'   => ['/blog/post/'];
        yield 'fragment'         => ['/blog/post#comments'];
    }

    #[DataProvider('equivalentRootUris')]
    public function testQueryStringsAndTrailingSlashesAreIgnored(string $uri): void
    {
        $this->dispatch($uri);
        self::assertSame([['show', ['post']]], SpyController::$calls);
    }

    public function testParameterDoesNotSpanSlashes(): void
    {
        $output = $this->dispatch('/blog/a/b');
        self::assertSame([], SpyController::$calls);
        self::assertSame(404, http_response_code());
        self::assertStringContainsString('404', $output);
    }

    public function testDotInRouteIsLiteral(): void
    {
        $this->dispatch('/sitemap.xml');
        self::assertCount(1, SpyController::$calls);

        SpyController::$calls = [];
        $this->dispatch('/sitemapXxml');
        self::assertSame([], SpyController::$calls, 'A "." in a route must not act as a regex wildcard.');
        self::assertSame(404, http_response_code());
    }

    public function testMethodMustMatch(): void
    {
        $this->dispatch('/items', 'GET');
        self::assertSame([], SpyController::$calls);
        self::assertSame(404, http_response_code());

        $this->dispatch('/items', 'POST');
        self::assertSame([['store', []]], SpyController::$calls);
    }

    public function testRequestMethodIsCaseInsensitive(): void
    {
        $this->dispatch('/items', 'post');
        self::assertSame([['store', []]], SpyController::$calls);
    }

    public function testPostCanBeOverriddenToPutAndDelete(): void
    {
        $_POST['_method'] = 'put';
        $this->dispatch('/items/7', 'POST');

        $_POST['_method'] = 'DELETE';
        $this->dispatch('/items/8', 'POST');

        self::assertSame([['update', ['7']], ['destroy', ['8']]], SpyController::$calls);
    }

    public function testOverrideCannotTurnPostIntoGet(): void
    {
        $_POST['_method'] = 'GET';
        $this->dispatch('/items', 'POST');
        self::assertSame([['store', []]], SpyController::$calls);
    }

    public function testNonStringOverrideIsIgnored(): void
    {
        $_POST['_method'] = ['PUT'];
        $this->dispatch('/items', 'POST');
        self::assertSame([['store', []]], SpyController::$calls);
    }

    public function testOverrideOnlyAppliesToPost(): void
    {
        $_POST['_method'] = 'DELETE';
        $this->dispatch('/items/1', 'GET');
        self::assertSame([], SpyController::$calls);
        self::assertSame(404, http_response_code());
    }

    public function testUnknownRouteRenders404(): void
    {
        $output = $this->dispatch('/does-not-exist');
        self::assertSame(404, http_response_code());
        self::assertStringContainsString('Page not found', $output);
    }

    public function testMalformedUriDoesNotCrash(): void
    {
        // parse_url('//') returns false; it must be treated as "/".
        $this->dispatch('//');
        self::assertSame([['index', []]], SpyController::$calls);
    }

    public function testMissingControllerOrMethodGives404(): void
    {
        $errorLog = ini_set('error_log', '/dev/null');
        try {
            $this->dispatch('/missing-controller');
            self::assertSame(404, http_response_code());

            http_response_code(200);
            $this->dispatch('/missing-method');
            self::assertSame(404, http_response_code());
        } finally {
            ini_set('error_log', (string) $errorLog);
        }
        self::assertSame([], SpyController::$calls);
    }

    public function testFirstMatchingRouteWins(): void
    {
        $router = new Router();
        $router->get('/blog/latest', 'Fixtures\SpyController@index');
        $router->get('/blog/{slug}', 'Fixtures\SpyController@show');

        ob_start();
        $router->dispatch('/blog/latest', 'GET');
        ob_end_clean();

        self::assertSame([['index', []]], SpyController::$calls);
    }
}
