<?php
/**
 * Admin SQL Scanner - Inventory remaining SQL patterns in public/admin.
 * Usage: php _scan_admin_sql.php
 */
$root = __DIR__ . '/public/admin';
$patterns = [
    '$conn'         => 'conn var',
    '$db'           => 'db var',
    '->query('      => 'query()',
    '->prepare('    => 'prepare()',
    'bind_param('   => 'bind_param()',
    'fetch_assoc('  => 'fetch_assoc()',
    'fetch_all('    => 'fetch_all()',
    'num_rows'      => 'num_rows',
    'new PDO('      => 'new PDO',
    'mysqli'        => 'mysqli',
    'CREATE TABLE'  => 'CREATE TABLE',
];

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$files = [];
foreach ($it as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $files[] = $file->getPathname();
    }
}
sort($files);

$report = [];
$totalStatements = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    $hits = [];
    foreach ($patterns as $needle => $label) {
        $count = substr_count($content, $needle);
        if ($count > 0) {
            $hits[] = "{$label}({$count})";
            $totalStatements += $count;
        }
    }
    if (!empty($hits)) {
        $rel = str_replace(__DIR__ . '/', '', $file);
        $report[] = $rel . ' => ' . implode(', ', $hits);
    }
}

echo "=== ADMIN SQL INVENTORY ===\n\n";
foreach ($report as $line) {
    echo $line . "\n";
}
echo "\nTotal files with SQL: " . count($report) . "\n";
echo "Total SQL statements: {$totalStatements}\n";
echo "\nFiles WITHOUT SQL (migrated):\n";
$clean = array_diff($files, array_map(function($r) { return __DIR__ . '/' . explode(' => ', $r)[0]; }, $report));
foreach ($clean as $f) {
    echo '  ✅ ' . str_replace(__DIR__ . '/', '', $f) . "\n";
}

