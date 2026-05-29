<?php

declare(strict_types=1);

initializePhpSession();

function initializePhpSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => request_is_secure(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', request_is_secure() ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', (string) SESSION_TIMEOUT);

    session_start();
}

function generateSessionToken(): string
{
    return generateSecureToken(64);
}

function generateDeviceHash(): string
{
    return secureHash(request_ip() . '|' . request_user_agent() . '|' . ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
}

function generateSessionFingerprint(): string
{
    return secureHash(request_ip() . '|' . request_user_agent());
}

function sessionDeviceName(): string
{
    return substr(request_user_agent(), 0, 255);
}

function syncLegacySessionKeys(array $user): void
{
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'] ?? null,
        'phone' => $user['phone'] ?? null,
        'role' => $user['role'],
        'status' => $user['status'] ?? 'active',
    ];

    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'] ?? null;
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['admin_id'] = $user['role'] === 'admin' ? (int) $user['id'] : null;
    $_SESSION['client_id'] = $user['role'] === 'client' ? (int) $user['id'] : null;
    $_SESSION['client_name'] = $user['role'] === 'client' ? $user['full_name'] : null;
}

function trackUserDevice(int $userId): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    $deviceHash = generateDeviceHash();

    $select = $conn->prepare('SELECT id FROM user_devices WHERE user_id = :user_id AND device_hash = :device_hash LIMIT 1');
    $select->execute([
        ':user_id' => $userId,
        ':device_hash' => $deviceHash,
    ]);

    $device = $select->fetch();

    if ($device) {
        $stmt = $conn->prepare(
            'UPDATE user_devices
             SET last_used_at = NOW(), ip_address = :ip_address, device_name = :device_name
             WHERE id = :id'
        );
        $stmt->execute([
            ':ip_address' => request_ip(),
            ':device_name' => sessionDeviceName(),
            ':id' => $device['id'],
        ]);
        return;
    }

    $stmt = $conn->prepare(
        'INSERT INTO user_devices (
            user_id,
            device_name,
            device_hash,
            ip_address,
            is_trusted,
            last_used_at,
            created_at
        ) VALUES (
            :user_id,
            :device_name,
            :device_hash,
            :ip_address,
            0,
            NOW(),
            NOW()
        )'
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':device_name' => sessionDeviceName(),
        ':device_hash' => $deviceHash,
        ':ip_address' => request_ip(),
    ]);
}

function storeSessionInDatabase(int $userId, string $sessionToken, string $role, ?string $rememberTokenHash = null): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    $stmt = $conn->prepare(
        'INSERT INTO user_sessions (
            user_id,
            session_token,
            remember_token,
            fingerprint_hash,
            last_activity,
            is_admin_session,
            ip_address,
            user_agent,
            device_name,
            expires_at,
            created_at,
            is_active
        ) VALUES (
            :user_id,
            :session_token,
            :remember_token,
            :fingerprint_hash,
            NOW(),
            :is_admin_session,
            :ip_address,
            :user_agent,
            :device_name,
            :expires_at,
            NOW(),
            1
        )'
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':session_token' => $sessionToken,
        ':remember_token' => $rememberTokenHash,
        ':fingerprint_hash' => generateSessionFingerprint(),
        ':is_admin_session' => $role === 'admin' ? 1 : 0,
        ':ip_address' => request_ip(),
        ':user_agent' => request_user_agent(),
        ':device_name' => sessionDeviceName(),
        ':expires_at' => date('Y-m-d H:i:s', strtotime('+' . REMEMBER_ME_DAYS . ' days')),
    ]);

    trackUserDevice($userId);
}

function markSuspiciousLoginIfNeeded(array $user): void
{
    $reasons = [];

    if (!empty($user['last_login_ip']) && $user['last_login_ip'] !== request_ip()) {
        $reasons[] = 'new_ip';
    }

    if (!empty($user['last_login_user_agent']) && $user['last_login_user_agent'] !== request_user_agent()) {
        $reasons[] = 'new_device';
    }

    if ($reasons === []) {
        return;
    }

    logSecurityEvent((int) $user['id'], 'suspicious_login_detected', 'warning', [
        'reasons' => $reasons,
        'ip' => request_ip(),
        'user_agent' => request_user_agent(),
    ]);
}

