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
require_once ROOT_PATH . '/public/includes/repositories.php';


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

// AuthController refers to User class without namespace; create an alias if needed.
if (!class_exists('User') && class_exists('App\Models\User')) {
    class_alias('App\Models\User', 'User');
}

// Simple autoloader for App\* classes without composer
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = ROOT_PATH . '/' . str_replace('\\', '/', $class) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

// Aliases for repository classes used in controllers/tests without namespace prefix
$repoAliases = [
    'UserRepository'       => 'App\Repositories\UserRepository',
    'LeadRepository'       => 'App\Repositories\LeadRepository',
    'ProjectRepository'    => 'App\Repositories\ProjectRepository',
    'QuotationRepository'  => 'App\Repositories\QuotationRepository',
    'BlogRepository'       => 'App\Repositories\BlogRepository',
    'MediaRepository'      => 'App\Repositories\MediaRepository',
    'PortfolioRepository'  => 'App\Repositories\PortfolioRepository',
    'ServiceRepository'    => 'App\Repositories\ServiceRepository',
    'TestimonialRepository'=> 'App\Repositories\TestimonialRepository',
    'VideoRepository'      => 'App\Repositories\VideoRepository',
    'ContentRepository'    => 'App\Repositories\ContentRepository',
    'SettingsRepository'   => 'App\Repositories\SettingsRepository',
    'CmsRepository'        => 'App\Repositories\CmsRepository',
'AuditRepository'      => 'App\Repositories\AuditRepository',
    'SessionRepository'    => 'App\Repositories\SessionRepository',
    'RateLimitRepository'  => 'App\Repositories\RateLimitRepository',
    'MailRepository'       => 'App\Repositories\MailRepository',
    'SmsRepository'        => 'App\Repositories\SmsRepository',
    'SupportRepository'    => 'App\Repositories\SupportRepository',
    'DashboardRepository'  => 'App\Repositories\DashboardRepository',
    'ReportRepository'     => 'App\Repositories\ReportRepository',
    'SecurityAdminRepository' => 'App\Repositories\SecurityAdminRepository',
    'EstimatorRepository'  => 'App\Repositories\EstimatorRepository',
    'ClientRepository'     => 'App\Repositories\ClientRepository',
    'InvoiceRepository'    => 'App\Repositories\InvoiceRepository',
];

foreach ($repoAliases as $global => $fqcn) {
    if (!class_exists($global) && class_exists($fqcn)) {
        class_alias($fqcn, $global);
    }
}

// Ensure auth controller class is loaded
$authControllerPath = ROOT_PATH . '/app/controllers/AuthController.php';
if (file_exists($authControllerPath)) {
    require_once $authControllerPath;
} else {
    $authControllerPath = ROOT_PATH . '/app/controllers/auth/AuthController.php';
    if (file_exists($authControllerPath)) {
        require_once $authControllerPath;
    }
}

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