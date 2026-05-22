<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| OTP SECURITY SYSTEM
|--------------------------------------------------------------------------
| File:
| /helpers/otp.php
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| OTP CONFIG
|--------------------------------------------------------------------------
*/

define('OTP_LENGTH', 6);

define('OTP_EXPIRY_MINUTES', 5);

define('OTP_MAX_ATTEMPTS', 5);

define('OTP_RESEND_LIMIT', 3);

define('OTP_BLOCK_MINUTES', 15);

/*
|--------------------------------------------------------------------------
| GENERATE OTP
|--------------------------------------------------------------------------
*/

function generateOtp($length = OTP_LENGTH)
{
    return str_pad(

        random_int(

            0,

            pow(10, $length) - 1
        ),

        $length,

        '0',

        STR_PAD_LEFT
    );
}

/*
|--------------------------------------------------------------------------
| HASH OTP
|--------------------------------------------------------------------------
*/

function hashOtp($otp)
{
    return password_hash(

        $otp,

        PASSWORD_BCRYPT
    );
}

/*
|--------------------------------------------------------------------------
| VERIFY OTP HASH
|--------------------------------------------------------------------------
*/

function verifyOtpHash(

    $otp,

    $hash
) {

    return password_verify(

        $otp,

        $hash
    );
}

/*
|--------------------------------------------------------------------------
| CREATE PHONE OTP
|--------------------------------------------------------------------------
*/

