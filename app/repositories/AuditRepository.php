<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Audit Repository
 * All SQL related to security_logs, audit_logs tables.
 */
class AuditRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in AuditRepository.");
            }
            $this->db = $conn;
        }
    }

/**
     * Log a security event.
     *
     * @param int|null $userId Authenticated user id, or null for
     *                         unauthenticated events (e.g. OTP send/verify).
     *                         NULL is stored so the FK fk_security_logs_user
     *                         (ON DELETE SET NULL) is satisfied.
     */
    public function logEvent(?int $userId, string $eventType, string $severity, string $details, string $ip, string $ua): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO security_logs (user_id, event_type, severity, details, ip_address, user_agent, created_at)
             VALUES (:user_id, :event, :severity, :details, :ip, :ua, NOW())"
        );
        return $stmt->execute([
            ':user_id'  => $userId,
            ':event'    => $eventType,
            ':severity' => $severity,
            ':details'  => $details,
            ':ip'       => $ip,
            ':ua'       => $ua,
        ]);
    }

    /**
     * Log an admin audit action.
     */
    public function logAudit(int $adminId, string $action, string $entityType, int $entityId, string $details, string $ip): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address, created_at)
             VALUES (:user_id, :action, :entity_type, :entity_id, :details, :ip, NOW())"
        );
        return $stmt->execute([
            ':user_id'     => $adminId,
            ':action'      => $action,
            ':entity_type' => $entityType,
            ':entity_id'   => $entityId,
            ':details'     => $details,
            ':ip'          => $ip,
        ]);
    }

    /**
     * Get recent security logs.
     */
    public function getSecurityLogs(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM security_logs ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get recent audit logs.
     */
    public function getAuditLogs(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get security logs for a specific user.
     */
    public function getSecurityLogsByUserId(int $userId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT event_type as event, severity, details as description, created_at
             FROM security_logs WHERE user_id = :user_id
             ORDER BY id DESC LIMIT :limit"
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Purge old security logs.
     */
    public function purgeOldLogs(int $daysOld = 90): int
    {
        $stmt = $this->db->prepare(
            "DELETE FROM security_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)"
        );
        $stmt->bindValue(':days', $daysOld, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}