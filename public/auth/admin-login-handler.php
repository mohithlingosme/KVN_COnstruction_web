<?php

declare(strict_types=1);

require_once '../../config/app.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/session.php';
require_once ROOT_PATH . '/app/controllers/auth/AdminAuthController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/login.php');
}

if (!verifyCsrfToken($_POST['_token'] ?? $_POST['csrf_token'] ?? null)) {
    $_SESSION['error'] = 'Invalid security token.';
    redirect('admin/login.php');
}

$controller = new AdminAuthController($conn);
$result = $controller->login(
    (string) ($_POST['email'] ?? ''),
    (string) ($_POST['password'] ?? '')
);

if (!$result['status']) {
    $_SESSION['error'] = $result['message'];
    redirect('admin/login.php');
}

$_SESSION['success'] = $result['message'];
redirect('admin/dashboard.php');
