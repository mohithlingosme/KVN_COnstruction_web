<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION
|--------------------------------------------------------------------------
| SECURE MAIL HELPER
|--------------------------------------------------------------------------
| File:
| /helpers/mail.php
|--------------------------------------------------------------------------
|
| FEATURES
| - Secure SMTP Mail
| - OTP Email
| - Password Reset Email
| - Admin Login Alert
| - Security Alerts
| - Contact Notifications
| - HTML Email Templates
| - Delivery Logging
| - Email Rate Limiting
|--------------------------------------------------------------------------
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/*
|--------------------------------------------------------------------------
| LOAD PHPMailer
|--------------------------------------------------------------------------
*/

require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/Exception.php';

require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';

require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/SMTP.php';

/*
|--------------------------------------------------------------------------
| CREATE MAILER INSTANCE
|--------------------------------------------------------------------------
*/

function createMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    /*
    |--------------------------------------------------------------------------
    | SMTP CONFIGURATION
    |--------------------------------------------------------------------------
    */

    $mail->isSMTP();

    $mail->Host =
    SMTP_HOST;

    $mail->SMTPAuth =
    true;

    $mail->Username =
    SMTP_USERNAME;

    $mail->Password =
    SMTP_PASSWORD;

    $mail->SMTPSecure =
    SMTP_ENCRYPTION;

    $mail->Port =
    SMTP_PORT;

    /*
    |--------------------------------------------------------------------------
    | SECURITY SETTINGS
    |--------------------------------------------------------------------------
    */

    $mail->SMTPAutoTLS =
    true;

    $mail->SMTPDebug =
    0;

    $mail->CharSet =
    'UTF-8';

    $mail->Timeout =
    15;

    /*
    |--------------------------------------------------------------------------
    | MAIL HEADERS
    |--------------------------------------------------------------------------
    */

    $mail->XMailer =
    APP_NAME;

    $mail->Priority =
    1;

    /*
    |--------------------------------------------------------------------------
    | FROM ADDRESS
    |--------------------------------------------------------------------------
    */

    $mail->setFrom(

        SMTP_FROM_EMAIL,

        SMTP_FROM_NAME
    );

    $mail->addReplyTo(

        SMTP_FROM_EMAIL,

        SMTP_FROM_NAME
    );

    return $mail;
}

/*
|--------------------------------------------------------------------------
| SEND EMAIL
|--------------------------------------------------------------------------
*/

function sendEmail(
    string $to,
    string $subject,
    string $htmlBody,
    string $plainText = ''
): bool {

    /*
    |--------------------------------------------------------------------------
    | VALIDATE EMAIL
    |--------------------------------------------------------------------------
    */

    if (!isValidEmail($to)) {

        return false;
    }

    try {

        $mail =
        createMailer();

        /*
        |--------------------------------------------------------------------------
        | RECIPIENT
        |--------------------------------------------------------------------------
        */

        $mail->addAddress($to);

        /*
        |--------------------------------------------------------------------------
        | EMAIL CONTENT
        |--------------------------------------------------------------------------
        */

        $mail->isHTML(true);

        $mail->Subject =
        $subject;

        $mail->Body =
        buildEmailTemplate(

            $subject,

            $htmlBody
        );

        $mail->AltBody =
        !empty($plainText)
            ? $plainText
            : strip_tags($htmlBody);

        /*
        |--------------------------------------------------------------------------
        | SEND MAIL
        |--------------------------------------------------------------------------
        */

        $result =
        $mail->send();

        /*
        |--------------------------------------------------------------------------
        | LOG SUCCESS
        |--------------------------------------------------------------------------
        */

        logMailActivity(

            $to,

            $subject,

            'success'
        );

        return $result;

    } catch (Exception $e) {

        /*
        |--------------------------------------------------------------------------
        | LOG FAILURE
        |--------------------------------------------------------------------------
        */

        logMailActivity(

            $to,

            $subject,

            'failed',

            $e->getMessage()
        );

        error_log(
            'Mail delivery failed.'
        );

        return false;
    }
}

/*
|--------------------------------------------------------------------------
| GENERIC EMAIL TEMPLATE
|--------------------------------------------------------------------------
*/

