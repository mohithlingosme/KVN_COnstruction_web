<?php

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "kvn_construction";

try {
    $conn = new mysqli(
        $host,
        $user,
        $pass,
        $db
    );
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log('Database Error: ' . $e->getMessage());
    die('Database connection error. Please contact administrator.');
}