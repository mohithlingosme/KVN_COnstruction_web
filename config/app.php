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
| ERROR REPORTING
|--------------------------------------------------------------------------
*/

if (APP_ENV === 'development') {

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

} else {

    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', ROOT_PATH . '/storage/logs/error.log');
    error_reporting(E_ALL);
}

ini_set('html_errors', '0');

set_exception_handler(function (\Throwable $exception) {
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    
    if (APP_ENV !== 'development') {
        http_response_code(500);
        if (is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Internal Server Error']);
        } else {
            echo "<h1>500 Internal Server Error</h1><p>Something went wrong. Our team has been notified.</p>";
        }
        exit;
    }
    // In development, let the default handler print the stack trace if display_errors is on
    throw $exception;
});

set_error_handler(function ($level, $message, $file, $line) {
    if (error_reporting() & $level) {
        throw new \ErrorException($message, 0, $level, $file, $line);
    }
    return false;
});

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

$conn = null;

if (file_exists(CONFIG_PATH . '/database.php')) {

    require_once CONFIG_PATH . '/database.php';

    if (class_exists('Database')) {

        try {

            $db = new Database();
            $conn = $db->connect();

        } catch (Throwable $e) {

            error_log(
                'Database connection failed: ' .
                $e->getMessage()
            );

            $conn = null;
        }
    }
}

$GLOBALS['conn'] = $conn;

/*
|--------------------------------------------------------------------------
| LOAD HELPERS
|--------------------------------------------------------------------------
*/

// Load core helpers early.
// IMPORTANT: do NOT load mail/sms/otp runtime heavy helpers on every request.
// This reduces latency on public routes.
$helperFiles = [
    HELPER_PATH . '/functions.php',
    HELPER_PATH . '/formatter.php',
    HELPER_PATH . '/csrf.php',
    HELPER_PATH . '/session.php',
    HELPER_PATH . '/rateLimiter.php',
    HELPER_PATH . '/security.php',
    HELPER_PATH . '/seo.php',
    HELPER_PATH . '/upload.php',
];

foreach ($helperFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
    } else {
        error_log("Missing helper file: {$file}");
    }
}

// Lazy-load side-effectful helpers only when needed.
if (!function_exists('requireOtpHelpers')) {
    function requireOtpHelpers(): void {
        static $loaded = false;
        if ($loaded) return;
        $loaded = true;
        $files = [
            HELPER_PATH . '/otp.php',
            HELPER_PATH . '/mail.php',
            HELPER_PATH . '/sms.php',
        ];
        foreach ($files as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}

// Backward compatibility: if code calls these helpers, ensure lazy-load occurred.
// generateOtp() canonical is defined in helpers/otp.php.
// Backward compatibility wrapper removed to eliminate duplicate function declarations.



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