function issueRememberMeToken(int $userId, string $sessionToken): void
{
    global $conn;

    $rawToken = generateSecureToken(80);
    $tokenHash = secureHash($rawToken);

    if (isset($conn)) {
        $stmt = $conn->prepare('UPDATE user_sessions SET remember_token = :remember_token WHERE session_token = :session_token');
        $stmt->execute([
            ':remember_token' => $tokenHash,
            ':session_token' => $sessionToken,
        ]);
    }

    setcookie(
        'remember_token',
        $rawToken,
        [
            'expires' => time() + (REMEMBER_ME_DAYS * 86400),
            'path' => '/',
            'secure' => request_is_secure(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );

    if (isset($conn)) {
        $conn->prepare('UPDATE users SET remember_token = :remember_token WHERE id = :id')->execute([
            ':remember_token' => $tokenHash,
            ':id' => $userId,
        ]);
    }
}

function clearRememberMeToken(?string $sessionToken = null, ?int $userId = null): void
{
    global $conn;

    if (isset($conn) && $sessionToken !== null) {
        $conn->prepare('UPDATE user_sessions SET remember_token = NULL WHERE session_token = :session_token')->execute([
            ':session_token' => $sessionToken,
        ]);
    }

    if (isset($conn) && $userId !== null) {
        $conn->prepare('UPDATE users SET remember_token = NULL WHERE id = :id')->execute([
            ':id' => $userId,
        ]);
    }

    setcookie('remember_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => request_is_secure(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function initializeSessionSecurity(array $user, bool $rememberMe = false): void
{
    session_regenerate_id(true);

    $sessionToken = generateSessionToken();

    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['session_token'] = $sessionToken;
    $_SESSION['fingerprint'] = generateSessionFingerprint();
    $_SESSION['device_hash'] = generateDeviceHash();
    $_SESSION['last_activity'] = time();
    $_SESSION['login_time'] = time();
    $_SESSION['is_admin'] = $user['role'] === 'admin';

    syncLegacySessionKeys($user);
    storeSessionInDatabase((int) $user['id'], $sessionToken, (string) $user['role']);

    if ($rememberMe) {
        issueRememberMeToken((int) $user['id'], $sessionToken);
    }

    markSuspiciousLoginIfNeeded($user);

    logSecurityEvent((int) $user['id'], 'session_initialized', 'info', [
        'role' => $user['role'],
        'remember_me' => $rememberMe,
    ]);
}

function createUserSession(array $user, bool $rememberMe = false): void
{
    initializeSessionSecurity($user, $rememberMe);
}

function createAdminSession(array $user, bool $rememberMe = false): void
{
    initializeSessionSecurity($user, $rememberMe);
}

function refreshSession(): void
{
    global $conn;

    if (empty($_SESSION['session_token'])) {
        return;
    }

    $_SESSION['last_activity'] = time();

    if (!isset($conn)) {
        return;
    }

    $stmt = $conn->prepare(
        'UPDATE user_sessions
         SET last_activity = NOW(), ip_address = :ip_address, user_agent = :user_agent, is_active = 1
         WHERE session_token = :session_token'
    );
    $stmt->execute([
        ':ip_address' => request_ip(),
        ':user_agent' => request_user_agent(),
        ':session_token' => $_SESSION['session_token'],
    ]);

    if (!empty($_SESSION['user_id'])) {
        $conn->prepare(
            'UPDATE users
             SET last_activity_at = NOW(), last_ip = :last_ip, last_user_agent = :last_user_agent
             WHERE id = :id'
        )->execute([
            ':last_ip' => request_ip(),
            ':last_user_agent' => request_user_agent(),
            ':id' => $_SESSION['user_id'],
        ]);
    }
}

function validateSession(): bool
{
    global $conn;

    if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id']) || empty($_SESSION['session_token'])) {
        return false;
    }

    if (($_SESSION['fingerprint'] ?? '') !== generateSessionFingerprint()) {
        logSecurityEvent((int) ($_SESSION['user_id'] ?? 0), 'session_hijack_attempt', 'critical', 'Fingerprint mismatch');
        destroySession();
        return false;
    }

    if (($_SESSION['device_hash'] ?? '') !== generateDeviceHash()) {
        logSecurityEvent((int) ($_SESSION['user_id'] ?? 0), 'session_device_mismatch', 'warning', 'Device hash mismatch');
        destroySession();
        return false;
    }

    $timeout = isAdmin() ? ADMIN_SESSION_TIMEOUT : SESSION_TIMEOUT;

    if ((time() - (int) ($_SESSION['last_activity'] ?? 0)) > $timeout) {
        logSecurityEvent((int) $_SESSION['user_id'], 'session_timeout', 'warning', 'Session expired');
        destroySession('timeout');
        return false;
    }

    if (!isset($conn)) {
        return true;
    }

    $stmt = $conn->prepare(
        'SELECT us.*, u.status, u.role, u.full_name, u.email, u.phone
         FROM user_sessions us
         INNER JOIN users u ON u.id = us.user_id
         WHERE us.session_token = :session_token
         LIMIT 1'
    );
    $stmt->execute([':session_token' => $_SESSION['session_token']]);
    $session = $stmt->fetch();

    if (!$session || (int) ($session['is_active'] ?? 0) !== 1 || !empty($session['revoked_at'])) {
        destroySession('revoked');
        return false;
    }

    if (($session['status'] ?? 'inactive') !== 'active') {
        destroySession('user_inactive');
        return false;
    }

    $_SESSION['role'] = $session['role'];
    $_SESSION['is_admin'] = $session['role'] === 'admin';

    syncLegacySessionKeys($session);
    refreshSession();

    return true;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return ($_SESSION['role'] ?? null) === 'admin';
}

function isClient(): bool
{
    return ($_SESSION['role'] ?? null) === 'client';
}

function currentUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function currentUserRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

function sessionRemainingTime(): int
{
    $timeout = isAdmin() ? ADMIN_SESSION_TIMEOUT : SESSION_TIMEOUT;
    return max(0, $timeout - (time() - (int) ($_SESSION['last_activity'] ?? 0)));
}

function revokeSessionByToken(string $sessionToken, string $reason = 'manual_logout'): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    $stmt = $conn->prepare(
        'UPDATE user_sessions
         SET is_active = 0, revoked_at = NOW(), logout_reason = :logout_reason, remember_token = NULL
         WHERE session_token = :session_token'
    );
    $stmt->execute([
        ':logout_reason' => $reason,
        ':session_token' => $sessionToken,
    ]);
}

function destroyOtherSessions(int $userId, string $currentToken): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    $stmt = $conn->prepare(
        'UPDATE user_sessions
         SET is_active = 0, revoked_at = NOW(), logout_reason = :logout_reason, remember_token = NULL
         WHERE user_id = :user_id AND session_token <> :session_token'
    );
    $stmt->execute([
        ':logout_reason' => 'other_sessions_revoked',
        ':user_id' => $userId,
        ':session_token' => $currentToken,
    ]);
}

function invalidateUserSessions(int $userId, ?string $exceptSessionToken = null, string $reason = 'invalidated'): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    $sql = 'UPDATE user_sessions SET is_active = 0, revoked_at = NOW(), logout_reason = :logout_reason, remember_token = NULL WHERE user_id = :user_id';
    $params = [
        ':logout_reason' => $reason,
        ':user_id' => $userId,
    ];

    if ($exceptSessionToken !== null) {
        $sql .= ' AND session_token <> :session_token';
        $params[':session_token'] = $exceptSessionToken;
    }

    $conn->prepare($sql)->execute($params);
    clearRememberMeToken(null, $userId);
}

