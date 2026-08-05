<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;
use Exception;

/**
 * Enterprise User Repository
 */
class UserRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in UserRepository.");
            }
            $this->db = $conn;
        }
    }

    public function findByPhone(string $phone): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE phone = :phone LIMIT 1");
            $stmt->execute([':phone' => $phone]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (\Throwable $e) {
            error_log('UserRepository::findByPhone error: ' . $e->getMessage());
            return null;
        }
    }

    public function findByEmail(string $email): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (\Throwable $e) {
            error_log('UserRepository::findByEmail error: ' . $e->getMessage());
            return null;
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (\Throwable $e) {
            error_log('UserRepository::findById error: ' . $e->getMessage());
            return null;
        }
    }

    public function findClientByUserId(int $userId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM clients WHERE user_id = :user_id LIMIT 1");
            $stmt->execute([':user_id' => $userId]);
            $client = $stmt->fetch();
            return $client ?: null;
        } catch (\Throwable $e) {
            error_log('UserRepository::findClientByUserId error: ' . $e->getMessage());
            return null;
        }
    }

    public function createUser(array $data): int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (full_name, email, phone, password_hash, role, status, created_at)
                VALUES (:full_name, :email, :phone, :password_hash, :role, :status, NOW())
            ");
            $stmt->execute([
                ':full_name'     => $data['full_name'] ?? $data['name'] ?? 'User',
                ':email'         => $data['email'] ?? null,
                ':phone'         => $data['phone'] ?? null,
                ':password_hash' => $data['password_hash'] ?? $data['password'] ?? '',
                ':role'          => $data['role'] ?? 'client',
                ':status'        => $data['status'] ?? 'active',
            ]);
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('UserRepository::createUser error: ' . $e->getMessage());
            return 0;
        }
    }

    public function createGuestUser(string $phone, string $name = 'Guest'): int
    {
        return $this->createUser([
            'full_name' => $name,
            'phone'     => $phone,
            'role'      => 'guest',
            'status'    => 'active'
        ]);
    }

    public function getAllUsers(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, full_name, email, phone, role, status, created_at FROM users ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('UserRepository::getAllUsers error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all users with role='client' (admin client management).
     */
    public function getClients(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, full_name, email, phone, status, phone_verified, created_at, last_login, last_ip, profile_image
                FROM users
                WHERE role = 'client'
                ORDER BY id DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('UserRepository::getClients error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find a client user by ID (with role check).
     */
    public function findClientById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id AND role = 'client' LIMIT 1");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (\Throwable $e) {
            error_log('UserRepository::findClientById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update a user record.
     *
     * @param int   $id   User ID
     * @param array $data Associative array of columns to update
     * @return bool
     */
    public function updateUser(int $id, array $data): bool
    {
        try {
            $sets = [];
            $params = [':id' => $id];

            foreach ($data as $column => $value) {
                // Map service-layer keys to database columns
                $dbColumn = $column;
                if ($column === 'password_hash') {
                    $dbColumn = 'password';
                } elseif ($column === 'full_name') {
                    $dbColumn = 'full_name';
                }

                $sets[] = "{$dbColumn} = :{$column}";
                $params[":{$column}"] = $value;
            }

            $sets[] = "updated_at = NOW()";
            $setClause = implode(', ', $sets);

            $stmt = $this->db->prepare(
                "UPDATE users SET {$setClause} WHERE id = :id"
            );
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('UserRepository::updateUser error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a user permanently.
     */
    public function deleteUser(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id LIMIT 1");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('UserRepository::deleteUser error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user activity from security logs.
     */
    public function getUserActivity(int $userId, int $limit = 10): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM security_logs WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit"
            );
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('UserRepository::getUserActivity error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete all security logs for a user.
     */
    public function deleteSecurityLogsByUserId(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM security_logs WHERE user_id = :user_id");
            return $stmt->execute([':user_id' => $userId]);
        } catch (\Throwable $e) {
            error_log('UserRepository::deleteSecurityLogsByUserId error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Save an OTP for a user.
     */
    public function saveOtp(int $userId, string $otp, string $purpose, int $expiryMinutes = 5): bool
    {
        try {
            // Expire old OTPs
            $this->expireOtp($userId, $purpose);

            $stmt = $this->db->prepare(
                "INSERT INTO user_otps (user_id, otp, purpose, attempts, resend_count, ip_address, user_agent, is_used, expires_at, created_at)
                 VALUES (:user_id, :otp, :purpose, 0, 0, :ip_address, :user_agent, 0, :expires_at, NOW())"
            );
            return $stmt->execute([
                ':user_id'    => $userId,
                ':otp'        => password_hash($otp, PASSWORD_DEFAULT),
                ':purpose'    => $purpose,
                ':expires_at' => date('Y-m-d H:i:s', time() + ($expiryMinutes * 60)),
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('UserRepository::saveOtp error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify an OTP for a user.
     */
    public function verifyOtp(int $userId, string $otp, string $purpose): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM user_otps
                 WHERE user_id = :user_id AND purpose = :purpose AND is_used = 0
                 AND expires_at > NOW() AND deleted_at IS NULL
                 ORDER BY id DESC LIMIT 1"
            );
            $stmt->execute([':user_id' => $userId, ':purpose' => $purpose]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return false;
            }

            if ((int)$row['attempts'] >= 5) {
                return false;
            }

            if (!password_verify($otp, $row['otp'])) {
                $attemptStmt = $this->db->prepare("UPDATE user_otps SET attempts = attempts + 1 WHERE id = :id");
                $attemptStmt->execute([':id' => $row['id']]);
                return false;
            }

            $usedStmt = $this->db->prepare("UPDATE user_otps SET is_used = 1 WHERE id = :id");
            $usedStmt->execute([':id' => $row['id']]);
            return true;
        } catch (\Throwable $e) {
            error_log('UserRepository::verifyOtp error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Expire all active OTPs for a user and purpose.
     */
    public function expireOtp(int $userId, string $purpose): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE user_otps SET is_used = 1, deleted_at = NOW()
                 WHERE user_id = :user_id AND purpose = :purpose AND is_used = 0"
            );
            return $stmt->execute([':user_id' => $userId, ':purpose' => $purpose]);
        } catch (\Throwable $e) {
            error_log('UserRepository::expireOtp error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find the latest active OTP for a user and purpose.
     */
    public function findActiveOtp(int $userId, string $purpose): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM user_otps
                 WHERE user_id = :user_id AND purpose = :purpose AND is_used = 0
                 AND expires_at > NOW() AND deleted_at IS NULL
                 ORDER BY id DESC LIMIT 1"
            );
            $stmt->execute([':user_id' => $userId, ':purpose' => $purpose]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('UserRepository::findActiveOtp error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Increment OTP attempts.
     */
    public function incrementOtpAttempts(int $otpId): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE user_otps SET attempts = attempts + 1 WHERE id = :id");
            return $stmt->execute([':id' => $otpId]);
        } catch (\Throwable $e) {
            error_log('UserRepository::incrementOtpAttempts error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark OTP as used.
     */
    public function markOtpUsed(int $otpId): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE user_otps SET is_used = 1 WHERE id = :id");
            return $stmt->execute([':id' => $otpId]);
        } catch (\Throwable $e) {
            error_log('UserRepository::markOtpUsed error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find user by identifier (email or phone).
     */
    public function findByIdentifier(string $identifier): ?array
    {
        $user = $this->findByEmail($identifier);
        if ($user) {
            return $user;
        }
        return $this->findByPhone($identifier);
    }

    /**
     * Increment failed login attempts for a user.
     */
    public function incrementFailedAttempts(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE users SET failed_attempts = failed_attempts + 1,
                 locked_until = CASE WHEN failed_attempts + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE) ELSE locked_until END
                 WHERE id = :id"
            );
            return $stmt->execute([':id' => $userId]);
        } catch (\Throwable $e) {
            error_log('UserRepository::incrementFailedAttempts error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reset failed login attempts for a user.
     */
    public function resetFailedAttempts(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = :id"
            );
            return $stmt->execute([':id' => $userId]);
        } catch (\Throwable $e) {
            error_log('UserRepository::resetFailedAttempts error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update last login timestamp and IP.
     */
    public function updateLastLogin(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE users SET last_login = NOW(), last_ip = :ip WHERE id = :id"
            );
            return $stmt->execute([
                ':id' => $userId,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('UserRepository::updateLastLogin error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get dashboard counts.
     */
    public function getDashboardCounts(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT
                    (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) as total_users,
                    (SELECT COUNT(*) FROM projects WHERE deleted_at IS NULL) as total_projects,
                    (SELECT COUNT(*) FROM blogs WHERE deleted_at IS NULL) as total_blogs,
                    (SELECT COUNT(*) FROM testimonials WHERE deleted_at IS NULL) as total_testimonials,
                    (SELECT COUNT(*) FROM quotations WHERE deleted_at IS NULL) as total_quotations,
                    (SELECT COUNT(*) FROM estimator_requests WHERE deleted_at IS NULL) as total_estimator_requests"
            );
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('UserRepository::getDashboardCounts error: ' . $e->getMessage());
            return [];
        }
    }
}