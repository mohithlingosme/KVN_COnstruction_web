<?php

declare(strict_types=1);

/*
 * PDO Database Connection (replaces legacy mysqli).
 *
 * All queries now use PDO prepared statements internally, eliminating
 * SQL injection vulnerabilities from string interpolation.
 *
 * The $conn variable provides a mysqli-compatible interface so that
 * existing code using $conn->query(), $conn->prepare(), fetch_assoc(),
 * num_rows, etc. continues to work without modification.
 *
 * @see App\Security\PdoDatabase
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/app/security/PdoDatabase.php';

use App\Security\PdoDatabase;

try {
    $conn = new PdoDatabase([
        'host'    => DB_HOST,
        'port'    => DB_PORT,
        'dbname'  => DB_NAME,
        'user'    => DB_USER,
        'pass'    => DB_PASS,
        'charset' => 'utf8mb4',
    ]);
} catch (\Throwable $e) {
    error_log('PDO Database connection failed: ' . $e->getMessage());
    throw new RuntimeException('Database connection unavailable.');
}
