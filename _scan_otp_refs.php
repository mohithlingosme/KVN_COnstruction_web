<?php
/**
 * Precise scan for every file (production only) referencing 'OtpService'
 * in any case, showing the matching lines.
 */
$root = __DIR__;
$excludeDirs = ['vendor', 'node_modules', '.git', 'audit-report', 'uploads', 'storage', 'reports', 'database', 'Summari_Ai_context'];
$excludeFiles = ['_dep_otp.php', '_scan_otp_refs.php', '_dep_graph.php', '_audit_php_architecture.php'];

$files = [];
function walk2($dir, $root, $excludeDirs, $excludeFiles, &$files) {
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            if (in_array($item, $excludeDirs)) continue;
            walk2($path, $root, $excludeDirs, $excludeFiles, $files);
        } elseif (substr($item, -4) === '.php') {
            if (in_array($item, $excludeFiles)) continue;
            $files[] = str_replace('\\', '/', substr($path, strlen($root) + 1));
        }
    }
}
walk2($root, $root, $excludeDirs, $excludeFiles, $files);
sort($files);

echo "=== FILES REFERENCING 'OtpService' (case-insensitive) ===\n\n";
$hits = 0;
foreach ($files as $f) {
    $content = @file_get_contents($root . '/' . $f);
    if ($content === false) continue;
    if (preg_match_all('/OtpService/i', $content, $m, PREG_OFFSET_CAPTURE)) {
        echo "--- {$f} ---\n";
        $lines = explode("\n", $content);
        $shown = [];
        foreach ($m[0] as $hit) {
            $pos = $hit[1];
            $lineNo = substr_count(substr($content, 0, $pos), "\n") + 1;
            if (isset($shown[$lineNo])) continue;
            $shown[$lineNo] = true;
            $trimmed = trim($lines[$lineNo - 1]);
            echo "  L{$lineNo}: {$trimmed}\n";
            $hits++;
        }
        echo "\n";
    }
}
echo "Total hit-lines: {$hits}\n";
