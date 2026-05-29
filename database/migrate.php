<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

ensureMigrationTable($conn);

$migrationDir = __DIR__ . '/migrations';
$files = glob($migrationDir . '/*.sql') ?: [];
sort($files, SORT_STRING);

$applied = $conn->query('SELECT migration_name FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN) ?: [];

foreach ($files as $file) {
    $name = basename($file);

    if (in_array($name, $applied, true)) {
        continue;
    }

    $sql = trim((string) file_get_contents($file));

    if ($sql === '') {
        continue;
    }

    echo 'Applying ' . $name . PHP_EOL;

    $conn->beginTransaction();

    try {
        $conn->exec($sql);
        $stmt = $conn->prepare('INSERT INTO schema_migrations (migration_name, applied_at) VALUES (:migration_name, NOW())');
        $stmt->execute([':migration_name' => $name]);
        $conn->commit();
    } catch (Throwable $exception) {
        $conn->rollBack();
        fwrite(STDERR, 'Failed ' . $name . ': ' . $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}

echo 'Migrations complete.' . PHP_EOL;

function ensureMigrationTable(PDO $conn): void
{
    $conn->exec(
        'CREATE TABLE IF NOT EXISTS schema_migrations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) NOT NULL UNIQUE,
            applied_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}
