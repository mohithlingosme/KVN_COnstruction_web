<?php

declare(strict_types=1);

// Lightweight test bootstrap (no composer required)

// Define project root for app code
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Avoid constant re-definition warnings if bootstrap is included multiple times
if (!defined('APP_URL')) {
    define('APP_URL', 'http://localhost');
}


// Ensure error visibility during tests
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start session if needed by auth controller
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Constants expected by app/config helpers
if (!defined('APP_URL')) {
    define('APP_URL', 'http://localhost');
}

// Load app helpers (avoid app config DB connection for unit tests)
require_once ROOT_PATH . '/helpers/functions.php';
// security.php defines strict logSecurityEvent; we rely on it in unit tests.
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/rateLimiter.php';
require_once ROOT_PATH . '/helpers/otp.php';


// SQLite compatibility: AuthController + helpers use NOW() in SQL.
// We'll register NOW() on every PDO connection created during tests.
function registerSqliteNow(PDO $pdo): void
{
    try {
        if (method_exists($pdo, 'sqliteCreateFunction')) {
            $pdo->sqliteCreateFunction('NOW', function (): string {
                return (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
            }, 0);
        }
    } catch (Throwable $e) {
        // ignore if not supported
    }
}




// Ensure base helpers / models are loaded for non-namespaced controller usage



// AuthController refers to User class without namespace; create an alias if needed.
if (!class_exists('User') && class_exists('App\Models\User')) {
    class_alias('App\Models\User', 'User');
}


// Ensure auth controller class is loaded
require_once ROOT_PATH . '/app/controllers/auth/AuthController.php';



// Stubs for external side effects
if (!function_exists('sendOtpSms')) {
    function 
    sendOtpSms(string $phone, string $otp): bool {
        return true;
    }
}

if (!function_exists('sendOtpEmail')) {
    function 
    sendOtpEmail(string $email, string $otp, string $name = 'User'): bool {
        return !empty($email);
    }
}

if (!function_exists('createUserSession')) {
    function 
    createUserSession(array $user): void {
        $_SESSION['user_id'] = (int)($user['id'] ?? 0);
    }
}

if (!function_exists('createAdminSession')) {
    function 
    createAdminSession(array $admin): void {
        $_SESSION['admin_id'] = (int)($admin['id'] ?? 0);
    }
}

if (!function_exists('destroySession')) {
    function 
    destroySession(): void {
        session_unset();
        // no session_destroy to keep tests isolated
    }
}

// logSecurityEvent is defined in helpers/security.php (strict typed). We rely on
// the production function but ensure argument types are compatible in tests.






if (!function_exists('logAdminAction')) {
    function 
    logAdminAction(...$args): void {
        // no-op
    }
}

if (!function_exists('sendAdminLoginAlert')) {
    function 
    sendAdminLoginAlert(...$args): void {
        // no-op
    }
}

if (!function_exists('incrementRateLimit')) {
    // If app rateLimiter provides these, keep them; otherwise stub.
    function 
    incrementRateLimit(string $key): void {}
}

if (!function_exists('clearRateLimit')) {
    function 
    clearRateLimit(string $key): void {}
}

// Helpers for CSRF validation: make it deterministic in unit tests
if (!function_exists('validateCsrf')) {
    function 
    validateCsrf(string $token): bool {
        return $token === 'valid-csrf';
    }
}

// Simple sanitizer for controller tests (use app helper if available)
if (!function_exists('sanitize')) {
    function 
    sanitize(string $value): string {
        return trim($value);
    }
}



