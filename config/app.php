<?php

declare(strict_types=1);

if (defined('APP_BOOTSTRAPPED')) {
    return;
}

define('APP_BOOTSTRAPPED', true);

ob_start();

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('HELPER_PATH', ROOT_PATH . '/helpers');
define('MIDDLEWARE_PATH', ROOT_PATH . '/middleware');
define('SERVICE_PATH', APP_PATH . '/services');

loadEnvironment(ROOT_PATH . '/.env');

date_default_timezone_set(env_value('APP_TIMEZONE', 'Asia/Kolkata'));

define('APP_NAME', env_value('APP_NAME', 'KVN Construction'));
define('APP_ENV', env_value('APP_ENV', 'development'));
define('APP_DEBUG', filter_var(env_value('APP_DEBUG', APP_ENV === 'production' ? '0' : '1'), FILTER_VALIDATE_BOOL));
define('APP_URL', rtrim(env_value('APP_URL', 'http://localhost/KVN_Construction/public'), '/'));
define('APP_KEY', env_value('APP_KEY', 'kvn-construction-dev-key'));

define('DB_HOST', env_value('DB_HOST', 'localhost'));
define('DB_NAME', env_value('DB_NAME', 'kvnc_platform'));
define('DB_USER', env_value('DB_USER', 'root'));
define('DB_PASS', env_value('DB_PASS', ''));
define('DB_CHARSET', env_value('DB_CHARSET', 'utf8mb4'));

define('SESSION_NAME', env_value('SESSION_NAME', 'KVNSESSID'));
define('SESSION_TIMEOUT', (int) env_value('SESSION_TIMEOUT', '3600'));
define('ADMIN_SESSION_TIMEOUT', (int) env_value('ADMIN_SESSION_TIMEOUT', '1800'));
define('REMEMBER_ME_DAYS', (int) env_value('REMEMBER_ME_DAYS', '30'));

define('OTP_EXPIRY_MINUTES', (int) env_value('OTP_EXPIRY_MINUTES', '5'));
define('OTP_MAX_ATTEMPTS', (int) env_value('OTP_MAX_ATTEMPTS', '5'));
define('OTP_RESEND_LIMIT', (int) env_value('OTP_RESEND_LIMIT', '3'));
define('OTP_RESEND_COOLDOWN', (int) env_value('OTP_RESEND_COOLDOWN', '60'));

define('LOGIN_RATE_LIMIT', (int) env_value('LOGIN_RATE_LIMIT', '5'));
define('LOGIN_RATE_WINDOW', (int) env_value('LOGIN_RATE_WINDOW', '300'));
define('OTP_RATE_LIMIT', (int) env_value('OTP_RATE_LIMIT', '3'));
define('OTP_RATE_WINDOW', (int) env_value('OTP_RATE_WINDOW', '600'));

define('MAX_UPLOAD_SIZE', (int) env_value('MAX_UPLOAD_SIZE', (string) (10 * 1024 * 1024)));
define('MAX_IMAGE_SIZE', (int) env_value('MAX_IMAGE_SIZE', (string) (5 * 1024 * 1024)));
define('MAX_DOCUMENT_SIZE', (int) env_value('MAX_DOCUMENT_SIZE', (string) (10 * 1024 * 1024)));

define('LOG_PATH', STORAGE_PATH . '/logs');
define('CACHE_PATH', STORAGE_PATH . '/cache');
define('PRIVATE_UPLOAD_PATH', STORAGE_PATH . '/private');

ensure_directory(STORAGE_PATH);
ensure_directory(LOG_PATH);
ensure_directory(CACHE_PATH);
ensure_directory(PRIVATE_UPLOAD_PATH);
ensure_directory(UPLOAD_PATH);

configure_runtime();

require_once CONFIG_PATH . '/database.php';
require_once HELPER_PATH . '/security.php';
require_once HELPER_PATH . '/csrf.php';
require_once HELPER_PATH . '/rateLimiter.php';
require_once HELPER_PATH . '/session.php';
require_once HELPER_PATH . '/permissions.php';
require_once HELPER_PATH . '/otp.php';

$db = new Database();
$conn = $db->connect();

securityHeaders();
validateCsrf();
restoreRememberedSession();

function loadEnvironment(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($key === '') {
            continue;
        }

        $value = trim($value, "\"'");

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}

function env_value(string $key, ?string $default = null): ?string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return (string) $value;
}

function configure_runtime(): void
{
    ini_set('default_charset', 'UTF-8');
    ini_set('log_errors', '1');
    ini_set('error_log', LOG_PATH . '/php-error.log');
    ini_set('display_errors', APP_DEBUG ? '1' : '0');
    ini_set('display_startup_errors', APP_DEBUG ? '1' : '0');

    error_reporting(APP_DEBUG ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);

    set_exception_handler(static function (Throwable $exception): void {
        logApplicationError('exception', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        http_response_code(500);

        if (APP_DEBUG) {
            echo nl2br(escape($exception->getMessage()));
            return;
        }

        echo 'Application error.';
    });

    set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        logApplicationError('php_error', [
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line,
        ]);

        return !APP_DEBUG;
    });

    register_shutdown_function(static function (): void {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        $fatalLevels = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE];

        if (!in_array($error['type'], $fatalLevels, true)) {
            return;
        }

        logApplicationError('shutdown', $error);
    });
}

function ensure_directory(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

function logApplicationError(string $type, array $payload): void
{
    ensure_directory(LOG_PATH);

    $logFile = LOG_PATH . '/app-' . date('Y-m-d') . '.log';
    $record = [
        'timestamp' => date('c'),
        'type' => $type,
        'uri' => $_SERVER['REQUEST_URI'] ?? null,
        'method' => $_SERVER['REQUEST_METHOD'] ?? null,
        'payload' => $payload,
    ];

    error_log(json_encode($record, JSON_UNESCAPED_SLASHES) . PHP_EOL, 3, $logFile);
}

function base_url(string $path = ''): string
{
    return APP_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . base_url($path));
    exit;
}

function current_url(): string
{
    $scheme = request_is_secure() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    return $scheme . '://' . $host . $uri;
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function request_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

function request_user_agent(): string
{
    return $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
}

function request_is_secure(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }

    if ((int) ($_SERVER['SERVER_PORT'] ?? 80) === 443) {
        return true;
    }

    return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

function isProduction(): bool
{
    return APP_ENV === 'production';
}

function is_ajax_request(): bool
{
    return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
}

function json_response(array $data = [], int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isLoggedIn();
}

function is_admin(): bool
{
    return isAdmin();
}

function is_client(): bool
{
    return isClient();
}

function site_setting(string $key, $default = null)
{
    global $conn;

    if (!isset($conn)) {
        return $default;
    }

    static $settings = null;

    if ($settings === null) {
        $settings = [];

        try {
            foreach ($conn->query('SELECT setting_key, setting_value FROM site_settings') as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $exception) {
            $settings = [];
        }
    }

    return $settings[$key] ?? $default;
}
