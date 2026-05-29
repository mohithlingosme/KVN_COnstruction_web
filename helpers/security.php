<?php

declare(strict_types=1);

function sanitize($value)
{
    if (is_array($value)) {
        return array_map('sanitize', $value);
    }

    return trim(strip_tags((string) $value));
}

function escape($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function safeRichText(?string $content): string
{
    $allowed = '<p><br><b><strong><i><em><ul><ol><li><h1><h2><h3><h4><blockquote><a><img><span>';

    return trim(strip_tags((string) $content, $allowed));
}

function secureHash(string $value): string
{
    return hash('sha256', $value);
}

function generateSecureToken(int $length = 64): string
{
    return bin2hex(random_bytes((int) ceil($length / 2)));
}

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

function validatePasswordStrength(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password) === 1
        && preg_match('/[a-z]/', $password) === 1
        && preg_match('/[0-9]/', $password) === 1;
}

function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Alias for validateEmail
function isValidEmail(string $email): bool
{
    return validateEmail($email);
}

function validatePhone(string $phone): bool
{
    return preg_match('/^[6-9]\d{9}$/', preg_replace('/\D/', '', $phone)) === 1;
}

function securityHeaders(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header('X-XSS-Protection: 0');

    if (request_is_secure()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    $csp = [
        "default-src 'self'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'",
        "object-src 'none'",
        "img-src 'self' data: https:",
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
        "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
        "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com",
        "connect-src 'self' https://www.fast2sms.com https://api.twilio.com",
        "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://maps.google.com",
        "media-src 'self' https:",
    ];

    header('Content-Security-Policy: ' . implode('; ', $csp));
}

function normalizeLogDetails($details): ?string
{
    if ($details === null || $details === '') {
        return null;
    }

    if (is_scalar($details)) {
        return (string) $details;
    }

    return json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function logSecurityEvent($userOrEvent, $eventOrDetails = null, string $level = 'info', $details = null): bool
{
    global $conn;

    if (!isset($conn)) {
        logApplicationError('security_log_skipped', ['reason' => 'db_unavailable']);
        return false;
    }

    $userId = null;
    $eventType = '';
    $eventLevel = in_array($level, ['info', 'warning', 'critical'], true) ? $level : 'info';
    $eventDetails = $details;

    if (is_int($userOrEvent) || ctype_digit((string) $userOrEvent)) {
        $userId = (int) $userOrEvent;
        $eventType = (string) $eventOrDetails;
    } else {
        $eventType = (string) $userOrEvent;
        $eventDetails = $eventOrDetails;
    }

    try {
        $stmt = $conn->prepare(
            'INSERT INTO security_logs (
                user_id,
                event_type,
                event_level,
                ip_address,
                user_agent,
                event_details,
                request_uri,
                request_method,
                created_by_system,
                created_at
            ) VALUES (
                :user_id,
                :event_type,
                :event_level,
                :ip_address,
                :user_agent,
                :event_details,
                :request_uri,
                :request_method,
                :created_by_system,
                NOW()
            )'
        );

        return $stmt->execute([
            ':user_id' => $userId,
            ':event_type' => $eventType,
            ':event_level' => $eventLevel,
            ':ip_address' => request_ip(),
            ':user_agent' => request_user_agent(),
            ':event_details' => normalizeLogDetails($eventDetails),
            ':request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            ':request_method' => request_method(),
            ':created_by_system' => 0,
        ]);
    } catch (Throwable $exception) {
        logApplicationError('security_log_error', ['message' => $exception->getMessage()]);
        return false;
    }
}

function logAdminAction(
    $userOrAction,
    $actionOrDescription = null,
    $description = null,
    ?string $entityType = null,
    $entityId = null,
    $oldValues = null,
    $newValues = null
): bool {
    global $conn;

    if (!isset($conn)) {
        logApplicationError('audit_log_skipped', ['reason' => 'db_unavailable']);
        return false;
    }

    $userId = null;
    $actionType = '';
    $actionDescription = null;

    if (is_int($userOrAction) || ctype_digit((string) $userOrAction)) {
        $userId = (int) $userOrAction;
        $actionType = (string) $actionOrDescription;
        $actionDescription = is_string($description) ? $description : normalizeLogDetails($description);
    } else {
        $actionType = (string) $userOrAction;
        $actionDescription = is_string($actionOrDescription) ? $actionOrDescription : normalizeLogDetails($actionOrDescription);
    }

    try {
        $stmt = $conn->prepare(
            'INSERT INTO audit_logs (
                user_id,
                action_type,
                description,
                ip_address,
                created_at,
                entity_type,
                entity_id,
                old_values,
                new_values
            ) VALUES (
                :user_id,
                :action_type,
                :description,
                :ip_address,
                NOW(),
                :entity_type,
                :entity_id,
                :old_values,
                :new_values
            )'
        );

        return $stmt->execute([
            ':user_id' => $userId,
            ':action_type' => $actionType,
            ':description' => $actionDescription,
            ':ip_address' => request_ip(),
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':old_values' => normalizeLogDetails($oldValues),
            ':new_values' => normalizeLogDetails($newValues),
        ]);
    } catch (Throwable $exception) {
        logApplicationError('audit_log_error', ['message' => $exception->getMessage()]);
        return false;
    }
}

function suspiciousActivity(string $reason, string $severity = 'critical'): void
{
    logSecurityEvent($_SESSION['user_id'] ?? null, 'suspicious_activity', $severity, $reason);
}

function safePost(string $key, $default = ''): string
{
    return sanitize($_POST[$key] ?? $default);
}

function safeGet(string $key, $default = ''): string
{
    return sanitize($_GET[$key] ?? $default);
}

function jsonResponse(array $data = [], int $status = 200): void
{
    json_response($data, $status);
}

function isAjaxRequest(): bool
{
    return is_ajax_request();
}

function requireAjax(): void
{
    if (!isAjaxRequest()) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function cleanupSecurityLogs(int $days = 90): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    try {
        $stmt = $conn->prepare('DELETE FROM security_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)');
        $stmt->bindValue(':days', $days);
        $stmt->execute();
    } catch (Throwable $exception) {
        logApplicationError('security_log_cleanup_error', ['message' => $exception->getMessage()]);
    }
}
