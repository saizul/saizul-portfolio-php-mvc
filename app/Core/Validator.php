<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Validator — small rule-based input validator.
 *
 * Usage:
 *   $v = Validator::make($_POST, [
 *       'name'    => 'required|max:150|single_line',
 *       'email'   => 'required|email|max:150',
 *       'subject' => ['in:' . implode(',', $topics)],
 *       'message' => 'required|min:10|max:5000',
 *   ]);
 *   if ($v->fails()) { $errors = $v->errors(); }
 *   $clean = $v->validated();
 *
 * Supported rules:
 *   required      value must be present and not blank after trimming
 *   email         valid email address (FILTER_VALIDATE_EMAIL)
 *   min:N         at least N characters (multibyte aware)
 *   max:N         at most N characters (multibyte aware)
 *   in:a,b,c      value must be one of the listed options
 *   single_line   no CR, LF or NUL characters (blocks mail header injection)
 *
 * Fields without "required" are optional: when blank, the remaining rules
 * are skipped and the field validates as an empty string.
 * Only the first failing rule per field is reported.
 */
final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    /** @var array<string, string> */
    private array $validated = [];

    /**
     * @param array<string, mixed>                $data
     * @param array<string, string|list<string>>  $rules
     */
    private function __construct(private array $data, private array $rules)
    {
        $this->run();
    }

    /**
     * @param array<string, mixed>                $data
     * @param array<string, string|list<string>>  $rules
     */
    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    /** @return array<string, string> field => first error message */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Trimmed values for every field that has rules (valid fields only).
     *
     * @return array<string, string>
     */
    public function validated(): array
    {
        return $this->validated;
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    private function run(): void
    {
        foreach ($this->rules as $field => $fieldRules) {
            $rules = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);
            $rules = array_values(array_filter(array_map('trim', $rules), fn(string $r) => $r !== ''));

            $raw = $this->data[$field] ?? '';
            // Arrays (e.g. name[]=x) are never valid for scalar text fields.
            if (is_array($raw) || is_object($raw)) {
                $this->errors[$field] = $this->label($field) . ' is invalid.';
                continue;
            }

            $value    = trim((string) $raw);
            $required = in_array('required', $rules, true);

            if ($value === '') {
                if ($required) {
                    $this->errors[$field] = $this->label($field) . ' is required.';
                } else {
                    $this->validated[$field] = '';
                }
                continue;
            }

            $error = null;
            foreach ($rules as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $error = $this->check($field, $value, $name, $param);
                if ($error !== null) {
                    break;
                }
            }

            if ($error !== null) {
                $this->errors[$field] = $error;
            } else {
                $this->validated[$field] = $value;
            }
        }
    }

    private function check(string $field, string $value, string $rule, ?string $param): ?string
    {
        $label = $this->label($field);

        return match ($rule) {
            'required'    => null, // handled before the rule loop
            'email'       => filter_var($value, FILTER_VALIDATE_EMAIL) !== false
                ? null
                : 'Please provide a valid email address.',
            'min'         => mb_strlen($value) >= $this->intParam($rule, $param)
                ? null
                : "{$label} must be at least {$this->intParam($rule, $param)} characters.",
            'max'         => mb_strlen($value) <= $this->intParam($rule, $param)
                ? null
                : "{$label} may not be longer than {$this->intParam($rule, $param)} characters.",
            'in'          => in_array($value, explode(',', (string) $param), true)
                ? null
                : "Please choose a valid {$this->lower($label)}.",
            'single_line' => preg_match('/[\r\n\0]/', $value) === 1
                ? "{$label} must be a single line."
                : null,
            default       => throw new \InvalidArgumentException("Unknown validation rule: {$rule}"),
        };
    }

    private function intParam(string $rule, ?string $param): int
    {
        if ($param === null || !ctype_digit($param)) {
            throw new \InvalidArgumentException("Rule '{$rule}' needs a numeric parameter, e.g. {$rule}:10");
        }
        return (int) $param;
    }

    private function label(string $field): string
    {
        return ucfirst(str_replace('_', ' ', $field));
    }

    private function lower(string $label): string
    {
        return mb_strtolower($label);
    }
}
