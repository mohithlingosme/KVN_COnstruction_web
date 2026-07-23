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
*/

/*
|--------------------------------------------------------------------------
| SEND OTP WITH SMS + EMAIL FALLBACK
|--------------------------------------------------------------------------
| Attempts to send OTP via SMS first. If SMS fails, falls back to email.
| Returns array with success status and delivery method used.
|--------------------------------------------------------------------------
*/

if (!function_exists('sendOtpWithFallback')) {
    function sendOtpWithFallback(
        string $phone,
        string $email,
        string $otp,
        string $name = 'User'
    ): array {
        // Attempt SMS first
        $smsSent = false;
        if (function_exists('sendOtpSms')) {
            $smsSent = sendOtpSms($phone, $otp);
        }

        if ($smsSent) {
            if (function_exists('logSecurityEvent')) {
                logSecurityEvent(null, 'otp_sent_sms', 'info', 'OTP sent via SMS to ' . $phone);
            }
            return [
                'success' => true,
                'method' => 'sms',
                'message' => 'OTP sent via SMS'
            ];
        }

        // Fallback to email
        if (function_exists('sendOtpEmail') && !empty($email)) {
            $emailSent = sendOtpEmail($email, $otp, $name);
            if ($emailSent) {
                if (function_exists('logSecurityEvent')) {
                    logSecurityEvent(null, 'otp_sent_email_fallback', 'info', 'OTP sent via Email fallback to ' . $email);
                }
                return [
                    'success' => true,
                    'method' => 'email',
                    'message' => 'OTP sent via Email (SMS unavailable)'
                ];
            }
        }

        // Both failed
        if (function_exists('logSecurityEvent')) {
            logSecurityEvent(null, 'otp_delivery_failed', 'critical', 'OTP delivery failed for phone: ' . $phone . ' email: ' . $email);
        }

        return [
            'success' => false,
            'method' => 'none',
            'message' => 'Unable to deliver OTP. Please try again later.'
        ];
    }
}

