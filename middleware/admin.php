<?php

/*
|--------------------------------------------------------------------------
| LOCAL TESTING BYPASS
|--------------------------------------------------------------------------
| Set ADMIN_AUTH_BYPASS_FOR_TESTING=true only in the local web-server
| environment. The bypass is deliberately limited to loopback requests, so it
| cannot disable administrator authentication for remote traffic.
*/
$adminTestingBypass = filter_var(
    getenv('ADMIN_AUTH_BYPASS_FOR_TESTING') ?: 'false',
    FILTER_VALIDATE_BOOLEAN
);
$adminTestingIp = $_SERVER['REMOTE_ADDR'] ?? '';

if ($adminTestingBypass && in_array($adminTestingIp, ['127.0.0.1', '::1'], true)) {
    return;
}

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ADMIN SECURITY MIDDLEWARE
|--------------------------------------------------------------------------
| REFACTORED: SQL queries delegated to UserRepository and SessionRepository.
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__) . '/config/app.php';

/*
|--------------------------------------------------------------------------
| LOAD HELPERS
|--------------------------------------------------------------------------
*/

require_once HELPER_PATH . '/session.php';
require_once HELPER_PATH . '/security.php';
require_once HELPER_PATH . '/csrf.php';

/*
|--------------------------------------------------------------------------
| LOAD REPOSITORIES
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__) . '/public/includes/repositories.php';

/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
*/

if (!defined('STRICT_ADMIN_IP_CHECK')) {
    define('STRICT_ADMIN_IP_CHECK', false);
}

if (!defined('STRICT_ADMIN_AGENT_CHECK')) {
    define('STRICT_ADMIN_AGENT_CHECK', false);
}

/*
|--------------------------------------------------------------------------
| SESSION VALIDATION
|--------------------------------------------------------------------------
*/

if (!validateSession()) {
    logSecurityEvent(
        $_SESSION['user_id'] ?? null,
        'invalid_admin_session',
        'warning',
        'Invalid admin session detected'
    );

    destroySession();
    $_SESSION['error'] = 'Session expired.';
    header('Location: ' . base_url('admin/login.php'));
    exit;
}

/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (!isLoggedIn()) {
    logSecurityEvent(
        null,
        'unauthenticated_admin_access',
        'warning',
        'Unauthenticated admin route access'
    );

    header('Location: ' . base_url('admin/login.php'));
    exit;
}

/*
|--------------------------------------------------------------------------
| ROLE CHECK
|--------------------------------------------------------------------------
*/

if (!isAdmin()) {
    logSecurityEvent(
        currentUserId(),
        'non_admin_access_attempt',
        'critical',
        'Non-admin attempted admin access'
    );

    destroySession();
    header('Location: ' . base_url('login.php'));
    exit;
}

/*
|--------------------------------------------------------------------------
| ADMIN SESSION ISOLATION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    logSecurityEvent(
        currentUserId(),
        'invalid_admin_session_flag',
        'critical',
        'Admin isolation failed'
    );

    destroySession();
    header('Location: ' . base_url('admin/login.php'));
    exit;
}

/*
|--------------------------------------------------------------------------
| DATABASE USER VALIDATION (via UserRepository)
|--------------------------------------------------------------------------
*/

try {
    $userRepo = repo('User');
    if ($userRepo) {
        $admin = $userRepo->findById((int)currentUserId());

        if (!$admin) {
            logSecurityEvent(currentUserId(), 'admin_account_missing', 'critical', 'Admin account deleted during session');
            destroySession();
            header('Location: ' . base_url('admin/login.php'));
            exit;
        }

        if (($admin['status'] ?? '') !== 'active') {
            logSecurityEvent($admin['id'], 'inactive_admin_access', 'critical', 'Inactive admin attempted access');
            destroySession();
            header('Location: ' . base_url('admin/login.php'));
            exit;
        }

        if (!empty($admin['locked_until']) && strtotime($admin['locked_until']) > time()) {
            logSecurityEvent($admin['id'], 'locked_admin_access', 'critical', 'Locked admin attempted access');
            destroySession();
            header('Location: ' . base_url('admin/login.php'));
            exit;
        }
    }
} catch (Exception $e) {
    error_log('Admin Middleware Error: ' . $e->getMessage());
    destroySession();
    header('Location: ' . base_url('admin/login.php'));
    exit;
}

/*
|--------------------------------------------------------------------------
| ADMIN SESSION DATABASE VALIDATION (via SessionRepository)
|--------------------------------------------------------------------------
*/

try {
    $sessionRepo = repo('Session');
    if ($sessionRepo && !empty($_SESSION['session_token'])) {
        $adminSession = $sessionRepo->findByToken((string)$_SESSION['session_token']);
        if (!$adminSession) {
            logSecurityEvent(currentUserId(), 'admin_session_missing', 'critical', 'Admin DB session missing');
            destroySession();
            header('Location: ' . base_url('admin/login.php'));
            exit;
        }
    }
} catch (Exception $e) {
    error_log($e->getMessage());
}

/*
|--------------------------------------------------------------------------
| OPTIONAL STRICT IP VALIDATION
|--------------------------------------------------------------------------
*/

if (STRICT_ADMIN_IP_CHECK) {
    if (isset($_SESSION['admin_ip']) && $_SESSION['admin_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
        logSecurityEvent(currentUserId(), 'admin_ip_mismatch', 'critical', 'Admin IP mismatch');
        destroySession();
        header('Location: ' . APP_URL . '/admin/login.php');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| OPTIONAL STRICT USER AGENT VALIDATION
|--------------------------------------------------------------------------
*/

if (STRICT_ADMIN_AGENT_CHECK) {
    if (isset($_SESSION['admin_user_agent']) && $_SESSION['admin_user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
        logSecurityEvent(currentUserId(), 'admin_agent_mismatch', 'critical', 'Admin browser mismatch');
        destroySession();
        header('Location: ' . APP_URL . '/admin/login.php');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| REFRESH SESSION
|--------------------------------------------------------------------------
*/

refreshSession();

/*
|--------------------------------------------------------------------------
| SECURITY HEADERS
|--------------------------------------------------------------------------
*/

securityHeaders();

/*
|--------------------------------------------------------------------------
| AUTO GENERATE CSRF TOKEN
|--------------------------------------------------------------------------
*/

generateCsrfToken();

/*
|--------------------------------------------------------------------------
| ADMIN ROUTE AUDIT LOG
|--------------------------------------------------------------------------
*/

logAdminAction(
    currentUserId(),
    'admin_route_access',
    current_url()
);

/*
|--------------------------------------------------------------------------
| CURRENT ADMIN OBJECT
|--------------------------------------------------------------------------
*/

$currentAdmin = [
    'id'    => $admin['id'] ?? null,
    'name'  => $admin['full_name'] ?? '',
    'email' => $admin['email'] ?? '',
    'role'  => $admin['role'] ?? '',
];

/*
|--------------------------------------------------------------------------
| ADMIN AUTHORIZED
|--------------------------------------------------------------------------
*/
