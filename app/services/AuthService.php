<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Service.php';

/**
 * AuthService - All authentication business logic
 * Replaces: helpers/auth.php, helpers/session.php, helpers/otp.php partial
 * SQL queries delegated to UserRepository
 */
class AuthService extends Service
{
    private UserRepository $userRepo;
    private PDO $db;

    public function __construct(UserRepository $userRepo, PDO $db)
    {
        $this->userRepo = $userRepo;
        $this->db = $db;
    }

    /**
     * Register a new user
     */
    public function register(array $input): array
    {
        $required = ['full_name', 'email', 'phone', 'password'];
        $missing = $this->validateRequired($input, $required);
        if ($missing !== null) {
            return $this->error('Required fields: ' . implode(', ', $missing));
        }

        $fullName = trim($input['full_name']);
        $email = strtolower(trim($input['email']));
        $phone = trim($input['phone']);
        $password = $input['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('Invalid email address.');
        }

        if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
            return $this->error('Invalid mobile number.');
        }

        $passwordErrors = $this->validatePasswordStrength($password);
        if (!empty($passwordErrors)) {
            return $this->error($passwordErrors[0]);
        }

        if ($this->userRepo->findByEmail($email)) {
            return $this->error('Email already registered.');
        }

        if ($this->userRepo->findByPhone($phone)) {
            return $this->error('Phone already registered.');
        }

        try {
            $userId = $this->userRepo->create([
                'full_name' => $fullName,
                'name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'client',
                'status' => 'active',
                'email_verified' => 0,
                'phone_verified' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->logEvent('USER_REGISTERED', "User registered: {$email}");

            return $this->success(['user_id' => $userId], 'Account created successfully.');
        } catch (\Throwable $e) {
            $this->logEvent('REGISTRATION_FAILED', $e->getMessage());
            return $this->error('Registration failed. Please try again.');
        }
    }

    /**
     * Login with email/phone and password
     */
    public function loginWithCredentials(string $identifier, string $password, bool $rememberMe = false): array
    {
        if (empty($identifier) || empty($password)) {
            return $this->error('Email/phone and password are required.');
        }

        $user = $this->userRepo->findByIdentifier($identifier);
        if (!$user || ($user['status'] ?? '') !== 'active') {
            return $this->error('Invalid credentials.');
        }

        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            $this->logEvent('LOGIN_LOCKED', "Account locked: {$identifier}");
            return $this->error('Account temporarily locked. Try again later.');
        }

        if (!password_verify($password, $user['password'])) {
            $this->userRepo->incrementFailedAttempts((int) $user['id']);
            $this->logEvent('LOGIN_FAILED', "Failed login attempt: {$identifier}");
            return $this->error('Invalid credentials.');
        }

        $this->userRepo->resetFailedAttempts((int) $user['id']);
        $this->initializeSession($user);

        if ($rememberMe) {
            $this->setRememberMeToken((int) $user['id']);
        }

        $this->userRepo->updateLastLogin((int) $user['id']);

        $this->logEvent('LOGIN_SUCCESS', "User logged in: {$identifier}");

        return $this->success([
            'user_id' => (int) $user['id'],
            'role' => $user['role'],
            'name' => $user['full_name'],
        ], 'Login successful.');
    }

    /**
     * Admin login
     */
    public function adminLogin(string $email, string $password): array
    {
        if (empty($email) || empty($password)) {
            return $this->error('Email and password are required.');
        }

        $admin = $this->userRepo->findByEmail($email);
        if (!$admin || ($admin['status'] ?? '') !== 'active') {
            return $this->error('Invalid credentials.');
        }

        if (!in_array($admin['role'] ?? '', ['admin', 'super_admin'], true)) {
            $this->logEvent('UNAUTHORIZED_ADMIN_ACCESS', "Non-admin login attempt: {$email}");
            return $this->error('Unauthorized access.');
        }

        if (!empty($admin['locked_until']) && strtotime($admin['locked_until']) > time()) {
            return $this->error('Account temporarily locked.');
        }

        if (!password_verify($password, $admin['password'])) {
            $this->userRepo->incrementFailedAttempts((int) $admin['id']);
            $this->logEvent('ADMIN_LOGIN_FAILED', "Failed admin login: {$email}");
            return $this->error('Invalid credentials.');
        }

        $this->userRepo->resetFailedAttempts((int) $admin['id']);
        $this->initializeAdminSession($admin);
        $this->userRepo->updateLastLogin((int) $admin['id']);

        $this->logEvent('ADMIN_LOGIN', "Admin logged in: {$email}");

        return $this->success([
            'user_id' => (int) $admin['id'],
            'role' => $admin['role'],
        ], 'Admin login successful.');
    }

