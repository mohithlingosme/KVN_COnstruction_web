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

// FIX: Set success message BEFORE destroying session
$_SESSION['success'] = 'Logged out successfully.';

// Destroy the session via AuthController if available
$authControllerPath = ROOT_PATH . '/app/controllers/auth/AuthController.php';
if (file_exists($authControllerPath)) {
    require_once $authControllerPath;
    $controller = new AuthController($conn);
    $controller->logout();
} else {
    // Fallback: destroy session directly
    destroySession();
}

header('Location: ' . base_url('login.php'));
exit;