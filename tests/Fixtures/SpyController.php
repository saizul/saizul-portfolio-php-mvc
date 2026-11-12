<?php

declare(strict_types=1);

namespace App\Controllers\Fixtures;

/**
 * Records which action the Router called and with which parameters.
 */
final class SpyController
{
    /** @var list<array{string, list<string>}> */
    public static array $calls = [];

    public function index(): void
    {
        self::$calls[] = ['index', []];
    }

    public function show(string $slug): void
    {
        self::$calls[] = ['show', [$slug]];
    }

    public function pair(string $category, string $slug): void
    {
        self::$calls[] = ['pair', [$category, $slug]];
    }

    public function update(string $id): void
    {
        self::$calls[] = ['update', [$id]];
    }

    public function destroy(string $id): void
    {
        self::$calls[] = ['destroy', [$id]];
    }

    public function store(): void
    {
        self::$calls[] = ['store', []];
    }
}
