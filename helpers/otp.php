<?php

declare(strict_types=1);

function generateOtp(int $length = 6): string
{
    return str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
}

function otpExpiryDate(int $minutes = OTP_EXPIRY_MINUTES): string
{
    return date('Y-m-d H:i:s', strtotime('+' . $minutes . ' minutes'));
}

function mapOtpPurpose(string $purpose): string
{
    return match ($purpose) {
        'phone_verification', 'verify_phone' => 'phone_verification',
        'password_reset', 'reset_password' => 'password_reset',
        default => 'login',
    };
}

function storeOtp(?int $userId, ?string $phone, ?string $email, string $otp, string $purpose = 'login'): bool
{
    global $conn;

    if (!isset($conn)) {
        return false;
    }

    $purpose = mapOtpPurpose($purpose);
    expireOtp($userId, $phone, $email, $purpose);

    $stmt = $conn->prepare(
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
        ':phone' => $phone,
        ':email' => $email,
        ':otp_code' => hashPassword($otp),
        ':otp_type' => $purpose,
        ':expires_at' => otpExpiryDate(),
        ':ip_address' => request_ip(),
        ':user_agent' => request_user_agent(),
    ]);
}

function expireOtp(?int $userId, ?string $phone, ?string $email, string $purpose): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    $conditions = ['otp_type = :otp_type', 'is_used = 0'];
    $params = [':otp_type' => $purpose];

    if ($userId !== null) {
        $conditions[] = 'user_id = :user_id';
        $params[':user_id'] = $userId;
    } elseif (!empty($phone)) {
        $conditions[] = 'phone = :phone';
        $params[':phone'] = $phone;
    } elseif (!empty($email)) {
        $conditions[] = 'email = :email';
        $params[':email'] = $email;
    } else {
        return;
    }

    $conn->prepare('UPDATE otps SET is_used = 1, used_at = NOW() WHERE ' . implode(' AND ', $conditions))->execute($params);
}

function latestOtp(?int $userId, ?string $phone, ?string $email, string $purpose): ?array
{
    global $conn;

    if (!isset($conn)) {
        return null;
    }

    $conditions = ['otp_type = :otp_type', 'is_used = 0'];
    $params = [':otp_type' => mapOtpPurpose($purpose)];

    if ($userId !== null) {
        $conditions[] = 'user_id = :user_id';
        $params[':user_id'] = $userId;
    } elseif (!empty($phone)) {
        $conditions[] = 'phone = :phone';
        $params[':phone'] = $phone;
    } elseif (!empty($email)) {
        $conditions[] = 'email = :email';
        $params[':email'] = $email;
    } else {
        return null;
    }

    $stmt = $conn->prepare('SELECT * FROM otps WHERE ' . implode(' AND ', $conditions) . ' ORDER BY id DESC LIMIT 1');
    $stmt->execute($params);
    $otp = $stmt->fetch();

    return $otp ?: null;
}

function verifyStoredOtp(?int $userId, ?string $phone, ?string $email, string $otp, string $purpose): bool
{
    global $conn;

    $record = latestOtp($userId, $phone, $email, $purpose);

    if ($record === null) {
        return false;
    }

    if ((int) $record['attempts'] >= OTP_MAX_ATTEMPTS || strtotime((string) $record['expires_at']) < time()) {
        return false;
    }

    if (!verifyPassword($otp, (string) $record['otp_code'])) {
        if (isset($conn)) {
            $conn->prepare('UPDATE otps SET attempts = attempts + 1 WHERE id = :id')->execute([':id' => $record['id']]);
        }
        return false;
    }

    if (isset($conn)) {
        $conn->prepare(
            'UPDATE otps
             SET verified = 1, verified_at = NOW(), is_used = 1, used_at = NOW()
             WHERE id = :id'
        )->execute([':id' => $record['id']]);
    }

    return true;
}

function createPhoneOtp(string $phone, ?int $userId = null, ?string $email = null): array
{
    if (!validatePhone($phone)) {
        return ['success' => false, 'message' => 'Invalid phone number.'];
    }

    $otp = generateOtp();
    $success = storeOtp($userId, preg_replace('/\D/', '', $phone), $email, $otp, 'login');

    if (!$success) {
        return ['success' => false, 'message' => 'Unable to create OTP.'];
    }

    return ['success' => true, 'otp' => $otp];
}

function isOtpBlocked(string $phone): bool
{
    $record = latestOtp(null, preg_replace('/\D/', '', $phone), null, 'login');

    return $record !== null && (int) $record['attempts'] >= OTP_MAX_ATTEMPTS;
}

function createOtpSession(string $phone): void
{
    $_SESSION['otp_phone'] = preg_replace('/\D/', '', $phone);
    $_SESSION['otp_created_at'] = time();
    $_SESSION['otp_attempts'] = 0;
}

function isOtpSessionValid(): bool
{
    return !empty($_SESSION['otp_phone']) && !empty($_SESSION['otp_created_at']);
}

function destroyOtpSession(): void
{
    unset($_SESSION['otp_phone'], $_SESSION['otp_created_at'], $_SESSION['otp_attempts']);
}

function clearOtpSession(): void
{
    destroyOtpSession();
}

function cleanupExpiredOtps(): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    $conn->exec('DELETE FROM otps WHERE expires_at < NOW() OR (is_used = 1 AND created_at < DATE_SUB(NOW(), INTERVAL 1 DAY))');
}
