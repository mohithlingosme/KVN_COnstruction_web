<?php

declare(strict_types=1);

/**
 * KVN Construction - Migration Runner
 *
 * Authoritative deployment mechanism for applying the canonical schema
 * (database/schema.sql) and required system seed data
 * (database/seeders/001_defaults.sql).
 *
 * This runner:
 *   - Applies database/schema.sql idempotently (all CREATE TABLE IF NOT EXISTS,
 *     CREATE OR REPLACE VIEW, DROP TRIGGER IF EXISTS + CREATE TRIGGER).
 *   - Optionally applies database/seeders/001_defaults.sql (system seed only).
 *   - Records each applied migration into the schema_migrations table.
 *   - Is idempotent: running it twice never corrupts or duplicates the schema.
 *
 * Usage:
 *   php scripts/run_migrations.php [--seed] [--fresh]
 *
 * Options:
 *   --seed   Also apply the system seed data (roles, permissions, statuses,
 *            settings, local admin account). Safe to run repeatedly.
 *   --fresh  Drop and recreate the database before applying the schema.
 *            ONLY for local/dev environments. NEVER run against production.
 *
 * Exit codes:
 *   0 = success
 *   1 = failure
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$ROOT = dirname(__DIR__);
$args = $argv ?? [];
$doSeed = in_array('--seed', $args, true);
$doFresh = in_array('--fresh', $args, true);

// ---------------------------------------------------------------------------
// Load environment/configuration (constants + helpers)
// ---------------------------------------------------------------------------
require_once $ROOT . '/config/app.php';

// ---------------------------------------------------------------------------
// Connect to MySQL (without selecting a database so we can create it for --fresh)
// ---------------------------------------------------------------------------
$host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
$port = defined('DB_PORT') ? DB_PORT : 3306;
$user = defined('DB_USER') ? DB_USER : 'root';
$pass = defined('DB_PASS') ? DB_PASS : '';
$dbName = defined('DB_NAME') ? DB_NAME : 'kvnc_platform';

try {
    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: Cannot connect to MySQL: " . $e->getMessage() . "\n");
    exit(1);
}

// ---------------------------------------------------------------------------
// --fresh: drop and recreate ONLY the local dev database
// ---------------------------------------------------------------------------
if ($doFresh) {
    echo "[migrate] --fresh: dropping and recreating '{$dbName}' (LOCAL ONLY)\n";
    try {
        $pdo->exec("DROP DATABASE IF EXISTS `{$dbName}`");
        $pdo->exec("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    } catch (Throwable $e) {
        fwrite(STDERR, "ERROR: --fresh step failed: " . $e->getMessage() . "\n");
        exit(1);
    }
}

// Select the database
try {
    $pdo->exec("USE `{$dbName}`");
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: Cannot select database '{$dbName}': " . $e->getMessage() . "\n");
    exit(1);
}

// ---------------------------------------------------------------------------
// Helper: execute a .sql file respecting DELIMITER directives (for triggers)
// ---------------------------------------------------------------------------
function executeSqlFile(PDO $pdo, string $file, string $label): void
{
    if (!is_file($file)) {
        fwrite(STDERR, "ERROR: $label file not found: {$file}\n");
        exit(1);
    }
    $sql = file_get_contents($file);
    if ($sql === false) {
        fwrite(STDERR, "ERROR: Cannot read $label file: {$file}\n");
        exit(1);
    }
    echo "[migrate] Applying {$label}: {$file}\n";

    $statements = splitDelimiter($sql);
    foreach ($statements as $segment) {
        $segment = trim($segment);
        if ($segment === '') {
            continue;
        }
        try {
            $pdo->exec($segment);
        } catch (Throwable $e) {
            fwrite(STDERR, "  FAILED statement: " . substr($segment, 0, 160) . "...\n");
            fwrite(STDERR, "  REASON: " . $e->getMessage() . "\n");
            exit(1);
        }
    }
}

/**
 * Split a SQL script into executable statements, honouring DELIMITER changes
 * used around triggers. Custom-delimiter bodies (e.g. $$ ... END$$) are kept
 * whole so internal semicolons do not break the statement.
 *
 * '$$' terminators are normalised back to ';' for the PDO protocol.
 */