function createPhoneOtp(

    $phone,

    $otpType = 'login'
) {

    global $conn;

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT
    |--------------------------------------------------------------------------
    */

    if (

        function_exists('otpRateLimit')

        &&

        !otpRateLimit()
    ) {

        return [

            'success' => false,

            'message' =>
            'Too many OTP requests.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK BLOCK
    |--------------------------------------------------------------------------
    */

    if (isOtpBlocked($phone)) {

        return [

            'success' => false,

            'message' =>
            'OTP temporarily blocked.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE OTP
    |--------------------------------------------------------------------------
    */

    $otp =
    generateOtp();

    $otpHash =
    hashOtp($otp);

    $expiresAt = date(

        'Y-m-d H:i:s',

        strtotime(
            '+' .
            OTP_EXPIRY_MINUTES .
            ' minutes'
        )
    );

    /*
    |--------------------------------------------------------------------------
    | SAVE OTP
    |--------------------------------------------------------------------------
    */

    $query = "

        INSERT INTO otp_login_logs (

            phone,
            otp_code_hash,
            otp_type,
            status,
            expires_at,
            ip_address,
            user_agent,
            created_at

        ) VALUES (

            :phone,
            :otp_hash,
            :otp_type,
            'sent',
            :expires_at,
            :ip_address,
            :user_agent,
            NOW()
        )
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':phone' =>
        $phone,

        ':otp_hash' =>
        $otpHash,

        ':otp_type' =>
        $otpType,

        ':expires_at' =>
        $expiresAt,

        ':ip_address' =>
        $_SERVER['REMOTE_ADDR']
        ?? null,

        ':user_agent' =>
        $_SERVER['HTTP_USER_AGENT']
        ?? null
    ]);

    /*
    |--------------------------------------------------------------------------
    | SECURITY LOG
    |--------------------------------------------------------------------------
    */

    logSecurityEvent(

        null,

        'otp_created',

        'info',

        'OTP sent to phone: ' . $phone
    );

    return [

        'success' => true,

        'otp' => $otp,

        'expires_at' => $expiresAt
    ];
}

/*
|--------------------------------------------------------------------------
| VERIFY PHONE OTP
|--------------------------------------------------------------------------
*/

function verifyPhoneOtp(

    $phone,

    $otp,

    $otpType = 'login'
) {

    global $conn;

    /*
    |--------------------------------------------------------------------------
    | FETCH LATEST OTP
    |--------------------------------------------------------------------------
    */

    $query = "

        SELECT *

        FROM otp_login_logs

        WHERE phone = :phone

        AND otp_type = :otp_type

        ORDER BY id DESC

        LIMIT 1
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':phone' =>
        $phone,

        ':otp_type' =>
        $otpType
    ]);

    $record =
    $stmt->fetch();

    /*
    |--------------------------------------------------------------------------
    | RECORD EXISTS
    |--------------------------------------------------------------------------
    */

    if (!$record) {

        return [

            'success' => false,

            'message' =>
            'OTP not found.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | OTP BLOCKED
    |--------------------------------------------------------------------------
    */

    if (

        !empty($record['blocked_until'])

        &&

        strtotime($record['blocked_until']) > time()
    ) {

        return [

            'success' => false,

            'message' =>
            'OTP verification blocked.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | OTP EXPIRED
    |--------------------------------------------------------------------------
    */

    if (

        strtotime($record['expires_at'])
        < time()
    ) {

        updateOtpStatus(

            $record['id'],

            'expired'
        );

        return [

            'success' => false,

            'message' =>
            'OTP expired.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | OTP VERIFY
    |--------------------------------------------------------------------------
    */

    if (

        verifyOtpHash(

            $otp,

            $record['otp_code_hash']
        )
    ) {

        updateOtpStatus(

            $record['id'],

            'verified'
        );

        logSecurityEvent(

            null,

            'otp_verified',

            'info',

            'OTP verified for phone: '
            .
            $phone
        );

        return [

            'success' => true
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FAILED ATTEMPT
    |--------------------------------------------------------------------------
    */

    incrementOtpAttempts($record['id']);

    logSecurityEvent(

        null,

        'otp_failed',

        'warning',

        'Invalid OTP attempt'
    );

    return [

        'success' => false,

        'message' =>
        'Invalid OTP.'
    ];
}

/*
|--------------------------------------------------------------------------
| UPDATE OTP STATUS
|--------------------------------------------------------------------------
*/

function updateOtpStatus(

    $otpId,

    $status
) {

    global $conn;

    $query = "

        UPDATE otp_login_logs

        SET status = :status

        WHERE id = :id
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':status' =>
        $status,

        ':id' =>
        $otpId
    ]);
}

/*
|--------------------------------------------------------------------------
| INCREMENT OTP ATTEMPTS
|--------------------------------------------------------------------------
*/

function incrementOtpAttempts($otpId)
{
    global $conn;

    /*
    |--------------------------------------------------------------------------
    | UPDATE ATTEMPTS
    |--------------------------------------------------------------------------
    */

    $query = "

        UPDATE otp_login_logs

        SET attempts = attempts + 1

        WHERE id = :id
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':id' =>
        $otpId
    ]);

    /*
    |--------------------------------------------------------------------------
    | FETCH UPDATED RECORD
    |--------------------------------------------------------------------------
    */

    $query = "

        SELECT attempts

        FROM otp_login_logs

        WHERE id = :id
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':id' =>
        $otpId
    ]);

    $record =
    $stmt->fetch();

    /*
    |--------------------------------------------------------------------------
    | BLOCK OTP
    |--------------------------------------------------------------------------
    */

    if (

        $record

        &&

        $record['attempts']
        >=
        OTP_MAX_ATTEMPTS
    ) {

        $blockedUntil = date(

            'Y-m-d H:i:s',

            strtotime(
                '+' .
                OTP_BLOCK_MINUTES .
                ' minutes'
            )
        );

        $query = "

            UPDATE otp_login_logs

            SET blocked_until = :blocked_until

            WHERE id = :id
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':blocked_until' =>
            $blockedUntil,

            ':id' =>
            $otpId
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| OTP BLOCK CHECK
|--------------------------------------------------------------------------
*/

function isOtpBlocked($phone)
{
    global $conn;

    $query = "

        SELECT blocked_until

        FROM otp_login_logs

        WHERE phone = :phone

        ORDER BY id DESC

        LIMIT 1
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':phone' =>
        $phone
    ]);

    $record =
    $stmt->fetch();

    if (

        !$record

        ||

        empty($record['blocked_until'])
    ) {

        return false;
    }

    return

        strtotime($record['blocked_until'])
        > time();
}

/*
|--------------------------------------------------------------------------
| RESEND OTP
|--------------------------------------------------------------------------
*/

function resendOtp(

    $phone,

    $otpType = 'login'
) {

    global $conn;

    /*
    |--------------------------------------------------------------------------
    | FETCH LAST OTP
    |--------------------------------------------------------------------------
    */

    $query = "

        SELECT *

        FROM otp_login_logs

        WHERE phone = :phone

        ORDER BY id DESC

        LIMIT 1
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':phone' =>
        $phone
    ]);

    $record =
    $stmt->fetch();

    /*
    |--------------------------------------------------------------------------
    | RESEND LIMIT
    |--------------------------------------------------------------------------
    */

    if (

        $record

        &&

        $record['resend_count']
        >=
        OTP_RESEND_LIMIT
    ) {

        return [

            'success' => false,

            'message' =>
            'OTP resend limit reached.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | INCREMENT RESEND
    |--------------------------------------------------------------------------
    */

    if ($record) {

        $query = "

            UPDATE otp_login_logs

            SET resend_count = resend_count + 1

            WHERE id = :id
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':id' =>
            $record['id']
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE NEW OTP
    |--------------------------------------------------------------------------
    */

    return createPhoneOtp(

        $phone,

        $otpType
    );
}

/*
|--------------------------------------------------------------------------
| OTP SESSION
|--------------------------------------------------------------------------
*/

function createOtpSession($phone)
{
    $_SESSION['otp_phone'] =
    $phone;

    $_SESSION['otp_verified'] =
    false;

    $_SESSION['otp_created_at'] =
    time();
}

/*
|--------------------------------------------------------------------------
| OTP SESSION VALID
|--------------------------------------------------------------------------
*/

function isOtpSessionValid()
{
    return (

        isset($_SESSION['otp_phone'])

        &&

        isset($_SESSION['otp_created_at'])

        &&

        (

            time()
            -
            $_SESSION['otp_created_at']

        ) < 600
    );
}

/*
|--------------------------------------------------------------------------
| CLEAR OTP SESSION
|--------------------------------------------------------------------------
*/

function clearOtpSession()
{
    unset($_SESSION['otp_phone']);

    unset($_SESSION['otp_verified']);

    unset($_SESSION['otp_created_at']);
}

/*
|--------------------------------------------------------------------------
| CLEANUP EXPIRED OTPS
|--------------------------------------------------------------------------
*/

function cleanupExpiredOtps()
{
    global $conn;

    try {

        $query = "

            DELETE FROM otp_login_logs

            WHERE expires_at < NOW()
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

/*
|--------------------------------------------------------------------------
| AUTO CLEANUP
|--------------------------------------------------------------------------
*/

cleanupExpiredOtps();

?>