<?php

declare(strict_types=1);

use App\Models\User;

require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/helpers/session.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/rateLimiter.php';
require_once ROOT_PATH . '/helpers/sms.php';

class AuthController
{
    private PDO $conn;
    private User $users;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
        $this->users = new User($conn);
    }

    public function sendLoginOtp(string $phone): array
    {
        $phone = preg_replace('/\D+/', '', (string) $phone);

        if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
            return [
                'status' => false,
                'message' => 'Enter a valid 10 digit mobile number.'
            ];
        }

        if (!checkRateLimit('client_otp', 3, 600)) {
            return [
                'status' => false,
                'message' => 'Too many OTP requests. Try again later.'
            ];
        }

        $user = $this->users->findByPhone($phone);

        if (
            !$user ||
            ($user['status'] ?? '') !== 'active' ||
            !in_array($user['role'] ?? '', ['client', 'user'], true)
        ) {
            return [
                'status' => false,
                'message' => 'Unable to process request.'
            ];
        }

        $otp = (string) random_int(100000, 999999);

        if (!$this->users->saveOtp((int) $user['id'], $otp, 'login', OTP_EXPIRY_MINUTES)) {
            return [
                'status' => false,
                'message' => 'Unable to send OTP right now.'
            ];
        }

        $_SESSION['otp_phone'] = $phone;
        $_SESSION['otp_created_at'] = time();
        $_SESSION['otp_attempts'] = 0;

        $this->sendOtp($phone, $otp, $user);

        return [
            'status' => true,
            'message' => 'OTP sent successfully.'
        ];
    }

    public function verifyPhoneOtp(string $phone, string $otp): array
    {
        $phone = preg_replace('/\D+/', '', (string) $phone);
        $otp = preg_replace('/\D+/', '', (string) $otp);

        if (!preg_match('/^[0-9]{6}$/', $otp)) {
            return [
                'status' => false,
                'message' => 'Enter a valid 6 digit OTP.'
            ];
        }

        $user = $this->users->findByPhone($phone);

        if (
            !$user ||
            ($user['status'] ?? '') !== 'active' ||
            !in_array($user['role'] ?? '', ['client', 'user'], true)
        ) {
            return [
                'status' => false,
                'message' => 'Invalid OTP.'
            ];
        }

        if (!$this->users->verifyOtp((int) $user['id'], $otp, 'login')) {
            $_SESSION['otp_attempts'] = (int) ($_SESSION['otp_attempts'] ?? 0) + 1;

            return [
                'status' => false,
                'message' => 'Invalid or expired OTP.'
            ];
        }

        initializeSessionSecurity($user);
        $this->users->updateLastLogin((int) $user['id']);

        unset(
            $_SESSION['otp_phone'],
            $_SESSION['otp_created_at'],
            $_SESSION['otp_attempts']
        );

        if (function_exists('logSecurityEvent')) {
            logSecurityEvent((int) $user['id'], 'client_login', 'info', 'Client logged in');
        }

        return [
            'status' => true,
            'message' => 'Login successful.'
        ];
    }

    public function register(array $data): array
    {
        $fullName = trim((string) sanitize($data['full_name'] ?? ''));
        $email = strtolower(trim((string) sanitize($data['email'] ?? '')));
        $phone = preg_replace('/\D+/', '', (string) ($data['phone'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $confirmPassword = (string) ($data['confirm_password'] ?? '');

        if ($fullName === '' || $email === '' || $phone === '' || $password === '') {
            return [
                'status' => false,
                'message' => 'Please fill all required fields.'
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => false,
                'message' => 'Enter a valid email address.'
            ];
        }

        if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
            return [
                'status' => false,
                'message' => 'Enter a valid 10 digit mobile number.'
            ];
        }

        if (strlen($password) < 8) {
            return [
                'status' => false,
                'message' => 'Password must be at least 8 characters.'
            ];
        }

        if ($password !== $confirmPassword) {
            return [
                'status' => false,
                'message' => 'Passwords do not match.'
            ];
        }

        if ($this->users->findByEmail($email)) {
            return [
                'status' => false,
                'message' => 'Email already registered.'
            ];
        }

        if ($this->users->findByPhone($phone)) {
            return [
                'status' => false,
                'message' => 'Mobile number already registered.'
            ];
        }

        try {
            $stmt = $this->conn->prepare("
                INSERT INTO users (
                    full_name,
                    email,
                    phone,
                    password,
                    role,
                    status,
                    phone_verified,
                    created_at
                ) VALUES (
                    :full_name,
                    :email,
                    :phone,
                    :password,
                    'client',
                    'active',
                    0,
                    NOW()
                )
            ");

            $stmt->execute([
                ':full_name' => $fullName,
                ':email' => $email,
                ':phone' => $phone,
                ':password' => password_hash($password, PASSWORD_DEFAULT)
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            return [
                'status' => false,
                'message' => 'Unable to create account right now.'
            ];
        }

        if (function_exists('logSecurityEvent')) {
            logSecurityEvent((int) $this->conn->lastInsertId(), 'client_register', 'info', 'Client registered');
        }

        return [
            'status' => true,
            'message' => 'Account created. Please login with OTP.'
        ];
    }

    public function logout(): void
    {
        if (function_exists('logSecurityEvent') && currentUserId() !== null) {
            logSecurityEvent(currentUserId(), 'user_logout', 'info', 'User logged out');
        }

        destroySession();
    }

    private function sendOtp(string $phone, string $otp, array $user): void
    {
        if (function_exists('sendOtpSms')) {
            sendOtpSms($phone, $otp);
        }

        if (
            !empty($user['email']) &&
            function_exists('sendOtpEmail')
        ) {
            sendOtpEmail(
                (string) $user['email'],
                $otp,
                (string) ($user['full_name'] ?? 'User')
            );
        }
    }
}
