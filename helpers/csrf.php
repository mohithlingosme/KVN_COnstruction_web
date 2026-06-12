<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CSRF SECURITY SYSTEM
|--------------------------------------------------------------------------
| File: /helpers/csrf.php
|--------------------------------------------------------------------------
*/

if (!defined('CSRF_TOKEN_EXPIRY')) {
    define('CSRF_TOKEN_EXPIRY', 1800); // 30 minutes
}

/*
|--------------------------------------------------------------------------
| GENERATE CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken(): string
    {
        if (
            isset($_SESSION['csrf_token']) &&
            isset($_SESSION['csrf_token_time']) &&
            (time() - $_SESSION['csrf_token_time']) < CSRF_TOKEN_EXPIRY
        ) {
            return $_SESSION['csrf_token'];
        }

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();

        $_SESSION['csrf_fingerprint'] = hash(
            'sha256',
            ($_SERVER['REMOTE_ADDR'] ?? '') .
            ($_SERVER['HTTP_USER_AGENT'] ?? '')
        );

        return $_SESSION['csrf_token'];
    }
}

/*
|--------------------------------------------------------------------------
| CSRF INPUT FIELD
|--------------------------------------------------------------------------
*/

if (!function_exists('csrfInputField')) {
    function csrfInputField(): string
    {
        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            htmlspecialchars(
                generateCsrfToken(),
                ENT_QUOTES,
                'UTF-8'
            )
        );
    }
}

/*
|--------------------------------------------------------------------------
| CSRF META TAG
|--------------------------------------------------------------------------
*/

if (!function_exists('csrfMetaTag')) {
    function csrfMetaTag(): string
    {
        return sprintf(
            '<meta name="csrf-token" content="%s">',
            htmlspecialchars(
                generateCsrfToken(),
                ENT_QUOTES,
                'UTF-8'
            )
        );
    }
}

/*
|--------------------------------------------------------------------------
| AJAX TOKEN
|--------------------------------------------------------------------------
*/

if (!function_exists('getAjaxCsrfToken')) {
    function getAjaxCsrfToken(): string
    {
        return generateCsrfToken();
    }
}

/*
|--------------------------------------------------------------------------
| VERIFY TOKEN
|--------------------------------------------------------------------------
*/

if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken(?string $token): bool
    {
        if (
            empty($_SESSION['csrf_token']) ||
            empty($_SESSION['csrf_token_time'])
        ) {
            return false;
        }

        if (empty($token)) {
            return false;
        }

        if (
            (time() - $_SESSION['csrf_token_time'])
            > CSRF_TOKEN_EXPIRY
        ) {
            destroyCsrfToken();
            return false;
        }

        $currentFingerprint = hash(
            'sha256',
            ($_SERVER['REMOTE_ADDR'] ?? '') .
            ($_SERVER['HTTP_USER_AGENT'] ?? '')
        );

        if (
            isset($_SESSION['csrf_fingerprint']) &&
            !hash_equals(
                $_SESSION['csrf_fingerprint'],
                $currentFingerprint
            )
        ) {
            if (function_exists('logSecurityEvent')) {
                logSecurityEvent(
                    $_SESSION['user_id'] ?? null,
                    'csrf_fingerprint_mismatch',
                    'critical',
                    'Possible session hijack attempt'
                );
            }

            destroyCsrfToken();
            return false;
        }

        return hash_equals(
            $_SESSION['csrf_token'],
            $token
        );
    }
}

/*
|--------------------------------------------------------------------------
| VALIDATE REQUEST
|--------------------------------------------------------------------------
*/

if (!function_exists('validateCsrf')) {
    function validateCsrf(): bool
    {
        $protectedMethods = [
            'POST',
            'PUT',
            'PATCH',
            'DELETE'
        ];

        if (
            !in_array(
                $_SERVER['REQUEST_METHOD'] ?? 'GET',
                $protectedMethods,
                true
            )
        ) {
            return true;
        }

        $token =
            $_POST['csrf_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? '';

        if (!verifyCsrfToken($token)) {

            if (function_exists('logSecurityEvent')) {
                logSecurityEvent(
                    $_SESSION['user_id'] ?? null,
                    'csrf_validation_failed',
                    'critical',
                    'Invalid CSRF token'
                );
            }

            http_response_code(403);
            exit('Invalid CSRF token.');
        }

        regenerateCsrfToken();

        return true;
    }
}

/*
|--------------------------------------------------------------------------
| REGENERATE TOKEN
|--------------------------------------------------------------------------
*/

if (!function_exists('regenerateCsrfToken')) {
    function regenerateCsrfToken(): string
    {
        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );

        $_SESSION['csrf_token_time'] = time();

        return $_SESSION['csrf_token'];
    }
}

/*
|--------------------------------------------------------------------------
| DESTROY TOKEN
|--------------------------------------------------------------------------
*/

if (!function_exists('destroyCsrfToken')) {
    function destroyCsrfToken(): void
    {
        unset(
            $_SESSION['csrf_token'],
            $_SESSION['csrf_token_time'],
            $_SESSION['csrf_fingerprint']
        );
    }
}

/*
|--------------------------------------------------------------------------
| VALIDATE AJAX REQUEST
|--------------------------------------------------------------------------
*/

if (!function_exists('validateAjaxCsrf')) {
    function validateAjaxCsrf(): bool
    {
        return verifyCsrfToken(
            $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''
        );
    }
}

/*
|--------------------------------------------------------------------------
| JSON ERROR RESPONSE
|--------------------------------------------------------------------------
*/

if (!function_exists('csrfJsonError')) {
    function csrfJsonError(): void
    {
        http_response_code(403);

        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => 'Invalid CSRF token.'
        ]);

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| CLEANUP EXPIRED TOKEN
|--------------------------------------------------------------------------
*/

if (!function_exists('cleanupExpiredCsrf')) {
    function cleanupExpiredCsrf(): void
    {
        if (
            isset($_SESSION['csrf_token_time']) &&
            (
                time() - $_SESSION['csrf_token_time']
            ) > CSRF_TOKEN_EXPIRY
        ) {
            destroyCsrfToken();
        }
    }
}

/*
|--------------------------------------------------------------------------
| AUTO CLEANUP
|--------------------------------------------------------------------------
*/

cleanupExpiredCsrf();