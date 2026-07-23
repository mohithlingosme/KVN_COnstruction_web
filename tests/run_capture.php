<?php
// Capture and display all output from run.php
ob_start();
$exitCode = 0;
try {
    require __DIR__ . '/run.php';
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
$output = ob_get_clean();
file_put_contents(__DIR__ . '/test_captured.txt', $output);
print $output;
