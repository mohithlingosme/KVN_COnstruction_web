<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CSRF SECURITY SYSTEM
|--------------------------------------------------------------------------
| File:
| /helpers/csrf.php
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| CSRF CONFIG
|--------------------------------------------------------------------------
*/

if (!defined('CSRF_TOKEN_EXPIRY')) {

    define('CSRF_TOKEN_EXPIRY', 1800);
}

/*
|--------------------------------------------------------------------------
| GENERATE CSRF TOKEN
|--------------------------------------------------------------------------
*/

function generateCsrfToken()
{
    /*
    |--------------------------------------------------------------------------
    | TOKEN EXISTS & VALID
    |--------------------------------------------------------------------------
    */

    if (

        isset($_SESSION['csrf_token'])

        &&

        isset($_SESSION['csrf_token_time'])

        &&

        (

            time() - $_SESSION['csrf_token_time']

        ) < CSRF_TOKEN_EXPIRY
    ) {

        return $_SESSION['csrf_token'];
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE NEW TOKEN
    |--------------------------------------------------------------------------
    */

    $_SESSION['csrf_token'] =

        bin2hex(random_bytes(32));

    $_SESSION['csrf_token_time'] =
    time();

    /*
    |--------------------------------------------------------------------------
    | OPTIONAL SESSION BINDING
    |--------------------------------------------------------------------------
    */

    $_SESSION['csrf_fingerprint'] =

        hash(

            'sha256',

            ($_SERVER['REMOTE_ADDR'] ?? '')

            .

            ($_SERVER['HTTP_USER_AGENT'] ?? '')
        );

    return $_SESSION['csrf_token'];
}

/*
|--------------------------------------------------------------------------
| GET TOKEN
|--------------------------------------------------------------------------
*/

function csrfToken()
{
    return generateCsrfToken();
}

/*
|--------------------------------------------------------------------------
| CSRF INPUT FIELD
|--------------------------------------------------------------------------
*/

function csrfField()
{
    return '

        <input
            type="hidden"
            name="csrf_token"
            value="'

            .

            escape(csrfToken())

            .

            '">
    ';
}

/*
|--------------------------------------------------------------------------
| VERIFY TOKEN
|--------------------------------------------------------------------------
*/

function verifyCsrfToken($token = null)
{
    /*
    |--------------------------------------------------------------------------
    | TOKEN EXISTS
    |--------------------------------------------------------------------------
    */

    if (

        empty($_SESSION['csrf_token'])

        ||

        empty($_SESSION['csrf_token_time'])
    ) {

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | TOKEN PROVIDED
    |--------------------------------------------------------------------------
    */

    if (empty($token)) {

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | TOKEN EXPIRY
    |--------------------------------------------------------------------------
    */

    if (

        (

            time() - $_SESSION['csrf_token_time']

        ) > CSRF_TOKEN_EXPIRY
    ) {

        destroyCsrfToken();

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | SESSION FINGERPRINT CHECK
    |--------------------------------------------------------------------------
    */

    $currentFingerprint =

        hash(

            'sha256',

            ($_SERVER['REMOTE_ADDR'] ?? '')

            .

            ($_SERVER['HTTP_USER_AGENT'] ?? '')
        );

    if (

        isset($_SESSION['csrf_fingerprint'])

        &&

        $_SESSION['csrf_fingerprint']
        !==
        $currentFingerprint
    ) {

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                $_SESSION['user_id'] ?? null,

                'csrf_fingerprint_mismatch',

                'critical',

                'Possible session hijack attempt'
            );
        }

        destroyCsrfToken();

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | TOKEN VERIFY
    |--------------------------------------------------------------------------
    */

    return hash_equals(

        $_SESSION['csrf_token'],

        $token
    );
}

/*
|--------------------------------------------------------------------------
| VALIDATE CSRF REQUEST
|--------------------------------------------------------------------------
*/

function validateCsrf()
{
    /*
    |--------------------------------------------------------------------------
    | ONLY VALIDATE STATE CHANGING METHODS
    |--------------------------------------------------------------------------
    */

    $allowedMethods = [

        'POST',
        'PUT',
        'PATCH',
        'DELETE'
    ];

    if (

        !in_array(

            $_SERVER['REQUEST_METHOD'],

            $allowedMethods
        )
    ) {

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | FETCH TOKEN
    |--------------------------------------------------------------------------
    */

    $token =

        $_POST['csrf_token']

        ??

        $_SERVER['HTTP_X_CSRF_TOKEN']

        ??

        '';

    /*
    |--------------------------------------------------------------------------
    | VERIFY TOKEN
    |--------------------------------------------------------------------------
    */

    if (!verifyCsrfToken($token)) {

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                $_SESSION['user_id'] ?? null,

                'csrf_validation_failed',

                'critical',

                'Invalid CSRF token'
            );
        }

        http_response_code(403);

        exit('Invalid CSRF token.');
    }

    /*
    |--------------------------------------------------------------------------
    | ROTATE TOKEN AFTER SUCCESS
    |--------------------------------------------------------------------------
    */

    regenerateCsrfToken();

    return true;
}

/*
|--------------------------------------------------------------------------
| REGENERATE TOKEN
|--------------------------------------------------------------------------
*/

function regenerateCsrfToken()
{
    $_SESSION['csrf_token'] =

        bin2hex(random_bytes(32));

    $_SESSION['csrf_token_time'] =
    time();

    return $_SESSION['csrf_token'];
}

/*
|--------------------------------------------------------------------------
| DESTROY TOKEN
|--------------------------------------------------------------------------
*/

function destroyCsrfToken()
{
    unset($_SESSION['csrf_token']);

    unset($_SESSION['csrf_token_time']);

    unset($_SESSION['csrf_fingerprint']);
}

/*
|--------------------------------------------------------------------------
| META TOKEN FOR AJAX
|--------------------------------------------------------------------------
*/

function csrfMetaTag()
{
    return '

        <meta
            name="csrf-token"
            content="'

            .

            escape(csrfToken())

            .

            '">
    ';
}

/*
|--------------------------------------------------------------------------
| GET AJAX TOKEN
|--------------------------------------------------------------------------
*/

function getAjaxCsrfToken()
{
    return csrfToken();
}

/*
|--------------------------------------------------------------------------
| VERIFY AJAX TOKEN
|--------------------------------------------------------------------------
*/

function validateAjaxCsrf()
{
    $token =

        $_SERVER['HTTP_X_CSRF_TOKEN']

        ??

        '';

    return verifyCsrfToken($token);
}

/*
|--------------------------------------------------------------------------
| CSRF JSON RESPONSE
|--------------------------------------------------------------------------
*/

function csrfJsonError()
{
    http_response_code(403);

    header('Content-Type: application/json');

    echo json_encode([

        'success' => false,

        'message' => 'Invalid CSRF token.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| AUTO CLEANUP EXPIRED TOKEN
|--------------------------------------------------------------------------
*/

function cleanupExpiredCsrf()
{
    if (

        isset($_SESSION['csrf_token_time'])

        &&

        (

            time() - $_SESSION['csrf_token_time']

        ) > CSRF_TOKEN_EXPIRY
    ) {

        destroyCsrfToken();
    }
}

/*
|--------------------------------------------------------------------------
| INITIALIZE CLEANUP
|--------------------------------------------------------------------------
*/

cleanupExpiredCsrf();

?>