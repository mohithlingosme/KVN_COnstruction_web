<?php

declare(strict_types=1);

/**
 * KVN Construction - Standardized API Response Helper
 * 
 * Every API endpoint must use these functions for consistent responses.
 * 
 * Response Format:
 * {
 *     "status": true|false,
 *     "message": "Human-readable message",
 *     "data": { ... } | [ ... ] | null,
 *     "errors": { ... } | null,
 *     "meta": { "total": N, "page": N, "per_page": N, "total_pages": N } | null
 * }
 */

if (!function_exists('apiSuccess')) {
    function apiSuccess($data = null, string $message = 'Success', int $statusCode = 200, ?array $meta = null): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => $meta,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('apiError')) {
    function apiError(string $message = 'Error', $errors = null, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'meta' => null,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('apiValidationError')) {
    function apiValidationError(array $errors, string $message = 'Validation failed'): void
    {
        apiError($message, $errors, 422);
    }
}

if (!function_exists('apiNotFound')) {
    function apiNotFound(string $message = 'Resource not found'): void
    {
        apiError($message, null, 404);
    }
}

if (!function_exists('apiUnauthorized')) {
    function apiUnauthorized(string $message = 'Unauthorized'): void
    {
        apiError($message, null, 401);
    }
}

if (!function_exists('apiForbidden')) {
    function apiForbidden(string $message = 'Forbidden'): void
    {
        apiError($message, null, 403);
    }
}

if (!function_exists('apiTooManyRequests')) {
    function apiTooManyRequests(string $message = 'Too many requests'): void
    {
        apiError($message, null, 429);
    }
}

if (!function_exists('apiServerError')) {
    function apiServerError(string $message = 'Internal server error'): void
    {
        apiError($message, null, 500);
    }
}

if (!function_exists('apiPaginated')) {
    function apiPaginated(array $data, int $total, int $page = 1, int $perPage = 15, string $message = 'Success'): void
    {
        $meta = [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
        apiSuccess($data, $message, 200, $meta);
    }
}

/**
 * HTTP Status Code Constants
 */
if (!defined('HTTP_OK')) { define('HTTP_OK', 200); }
if (!defined('HTTP_CREATED')) { define('HTTP_CREATED', 201); }
if (!defined('HTTP_NO_CONTENT')) { define('HTTP_NO_CONTENT', 204); }
if (!defined('HTTP_BAD_REQUEST')) { define('HTTP_BAD_REQUEST', 400); }
if (!defined('HTTP_UNAUTHORIZED')) { define('HTTP_UNAUTHORIZED', 401); }
if (!defined('HTTP_FORBIDDEN')) { define('HTTP_FORBIDDEN', 403); }
if (!defined('HTTP_NOT_FOUND')) { define('HTTP_NOT_FOUND', 404); }
if (!defined('HTTP_UNPROCESSABLE')) { define('HTTP_UNPROCESSABLE', 422); }
if (!defined('HTTP_TOO_MANY')) { define('HTTP_TOO_MANY', 429); }
if (!defined('HTTP_SERVER_ERROR')) { define('HTTP_SERVER_ERROR', 500); }