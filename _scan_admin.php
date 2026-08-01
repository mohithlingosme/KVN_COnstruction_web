<?php
// Broad scanner for admin SQL usage
$dir = 'c:/xampp/htdocs/KVN_Construction/public/admin';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$results = [];
$patterns = [
    '/\$conn\s*->/' => 'conn->',
    '/\$db\s*->/' => 'db->',
    '/\$pdo\s*->/' => 'pdo->',
    '/\$database\s*->/' => 'database->',
    '/new PDO/' => 'new PDO',
    '/mysqli/' => 'mysqli',
    '/->prepare\(/' => '->prepare(',
    '/->query\(/' => '->query(',
    '/->exec\(/' => '->exec(',
    '/fetch_assoc/' => 'fetch_assoc',
    '/fetch_all/' => 'fetch_all',
    '/num_rows/' => 'num_rows',
    '/bind_param/' => 'bind_param',
    '/CREATE TABLE/' => 'CREATE TABLE',
    '/SELECT .* FROM/' => 'SELECT',
    '/INSERT INTO/' => 'INSERT INTO',
    '/UPDATE .* SET/' => 'UPDATE',
    '/DELETE FROM/' => 'DELETE FROM',
];
foreach ($it as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $c = file_get_contents($f->getPathname());
        $count = 0;
        $found = [];
        foreach ($patterns as $pat => $label) {
            $n = preg_match_all($pat, $c);
            if ($n > 0) { $count += $n; $found[] = $label . 'x' . $n; }
        }
        if ($count > 0) {
            $results[] = str_pad($count, 4, ' ', STR_PAD_LEFT) . ' ' . str_replace($dir . '/', '', $f->getPathname()) . '  [' . implode(', ', $found) . ']';
        }
    }
}
sort($results);
echo "SQL-Usage File Inventory (count / file) [patterns]\n";
echo "==================================================\n";
echo implode("\n", $results);
echo "\n\nTotal files with SQL usage: " . count($results) . "\n";

