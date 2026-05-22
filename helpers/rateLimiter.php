<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ADVANCED RATE LIMITER
|--------------------------------------------------------------------------
| File:
| /helpers/rateLimiter.php
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| DEFAULT LIMITS
|--------------------------------------------------------------------------
*/

define('DEFAULT_RATE_LIMIT', 5);

define('DEFAULT_RATE_WINDOW', 300);

/*
|--------------------------------------------------------------------------
| CLIENT IDENTIFIER
|--------------------------------------------------------------------------
*/

function limiterIdentifier()
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    return hash(

        'sha256',

        $ip . $agent
    );
}

/*
|--------------------------------------------------------------------------
| CURRENT ROUTE
|--------------------------------------------------------------------------
*/

function currentRouteName()
{
    return $_SERVER['REQUEST_URI'] ?? 'unknown';
}

/*
|--------------------------------------------------------------------------
| CHECK RATE LIMIT
|--------------------------------------------------------------------------
*/

function checkRateLimit(

    $actionType,

    $maxAttempts = DEFAULT_RATE_LIMIT,

    $decaySeconds = DEFAULT_RATE_WINDOW
) {

    global $conn;

    $identifier =
    limiterIdentifier();

    $routeName =
    currentRouteName();

    try {

        /*
        |--------------------------------------------------------------------------
        | FETCH RATE LIMIT
        |--------------------------------------------------------------------------
        */

        $query = "

            SELECT *

            FROM rate_limits

            WHERE identifier = :identifier

            AND action_type = :action_type

            AND route_name = :route_name

            LIMIT 1
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':identifier' =>
            $identifier,

            ':action_type' =>
            $actionType,

            ':route_name' =>
            $routeName
        ]);

        $record =
        $stmt->fetch();

        /*
        |--------------------------------------------------------------------------
        | NO RECORD
        |--------------------------------------------------------------------------
        */

        if (!$record) {

            createRateLimit(

                $identifier,

                $actionType,

                $routeName
            );

            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | BLOCKED CHECK
        |--------------------------------------------------------------------------
        */

        if (

            !empty($record['blocked_until'])

            &&

            strtotime($record['blocked_until']) > time()
        ) {

            if (function_exists('logSecurityEvent')) {

                logSecurityEvent(

                    $_SESSION['user_id'] ?? null,

                    'rate_limit_blocked',

                    'warning',

                    'Blocked action: ' . $actionType
                );
            }

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | RESET WINDOW
        |--------------------------------------------------------------------------
        */

        $updatedAt =
        strtotime($record['updated_at']);

        if (

            (time() - $updatedAt)
            > $decaySeconds
        ) {

            resetRateLimit($actionType);

            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | MAX ATTEMPTS REACHED
        |--------------------------------------------------------------------------
        */

        if (

            $record['attempts']
            >=
            $maxAttempts
        ) {

            blockRateLimit(

                $identifier,

                $actionType,

                $routeName,

                $decaySeconds
            );

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | INCREMENT ATTEMPTS
        |--------------------------------------------------------------------------
        */

        incrementRateLimit(

            $identifier,

            $actionType,

            $routeName
        );

        return true;

    } catch (Exception $e) {

        error_log(

            'Rate Limit Error: '

            .

            $e->getMessage()
        );

        return true;
    }
}

/*
|--------------------------------------------------------------------------
| CREATE RATE LIMIT RECORD
|--------------------------------------------------------------------------
*/

function createRateLimit(

    $identifier,

    $actionType,

    $routeName
) {

    global $conn;

    $query = "

        INSERT INTO rate_limits (

            identifier,
            action_type,
            route_name,
            attempts,
            created_at,
            updated_at

        ) VALUES (

            :identifier,
            :action_type,
            :route_name,
            1,
            NOW(),
            NOW()
        )
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':identifier' =>
        $identifier,

        ':action_type' =>
        $actionType,

        ':route_name' =>
        $routeName
    ]);
}

/*
|--------------------------------------------------------------------------
| INCREMENT RATE LIMIT
|--------------------------------------------------------------------------
*/

function incrementRateLimit(

    $identifier,

    $actionType,

    $routeName
) {

    global $conn;

    $query = "

        UPDATE rate_limits

        SET

            attempts = attempts + 1,
            updated_at = NOW()

        WHERE identifier = :identifier

        AND action_type = :action_type

        AND route_name = :route_name
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':identifier' =>
        $identifier,

        ':action_type' =>
        $actionType,

        ':route_name' =>
        $routeName
    ]);
}

