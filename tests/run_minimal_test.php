<?php
// Absolute minimal test to see if test framework works
require_once __DIR__ . '/bootstrap.php';

define('CONFIG_PATH', __DIR__ . '/Fakes');

// Test 1: Create a PDO and run a query
$pdo = new PDO('sqlite::memory:');
$pdo->exec("CREATE TABLE test_table (id INTEGER, name TEXT)");
$pdo->exec("INSERT INTO test_table VALUES (1, 'test')");
$stmt = $pdo->query("SELECT * FROM test_table");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$results = [];
$results[] = 'PDO test: ' . ($row['name'] === 'test' ? 'PASS' : 'FAIL');

// Test 2: Load AuthController
require_once ROOT_PATH . '/app/controllers/auth/AuthController.php';
$controller = new AuthController($pdo);
$results[] = 'AuthController loaded: PASS';

// Test 3: sendLoginOtp with empty phone
$res = $controller->sendLoginOtp('');
$results[] = 'Empty phone test: ' . ($res['status'] === false ? 'PASS' : 'FAIL');

// Test 4: Load AdminController  
require_once ROOT_PATH . '/app/controllers/admin/AdminController.php';
$results[] = 'AdminController loaded: PASS';

// Output results - config/app.php started ob_start(); need to collect it first
$captured = '';
while (ob_get_level() > 0) {
    $buf = ob_get_clean();
    if ($buf !== false) {
        $captured = $buf . $captured;
    }
}
file_put_contents(__DIR__ . '/debug_output.txt', $captured);
foreach ($results as $r) {
    file_put_contents(__DIR__ . '/debug_output.txt', $r . "\n", FILE_APPEND);
}
file_put_contents(__DIR__ . '/debug_output.txt', "All " . count($results) . " tests completed.\n", FILE_APPEND);
print $captured;
foreach ($results as $r) {
    print $r . "\n";
}
print "All " . count($results) . " tests completed.\n";
