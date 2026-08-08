<?php

declare(strict_types=1);

/**
 * KVN Construction - Health Check Endpoint
 *
 * Lightweight health probe for uptime monitors / load balancers.
 *
 * GET /health.php
 *   - 200 {"status":"ok","app":"kvnc_construction","db":true,"env":"production"}
 *   - 503 {"status":"degraded", ...} when database is unreachable.
 *
 * This endpoint does NOT expose secrets, file listings, or configuration.
 */

header('Content-Type: application/json');

try {
    $configFile = dirname(__DIR__) . '/config/app.php';
    if (is_file($configFile)) {
        require_once $configFile;
    }
} catch (Throwable $e) {
    // fall through; report degraded
}

$env = defined('APP_ENV') ? APP_ENV : 'unknown';
$dbOk = false;
$dbError = '';

try {
    require_once dirname(__DIR__) . '/config/database.php';
    $pdo = Database::getInstance()->getConnection();
    if ($pdo) {
        $pdo->query('SELECT 1');
        $dbOk = true;
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

if ($dbOk) {
    http_response_code(200);
    echo json_encode([
        'status' => 'ok',
        'app'    => 'kvnc_construction',
        'db'     => true,
        'env'    => $env,
        'time'   => gmdate('c'),
    ]);
} else {
    http_response_code(503);
    echo json_encode([
        'status' => 'degraded',
        'app'    => 'kvnc_construction',
        'db'     => false,
        'env'    => $env,
        'error'  => 'database_unavailable',
        'time'   => gmdate('c'),
    ]);
}
exit;