function buildEmailTemplate(
    string $title,
    string $body
): string {

    return '

        <div style="
            background:#f5f5f5;
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
                        color:#ffffff;
                        margin:0;
                        font-size:28px;
                    ">

                        '

                        .

                        escape(APP_NAME)

                        .

                        '

                    </h1>

                </div>

                <div style="padding:40px;">

                    '

                    .

                    $body

                    .

                '

                </div>

                <div style="
                    background:#f9fafb;
                    padding:20px;
                    text-align:center;
                    color:#6b7280;
                    font-size:14px;
                ">

                    © '

                    .

                    date('Y')

                    .

                    ' '

                    .

                    escape(APP_NAME)

                    .

                    '. All rights reserved.

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
    string $email,
    string $otp,
    string $name = 'User'
): bool {

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT
    |--------------------------------------------------------------------------
    */

    if (
        !checkRateLimit(
            'otp_email_' . md5($email),
            3,
            600
        )
    ) {

        return false;
    }

    $subject =
    'OTP Verification Code';

    $body = '

        <h2>OTP Verification</h2>

        <p>Hello '

        .

        escape($name)

        .

        ',</p>

        <p>Your OTP code is:</p>

        <h1 style="
            font-size:40px;
            letter-spacing:6px;
            color:#111827;
        ">
            '

            .

            escape($otp)

            .

        '
        </h1>

        <p>

            This OTP expires in '

            .

            OTP_EXPIRY_MINUTES

            .

            ' minutes.

        </p>

        <p>

            Never share this OTP with anyone.

        </p>
    ';

    $plainText =

        'Your OTP is '

        .

        $otp

        .

        '. It expires in '

        .

        OTP_EXPIRY_MINUTES

        .

        ' minutes.';

    return sendEmail(

        $email,

        $subject,

        $body,

        $plainText
    );
}

/*
|--------------------------------------------------------------------------
| PASSWORD RESET EMAIL
|--------------------------------------------------------------------------
*/

function sendPasswordResetEmail(
    string $email,
    string $otp,
    string $name = 'User'
): bool {

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT
    |--------------------------------------------------------------------------
    */

    if (
        !checkRateLimit(
            'password_reset_' . md5($email),
            3,
            600
        )
    ) {

        return false;
    }

    $subject =
    'Password Reset OTP';

    $body = '

        <h2>Password Reset Request</h2>

        <p>Hello '

        .

        escape($name)

        .

        ',</p>

        <p>

            Your password reset OTP is:

        </p>

        <h1 style="
            font-size:40px;
            letter-spacing:6px;
            color:#111827;
        ">
            '

            .

            escape($otp)

            .

        '
        </h1>

        <p>

            This OTP expires in '

            .

            OTP_EXPIRY_MINUTES

            .

            ' minutes.

        </p>

        <p>

            If you did not request this,
            ignore this email immediately.

        </p>
    ';

    $plainText =

        'Password reset OTP: '

        .

        $otp

        .

        '. Expires in '

        .

        OTP_EXPIRY_MINUTES

        .

        ' minutes.';

    return sendEmail(

        $email,

        $subject,

        $body,

        $plainText
    );
}

/*
|--------------------------------------------------------------------------
| ADMIN LOGIN ALERT
|--------------------------------------------------------------------------
*/

function sendAdminLoginAlert(
    string $email,
    string $adminName,
    string $ipAddress,
    string $device
): bool {

    $subject =
    'Admin Login Alert';

    $body = '

        <h2>Admin Login Detected</h2>

        <p>Hello '

        .

        escape($adminName)

        .

        ',</p>

        <p>

            A new admin login was detected.

        </p>

        <table cellpadding="10">

            <tr>

                <td><strong>IP Address:</strong></td>

                <td>'

                .

                escape($ipAddress)

                .

                '</td>

            </tr>

            <tr>

                <td><strong>Device:</strong></td>

                <td>'

                .

                escape($device)

                .

                '</td>

            </tr>

            <tr>

                <td><strong>Time:</strong></td>

                <td>'

                .

                date('Y-m-d H:i:s')

                .

                '</td>

            </tr>

        </table>

        <p>

            If this was not you,
            reset your password immediately.

        </p>
    ';

    return sendEmail(

        $email,

        $subject,

        $body,

        'Admin login detected.'
    );
}

