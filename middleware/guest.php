<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| GUEST ACCESS MIDDLEWARE
|--------------------------------------------------------------------------
| File:
| /middleware/guest.php
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| LOAD APPLICATION
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__) . '/config/app.php';

/*
|--------------------------------------------------------------------------
| VALID SESSION CHECK
|--------------------------------------------------------------------------
*/

if (

    isset($_SESSION['logged_in'])

    &&

    $_SESSION['logged_in'] === true
) {

    /*
    |--------------------------------------------------------------------------
    | VALIDATE ACTIVE SESSION
    |--------------------------------------------------------------------------
    */

    if (validateSession()) {

        /*
        |--------------------------------------------------------------------------
        | SECURITY LOG
        |--------------------------------------------------------------------------
        */

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                $_SESSION['user_id'] ?? null,

                'guest_access_blocked',

                'info',

                'Authenticated user attempted guest route access'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | REDIRECT BASED ON ROLE
        |--------------------------------------------------------------------------
        */

        if (

            isset($_SESSION['role'])

            &&

            $_SESSION['role'] === 'admin'
        ) {

            header(

                'Location: '
                .
                APP_URL
                .
                '/admin/dashboard.php'
            );

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | CLIENT REDIRECT
        |--------------------------------------------------------------------------
        */

        if (

            isset($_SESSION['role'])

            &&

            $_SESSION['role'] === 'client'
        ) {

            header(

                'Location: '
                .
                APP_URL
                .
                '/client/dashboard.php'
            );

            exit;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INVALID SESSION
    |--------------------------------------------------------------------------
    */

    destroySession();
}

/*
|--------------------------------------------------------------------------
| OTP SESSION PROTECTION
|--------------------------------------------------------------------------
|
| Prevent direct access to OTP verification
|--------------------------------------------------------------------------
*/

$currentPage =
basename($_SERVER['PHP_SELF']);

if (

    $currentPage === 'verify-phone-otp.php'
) {

    if (

        !isset($_SESSION['otp_phone'])
    ) {

        /*
        |--------------------------------------------------------------------------
        | SECURITY LOG
        |--------------------------------------------------------------------------
        */

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                null,

                'invalid_otp_route_access',

                'warning',

                'OTP page accessed without session'
            );
        }

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
    | OTP SESSION EXPIRY
    |--------------------------------------------------------------------------
    */

    if (

        isset($_SESSION['otp_created_at'])

        &&

        (

            time()

            -

            $_SESSION['otp_created_at']

        ) > 600
    ) {

        clearOtpSession();

        $_SESSION['error'] =
        'OTP session expired.';

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
| FORGOT PASSWORD ACCESS
|--------------------------------------------------------------------------
*/

if (

    $currentPage === 'reset-password.php'
) {

    if (

        !isset($_SESSION['password_reset_verified'])
    ) {

        logSecurityEvent(

            null,

            'invalid_password_reset_access',

            'warning',

            'Unauthorized password reset access'
        );

        header(

            'Location: '
            .
            APP_URL
            .
            '/forgot-password.php'
        );

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| OPTIONAL RATE LIMIT FOR AUTH PAGES
|--------------------------------------------------------------------------
*/

if (

    in_array(

        $currentPage,

        [

            'login.php',

            'register.php',

            'forgot-password.php',

            'phone-login.php'
        ]
    )
) {

    if (

        function_exists('loginRateLimit')

        &&

        !loginRateLimit()
    ) {

        /*
        |--------------------------------------------------------------------------
        | LOG RATE LIMIT
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(

            null,

            'auth_rate_limit_exceeded',

            'warning',

            'Too many auth attempts'
        );

        http_response_code(429);

        exit(

            'Too many attempts. Please try again later.'
        );
    }
}

/*
|--------------------------------------------------------------------------
| GUEST ACCESS GRANTED
|--------------------------------------------------------------------------
*/
?>