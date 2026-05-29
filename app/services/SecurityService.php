<?php

declare(strict_types=1);

class SecurityService
{
    public function log(string $eventType, string $level = 'info', $details = null, ?int $userId = null): bool
    {
        return logSecurityEvent($userId, $eventType, $level, $details);
    }

    public function logSuspicious(string $reason, ?int $userId = null): bool
    {
        return logSecurityEvent($userId, 'suspicious_activity', 'critical', $reason);
    }
}
