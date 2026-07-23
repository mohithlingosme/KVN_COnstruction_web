<?php
// Redirect ALL output to a file
$debugFile = __DIR__ . '/test_run_output.txt';
file_put_contents($debugFile, "=== START ===\n", FILE_APPEND);

try {
    require __DIR__ . '/run.php';
} catch (Throwable $e) {
    file_put_contents($debugFile, "CATCH: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
}
