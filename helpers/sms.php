<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| SMS GATEWAY SYSTEM
|--------------------------------------------------------------------------
| File:
| /helpers/sms.php
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| SMS CONFIG
|--------------------------------------------------------------------------
|
| PROVIDERS:
| - fast2sms
| - textlocal
| - twilio (future)
|--------------------------------------------------------------------------
*/

define('SMS_PROVIDER', 'fast2sms');

/*
|--------------------------------------------------------------------------
| FAST2SMS CONFIG
|--------------------------------------------------------------------------
*/

define('FAST2SMS_API_KEY', 'YOUR_FAST2SMS_API_KEY');

/*
|--------------------------------------------------------------------------
| TEXTLOCAL CONFIG
|--------------------------------------------------------------------------
*/

define('TEXTLOCAL_API_KEY', 'YOUR_TEXTLOCAL_API_KEY');

define('TEXTLOCAL_SENDER', 'KVNCON');

/*
|--------------------------------------------------------------------------
| SEND SMS
|--------------------------------------------------------------------------
*/

function sendSms(

    $phone,

    $message
) {

    switch (SMS_PROVIDER) {

        case 'fast2sms':

            return sendFast2Sms(

                $phone,

                $message
            );

        case 'textlocal':

            return sendTextLocalSms(

                $phone,

                $message
            );

        default:

            return [

                'success' => false,

                'message' =>
                'Invalid SMS provider.'
            ];
    }
}

/*
|--------------------------------------------------------------------------
| FAST2SMS
|--------------------------------------------------------------------------
*/

