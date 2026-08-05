<?php
/**
 * Project-wide PHP lint via php -l.
 */
$root = __DIR__;
$excludeDirs = ['vendor', 'node_modules', '.git', 'audit-report', 'uploads', 'storage'];
$files = [];

function walk($dir, $root, $excludeDirs, &$files) {
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            if (in_array($item, $excludeDirs)) continue;
            walk($path, $root, $excludeDirs, $files);
        } elseif (substr($item, -4) === '.php') {
            $files[] = $path;
        }
    }
}
walk($root, $root, $excludeDirs, $files);

echo "Total PHP files to lint: " . count($files) . "\n";
$fail = 0;
foreach ($files as $f) {
    $out = shell_exec('php -l ' . escapeshellarg($f));
    if (stripos($out, 'No syntax errors') === false) {
        $fail++;
        echo "FAIL: {$f}\n{$out}\n";
    }
}
echo "\n=== LINT RESULT ===\n";
echo "Failed: {$fail} / " . count($files) . "\n";
if ($fail === 0) echo "ALL PHP FILES PASS LINT\n";
