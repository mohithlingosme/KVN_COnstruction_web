<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise OTP Repository
 * All SQL related to the otps table.
 */
class OtpRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in OtpRepository.");
            }
            $this->db = $conn;
        }
    }

    /**
     * Create a new OTP record.
     */
    public function create(string $phone, ?int $userId, string $otpHash, string $expiresAt, ?string $ipAddress): bool
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO otps (phone_number, user_id, otp_hash, expires_at, ip_address)
                 VALUES (:phone, :user_id, :hash, :expires_at, :ip)"
            );
            return $stmt->execute([
                ':phone'      => $phone,
                ':user_id'    => $userId,
                ':hash'       => $otpHash,
                ':expires_at' => $expiresAt,
                ':ip'         => $ipAddress,
            ]);
        } catch (\Throwable $e) {
            error_log('OtpRepository::create error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find the latest active (unused) OTP for a phone number.
     */
    public function findActiveByPhone(string $phone): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, otp_hash, expires_at
                 FROM otps
                 WHERE phone_number = :phone AND is_used = 0
                 ORDER BY created_at DESC LIMIT 1"
            );
            $stmt->execute([':phone' => $phone]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('OtpRepository::findActiveByPhone error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark an OTP as used by ID.
     */
    public function markUsed(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE otps SET is_used = 1 WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('OtpRepository::markUsed error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidate all active OTPs for a phone number.
     */
    public function invalidateByPhone(string $phone): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE otps SET is_used = 1 WHERE phone_number = :phone AND is_used = 0"
            );
            return $stmt->execute([':phone' => $phone]);
        } catch (\Throwable $e) {
            error_log('OtpRepository::invalidateByPhone error: ' . $e->getMessage());
            return false;
        }
    }
}