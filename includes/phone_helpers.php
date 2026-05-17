<?php

declare(strict_types=1);

/**
 * Normalize Philippine mobile numbers to E.164-style +639XXXXXXXXX.
 * Accepts 09062668190, 9062668190, +639062668190, etc.
 */
function normalize_ph_mobile(?string $input): ?string
{
    if ($input === null || trim($input) === '') {
        return null;
    }

    $digits = preg_replace('/\D/', '', $input);
    if ($digits === '') {
        return null;
    }

    if (str_starts_with($digits, '63')) {
        $digits = substr($digits, 2);
    }

    if (strlen($digits) === 11 && $digits[0] === '0') {
        $digits = substr($digits, 1);
    }

    if (strlen($digits) === 10 && $digits[0] === '9') {
        return '+63' . $digits;
    }

    return null;
}

/**
 * Format stored mobile for display in +63-prefixed inputs (10 digits, no leading 0).
 */
function format_ph_mobile_input(?string $stored): string
{
    $normalized = normalize_ph_mobile($stored);
    if ($normalized === null) {
        return '';
    }

    return substr($normalized, 3);
}

/**
 * @throws InvalidArgumentException when input is non-empty but invalid
 */
function require_valid_ph_mobile(?string $input, bool $required = false): ?string
{
    $normalized = normalize_ph_mobile($input);

    if ($input !== null && trim((string) $input) !== '' && $normalized === null) {
        throw new InvalidArgumentException(
            'Enter a valid PH mobile number (10 digits starting with 9, without the leading 0).'
        );
    }

    if ($required && $normalized === null) {
        throw new InvalidArgumentException(
            'Enter a valid PH mobile number (10 digits starting with 9, without the leading 0).'
        );
    }

    return $normalized;
}
