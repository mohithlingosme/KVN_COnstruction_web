<?php

declare(strict_types=1);

class User
{
    public function __construct(?PDO $database = null)
    {
    }

    private static function db(): PDO
    {
        global $conn;
        return $conn;
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => strtolower(trim($email))]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByPhone(string $phone): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE phone = :phone LIMIT 1');
        $stmt->execute([':phone' => preg_replace('/\D/', '', $phone)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function incrementFailedAttempts(int $userId): bool
    {
        return self::db()->prepare(
            'UPDATE users
             SET failed_attempts = failed_attempts + 1,
                 failed_login_attempts = failed_login_attempts + 1,
                 last_login_ip = :last_login_ip,
                 last_login_user_agent = :last_login_user_agent
             WHERE id = :id'
        )->execute([
            ':last_login_ip' => request_ip(),
            ':last_login_user_agent' => request_user_agent(),
            ':id' => $userId,
        ]);
    }

    public static function resetAttempts(int $userId): bool
    {
        return self::db()->prepare(
            'UPDATE users
             SET failed_attempts = 0,
                 failed_login_attempts = 0,
                 locked_until = NULL
             WHERE id = :id'
        )->execute([':id' => $userId]);
    }

    public static function lockAccount(int $userId, int $minutes = 15): bool
    {
        $stmt = self::db()->prepare('UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL :minutes MINUTE) WHERE id = :id');
        $stmt->bindValue(':minutes', $minutes, PDO::PARAM_INT);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function updatePassword(int $userId, string $password): bool
    {
        $hash = strlen($password) > 55 && str_starts_with($password, '$2') ? $password : hashPassword($password);

        return self::db()->prepare(
            'UPDATE users
             SET password = :password,
                 failed_attempts = 0,
                 failed_login_attempts = 0,
                 locked_until = NULL,
                 last_password_change = NOW(),
                 password_changed_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id'
        )->execute([
            ':password' => $hash,
            ':id' => $userId,
        ]);
    }

    public static function saveOtp(int $userId, string $otp, string $purpose = 'login', int $expiryMinutes = OTP_EXPIRY_MINUTES): bool
    {
        $user = self::findById($userId);

        if ($user === null) {
            return false;
        }

        expireOtp($userId, $user['phone'] ?? null, $user['email'] ?? null, $purpose);

        $stmt = self::db()->prepare(
            'INSERT INTO otps (
                user_id,
                phone,
                email,
                otp_code,
                otp_type,
                attempts,
                resend_count,
                is_used,
                expires_at,
                created_at,
                last_sent_at,
                ip_address,
                user_agent,
                verified
            ) VALUES (
                :user_id,
                :phone,
                :email,
                :otp_code,
                :otp_type,
                0,
                0,
                0,
                DATE_ADD(NOW(), INTERVAL :expiry MINUTE),
                NOW(),
                NOW(),
                :ip_address,
                :user_agent,
                0
            )'
        );

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':phone', $user['phone'] ?? null);
        $stmt->bindValue(':email', $user['email'] ?? null);
        $stmt->bindValue(':otp_code', hashPassword($otp));
        $stmt->bindValue(':otp_type', mapOtpPurpose($purpose));
        $stmt->bindValue(':expiry', $expiryMinutes, PDO::PARAM_INT);
        $stmt->bindValue(':ip_address', request_ip());
        $stmt->bindValue(':user_agent', request_user_agent());

        return $stmt->execute();
    }

    public static function invalidateUserSessions(int $userId): bool
    {
        invalidateUserSessions($userId);
        return true;
    }

    public static function destroyOtherSessions(int $userId, string $currentToken): bool
    {
        destroyOtherSessions($userId, $currentToken);
        return true;
    }

    public static function validateSession(string $token): bool
    {
        $stmt = self::db()->prepare('SELECT id FROM user_sessions WHERE session_token = :token AND is_active = 1 AND revoked_at IS NULL LIMIT 1');
        $stmt->execute([':token' => $token]);
        return (bool) $stmt->fetch();
    }

    public static function cleanupOldSessions(): bool
    {
        cleanupExpiredSessions();
        return true;
    }

    public static function updateLastLogin(int $userId): bool
    {
        return self::db()->prepare(
            'UPDATE users
             SET last_login = NOW(),
                 last_login_at = NOW(),
                 last_login_ip = :last_login_ip,
                 last_login_user_agent = :last_login_user_agent
             WHERE id = :id'
        )->execute([
            ':last_login_ip' => request_ip(),
            ':last_login_user_agent' => request_user_agent(),
            ':id' => $userId,
        ]);
    }

    public static function savePasswordResetOtp(int $userId, string $otpHash, string $expiresAt): bool
    {
        $user = self::findById($userId);

        if ($user === null) {
            return false;
        }

        expireOtp($userId, $user['phone'] ?? null, $user['email'] ?? null, 'password_reset');

        $stmt = self::db()->prepare(
            'INSERT INTO otps (
                user_id,
                phone,
                email,
                otp_code,
                otp_type,
                attempts,
                resend_count,
                is_used,
                expires_at,
                created_at,
                last_sent_at,
                ip_address,
                user_agent,
                verified
            ) VALUES (
                :user_id,
                :phone,
                :email,
                :otp_code,
                :otp_type,
                0,
                0,
                0,
                :expires_at,
                NOW(),
                NOW(),
                :ip_address,
                :user_agent,
                0
            )'
        );

        return $stmt->execute([
            ':user_id' => $userId,
            ':phone' => $user['phone'] ?? null,
            ':email' => $user['email'] ?? null,
            ':otp_code' => $otpHash,
            ':otp_type' => 'password_reset',
            ':expires_at' => $expiresAt,
            ':ip_address' => request_ip(),
            ':user_agent' => request_user_agent(),
        ]);
    }

    public static function clearPasswordResetOtp(int $userId): bool
    {
        return self::db()->prepare(
            'UPDATE otps
             SET is_used = 1, used_at = NOW()
             WHERE user_id = :user_id AND otp_type = :otp_type AND is_used = 0'
        )->execute([
            ':user_id' => $userId,
            ':otp_type' => 'password_reset',
        ]);
    }
}
