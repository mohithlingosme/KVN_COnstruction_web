<?php

declare(strict_types=1);

require_once '../../config/app.php';
require_once ROOT_PATH . '/app/services/AuthService.php';
require_once ROOT_PATH . '/helpers/mail.php';
require_once ROOT_PATH . '/helpers/sms.php';

if (request_method() !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
}

if (!isOtpSessionValid()) {
    json_response(['success' => false, 'message' => 'OTP session expired.'], 422);
}

$service = new AuthService();
$result = $service->sendPhoneLoginOtp((string) ($_SESSION['otp_phone'] ?? ''));

json_response($result);
