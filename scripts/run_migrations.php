<?php

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->connect();

$migrationsDir = __DIR__ . '/../database/migration';
$files = scandir($migrationsDir);

$sqlFiles = array_filter($files, function($file) {
    return pathinfo($file, PATHINFO_EXTENSION) === 'sql';
});

sort($sqlFiles);

echo "Starting migrations...\n";

foreach ($sqlFiles as $file) {
    $filePath = $migrationsDir . '/' . $file;
    echo "Running $file...\n";
    $sql = file_get_contents($filePath);
    
    try {
        $conn->exec($sql);
        echo "Successfully executed $file\n";
    } catch (PDOException $e) {
        echo "Error in $file: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "All migrations completed successfully.\n";
