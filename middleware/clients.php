<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CLIENT ACCESS MIDDLEWARE
|--------------------------------------------------------------------------
| File: /middleware/client.php
|--------------------------------------------------------------------------
| REFACTORED: SQL queries delegated to UserRepository.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| LOAD AUTH MIDDLEWARE
|--------------------------------------------------------------------------
*/

require_once dirname(__FILE__) . '/auth.php';

/*
|--------------------------------------------------------------------------
| CLIENT ROLE VALIDATION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent($_SESSION['user_id'] ?? null, 'unauthorized_client_access', 'warning', 'Non-client attempted to access client portal');
    }

    $_SESSION['error'] = 'Unauthorized access.';

    if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin'], true)) {
        header('Location: ' . APP_URL . '/admin/dashboard.php');
    } else {
        destroySession();
        header('Location: ' . APP_URL . '/login.php');
    }
    exit;
}

/*
|--------------------------------------------------------------------------
| CLIENT STATUS VALIDATION (via UserRepository)
|--------------------------------------------------------------------------
*/

try {
    $userRepo = repo('User');
    if ($userRepo) {
        $client = $userRepo->findById((int)$_SESSION['user_id']);

        if (!$client) {
            logSecurityEvent($_SESSION['user_id'], 'client_account_missing', 'critical', 'Client account deleted during session');
            destroySession();
            $_SESSION['error'] = 'Account not found.';
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }

        if ($client['status'] !== 'active') {
            logSecurityEvent($client['id'], 'inactive_client_access', 'warning', 'Inactive client attempted access');
            destroySession();
            $_SESSION['error'] = 'Account inactive.';
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }

        $_SESSION['client'] = [
            'id' => $client['id'],
            'name' => $client['full_name'],
            'email' => $client['email'],
            'phone' => $client['phone'] ?? '',
        ];
    }
} catch (Exception $e) {
    error_log('Client Middleware Error: ' . $e->getMessage());
    destroySession();
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| OPTIONAL CLIENT ROUTE LOGGING
|--------------------------------------------------------------------------
*/

if (function_exists('logSecurityEvent')) {
    logSecurityEvent($_SESSION['user_id'], 'client_portal_access', 'info', current_url());
}

/*
|--------------------------------------------------------------------------
| CLIENT AUTHORIZED
|--------------------------------------------------------------------------
*/
