<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Security Admin Repository
 * All SQL related to admin security pages: admin_sessions, security_logs,
 * login_attempts, blocked_users, audit_logs.
 */
class SecurityAdminRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in SecurityAdminRepository.");
            }
            $this->db = $conn;
        }
    }

    // ========================================================================
    // ADMIN SESSIONS
    // ========================================================================

    public function getAdminSessions(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM admin_sessions ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::getAdminSessions error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertAdminSession(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO admin_sessions (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::insertAdminSession error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteAdminSession(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM admin_sessions WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::deleteAdminSession error: ' . $e->getMessage());
            return false;
        }
    }

    public function terminateSession(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE admin_sessions SET status = 'expired' WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::terminateSession error: ' . $e->getMessage());
            return false;
        }
    }

    public function terminateAllSessions(): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE admin_sessions SET status = 'expired'");
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::terminateAllSessions error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // SECURITY LOGS
    // ========================================================================

    public function getSecurityLogs(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM security_logs ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::getSecurityLogs error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertSecurityLog(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO security_logs (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::insertSecurityLog error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteSecurityLog(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM security_logs WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::deleteSecurityLog error: ' . $e->getMessage());
            return false;
        }
    }

    public function clearSecurityLogs(): bool
    {
        try {
            $stmt = $this->db->prepare("TRUNCATE TABLE security_logs");
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::clearSecurityLogs error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // LOGIN ATTEMPTS
    // ========================================================================

    public function getLoginAttempts(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM login_attempts ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::getLoginAttempts error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertLoginAttempt(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO login_attempts (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::insertLoginAttempt error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteLoginAttempt(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM login_attempts WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::deleteLoginAttempt error: ' . $e->getMessage());
            return false;
        }
    }

    public function clearLoginAttempts(): bool
    {
        try {
            $stmt = $this->db->prepare("TRUNCATE TABLE login_attempts");
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::clearLoginAttempts error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // BLOCKED USERS
    // ========================================================================

    public function getBlockedUsers(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM blocked_users ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::getBlockedUsers error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertBlockedUser(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO blocked_users (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::insertBlockedUser error: ' . $e->getMessage());
            return false;
        }
    }

public function deleteBlockedUser(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM blocked_users WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::deleteBlockedUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function unblockUser(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE blocked_users SET status = 'unblocked', updated_at = NOW() WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::unblockUser error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // AUDIT LOGS
    // ========================================================================

    public function getAuditLogs(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM audit_logs ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::getAuditLogs error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertAuditLog(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO audit_logs (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::insertAuditLog error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteAuditLog(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM audit_logs WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::deleteAuditLog error: ' . $e->getMessage());
            return false;
        }
    }

    public function clearAuditLogs(): bool
    {
        try {
            $stmt = $this->db->prepare("TRUNCATE TABLE audit_logs");
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('SecurityAdminRepository::clearAuditLogs error: ' . $e->getMessage());
            return false;
        }
    }
}
