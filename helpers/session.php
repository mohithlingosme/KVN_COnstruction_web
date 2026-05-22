<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION
|--------------------------------------------------------------------------
| SECURE SESSION MANAGEMENT SYSTEM
|--------------------------------------------------------------------------
| File:
| /helpers/session.php
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    /*
    |--------------------------------------------------------------------------
    | SESSION SECURITY
    |--------------------------------------------------------------------------
    */

    ini_set('session.use_only_cookies', 1);

    ini_set('session.use_strict_mode', 1);

    ini_set('session.cookie_httponly', 1);

    ini_set('session.cookie_secure', 0);

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

function generateSessionToken()
{
    return bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| GENERATE SESSION FINGERPRINT
|--------------------------------------------------------------------------
*/

function generateSessionFingerprint()
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
| INITIALIZE SECURE SESSION
|--------------------------------------------------------------------------
*/

function initializeSessionSecurity($user)
{
    global $conn;

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
    $user['id'];

    $_SESSION['user_name'] =
    $user['full_name'];

    $_SESSION['role'] =
    $user['role'];

    $_SESSION['session_token'] =
    $sessionToken;

    $_SESSION['fingerprint'] =
    generateSessionFingerprint();

    $_SESSION['last_activity'] =
    time();

    $_SESSION['login_time'] =
    time();

    /*
    |--------------------------------------------------------------------------
    | ADMIN SESSION
    |--------------------------------------------------------------------------
    */

    $_SESSION['is_admin'] =

        $user['role']
        ===
        'admin';

    /*
    |--------------------------------------------------------------------------
    | DEVICE INFO
    |--------------------------------------------------------------------------
    */

    $deviceInfo =

        $_SERVER['HTTP_USER_AGENT']
        ?? 'Unknown Device';

    /*
    |--------------------------------------------------------------------------
    | SAVE SESSION TO DATABASE
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            INSERT INTO user_sessions (

                user_id,
                session_token,
                fingerprint_hash,
                ip_address,
                user_agent,
                is_admin_session,
                last_activity,
                created_at

            ) VALUES (

                :user_id,
                :session_token,
                :fingerprint_hash,
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
            $user['id'],

            ':session_token' =>
            $sessionToken,

            ':fingerprint_hash' =>
            $_SESSION['fingerprint'],

            ':ip_address' =>
            $_SERVER['REMOTE_ADDR']
            ?? null,

            ':user_agent' =>
            $deviceInfo,

            ':is_admin_session' =>

                $user['role']
                ===
                'admin'
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

            'Secure session started'
        );
    }
}

/*
|--------------------------------------------------------------------------
| VALIDATE SESSION
|--------------------------------------------------------------------------
*/

function validateSession()
{
    global $conn;

    /*
    |--------------------------------------------------------------------------
    | BASIC CHECK
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

                'session_fingerprint_mismatch',

                'critical',

                'Possible session hijacking'
            );
        }

        destroySession();

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | TIMEOUT CHECK
    |--------------------------------------------------------------------------
    */

    $timeout =
    SESSION_TIMEOUT;

    if (

        isset($_SESSION['role'])

        &&

        $_SESSION['role'] === 'admin'
    ) {

        $timeout =
        ADMIN_SESSION_TIMEOUT;
    }

    if (

        isset($_SESSION['last_activity'])

        &&

        (time() - $_SESSION['last_activity'])
        > $timeout
    ) {

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                $_SESSION['user_id'] ?? null,

                'session_timeout',

                'warning',

                'Inactive session expired'
            );
        }

        destroySession();

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | DATABASE SESSION CHECK
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

function refreshSession()
{
    global $conn;

    $_SESSION['last_activity'] =
    time();

    /*
    |--------------------------------------------------------------------------
    | UPDATE DATABASE
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            UPDATE user_sessions

            SET last_activity = NOW()

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
| DESTROY SESSION
|--------------------------------------------------------------------------
*/

function destroySession()
{
    global $conn;

    /*
    |--------------------------------------------------------------------------
    | REMOVE DB SESSION
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
    | CLEAR SESSION
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

    session_destroy();
}

/*
|--------------------------------------------------------------------------
| LOGOUT USER
|--------------------------------------------------------------------------
*/

function logout()
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

            'User logout'
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

function isLoggedIn()
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

function isAdmin()
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

function isClient()
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

function requireLogin()
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

function requireAdmin()
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

function requireClient()
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
| CURRENT USER
|--------------------------------------------------------------------------
*/

function currentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/*
|--------------------------------------------------------------------------
| CURRENT USER ROLE
|--------------------------------------------------------------------------
*/

function currentUserRole()
{
    return $_SESSION['role'] ?? null;
}

/*
|--------------------------------------------------------------------------
| SESSION REMAINING TIME
|--------------------------------------------------------------------------
*/

function sessionRemainingTime()
{
    $timeout =
    isAdmin()

    ?

    ADMIN_SESSION_TIMEOUT

    :

    SESSION_TIMEOUT;

    return max(

        0,

        $timeout -

        (

            time()

            -

            ($_SESSION['last_activity'] ?? 0)
        )
    );
}

/*
|--------------------------------------------------------------------------
| CLEAN EXPIRED SESSIONS
|--------------------------------------------------------------------------
*/

function cleanupExpiredSessions()
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