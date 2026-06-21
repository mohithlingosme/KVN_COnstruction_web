    <?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| GLOBAL APPLICATION CONFIGURATION
|--------------------------------------------------------------------------
*/

ob_start();

/*
|--------------------------------------------------------------------------
| APPLICATION INFO
|--------------------------------------------------------------------------
*/

define('APP_NAME', 'KVN Construction');
define('APP_URL', 'http://localhost/kvn_construction/public');
define('APP_ENV', 'development');

/*
|--------------------------------------------------------------------------
| ROOT PATHS
|--------------------------------------------------------------------------
*/

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('HELPER_PATH', ROOT_PATH . '/helpers');
define('MIDDLEWARE_PATH', ROOT_PATH . '/middleware');

/*
|--------------------------------------------------------------------------
| DATABASE CONFIG
|--------------------------------------------------------------------------
*/

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'kvnc_platform');
define('DB_USER', 'root');
define('DB_PASS', '');

/*
|--------------------------------------------------------------------------
| APPLICATION SETTINGS
|--------------------------------------------------------------------------
*/

date_default_timezone_set('Asia/Kolkata');

define('APP_DEBUG', true);

/*
|--------------------------------------------------------------------------
| SESSION SETTINGS
|--------------------------------------------------------------------------
*/

define('SESSION_TIMEOUT', 3600);
define('ADMIN_SESSION_TIMEOUT', 1800);
define('SESSION_NAME', 'KVNSESSID');

/*
|--------------------------------------------------------------------------
| OTP SETTINGS
|--------------------------------------------------------------------------
*/

define('OTP_EXPIRY_MINUTES', 5);
define('OTP_MAX_ATTEMPTS', 3);
define('OTP_RESEND_LIMIT', 3);
define('OTP_BLOCK_MINUTES', 15);

/*
|--------------------------------------------------------------------------
| RATE LIMIT SETTINGS
|--------------------------------------------------------------------------
*/

define('LOGIN_RATE_LIMIT', 5);
define('LOGIN_RATE_WINDOW', 300);

define('OTP_RATE_LIMIT', 3);
define('OTP_RATE_WINDOW', 600);

/*
|--------------------------------------------------------------------------
| FILE UPLOAD SETTINGS
|--------------------------------------------------------------------------
*/

define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

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
| SESSION SECURITY
|--------------------------------------------------------------------------
*/

ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);

session_name(SESSION_NAME);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

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
    return APP_URL . '/' . ltrim($path, '/');
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

define('MAINTENANCE_MODE', false);

if (
    MAINTENANCE_MODE &&
    !is_admin()
) {
    die('<h1>Maintenance Mode</h1>');      
}