/*
|--------------------------------------------------------------------------
| BLOCK RATE LIMIT
|--------------------------------------------------------------------------
*/

function blockRateLimit(

    $identifier,

    $actionType,

    $routeName,

    $seconds
) {

    global $conn;

    $blockedUntil = date(

        'Y-m-d H:i:s',

        strtotime(
            '+' . $seconds . ' seconds'
        )
    );

    $query = "

        UPDATE rate_limits

        SET blocked_until = :blocked_until

        WHERE identifier = :identifier

        AND action_type = :action_type

        AND route_name = :route_name
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':blocked_until' =>
        $blockedUntil,

        ':identifier' =>
        $identifier,

        ':action_type' =>
        $actionType,

        ':route_name' =>
        $routeName
    ]);

    /*
    |--------------------------------------------------------------------------
    | SECURITY LOG
    |--------------------------------------------------------------------------
    */

    if (function_exists('logSecurityEvent')) {

        logSecurityEvent(

            $_SESSION['user_id'] ?? null,

            'rate_limit_exceeded',

            'critical',

            'Action blocked: ' . $actionType
        );
    }
}

/*
|--------------------------------------------------------------------------
| RESET RATE LIMIT
|--------------------------------------------------------------------------
*/

function resetRateLimit($actionType)
{
    global $conn;

    $identifier =
    limiterIdentifier();

    $routeName =
    currentRouteName();

    $query = "

        DELETE FROM rate_limits

        WHERE identifier = :identifier

        AND action_type = :action_type

        AND route_name = :route_name
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':identifier' =>
        $identifier,

        ':action_type' =>
        $actionType,

        ':route_name' =>
        $routeName
    ]);
}

/*
|--------------------------------------------------------------------------
| REMAINING ATTEMPTS
|--------------------------------------------------------------------------
*/

function remainingAttempts(

    $actionType,

    $maxAttempts = DEFAULT_RATE_LIMIT
) {

    global $conn;

    $identifier =
    limiterIdentifier();

    $routeName =
    currentRouteName();

    $query = "

        SELECT attempts

        FROM rate_limits

        WHERE identifier = :identifier

        AND action_type = :action_type

        AND route_name = :route_name

        LIMIT 1
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':identifier' =>
        $identifier,

        ':action_type' =>
        $actionType,

        ':route_name' =>
        $routeName
    ]);

    $record =
    $stmt->fetch();

    if (!$record) {

        return $maxAttempts;
    }

    return max(

        0,

        $maxAttempts - $record['attempts']
    );
}

/*
|--------------------------------------------------------------------------
| RETRY AFTER
|--------------------------------------------------------------------------
*/

function retryAfter($actionType)
{
    global $conn;

    $identifier =
    limiterIdentifier();

    $routeName =
    currentRouteName();

    $query = "

        SELECT blocked_until

        FROM rate_limits

        WHERE identifier = :identifier

        AND action_type = :action_type

        AND route_name = :route_name

        LIMIT 1
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':identifier' =>
        $identifier,

        ':action_type' =>
        $actionType,

        ':route_name' =>
        $routeName
    ]);

    $record =
    $stmt->fetch();

    if (

        !$record

        ||

        empty($record['blocked_until'])
    ) {

        return 0;
    }

    return max(

        0,

        strtotime($record['blocked_until'])
        - time()
    );
}

/*
|--------------------------------------------------------------------------
| CLEANUP EXPIRED LIMITS
|--------------------------------------------------------------------------
*/

function cleanupExpiredRateLimits()
{
    global $conn;

    try {

        $query = "

            DELETE FROM rate_limits

            WHERE updated_at
            <

            DATE_SUB(
                NOW(),
                INTERVAL 1 DAY
            )
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
| COMMON LIMIT CONFIGS
|--------------------------------------------------------------------------
*/

function loginRateLimit()
{
    return checkRateLimit(

        'login',

        5,

        300
    );
}

function adminLoginRateLimit()
{
    return checkRateLimit(

        'admin_login',

        3,

        600
    );
}

function otpRateLimit()
{
    return checkRateLimit(

        'otp',

        3,

        600
    );
}

function estimatorRateLimit()
{
    return checkRateLimit(

        'estimator',

        20,

        3600
    );
}

function contactRateLimit()
{
    return checkRateLimit(

        'contact',

        5,

        3600
    );
}

/*
|--------------------------------------------------------------------------
| AUTO CLEANUP
|--------------------------------------------------------------------------
*/

cleanupExpiredRateLimits();

?>