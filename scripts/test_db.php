<?php
require_once 'c:\xampp\htdocs\KVN_Construction\config\database.php';
$db = new Database();
$conn = $db->connect();
try {
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $st = $conn->query("SHOW TABLE STATUS LIKE '$table'");
        $res = $st->fetch(PDO::FETCH_ASSOC);
        if ($res['Engine'] === null) {
            echo "Error in table $table: " . $res['Comment'] . "\n";
        } else {
            echo "Table $table is OK.\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
