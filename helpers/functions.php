<?php

declare(strict_types=1);

function asset(string $path): string
{
    // Centralized asset helper for public web root
    return '/KVN_Construction/public/' . ltrim($path, '/');
}

function base_url_public(string $path = ''): string
{
    return asset($path);
}

function projectImageFallback(): string
{
    // Image placeholder for missing project/blog images
    return asset('assets/images/favicon.png');
}

function limitText(string $text, int $length = 120): string {
    $text = trim(strip_tags($text));

    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length) . '...';
}

// Secure HTML escaping helper (widely used in public views).
// Keep signature compatible with helpers/security.php usage.
if (!function_exists('escape')) {
    function escape(?string $data): string
    {
        return htmlspecialchars(
            $data ?? '',
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}


// Common alias used in some codebases.
if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return escape($value);
    }
}





