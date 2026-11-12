<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\ContactController;
use App\Core\Validator;
use PHPUnit\Framework\TestCase;

/**
 * The contact form's validation rules and mail-header hardening.
 */
final class ContactFormTest extends TestCase
{
    /** @return array<string, string> */
    private function valid(): array
    {
        return [
            'name'    => 'Ada Lovelace',
            'email'   => 'ada@example.com',
            'subject' => 'Technical Consulting',
            'message' => 'I would like to talk about an analytical engine project.',
        ];
    }

    public function testValidSubmissionPasses(): void
    {
        $v = Validator::make($this->valid(), ContactController::rules());
        self::assertTrue($v->passes(), json_encode($v->errors()) ?: '');
    }

    public function testEverySubjectOptionIsAccepted(): void
    {
        foreach (ContactController::SUBJECTS as $subject) {
            $data = ['subject' => $subject] + $this->valid();
            self::assertTrue(Validator::make($data, ContactController::rules())->passes(), $subject);
        }
    }

    public function testSubjectIsOptional(): void
    {
        $data = ['subject' => ''] + $this->valid();
        self::assertTrue(Validator::make($data, ContactController::rules())->passes());
    }

    public function testSubjectOutsideTheListIsRejected(): void
    {
        $data = ['subject' => "Hi\r\nBcc: list@example.com"] + $this->valid();
        $v = Validator::make($data, ContactController::rules());
        self::assertArrayHasKey('subject', $v->errors());
    }

    public function testNameWithLineBreakIsRejected(): void
    {
        $data = ['name' => "Ada\nLovelace"] + $this->valid();
        self::assertArrayHasKey('name', Validator::make($data, ContactController::rules())->errors());
    }

    public function testLengthLimitsMatchDatabaseColumns(): void
    {
        $data = ['name' => str_repeat('a', 151)] + $this->valid();
        self::assertArrayHasKey('name', Validator::make($data, ContactController::rules())->errors());

        $data = ['message' => 'Too short'] + $this->valid();
        self::assertArrayHasKey('message', Validator::make($data, ContactController::rules())->errors());

        $data = ['message' => str_repeat('a', 5001)] + $this->valid();
        self::assertArrayHasKey('message', Validator::make($data, ContactController::rules())->errors());
    }

    public function testRequiredFields(): void
    {
        $v = Validator::make([], ContactController::rules());
        self::assertSame(['name', 'email', 'message'], array_keys($v->errors()));
    }

    public function testHeaderSafeStripsLineBreaks(): void
    {
        self::assertSame(
            'New Contact: x  Bcc: a@example.com',
            ContactController::headerSafe("New Contact: x\r\nBcc: a@example.com\0")
        );
    }

    public function testRateLimitSettingsAreSane(): void
    {
        self::assertGreaterThan(0, ContactController::RATE_LIMIT_MAX);
        self::assertGreaterThanOrEqual(60, ContactController::RATE_LIMIT_WINDOW);
    }
}
