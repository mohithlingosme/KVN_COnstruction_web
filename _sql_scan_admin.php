<?php
/**
 * Targeted SQL scan on admin pages (NOT ALLOWED zone).
 * Detects real SQL patterns: raw DB object usage, SQL keywords in code context.
 */
$root = __DIR__;
$adminDir = $root . '/public/admin';

// Real SQL patterns: uppercase SQL keywords used in PHP code strings
$patterns = [
    'conn_var'   => '/\$conn\b/',
    'db_var'     => '/\$db\b/',
    'mysqli'     => '/\bmysqli\b/i',
    'pdo_class'  => '/\bPDO\b/i',
    'prepare'    => '/->prepare\s*\(/',
    'query_call' => '/->query\s*\(/',
    'bind_param' => '/->bind_param\s*\(/',
    'fetch_assoc'=> '/->fetch_assoc\s*\(/',
    'fetch_all'  => '/->fetch_all\s*\(/',
    'num_rows'   => '/->num_rows\s*\(/',
    'SELECT'     => '/SELECT\s+[A-Z_*]/i',
    'INSERT'     => '/INSERT\s+INTO/i',
    'UPDATE'     => '/UPDATE\s+[A-Z_]+/i',
    'DELETE'     => '/DELETE\s+FROM/i',
    'CREATE_TBL' => '/CREATE\s+TABLE/i',
    'ALTER_TBL'  => '/ALTER\s+TABLE/i',
    'DROP_TBL'   => '/DROP\s+TABLE/i',
];

function walkFiles($dir, &$files) {
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) walkFiles($path, $files);
        elseif (substr($item, -4) === '.php') $files[] = $path;
    }
}

$files = [];
walkFiles($adminDir, $files);
sort($files);

echo "Scanning " . count($files) . " admin files\n\n";
$violations = [];
foreach ($files as $f) {
    $content = file_get_contents($f);
    $rel = str_replace($root . '/', '', str_replace('\\', '/', $f));
    $hits = [];
    foreach ($patterns as $pname => $pattern) {
        if (preg_match_all($pattern, $content, $m)) {
            $hits[$pname] = count($m[0]);
        }
    }
    if (!empty($hits)) {
        $violations[$rel] = $hits;
        echo "VIOLATION: {$rel}\n";
        foreach ($hits as $h => $c) echo "    {$h}: {$c}\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total files with SQL: " . count($violations) . "\n";
if (empty($violations)) echo "ALL ADMIN PAGES ARE SQL-FREE\n";
