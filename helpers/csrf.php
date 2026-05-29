<?php

declare(strict_types=1);

if (!defined('CSRF_TOKEN_EXPIRY')) {
    define('CSRF_TOKEN_EXPIRY', 1800);
}

function csrfFingerprint(): string
{
    return secureHash(request_ip() . '|' . request_user_agent());
}

function csrfToken(): string
{
    $expired = !isset($_SESSION['csrf_token_time'])
        || (time() - (int) $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRY;

    if (!isset($_SESSION['csrf_token']) || $expired) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
        $_SESSION['csrf_fingerprint'] = csrfFingerprint();
    }

    return $_SESSION['csrf_token'];
}

// Alias for backwards compatibility
function generateCsrfToken(): string
{
    return csrfToken();
}

function regenerateCsrfToken(): string
{
    unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time'], $_SESSION['csrf_fingerprint']);
    return generateCsrfToken();
}

function refreshCsrf(): string
{
    return regenerateCsrfToken();
}

function destroyCsrfToken(): void
{
    unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time'], $_SESSION['csrf_fingerprint']);
}

function verifyCsrfToken(?string $token = null): bool
{
    if (empty($token) || empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
        return false;
    }

    if ((time() - (int) $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRY) {
        destroyCsrfToken();
        return false;
    }

    if (($_SESSION['csrf_fingerprint'] ?? '') !== csrfFingerprint()) {
        destroyCsrfToken();
        return false;
    }

    return hash_equals((string) $_SESSION['csrf_token'], $token);
}

function validateCsrf(?string $token = null): bool
{
    $methods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    if (!in_array(request_method(), $methods, true)) {
        return true;
    }

    $submittedToken = $token
        ?? $_POST['csrf_token']
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? null;

    if (!verifyCsrfToken($submittedToken)) {
        logSecurityEvent($_SESSION['user_id'] ?? null, 'csrf_validation_failed', 'critical', [
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
        ]);

        if (is_ajax_request()) {
            csrfJsonError();
        }

        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    regenerateCsrfToken();

    return true;
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . escape(csrfToken()) . '">';
}

function csrfMetaTag(): string
{
    return '<meta name="csrf-token" content="' . escape(csrfToken()) . '">';
}

function validateAjaxCsrf(): bool
{
    return verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
}

function getAjaxCsrfToken(): string
{
    return csrfToken();
}

function csrfJsonError(): void
{
    json_response([
        'success' => false,
        'message' => 'Invalid CSRF token.',
    ], 403);
}

function cleanupExpiredCsrf(): void
{
    if (isset($_SESSION['csrf_token_time']) && (time() - (int) $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRY) {
        destroyCsrfToken();
    }
}

cleanupExpiredCsrf();
