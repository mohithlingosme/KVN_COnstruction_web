<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Mail Repository
 * All SQL related to mail_logs table.
 */
class MailRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in MailRepository.");
            }
            $this->db = $conn;
        }
    }

    /**
     * Log a mail delivery attempt.
     */
    public function log(
        string $recipient,
        string $subject,
        string $status,
        string $error = '',
        ?string $ipAddress = null
    ): bool {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO mail_logs (recipient, subject, status, error_message, ip_address, created_at)
                 VALUES (:recipient, :subject, :status, :error_message, :ip_address, NOW())"
            );
            return $stmt->execute([
                ':recipient'     => $recipient,
                ':subject'       => $subject,
                ':status'        => $status,
                ':error_message' => $error,
                ':ip_address'    => $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('MailRepository::log error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent mail logs.
     */
    public function getRecent(int $limit = 50): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM mail_logs ORDER BY id DESC LIMIT :limit"
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('MailRepository::getRecent error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Prune old mail logs.
     */
    public function prune(int $daysOld = 90): int
    {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM mail_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)"
            );
            $stmt->bindValue(':days', $daysOld, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (\Throwable $e) {
            error_log('MailRepository::prune error: ' . $e->getMessage());
            return 0;
        }
    }
}