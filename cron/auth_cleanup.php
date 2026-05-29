<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

cleanupExpiredSessions();
cleanupExpiredOtps();
cleanupExpiredRateLimits();

$conn->exec('DELETE FROM email_verification_tokens WHERE expires_at < NOW() OR verified_at IS NOT NULL');

echo "Auth cleanup complete.\n";
