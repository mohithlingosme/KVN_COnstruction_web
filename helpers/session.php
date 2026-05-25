<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION
|--------------------------------------------------------------------------
| ENTERPRISE SESSION SECURITY SYSTEM
|--------------------------------------------------------------------------
| File:
| /helpers/session.php
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| SECURE SESSION CONFIGURATION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    /*
    |--------------------------------------------------------------------------
    | HTTPS DETECTION
    |--------------------------------------------------------------------------
    */

    $isHttps =

        (
            isset($_SERVER['HTTPS'])
            &&
            $_SERVER['HTTPS'] !== 'off'
        )

        ||

        (
            ($_SERVER['SERVER_PORT'] ?? 80)
            == 443
        );

    /*
    |--------------------------------------------------------------------------
    | SECURE COOKIE SETTINGS
    |--------------------------------------------------------------------------
    */

    session_set_cookie_params([

        'lifetime' => 0,

        'path' => '/',

        'domain' => '',

        'secure' => $isHttps,

        'httponly' => true,

        'samesite' => 'Strict'
    ]);

    /*
    |--------------------------------------------------------------------------
    | SESSION HARDENING
    |--------------------------------------------------------------------------
    */

    ini_set('session.use_only_cookies', '1');

    ini_set('session.use_strict_mode', '1');

    ini_set('session.cookie_httponly', '1');

    ini_set('session.cookie_secure', $isHttps ? '1' : '0');

    ini_set('session.cookie_samesite', 'Strict');

    ini_set('session.gc_maxlifetime', '3600');

    session_name('KVNSESSID');

    session_start();
}

/*
|--------------------------------------------------------------------------
| SESSION CONSTANTS
|--------------------------------------------------------------------------
*/

if (!defined('SESSION_TIMEOUT')) {

    define('SESSION_TIMEOUT', 3600);
}

if (!defined('ADMIN_SESSION_TIMEOUT')) {

    define('ADMIN_SESSION_TIMEOUT', 1800);
}

/*
|--------------------------------------------------------------------------
| GENERATE SESSION TOKEN
|--------------------------------------------------------------------------
*/

function generateSessionToken(): string
{
    return bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| GENERATE DEVICE HASH
|--------------------------------------------------------------------------
*/

function generateDeviceHash(): string
{
    return hash(

        'sha256',

        ($_SERVER['REMOTE_ADDR'] ?? '')

        .

        ($_SERVER['HTTP_USER_AGENT'] ?? '')

        .

        ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')
    );
}

/*
|--------------------------------------------------------------------------
| GENERATE SESSION FINGERPRINT
|--------------------------------------------------------------------------
*/

function generateSessionFingerprint(): string
{
    return hash(

        'sha256',

        ($_SERVER['REMOTE_ADDR'] ?? '')

        .

        ($_SERVER['HTTP_USER_AGENT'] ?? '')
    );
}

/*
|--------------------------------------------------------------------------
| STORE SESSION IN DATABASE
|--------------------------------------------------------------------------
*/

function storeSessionInDatabase(
    int $userId,
    string $sessionToken,
    string $role
): void {

    global $conn;

    try {

        $query = "

            INSERT INTO user_sessions (

                user_id,
                session_token,
                fingerprint_hash,
                device_hash,
                ip_address,
                user_agent,
                is_admin_session,
                last_activity,
                created_at

            )

            VALUES (

                :user_id,
                :session_token,
                :fingerprint_hash,
                :device_hash,
                :ip_address,
                :user_agent,
                :is_admin_session,
                NOW(),
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':user_id' =>
            $userId,

            ':session_token' =>
            $sessionToken,

            ':fingerprint_hash' =>
            generateSessionFingerprint(),

            ':device_hash' =>
            generateDeviceHash(),

            ':ip_address' =>
            $_SERVER['REMOTE_ADDR']
            ?? null,

            ':user_agent' =>
            $_SERVER['HTTP_USER_AGENT']
            ?? 'Unknown Device',

            ':is_admin_session' =>

                $role === 'admin'
                ? 1
                : 0
        ]);

    } catch (Exception $e) {

        error_log(
            'Session DB Error: '
            .
            $e->getMessage()
        );
    }
}

