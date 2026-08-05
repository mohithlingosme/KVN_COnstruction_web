<?php

declare(strict_types=1);
/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION
|--------------------------------------------------------------------------
| ENTERPRISE SESSION SECURITY SYSTEM
|--------------------------------------------------------------------------
| File: /helpers/session.php
|--------------------------------------------------------------------------
| REFACTORED: All SQL delegated to App\Repositories\SessionRepository.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| SECURE SESSION CONFIGURATION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $isHttps ? '1' : '0');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', '3600');

    session_name('KVNSESSID');
    session_start();
}

/*
|--------------------------------------------------------------------------
| SESSION CONSTANTS
|--------------------------------------------------------------------------
*/

if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600);
}

if (!defined('ADMIN_SESSION_TIMEOUT')) {
    define('ADMIN_SESSION_TIMEOUT', 1800);
}

/*
|--------------------------------------------------------------------------
| GENERATE SESSION TOKEN
|--------------------------------------------------------------------------
*/

function generateSessionToken(): string
{
    return bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| GENERATE DEVICE HASH
|--------------------------------------------------------------------------
*/

function generateDeviceHash(): string
{
    return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . ($_SERVER['HTTP_USER_AGENT'] ?? '') . ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
}

/*
|--------------------------------------------------------------------------
| GENERATE SESSION FINGERPRINT
|--------------------------------------------------------------------------
*/

function generateSessionFingerprint(): string
{
    return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
}

/*
|--------------------------------------------------------------------------
| STORE SESSION IN DATABASE (delegates to SessionRepository)
|--------------------------------------------------------------------------
*/

function storeSessionInDatabase(int $userId, string $sessionToken, string $role): void
{
    try {
        $repo = repo('Session');
        if (!$repo) return;

        $repo->create(
            $userId,
            $sessionToken,
            generateSessionFingerprint(),
            generateDeviceHash(),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device',
            in_array($role, ['admin', 'super_admin'], true)
        );
    } catch (Exception $e) {
        error_log('Session DB Error: ' . $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| INITIALIZE SESSION SECURITY
|--------------------------------------------------------------------------
*/

function initializeSessionSecurity(array $user): void
{
    session_regenerate_id(true);

    $sessionToken = generateSessionToken();

    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['session_token'] = $sessionToken;
    $_SESSION['fingerprint'] = generateSessionFingerprint();
    $_SESSION['device_hash'] = generateDeviceHash();
    $_SESSION['last_activity'] = time();
    $_SESSION['login_time'] = time();
    $_SESSION['is_admin'] = in_array($user['role'], ['admin', 'super_admin'], true);

    storeSessionInDatabase((int) $user['id'], $sessionToken, $user['role']);

    if (function_exists('logSecurityEvent')) {
        logSecurityEvent($user['id'], 'session_initialized', 'info', 'Secure session initialized');
    }
}

/*
|--------------------------------------------------------------------------
| AUTH SESSION WRAPPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('createUserSession')) {
    function createUserSession(array $user): void
    {
        initializeSessionSecurity($user);
        $_SESSION['user_email'] = $user['email'] ?? '';
        $_SESSION['user_role'] = $user['role'] ?? 'client';
    }
}

if (!function_exists('createAdminSession')) {
    function createAdminSession(array $admin): void
    {
        initializeSessionSecurity($admin);
    }
}

/*
|--------------------------------------------------------------------------
| OTP SESSION HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('startOtpSession')) {
    function startOtpSession(array $user, string $purpose = 'login'): void
    {
        $_SESSION['otp_user_id'] = (int) ($user['id'] ?? 0);
        $_SESSION['otp_phone'] = (string) ($user['phone'] ?? '');
        $_SESSION['otp_email'] = (string) ($user['email'] ?? '');
        $_SESSION['otp_purpose'] = $purpose;
        $_SESSION['otp_created_at'] = time();
        $_SESSION['otp_attempts'] = 0;
        $_SESSION['otp_last_resend'] = 0;
    }
}

if (!function_exists('isOtpSessionValid')) {
    function isOtpSessionValid(): bool
    {
        if (empty($_SESSION['otp_user_id']) || empty($_SESSION['otp_phone'])) {
            return false;
        }
        $createdAt = (int) ($_SESSION['otp_created_at'] ?? 0);
        if ($createdAt <= 0) {
            return false;
        }
        $expiryMinutes = defined('OTP_EXPIRY_MINUTES') ? OTP_EXPIRY_MINUTES : 5;
        return (time() - $createdAt) <= ($expiryMinutes * 60);
    }
}

if (!function_exists('destroyOtpSession')) {
    function destroyOtpSession(): void
    {
        unset(
            $_SESSION['otp_user_id'],
            $_SESSION['otp_phone'],
            $_SESSION['otp_email'],
            $_SESSION['otp_purpose'],
            $_SESSION['otp_created_at'],
            $_SESSION['otp_attempts'],
            $_SESSION['otp_last_resend']
        );
    }
}

/*
|--------------------------------------------------------------------------
| VALIDATE SESSION
|--------------------------------------------------------------------------
*/
function validateSession(): bool
{
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return false;
    }

    if (empty($_SESSION['session_token'])) {
        destroySession();
        return false;
    }

    $currentFingerprint = generateSessionFingerprint();
    if (!isset($_SESSION['fingerprint']) || $_SESSION['fingerprint'] !== $currentFingerprint) {
        if (function_exists('logSecurityEvent')) {
            logSecurityEvent($_SESSION['user_id'] ?? null, 'session_hijack_attempt', 'critical', 'Session fingerprint mismatch');
        }
        destroySession();
        return false;
    }

    $currentDeviceHash = generateDeviceHash();
    if (!isset($_SESSION['device_hash']) || $_SESSION['device_hash'] !== $currentDeviceHash) {
        destroySession();
        return false;
    }

    $timeout = isAdmin() ? ADMIN_SESSION_TIMEOUT : SESSION_TIMEOUT;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        if (function_exists('logSecurityEvent')) {
            logSecurityEvent($_SESSION['user_id'] ?? null, 'session_timeout', 'warning', 'Session expired due to inactivity');
        }
        destroySession();
        return false;
    }

    // Database session validation via SessionRepository
    try {
        $repo = repo('Session');
        if ($repo) {
            $session = $repo->findByToken($_SESSION['session_token']);
            if (!$session) {
                destroySession();
                return false;
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }

    refreshSession();
    return true;
}

/*
|--------------------------------------------------------------------------
| REFRESH SESSION
|--------------------------------------------------------------------------
*/
function refreshSession(): void
{
    $_SESSION['last_activity'] = time();

    try {
        $repo = repo('Session');
        if (!$repo || empty($_SESSION['session_token'])) return;

        $session = $repo->findByToken($_SESSION['session_token']);
        if ($session && isset($session['id'])) {
            $repo->updateActivity((int)$session['id']);
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| DESTROY OTHER SESSIONS
|--------------------------------------------------------------------------
*/
function destroyOtherSessions(int $userId, string $currentToken): void
{
    try {
        $repo = repo('Session');
        if (!$repo) return;

        $sessions = $repo->findByUserId($userId);
        foreach ($sessions as $s) {
            if ($s['session_token'] !== $currentToken) {
                $repo->deleteByToken($s['session_token']);
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| DESTROY SESSION
|--------------------------------------------------------------------------
*/
if (!function_exists('destroySession')) {
    function destroySession(): void
    {
        if (isset($_SESSION['session_token'])) {
            try {
                $repo = repo('Session');
                if ($repo) {
                    $repo->deleteByToken($_SESSION['session_token']);
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }
}

/*
|--------------------------------------------------------------------------
| LOGOUT USER
|--------------------------------------------------------------------------
*/
function logout(): void
{
    if (function_exists('logSecurityEvent') && isset($_SESSION['user_id'])) {
        logSecurityEvent($_SESSION['user_id'], 'logout', 'info', 'User logged out');
    }

    destroySession();

    header('Location: ' . APP_URL . '/login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/
function isLoggedIn(): bool
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/*
|--------------------------------------------------------------------------
| ADMIN CHECK
|--------------------------------------------------------------------------
*/
function isAdmin(): bool
{
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin'], true);
}

/*
|--------------------------------------------------------------------------
| CLIENT CHECK
|--------------------------------------------------------------------------
*/
function isClient(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'client';
}

/*
|--------------------------------------------------------------------------
| REQUIRE LOGIN
|--------------------------------------------------------------------------
*/
function requireLogin(): void
{
    if (!validateSession()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| REQUIRE ADMIN
|--------------------------------------------------------------------------
*/
function requireAdmin(): void
{
    if (!validateSession() || !isAdmin()) {
        header('Location: ' . APP_URL . '/admin/login.php');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| REQUIRE CLIENT
|--------------------------------------------------------------------------
*/
function requireClient(): void
{
    if (!validateSession() || !isClient()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| CURRENT USER ID
|--------------------------------------------------------------------------
*/
function currentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/*
|--------------------------------------------------------------------------
| CURRENT USER ROLE
|--------------------------------------------------------------------------
*/
function currentUserRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

/*
|--------------------------------------------------------------------------
| SESSION REMAINING TIME
|--------------------------------------------------------------------------
*/
function sessionRemainingTime(): int
{
    $timeout = isAdmin() ? ADMIN_SESSION_TIMEOUT : SESSION_TIMEOUT;
    return max(0, $timeout - (time() - ($_SESSION['last_activity'] ?? 0)));
}

/*
|--------------------------------------------------------------------------
| CLEANUP EXPIRED SESSIONS
|--------------------------------------------------------------------------
*/
function cleanupExpiredSessions(): void
{
    try {
        $repo = repo('Session');
        if ($repo) {
            $repo->deleteExpired(1);
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