function sendFast2Sms(

    $phone,

    $message
) {

    $url =
    'https://www.fast2sms.com/dev/bulkV2';

    $data = [

        'route' => 'q',

        'message' => $message,

        'language' => 'english',

        'flash' => 0,

        'numbers' => $phone
    ];

    $headers = [

        'authorization: '
        .
        FAST2SMS_API_KEY,

        'Content-Type: application/x-www-form-urlencoded'
    ];

    $curl =
    curl_init();

    curl_setopt_array($curl, [

        CURLOPT_URL =>
        $url,

        CURLOPT_RETURNTRANSFER =>
        true,

        CURLOPT_ENCODING =>
        '',

        CURLOPT_MAXREDIRS =>
        10,

        CURLOPT_TIMEOUT =>
        30,

        CURLOPT_FOLLOWLOCATION =>
        true,

        CURLOPT_HTTP_VERSION =>
        CURL_HTTP_VERSION_1_1,

        CURLOPT_CUSTOMREQUEST =>
        'POST',

        CURLOPT_POSTFIELDS =>
        http_build_query($data),

        CURLOPT_HTTPHEADER =>
        $headers
    ]);

    $response =
    curl_exec($curl);

    $error =
    curl_error($curl);

    curl_close($curl);

    /*
    |--------------------------------------------------------------------------
    | CURL ERROR
    |--------------------------------------------------------------------------
    */

    if ($error) {

        logSmsFailure(

            $phone,

            $error
        );

        return [

            'success' => false,

            'message' => $error
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    $result =
    json_decode(

        $response,

        true
    );

    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    if (

        isset($result['return'])

        &&

        $result['return'] === true
    ) {

        logSmsSuccess($phone);

        return [

            'success' => true,

            'response' => $result
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FAILURE
    |--------------------------------------------------------------------------
    */

    logSmsFailure(

        $phone,

        $response
    );

    return [

        'success' => false,

        'response' => $result
    ];
}

/*
|--------------------------------------------------------------------------
| TEXTLOCAL
|--------------------------------------------------------------------------
*/

function sendTextLocalSms(

    $phone,

    $message
) {

    $data = [

        'apikey' =>
        TEXTLOCAL_API_KEY,

        'numbers' =>
        $phone,

        'message' =>
        $message,

        'sender' =>
        TEXTLOCAL_SENDER
    ];

    $curl =
    curl_init();

    curl_setopt_array($curl, [

        CURLOPT_URL =>
        'https://api.textlocal.in/send/',

        CURLOPT_RETURNTRANSFER =>
        true,

        CURLOPT_POST =>
        true,

        CURLOPT_POSTFIELDS =>
        $data
    ]);

    $response =
    curl_exec($curl);

    $error =
    curl_error($curl);

    curl_close($curl);

    if ($error) {

        logSmsFailure(

            $phone,

            $error
        );

        return [

            'success' => false,

            'message' => $error
        ];
    }

    $result =
    json_decode(

        $response,

        true
    );

    if (

        isset($result['status'])

        &&

        strtolower(
            $result['status']
        ) === 'success'
    ) {

        logSmsSuccess($phone);

        return [

            'success' => true,

            'response' => $result
        ];
    }

    logSmsFailure(

        $phone,

        $response
    );

    return [

        'success' => false,

        'response' => $result
    ];
}

/*
|--------------------------------------------------------------------------
| SEND OTP SMS
|--------------------------------------------------------------------------
*/

function sendOtpSms(

    $phone,

    $otp
) {

    $message =

        'Your '
        .
        APP_NAME
        .
        ' OTP is '
        .
        $otp
        .
        '. Valid for '
        .
        OTP_EXPIRY_MINUTES
        .
        ' minutes.';

    return sendSms(

        $phone,

        $message
    );
}

/*
|--------------------------------------------------------------------------
| SEND LOGIN ALERT SMS
|--------------------------------------------------------------------------
*/

function sendLoginAlertSms(

    $phone
) {

    $message =

        'A login was detected on your '
        .
        APP_NAME
        .
        ' account. If this was not you, contact support immediately.';

    return sendSms(

        $phone,

        $message
    );
}

/*
|--------------------------------------------------------------------------
| SEND ESTIMATOR SMS
|--------------------------------------------------------------------------
*/

function sendEstimatorSms(

    $phone,

    $estimate
) {

    $message =

        'Your estimated construction cost is ₹'
        .
        number_format($estimate)
        .
        '. Team '
        .
        APP_NAME
        .
        ' will contact you shortly.';

    return sendSms(

        $phone,

        $message
    );
}

/*
|--------------------------------------------------------------------------
| SEND CONTACT ACKNOWLEDGEMENT
|--------------------------------------------------------------------------
*/

function sendContactAcknowledgementSms(

    $phone
) {

    $message =

        'Thank you for contacting '
        .
        APP_NAME
        .
        '. Our team will contact you shortly.';

    return sendSms(

        $phone,

        $message
    );
}

/*
|--------------------------------------------------------------------------
| SMS SUCCESS LOG
|--------------------------------------------------------------------------
*/

function logSmsSuccess($phone)
{
    if (function_exists('logSecurityEvent')) {

        logSecurityEvent(

            $_SESSION['user_id'] ?? null,

            'sms_sent',

            'info',

            'SMS sent to: '
            .
            $phone
        );
    }
}

/*
|--------------------------------------------------------------------------
| SMS FAILURE LOG
|--------------------------------------------------------------------------
*/

function logSmsFailure(

    $phone,

    $reason
) {

    if (function_exists('logSecurityEvent')) {

        logSecurityEvent(

            $_SESSION['user_id'] ?? null,

            'sms_failed',

            'warning',

            'SMS failure for '
            .
            $phone
            .
            ' : '
            .
            $reason
        );
    }
}

/*
|--------------------------------------------------------------------------
| VALIDATE PHONE NUMBER
|--------------------------------------------------------------------------
*/

function validateIndianPhone($phone)
{
    return preg_match(

        '/^[6-9]\d{9}$/',

        $phone
    );
}

/*
|--------------------------------------------------------------------------
| SANITIZE PHONE
|--------------------------------------------------------------------------
*/

function sanitizePhoneNumber($phone)
{
    return preg_replace(

        '/[^0-9]/',

        '',

        $phone
    );
}

?>