    /**
     * Send OTP for phone login
     */
    public function sendOtp(string $phone): array
    {
        if (empty($phone)) {
            return $this->error('Phone number is required.');
        }

        $user = $this->userRepo->findByPhone($phone);
        if (!$user || ($user['status'] ?? '') !== 'active') {
            return $this->error('Unable to process request.');
        }

        // Rate limiting
        $rateKey = 'otp_' . $phone;
        if (!$this->checkRateLimit($rateKey, 3, 600)) {
            return $this->error('Too many requests. Try again later.');
        }

        try {
            $otp = $this->generateSecureOtp();
            $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);

            // Expire old OTPs
            $this->db->prepare(
                "UPDATE user_otps SET is_used = 1, deleted_at = NOW() WHERE user_id = :user_id AND purpose = 'login' AND is_used = 0"
            )->execute([':user_id' => $user['id']]);

            // Insert new OTP
            $stmt = $this->db->prepare(
                "INSERT INTO user_otps (user_id, otp, purpose, attempts, resend_count, ip_address, user_agent, expires_at, created_at)
                 VALUES (:user_id, :otp, 'login', 0, 0, :ip, :ua, :expires_at, NOW())"
            );
            $stmt->execute([
                ':user_id' => $user['id'],
                ':otp' => $hashedOtp,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                ':expires_at' => date('Y-m-d H:i:s', time() + 300), // 5 minutes
            ]);

            // Store OTP in session for verification
            $_SESSION['otp_user_id'] = (int) $user['id'];
            $_SESSION['otp_phone'] = $phone;
            $_SESSION['otp_purpose'] = 'login';
            $_SESSION['otp_expires_at'] = time() + 300;

            $this->logEvent('OTP_SENT', "OTP sent to {$phone}");

            return $this->success(['phone' => $phone], 'OTP sent successfully.');
        } catch (\Throwable $e) {
            $this->logEvent('OTP_SEND_FAILED', $e->getMessage());
            return $this->error('Failed to send OTP.');
        }
    }

    /**
     * Verify OTP and login
     */
    public function verifyOtpAndLogin(int $userId, string $otp, string $purpose = 'login'): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM user_otps 
             WHERE user_id = :user_id AND purpose = :purpose AND is_used = 0 
             AND expires_at > NOW() 
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([':user_id' => $userId, ':purpose' => $purpose]);
        $otpRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$otpRow) {
            return $this->error('OTP expired or not found.');
        }

        if ((int) $otpRow['attempts'] >= 5) {
            $this->logEvent('OTP_BLOCKED', "OTP attempts exceeded for user: {$userId}");
            return $this->error('Too many attempts. Request a new OTP.');
        }

        if (!password_verify($otp, $otpRow['otp'])) {
            $this->db->prepare("UPDATE user_otps SET attempts = attempts + 1 WHERE id = :id")
                ->execute([':id' => $otpRow['id']]);
            return $this->error('Invalid OTP.');
        }

        // Mark OTP as used
        $this->db->prepare("UPDATE user_otps SET is_used = 1 WHERE id = :id")
            ->execute([':id' => $otpRow['id']]);

        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return $this->error('User not found.');
        }

        session_regenerate_id(true);
        $this->initializeSession($user);
        $this->userRepo->updateLastLogin($userId);

        $this->logEvent('OTP_VERIFIED', "OTP login successful for user: {$userId}");

        return $this->success([
            'user_id' => $userId,
            'role' => $user['role'],
        ], 'Login successful.');
    }

    /**
     * Logout
     */
    public function logout(): array
    {
        if (isset($_SESSION['user_id'])) {
            $this->logEvent('USER_LOGOUT', "User logged out: {$_SESSION['user_id']}");
        }

        $this->destroySession();
        return $this->success(null, 'Logged out successfully.');
    }

    /**
     * Initialize user session
     */
    private function initializeSession(array $user): void
    {
        session_regenerate_id(true);
        $sessionToken = bin2hex(random_bytes(32));

        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'] ?? '';
        $_SESSION['role'] = $user['role'];
        $_SESSION['session_token'] = $sessionToken;
        $_SESSION['fingerprint'] = $this->generateFingerprint();
        $_SESSION['last_activity'] = time();
        $_SESSION['login_time'] = time();
        $_SESSION['is_admin'] = in_array($user['role'], ['admin', 'super_admin'], true);

        // Store session in database
        try {
            $this->db->prepare(
                "INSERT INTO user_sessions (user_id, session_token, fingerprint_hash, device_hash, ip_address, user_agent, is_admin_session, last_activity, created_at)
                 VALUES (:user_id, :token, :fingerprint, :device, :ip, :ua, :is_admin, NOW(), NOW())"
            )->execute([
                ':user_id' => $user['id'],
                ':token' => $sessionToken,
                ':fingerprint' => $this->generateFingerprint(),
                ':device' => $this->generateDeviceHash(),
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                ':is_admin' => in_array($user['role'], ['admin', 'super_admin'], true) ? 1 : 0,
            ]);
        } catch (\Throwable $e) {
            error_log('Session storage error: ' . $e->getMessage());
        }
    }

    /**
     * Initialize admin session
     */
    private function initializeAdminSession(array $user): void
    {
        $this->initializeSession($user);
    }

    /**
     * Set remember me token
     */
    private function setRememberMeToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        try {
            $this->db->prepare(
                "INSERT INTO remember_tokens (user_id, token_hash, ip_address, user_agent, expires_at, created_at)
                 VALUES (:user_id, :hash, :ip, :ua, :expires, NOW())"
            )->execute([
                ':user_id' => $userId,
                ':hash' => $tokenHash,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                ':expires' => date('Y-m-d H:i:s', time() + 86400 * 30),
            ]);

            setcookie('remember_token', $token, [
                'expires' => time() + 86400 * 30,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } catch (\Throwable $e) {
            error_log('Remember token error: ' . $e->getMessage());
        }
    }

    /**
     * Destroy the current session
     */
    private function destroySession(): void
    {
        if (isset($_SESSION['session_token'])) {
            try {
                $this->db->prepare("DELETE FROM user_sessions WHERE session_token = :token")
                    ->execute([':token' => $_SESSION['session_token']]);
            } catch (\Throwable $e) {
                error_log('Session destroy error: ' . $e->getMessage());
            }
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    /**
     * Generate session fingerprint
     */
    private function generateFingerprint(): string
    {
        return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    }

    /**
     * Generate device hash
     */
    private function generateDeviceHash(): string
    {
        return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . ($_SERVER['HTTP_USER_AGENT'] ?? '') . ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    }

    /**
     * Validate password strength
     */
    private function validatePasswordStrength(string $password): array
    {
        $errors = [];
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
        if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password needs an uppercase letter.';
        if (!preg_match('/[a-z]/', $password)) $errors[] = 'Password needs a lowercase letter.';
        if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password needs a number.';
        if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = 'Password needs a special character.';
        return $errors;
    }

    /**
     * Generate secure 6-digit OTP
     */
    private function generateSecureOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check rate limit
     */
    private function checkRateLimit(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        if (!isset($_SESSION['_rate_limit'][$key])) {
            $_SESSION['_rate_limit'][$key] = [];
        }

        $now = time();
        $_SESSION['_rate_limit'][$key] = array_filter(
            $_SESSION['_rate_limit'][$key],
            fn($t) => ($now - $t) < $windowSeconds
        );

        if (count($_SESSION['_rate_limit'][$key]) >= $maxAttempts) {
            return false;
        }

        $_SESSION['_rate_limit'][$key][] = $now;
        return true;
    }

    /**
     * Log security event
     */
    private function logEvent(string $event, string $details): void
    {
        if (!isset($GLOBALS['conn'])) return;

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO security_logs (user_id, event_type, severity, details, ip_address, user_agent, created_at)
                 VALUES (:user_id, :event, 'info', :details, :ip, :ua, NOW())"
            );
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'] ?? null,
                ':event' => $event,
                ':details' => $details,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('Security log error: ' . $e->getMessage());
        }
    }
}