<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| RESEND RESET OTP HANDLER (AJAX)
|--------------------------------------------------------------------------
*/

require_once '../../config/app.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/session.php';
require_once ROOT_PATH . '/helpers/csrf.php';
require_once ROOT_PATH . '/helpers/rateLimiter.php';

// Lazy-load mail helper if needed
if (function_exists('requireOtpHelpers')) {
    requireOtpHelpers();
} else {
    require_once ROOT_PATH . '/helpers/mail.php';
}

require_once ROOT_PATH . '/app/models/User.php';

use App\Models\User;

/*
|--------------------------------------------------------------------------
| RESPONSE HEADER
|--------------------------------------------------------------------------
*/

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

/*
|--------------------------------------------------------------------------
| CSRF VALIDATION
|--------------------------------------------------------------------------
*/

if (!validateCsrf($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token.']);
    exit;
}

/*
|--------------------------------------------------------------------------
| VALIDATE SESSION
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['password_reset_user_id']) || empty($_SESSION['password_reset_email'])) {
    echo json_encode(['success' => false, 'error' => 'Session expired. Please start over.']);
    exit;
}

$userId = (int) $_SESSION['password_reset_user_id'];
$email = $_SESSION['password_reset_email'];

/*
|--------------------------------------------------------------------------
| COOLDOWN / RATE LIMITING
|--------------------------------------------------------------------------
*/

$rateKey = 'resend_reset_otp_' . $userId;
if (!checkRateLimit($rateKey, 3, 300)) { // Max 3 resends per 5 mins
    echo json_encode(['success' => false, 'error' => 'Too many requests. Please wait.']);
    exit;
}

$lastResend = $_SESSION['password_reset_last_resend'] ?? 0;
if ((time() - $lastResend) < 60) {
    echo json_encode(['success' => false, 'error' => 'Please wait before requesting a new OTP.']);
    exit;
}

/*
|--------------------------------------------------------------------------
| GENERATE NEW OTP
|--------------------------------------------------------------------------
*/

$otp = random_int(100000, 999999);

$userModel = new User($GLOBALS['conn']);

$saved = $userModel->saveOtp($userId, (string)$otp, 'password_reset', 5);

if (!$saved) {
    echo json_encode(['success' => false, 'error' => 'Unable to generate new OTP.']);
    exit;
}

/*
|--------------------------------------------------------------------------
| SEND EMAIL
|--------------------------------------------------------------------------
*/

if (function_exists('sendPasswordResetEmail')) {
    $user = $userModel->findById($userId);
    $name = $user['full_name'] ?? 'User';
    sendPasswordResetEmail($email, (string)$otp, $name);
}

/*
|--------------------------------------------------------------------------
| UPDATE SESSION & LIMITS
|--------------------------------------------------------------------------
*/

$_SESSION['password_reset_last_resend'] = time();
$_SESSION['password_reset_created_at'] = time(); // Reset expiry timer
$_SESSION['password_reset_attempts'] = 0; // Reset attempts for new OTP

incrementRateLimit($rateKey);

if (function_exists('logSecurityEvent')) {
    logSecurityEvent('RESET_OTP_RESEND', [
        'user_id' => $userId,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);
}

echo json_encode(['success' => true]);
