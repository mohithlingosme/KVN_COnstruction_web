<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

function connect() {
    static $pdo = null;
    if ($pdo) return $pdo;
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=kvnc_platform;charset=utf8mb4', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (Throwable $e) {
        return null;
    }
}

$pdo = connect();
if (!$pdo) {
    echo "DB CONNECT: FAIL (kvnc_platform)\n";
    // try no-db connection
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "DB CONNECT (no db): OK\n";
        $dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
        echo "DATABASES: " . implode(', ', $dbs) . "\n";
        return;
    } catch (Throwable $e) {
        echo "DB CONNECT (no db): FAIL - " . $e->getMessage() . "\n";
        return;
    }
}

echo "DB CONNECT: OK\n";

// triggers
echo "--- TRIGGERS ---\n";
try {
    $triggers = $pdo->query("SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA='kvnc_platform'")->fetchAll();
    if (!$triggers) echo "(none)\n";
    foreach ($triggers as $t) {
        echo "{$t['TRIGGER_NAME']} ({$t['EVENT_MANIPULATION']} on {$t['EVENT_OBJECT_TABLE']})\n";
    }
} catch (Throwable $e) { echo "ERR: " . $e->getMessage() . "\n"; }

// schema_migrations
echo "--- schema_migrations ---\n";
try {
    $m = $pdo->query("SELECT migration_name, applied_at FROM schema_migrations ORDER BY id")->fetchAll();
    if (!$m) echo "(zero rows)\n";
    foreach ($m as $row) echo $row['migration_name'] . " @ " . $row['applied_at'] . "\n";
} catch (Throwable $e) { echo "ERR: " . $e->getMessage() . "\n"; }

// key tables row count
echo "--- ROW COUNTS ---\n";
$tables = ['users','roles','permissions','user_otps','otps','leads','blogs','services','portfolio','testimonials','packages','construction_packages','faqs','estimator_packages','projects','quotations'];
foreach ($tables as $t) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) AS c FROM `$t`");
        echo "$t = " . $stmt->fetch()['c'] . "\n";
    } catch (Throwable $e) {
        echo "$t = ERR: " . $e->getMessage() . "\n";
    }
}

// does otps view exist
echo "--- otps view/table ---\n";
try {
    $r = $pdo->query("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA='kvnc_platform' AND TABLE_NAME IN ('otps','user_otps')")->fetchAll();
    foreach ($r as $row) echo "{$row['TABLE_NAME']} = {$row['TABLE_TYPE']}\n";
} catch (Throwable $e) { echo "ERR: " . $e->getMessage() . "\n"; }

