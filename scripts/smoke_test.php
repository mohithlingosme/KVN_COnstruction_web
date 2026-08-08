<?php

declare(strict_types=1);

/**
 * KVN Construction - Deployment Smoke Test
 *
 * Validates that a freshly deployed instance is operational:
 *   1. PHP version / required extensions present.
 *   2. Configuration loads (config/app.php).
 *   3. Database connection and required tables/views exist.
 *   4. migration records exist in schema_migrations.
 *   5. OTP auth infrastructure (otps view, user_otps table, triggers) present.
 *   6. Required system seed data present (roles, permissions, admin user).
 *
 * Usage:
 *   php scripts/smoke_test.php
 *
 * Exit codes:
 *   0 = all checks passed
 *   1 = one or more checks failed
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$ROOT = dirname(__DIR__);
$failures = [];
$checks = 0;
$passed = 0;

function check(string $label, bool $ok, array &$failures, string $detail = ''): void
{
    global $checks, $passed;
    $checks++;
    if ($ok) {
        $passed++;
        echo "  [PASS] {$label}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
    } else {
        $failures[] = $label;
        echo "  [FAIL] {$label}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
    }
}

echo "=== KVN Construction Smoke Test ===\n\n";

// 1. PHP version + extensions
echo "-- PHP --\n";
$phpVersion = PHP_VERSION;
$extMissing = [];
foreach (['pdo', 'pdo_mysql', 'session', 'json', 'openssl'] as $ext) {
    if (!extension_loaded($ext)) {
        $extMissing[] = $ext;
    }
}
check('PHP >= 8.0', version_compare($phpVersion, '8.0.0', '>='), $failures, $phpVersion);
check('Required PHP extensions', empty($extMissing), $failures, $extMissing ? implode(', ', $extMissing) : 'pdo, pdo_mysql, session, json, openssl');

// 2. Configuration loads
echo "\n-- Configuration --\n";
try {
    require_once $ROOT . '/config/app.php';
    check('config/app.php loads', true, $failures);
    check('APP_ENV defined', defined('APP_ENV'), $failures, defined('APP_ENV') ? APP_ENV : '');
    check('APP_DEBUG defined', defined('APP_DEBUG'), $failures);
    check('APP_KEY set (not placeholder)', defined('APP_KEY') && APP_KEY !== '' && !str_contains((string) APP_KEY, 'CHANGE_ME'), $failures);
} catch (Throwable $e) {
    check('config/app.php loads', false, $failures, $e->getMessage());
}

// 3. Database
echo "\n-- Database --\n";
$pdo = null;
try {
    require_once $ROOT . '/config/database.php';
    $pdo = Database::getInstance()->getConnection();
    check('Database connection', $pdo !== null, $failures);
} catch (Throwable $e) {
    check('Database connection', false, $failures, $e->getMessage());
}

if ($pdo) {
    // Required tables
    $requiredTables = ['users', 'roles', 'permissions', 'user_otps', 'otps', 'schema_migrations', 'settings', 'leads', 'projects', 'quotations'];
    $actual = [];
    foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM) as $r) {
        $actual[] = $r[0];
    }
    $missingTables = array_values(array_diff($requiredTables, $actual));
    check('Required tables present', empty($missingTables), $failures, $missingTables ? implode(', ', $missingTables) : implode(', ', $requiredTables));

    // otps view type
    $viewRow = $pdo->query("SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'otps'")->fetch();
    check('otps is a VIEW', ($viewRow['TABLE_TYPE'] ?? '') === 'VIEW', $failures, $viewRow['TABLE_TYPE'] ?? 'missing');

    // schema_migrations records
    $migCount = (int) $pdo->query('SELECT COUNT(*) AS c FROM schema_migrations')->fetch()['c'];
    check('schema_migrations populated', $migCount > 0, $failures, "$migCount record(s)");

    // OTP triggers
    $trig = $pdo->query("SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE()")->fetchAll(PDO::FETCH_COLUMN);
    check('OTP sync triggers present', count($trig) >= 2, $failures, '(' . count($trig) . ') ' . implode(', ', $trig));

    // System seed
    $roles = (int) $pdo->query('SELECT COUNT(*) AS c FROM roles')->fetch()['c'];
    $perms = (int) $pdo->query('SELECT COUNT(*) AS c FROM permissions')->fetch()['c'];
    $admins = (int) $pdo->query("SELECT COUNT(*) AS c FROM users WHERE role IN ('admin','super_admin')")->fetch()['c'];
    check('System seed: roles', $roles > 0, $failures, "$roles role(s)");
    check('System seed: permissions', $perms > 0, $failures, "$perms permission(s)");
    check('System seed: admin user', $admins > 0, $failures, "$admins admin(s)");
} else {
    for ($i = 0; $i < 8; $i++) {
        check('Database checks (skipped - no connection)', false, $failures, 'connection unavailable');
    }
}

echo "\n=== RESULT: {$passed}/{$checks} checks passed ===\n";
if ($failures) {
    echo "FAILED CHECKS:\n";
    foreach ($failures as $f) {
        echo "  - {$f}\n";
    }
    exit(1);
}
exit(0);
