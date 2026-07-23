<?php

declare(strict_types=1);

/*
 * Compatibility bridge for legacy dashboard pages which use mysqli. New code
 * uses the PDO connection created by config/app.php; these older pages expect
 * mysqli methods such as bind_param() and num_rows, so give them a dedicated
 * connection without exposing credentials or connection errors to visitors.
 */
require_once dirname(__DIR__, 2) . '/config/app.php';

if (!extension_loaded('mysqli')) {
    throw new RuntimeException('The mysqli PHP extension is required for legacy admin pages.');
}

$legacyConnection = mysqli_init();
if ($legacyConnection === false || !@mysqli_real_connect($legacyConnection, DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT)) {
    error_log('Legacy mysqli connection failed.');
    throw new RuntimeException('Database connection unavailable.');
}

$legacyConnection->set_charset('utf8mb4');
$conn = $legacyConnection;
