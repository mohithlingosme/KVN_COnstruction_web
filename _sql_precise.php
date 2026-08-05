<?php
/**
 * Precise SQL detector.
 * Detects:
 *  1. Direct DB object calls (->prepare, ->query, ->fetch, bind_param, etc.)
 *  2. Real SQL statements inside PHP string literals (quoted).
 *  3. $conn/$db/$mysqli/$pdo variables.
 * Ignores HTML <select> tags and method names like ->update().
 */
$root = __DIR__;
$excludeDirs = ['vendor', 'node_modules', '.git', 'audit-report', 'uploads', 'storage', 'database', 'reports', 'tests'];
$allFiles = [];

function walk($dir, $root, $excludeDirs, &$files) {
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            if (in_array($item, $excludeDirs)) continue;
            walk($path, $root, $excludeDirs, $files);
        } elseif (substr($item, -4) === '.php') {
            $files[] = str_replace('\\', '/', substr($path, strlen($root) + 1));
        }
    }
}
walk($root, $root, $excludeDirs, $allFiles);

// DB object usage patterns
$dbObjPatterns = [
    'conn_var'   => '/\$conn\b/',
    'db_var'     => '/\$db\b/',
    'mysqli'     => '/\bmysqli(_|->|\s)/i',
    'pdo_class'  => '/\bnew\s+PDO\b|\bPDO::/i',
    'prepare'    => '/->prepare\s*\(/',
    'query_call' => '/->query\s*\(/',
    'bind_param' => '/->bind_param\s*\(/',
    'fetch_assoc'=> '/->fetch_assoc\s*\(/',
    'fetch_all'  => '/->fetch_all\s*\(/',
    'num_rows'   => '/->num_rows\s*\(/',
    'exec_call'  => '/->exec\s*\(/',
];

// Real SQL in string literals: SQL keyword followed by table-like ident, inside quotes
// Matches: "SELECT ... FROM", 'INSERT INTO', "UPDATE ... SET", "DELETE FROM"
$sqlInString = [
    'SELECT' => '/["\']\s*SELECT\s+.{1,40}?\s+FROM\b/i',
    'INSERT' => '/["\']\s*INSERT\s+INTO\b/i',
    'UPDATE' => '/["\']\s*UPDATE\s+[A-Za-z_][A-Za-z0-9_]*\s+SET\b/i',
    'DELETE' => '/["\']\s*DELETE\s+FROM\b/i',
    'CREATE_TBL' => '/["\']\s*CREATE\s+TABLE\b/i',
    'ALTER_TBL'  => '/["\']\s*ALTER\s+TABLE\b/i',
    'DROP_TBL'   => '/["\']\s*DROP\s+TABLE\b/i',
];

$allowedPrefixes = ['app/repositories/', 'core/Repository.php', 'app/Core/', 'config/database.php', 'bootstrap/'];

function isAllowed($rel, $allowedPrefixes) {
    foreach ($allowedPrefixes as $p) {
        if (strpos($rel, $p) === 0) return true;
    }
    return false;
}

echo "Scanning " . count($allFiles) . " files\n\n";
$violations = [];
foreach ($allFiles as $rel) {
    $content = file_get_contents($root . '/' . $rel);
    if (!$content) continue;
    $allowed = isAllowed($rel, $allowedPrefixes);
    if ($allowed) continue; // skip repositories/db layer entirely

    $hits = [];
    foreach ($dbObjPatterns as $pname => $pattern) {
        if (preg_match_all($pattern, $content, $m)) $hits[$pname] = count($m[0]);
    }
    foreach ($sqlInString as $pname => $pattern) {
        if (preg_match_all($pattern, $content, $m)) $hits[$pname] = count($m[0]);
    }
    if (!empty($hits)) {
        $violations[$rel] = $hits;
        echo "VIOLATION: {$rel}\n";
        foreach ($hits as $h => $c) echo "    {$h}: {$c}\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Files with real SQL (outside repositories): " . count($violations) . "\n";
if (empty($violations)) echo "ZERO SQL OUTSIDE REPOSITORIES\n";