/*
|--------------------------------------------------------------------------
| SECURITY ALERT EMAIL
|--------------------------------------------------------------------------
*/

function sendSecurityAlert(
    string $email,
    string $event,
    string $details = ''
): bool {

    /*
    |--------------------------------------------------------------------------
    | ALERT THROTTLING
    |--------------------------------------------------------------------------
    */

    if (
        !checkRateLimit(
            'security_alert_' . md5($email),
            5,
            3600
        )
    ) {

        return false;
    }

    $subject =
    'Security Alert';

    $body = '

        <h2>Security Alert</h2>

        <p>

            Suspicious activity detected.

        </p>

        <table cellpadding="10">

            <tr>

                <td><strong>Event:</strong></td>

                <td>'

                .

                escape($event)

                .

                '</td>

            </tr>

            <tr>

                <td><strong>Details:</strong></td>

                <td>'

                .

                escape($details)

                .

                '</td>

            </tr>

            <tr>

                <td><strong>IP Address:</strong></td>

                <td>'

                .

                escape(
                    $_SERVER['REMOTE_ADDR']
                    ?? 'Unknown'
                )

                .

                '</td>

            </tr>

        </table>

        <p>

            Contact support immediately if this was not you.

        </p>
    ';

    return sendEmail(

        $email,

        $subject,

        $body,

        'Security alert triggered.'
    );
}

/*
|--------------------------------------------------------------------------
| CONTACT FORM NOTIFICATION
|--------------------------------------------------------------------------
*/

function sendContactNotification(
    string $name,
    string $email,
    string $phone,
    string $message
): bool {

    $subject =
    'New Contact Inquiry';

    $body = '

        <h2>New Contact Form Submission</h2>

        <table cellpadding="10">

            <tr>

                <td><strong>Name:</strong></td>

                <td>'

                .

                escape($name)

                .

                '</td>

            </tr>

            <tr>

                <td><strong>Email:</strong></td>

                <td>'

                .

                escape($email)

                .

                '</td>

            </tr>

            <tr>

                <td><strong>Phone:</strong></td>

                <td>'

                .

                escape($phone)

                .

                '</td>

            </tr>

            <tr>

                <td><strong>Message:</strong></td>

                <td>'

                .

                nl2br(
                    escape($message)
                )

                .

                '</td>

            </tr>

        </table>
    ';

    return sendEmail(

        ADMIN_EMAIL,

        $subject,

        $body,

        'New contact inquiry received.'
    );
}

/*
|--------------------------------------------------------------------------
| MAIL ACTIVITY LOGGING
|--------------------------------------------------------------------------
*/

function logMailActivity(
    string $recipient,
    string $subject,
    string $status,
    string $error = ''
): void {

    global $conn;

    try {

        if (!isset($conn)) {

            return;
        }

        $query = "

            INSERT INTO mail_logs (

                recipient,
                subject,
                status,
                error_message,
                ip_address,
                created_at

            )

            VALUES (

                :recipient,
                :subject,
                :status,
                :error_message,
                :ip_address,
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':recipient' =>
            $recipient,

            ':subject' =>
            $subject,

            ':status' =>
            $status,

            ':error_message' =>
            $error,

            ':ip_address' =>

                $_SERVER['REMOTE_ADDR']
                ?? 'Unknown'
        ]);

    } catch (Exception $e) {

        error_log(
            'Mail log failed.'
        );
    }
}

/*
|--------------------------------------------------------------------------
| EMAIL VALIDATION
|--------------------------------------------------------------------------
*/

function isValidEmail(
    string $email
): bool {

    return filter_var(

        $email,

        FILTER_VALIDATE_EMAIL

    ) !== false;
}

/*
|--------------------------------------------------------------------------
| TEST SMTP CONNECTION
|--------------------------------------------------------------------------
*/

function testSmtpConnection(): bool
{
    try {

        $mail =
        createMailer();

        $mail->smtpConnect();

        $mail->smtpClose();

        return true;

    } catch (Exception $e) {

        error_log(
            'SMTP test failed.'
        );

        return false;
    }
}

?>