/*
|--------------------------------------------------------------------------
| INITIALIZE SESSION SECURITY
|--------------------------------------------------------------------------
*/

function initializeSessionSecurity(array $user): void
{
    /*
    |--------------------------------------------------------------------------
    | REGENERATE SESSION
    |--------------------------------------------------------------------------
    */

    session_regenerate_id(true);

    /*
    |--------------------------------------------------------------------------
    | SESSION TOKEN
    |--------------------------------------------------------------------------
    */

    $sessionToken =
    generateSessionToken();

    /*
    |--------------------------------------------------------------------------
    | SESSION DATA
    |--------------------------------------------------------------------------
    */

    $_SESSION['logged_in'] = true;

    $_SESSION['user_id'] =
    (int) $user['id'];

    $_SESSION['user_name'] =
    $user['full_name'];

    $_SESSION['role'] =
    $user['role'];

    $_SESSION['session_token'] =
    $sessionToken;

    $_SESSION['fingerprint'] =
    generateSessionFingerprint();

    $_SESSION['device_hash'] =
    generateDeviceHash();

    $_SESSION['last_activity'] =
    time();

    $_SESSION['login_time'] =
    time();

    $_SESSION['is_admin'] =

        $user['role']
        ===
        'admin';

    /*
    |--------------------------------------------------------------------------
    | STORE SESSION
    |--------------------------------------------------------------------------
    */

    storeSessionInDatabase(

        (int) $user['id'],

        $sessionToken,

        $user['role']
    );

    /*
    |--------------------------------------------------------------------------
    | SECURITY LOG
    |--------------------------------------------------------------------------
    */

    if (function_exists('logSecurityEvent')) {

        logSecurityEvent(

            $user['id'],

            'session_initialized',

            'info',

            'Secure session initialized'
        );
    }
}

/*
|--------------------------------------------------------------------------
| VALIDATE SESSION
|--------------------------------------------------------------------------
*/

function validateSession(): bool
{
    global $conn;

    /*
    |--------------------------------------------------------------------------
    | LOGIN CHECK
    |--------------------------------------------------------------------------
    */

    if (

        !isset($_SESSION['logged_in'])

        ||

        $_SESSION['logged_in'] !== true
    ) {

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | TOKEN CHECK
    |--------------------------------------------------------------------------
    */

    if (

        empty($_SESSION['session_token'])
    ) {

        destroySession();

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | FINGERPRINT CHECK
    |--------------------------------------------------------------------------
    */

    $currentFingerprint =
    generateSessionFingerprint();

    if (

        !isset($_SESSION['fingerprint'])

        ||

        $_SESSION['fingerprint']
        !==
        $currentFingerprint
    ) {

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                $_SESSION['user_id'] ?? null,

                'session_hijack_attempt',

                'critical',

                'Session fingerprint mismatch'
            );
        }

        destroySession();

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | DEVICE HASH CHECK
    |--------------------------------------------------------------------------
    */

    $currentDeviceHash =
    generateDeviceHash();

    if (

        !isset($_SESSION['device_hash'])

        ||

        $_SESSION['device_hash']
        !==
        $currentDeviceHash
    ) {

        destroySession();

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | SESSION TIMEOUT
    |--------------------------------------------------------------------------
    */

    $timeout =

        isAdmin()

        ?

        ADMIN_SESSION_TIMEOUT

        :

        SESSION_TIMEOUT;

    if (

        isset($_SESSION['last_activity'])

        &&

        (

            time()

            -

            $_SESSION['last_activity']
        )

        >

        $timeout
    ) {

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                $_SESSION['user_id'] ?? null,

                'session_timeout',

                'warning',

                'Session expired due to inactivity'
            );
        }

        destroySession();

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | DATABASE SESSION VALIDATION
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            SELECT id

            FROM user_sessions

            WHERE session_token = :token

            LIMIT 1
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':token' =>
            $_SESSION['session_token']
        ]);

        $session =
        $stmt->fetch();

        if (!$session) {

            destroySession();

            return false;
        }

    } catch (Exception $e) {

        error_log(
            $e->getMessage()
        );

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ACTIVITY
    |--------------------------------------------------------------------------
    */

    refreshSession();

    return true;
}

