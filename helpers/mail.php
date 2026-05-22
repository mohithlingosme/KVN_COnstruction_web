<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ADVANCED MAIL SYSTEM
|--------------------------------------------------------------------------
| File:
| /helpers/mail.php
|--------------------------------------------------------------------------
*/

use PHPMailer\PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\Exception;

require_once ROOT_PATH . '/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| SMTP CONFIG
|--------------------------------------------------------------------------
*/

define('MAIL_HOST', 'smtp.gmail.com');

define('MAIL_PORT', 587);

define('MAIL_USERNAME', 'your-email@gmail.com');

define('MAIL_PASSWORD', 'your-app-password');

define('MAIL_ENCRYPTION', 'tls');

define('MAIL_FROM_EMAIL', 'your-email@gmail.com');

define('MAIL_FROM_NAME', APP_NAME);

/*
|--------------------------------------------------------------------------
| BASE MAIL FUNCTION
|--------------------------------------------------------------------------
*/

function sendMail(

    $to,

    $subject,

    $body,

    $recipientName = ''
) {

    $mail = new PHPMailer(true);

    try {

        /*
        |--------------------------------------------------------------------------
        | SERVER SETTINGS
        |--------------------------------------------------------------------------
        */

        $mail->isSMTP();

        $mail->Host =
        MAIL_HOST;

        $mail->SMTPAuth = true;

        $mail->Username =
        MAIL_USERNAME;

        $mail->Password =
        MAIL_PASSWORD;

        $mail->SMTPSecure =
        MAIL_ENCRYPTION;

        $mail->Port =
        MAIL_PORT;

        /*
        |--------------------------------------------------------------------------
        | FROM
        |--------------------------------------------------------------------------
        */

        $mail->setFrom(

            MAIL_FROM_EMAIL,

            MAIL_FROM_NAME
        );

        /*
        |--------------------------------------------------------------------------
        | TO
        |--------------------------------------------------------------------------
        */

        $mail->addAddress(

            $to,

            $recipientName
        );

        /*
        |--------------------------------------------------------------------------
        | EMAIL CONTENT
        |--------------------------------------------------------------------------
        */

        $mail->isHTML(true);

        $mail->Subject = $subject;

        $mail->Body = buildMailTemplate($body);

        /*
        |--------------------------------------------------------------------------
        | SEND
        |--------------------------------------------------------------------------
        */

        $mail->send();

        /*
        |--------------------------------------------------------------------------
        | SECURITY LOG
        |--------------------------------------------------------------------------
        */

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                $_SESSION['user_id'] ?? null,

                'mail_sent',

                'info',

                'Mail sent to: ' . $to
            );
        }

        return true;

    } catch (Exception $e) {

        error_log(

            'Mail Error: '
            .
            $mail->ErrorInfo
        );

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                $_SESSION['user_id'] ?? null,

                'mail_failed',

                'warning',

                $mail->ErrorInfo
            );
        }

        return false;
    }
}

/*
|--------------------------------------------------------------------------
| GLOBAL MAIL TEMPLATE
|--------------------------------------------------------------------------
*/

function buildMailTemplate($content)
{
    return '

    <div style="
        background:#f5f7fa;
        padding:40px;
        font-family:Arial,sans-serif;
    ">

        <div style="
            max-width:650px;
            margin:auto;
            background:#ffffff;
            border-radius:12px;
            overflow:hidden;
            box-shadow:0 5px 20px rgba(0,0,0,0.08);
        ">

            <div style="
                background:#111827;
                padding:25px;
                text-align:center;
            ">

                <h1 style="
                    color:#fff;
                    margin:0;
                    font-size:28px;
                ">

                    ' . APP_NAME . '

                </h1>

            </div>

            <div style="padding:40px;">

                ' . $content . '

            </div>

            <div style="
                background:#f3f4f6;
                padding:20px;
                text-align:center;
                font-size:13px;
                color:#6b7280;
            ">

                © ' . date('Y') . ' '
                . APP_NAME . '

            </div>

        </div>

    </div>
    ';
}

/*
|--------------------------------------------------------------------------
| SEND OTP EMAIL
|--------------------------------------------------------------------------
*/

function sendOtpEmail(

    $email,

    $otp,

    $name = 'User'
) {

    $subject =
    APP_NAME .
    ' OTP Verification';

    $body = '

        <h2>Hello '
        . escape($name) .
        ',</h2>

        <p>

            Your One Time Password (OTP)
            is:

        </p>

        <div style="
            font-size:42px;
            font-weight:bold;
            letter-spacing:8px;
            color:#f5b400;
            margin:30px 0;
            text-align:center;
        ">

            ' . escape($otp) . '

        </div>

        <p>

            This OTP expires in
            <strong>5 minutes</strong>.

        </p>

        <p>

            If you did not request this,
            please ignore this email.

        </p>
    ';

    return sendMail(

        $email,

        $subject,

        $body,

        $name
    );
}

