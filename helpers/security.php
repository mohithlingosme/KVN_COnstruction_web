<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| SECURITY HELPER SYSTEM
|--------------------------------------------------------------------------
| File:
| /helpers/security.php
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| SANITIZE STRING
|--------------------------------------------------------------------------
*/

function sanitize($data)
{
    if (is_array($data)) {

        return array_map('sanitize', $data);
    }

    return trim(

        htmlspecialchars(

            strip_tags($data),

            ENT_QUOTES,

            'UTF-8'
        )
    );
}

/*
|--------------------------------------------------------------------------
| ESCAPE OUTPUT
|--------------------------------------------------------------------------
*/

function escape($data)
{
    return htmlspecialchars(

        $data ?? '',

        ENT_QUOTES,

        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| SAFE RICH TEXT
|--------------------------------------------------------------------------
|
| Used for:
| - Blogs
| - CMS
| - Testimonials
|--------------------------------------------------------------------------
*/

function safeRichText($content)
{
    return strip_tags(

        $content,

        '<p><br><b><strong><i><em><ul><ol><li><h1><h2><h3><h4><blockquote><a><img>'
    );
}

/*
|--------------------------------------------------------------------------
| SECURITY HEADERS
|--------------------------------------------------------------------------
*/

function securityHeaders()
{
    /*
    |--------------------------------------------------------------------------
    | CLICKJACKING
    |--------------------------------------------------------------------------
    */

    header('X-Frame-Options: SAMEORIGIN');

    /*
    |--------------------------------------------------------------------------
    | MIME SNIFFING
    |--------------------------------------------------------------------------
    */

    header('X-Content-Type-Options: nosniff');

    /*
    |--------------------------------------------------------------------------
    | XSS FILTER
    |--------------------------------------------------------------------------
    */

    header('X-XSS-Protection: 1; mode=block');

    /*
    |--------------------------------------------------------------------------
    | REFERRER POLICY
    |--------------------------------------------------------------------------
    */

    header(

        'Referrer-Policy: strict-origin-when-cross-origin'
    );

    /*
    |--------------------------------------------------------------------------
    | CONTENT SECURITY POLICY
    |--------------------------------------------------------------------------
    */

    header(

        "Content-Security-Policy:

        default-src 'self';

        img-src 'self' data: https:;

        style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com;

        script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com;

        font-src 'self' https://fonts.gstatic.com;

        frame-src https://www.youtube.com https://www.youtube-nocookie.com;

        connect-src 'self';

        object-src 'none';"
    );

    /*
    |--------------------------------------------------------------------------
    | PERMISSIONS POLICY
    |--------------------------------------------------------------------------
    */

    header(

        'Permissions-Policy:
        geolocation=(),
        microphone=(),
        camera=()'
    );
}

/*
|--------------------------------------------------------------------------
| GENERATE SECURE TOKEN
|--------------------------------------------------------------------------
*/

function generateSecureToken($length = 64)
{
    return bin2hex(

        random_bytes($length / 2)
    );
}

/*
|--------------------------------------------------------------------------
| HASH VALUE
|--------------------------------------------------------------------------
*/

function secureHash($value)
{
    return hash(

        'sha256',

        $value
    );
}

/*
|--------------------------------------------------------------------------
| PASSWORD HASH
|--------------------------------------------------------------------------
*/

function hashPassword($password)
{
    return password_hash(

        $password,

        PASSWORD_BCRYPT
    );
}

/*
|--------------------------------------------------------------------------
| VERIFY PASSWORD
|--------------------------------------------------------------------------
*/

function verifyPassword(

    $password,

    $hash
) {

    return password_verify(

        $password,

        $hash
    );
}

/*
|--------------------------------------------------------------------------
| PASSWORD STRENGTH CHECK
|--------------------------------------------------------------------------
*/

function validatePasswordStrength($password)
{
    return (

        strlen($password) >= 8

        &&

        preg_match('/[A-Z]/', $password)

        &&

        preg_match('/[a-z]/', $password)

        &&

        preg_match('/[0-9]/', $password)
    );
}

/*
|--------------------------------------------------------------------------
| VALIDATE EMAIL
|--------------------------------------------------------------------------
*/

function validateEmail($email)
{
    return filter_var(

        $email,

        FILTER_VALIDATE_EMAIL
    );
}

/*
|--------------------------------------------------------------------------
| VALIDATE PHONE
|--------------------------------------------------------------------------
*/

function validatePhone($phone)
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

function sanitizePhone($phone)
{
    return preg_replace(

        '/[^0-9]/',

        '',

        $phone
    );
}

/*
|--------------------------------------------------------------------------
| GENERATE OTP
|--------------------------------------------------------------------------
*/

function generateOtp($length = 6)
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
| VALIDATE FILE MIME
|--------------------------------------------------------------------------
*/

function validateFileMime(

    $file,

    $allowedTypes = []
) {

    if (

        empty($allowedTypes)
    ) {

        $allowedTypes = [

            'image/jpeg',
            'image/png',
            'image/webp',
            'application/pdf'
        ];
    }

    $finfo =
    finfo_open(FILEINFO_MIME_TYPE);

    $mime =
    finfo_file(

        $finfo,

        $file['tmp_name']
    );

    finfo_close($finfo);

    return in_array(

        $mime,

        $allowedTypes
    );
}

/*
|--------------------------------------------------------------------------
| VALIDATE FILE EXTENSION
|--------------------------------------------------------------------------
*/

function validateFileExtension(

    $filename,

    $allowedExtensions = []
) {

    $dangerousExtensions = [

        'php',
        'phtml',
        'exe',
        'sh',
        'js',
        'bat'
    ];

    $extension = strtolower(

        pathinfo(

            $filename,

            PATHINFO_EXTENSION
        )
    );

    if (

        in_array(

            $extension,

            $dangerousExtensions
        )
    ) {

        return false;
    }

    if (!empty($allowedExtensions)) {

        return in_array(

            $extension,

            $allowedExtensions
        );
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| SECURE FILE NAME
|--------------------------------------------------------------------------
*/

function secureFilename($filename)
{
    $extension = pathinfo(

        $filename,

        PATHINFO_EXTENSION
    );

    return

        uniqid('kvn_', true)

        .

        '.'

        .

        strtolower($extension);
}

/*
|--------------------------------------------------------------------------
| VALIDATE IMAGE
|--------------------------------------------------------------------------
*/

function validateImage($file)
{
    return getimagesize(

        $file['tmp_name']
    ) !== false;
}

/*
|--------------------------------------------------------------------------
| LOG SECURITY EVENT
|--------------------------------------------------------------------------
*/

function logSecurityEvent(

    $userId,

    $eventType,

    $eventLevel = 'info',

    $details = null
) {

    global $conn;

    try {

        $query = "

            INSERT INTO security_logs (

                user_id,
                event_type,
                event_level,
                ip_address,
                user_agent,
                event_details,
                request_uri,
                created_at

            ) VALUES (

                :user_id,
                :event_type,
                :event_level,
                :ip_address,
                :user_agent,
                :event_details,
                :request_uri,
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':user_id' =>
            $userId,

            ':event_type' =>
            $eventType,

            ':event_level' =>
            $eventLevel,

            ':ip_address' =>
            $_SERVER['REMOTE_ADDR']
            ?? null,

            ':user_agent' =>
            $_SERVER['HTTP_USER_AGENT']
            ?? null,

            ':event_details' =>
            $details,

            ':request_uri' =>
            $_SERVER['REQUEST_URI']
            ?? null
        ]);

    } catch (Exception $e) {

        error_log(

            'Security Log Error: '

            .

            $e->getMessage()
        );
    }
}

/*
|--------------------------------------------------------------------------
| LOG ADMIN ACTION
|--------------------------------------------------------------------------
*/

function logAdminAction(

    $adminId,

    $action,

    $details = null
) {

    global $conn;

    try {

        $query = "

            INSERT INTO audit_logs (

                user_id,
                action,
                details,
                ip_address,
                created_at

            ) VALUES (

                :user_id,
                :action,
                :details,
                :ip_address,
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':user_id' =>
            $adminId,

            ':action' =>
            $action,

            ':details' =>
            $details,

            ':ip_address' =>
            $_SERVER['REMOTE_ADDR']
            ?? null
        ]);

    } catch (Exception $e) {

        error_log(

            'Audit Log Error: '

            .

            $e->getMessage()
        );
    }
}

/*
|--------------------------------------------------------------------------
| SUSPICIOUS ACTIVITY
|--------------------------------------------------------------------------
*/

function suspiciousActivity(

    $reason,

    $severity = 'critical'
) {

    logSecurityEvent(

        null,

        'suspicious_activity',

        $severity,

        $reason
    );
}

/*
|--------------------------------------------------------------------------
| CSRF SAFE POST
|--------------------------------------------------------------------------
*/

function safePost($key)
{
    return sanitize(

        $_POST[$key] ?? ''
    );
}

/*
|--------------------------------------------------------------------------
| SAFE GET
|--------------------------------------------------------------------------
*/

function safeGet($key)
{
    return sanitize(

        $_GET[$key] ?? ''
    );
}

/*
|--------------------------------------------------------------------------
| JSON RESPONSE
|--------------------------------------------------------------------------
*/

function jsonResponse(

    $data = [],

    $status = 200
) {

    http_response_code($status);

    header(
        'Content-Type: application/json'
    );

    echo json_encode($data);

    exit;
}

/*
|--------------------------------------------------------------------------
| CLIENT IP
|--------------------------------------------------------------------------
*/

function clientIp()
{
    return $_SERVER['REMOTE_ADDR']
    ?? 'UNKNOWN';
}

/*
|--------------------------------------------------------------------------
| USER AGENT
|--------------------------------------------------------------------------
*/

function clientUserAgent()
{
    return $_SERVER['HTTP_USER_AGENT']
    ?? 'UNKNOWN';
}

/*
|--------------------------------------------------------------------------
| AJAX REQUEST
|--------------------------------------------------------------------------
*/

function isAjaxRequest()
{
    return (

        !empty(

            $_SERVER['HTTP_X_REQUESTED_WITH']
        )

        &&

        strtolower(

            $_SERVER['HTTP_X_REQUESTED_WITH']

        ) === 'xmlhttprequest'
    );
}

/*
|--------------------------------------------------------------------------
| REQUIRE AJAX
|--------------------------------------------------------------------------
*/

function requireAjax()
{
    if (!isAjaxRequest()) {

        http_response_code(403);

        exit('Forbidden');
    }
}

/*
|--------------------------------------------------------------------------
| CLEAN EXPIRED SECURITY LOGS
|--------------------------------------------------------------------------
*/

function cleanupSecurityLogs($days = 90)
{
    global $conn;

    try {

        $query = "

            DELETE FROM security_logs

            WHERE created_at
            <

            DATE_SUB(
                NOW(),
                INTERVAL :days DAY
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->bindValue(

            ':days',

            (int)$days,

            PDO::PARAM_INT
        );

        $stmt->execute();

    } catch (Exception $e) {

        error_log(
            $e->getMessage()
        );
    }
}
?>