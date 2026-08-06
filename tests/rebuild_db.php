<?php

declare(strict_types=1);

require __DIR__ . '/smoke/bootstrap.php';

// Connect without selecting a database
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';

$dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "Dropping database if exists...\n";
try {
    $pdo->exec('DROP DATABASE IF EXISTS kvnc_platform');
} catch (Throwable $e) {
    echo "  Could not drop: " . $e->getMessage() . "\n";
}

echo "Creating database...\n";
$pdo->exec('CREATE DATABASE kvnc_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo->exec('USE kvnc_platform');

echo "Importing schema from database/schema.sql...\n";
$schema = file_get_contents(ROOT_PATH . '/database/schema.sql');
if ($schema === false) {
    fwrite(STDERR, "Cannot read database/schema.sql\n");
    exit(1);
}

// Remove CREATE DATABASE and USE statements since we already created the database
$schema = preg_replace('/CREATE DATABASE[^;]+;/i', '', $schema);
$schema = preg_replace('/USE\s+[^;]+;/i', '', $schema);

// Execute the schema as a multi-query
try {
    $pdo->exec($schema);
    echo "Schema imported successfully.\n";
} catch (Throwable $e) {
    // Try splitting into individual statements for better error reporting
    echo "Multi-statement failed: " . $e->getMessage() . "\n";
    echo "Attempting statement by statement...\n";
    
    // Simple statement splitter that respects DELIMITER
    $stmt = $pdo;
    $statements = preg_split('/;\s*(\r?\n|$)/', $schema, -1, PREG_SPLIT_NO_EMPTY);
    $count = 0;
    foreach ($statements as $s) {
        $s = trim($s);
        if (empty($s)) continue;
        try {
            $stmt->exec($s);
            $count++;
        } catch (Throwable $ex) {
            echo "  FAILED statement #" . ($count + 1) . ": " . substr($s, 0, 100) . "... - " . $ex->getMessage() . "\n";
        }
    }
    echo "Executed {$count} statements.\n";
}

echo "Importing seed data from database/seeders/001_defaults.sql...\n";
$seeder = file_get_contents(ROOT_PATH . '/database/seeders/001_defaults.sql');
if ($seeder !== false) {
    try {
        $pdo->exec($seeder);
        echo "Seed data imported.\n";
    } catch (Throwable $e) {
        echo "Seeder failed: " . $e->getMessage() . "\n";
    }
}

echo "Database rebuild complete.\n";