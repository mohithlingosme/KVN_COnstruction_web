<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| AUTHENTICATION MIDDLEWARE
|--------------------------------------------------------------------------
| REFACTORED: SQL queries delegated to UserRepository and SessionRepository.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| LOAD APPLICATION
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/public/includes/repositories.php';

/*
|--------------------------------------------------------------------------
| VALIDATE ACTIVE SESSION
|--------------------------------------------------------------------------
*/

if (!validateSession()) {
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent(
            $_SESSION['user_id'] ?? null,
            'unauthorized_access',
            'warning',
            'Unauthenticated access attempt'
        );
    }

    destroySession();
    $_SESSION['error'] = 'Please login to continue.';
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| USER EXISTS
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    destroySession();
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| SESSION TIMEOUT CHECK
|--------------------------------------------------------------------------
*/

$timeout = isAdmin() ? ADMIN_SESSION_TIMEOUT : SESSION_TIMEOUT;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    logSecurityEvent(
        $_SESSION['user_id'],
        'session_timeout',
        'warning',
        'Session expired due to inactivity'
    );

    destroySession();
    $_SESSION['error'] = 'Session expired. Please login again.';
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| SESSION FINGERPRINT VALIDATION
|--------------------------------------------------------------------------
*/

$currentFingerprint = generateSessionFingerprint();

if (!isset($_SESSION['fingerprint']) || $_SESSION['fingerprint'] !== $currentFingerprint) {
    logSecurityEvent(
        $_SESSION['user_id'],
        'session_hijack_attempt',
        'critical',
        'Session fingerprint mismatch'
    );

    destroySession();
    $_SESSION['error'] = 'Security validation failed.';
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| USER STATUS VALIDATION (via UserRepository)
|--------------------------------------------------------------------------
*/

try {
    $userRepo = repo('User');
    if ($userRepo) {
        $user = $userRepo->findById((int)$_SESSION['user_id']);

        if (!$user) {
            logSecurityEvent($_SESSION['user_id'], 'invalid_user_session', 'critical', 'User account missing');
            destroySession();
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }

        if (($user['status'] ?? '') !== 'active') {
            logSecurityEvent($user['id'], 'blocked_user_access', 'warning', 'Inactive account tried accessing system');
            destroySession();
            $_SESSION['error'] = 'Account inactive.';
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }

        $_SESSION['user'] = $user;
    }
} catch (Exception $e) {
    error_log('Auth Middleware Error: ' . $e->getMessage());
    destroySession();
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| UPDATE LAST ACTIVITY
|--------------------------------------------------------------------------
*/

$_SESSION['last_activity'] = time();

/*
|--------------------------------------------------------------------------
| UPDATE DB SESSION ACTIVITY (via SessionRepository)
|--------------------------------------------------------------------------
*/

try {
    $sessionRepo = repo('Session');
    if ($sessionRepo && !empty($_SESSION['session_token'])) {
        $sessionRepo->updateActivityByToken((string)$_SESSION['session_token']);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
}

/*
|--------------------------------------------------------------------------
| OPTIONAL ADMIN ROUTE LOGGING
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin'], true)) {
    if (function_exists('logAdminAction')) {
        logAdminAction(
            $_SESSION['user_id'],
            'admin_route_access',
            current_url()
        );
    }
}

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER READY
|--------------------------------------------------------------------------
*/
