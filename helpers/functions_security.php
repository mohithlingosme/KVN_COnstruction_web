<?php

declare(strict_types=1);

/**
 * Central, context-aware escaping helpers for templates.
 *
 * Note: Kept separate from helpers/functions.php to minimize merge conflicts.
 * If you prefer to add these into helpers/functions.php, remove this file and
 * copy functions there.
 */

if (!function_exists('escapeAttr')) {
    function escapeAttr(mixed $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('escapeCssClass')) {
    function escapeCssClass(mixed $value): string
    {
        $s = (string)($value ?? '');
        // Prevent breaking out of class attributes; allow only safe chars.
        $s = preg_replace('/[^a-zA-Z0-9_\s-]/', '', $s) ?? '';
        return escapeAttr(trim($s));
    }
}

if (!function_exists('escapeUrl')) {
    function escapeUrl(mixed $value): string
    {
        $s = (string)($value ?? '');

        // If it's a simple integer, casting is enough.
        if (is_numeric($s) && ctype_digit((string)(int)$s)) {
            return (string)(int)$s;
        }

        // Encode URL segments/fragments safely.
        // For query param values, rawurlencode is correct.
        return rawurlencode($s);
    }
}

