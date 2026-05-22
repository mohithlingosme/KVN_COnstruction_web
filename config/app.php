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

define(
    'APP_URL',
    'http://localhost/KVN_Construction/public'
);

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

define('DB_HOST', 'localhost');

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

/*
|--------------------------------------------------------------------------
| ENABLE HTTPS COOKIE ON LIVE SERVER
|--------------------------------------------------------------------------
*/

ini_set('session.cookie_secure', 0);

/*
|--------------------------------------------------------------------------
| SESSION NAME
|--------------------------------------------------------------------------
*/

session_name(SESSION_NAME);

/*
|--------------------------------------------------------------------------
| START SESSION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    session_start();
}

/*
|--------------------------------------------------------------------------
| SESSION REGENERATION
|--------------------------------------------------------------------------
*/

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

    ini_set('display_errors', 1);

    ini_set('display_startup_errors', 1);

    error_reporting(E_ALL);

} else {

    ini_set('display_errors', 0);

    error_reporting(0);
}

/*
|--------------------------------------------------------------------------
| LOAD DATABASE
|--------------------------------------------------------------------------
*/

require_once CONFIG_PATH . '/database.php';

$db = new Database();

$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| LOAD HELPERS
|--------------------------------------------------------------------------
*/

require_once HELPER_PATH . '/security.php';

require_once HELPER_PATH . '/csrf.php';

require_once HELPER_PATH . '/session.php';

require_once HELPER_PATH . '/rateLimiter.php';

/*
|--------------------------------------------------------------------------
| SECURITY HEADERS
|--------------------------------------------------------------------------
*/

securityHeaders();

/*
|--------------------------------------------------------------------------
| BASE URL
|--------------------------------------------------------------------------
*/

function base_url($path = '')
{
    return APP_URL . '/' . ltrim($path, '/');
}

/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/

function redirect($path)
{
    header(

        'Location: '
        .
        base_url($path)
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| AUTH USER
|--------------------------------------------------------------------------
*/

function auth_user()
{
    return $_SESSION['user'] ?? null;
}

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

function is_logged_in()
{
    return isset($_SESSION['logged_in']);
}

/*
|--------------------------------------------------------------------------
| ADMIN CHECK
|--------------------------------------------------------------------------
*/

function is_admin()
{
    return (

        isset($_SESSION['role'])

        &&

        $_SESSION['role'] === 'admin'
    );
}

/*
|--------------------------------------------------------------------------
| CLIENT CHECK
|--------------------------------------------------------------------------
*/

function is_client()
{
    return (

        isset($_SESSION['role'])

        &&

        $_SESSION['role'] === 'client'
    );
}

/*
|--------------------------------------------------------------------------
| APP ENV CHECK
|--------------------------------------------------------------------------
*/

function isProduction()
{
    return APP_ENV === 'production';
}

/*
|--------------------------------------------------------------------------
| CURRENT URL
|--------------------------------------------------------------------------
*/

function current_url()
{
    return

        (
            isset($_SERVER['HTTPS'])
            ? 'https'
            : 'http'
        )

        .

        '://'

        .

        $_SERVER['HTTP_HOST']

        .

        $_SERVER['REQUEST_URI'];
}

/*
|--------------------------------------------------------------------------
| REQUEST METHOD
|--------------------------------------------------------------------------
*/

function request_method()
{
    return $_SERVER['REQUEST_METHOD'];
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
| AJAX REQUEST CHECK
|--------------------------------------------------------------------------
*/

function is_ajax_request()
{
    return (

        !empty(
            $_SERVER['HTTP_X_REQUESTED_WITH']
        )

        &&

        strtolower(
            $_SERVER['HTTP_X_REQUESTED_WITH']
        ) === 'xmlhttprequest'
    );
}

/*
|--------------------------------------------------------------------------
| REQUEST IP
|--------------------------------------------------------------------------
*/

function request_ip()
{
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

/*
|--------------------------------------------------------------------------
| REQUEST USER AGENT
|--------------------------------------------------------------------------
*/

function request_user_agent()
{
    return $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
}

/*
|--------------------------------------------------------------------------
| MAINTENANCE MODE
|--------------------------------------------------------------------------
*/

define('MAINTENANCE_MODE', false);

if (

    MAINTENANCE_MODE

    &&

    !is_admin()
) {

    die(

        '<h1>Maintenance Mode</h1>'
    );
}
?>