/*
|--------------------------------------------------------------------------
| REFRESH SESSION
|--------------------------------------------------------------------------
*/

function refreshSession(): void
{
    global $conn;

    $_SESSION['last_activity'] =
    time();

    try {

        $query = "

            UPDATE user_sessions

            SET

                last_activity = NOW()

            WHERE session_token = :token
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':token' =>
            $_SESSION['session_token']
        ]);

    } catch (Exception $e) {

        error_log(
            $e->getMessage()
        );
    }
}

/*
|--------------------------------------------------------------------------
| DESTROY OTHER SESSIONS
|--------------------------------------------------------------------------
*/

function destroyOtherSessions(
    int $userId,
    string $currentToken
): void {

    global $conn;

    try {

        $query = "

            DELETE FROM user_sessions

            WHERE user_id = :user_id

            AND session_token != :token
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':user_id' =>
            $userId,

            ':token' =>
            $currentToken
        ]);

    } catch (Exception $e) {

        error_log(
            $e->getMessage()
        );
    }
}

/*
|--------------------------------------------------------------------------
| DESTROY SESSION
|--------------------------------------------------------------------------
*/

function destroySession(): void
{
    global $conn;

    /*
    |--------------------------------------------------------------------------
    | REMOVE DATABASE SESSION
    |--------------------------------------------------------------------------
    */

    if (

        isset($_SESSION['session_token'])
    ) {

        try {

            $query = "

                DELETE FROM user_sessions

                WHERE session_token = :token
            ";

            $stmt =
            $conn->prepare($query);

            $stmt->execute([

                ':token' =>
                $_SESSION['session_token']
            ]);

        } catch (Exception $e) {

            error_log(
                $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAR SESSION ARRAY
    |--------------------------------------------------------------------------
    */

    $_SESSION = [];

    /*
    |--------------------------------------------------------------------------
    | DESTROY COOKIE
    |--------------------------------------------------------------------------
    */

    if (ini_get('session.use_cookies')) {

        $params =
        session_get_cookie_params();

        setcookie(

            session_name(),

            '',

            time() - 42000,

            $params['path'],

            $params['domain'],

            $params['secure'],

            $params['httponly']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY SESSION
    |--------------------------------------------------------------------------
    */

    session_destroy();
}

/*
|--------------------------------------------------------------------------
| LOGOUT USER
|--------------------------------------------------------------------------
*/

function logout(): void
{
    if (

        function_exists('logSecurityEvent')

        &&

        isset($_SESSION['user_id'])
    ) {

        logSecurityEvent(

            $_SESSION['user_id'],

            'logout',

            'info',

            'User logged out'
        );
    }

    destroySession();

    header(

        'Location: '
        .
        APP_URL
        .
        '/login.php'
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

function isLoggedIn(): bool
{
    return (

        isset($_SESSION['logged_in'])

        &&

        $_SESSION['logged_in'] === true
    );
}

/*
|--------------------------------------------------------------------------
| ADMIN CHECK
|--------------------------------------------------------------------------
*/

function isAdmin(): bool
{
    return (

        isset($_SESSION['role'])

        &&

        $_SESSION['role'] === 'admin'
    );
}

/*
|--------------------------------------------------------------------------
| CLIENT CHECK
|--------------------------------------------------------------------------
*/

function isClient(): bool
{
    return (

        isset($_SESSION['role'])

        &&

        $_SESSION['role'] === 'client'
    );
}

/*
|--------------------------------------------------------------------------
| REQUIRE LOGIN
|--------------------------------------------------------------------------
*/

function requireLogin(): void
{
    if (!validateSession()) {

        header(

            'Location: '
            .
            APP_URL
            .
            '/login.php'
        );

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| REQUIRE ADMIN
|--------------------------------------------------------------------------
*/

function requireAdmin(): void
{
    if (

        !validateSession()

        ||

        !isAdmin()
    ) {

        header(

            'Location: '
            .
            APP_URL
            .
            '/admin/login.php'
        );

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| REQUIRE CLIENT
|--------------------------------------------------------------------------
*/

function requireClient(): void
{
    if (

        !validateSession()

        ||

        !isClient()
    ) {

        header(

            'Location: '
            .
            APP_URL
            .
            '/login.php'
        );

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| CURRENT USER ID
|--------------------------------------------------------------------------
*/

function currentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/*
|--------------------------------------------------------------------------
| CURRENT USER ROLE
|--------------------------------------------------------------------------
*/

function currentUserRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

/*
|--------------------------------------------------------------------------
| SESSION REMAINING TIME
|--------------------------------------------------------------------------
*/

function sessionRemainingTime(): int
{
    $timeout =

        isAdmin()

        ?

        ADMIN_SESSION_TIMEOUT

        :

        SESSION_TIMEOUT;

    return max(

        0,

        $timeout

        -

        (

            time()

            -

            ($_SESSION['last_activity'] ?? 0)
        )
    );
}

/*
|--------------------------------------------------------------------------
| CLEANUP EXPIRED SESSIONS
|--------------------------------------------------------------------------
*/

function cleanupExpiredSessions(): void
{
    global $conn;

    try {

        $query = "

            DELETE FROM user_sessions

            WHERE last_activity

            <

            DATE_SUB(

                NOW(),

                INTERVAL 1 DAY
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute();

    } catch (Exception $e) {

        error_log(
            $e->getMessage()
        );
    }
}

?><?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION
|--------------------------------------------------------------------------
| ENTERPRISE SESSION SECURITY SYSTEM
|--------------------------------------------------------------------------
| File:
| /helpers/session.php
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| SECURE SESSION CONFIGURATION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    /*
    |--------------------------------------------------------------------------
    | HTTPS DETECTION
    |--------------------------------------------------------------------------
    */

    $isHttps =

        (
            isset($_SERVER['HTTPS'])
            &&
            $_SERVER['HTTPS'] !== 'off'
        )

        ||

        (
            ($_SERVER['SERVER_PORT'] ?? 80)
            == 443
        );

    /*
    |--------------------------------------------------------------------------
    | SECURE COOKIE SETTINGS
    |--------------------------------------------------------------------------
    */

    session_set_cookie_params([

        'lifetime' => 0,

        'path' => '/',

        'domain' => '',

        'secure' => $isHttps,

        'httponly' => true,

        'samesite' => 'Strict'
    ]);

    /*
    |--------------------------------------------------------------------------
    | SESSION HARDENING
    |--------------------------------------------------------------------------
    */

    ini_set('session.use_only_cookies', '1');

    ini_set('session.use_strict_mode', '1');

    ini_set('session.cookie_httponly', '1');

    ini_set('session.cookie_secure', $isHttps ? '1' : '0');

    ini_set('session.cookie_samesite', 'Strict');

    ini_set('session.gc_maxlifetime', '3600');

    session_name('KVNSESSID');

    session_start();
}

/*
|--------------------------------------------------------------------------
| SESSION CONSTANTS
|--------------------------------------------------------------------------
*/

if (!defined('SESSION_TIMEOUT')) {

    define('SESSION_TIMEOUT', 3600);
}

if (!defined('ADMIN_SESSION_TIMEOUT')) {

    define('ADMIN_SESSION_TIMEOUT', 1800);
}

/*
|--------------------------------------------------------------------------
| GENERATE SESSION TOKEN
|--------------------------------------------------------------------------
*/

function generateSessionToken(): string
{
    return bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| GENERATE DEVICE HASH
|--------------------------------------------------------------------------
*/

function generateDeviceHash(): string
{
    return hash(

        'sha256',

        ($_SERVER['REMOTE_ADDR'] ?? '')

        .

        ($_SERVER['HTTP_USER_AGENT'] ?? '')

        .

        ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')
    );
}

/*
|--------------------------------------------------------------------------
| GENERATE SESSION FINGERPRINT
|--------------------------------------------------------------------------
*/

function generateSessionFingerprint(): string
{
    return hash(

        'sha256',

        ($_SERVER['REMOTE_ADDR'] ?? '')

        .

        ($_SERVER['HTTP_USER_AGENT'] ?? '')
    );
}

/*
|--------------------------------------------------------------------------
| STORE SESSION IN DATABASE
|--------------------------------------------------------------------------
*/

function storeSessionInDatabase(
    int $userId,
    string $sessionToken,
    string $role
): void {

    global $conn;

    try {

        $query = "

            INSERT INTO user_sessions (

                user_id,
                session_token,
                fingerprint_hash,
                device_hash,
                ip_address,
                user_agent,
                is_admin_session,
                last_activity,
                created_at

            )

            VALUES (

                :user_id,
                :session_token,
                :fingerprint_hash,
                :device_hash,
                :ip_address,
                :user_agent,
                :is_admin_session,
                NOW(),
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':user_id' =>
            $userId,

            ':session_token' =>
            $sessionToken,

            ':fingerprint_hash' =>
            generateSessionFingerprint(),

            ':device_hash' =>
            generateDeviceHash(),

            ':ip_address' =>
            $_SERVER['REMOTE_ADDR']
            ?? null,

            ':user_agent' =>
            $_SERVER['HTTP_USER_AGENT']
            ?? 'Unknown Device',

            ':is_admin_session' =>

                $role === 'admin'
                ? 1
                : 0
        ]);

    } catch (Exception $e) {

        error_log(
            'Session DB Error: '
            .
            $e->getMessage()
        );
    }
}

/*
|--------------------------------------------------------------------------
| INITIALIZE SESSION SECURITY
|--------------------------------------------------------------------------
*/

function initializeSessionSecurity(array $user): void
{
    /*
    |--------------------------------------------------------------------------
    | REGENERATE SESSION
    |--------------------------------------------------------------------------
    */

    session_regenerate_id(true);

    /*
    |--------------------------------------------------------------------------
    | SESSION TOKEN
    |--------------------------------------------------------------------------
    */

    $sessionToken =
    generateSessionToken();

    /*
    |--------------------------------------------------------------------------
    | SESSION DATA
    |--------------------------------------------------------------------------
    */

    $_SESSION['logged_in'] = true;

    $_SESSION['user_id'] =
    (int) $user['id'];

    $_SESSION['user_name'] =
    $user['full_name'];

    $_SESSION['role'] =
    $user['role'];

    $_SESSION['session_token'] =
    $sessionToken;

    $_SESSION['fingerprint'] =
    generateSessionFingerprint();

    $_SESSION['device_hash'] =
    generateDeviceHash();

    $_SESSION['last_activity'] =
    time();

    $_SESSION['login_time'] =
    time();

    $_SESSION['is_admin'] =

        $user['role']
        ===
        'admin';

    /*
    |--------------------------------------------------------------------------
    | STORE SESSION
    |--------------------------------------------------------------------------
    */

    storeSessionInDatabase(

        (int) $user['id'],

        $sessionToken,

        $user['role']
    );

    /*
    |--------------------------------------------------------------------------
    | SECURITY LOG
    |--------------------------------------------------------------------------
    */

    if (function_exists('logSecurityEvent')) {

        logSecurityEvent(

            $user['id'],

            'session_initialized',

            'info',

            'Secure session initialized'
        );
    }
}

/*
|--------------------------------------------------------------------------
| VALIDATE SESSION
|--------------------------------------------------------------------------
*/

function validateSession(): bool
{
    global $conn;

    /*
    |--------------------------------------------------------------------------
    | LOGIN CHECK
    |--------------------------------------------------------------------------
    */

    if (

        !isset($_SESSION['logged_in'])

        ||

        $_SESSION['logged_in'] !== true
    ) {

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | TOKEN CHECK
    |--------------------------------------------------------------------------
    */

    if (

        empty($_SESSION['session_token'])
    ) {

        destroySession();

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | FINGERPRINT CHECK
    |--------------------------------------------------------------------------
    */

    $currentFingerprint =
    generateSessionFingerprint();

    if (

        !isset($_SESSION['fingerprint'])

        ||

        $_SESSION['fingerprint']
        !==
        $currentFingerprint
    ) {

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                $_SESSION['user_id'] ?? null,

                'session_hijack_attempt',

                'critical',

                'Session fingerprint mismatch'
            );
        }

        destroySession();

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | DEVICE HASH CHECK
    |--------------------------------------------------------------------------
    */

    $currentDeviceHash =
    generateDeviceHash();

    if (

        !isset($_SESSION['device_hash'])

        ||

        $_SESSION['device_hash']
        !==
        $currentDeviceHash
    ) {

        destroySession();

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | SESSION TIMEOUT
    |--------------------------------------------------------------------------
    */

    $timeout =

        isAdmin()

        ?

        ADMIN_SESSION_TIMEOUT

        :

        SESSION_TIMEOUT;

    if (

        isset($_SESSION['last_activity'])

        &&

        (

            time()

            -

            $_SESSION['last_activity']
        )

        >

        $timeout
    ) {

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                $_SESSION['user_id'] ?? null,

                'session_timeout',

                'warning',

                'Session expired due to inactivity'
            );
        }

        destroySession();

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | DATABASE SESSION VALIDATION
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            SELECT id

            FROM user_sessions

            WHERE session_token = :token

            LIMIT 1
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':token' =>
            $_SESSION['session_token']
        ]);

        $session =
        $stmt->fetch();

        if (!$session) {

            destroySession();

            return false;
        }

    } catch (Exception $e) {

        error_log(
            $e->getMessage()
        );

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ACTIVITY
    |--------------------------------------------------------------------------
    */

    refreshSession();

    return true;
}

/*
|--------------------------------------------------------------------------
| REFRESH SESSION
|--------------------------------------------------------------------------
*/

function refreshSession(): void
{
    global $conn;

    $_SESSION['last_activity'] =
    time();

    try {

        $query = "

            UPDATE user_sessions

            SET

                last_activity = NOW()

            WHERE session_token = :token
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':token' =>
            $_SESSION['session_token']
        ]);

    } catch (Exception $e) {

        error_log(
            $e->getMessage()
        );
    }
}

/*
|--------------------------------------------------------------------------
| DESTROY OTHER SESSIONS
|--------------------------------------------------------------------------
*/

function destroyOtherSessions(
    int $userId,
    string $currentToken
): void {

    global $conn;

    try {

        $query = "

            DELETE FROM user_sessions

            WHERE user_id = :user_id

            AND session_token != :token
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':user_id' =>
            $userId,

            ':token' =>
            $currentToken
        ]);

    } catch (Exception $e) {

        error_log(
            $e->getMessage()
        );
    }
}

/*
|--------------------------------------------------------------------------
| DESTROY SESSION
|--------------------------------------------------------------------------
*/

function destroySession(): void
{
    global $conn;

    /*
    |--------------------------------------------------------------------------
    | REMOVE DATABASE SESSION
    |--------------------------------------------------------------------------
    */

    if (

        isset($_SESSION['session_token'])
    ) {

        try {

            $query = "

                DELETE FROM user_sessions

                WHERE session_token = :token
            ";

            $stmt =
            $conn->prepare($query);

            $stmt->execute([

                ':token' =>
                $_SESSION['session_token']
            ]);

        } catch (Exception $e) {

            error_log(
                $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAR SESSION ARRAY
    |--------------------------------------------------------------------------
    */

    $_SESSION = [];

    /*
    |--------------------------------------------------------------------------
    | DESTROY COOKIE
    |--------------------------------------------------------------------------
    */

    if (ini_get('session.use_cookies')) {

        $params =
        session_get_cookie_params();

        setcookie(

            session_name(),

            '',

            time() - 42000,

            $params['path'],

            $params['domain'],

            $params['secure'],

            $params['httponly']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY SESSION
    |--------------------------------------------------------------------------
    */

    session_destroy();
}

/*
|--------------------------------------------------------------------------
| LOGOUT USER
|--------------------------------------------------------------------------
*/

function logout(): void
{
    if (

        function_exists('logSecurityEvent')

        &&

        isset($_SESSION['user_id'])
    ) {

        logSecurityEvent(

            $_SESSION['user_id'],

            'logout',

            'info',

            'User logged out'
        );
    }

    destroySession();

    header(

        'Location: '
        .
        APP_URL
        .
        '/login.php'
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

function isLoggedIn(): bool
{
    return (

        isset($_SESSION['logged_in'])

        &&

        $_SESSION['logged_in'] === true
    );
}

/*
|--------------------------------------------------------------------------
| ADMIN CHECK
|--------------------------------------------------------------------------
*/

function isAdmin(): bool
{
    return (

        isset($_SESSION['role'])

        &&

        $_SESSION['role'] === 'admin'
    );
}

/*
|--------------------------------------------------------------------------
| CLIENT CHECK
|--------------------------------------------------------------------------
*/

function isClient(): bool
{
    return (

        isset($_SESSION['role'])

        &&

        $_SESSION['role'] === 'client'
    );
}

/*
|--------------------------------------------------------------------------
| REQUIRE LOGIN
|--------------------------------------------------------------------------
*/

function requireLogin(): void
{
    if (!validateSession()) {

        header(

            'Location: '
            .
            APP_URL
            .
            '/login.php'
        );

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| REQUIRE ADMIN
|--------------------------------------------------------------------------
*/

function requireAdmin(): void
{
    if (

        !validateSession()

        ||

        !isAdmin()
    ) {

        header(

            'Location: '
            .
            APP_URL
            .
            '/admin/login.php'
        );

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| REQUIRE CLIENT
|--------------------------------------------------------------------------
*/

function requireClient(): void
{
    if (

        !validateSession()

        ||

        !isClient()
    ) {

        header(

            'Location: '
            .
            APP_URL
            .
            '/login.php'
        );

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| CURRENT USER ID
|--------------------------------------------------------------------------
*/

function currentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/*
|--------------------------------------------------------------------------
| CURRENT USER ROLE
|--------------------------------------------------------------------------
*/

function currentUserRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

/*
|--------------------------------------------------------------------------
| SESSION REMAINING TIME
|--------------------------------------------------------------------------
*/

function sessionRemainingTime(): int
{
    $timeout =

        isAdmin()

        ?

        ADMIN_SESSION_TIMEOUT

        :

        SESSION_TIMEOUT;

    return max(

        0,

        $timeout

        -

        (

            time()

            -

            ($_SESSION['last_activity'] ?? 0)
        )
    );
}

/*
|--------------------------------------------------------------------------
| CLEANUP EXPIRED SESSIONS
|--------------------------------------------------------------------------
*/

function cleanupExpiredSessions(): void
{
    global $conn;

    try {

        $query = "

            DELETE FROM user_sessions

            WHERE last_activity

            <

            DATE_SUB(

                NOW(),

                INTERVAL 1 DAY
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute();

    } catch (Exception $e) {

        error_log(
            $e->getMessage()
        );
    }
}

?>