<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION
|--------------------------------------------------------------------------
| OTP SECURITY HELPER
|--------------------------------------------------------------------------
| File:
| /helpers/otp.php
|--------------------------------------------------------------------------
*/

if (!defined('OTP_EXPIRY_MINUTES')) {

    define('OTP_EXPIRY_MINUTES', 5);
}

if (!defined('OTP_RESEND_COOLDOWN')) {

    define('OTP_RESEND_COOLDOWN', 60);
}

if (!defined('OTP_MAX_ATTEMPTS')) {

    define('OTP_MAX_ATTEMPTS', 5);
}

if (!defined('OTP_MAX_RESENDS')) {

    define('OTP_MAX_RESENDS', 3);
}

/*
|--------------------------------------------------------------------------
| GENERATE OTP
|--------------------------------------------------------------------------
*/

function generateOtp(
    int $length = 6
): string {

    $otp = '';

    for ($i = 0; $i < $length; $i++) {

        $otp .= random_int(0, 9);
    }

    return $otp;
}

/*
|--------------------------------------------------------------------------
| GENERATE OTP EXPIRY
|--------------------------------------------------------------------------