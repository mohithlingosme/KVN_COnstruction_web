<?php
$host = '127.0.0.1';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn->exec("DROP DATABASE IF EXISTS kvnc_db");
    $conn->exec("CREATE DATABASE kvnc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database kvnc_db recreated successfully.\n";
} catch (PDOException $e) {
    echo "Error recreating database: " . $e->getMessage() . "\n";
    exit(1);
}
