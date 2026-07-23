<?php

declare(strict_types=1);

require_once '../../config/app.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/session.php';
require_once ROOT_PATH . '/app/controllers/auth/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

if (!verifyCsrfToken($_POST['_token'] ?? $_POST['csrf_token'] ?? null)) {
    $_SESSION['error'] = 'Invalid security token.';
    redirect('verify-phone-otp.php');
}

if (!empty($_POST['website'])) {
    $_SESSION['error'] = 'Invalid request.';
    redirect('login.php');
}

$phone = (string) ($_SESSION['otp_phone'] ?? '');

$controller = new AuthController($conn);
$result = $controller->verifyPhoneOtp($phone, (string) ($_POST['otp'] ?? ''));

if (!$result['status']) {
    $_SESSION['error'] = $result['message'];
    redirect('verify-phone-otp.php');
}

$_SESSION['success'] = $result['message'];
redirect('client/dashboard.php');
