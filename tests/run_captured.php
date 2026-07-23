<?php
// Run tests and capture output to a file
ob_start();
require __DIR__ . '/run.php';
$output = ob_get_clean();
file_put_contents(__DIR__ . '/test_results.txt', $output);
print $output;
