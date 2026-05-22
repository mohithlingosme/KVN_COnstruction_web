<?php

// =====================================
// SECURE SESSION CONFIGURATION
// =====================================

if(session_status() === PHP_SESSION_NONE){

    ini_set('session.use_only_cookies', 1);

    ini_set('session.use_strict_mode', 1);

    session_set_cookie_params([

        'lifetime' => 0,

        'path' => '/',

        'domain' => '',

        'secure' => false, // TRUE when HTTPS enabled

        'httponly' => true,

        'samesite' => 'Lax'
    ]);

    session_start();
}

// =====================================
// SESSION TIMEOUT
// =====================================

define('SESSION_TIMEOUT', 1800); // 30 MINUTES

// =====================================
// LOGIN USER
// =====================================

function loginUser($user)
{
    // REGENERATE SESSION

    session_regenerate_id(true);

    // STORE USER DATA

    $_SESSION['user_id'] =
    $user['id'];

    $_SESSION['user_name'] =
    $user['full_name'];

    $_SESSION['user_email'] =
    $user['email'];

    $_SESSION['user_role'] =
    $user['role'];

    $_SESSION['last_activity'] =
    time();

    $_SESSION['user_ip'] =
    $_SERVER['REMOTE_ADDR'];

    $_SESSION['user_agent'] =
    $_SERVER['HTTP_USER_AGENT'];
}

// =====================================
// LOGOUT USER
// =====================================

function logoutUser()
{
    $_SESSION = [];

    if(ini_get("session.use_cookies")){

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

// =====================================
// CHECK AUTHENTICATION
// =====================================