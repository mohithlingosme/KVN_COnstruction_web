<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| LOGOUT SYSTEM
|--------------------------------------------------------------------------
| File:
| /public/logout.php
|--------------------------------------------------------------------------
*/

require_once '../config/app.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/session.php';
require_once ROOT_PATH . '/app/controllers/auth/AuthController.php';

$controller = new AuthController($conn);
$controller->logout();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(SESSION_NAME);
    session_start();
}

$_SESSION['success'] = 'Logged out successfully.';

header('Location: ' . base_url('login.php'));
exit;
