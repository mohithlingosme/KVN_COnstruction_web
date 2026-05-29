<?php

declare(strict_types=1);

require_once '../../config/app.php';
require_once ROOT_PATH . '/app/services/AuthService.php';

if (request_method() !== 'POST') {
    redirect('login.php');
}

if (!isOtpSessionValid()) {
    $_SESSION['error'] = 'OTP session expired.';
    redirect('login.php');
}

if (!empty($_POST['website'])) {
    logSecurityEvent(null, 'otp_honeypot_triggered', 'warning', 'OTP honeypot field populated');
    $_SESSION['error'] = 'Invalid request.';
    redirect('verify-phone-otp.php');
}

$service = new AuthService();
$result = $service->verifyPhoneLoginOtp(
    (string) ($_SESSION['otp_phone'] ?? ''),
    (string) ($_POST['otp'] ?? '')
);

$_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];

if ($result['success']) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'client/dashboard.php');
}

redirect('verify-phone-otp.php');