/*
|--------------------------------------------------------------------------
| PASSWORD RESET EMAIL
|--------------------------------------------------------------------------
*/

function sendPasswordResetMail(

    $email,

    $otp,

    $name = 'User'
) {

    $subject =
    APP_NAME .
    ' Password Reset';

    $body = '

        <h2>Password Reset Request</h2>

        <p>

            Hello '
            . escape($name) .
            ',

        </p>

        <p>

            Use the OTP below to
            reset your password:

        </p>

        <div style="
            font-size:40px;
            font-weight:bold;
            text-align:center;
            margin:30px 0;
            color:#ef4444;
        ">

            ' . escape($otp) . '

        </div>

        <p>

            OTP validity:
            <strong>10 minutes</strong>

        </p>
    ';

    return sendMail(

        $email,

        $subject,

        $body,

        $name
    );
}

/*
|--------------------------------------------------------------------------
| CONTACT FORM ALERT
|--------------------------------------------------------------------------
*/

function sendContactNotification($data = [])
{
    $subject =
    'New Contact Inquiry';

    $body = '

        <h2>New Contact Inquiry</h2>

        <table
            width="100%"
            cellpadding="10"
        >

            <tr>
                <td><strong>Name</strong></td>
                <td>' . escape($data['name']) . '</td>
            </tr>

            <tr>
                <td><strong>Email</strong></td>
                <td>' . escape($data['email']) . '</td>
            </tr>

            <tr>
                <td><strong>Phone</strong></td>
                <td>' . escape($data['phone']) . '</td>
            </tr>

            <tr>
                <td><strong>Message</strong></td>
                <td>' . nl2br(
                    escape($data['message'])
                ) . '</td>
            </tr>

        </table>
    ';

    return sendMail(

        MAIL_FROM_EMAIL,

        $subject,

        $body
    );
}

/*
|--------------------------------------------------------------------------
| ESTIMATOR LEAD ALERT
|--------------------------------------------------------------------------
*/

function sendEstimatorLeadMail($data = [])
{
    $subject =
    'New Construction Estimate Lead';

    $body = '

        <h2>New Estimator Lead</h2>

        <table
            width="100%"
            cellpadding="10"
        >

            <tr>
                <td><strong>Name</strong></td>
                <td>' . escape($data['name']) . '</td>
            </tr>

            <tr>
                <td><strong>Phone</strong></td>
                <td>' . escape($data['phone']) . '</td>
            </tr>

            <tr>
                <td><strong>Location</strong></td>
                <td>' . escape($data['location']) . '</td>
            </tr>

            <tr>
                <td><strong>Area</strong></td>
                <td>' . escape($data['area']) . ' sqft</td>
            </tr>

            <tr>
                <td><strong>Package</strong></td>
                <td>' . escape($data['package']) . '</td>
            </tr>

            <tr>
                <td><strong>Estimate</strong></td>
                <td>
                    ₹' . number_format(
                        $data['estimate']
                    ) . '
                </td>
            </tr>

        </table>
    ';

    return sendMail(

        MAIL_FROM_EMAIL,

        $subject,

        $body
    );
}

/*
|--------------------------------------------------------------------------
| ADMIN LOGIN ALERT
|--------------------------------------------------------------------------
*/

function sendAdminLoginAlert(

    $adminEmail,

    $adminName
) {

    $subject =
    'Admin Login Alert';

    $body = '

        <h2>Admin Login Detected</h2>

        <p>

            Hello '
            . escape($adminName) .
            ',

        </p>

        <p>

            A new admin login
            was detected.

        </p>

        <table
            width="100%"
            cellpadding="10"
        >

            <tr>
                <td><strong>IP Address</strong></td>
                <td>'
                . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown')
                . '</td>
            </tr>

            <tr>
                <td><strong>Time</strong></td>
                <td>'
                . date('d M Y h:i A')
                . '</td>
            </tr>

        </table>

        <p>

            If this wasn't you,
            reset your password immediately.

        </p>
    ';

    return sendMail(

        $adminEmail,

        $subject,

        $body,

        $adminName
    );
}

/*
|--------------------------------------------------------------------------
| SECURITY ALERT MAIL
|--------------------------------------------------------------------------
*/

function sendSecurityAlert(

    $subject,

    $message
) {

    return sendMail(

        MAIL_FROM_EMAIL,

        '[SECURITY ALERT] ' . $subject,

        '<p>' . escape($message) . '</p>'
    );
}

?>