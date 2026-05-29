<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

$backupDir = STORAGE_PATH . '/backups';
ensure_directory($backupDir);

$file = $backupDir . '/backup-' . date('Ymd-His') . '.sql';
$command = sprintf(
    'mysqldump --host=%s --user=%s %s %s > %s',
    escapeshellarg(DB_HOST),
    escapeshellarg(DB_USER),
    DB_PASS !== '' ? '--password=' . escapeshellarg(DB_PASS) : '',
    escapeshellarg(DB_NAME),
    escapeshellarg($file)
);

exec($command, $output, $exitCode);

if ($exitCode !== 0) {
    fwrite(STDERR, "Backup failed.\n");
    exit(1);
}

echo "Backup written to $file\n";
