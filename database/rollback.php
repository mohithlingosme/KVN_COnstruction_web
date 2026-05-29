<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

$steps = isset($argv[1]) ? max(1, (int) $argv[1]) : 1;

$stmt = $conn->prepare('SELECT migration_name FROM schema_migrations ORDER BY id DESC LIMIT :limit');
$stmt->bindValue(':limit', $steps, PDO::PARAM_INT);
$stmt->execute();
$migrations = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

foreach ($migrations as $migration) {
    $rollbackFile = __DIR__ . '/rollbacks/' . $migration;

    if (!is_file($rollbackFile)) {
        fwrite(STDERR, 'Missing rollback for ' . $migration . PHP_EOL);
        exit(1);
    }

    $sql = trim((string) file_get_contents($rollbackFile));

    echo 'Rolling back ' . $migration . PHP_EOL;

    $conn->beginTransaction();

    try {
        if ($sql !== '') {
            $conn->exec($sql);
        }

        $delete = $conn->prepare('DELETE FROM schema_migrations WHERE migration_name = :migration_name');
        $delete->execute([':migration_name' => $migration]);
        $conn->commit();
    } catch (Throwable $exception) {
        $conn->rollBack();
        fwrite(STDERR, 'Rollback failed for ' . $migration . ': ' . $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}

echo 'Rollback complete.' . PHP_EOL;
