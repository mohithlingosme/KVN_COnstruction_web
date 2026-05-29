<?php

declare(strict_types=1);

function userHasPermission(?int $userId, string $permission): bool
{
    global $conn;

    if ($userId === null) {
        return false;
    }

    if (isAdmin()) {
        return true;
    }

    if (!isset($conn)) {
        return false;
    }

    try {
        $stmt = $conn->prepare(
            'SELECT 1
             FROM user_roles ur
             INNER JOIN role_permissions rp ON rp.role_id = ur.role_id
             INNER JOIN permissions p ON p.id = rp.permission_id
             WHERE ur.user_id = :user_id AND p.permission_key = :permission
             LIMIT 1'
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':permission' => $permission,
        ]);

        return (bool) $stmt->fetch();
    } catch (Throwable $exception) {
        return false;
    }
}

function requirePermission(string $permission): void
{
    if (userHasPermission(currentUserId(), $permission)) {
        return;
    }

    logSecurityEvent(currentUserId(), 'permission_denied', 'warning', [
        'permission' => $permission,
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
    ]);

    http_response_code(403);
    exit('Forbidden');
}
