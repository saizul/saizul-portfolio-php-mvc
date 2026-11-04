<?php

declare(strict_types=1);

/**
 * Sanitization helpers.
 */

function sanitizeString(mixed $value): string
{
    return htmlspecialchars(strip_tags(trim((string) $value)), ENT_QUOTES, 'UTF-8');
}

function sanitizeEmail(mixed $value): string|false
{
    return filter_var(trim((string) $value), FILTER_SANITIZE_EMAIL);
}

function sanitizeInt(mixed $value): int
{
    return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
}

function sanitizeUrl(mixed $value): string|false
{
    return filter_var(trim((string) $value), FILTER_SANITIZE_URL);
}

/**
 * Sanitize an array of POST fields.
 * $rules: ['field' => 'string|email|int|url']
 */
function sanitizeInput(array $input, array $rules): array
{
    $clean = [];
    foreach ($rules as $field => $type) {
        $value = $input[$field] ?? '';
        $clean[$field] = match ($type) {
            'email'  => sanitizeEmail($value),
            'int'    => sanitizeInt($value),
            'url'    => sanitizeUrl($value),
            default  => sanitizeString($value),
        };
    }
    return $clean;
}

/**
 * Validate required fields are non-empty.
 * Returns array of error messages, empty if all pass.
 */
function validateRequired(array $data, array $fields): array
{
    $errors = [];
    foreach ($fields as $field) {
        if (empty(trim((string) ($data[$field] ?? '')))) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    return $errors;
}

function isValidEmail(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}
