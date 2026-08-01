<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ADVANCED RATE LIMITER
|--------------------------------------------------------------------------
| File: /helpers/rateLimiter.php
|--------------------------------------------------------------------------
| REFACTORED: All SQL delegated to App\Repositories\RateLimitRepository.
| This file now contains only convenience wrappers.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| DEFAULT LIMITS
|--------------------------------------------------------------------------
*/

if (!defined('DEFAULT_RATE_LIMIT')) {
    define('DEFAULT_RATE_LIMIT', 5);
}

if (!defined('DEFAULT_RATE_WINDOW')) {
    define('DEFAULT_RATE_WINDOW', 300);
}

/*
|--------------------------------------------------------------------------
| CLIENT IDENTIFIER
|--------------------------------------------------------------------------
*/

function limiterIdentifier()
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    return hash('sha256', $ip . $agent);
}

/*
|--------------------------------------------------------------------------
| CURRENT ROUTE
|--------------------------------------------------------------------------
*/

function currentRouteName()
{
    return $_SERVER['REQUEST_URI'] ?? 'unknown';
}

/*
|--------------------------------------------------------------------------
| CHECK RATE LIMIT
|--------------------------------------------------------------------------
*/

function checkRateLimit(
    $actionType,
    $maxAttempts = DEFAULT_RATE_LIMIT,
    $decaySeconds = DEFAULT_RATE_WINDOW,
    ?PDO $pdo = null
) {
    $ident = limiterIdentifier();
    $route = currentRouteName();
    $key = $actionType . ':' . $ident . ':' . $route;

    try {
        $repo = repo('RateLimit');
        if (!$repo) {
            error_log('Rate limiter: RateLimitRepository unavailable');
            return true;
        }

        $blocked = $repo->getBlockedUntil($key);
        if ($blocked !== null) {
            return false;
        }

        $attempts = $repo->getAttempts($key);
        if ($attempts >= $maxAttempts) {
            $repo->block($key, (int)ceil($decaySeconds / 60));
            return false;
        }

        $repo->increment($key, (int)ceil($decaySeconds / 60));
        return true;
    } catch (\Throwable $e) {
        error_log('Rate Limit Error: ' . $e->getMessage());
        return true;
    }
}

/*
|--------------------------------------------------------------------------
| REMAINING ATTEMPTS
|--------------------------------------------------------------------------
*/

function remainingAttempts($actionType, $maxAttempts = DEFAULT_RATE_LIMIT)
{
    $ident = limiterIdentifier();
    $route = currentRouteName();
    $key = $actionType . ':' . $ident . ':' . $route;

    try {
        $repo = repo('RateLimit');
        if (!$repo) return $maxAttempts;

        $attempts = $repo->getAttempts($key);
        return max(0, $maxAttempts - $attempts);
    } catch (\Throwable $e) {
        return $maxAttempts;
    }
}

/*
|--------------------------------------------------------------------------
| RETRY AFTER
|--------------------------------------------------------------------------
*/

function retryAfter($actionType)
{
    $ident = limiterIdentifier();
    $route = currentRouteName();
    $key = $actionType . ':' . $ident . ':' . $route;

    try {
        $repo = repo('RateLimit');
        if (!$repo) return 0;

        $blocked = $repo->getBlockedUntil($key);
        if ($blocked === null) return 0;

        return max(0, strtotime($blocked) - time());
    } catch (\Throwable $e) {
        return 0;
    }
}

/*
|--------------------------------------------------------------------------
| CLEAR / RESET RATE LIMIT
|--------------------------------------------------------------------------
*/

function resetRateLimit($actionType)
{
    $ident = limiterIdentifier();
    $route = currentRouteName();
    $key = $actionType . ':' . $ident . ':' . $route;

    try {
        $repo = repo('RateLimit');
        if (!$repo) return;
        $repo->reset($key);
    } catch (\Throwable $e) {
        error_log($e->getMessage());
    }
}

if (!function_exists('clearRateLimit')) {
    function clearRateLimit(string $actionType): void
    {
        resetRateLimit($actionType);
    }
}

/*
|--------------------------------------------------------------------------
| CLEANUP EXPIRED LIMITS
|--------------------------------------------------------------------------
*/

function cleanupExpiredRateLimits()
{
    try {
        $repo = repo('RateLimit');
        if (!$repo) return;
        $repo->cleanExpired();
    } catch (\Throwable $e) {
        error_log($e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| COMMON LIMIT CONFIGS
|--------------------------------------------------------------------------
*/

function loginRateLimit()
{
    return checkRateLimit('login', 5, 300);
}

function adminLoginRateLimit()
{
    return checkRateLimit('admin_login', 3, 600);
}

function otpRateLimit()
{
    return checkRateLimit('otp', 3, 600);
}

function estimatorRateLimit()
{
    return checkRateLimit('estimator', 20, 3600);
}

function contactRateLimit()
{
    return checkRateLimit('contact', 5, 3600);
}

/*
|--------------------------------------------------------------------------
| AUTO CLEANUP
|--------------------------------------------------------------------------
*/

cleanupExpiredRateLimits();
</｜DSML｜parameter>
</create_file>
