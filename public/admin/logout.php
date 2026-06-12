<?php

declare(strict_types=1);

require_once '../../config/app.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/session.php';
require_once ROOT_PATH . '/app/controllers/AdminAuthController.php';

$controller = new AdminAuthController($conn);
$controller->logout();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(SESSION_NAME);
    session_start();
}

$_SESSION['success'] = 'Admin logged out successfully.';

header('Location: ' . APP_URL . '/admin/login.php');
exit;
