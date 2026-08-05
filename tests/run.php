<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

// Point CONFIG_PATH at Fakes so AdminController does not try a real MySQL connection
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', __DIR__ . '/Fakes');
}

// Load AdminController for admin tests
require_once ROOT_PATH . '/app/controllers/admin/AdminController.php';


$testFiles = glob(__DIR__ . '/*Test.php');

if (!$testFiles) {
    fwrite(STDERR, "No tests found in " . __DIR__ . "\n");
    exit(1);
}

$classNames = [];
foreach ($testFiles as $file) {
    require_once $file;
}

// Very small test runner
$failures = 0;
$total = 0;

foreach (get_declared_classes() as $class) {
    if (str_ends_with($class, 'Test')) {
        $obj = new $class();
        foreach (get_class_methods($class) as $method) {
            if (str_starts_with($method, 'test')) {
                $total++;
                try {
                    $obj->$method();
                    echo "[PASS] {$class}::{$method}\n";
                } catch (Throwable $e) {
                    $failures++;
                    echo "[FAIL] {$class}::{$method} - {$e->getMessage()}\n";
                }
            }
        }
    }
}

// Collect buffered application output before emitting the test report. This keeps
// accidental output out of API assertions without swallowing the runner summary.
$bufferedOutput = '';
while (ob_get_level() > 0) {
    $bufferedOutput = ob_get_clean() . $bufferedOutput;
}

fwrite(STDOUT, $bufferedOutput);
fwrite(STDOUT, "Ran {$total} tests, failures: {$failures}\n");
exit($failures > 0 ? 1 : 0);
