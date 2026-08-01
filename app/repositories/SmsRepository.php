<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise SMS Repository
 * All SQL related to sms_logs table.
 */
class SmsRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in SmsRepository.");
            }
            $this->db = $conn;
        }
    }

    /**
     * Get the last SMS sent to a phone number.
     */
    public function getLastSent(string $phone): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT created_at FROM sms_logs WHERE phone_number = :phone ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([':phone' => $phone]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Log an SMS send attempt.
     */
    public function log(string $phone, string $message, string $status, string $provider, ?string $error = null): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO sms_logs (phone_number, message, status, provider, error_message, created_at)
             VALUES (:phone, :message, :status, :provider, :error, NOW())"
        );
        return $stmt->execute([
            ':phone'    => $phone,
            ':message'  => $message,
            ':status'   => $status,
            ':provider' => $provider,
            ':error'    => $error,
        ]);
    }
}