function destroySession(string $reason = 'logout'): void
{
    $sessionToken = $_SESSION['session_token'] ?? null;
    $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

    if (is_string($sessionToken) && $sessionToken !== '') {
        revokeSessionByToken($sessionToken, $reason);
        clearRememberMeToken($sessionToken, $userId);
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => request_is_secure(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    session_destroy();
}

function logout(): void
{
    if (isLoggedIn()) {
        logSecurityEvent((int) $_SESSION['user_id'], 'logout', 'info', 'User logged out');
    }

    destroySession('logout');
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

function requireLogin(): void
{
    if (validateSession()) {
        return;
    }

    $_SESSION['error'] = 'Please login to continue.';
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

function requireAdmin(): void
{
    if (validateSession() && isAdmin()) {
        return;
    }

    $_SESSION['error'] = 'Admin authentication required.';
    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}

function requireClient(): void
{
    if (validateSession() && isClient()) {
        return;
    }

    $_SESSION['error'] = 'Client authentication required.';
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

function getUserSessions(int $userId, bool $includeInactive = false): array
{
    global $conn;

    if (!isset($conn)) {
        return [];
    }

    $sql = 'SELECT * FROM user_sessions WHERE user_id = :user_id';

    if (!$includeInactive) {
        $sql .= ' AND is_active = 1 AND revoked_at IS NULL';
    }

    $sql .= ' ORDER BY last_activity DESC, created_at DESC';

    $stmt = $conn->prepare($sql);
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetchAll() ?: [];
}

function restoreRememberedSession(): void
{
    global $conn;

    if (isLoggedIn() || empty($_COOKIE['remember_token']) || !isset($conn)) {
        return;
    }

    $tokenHash = secureHash((string) $_COOKIE['remember_token']);

    $stmt = $conn->prepare(
        'SELECT us.*, u.*
         FROM user_sessions us
         INNER JOIN users u ON u.id = us.user_id
         WHERE us.remember_token = :remember_token
           AND us.is_active = 1
           AND us.revoked_at IS NULL
           AND (us.expires_at IS NULL OR us.expires_at > NOW())
         LIMIT 1'
    );
    $stmt->execute([':remember_token' => $tokenHash]);
    $user = $stmt->fetch();

    if (!$user || ($user['status'] ?? 'inactive') !== 'active') {
        clearRememberMeToken();
        return;
    }

    initializeSessionSecurity($user, true);
    logSecurityEvent((int) $user['id'], 'remember_me_restored', 'info', 'Remember-me session restored');
}

function cleanupExpiredSessions(): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    $conn->exec(
        "UPDATE user_sessions
         SET is_active = 0, revoked_at = NOW(), logout_reason = 'expired', remember_token = NULL
         WHERE is_active = 1
           AND (
               (expires_at IS NOT NULL AND expires_at < NOW())
               OR last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY)
           )"
    );
}
