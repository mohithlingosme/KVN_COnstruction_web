<?php

declare(strict_types=1);

/**
 * SQLite fixture for OTP + users.
 * Creates minimal schema used by AuthController + User model.
 */

function buildOtpFixture(PDO $pdo, string $mode = 'all'): void
{
    $pdo->exec('PRAGMA foreign_keys = ON');

    // Avoid side-effect errors during tests
    $pdo->exec('CREATE TABLE IF NOT EXISTS security_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        event_type TEXT,
        severity TEXT,
        details TEXT,
        ip_address TEXT,
        user_agent TEXT,
        created_at TEXT
    )');

    $pdo->exec('DROP TABLE IF EXISTS user_otps');
    $pdo->exec('DROP TABLE IF EXISTS users');

    $pdo->exec(
        'CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            phone TEXT,
            email TEXT,
            full_name TEXT,
            name TEXT,
            status TEXT,
            role TEXT,
            password TEXT,
            locked_until TEXT,
            last_login_ip TEXT,
            last_login_user_agent TEXT,
            last_activity_at TEXT,
            last_ip TEXT,
            last_user_agent TEXT,
            deleted_at TEXT,
            created_at TEXT,
            updated_at TEXT,
            last_login TEXT
        )'
    );

    $pdo->exec(
        'CREATE TABLE user_otps (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            purpose TEXT NOT NULL,
            otp TEXT NOT NULL,
            resend_count INTEGER NOT NULL DEFAULT 0,
            ip_address TEXT,
            user_agent TEXT,
            expires_at TEXT NOT NULL,
            is_used INTEGER NOT NULL DEFAULT 0,
            attempts INTEGER NOT NULL DEFAULT 0,
            deleted_at TEXT,
            created_at TEXT,
            updated_at TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )'
    );

    // Ensure now() works in controller SQL on SQLite.
    if (function_exists('registerSqliteNow')) {
        registerSqliteNow($pdo);
    }

    $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
    $future = (new DateTimeImmutable('+10 minutes'))->format('Y-m-d H:i:s');
    $past = (new DateTimeImmutable('-10 minutes'))->format('Y-m-d H:i:s');

    // Active user
    $stmt = $pdo->prepare(
        'INSERT INTO users (
            phone, email, full_name, name, status, role, password,
            locked_until, last_login_ip, last_login_user_agent,
            last_activity_at, last_ip, last_user_agent,
            deleted_at, created_at, updated_at, last_login
        ) VALUES (
            :phone, :email, :full_name, :name, :status, :role, :password,
            :locked_until, :last_login_ip, :last_login_user_agent,
            :last_activity_at, :last_ip, :last_user_agent,
            :deleted_at, :created_at, :updated_at, :last_login
        )'
    );

    $stmt->execute([
        ':phone' => '9999999999',
        ':email' => 'user@example.com',
        ':full_name' => 'Test User',
        ':name' => 'Test User',
        ':status' => 'active',
        ':role' => 'client',
        ':password' => password_hash('dummy', PASSWORD_BCRYPT),
        ':locked_until' => null,
        ':last_login_ip' => null,
        ':last_login_user_agent' => null,
        ':last_activity_at' => null,
        ':last_ip' => null,
        ':last_user_agent' => null,
        ':deleted_at' => null,
        ':created_at' => $now,
        ':updated_at' => $now,
        ':last_login' => null,
    ]);

    $userId = (int)$pdo->lastInsertId();

    // Mode-specific seeding.
    // AuthController selects latest OTP by: ORDER BY id DESC, and only where expires_at > NOW(), is_used=0, purpose='login'.
    // To make tests deterministic we ensure the *latest qualifying row* is the one we want.

    $insertOtp = function (string $plain, string $expiresAt, int $attempts) use ($pdo, $userId, $now): void {
        $hashed = password_hash($plain, PASSWORD_BCRYPT);
        $pdo->prepare(
            'INSERT INTO user_otps (user_id, purpose, otp, resend_count, ip_address, user_agent, expires_at, is_used, attempts, deleted_at, created_at, updated_at)
             VALUES (:user_id, :purpose, :otp, :resend_count, :ip_address, :user_agent, :expires_at, :is_used, :attempts, :deleted_at, :created_at, :updated_at)'
        )->execute([
            ':user_id' => $userId,
            ':purpose' => 'login',
            ':otp' => $hashed,
            ':resend_count' => 0,
            ':ip_address' => '127.0.0.1',
            ':user_agent' => 'unit-test',
            ':expires_at' => $expiresAt,
            ':is_used' => 0,
            ':attempts' => $attempts,
            ':deleted_at' => null,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
    };

    if ($mode === 'happy') {
        // Latest valid OTP only
        $insertOtp('123456', $future, 0);
        return;
    }

    if ($mode === 'expired') {
        // Latest OTP is expired, and there are NO later valid OTP rows => should return 'OTP expired.'
        $insertOtp('654321', $past, 0);
        return;
    }

    if ($mode === 'attempt_limit') {
        // Latest valid OTP has attempts >= 5
        $insertOtp('111222', $future, 5);
        return;
    }

    // default: all three rows (for backwards compatibility)
    // Create older attempt-limit first, then expired, then happy so happy is the latest valid.
    $insertOtp('111222', $future, 5);
    $insertOtp('654321', $past, 0);
    $insertOtp('123456', $future, 0);


}

function getFixtureUserId(PDO $pdo): int
{
    $stmt = $pdo->query('SELECT id FROM users ORDER BY id DESC LIMIT 1');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)($row['id'] ?? 0);
}