function splitDelimiter(string $sql): array
{
    $lines = preg_split('/\r?\n/', $sql);
    $statements = [];
    $current = '';
    $delimiter = ';';

    foreach ($lines as $line) {
        $trim = trim($line);

        if (preg_match('/^DELIMITER\s+(\S+)/i', $trim, $m)) {
            if (trim($current) !== '') {
                $statements[] = $current;
                $current = '';
            }
            $delimiter = $m[1];
            continue;
        }

        $current .= $line . "\n";

        if ($delimiter === ';') {
            // Ends a top-level statement when a ';' finishes the line.
            if (rtrim($current) !== '' && str_ends_with(rtrim($current), ';')) {
                $statements[] = $current;
                $current = '';
            }
        } else {
            // Custom delimiter (e.g. $$). Body may contain internal semicolons.
            $len = strlen($delimiter);
            $body = rtrim($current);
            if ($body !== '' && substr($body, -$len) === $delimiter) {
                // Replace the custom terminator with ';' and finish the statement.
                $statement = substr($body, 0, -$len) . ';';
                $statements[] = $statement . "\n";
                $current = '';
            }
        }
    }

    if (trim($current) !== '') {
        $statements[] = $current;
    }

    return $statements;
}

// ---------------------------------------------------------------------------
// Migration tracking
// ---------------------------------------------------------------------------
function ensureMigrationsTable(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS schema_migrations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            migration_name VARCHAR(255) NOT NULL,
            applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_schema_migrations_name (migration_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

function isApplied(PDO $pdo, string $name): bool
{
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM schema_migrations WHERE migration_name = :n");
    $stmt->execute([':n' => $name]);
    return (int) $stmt->fetch()['c'] > 0;
}

function recordMigration(PDO $pdo, string $name): void
{
    if (isApplied($pdo, $name)) {
        return;
    }
    $stmt = $pdo->prepare("INSERT INTO schema_migrations (migration_name, applied_at) VALUES (:n, NOW())");
    $stmt->execute([':n' => $name]);
    echo "[migrate] Recorded migration: {$name}\n";
}

// ---------------------------------------------------------------------------
// Apply schema + record
// ---------------------------------------------------------------------------
ensureMigrationsTable($pdo);

const MIGRATION_SCHEMA = '0001_schema';
const MIGRATION_SEED   = '0002_system_seed';

if (!isApplied($pdo, MIGRATION_SCHEMA) || $doFresh) {
    executeSqlFile($pdo, $ROOT . '/database/schema.sql', 'schema');
    recordMigration($pdo, MIGRATION_SCHEMA);
} else {
    echo "[migrate] Schema already applied. Skipping (idempotent).\n";
}

if ($doSeed) {
    executeSqlFile($pdo, $ROOT . '/database/seeders/001_defaults.sql', 'system seed');
    recordMigration($pdo, MIGRATION_SEED);
}

// ---------------------------------------------------------------------------
// Verify OTP triggers (informational)
// ---------------------------------------------------------------------------
try {
    $q = $pdo->query(
        "SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = " . $pdo->quote($dbName)
    );
    $triggers = $q ? $q->fetchAll(PDO::FETCH_COLUMN) : [];
    echo "[migrate] Database triggers present (" . count($triggers) . "): " . implode(', ', $triggers) . "\n";
} catch (Throwable $e) {
    echo "[migrate] Could not inspect triggers: " . $e->getMessage() . "\n";
}

echo "[migrate] Done. schema_migrations records:\n";
$rows = $pdo->query("SELECT migration_name, applied_at FROM schema_migrations ORDER BY id")->fetchAll();
foreach ($rows as $r) {
    echo "  - {$r['migration_name']} @ {$r['applied_at']}\n";
}

exit(0);
