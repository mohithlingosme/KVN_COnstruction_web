<?php

declare(strict_types=1);

/**
 * Base Service - All business logic must live here.
 * Services orchestrate repositories and contain validation, calculations, workflows.
 * Services must NOT contain SQL queries directly.
 */
abstract class Service
{
    /**
     * Standardized API response format
     */
    protected function success($data = null, string $message = 'Success', int $status = 200): array
    {
        return [
            'status' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => null,
        ];
    }

    protected function error(string $message, $errors = null, int $status = 400): array
    {
        return [
            'status' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'meta' => null,
        ];
    }

    protected function paginated(array $data, int $total, int $page, int $perPage): array
    {
        return [
            'status' => true,
            'message' => 'Success',
            'data' => $data,
            'errors' => null,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ];
    }

    /**
     * Validate required fields
     */
    protected function validateRequired(array $data, array $fields): ?array
    {
        $missing = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $missing[] = $field;
            }
        }
        return empty($missing) ? null : $missing;
    }

    /**
     * Sanitize input data
     */
    protected function sanitize(array $data, array $allowedFields): array
    {
        $sanitized = [];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $value = $data[$field];
                if (is_string($value)) {
                    $sanitized[$field] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
                } else {
                    $sanitized[$field] = $value;
                }
            }
        }
        return $sanitized;
    }

    /**
     * Begin database transaction
     */
    protected function beginTransaction(): void
    {
        // Transaction management handled at controller level if needed
    }
}