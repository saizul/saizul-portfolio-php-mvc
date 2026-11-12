<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testPassingDataIsReturnedTrimmed(): void
    {
        $v = Validator::make(
            ['name' => '  Ada  ', 'email' => 'ada@example.com ', 'extra' => 'ignored'],
            ['name' => 'required|max:10', 'email' => 'required|email']
        );

        self::assertTrue($v->passes());
        self::assertFalse($v->fails());
        self::assertSame([], $v->errors());
        self::assertSame(['name' => 'Ada', 'email' => 'ada@example.com'], $v->validated());
    }

    public function testRequiredRejectsMissingAndBlankValues(): void
    {
        $v = Validator::make(['first_name' => "  \t "], [
            'first_name' => 'required',
            'email'      => 'required|email',
        ]);

        self::assertTrue($v->fails());
        self::assertSame([
            'first_name' => 'First name is required.',
            'email'      => 'Email is required.',
        ], $v->errors());
        self::assertSame([], $v->validated());
    }

    public function testOptionalBlankFieldSkipsOtherRules(): void
    {
        $v = Validator::make(['subject' => ''], ['subject' => 'in:a,b|min:5']);

        self::assertTrue($v->passes());
        self::assertSame(['subject' => ''], $v->validated());
    }

    public function testEmail(): void
    {
        self::assertTrue(Validator::make(['e' => 'a.b+tag@example.co.uk'], ['e' => 'email'])->passes());

        $v = Validator::make(['e' => 'not-an-email'], ['e' => 'email']);
        self::assertSame(['e' => 'Please provide a valid email address.'], $v->errors());
    }

    public function testEmailRejectsHeaderInjection(): void
    {
        $v = Validator::make(['e' => "a@example.com\r\nBcc: victim@example.com"], ['e' => 'email']);
        self::assertTrue($v->fails());
    }

    public function testMinAndMaxCountCharactersNotBytes(): void
    {
        // 5 multibyte characters, 15 bytes.
        $word = 'ああああア';

        self::assertTrue(Validator::make(['w' => $word], ['w' => 'min:5|max:5'])->passes());

        $tooLong = Validator::make(['w' => $word], ['w' => 'max:4']);
        self::assertSame(['w' => 'W may not be longer than 4 characters.'], $tooLong->errors());

        $tooShort = Validator::make(['w' => $word], ['w' => 'min:6']);
        self::assertSame(['w' => 'W must be at least 6 characters.'], $tooShort->errors());
    }

    public function testIn(): void
    {
        $rules = ['topic' => 'in:Speaking / Writing,Something else'];

        self::assertTrue(Validator::make(['topic' => 'Speaking / Writing'], $rules)->passes());

        $v = Validator::make(['topic' => 'Viagra'], $rules);
        self::assertSame(['topic' => 'Please choose a valid topic.'], $v->errors());
    }

    public function testSingleLine(): void
    {
        $rules = ['name' => 'single_line'];

        self::assertTrue(Validator::make(['name' => 'Ada Lovelace'], $rules)->passes());
        self::assertTrue(Validator::make(['name' => "Ada\nBcc: x@example.com"], $rules)->fails());
        self::assertTrue(Validator::make(['name' => "Ada\rLovelace"], $rules)->fails());
        self::assertTrue(Validator::make(['name' => "Ad\0a"], $rules)->fails());
    }

    public function testOnlyFirstErrorPerFieldIsReported(): void
    {
        $v = Validator::make(['email' => str_repeat('x', 300)], ['email' => 'required|email|max:150']);
        self::assertSame(['email' => 'Please provide a valid email address.'], $v->errors());
    }

    public function testArrayInputIsRejected(): void
    {
        $v = Validator::make(['name' => ['a', 'b']], ['name' => 'required']);
        self::assertSame(['name' => 'Name is invalid.'], $v->errors());
    }

    public function testRulesMayBeGivenAsArray(): void
    {
        $v = Validator::make(['n' => 'abc'], ['n' => ['required', 'max:2']]);
        self::assertSame(['n' => 'N may not be longer than 2 characters.'], $v->errors());
    }

    public function testUnknownRuleThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Validator::make(['n' => 'x'], ['n' => 'uppercase']);
    }

    public function testNumericRuleWithoutParameterThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Validator::make(['n' => 'x'], ['n' => 'max']);
    }
}
