<?php
// Minimal test runner that writes output to a file

// Clear any existing output buffers
while (ob_get_level()) ob_end_clean();

// Start a new buffer  
ob_start();

require __DIR__ . '/run.php';

// Get whatever output was produced
$output = ob_get_clean();

// Write to file for inspection
file_put_contents(__DIR__ . '/test_results.txt', $output ?: "NO OUTPUT PRODUCED");

// Also echo to console
print $output ?: "NO OUTPUT PRODUCED";
