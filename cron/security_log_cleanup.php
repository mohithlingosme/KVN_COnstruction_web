<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

cleanupSecurityLogs(90);

echo "Security log cleanup complete.\n";
