<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Session Repository
 * All SQL related to user_sessions, remember_tokens tables.
 */
class SessionRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in SessionRepository.");
            }
            $this->db = $conn;
        }
    }

    /**
     * Create a new user session.
     */
    public function create(int $userId, string $token, string $fingerprint, string $device, string $ip, string $ua, bool $isAdmin = false): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO user_sessions (user_id, session_token, fingerprint_hash, device_hash, ip_address, user_agent, is_admin_session, last_activity, created_at)
             VALUES (:user_id, :token, :fingerprint, :device, :ip, :ua, :is_admin, NOW(), NOW())"
        );
        return $stmt->execute([
            ':user_id'     => $userId,
            ':token'       => $token,
            ':fingerprint' => $fingerprint,
            ':device'      => $device,
            ':ip'          => $ip,
            ':ua'          => $ua,
            ':is_admin'    => $isAdmin ? 1 : 0,
        ]);
    }

    /**
     * Find a session by token.
     */
    public function findByToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM user_sessions WHERE session_token = :token LIMIT 1"
        );
        $stmt->execute([':token' => $token]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Update session activity by session ID.
     */
    public function updateActivity(int $sessionId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE user_sessions SET last_activity = NOW() WHERE id = :id"
        );
        return $stmt->execute([':id' => $sessionId]);
    }

    /**
     * Update session activity by session token.
     */
    public function updateActivityByToken(string $token): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE user_sessions SET last_activity = NOW() WHERE session_token = :token"
        );
        return $stmt->execute([':token' => $token]);
    }

    /**
     * Delete a session by token.
     */
    public function deleteByToken(string $token): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM user_sessions WHERE session_token = :token"
        );
        return $stmt->execute([':token' => $token]);
    }

    /**
     * Delete all sessions for a user.
     */
    public function deleteByUserId(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM user_sessions WHERE user_id = :user_id"
        );
        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Delete expired sessions.
     */
    public function deleteExpired(int $daysOld = 30): int
    {
        $stmt = $this->db->prepare(
            "DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL :days DAY)"
        );
        $stmt->bindValue(':days', $daysOld, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Get active sessions for a user.
     */
    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM user_sessions WHERE user_id = :user_id ORDER BY last_activity DESC"
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Create a remember token.
     */
    public function createRememberToken(int $userId, string $hash, string $ip, string $ua, string $expiresAt): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO remember_tokens (user_id, token_hash, ip_address, user_agent, expires_at, created_at)
             VALUES (:user_id, :hash, :ip, :ua, :expires, NOW())"
        );
        return $stmt->execute([
            ':user_id' => $userId,
            ':hash'    => $hash,
            ':ip'      => $ip,
            ':ua'      => $ua,
            ':expires' => $expiresAt,
        ]);
    }
}
