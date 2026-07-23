<?php

declare(strict_types=1);

require_once '../../config/app.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/session.php';
require_once ROOT_PATH . '/app/controllers/auth/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register.php');
}

if (!verifyCsrfToken($_POST['_token'] ?? $_POST['csrf_token'] ?? null)) {
    $_SESSION['error'] = 'Invalid security token.';
    redirect('register.php');
}

$controller = new AuthController($conn);
$result = $controller->register($_POST);

if (!$result['status']) {
    $_SESSION['error'] = $result['message'];
    redirect('register.php');
}

$_SESSION['success'] = $result['message'];
redirect('login.php');
