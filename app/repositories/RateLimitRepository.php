<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Rate Limit Repository
 * All SQL related to rate_limits table.
 */
class RateLimitRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in RateLimitRepository.");
            }
            $this->db = $conn;
        }
    }

    /**
     * Get current attempt count for a key.
     */
    public function getAttempts(string $key): int
    {
        $stmt = $this->db->prepare(
            "SELECT attempts FROM rate_limits WHERE `key` = :key AND expires_at > NOW() LIMIT 1"
        );
        $stmt->execute([':key' => $key]);
        $row = $stmt->fetch();
        return (int)($row['attempts'] ?? 0);
    }

    /**
     * Get blocked_until timestamp.
     */
    public function getBlockedUntil(string $key): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT blocked_until FROM rate_limits WHERE `key` = :key AND blocked_until > NOW() LIMIT 1"
        );
        $stmt->execute([':key' => $key]);
        $row = $stmt->fetch();
        return $row['blocked_until'] ?? null;
    }

    /**
     * Create or increment rate limit.
     */
    public function increment(string $key, int $decayMinutes = 1): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO rate_limits (`key`, attempts, expires_at, created_at)
             VALUES (:key, 1, DATE_ADD(NOW(), INTERVAL :decay MINUTE), NOW())
             ON DUPLICATE KEY UPDATE attempts = attempts + 1"
        );
        return $stmt->execute([':key' => $key, ':decay' => $decayMinutes]);
    }

    /**
     * Block a key until a specific time.
     */
    public function block(string $key, int $blockMinutes = 15): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE rate_limits SET blocked_until = DATE_ADD(NOW(), INTERVAL :block MINUTE) WHERE `key` = :key"
        );
        return $stmt->execute([':key' => $key, ':block' => $blockMinutes]);
    }

    /**
     * Reset rate limit for a key.
     */
    public function reset(string $key): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM rate_limits WHERE `key` = :key"
        );
        return $stmt->execute([':key' => $key]);
    }

    /**
     * Clean up expired rate limits.
     */
    public function cleanExpired(): int
    {
        $stmt = $this->db->prepare(
            "DELETE FROM rate_limits WHERE expires_at < NOW() AND blocked_until IS NULL"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }
}