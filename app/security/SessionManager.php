<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| SESSION MANAGER - Enterprise Session Handler
|--------------------------------------------------------------------------
| File: /app/security/SessionManager.php
|--------------------------------------------------------------------------
*/

namespace App\Security;

use PDO;
use Exception;

class SessionManager
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Get current session user ID
     */
    public function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Completely destroy all active sessions for a user
     * Used after password reset to force re-login everywhere
     */
    public function destroyAllUserSessions(int $userId): bool
    {
        try {
            $this->conn->beginTransaction();

            // Delete all session records from database
            $query = "
                DELETE FROM user_sessions
                WHERE user_id = :user_id
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);

            // Invalidate all remember-me tokens
            $query = "
                DELETE FROM remember_tokens
                WHERE user_id = :user_id
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);

            $this->conn->commit();

            // Completely destroy current session
            $this->forceSessionDestroy();

            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('SessionManager::destroyAllUserSessions - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Force complete session destruction
     */
    public function forceSessionDestroy(): void
    {
        // Clear all session variables
        $_SESSION = [];

        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy the session
        session_destroy();
    }

    /**
     * Clean expired sessions from database
     */
    public function cleanExpiredSessions(): int
    {
        try {
            $query = "
                DELETE FROM user_sessions
                WHERE expires_at < NOW()
                OR last_activity < DATE_SUB(NOW(), INTERVAL 1 DAY)
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log('SessionManager::cleanExpiredSessions - ' . $e->getMessage());
            return 0;
        }
    }
}