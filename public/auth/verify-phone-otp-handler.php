<?php

declare(strict_types=1);

require_once '../../config/app.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/session.php';
require_once ROOT_PATH . '/app/repositories/UserRepository.php';
require_once ROOT_PATH . '/app/repositories/SessionRepository.php';
require_once ROOT_PATH . '/app/repositories/AuditRepository.php';
require_once ROOT_PATH . '/app/services/AuthService.php';
require_once ROOT_PATH . '/bootstrap/providers/ServiceProvider.php';

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

$userId = (int) ($_SESSION['otp_user_id'] ?? 0);
$otp = sanitize($_POST['otp'] ?? '');

if ($userId <= 0 || empty($otp)) {
    $_SESSION['error'] = 'Invalid OTP session.';
    redirect('verify-phone-otp.php');
}

$authService = ServiceProvider::get('AuthService');
$result = $authService->verifyOtpAndLogin($userId, $otp, 'login');

if (!$result['success']) {
    $_SESSION['error'] = $result['message'];
    redirect('verify-phone-otp.php');
}

$_SESSION['success'] = $result['message'];
redirect('client/dashboard.php');