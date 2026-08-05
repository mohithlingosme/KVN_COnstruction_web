<?php
/**
 * KVN PHP Architecture Audit v2
 * Case-sensitive SQL keyword detection to eliminate false positives.
 */
error_reporting(E_ERROR | E_PARSE);

$root = __DIR__;
$excludeDirs = ['vendor', 'node_modules', 'storage', 'uploads', 'bootstrap/cache', '.git', 'audit-report'];
$excludeFiles = ['_audit_v2.php', '_audit_php_architecture.php'];

// Strong signals: variable / object-level DB usage (never in pages/helpers/services)
$strongPatterns = [
    'conn_var'    => '/\$conn\s*=/',
    'db_var'      => '/\$db\s*=/',
    'pdo_var'     => '/\$pdo\s*=/',
    'mysqli'      => '/new\s+mysqli\b|mysqli_/i',
    'prepare'     => '/[a-zA-Z0-9_>\]]->\s*prepare\s*\(/',
    'query'       => '/[a-zA-Z0-9_>\]]->\s*query\s*\(/',
    'bind_param'  => '/->\s*bind_param\s*\(/',
    'fetch_assoc' => '/->\s*fetch_assoc\s*\(/',
    'fetch_all'   => '/->\s*fetch_all\s*\(/',
    'num_rows'    => '/->\s*num_rows\s*\(/',
    'exec'        => '/[a-zA-Z0-9_>\]]->\s*exec\s*\(/',
];

// SQL statement keywords (uppercase only = actual SQL)
$sqlPatterns = [
    'SELECT'     => '/\bSELECT\b[^\n]*\bFROM\b/i',
    'INSERT'     => '/\bINSERT\s+INTO\b/i',
    'UPDATE'     => '/\bUPDATE\b[^\n]*\bSET\b/i',
    'DELETE'     => '/\bDELETE\s+FROM\b/i',
    'CREATE_TBL' => '/\bCREATE\s+TABLE\b/i',
    'ALTER_TBL'  => '/\bALTER\s+TABLE\b/i',
    'DROP_TBL'   => '/\bDROP\s+TABLE\b/i',
];

function kvn_walk($dir, $root, $excludeDirs, $excludeFiles, &$bucket) {
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            if (in_array($item, $excludeDirs)) continue;
            kvn_walk($path, $root, $excludeDirs, $excludeFiles, $bucket);
        } elseif (is_file($path) && substr($item, -4) === '.php') {
            if (in_array($item, $excludeFiles)) continue;
            $bucket[] = str_replace('\\', '/', substr($path, strlen($root) + 1));
        }
    }
}

function kvn_zone($rel) {
    if (strpos($rel, 'app/repositories/') === 0) return 'repositories';
    if (strpos($rel, 'public/admin/') === 0)     return 'admin_pages';
    if (strpos($rel, 'public/client/') === 0)    return 'client_pages';
    if (strpos($rel, 'public/') === 0)           return 'public_pages';
    if (strpos($rel, 'app/services/') === 0)     return 'services';
    if (strpos($rel, 'helpers/') === 0)          return 'helpers';
    if (strpos($rel, 'middleware/') === 0)       return 'middleware';
    if (strpos($rel, 'app/controllers/') === 0)  return 'controllers';
    if (strpos($rel, 'app/security/') === 0)     return 'security';
    if (strpos($rel, 'routes/') === 0)           return 'routes';
    if (strpos($rel, 'core/') === 0)             return 'core';
    if (strpos($rel, 'app/models/') === 0)       return 'models';
    if (strpos($rel, 'app/Core/') === 0)         return 'core';
    if (strpos($rel, 'config/') === 0)           return 'config';
    if (strpos($rel, 'bootstrap/') === 0)        return 'bootstrap';
    if (strpos($rel, 'app/Requests/') === 0)     return 'controllers';
    if (strpos($rel, 'app/views/') === 0)        return 'views';
    if (strpos($rel, 'tests/') === 0)            return 'tests';
    if (strpos($rel, 'database/') === 0)         return 'database';
    if (strpos($rel, 'reports/') === 0)          return 'reports';
    if (strpos($rel, 'routes/') === 0)           return 'routes';
    return 'other';
}

$files = [];
kvn_walk($root, $root, $excludeDirs, $excludeFiles, $files);

echo "=== KVN PHP ARCHITECTURE AUDIT v2 ===\n\n";
$violations = [];

foreach ($files as $rel) {
    $zone = kvn_zone($rel);
    $content = file_get_contents($root . '/' . $rel);
    $hits = [];

    foreach ($strongPatterns as $name => $pat) {
        if (preg_match_all($pat, $content, $m)) $hits[$name] = count($m[0]);
    }
    foreach ($sqlPatterns as $name => $pat) {
        if (preg_match_all($pat, $content, $m)) $hits[$name] = count($m[0]);
    }

    if (empty($hits)) continue;

    $allowedZone = in_array($zone, ['repositories', 'core', 'config', 'tests', 'database', 'reports', 'bootstrap']);
    $isCoreInfra = ($zone === 'bootstrap' && strpos($rel, 'ServiceProvider') !== false);
    $allowed = $allowedZone || $isCoreInfra;

    $marker = $allowed ? '  ALLOWED  ' : '** VIOLATION **';
    echo "{$marker} [{$zone}] {$rel}\n";
    foreach ($hits as $h => $c) echo "       {$h}: {$c}\n";
    if (!$allowed) $violations[] = ['zone' => $zone, 'file' => $rel, 'hits' => $hits];
    echo "\n";
}

echo "\n=== SUMMARY ===\n";
echo "Total files with matches: " . count($files) . "\n";
echo "Total violations: " . count($violations) . "\n";

echo "\n=== LEGACY MODELS / WRAPPERS ===\n";
foreach ($files as $rel) {
    if (stripos($rel, 'app/models/') === 0) echo "  model: {$rel}\n";
    if (stripos(basename($rel), 'PdoDatabase') !== false) echo "  wrapper: {$rel}\n";
    if (stripos(basename($rel), 'Model.php') !== false) echo "  base model: {$rel}\n";
}

