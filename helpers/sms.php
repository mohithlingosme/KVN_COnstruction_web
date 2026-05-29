<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION
|--------------------------------------------------------------------------
| SMS SECURITY HELPER
|--------------------------------------------------------------------------
| File:
| /helpers/sms.php
|--------------------------------------------------------------------------
|
| FEATURES
| - OTP SMS
| - Delivery Logging
| - Security Alerts
| - Resend Cooldown
| - Provider Failover Ready
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| SMS CONFIGURATION
|--------------------------------------------------------------------------
*/

if (!defined('SMS_PROVIDER')) {

    define('SMS_PROVIDER', 'fast2sms');
}

if (!defined('SMS_ENABLED')) {

    define('SMS_ENABLED', filter_var(env_value('SMS_ENABLED', '0'), FILTER_VALIDATE_BOOL));
}

if (!defined('SMS_RESEND_COOLDOWN')) {

    define('SMS_RESEND_COOLDOWN', 60);
}

/*
|--------------------------------------------------------------------------
| SEND SMS
|--------------------------------------------------------------------------
*/

function sendSms(
    string $phone,
    string $message,
    string $type = 'general'
): bool {

    /*
    |--------------------------------------------------------------------------
    | SMS DISABLED
    |--------------------------------------------------------------------------
    */

    if (!SMS_ENABLED) {

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | SANITIZE PHONE
    |--------------------------------------------------------------------------
    */

    $phone =
    sanitizePhoneNumber($phone);

    /*
    |--------------------------------------------------------------------------
    | VALIDATE PHONE
    |--------------------------------------------------------------------------
    */

    if (!isValidPhoneNumber($phone)) {

        logSmsDelivery(

            $phone,

            $message,

            'failed',

            'Invalid phone number'
        );

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | PROVIDER SWITCH
    |--------------------------------------------------------------------------
    */

    switch (SMS_PROVIDER) {

        case 'fast2sms':

            return sendViaFast2Sms(

                $phone,

                $message,

                $type
            );

        default:

            logSmsDelivery(

                $phone,

                $message,

                'failed',

                'Unknown SMS provider'
            );

            return false;
    }
}

/*
|--------------------------------------------------------------------------
| FAST2SMS PROVIDER
|--------------------------------------------------------------------------
*/

function sendViaFast2Sms(
    string $phone,
    string $message,
    string $type = 'general'
): bool {

    try {

        /*
        |--------------------------------------------------------------------------
        | API URL
        |--------------------------------------------------------------------------
        */

        $url =
        'https://www.fast2sms.com/dev/bulkV2';

        /*
        |--------------------------------------------------------------------------
        | REQUEST DATA
        |--------------------------------------------------------------------------
        */

        $payload = [

            'route' => 'q',

            'message' => $message,

            'language' => 'english',

            'numbers' => $phone
        ];

        /*
        |--------------------------------------------------------------------------
        | CURL INIT
        |--------------------------------------------------------------------------
        */

        $curl =
        curl_init();

        curl_setopt_array($curl, [

            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_POST => true,

            CURLOPT_POSTFIELDS =>
            http_build_query($payload),

            CURLOPT_HTTPHEADER => [

                'authorization: '
                .
                FAST2SMS_API_KEY,

                'cache-control: no-cache',

                'content-type: application/x-www-form-urlencoded'
            ],

            CURLOPT_TIMEOUT => 30
        ]);

        /*
        |--------------------------------------------------------------------------
        | EXECUTE
        |--------------------------------------------------------------------------
        */

        $response =
        curl_exec($curl);

        $httpCode =
        curl_getinfo(

            $curl,

            CURLINFO_HTTP_CODE
        );

        $curlError =
        curl_error($curl);

        curl_close($curl);

        /*
        |--------------------------------------------------------------------------
        | CURL ERROR
        |--------------------------------------------------------------------------
        */

        if ($curlError) {

            logSmsDelivery(

                $phone,

                $message,

                'failed',

                $curlError
            );

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        $responseData =
        json_decode($response, true);

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        if (

            $httpCode === 200

            &&

            isset($responseData['return'])

            &&

            $responseData['return'] === true
        ) {

            logSmsDelivery(

                $phone,

                $message,

                'success'
            );

            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | FAILURE
        |--------------------------------------------------------------------------
        */

        logSmsDelivery(

            $phone,

            $message,

            'failed',

            $response
        );

        return false;

    } catch (Exception $e) {

        logSmsDelivery(

            $phone,

            $message,

            'failed',

            $e->getMessage()
        );

        error_log(
            $e->getMessage()
        );

        return false;
    }
}

/*
|--------------------------------------------------------------------------
| SEND OTP SMS
|--------------------------------------------------------------------------
*/

function sendOtpSms(
    string $phone,
    string $otp
): bool {

    $message =

        "Your "

        .

        APP_NAME

        .

        " OTP is "

        .

        $otp

        .

        ". Valid for "

        .

        OTP_EXPIRY_MINUTES

        .

        " minutes. Do not share it.";

    return sendSms(

        $phone,

        $message,

        'otp'
    );
}

/*
|--------------------------------------------------------------------------
| SEND PASSWORD RESET SMS
|--------------------------------------------------------------------------
*/

function sendPasswordResetSms(
    string $phone,
    string $otp
): bool {

    $message =

        "Password reset OTP: "

        .

        $otp

        .

        ". Expires in "

        .

        OTP_EXPIRY_MINUTES

        .

        " minutes.";

    return sendSms(

        $phone,

        $message,

        'password_reset'
    );
}

/*
|--------------------------------------------------------------------------
| SEND SECURITY ALERT SMS
|--------------------------------------------------------------------------
*/

function sendSecurityAlertSms(
    string $phone,
    string $event
): bool {

    $message =

        "Security Alert: "

        .

        $event

        .

        ". If this was not you, contact support immediately.";

    return sendSms(

        $phone,

        $message,

        'security'
    );
}

/*
|--------------------------------------------------------------------------
| ADMIN LOGIN ALERT SMS
|--------------------------------------------------------------------------
*/

function sendAdminLoginSms(
    string $phone,
    string $ip
): bool {

    $message =

        "Admin login detected from IP "

        .

        $ip

        .

        " at "

        .

        date('H:i:s')

        .

        ".";

    return sendSms(

        $phone,

        $message,

        'admin_login'
    );
}

/*
|--------------------------------------------------------------------------
| RESEND COOLDOWN CHECK
|--------------------------------------------------------------------------
*/

function canResendSms(
    string $phone
): bool {

    global $conn;

    try {

        $query = "

            SELECT created_at

            FROM sms_logs

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

        $last =
        $stmt->fetch();

        /*
        |--------------------------------------------------------------------------
        | NO PREVIOUS SMS
        |--------------------------------------------------------------------------
        */

        if (!$last) {

            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK TIME
        |--------------------------------------------------------------------------
        */

        $lastTime =
        strtotime($last['created_at']);

        return (

            time()

            -

            $lastTime
        )

        >=

        SMS_RESEND_COOLDOWN;

    } catch (Exception $e) {

        error_log(
            $e->getMessage()
        );

        return false;
    }
}

/*
|--------------------------------------------------------------------------
| SMS DELIVERY LOGGING
|--------------------------------------------------------------------------
*/

function logSmsDelivery(
    string $phone,
    string $message,
    string $status,
    string $response = ''
): void {

    global $conn;

    try {

        /*
        |--------------------------------------------------------------------------
        | DATABASE LOG
        |--------------------------------------------------------------------------
        */

        if (isset($conn)) {

            $query = "

                INSERT INTO sms_logs (

                    phone,
                    message,
                    status,
                    provider_response,
                    created_at

                )

                VALUES (

                    :phone,
                    :message,
                    :status,
                    :response,
                    NOW()
                )
            ";

            $stmt =
            $conn->prepare($query);

            $stmt->execute([

                ':phone' =>
                $phone,

                ':message' =>
                $message,

                ':status' =>
                $status,

                ':response' =>
                $response
            ]);
        }

    } catch (Exception $e) {

        error_log(
            $e->getMessage()
        );
    }
}

/*
|--------------------------------------------------------------------------
| PHONE VALIDATION
|--------------------------------------------------------------------------
*/

function isValidPhoneNumber(
    string $phone
): bool {

    return preg_match(

        '/^[6-9][0-9]{9}$/',

        $phone
    ) === 1;
}

function validateIndianPhone(string $phone): bool
{
    return isValidPhoneNumber(sanitizePhoneNumber($phone));
}

/*
|--------------------------------------------------------------------------
| SANITIZE PHONE
|--------------------------------------------------------------------------
*/

function sanitizePhoneNumber(
    string $phone
): string {

    return preg_replace(

        '/[^0-9]/',

        '',

        $phone
    );
}

/*
|--------------------------------------------------------------------------
| MASK PHONE NUMBER
|--------------------------------------------------------------------------
*/

function maskPhoneNumber(
    string $phone
): string {

    $phone =
    sanitizePhoneNumber($phone);

    return substr($phone, 0, 2)

        .

        '******'

        .

        substr($phone, -2);
}

/*
|--------------------------------------------------------------------------
| SMS PROVIDER HEALTH CHECK
|--------------------------------------------------------------------------
*/

function checkSmsProvider(): bool
{
    return SMS_ENABLED;
}

?>
