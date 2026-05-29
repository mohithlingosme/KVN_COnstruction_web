<?php

declare(strict_types=1);

class AuditLogService
{
    public function log(int $userId, string $actionType, ?string $description = null, ?string $entityType = null, $entityId = null, $oldValues = null, $newValues = null): bool
    {
        return logAdminAction($userId, $actionType, $description, $entityType, $entityId, $oldValues, $newValues);
    }
}
