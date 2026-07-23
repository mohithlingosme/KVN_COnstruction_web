<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use Exception;

class User
{
    private PDO $db;

    public function __construct(PDO $database)
    {
        $this->db = $database;
    }

    /* =========================================================
       FIND USERS
    ========================================================= */

    public function findById(int $id): ?array
    {
        $query = "

            SELECT *

            FROM users

            WHERE id = :id

            AND deleted_at IS NULL

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

            AND deleted_at IS NULL

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

            AND deleted_at IS NULL

            LIMIT 1

        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            ':phone' => trim($phone)
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /* =========================================================
       ACCOUNT STATUS
    ========================================================= */

    public function isActive(int $userId): bool
    {
        $query = "

            SELECT status

            FROM users

            WHERE id = :id

            AND deleted_at IS NULL

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

    /* =========================================================
       LOGIN ATTEMPTS
    ========================================================= */

    public function incrementFailedAttempts(int $userId): bool
    {
        try {

            $this->db->beginTransaction();

            $query = "

                UPDATE users

                SET failed_attempts = failed_attempts + 1

                WHERE id = :id

            ";

            $stmt = $this->db->prepare($query);

            $stmt->execute([
                ':id' => $userId
            ]);

            /*
            |--------------------------------------------------------------------------
            | AUTO LOCK ACCOUNT
            |--------------------------------------------------------------------------
            */

            $lockQuery = "

                UPDATE users

                SET locked_until = DATE_ADD(
                    NOW(),
                    INTERVAL 15 MINUTE
                )

                WHERE id = :id

                AND failed_attempts >= 5

            ";

            $lockStmt = $this->db->prepare($lockQuery);

            $lockStmt->execute([
                ':id' => $userId
            ]);

            $this->db->commit();

            return true;

        } catch (PDOException $e) {

            $this->db->rollBack();

            error_log($e->getMessage());

            return false;
        }
    }

    public function resetAttempts(int $userId): bool
    {
        $query = "

            UPDATE users

            SET

                failed_attempts = 0,
                locked_until = NULL,
                updated_at = NOW()

            WHERE id = :id

        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ':id' => $userId
        ]);
    }

    /* =========================================================
       PASSWORD MANAGEMENT
    ========================================================= */

    public function updatePassword(
        int $userId,
        string $password
    ): bool {

        try {

            $this->db->beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | HASH PASSWORD
            |--------------------------------------------------------------------------
            */

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            /*
            |--------------------------------------------------------------------------
            | UPDATE PASSWORD
            |--------------------------------------------------------------------------
            */

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

            $stmt->execute([

                ':password' => $hashedPassword,

                ':id' => $userId
            ]);

            /*
            |--------------------------------------------------------------------------
            | PASSWORD HISTORY
            |--------------------------------------------------------------------------
            */

            $historyQuery = "

                INSERT INTO password_histories (

                    user_id,
                    password_hash,
                    created_at

                ) VALUES (

                    :user_id,
                    :password_hash,
                    NOW()

                )

            ";

            $historyStmt =
            $this->db->prepare($historyQuery);

            $historyStmt->execute([

                ':user_id' => $userId,
                ':password_hash' => $hashedPassword
            ]);

            /*
            |--------------------------------------------------------------------------
            | INVALIDATE SESSIONS
            |--------------------------------------------------------------------------
            */

            $this->invalidateUserSessions($userId);

            $this->db->commit();

            return true;

        } catch (PDOException $e) {

            $this->db->rollBack();

            error_log($e->getMessage());

            return false;
        }
    }

    /* =========================================================
       OTP SYSTEM
    ========================================================= */

    public function saveOtp(
        int $userId,
        string $otp,
        string $purpose = 'login',
        int $expiryMinutes = 5
    ): bool {

        try {

            $this->db->beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | EXPIRE OLD OTPs
            |--------------------------------------------------------------------------
            */

            $this->expireOtp(
                $userId,
                $purpose
            );

            /*
            |--------------------------------------------------------------------------
            | INSERT OTP
            |--------------------------------------------------------------------------
            */

            $query = "

                INSERT INTO user_otps (

                    user_id,
                    otp,
                    purpose,
                    attempts,
                    resend_count,
                    ip_address,
                    user_agent,
                    is_used,
                    expires_at,
                    created_at

                ) VALUES (

                    :user_id,
                    :otp,
                    :purpose,
                    0,
                    0,
                    :ip_address,
                    :user_agent,
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

            $stmt->bindValue(
                ':ip_address',
                $_SERVER['REMOTE_ADDR'] ?? null
            );

            $stmt->bindValue(
                ':user_agent',
                $_SERVER['HTTP_USER_AGENT'] ?? null
            );

            $stmt->execute();

            $this->db->commit();

            return true;

        } catch (PDOException $e) {

            $this->db->rollBack();

            error_log($e->getMessage());

            return false;
        }
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

            AND deleted_at IS NULL

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

        /*
        |--------------------------------------------------------------------------
        | ATTEMPT LIMIT
        |--------------------------------------------------------------------------
        */

        if ((int)$row['attempts'] >= 5) {

            logSecurityEvent(
                $userId,
                'otp_limit_exceeded',
                'warning',
                'OTP attempts exceeded for user_id: ' . $userId
            );

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFY OTP
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | MARK USED
        |--------------------------------------------------------------------------
        */

        $usedQuery = "

            UPDATE user_otps

            SET

                is_used = 1

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

            SET

                is_used = 1,
                deleted_at = NOW()

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

    /* =========================================================
       SESSION MANAGEMENT
    ========================================================= */

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
                user_agent,
                is_active,
                expires_at,
                last_activity,
                created_at

            ) VALUES (

                :user_id,
                :session_token,
                :fingerprint_hash,
                :device_hash,
                :ip_address,
                :user_agent,
                1,
                DATE_ADD(
                    NOW(),
                    INTERVAL 30 DAY
                ),
                NOW(),
                NOW()

            )

        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([

            ':user_id' => $userId,

            ':session_token' => hash(
                'sha256',
                $sessionToken
            ),

            ':fingerprint_hash' => $fingerprintHash,

            ':device_hash' => $deviceHash,

            ':ip_address' => $ipAddress,

            ':user_agent' =>
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    public function validateSession(
        string $token
    ): bool {

        $query = "

            SELECT id

            FROM user_sessions

            WHERE session_token = :token

            AND is_active = 1

            AND expires_at > NOW()

            LIMIT 1

        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([

            ':token' => hash(
                'sha256',
                $token
            )
        ]);

        return (bool) $stmt->fetch();
    }

    public function invalidateUserSessions(
        int $userId
    ): bool {

        $query = "

            UPDATE user_sessions

            SET

                is_active = 0,
                revoked_at = NOW()

            WHERE user_id = :user_id

        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ':user_id' => $userId
        ]);
    }

    /* =========================================================
       LAST LOGIN
    ========================================================= */

    public function updateLastLogin(
        int $userId
    ): bool {

        $query = "

            UPDATE users

            SET

                last_login = NOW(),
                updated_at = NOW()

            WHERE id = :id

        ";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ':id' => $userId
        ]);
    }
}
?>