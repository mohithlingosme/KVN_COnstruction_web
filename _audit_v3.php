<?php
/**
 * KVN PHP Architecture Verification v3
 * - Namespace consistency in repositories
 * - Duplicate repository/service methods
 * - Legacy model references
 * - Raw SQL/db patterns in remaining admin pages (case-sensitive)
 */
error_reporting(E_ERROR | E_PARSE);
$root = __DIR__;

echo "=== 1. REPOSITORY NAMESPACES ===\n";
$repoDir = $root . '/app/repositories';
foreach (scandir($repoDir) as $f) {
    if (substr($f, -4) !== '.php') continue;
    $content = file_get_contents("$repoDir/$f");
    preg_match('/\bnamespace\s+([^;]+);/', $content, $m);
    preg_match('/\bclass\s+(\w+)/', $content, $m2);
    $ns = $m[1] ?? '(none/global)';
    $cls = $m2[1] ?? '?';
    echo "  {$f} => namespace: {$ns}, class: {$cls}\n";
}

echo "\n=== 2. DUPLICATE METHODS ===\n";
function collectMethods($dir, $label) {
    $methods = [];
    foreach (scandir($dir) as $f) {
        if (substr($f, -4) !== '.php') continue;
        $content = file_get_contents("$dir/$f");
        preg_match_all('/public\s+function\s+(\w+)\s*\(/', $content, $m);
        foreach ($m[1] as $name) {
            $methods[$name][] = $f;
        }
    }
    echo "  [$label] Duplicate method names across files:\n";
    $dupFound = false;
    foreach ($methods as $name => $files) {
        $unique = array_unique($files);
        if (count($unique) > 1) {
            $dupFound = true;
            echo "    {$name}: " . implode(', ', $unique) . "\n";
        }
    }
    if (!$dupFound) echo "    (none)\n";
}
collectMethods($root . '/app/repositories', 'Repositories');
collectMethods($root . '/app/services', 'Services');

echo "\n=== 3. LEGACY MODEL REFERENCES ===\n";
$legacyRefs = [];
function scanRefs($dir, $root, &$results) {
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            if (in_array($item, ['vendor', 'node_modules', '.git', 'audit-report', 'uploads'])) continue;
            scanRefs($path, $root, $results);
        } elseif (substr($item, -4) === '.php') {
            $content = file_get_contents($path);
            $rel = str_replace('\\', '/', substr($path, strlen($root) + 1));
            // Look for generic model usage patterns like new XxxModel or App\Models
            if (preg_match('/new\s+\\?\\w+Model\b|App\\\\Models\\\\|extends\s+\\?\\w+Model\b/', $content, $m)) {
                $results[] = [$rel, $m[0]];
            }
        }
    }
}
scanRefs($root, $root, $legacyRefs);
if (empty($legacyRefs)) {
    echo "  (no legacy model instantiations found)\n";
} else {
    foreach ($legacyRefs as [$f, $pat]) echo "  {$f}: {$pat}\n";
}

echo "\n=== 4. ADMIN PAGES DB/SQL PATTERN SCAN (case-sensitive) ===\n";
$violations = [];
$patterns = [
    '/\$conn\s*=/', '/\$mysqli\s*=/', '/new\s+mysqli\b/',
    '/->prepare\s*\(/', '/->query\s*\(/', '/->exec\s*\(/',
    '/->bind_param\s*\(/', '/->fetch_assoc\s*\(/', '/->fetch_all\s*\(/', '/->num_rows\s*\(/',
    '/\bSELECT\b[^\n]*\bFROM\b/i', '/\bINSERT\s+INTO\b/i', '/\bUPDATE\b[^\n]*\bSET\b/i',
    '/\bDELETE\s+FROM\b/i', '/\bCREATE\s+TABLE\b/i', '/\bALTER\s+TABLE\b/i', '/\bDROP\s+TABLE\b/i',
];
$adminDir = $root . '/public/admin';
foreach (scandir($adminDir) as $module) {
    if (substr($module, -4) !== '.php' && !is_dir("$adminDir/$module")) continue;
    $path = "$adminDir/$module";
    if (is_dir($path)) {
        foreach (scandir($path) as $file) {
            if (substr($file, -4) !== '.php') continue;
            $fp = "$path/$file";
            $content = file_get_contents($fp);
            $rel = "public/admin/$module/$file";
            $hits = [];
            foreach ($patterns as $p) {
                if (preg_match_all($p, $content, $m)) $hits[] = $p . '(' . count($m[0]) . ')';
            }
            if (!empty($hits)) $violations[] = [$rel, $hits];
        }
        continue;
    }
    if (substr($module, -4) !== '.php') continue;
    $content = file_get_contents($path);
    $hits = [];
    foreach ($patterns as $p) {
        if (preg_match_all($p, $content, $m)) $hits[] = $p . '(' . count($m[0]) . ')';
    }
    if (!empty($hits)) $violations[] = ["public/admin/$module", $hits];
}
if (empty($violations)) {
    echo "  ZERO raw SQL/PDO patterns in all admin pages\n";
} else {
    foreach ($violations as [$f, $hits]) {
        echo "  {$f}:\n";
        foreach ($hits as $h) echo "      {$h}\n";
    }
}

echo "\n=== 5. ROOT/OTHER PUBLIC PAGES CHECK ===\n";
// Scan public root pages (not admin/client)
$rootPatterns = ['/\$conn\s*=/', '/->prepare\s*\(/', '/->query\s*\(/', '/new\s+mysqli\b/', '/->bind_param\s*\(/'];
$publicDir = $root . '/public';
$issues = [];
foreach (scandir($publicDir) as $f) {
    if (substr($f, -4) !== '.php') continue;
    $content = file_get_contents("$publicDir/$f");
    $hits = [];
    foreach ($rootPatterns as $p) if (preg_match_all($p, $content, $m)) $hits[] = $p . '(' . count($m[0]) . ')';
    if (!empty($hits)) $issues[] = ["public/$f", $hits];
}
if (empty($issues)) echo "  ZERO raw DB patterns in public root pages\n";
else foreach ($issues as [$f, $hits]) echo "  {$f}: " . implode(', ', $hits) . "\n";

