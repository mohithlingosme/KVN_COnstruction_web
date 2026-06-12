<?php

declare(strict_types=1);

use App\Models\User;

require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/helpers/session.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/rateLimiter.php';

class AdminAuthController
{
    private PDO $conn;
    private User $users;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
        $this->users = new User($conn);
    }

    public function login(string $email, string $password): array
    {
        $email = strtolower(trim((string) sanitize($email)));

        if ($email === '' || $password === '') {
            return [
                'status' => false,
                'message' => 'Email and password are required.'
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => false,
                'message' => 'Enter a valid admin email.'
            ];
        }

        if (!checkRateLimit('admin_login', 5, 900)) {
            $this->logSecurity(null, 'admin_login_rate_limited', 'warning', 'Admin login rate limit reached');

            return [
                'status' => false,
                'message' => 'Too many login attempts. Try again later.'
            ];
        }

        $admin = $this->users->findByEmail($email);

        if (
            !$admin ||
            ($admin['status'] ?? '') !== 'active' ||
            !in_array($admin['role'] ?? '', ['admin', 'super_admin'], true)
        ) {
            $this->logSecurity(null, 'invalid_admin_login', 'warning', 'Invalid admin login attempt: ' . $email);

            return [
                'status' => false,
                'message' => 'Invalid credentials.'
            ];
        }

        if ($this->users->isLocked($admin)) {
            $this->logSecurity((int) $admin['id'], 'locked_admin_login', 'warning', 'Locked admin account login attempt');

            return [
                'status' => false,
                'message' => 'Account temporarily locked.'
            ];
        }

        if (
            empty($admin['password']) ||
            !password_verify($password, (string) $admin['password'])
        ) {
            $this->users->incrementFailedAttempts((int) $admin['id']);
            $this->logSecurity((int) $admin['id'], 'invalid_admin_password', 'warning', 'Invalid admin password');

            return [
                'status' => false,
                'message' => 'Invalid credentials.'
            ];
        }

        $this->users->resetAttempts((int) $admin['id']);
        initializeSessionSecurity($admin);

        $_SESSION['admin_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['admin_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['admin_logged_in_at'] = time();

        $this->users->updateLastLogin((int) $admin['id']);
        $this->logSecurity((int) $admin['id'], 'admin_login', 'info', 'Admin logged in');
        $this->logAdminAction((int) $admin['id'], 'ADMIN_LOGIN', 'Admin logged in');

        return [
            'status' => true,
            'message' => 'Admin login successful.'
        ];
    }

    public function logout(): void
    {
        $adminId = currentUserId();

        if ($adminId !== null) {
            $this->logSecurity($adminId, 'admin_logout', 'info', 'Admin logged out');
            $this->logAdminAction($adminId, 'ADMIN_LOGOUT', 'Admin logged out');
        }

        destroySession();
    }

    private function logSecurity(?int $userId, string $event, string $severity, string $details): void
    {
        if (function_exists('logSecurityEvent')) {
            logSecurityEvent($userId, $event, $severity, $details);
        }
    }

    private function logAdminAction(int $adminId, string $action, string $details): void
    {
        if (function_exists('logAdminAction')) {
            logAdminAction($adminId, $action, $details);
        }
    }
}
