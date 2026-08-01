<?php

declare(strict_types=1);

/**
 * KVN Construction - Security Middleware
 * 
 * Applied to every request to enforce security policies.
 * - Security headers
 * - CSRF protection
 * - Rate limiting
 * - Input validation
 * - Session hardening
 */

// ============================================
// 1. SECURITY HEADERS
// ============================================
if (!headers_sent()) {
    // Prevent MIME-type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Enable XSS filter in older browsers
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Permissions policy
    header("Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()");
    
    // Content Security Policy
    $cspDirectives = [
        "default-src 'self'",
        "img-src 'self' data: https:",
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
        "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com",
        "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:",
        "frame-src https://www.youtube.com https://www.youtube-nocookie.com",
        "connect-src 'self' https://cdn.jsdelivr.net",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
    ];
    header('Content-Security-Policy: ' . implode('; ', $cspDirectives));
    
    // HSTS (only if HTTPS)
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}

// ============================================
// 2. CSRF PROTECTION
// ============================================
if (!function_exists('validateCsrfToken')) {
    function validateCsrfToken(?string $token = null): bool
    {
        if (empty($_SESSION['_csrf_token'])) {
            return false;
        }
        
        $submittedToken = $token 
            ?? $_POST['_csrf_token'] 
            ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
            ?? null;
        
        if (empty($submittedToken)) {
            return false;
        }
        
        return hash_equals($_SESSION['_csrf_token'], $submittedToken);
    }
}

if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('requireCsrfToken')) {
    function requireCsrfToken(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validateCsrfToken()) {
                http_response_code(403);
                if (function_exists('apiError')) {
                    apiError('Invalid CSRF token.', null, 403);
                }
                exit('Invalid CSRF token.');
            }
        }
    }
}

// ============================================
// 3. RATE LIMITING
// ============================================
if (!function_exists('checkRateLimit')) {
    function checkRateLimit(string $key, int $maxAttempts = 10, int $windowSeconds = 300): bool
    {
        if (!isset($_SESSION['_rate_limit'])) {
            $_SESSION['_rate_limit'] = [];
        }
        
        if (!isset($_SESSION['_rate_limit'][$key])) {
            $_SESSION['_rate_limit'][$key] = [];
        }
        
        $now = time();
        $_SESSION['_rate_limit'][$key] = array_values(
            array_filter(
                $_SESSION['_rate_limit'][$key],
                fn($timestamp) => ($now - $timestamp) < $windowSeconds
            )
        );
        
        if (count($_SESSION['_rate_limit'][$key]) >= $maxAttempts) {
            return false;
        }
        
        $_SESSION['_rate_limit'][$key][] = $now;
        return true;
    }
}

if (!function_exists('clearRateLimit')) {
    function clearRateLimit(string $key): void
    {
        unset($_SESSION['_rate_limit'][$key]);
    }
}

// ============================================
// 4. INPUT SANITIZATION
// ============================================
if (!function_exists('sanitizeInput')) {
    function sanitizeInput(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map('sanitizeInput', $data);
        }
        
        if (is_string($data)) {
            // Strip null bytes
            $data = str_replace("\0", '', $data);
            // Strip HTML tags (except safe ones for rich text)
            $data = strip_tags($data, '<p><br><b><strong><i><em><u><ul><ol><li><a><img>');
            // Escape HTML entities
            $data = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            return trim($data);
        }
        
        return $data;
    }
}

if (!function_exists('sanitizeFilename')) {
    function sanitizeFilename(string $filename): string
    {
        // Remove directory traversal
        $filename = str_replace(['../', '..\\', './', '.\\'], '', $filename);
        // Remove null bytes
        $filename = str_replace("\0", '', $filename);
        // Only allow safe characters
        $filename = preg_replace('/[^\w\-\.]/', '_', $filename);
        return $filename;
    }
}

// ============================================
// 5. OUTPUT ESCAPING
// ============================================
if (!function_exists('escapeHtml')) {
    function escapeHtml(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('escapeJs')) {
    function escapeJs(string $value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}

if (!function_exists('escapeUrl')) {
    function escapeUrl(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

// ============================================
// 6. PREVENT OPEN REDIRECT
// ============================================
if (!function_exists('isSafeRedirect')) {
    function isSafeRedirect(string $url): bool
    {
        $baseUrl = defined('APP_URL') ? APP_URL : '';
        if (empty($baseUrl)) {
            return false;
        }
        
        // Allow relative URLs
        if (str_starts_with($url, '/')) {
            return true;
        }
        
        // Only allow URLs to our own domain
        $baseHost = parse_url($baseUrl, PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);
        
        return $urlHost === $baseHost || empty($urlHost);
    }
}

if (!function_exists('safeRedirect')) {
    function safeRedirect(string $path): void
    {
        if (!isSafeRedirect($path)) {
            $path = '/';
        }
        header('Location: ' . $path);
        exit;
    }
}

// ============================================
// 7. PREVENT MASS ASSIGNMENT
// ============================================
if (!function_exists('filterAllowedFields')) {
    function filterAllowedFields(array $input, array $allowedFields): array
    {
        $filtered = [];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $input)) {
                $filtered[$field] = $input[$field];
            }
        }
        return $filtered;
    }
}

// ============================================
// 8. SECURE COOKIE SETTINGS
// ============================================
if (!function_exists('setSecureCookie')) {
    function setSecureCookie(string $name, string $value, int $expires = 0, string $path = '/', string $samesite = 'Lax'): bool
    {
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        return setcookie($name, $value, [
            'expires' => $expires,
            'path' => $path,
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => $samesite,
        ]);
    }
}

// ============================================
// 9. GENERATE CSRF TOKEN FOR THIS REQUEST
// ============================================
generateCsrfToken();