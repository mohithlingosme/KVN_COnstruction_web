<?php

declare(strict_types=1);

if (!defined('DEFAULT_RATE_LIMIT')) {
    define('DEFAULT_RATE_LIMIT', 5);
}

if (!defined('DEFAULT_RATE_WINDOW')) {
    define('DEFAULT_RATE_WINDOW', 300);
}

function limiterIdentifier(?string $suffix = null): string
{
    $base = request_ip() . '|' . request_user_agent();

    if ($suffix !== null && $suffix !== '') {
        $base .= '|' . $suffix;
    }

    return secureHash($base);
}

function currentRouteName(): string
{
    return $_SERVER['REQUEST_URI'] ?? 'unknown';
}

function normalizeRateLimitArgs($identifierOrMaxAttempts = null, ?int $maxAttempts = null, ?int $decaySeconds = null): array
{
    $identifier = limiterIdentifier();
    $limit = DEFAULT_RATE_LIMIT;
    $window = DEFAULT_RATE_WINDOW;

    if (is_int($identifierOrMaxAttempts)) {
        $limit = $identifierOrMaxAttempts;
        $window = $maxAttempts ?? DEFAULT_RATE_WINDOW;
    } elseif (is_string($identifierOrMaxAttempts) && $maxAttempts !== null && $decaySeconds !== null) {
        $identifier = limiterIdentifier($identifierOrMaxAttempts);
        $limit = $maxAttempts;
        $window = $decaySeconds;
    } elseif ($maxAttempts !== null && $decaySeconds !== null) {
        $limit = $maxAttempts;
        $window = $decaySeconds;
    }

    return [$identifier, $limit, $window];
}

function fetchRateLimitRecord(string $actionType, string $identifier): ?array
{
    global $conn;

    if (!isset($conn)) {
        return null;
    }

    $stmt = $conn->prepare(
        'SELECT *
         FROM rate_limits
         WHERE identifier = :identifier
           AND action_type = :action_type
           AND route_name = :route_name
         LIMIT 1'
    );

    $stmt->execute([
        ':identifier' => $identifier,
        ':action_type' => $actionType,
        ':route_name' => currentRouteName(),
    ]);

    $record = $stmt->fetch();

    return $record ?: null;
}

function checkRateLimit(string $actionType, $identifierOrMaxAttempts = null, ?int $maxAttempts = null, ?int $decaySeconds = null): bool
{
    global $conn;

    if (!isset($conn)) {
        return true;
    }

    [$identifier, $limit, $window] = normalizeRateLimitArgs($identifierOrMaxAttempts, $maxAttempts, $decaySeconds);
    $record = fetchRateLimitRecord($actionType, $identifier);

    if ($record === null) {
        return true;
    }

    if (!empty($record['blocked_until']) && strtotime((string) $record['blocked_until']) > time()) {
        return false;
    }

    $updatedAt = strtotime((string) ($record['updated_at'] ?? 'now'));

    if ((time() - $updatedAt) > $window) {
        resetRateLimit($actionType, $identifier);
        return true;
    }

    return (int) $record['attempts'] < $limit;
}

function incrementRateLimit(string $actionType, $identifierOrNull = null, ?int $maybeMaxAttempts = null): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    $identifier = is_string($identifierOrNull) && $maybeMaxAttempts === null
        ? limiterIdentifier($identifierOrNull)
        : limiterIdentifier();

    $record = fetchRateLimitRecord($actionType, $identifier);

    if ($record === null) {
        $stmt = $conn->prepare(
            'INSERT INTO rate_limits (
                identifier,
                action_type,
                route_name,
                attempts,
                created_at,
                updated_at
            ) VALUES (
                :identifier,
                :action_type,
                :route_name,
                1,
                NOW(),
                NOW()
            )'
        );

        $stmt->execute([
            ':identifier' => $identifier,
            ':action_type' => $actionType,
            ':route_name' => currentRouteName(),
        ]);

        return;
    }

    $stmt = $conn->prepare(
        'UPDATE rate_limits
         SET attempts = attempts + 1, updated_at = NOW()
         WHERE id = :id'
    );
    $stmt->execute([':id' => $record['id']]);
}

function blockRateLimit(string $actionType, int $seconds, ?string $identifierSuffix = null): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    $identifier = limiterIdentifier($identifierSuffix);
    $record = fetchRateLimitRecord($actionType, $identifier);

    if ($record === null) {
        incrementRateLimit($actionType, $identifierSuffix);
        $record = fetchRateLimitRecord($actionType, $identifier);
    }

    if ($record === null) {
        return;
    }

    $stmt = $conn->prepare('UPDATE rate_limits SET blocked_until = DATE_ADD(NOW(), INTERVAL :seconds SECOND) WHERE id = :id');
    $stmt->bindValue(':seconds', $seconds);
    $stmt->bindValue(':id', $record['id']);
    $stmt->execute();
}

function resetRateLimit(string $actionType, ?string $identifier = null): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    $stmt = $conn->prepare(
        'DELETE FROM rate_limits
         WHERE identifier = :identifier
           AND action_type = :action_type
           AND route_name = :route_name'
    );

    $stmt->execute([
        ':identifier' => $identifier ?? limiterIdentifier(),
        ':action_type' => $actionType,
        ':route_name' => currentRouteName(),
    ]);
}

function clearRateLimit(string $actionType, ?string $identifierSuffix = null): void
{
    resetRateLimit($actionType, limiterIdentifier($identifierSuffix));
}

function remainingAttempts(string $actionType, int $maxAttempts = DEFAULT_RATE_LIMIT): int
{
    $record = fetchRateLimitRecord($actionType, limiterIdentifier());

    if ($record === null) {
        return $maxAttempts;
    }

    return max(0, $maxAttempts - (int) $record['attempts']);
}

function retryAfter(string $actionType): int
{
    $record = fetchRateLimitRecord($actionType, limiterIdentifier());

    if ($record === null || empty($record['blocked_until'])) {
        return 0;
    }

    return max(0, strtotime((string) $record['blocked_until']) - time());
}

function cleanupExpiredRateLimits(): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    $conn->exec('DELETE FROM rate_limits WHERE updated_at < DATE_SUB(NOW(), INTERVAL 1 DAY)');
}

function loginRateLimit(): bool
{
    return checkRateLimit('login', LOGIN_RATE_LIMIT, LOGIN_RATE_WINDOW);
}

function adminLoginRateLimit(): bool
{
    return checkRateLimit('admin_login', 3, 600);
}

function otpRateLimit(): bool
{
    return checkRateLimit('otp', OTP_RATE_LIMIT, OTP_RATE_WINDOW);
}

function estimatorRateLimit(): bool
{
    return checkRateLimit('estimator', 20, 3600);
}

function contactRateLimit(): bool
{
    return checkRateLimit('contact_form', 5, 3600);
}

cleanupExpiredRateLimits();
