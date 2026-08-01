<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| SECURITY HELPER SYSTEM
|--------------------------------------------------------------------------
| File: /helpers/security.php
|--------------------------------------------------------------------------
| REFACTORED: All SQL delegated to App\Repositories\AuditRepository.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| SANITIZE INPUT
|--------------------------------------------------------------------------
*/
function sanitize(mixed $data): mixed
{
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return trim(htmlspecialchars(strip_tags((string) $data), ENT_QUOTES, 'UTF-8'));
}

/*
|--------------------------------------------------------------------------
| SAFE RICH TEXT
|--------------------------------------------------------------------------
*/
function safeRichText(string $content): string
{
    return strip_tags($content, '<p><br><b><strong><i><em><u><ul><ol><li><h1><h2><h3><h4><blockquote><a><img>');
}

if (!function_exists('sanitize_html')) {
    function sanitize_html(string $content): string
    {
        return safeRichText($content);
    }
}

/*
|--------------------------------------------------------------------------
| SECURITY HEADERS
|--------------------------------------------------------------------------
*/
function securityHeaders(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    header(
        "Content-Security-Policy: "
        . "default-src 'self'; "
        . "img-src 'self' data: https:; "
        . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; "
        . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com; "
        . "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; "
        . "frame-src https://www.youtube.com https://www.youtube-nocookie.com; "
        . "connect-src 'self' https://cdn.jsdelivr.net;"
        . "object-src 'none'; "
        . "base-uri 'self'; "
        . "form-action 'self';"
    );

    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/
function csrfToken(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/*
|--------------------------------------------------------------------------
| CSRF FIELD
|--------------------------------------------------------------------------
*/
/* csrfField() canonical is defined in helpers/csrf.php. */

/*
|--------------------------------------------------------------------------
| VERIFY CSRF TOKEN
|--------------------------------------------------------------------------
*/
// verifyCsrfToken() canonical is defined in helpers/csrf.php.

/*
|--------------------------------------------------------------------------
| REQUIRE CSRF
|--------------------------------------------------------------------------
*/
function requireCsrf(): void
{
    $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!verifyCsrfToken($token)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }
}

/*
|--------------------------------------------------------------------------
| GENERATE RANDOM TOKEN
|--------------------------------------------------------------------------
*/
function generateSecureToken(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

/*
|--------------------------------------------------------------------------
| PASSWORD HASH
|--------------------------------------------------------------------------
*/
function validatePasswordStrength(string $password): array
{
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character.';
    }
    return $errors;
}

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/*
|--------------------------------------------------------------------------
| PASSWORD VERIFY
|--------------------------------------------------------------------------
*/
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/*
|--------------------------------------------------------------------------
| RATE LIMIT
|--------------------------------------------------------------------------
*/
function rateLimit(string $key, int $maxAttempts = 10, int $window = 300): bool
{
    if (!isset($_SESSION['_rate_limit'])) {
        $_SESSION['_rate_limit'] = [];
    }

    $now = time();
    if (!isset($_SESSION['_rate_limit'][$key])) {
        $_SESSION['_rate_limit'][$key] = [];
    }

    $_SESSION['_rate_limit'][$key] = array_filter(
        $_SESSION['_rate_limit'][$key],
        fn($timestamp) => ($now - $timestamp) < $window
    );

    if (count($_SESSION['_rate_limit'][$key]) >= $maxAttempts) {
        return false;
    }

    $_SESSION['_rate_limit'][$key][] = $now;
    return true;
}

/*
|--------------------------------------------------------------------------
| CLIENT IP
|--------------------------------------------------------------------------
*/
function getClientIp(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

/*
|--------------------------------------------------------------------------
| USER AGENT
|--------------------------------------------------------------------------
*/
function getUserAgent(): string
{
    return $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
}

/*
|--------------------------------------------------------------------------
| AJAX REQUEST
|--------------------------------------------------------------------------
*/
function isAjaxRequest(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/*
|--------------------------------------------------------------------------
| REQUIRE AJAX
|--------------------------------------------------------------------------
*/
function requireAjax(): void
{
    if (!isAjaxRequest()) {
        http_response_code(403);
        exit('Forbidden');
    }
}

/*
|--------------------------------------------------------------------------
| SECURITY LOG (delegates to AuditRepository)
|--------------------------------------------------------------------------
*/
function logSecurityEvent(
    $userIdOrEvent,
    $eventOrMessage,
    $severity = 'info',
    $details = ''
): void {
    // Support both call signatures used across the codebase
    if (!is_null($userIdOrEvent) && !is_int($userIdOrEvent) && !is_numeric($userIdOrEvent)) {
        $event = (string)$userIdOrEvent;
        $message = $eventOrMessage;
        $context = $severity;
        if (is_array($context)) {
            $details = json_encode($context);
        } else {
            $details = (string)$message;
        }
        $severity = 'info';
        $userId = null;
    } else {
        $userId = $userIdOrEvent === null ? null : (int)$userIdOrEvent;
        $event = $eventOrMessage;
    }

    try {
        $repo = repo('Audit');
        if (!$repo) return;

        $repo->logEvent(
            $userId ?? 0,
            $event,
            $severity,
            $details,
            getClientIp(),
            getUserAgent()
        );
    } catch (\Throwable $e) {
        error_log($e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| CLEANUP SECURITY LOGS
|--------------------------------------------------------------------------
*/
function cleanupSecurityLogs(int $days = 90): void
{
    try {
        $repo = repo('Audit');
        if (!$repo) return;
        $repo->purgeOldLogs($days);
    } catch (\Throwable $e) {
        error_log($e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| ADMIN AUDIT LOG (delegates to AuditRepository)
|--------------------------------------------------------------------------
*/
if (!function_exists('logAdminAction')) {
    function logAdminAction(?int $adminId, string $action, string $details = ''): void
    {
        try {
            $repo = repo('Audit');
            if (!$repo) return;

            $repo->logAudit(
                $adminId ?? 0,
                $action,
                'admin',
                0,
                $details,
                getClientIp()
            );
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        }
    }
}

/*
|--------------------------------------------------------------------------
| AUTO APPLY SECURITY HEADERS
|--------------------------------------------------------------------------
*/
securityHeaders();
