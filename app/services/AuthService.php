<?php

declare(strict_types=1);

require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/helpers/mail.php';
require_once ROOT_PATH . '/helpers/sms.php';

class AuthService
{
    public function adminLogin(string $email, string $password, bool $rememberMe = false): array
    {
        if (!checkRateLimit('admin_login', 3, 600)) {
            return ['success' => false, 'message' => 'Too many login attempts. Please try again later.'];
        }

        $user = User::findByEmail($email);

        if (!$user || ($user['role'] ?? null) !== 'admin') {
            incrementRateLimit('admin_login');
            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        if (($user['status'] ?? 'inactive') !== 'active') {
            return ['success' => false, 'message' => 'Account inactive.'];
        }

        if (!empty($user['locked_until']) && strtotime((string) $user['locked_until']) > time()) {
            return ['success' => false, 'message' => 'Account temporarily locked.'];
        }

        if (!verifyPassword($password, (string) $user['password'])) {
            User::incrementFailedAttempts((int) $user['id']);
            incrementRateLimit('admin_login');
            logSecurityEvent((int) $user['id'], 'invalid_admin_login', 'warning', ['email' => $email]);
            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        User::resetAttempts((int) $user['id']);
        initializeSessionSecurity($user, $rememberMe);
        User::updateLastLogin((int) $user['id']);
        clearRateLimit('admin_login');

        sendAdminLoginAlert(
            $user['email'] ?? '',
            $user['full_name'],
            request_ip(),
            sessionDeviceName()
        );

        logAdminAction((int) $user['id'], 'admin_login', 'Admin logged in');

        return ['success' => true, 'message' => 'Login successful.'];
    }

    public function sendPhoneLoginOtp(string $phone): array
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (!validatePhone($phone)) {
            return ['success' => false, 'message' => 'Enter a valid phone number.'];
        }

        if (!checkRateLimit('client_otp', 3, 600)) {
            return ['success' => false, 'message' => 'Too many OTP requests. Try again later.'];
        }

        $user = User::findByPhone($phone);

        if (!$user || ($user['status'] ?? 'inactive') !== 'active') {
            incrementRateLimit('client_otp');
            return ['success' => false, 'message' => 'Account not found or inactive.'];
        }

        $otp = generateOtp();
        User::saveOtp((int) $user['id'], $otp, 'login');

        createOtpSession($phone);
        $_SESSION['otp_user_id'] = (int) $user['id'];

        sendOtpSms($phone, $otp);

        if (!empty($user['email'])) {
            sendOtpEmail($user['email'], $otp, $user['full_name']);
        }

        incrementRateLimit('client_otp');

        return ['success' => true, 'message' => 'OTP sent successfully.'];
    }

    public function verifyPhoneLoginOtp(string $phone, string $otp): array
    {
        $phone = preg_replace('/\D/', '', $phone);
        $user = User::findByPhone($phone);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        if (!verifyStoredOtp((int) $user['id'], $phone, $user['email'] ?? null, $otp, 'login')) {
            $_SESSION['otp_attempts'] = ((int) ($_SESSION['otp_attempts'] ?? 0)) + 1;
            return ['success' => false, 'message' => 'Invalid or expired OTP.'];
        }

        initializeSessionSecurity($user, false);
        User::updateLastLogin((int) $user['id']);
        destroyOtpSession();
        unset($_SESSION['otp_user_id']);

        return ['success' => true, 'message' => 'OTP verified successfully.'];
    }

    public function sendEmailVerification(int $userId): bool
    {
        global $conn;

        $user = User::findById($userId);

        if (!$user || empty($user['email'])) {
            return false;
        }

        $token = generateSecureToken(80);
        $tokenHash = secureHash($token);

        $conn->prepare('DELETE FROM email_verification_tokens WHERE user_id = :user_id')->execute([
            ':user_id' => $userId,
        ]);

        $conn->prepare(
            'INSERT INTO email_verification_tokens (user_id, token_hash, expires_at, created_at)
             VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 1 DAY), NOW())'
        )->execute([
            ':user_id' => $userId,
            ':token_hash' => $tokenHash,
        ]);

        if (function_exists('sendEmailVerificationEmail')) {
            return sendEmailVerificationEmail($user['email'], $user['full_name'], $token);
        }

        return false;
    }
}
