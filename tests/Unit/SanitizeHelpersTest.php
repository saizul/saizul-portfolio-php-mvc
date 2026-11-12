<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class SanitizeHelpersTest extends TestCase
{
    public function testSanitizeStringStripsTagsAndEscapes(): void
    {
        self::assertSame('alert(1) &amp; &quot;hi&quot;', sanitizeString(' <script>alert(1)</script> & "hi" '));
    }

    public function testSanitizeInputAppliesRulesPerField(): void
    {
        $clean = sanitizeInput(
            ['name' => '<b>Ada</b>', 'age' => '3x6', 'email' => ' a(b)@example.com '],
            ['name' => 'string', 'age' => 'int', 'email' => 'email', 'missing' => 'string']
        );

        self::assertSame(['name' => 'Ada', 'age' => 36, 'email' => 'ab@example.com', 'missing' => ''], $clean);
    }

    public function testValidateRequired(): void
    {
        self::assertSame(
            ['first_name' => 'First name is required.'],
            validateRequired(['first_name' => '  ', 'email' => 'x'], ['first_name', 'email'])
        );
    }

    public function testEscapeHelper(): void
    {
        self::assertSame('&lt;a href=&#039;x&#039;&gt;', e("<a href='x'>"));
    }

    public function testSlugify(): void
    {
        self::assertSame('hello-world-2026', slugify('  Hello, World! 2026 '));
    }
}
