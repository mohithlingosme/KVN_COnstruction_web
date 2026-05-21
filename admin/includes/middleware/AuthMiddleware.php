<?php

declare(strict_types=1);

/**
 * Admin Authentication Middleware
 * 
 * Ensures the current request is from an authenticated admin.
 * Includes timeout protection and session validation.
 */

class AuthMiddleware
{
    private static $session_timeout = 30 * 60; // 30 minutes

    /**
     * Check if the current request is authenticated
     * 
     * @return bool True if authenticated, false otherwise
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }

    /**
     * Get current admin ID
     * 
     * @return int|null Admin ID or null if not logged in
     */
    public static function getAdminId(): ?int
    {
        return $_SESSION['admin_id'] ?? null;
    }

    /**
     * Get current admin email
     * 
     * @return string|null Admin email or null if not logged in
     */
    public static function getAdminEmail(): ?string
    {
        return $_SESSION['admin_email'] ?? null;
    }

    /**
     * Check if session is valid (not expired)
     * 
     * @return bool True if session is valid
     */
    public static function isSessionValid(): bool
    {
        if (!isset($_SESSION['login_time'])) {
            return false;
        }

        if (time() - $_SESSION['login_time'] > self::$session_timeout) {
            return false;
        }

        return true;
    }

    /**
     * Require authentication - redirect to login if not authenticated
     * 
     * @return void
     */
    public static function requireAuth(): void
    {
        if (!self::isAuthenticated() || !self::isSessionValid()) {
            header("Location: login.php");
            exit();
        }

        // Update last activity
        $_SESSION['login_time'] = time();
    }

    /**
     * Require guest (not logged in) - redirect to dashboard if already logged in
     * 
     * @return void
     */
    public static function requireGuest(): void
    {
        if (self::isAuthenticated() && self::isSessionValid()) {
            header("Location: dashboard.php");
            exit();
        }
    }
}
?>
