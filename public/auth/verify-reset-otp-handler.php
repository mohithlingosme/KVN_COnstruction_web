<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| VERIFY RESET OTP HANDLER
|--------------------------------------------------------------------------
*/

require_once '../../config/app.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/session.php';
require_once ROOT_PATH . '/helpers/csrf.php';
require_once ROOT_PATH . '/helpers/rateLimiter.php';
require_once ROOT_PATH . '/app/models/User.php';

use App\Models\User;

/*
|--------------------------------------------------------------------------
| METHOD CHECK
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

/*
|--------------------------------------------------------------------------
| CSRF VALIDATION
|--------------------------------------------------------------------------
*/

if (!validateCsrf($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid security token.';
    redirect('verify-reset-otp.php');
}

/*
|--------------------------------------------------------------------------
| HONEYPOT
|--------------------------------------------------------------------------
*/

if (!empty($_POST['website'])) {
    redirect('login.php'); // Silently fail for bots
}

/*
|--------------------------------------------------------------------------
| RATE LIMIT
|--------------------------------------------------------------------------
*/

$rateKey = 'verify_reset_otp_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!checkRateLimit($rateKey, OTP_RATE_LIMIT, OTP_RATE_WINDOW)) {
    $_SESSION['error'] = 'Too many attempts. Please try again later.';
    redirect('verify-reset-otp.php');
}

/*
|--------------------------------------------------------------------------
| VALIDATE SESSION
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['password_reset_user_id'])) {
    $_SESSION['error'] = 'Session expired. Please request a new OTP.';
    redirect('forgot-password.php');
}

$userId = (int) $_SESSION['password_reset_user_id'];
$otp = sanitize($_POST['otp'] ?? '');

if (empty($otp) || strlen($otp) !== 6 || !ctype_digit($otp)) {
    $_SESSION['error'] = 'Please enter a valid 6-digit OTP.';
    redirect('verify-reset-otp.php');
}

/*
|--------------------------------------------------------------------------
| MAX ATTEMPTS
|--------------------------------------------------------------------------
*/

$attempts = (int) ($_SESSION['password_reset_attempts'] ?? 0);
$maxAttempts = defined('OTP_MAX_ATTEMPTS') ? OTP_MAX_ATTEMPTS : 3;

if ($attempts >= $maxAttempts) {
    unset($_SESSION['password_reset_user_id']);
    unset($_SESSION['password_reset_email']);
    unset($_SESSION['password_reset_created_at']);
    unset($_SESSION['password_reset_attempts']);
    
    $_SESSION['error'] = 'Maximum OTP attempts exceeded. Please start over.';
    redirect('forgot-password.php');
}

/*
|--------------------------------------------------------------------------
| VERIFY OTP
|--------------------------------------------------------------------------
*/

$userModel = new User($GLOBALS['conn']);

if ($userModel->verifyOtp($userId, $otp, 'password_reset')) {
    // Success
    unset($_SESSION['password_reset_attempts']);
    $_SESSION['password_reset_verified'] = true;
    
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent('RESET_OTP_VERIFIED', [
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    }
    
    incrementRateLimit($rateKey);
    
    redirect('reset-password.php');
    
} else {
    // Failed
    $_SESSION['password_reset_attempts'] = $attempts + 1;
    incrementRateLimit($rateKey);
    
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent('RESET_OTP_FAILED', [
            'user_id' => $userId,
            'attempt' => $_SESSION['password_reset_attempts'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    }
    
    $_SESSION['error'] = 'Invalid OTP. You have ' . ($maxAttempts - $_SESSION['password_reset_attempts']) . ' attempts left.';
    redirect('verify-reset-otp.php');
}
