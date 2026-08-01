<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| GLOBAL APPLICATION CONFIGURATION
|--------------------------------------------------------------------------
*/

ob_start();

/* Load deployment configuration before constants are defined.  Values already
 * supplied by the web server always take precedence over the local .env file. */
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $envLine) {
        $envLine = trim($envLine);
        if ($envLine === '' || str_starts_with($envLine, '#') || !str_contains($envLine, '=')) {
            continue;
        }
        [$envKey, $envValue] = explode('=', $envLine, 2);
        $envKey = trim($envKey);
        $envValue = trim($envValue);
        $envValue = trim($envValue, "\"'");
        if ($envKey !== '' && getenv($envKey) === false) {
            putenv($envKey . '=' . $envValue);
            $_ENV[$envKey] = $envValue;
        }
    }
}

function env_value(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

/*
|--------------------------------------------------------------------------
| APPLICATION INFO
|--------------------------------------------------------------------------
*/
if (!defined('APP_NAME')) { define('APP_NAME', env_value('APP_NAME', 'KVN Construction')); }
if (!defined('APP_URL')) { define('APP_URL', rtrim((string) env_value('APP_URL', 'https://kvnconstruction.com'), '/')); }
if (!defined('APP_ENV')) { define('APP_ENV', env_value('APP_ENV', 'production')); }
/*
|--------------------------------------------------------------------------
| ROOT PATHS
|--------------------------------------------------------------------------
*/
if (!defined('ROOT_PATH')) { define('ROOT_PATH', dirname(__DIR__)); }
if (!defined('APP_PATH')) { define('APP_PATH', ROOT_PATH . '/app'); }
if (!defined('CONFIG_PATH')) { define('CONFIG_PATH', ROOT_PATH . '/config'); }
if (!defined('PUBLIC_PATH')) { define('PUBLIC_PATH', ROOT_PATH . '/public'); }
if (!defined('UPLOAD_PATH')) { define('UPLOAD_PATH', ROOT_PATH . '/uploads'); }
if (!defined('HELPER_PATH')) { define('HELPER_PATH', ROOT_PATH . '/helpers'); }
if (!defined('MIDDLEWARE_PATH')) { define('MIDDLEWARE_PATH', ROOT_PATH . '/middleware'); }

/*
|--------------------------------------------------------------------------
| PSR-4 AUTOLOADER FOR App\ NAMESPACE & CORE CLASSES
|--------------------------------------------------------------------------
*/
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }

        // Case-insensitive fallback for operating system file matching (e.g., Core vs core, Controllers vs controllers)
        $parts = explode('\\', $relativeClass);
        $filename = array_pop($parts) . '.php';
        $dir = $baseDir . implode('/', array_map('lcfirst', $parts));
        $altFile = rtrim($dir, '/') . '/' . $filename;
        if (file_exists($altFile)) {
            require_once $altFile;
            return;
        }
    }

    // Legacy unnamespaced class fallbacks in core, app/controllers, app/services, app/repositories
    $legacyPaths = [
        ROOT_PATH . '/core/' . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
        APP_PATH . '/services/' . $class . '.php',
        APP_PATH . '/repositories/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
    ];
    foreach ($legacyPaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

/*
|--------------------------------------------------------------------------
| DATABASE CONFIG
|--------------------------------------------------------------------------
*/
if (!defined('DB_HOST')) { define('DB_HOST', env_value('DB_HOST', '127.0.0.1')); }
if (!defined('DB_PORT')) { define('DB_PORT', (int) env_value('DB_PORT', 3306)); }
if (!defined('DB_NAME')) { define('DB_NAME', env_value('DB_NAME', 'kvnc_platform')); }
if (!defined('DB_USER')) { define('DB_USER', env_value('DB_USER', 'root')); }
if (!defined('DB_PASS')) { define('DB_PASS', env_value('DB_PASS', '')); }
/*
|--------------------------------------------------------------------------
| APPLICATION SETTINGS
|--------------------------------------------------------------------------
*/

date_default_timezone_set('Asia/Kolkata');
if (!defined('APP_DEBUG')) { define('APP_DEBUG', filter_var(env_value('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN)); }
/*
|--------------------------------------------------------------------------
| SESSION SETTINGS
|--------------------------------------------------------------------------
*/
if (!defined('SESSION_TIMEOUT')) { define('SESSION_TIMEOUT', 3600); }
if (!defined('ADMIN_SESSION_TIMEOUT')) { define('ADMIN_SESSION_TIMEOUT', 1800); }
if (!defined('SESSION_NAME')) { define('SESSION_NAME', 'KVNSESSID'); }
/*
|--------------------------------------------------------------------------
| OTP SETTINGS
|--------------------------------------------------------------------------
*/
if (!defined('OTP_EXPIRY_MINUTES')) { define('OTP_EXPIRY_MINUTES', 5); }
if (!defined('OTP_MAX_ATTEMPTS')) { define('OTP_MAX_ATTEMPTS', 3); }
if (!defined('OTP_RESEND_LIMIT')) { define('OTP_RESEND_LIMIT', 3); }
if (!defined('OTP_BLOCK_MINUTES')) { define('OTP_BLOCK_MINUTES', 15); }
/*
|--------------------------------------------------------------------------
| RATE LIMIT SETTINGS
|--------------------------------------------------------------------------
*/
if (!defined('LOGIN_RATE_LIMIT')) { define('LOGIN_RATE_LIMIT', 5); }
if (!defined('LOGIN_RATE_WINDOW')) { define('LOGIN_RATE_WINDOW', 300); }
if (!defined('OTP_RATE_LIMIT')) { define('OTP_RATE_LIMIT', 3); }
if (!defined('OTP_RATE_WINDOW')) { define('OTP_RATE_WINDOW', 600); }
/*
|--------------------------------------------------------------------------
| FILE UPLOAD SETTINGS
|--------------------------------------------------------------------------
*/
if (!defined('MAX_UPLOAD_SIZE')) { define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); }
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg',
    'image/png',
    'image/webp'
]);

define('ALLOWED_DOCUMENT_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);

/*
|--------------------------------------------------------------------------
| SESSION SECURITY - Delegated to helpers/session.php
|--------------------------------------------------------------------------
| Session initialization and security hardening is handled by
| helpers/session.php which is loaded below. This ensures
| consistent configuration with proper HTTPS detection.
| Do NOT duplicate session_start() here.
*/


/*
|--------------------------------------------------------------------------
| SECURITY HEADERS
|--------------------------------------------------------------------------
*/

if (function_exists('securityHeaders')) {
    securityHeaders();
}

/*
|--------------------------------------------------------------------------
| URL HELPERS
|--------------------------------------------------------------------------
*/

function base_url($path = '')
{
    // XAMPP installations are commonly served from a project subdirectory
    // (for example /KVN_Construction/public).  Prefer that detected local
    // web path so assets do not incorrectly point at the production domain
    // when a production .env file is present locally.
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    $isLocal = preg_match('/^(localhost|127\\.0\\.0\\.1|::1)(:\\d+)?$/i', $host) === 1;
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $publicPosition = stripos($scriptName, '/public/');

    if ($isLocal && $publicPosition !== false) {
        $localBase = substr($scriptName, 0, $publicPosition + strlen('/public'));
        return rtrim($localBase, '/') . '/' . ltrim((string) $path, '/');
    }

    return rtrim(APP_URL, '/') . '/' . ltrim((string) $path, '/');
}

function redirect($path = '')
{
    header('Location: ' . base_url($path));
    exit;
}

/*
|--------------------------------------------------------------------------
| AUTH HELPERS
|--------------------------------------------------------------------------
*/

function auth_user()
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in()
{
    return !empty($_SESSION['logged_in']);
}

function is_admin()
{
    return isset($_SESSION['role']) &&
        in_array(
            $_SESSION['role'],
            ['admin', 'super_admin'],
            true
        );
}

function is_client()
{
    return isset($_SESSION['role']) &&
        $_SESSION['role'] === 'client';
}

/*
|--------------------------------------------------------------------------
| ENVIRONMENT
|--------------------------------------------------------------------------
*/

function isProduction()
{
    return APP_ENV === 'production';
}

/*
|--------------------------------------------------------------------------
| REQUEST HELPERS
|--------------------------------------------------------------------------
*/

function current_url()
{
    $scheme =
        (!empty($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] !== 'off')
        ? 'https'
        : 'http';

    return $scheme .
        '://' .
        ($_SERVER['HTTP_HOST'] ?? '') .
        ($_SERVER['REQUEST_URI'] ?? '');
}

function request_method()
{
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

function request_ip()
{
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

function request_user_agent()
{
    return $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
}

function is_ajax_request()
{
    return !empty(
        $_SERVER['HTTP_X_REQUESTED_WITH']
    ) &&
    strtolower(
        $_SERVER['HTTP_X_REQUESTED_WITH']
    ) === 'xmlhttprequest';
}

/*
|--------------------------------------------------------------------------
| JSON RESPONSE
|--------------------------------------------------------------------------
*/

function json_response(
    $data = [],
    $status = 200
) {
    http_response_code($status);
    header('Content-Type: application/json');

    echo json_encode($data);

    exit;
}

/*
|--------------------------------------------------------------------------
| MAINTENANCE MODE
|--------------------------------------------------------------------------
*/
if (!defined('MAINTENANCE_MODE')) { define('MAINTENANCE_MODE', false); }
if (
    MAINTENANCE_MODE &&
    !is_admin()
) {
    die('<h1>Maintenance Mode</h1>');      
}
