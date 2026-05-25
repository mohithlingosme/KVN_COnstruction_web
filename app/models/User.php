<?php

namespace App\Models;

use PDO;
use PDOException;

class User
{
    private PDO $db;

    public function __construct(PDO $database)
    {
        $this->db = $database;
    }

    /*
    |--------------------------------------------------------------------------
    | FIND USERS
    |--------------------------------------------------------------------------
    */

    public function findById(int $id): ?array
    {
        $query = "
            SELECT *
            FROM users
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            ':id' => $id
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $query = "
            SELECT *
            FROM users
            WHERE email = :email
            LIMIT 1
        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            ':email' => strtolower(trim($email))
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByPhone(string $phone): ?array
    {
        $query = "
            SELECT *
            FROM users
            WHERE phone = :phone
            LIMIT 1
        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            ':phone' => trim($phone)
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | ACCOUNT STATUS
    |--------------------------------------------------------------------------
    */

    public function isActive(int $userId): bool
    {
        $query = "
            SELECT status
            FROM users
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            ':id' => $userId
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return (
            $user &&
            $user['status'] === 'active'
        );
    }

    public function isLocked(array $user): bool
    {
        if (empty($user['locked_until'])) {
            return false;
        }

        return strtotime($user['locked_until']) > time();
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN ATTEMPTS
    |--------------------------------------------------------------------------
    */

    public function incrementFailedAttempts(int $userId): bool
    {
        $query = "
            UPDATE users
            SET failed_attempts = failed_attempts + 1
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ':id' => $userId
        ]);
    }

    public function resetAttempts(int $userId): bool
    {
        $query = "
            UPDATE users
            SET
                failed_attempts = 0,
                locked_until = NULL
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ':id' => $userId
        ]);
    }

    public function lockAccount(
        int $userId,
        int $minutes = 15
    ): bool {

        $query = "
            UPDATE users
            SET locked_until = DATE_ADD(
                NOW(),
                INTERVAL :minutes MINUTE
            )
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':minutes', $minutes, PDO::PARAM_INT);

        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | PASSWORD
    |--------------------------------------------------------------------------
    */

    public function updatePassword(
        int $userId,
        string $password
    ): bool {

        $query = "
            UPDATE users
            SET
                password = :password,
                failed_attempts = 0,
                locked_until = NULL,
                updated_at = NOW()
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([

            ':password' => password_hash(
                $password,
                PASSWORD_DEFAULT
            ),

            ':id' => $userId
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | OTP SYSTEM
    |--------------------------------------------------------------------------
    */

    public function saveOtp(
        int $userId,
        string $otp,
        string $purpose = 'login',
        int $expiryMinutes = 5
    ): bool {

        $this->expireOtp($userId, $purpose);

        $query = "
            INSERT INTO user_otps (

                user_id,
                otp,
                purpose,
                attempts,
                resend_count,
                is_used,
                expires_at,
                created_at

            ) VALUES (

                :user_id,
                :otp,
                :purpose,
                0,
                0,
                0,
                DATE_ADD(
                    NOW(),
                    INTERVAL :expiry MINUTE
                ),
                NOW()
            )
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(
            ':user_id',
            $userId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':otp',
            password_hash(
                $otp,
                PASSWORD_DEFAULT
            )
        );

        $stmt->bindValue(
            ':purpose',
            $purpose
        );

        $stmt->bindValue(
            ':expiry',
            $expiryMinutes,
            PDO::PARAM_INT
        );

        return $stmt->execute();
    }

    public function verifyOtp(
        int $userId,
        string $otp,
        string $purpose
    ): bool {

        $query = "
            SELECT *
            FROM user_otps
            WHERE user_id = :user_id
            AND purpose = :purpose
            AND is_used = 0
            AND expires_at > NOW()
            ORDER BY id DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([

            ':user_id' => $userId,
            ':purpose' => $purpose
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        if ($row['attempts'] >= 5) {
            return false;
        }

        if (!password_verify($otp, $row['otp'])) {

            $attemptQuery = "
                UPDATE user_otps
                SET attempts = attempts + 1
                WHERE id = :id
            ";

            $attemptStmt =
            $this->db->prepare($attemptQuery);

            $attemptStmt->execute([
                ':id' => $row['id']
            ]);

            return false;
        }

        $usedQuery = "
            UPDATE user_otps
            SET is_used = 1
            WHERE id = :id
        ";

        $usedStmt =
        $this->db->prepare($usedQuery);

        $usedStmt->execute([
            ':id' => $row['id']
        ]);

        return true;
    }

    public function expireOtp(
        int $userId,
        string $purpose
    ): bool {

        $query = "
            UPDATE user_otps
            SET is_used = 1
            WHERE user_id = :user_id
            AND purpose = :purpose
            AND is_used = 0
        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([

            ':user_id' => $userId,
            ':purpose' => $purpose
        ]);
    }

    public function canResendOtp(
        int $userId,
        string $purpose
    ): bool {

        $query = "
            SELECT created_at
            FROM user_otps
            WHERE user_id = :user_id
            AND purpose = :purpose
            ORDER BY id DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([

            ':user_id' => $userId,
            ':purpose' => $purpose
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return true;
        }

        return (
            time() - strtotime($row['created_at'])
        ) >= 60;
    }

    public function cleanupExpiredOtps(): bool
    {
        $query = "
            DELETE FROM user_otps
            WHERE expires_at < NOW()
        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | SESSION MANAGEMENT
    |--------------------------------------------------------------------------
    */

    public function updateSession(
        int $userId,
        string $sessionToken,
        string $fingerprintHash,
        string $deviceHash,
        string $ipAddress
    ): bool {

        $query = "
            INSERT INTO user_sessions (

                user_id,
                session_token,
                fingerprint_hash,
                device_hash,
                ip_address,
                last_activity,
                created_at

            ) VALUES (

                :user_id,
                :session_token,
                :fingerprint_hash,
                :device_hash,
                :ip_address,
                NOW(),
                NOW()
            )
        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([

            ':user_id' => $userId,
            ':session_token' => $sessionToken,
            ':fingerprint_hash' => $fingerprintHash,
            ':device_hash' => $deviceHash,
            ':ip_address' => $ipAddress
        ]);
    }

    public function validateSession(
        string $token
    ): bool {

        $query = "
            SELECT id
            FROM user_sessions
            WHERE session_token = :token
            LIMIT 1
        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            ':token' => $token
        ]);

        return (bool) $stmt->fetch();
    }

    public function invalidateUserSessions(
        int $userId
    ): bool {

        $query = "
            DELETE FROM user_sessions
            WHERE user_id = :user_id
        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ':user_id' => $userId
        ]);
    }

    public function destroyOtherSessions(
        int $userId,
        string $currentToken
    ): bool {

        $query = "
            DELETE FROM user_sessions
            WHERE user_id = :user_id
            AND session_token != :token
        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([

            ':user_id' => $userId,
            ':token' => $currentToken
        ]);
    }

    public function cleanupOldSessions(): bool
    {
        $query = "
            DELETE FROM user_sessions
            WHERE last_activity < DATE_SUB(
                NOW(),
                INTERVAL 30 DAY
            )
        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | LAST LOGIN
    |--------------------------------------------------------------------------
    */

    public function updateLastLogin(
        int $userId
    ): bool {

        $query = "
            UPDATE users
            SET last_login = NOW()
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ':id' => $userId
        ]);
    }
}