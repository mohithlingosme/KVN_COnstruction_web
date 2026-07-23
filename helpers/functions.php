<?php

declare(strict_types=1);

function asset(string $path): string
{
    // Centralized asset helper using APP_URL
    return base_url($path);
}

function base_url_public(string $path = ''): string
{
    return asset($path);
}

// escapeCssClass is defined in helpers/functions_security.php (more complete version)
// Do NOT redefine here to avoid conflicts
function projectImageFallback(): string
{
    // Image placeholder for missing project/blog images
    return asset('assets/images/favicon.png');
}

if (!function_exists('limitText')) {
    function limitText(string $text, int $length = 120): string {
        $text = trim(strip_tags($text));

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . '...';
    }
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

// Context-aware escaping for HTML attributes / URL params.
// Implementations live in helpers/functions_security.php.
// This file is included by bootstrap/helpers autoload in many setups,
// but we also defensively include it here if possible.
$__functions_security_path = __DIR__ . '/functions_security.php';
if (file_exists($__functions_security_path)) {
    require_once $__functions_security_path;
}


// Common alias used in some codebases.
if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return escape($value);
    }
}






