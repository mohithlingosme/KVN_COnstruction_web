<?php

declare(strict_types=1);

require_once '../../config/app.php';
require_once ROOT_PATH . '/app/services/AuthService.php';
require_once ROOT_PATH . '/helpers/mail.php';
require_once ROOT_PATH . '/helpers/sms.php';

if (request_method() !== 'POST') {
    redirect('login.php');
}

$service = new AuthService();
$result = $service->sendPhoneLoginOtp((string) ($_POST['phone'] ?? ''));

$_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];

redirect($result['success'] ? 'verify-phone-otp.php' : 'login.php');
