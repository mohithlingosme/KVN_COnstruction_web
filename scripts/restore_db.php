<?php
$host = 'localhost';
$username = 'root';
$password = '';
$db_name = 'kvnc_platform';
$sql_file = 'c:\xampp\htdocs\KVN_Construction\database\migration\Kvnc_platform.sql';

try {
    // Connect without database to drop and recreate
    $conn = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Dropping database $db_name if exists...\n";
    $conn->exec("DROP DATABASE IF EXISTS $db_name");
    
    echo "Creating database $db_name...\n";
    $conn->exec("CREATE DATABASE $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Connect to the new database
    $conn->exec("USE $db_name");
    
    echo "Importing SQL dump from $sql_file...\n";
    $sql = file_get_contents($sql_file);
    if ($sql === false) {
        throw new Exception("Failed to read SQL file.");
    }
    
    // Execute SQL file
    $conn->exec($sql);
    
    echo "Database $db_name restored successfully.\n";
} catch (Exception $e) {
    echo "Error restoring database: " . $e->getMessage() . "\n";
    exit(1);
}
?>
