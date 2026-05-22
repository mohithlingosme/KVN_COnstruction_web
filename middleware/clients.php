<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CLIENT ACCESS MIDDLEWARE
|--------------------------------------------------------------------------
| File:
| /middleware/client.php
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| LOAD AUTH MIDDLEWARE
|--------------------------------------------------------------------------
*/

require_once dirname(__FILE__) . '/auth.php';

/*
|--------------------------------------------------------------------------
| CLIENT ROLE VALIDATION
|--------------------------------------------------------------------------
*/

if (

    !isset($_SESSION['role'])

    ||

    $_SESSION['role'] !== 'client'
) {

    /*
    |--------------------------------------------------------------------------
    | SECURITY LOG
    |--------------------------------------------------------------------------
    */

    if (function_exists('logSecurityEvent')) {

        logSecurityEvent(

            $_SESSION['user_id'] ?? null,

            'unauthorized_client_access',

            'warning',

            'Non-client attempted to access client portal'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INVALID ROLE
    |--------------------------------------------------------------------------
    */

    $_SESSION['error'] =
    'Unauthorized access.';

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

    } else {

        destroySession();

        header(

            'Location: '
            .
            APP_URL
            .
            '/login.php'
        );
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| VERIFY CLIENT ACCOUNT
|--------------------------------------------------------------------------
*/

try {

    $query = "

        SELECT

            id,
            full_name,
            email,
            phone,
            role,
            status,
            phone_verified

        FROM users

        WHERE id = :id

        LIMIT 1
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':id' =>
        $_SESSION['user_id']
    ]);

    $client =
    $stmt->fetch();

    /*
    |--------------------------------------------------------------------------
    | CLIENT EXISTS
    |--------------------------------------------------------------------------
    */

    if (!$client) {

        logSecurityEvent(

            $_SESSION['user_id'],

            'missing_client_account',

            'critical',

            'Client account missing'
        );

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
    | CLIENT STATUS
    |--------------------------------------------------------------------------
    */

    if (

        $client['status']
        !==
        'active'
    ) {

        logSecurityEvent(

            $client['id'],

            'inactive_client_access',

            'warning',

            'Inactive client attempted access'
        );

        destroySession();

        $_SESSION['error'] =
        'Account inactive.';

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
    | PHONE VERIFICATION CHECK
    |--------------------------------------------------------------------------
    */

    if (

        isset($client['phone_verified'])

        &&

        !$client['phone_verified']
    ) {

        logSecurityEvent(

            $client['id'],

            'unverified_phone_access',

            'warning',

            'Client phone not verified'
        );

        $_SESSION['error'] =
        'Phone verification required.';

        header(

            'Location: '
            .
            APP_URL
            .
            '/verify-phone-otp.php'
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | CLIENT SESSION DATA
    |--------------------------------------------------------------------------
    */

    $_SESSION['client'] = [

        'id' =>
        $client['id'],

        'name' =>
        $client['full_name'],

        'email' =>
        $client['email'],

        'phone' =>
        $client['phone']
    ];

} catch (Exception $e) {

    error_log(

        'Client Middleware Error: '
        .
        $e->getMessage()
    );

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
| OPTIONAL CLIENT ROUTE LOGGING
|--------------------------------------------------------------------------
*/

if (function_exists('logSecurityEvent')) {

    logSecurityEvent(

        $_SESSION['user_id'],

        'client_portal_access',

        'info',

        current_url()
    );
}

/*
|--------------------------------------------------------------------------
| CLIENT AUTHORIZED
|--------------------------------------------------------------------------
*/
?>