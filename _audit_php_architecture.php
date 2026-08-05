<?php
/**
 * Fresh repository-wide PHP architecture audit.
 * Scans for forbidden SQL patterns outside repositories.
 */

$root = __DIR__;
$excludeDirs = ['vendor', 'node_modules', 'storage', 'uploads', 'bootstrap/cache', '.git', 'audit-report'];
$excludeFiles = ['_audit_php_architecture.php'];

$patterns = [
    'conn_var'       => '/\$conn\b/',
    'db_var'         => '/\$db\b/',
    'mysqli'         => '/\bmysqli\b/i',
    'pdo_var'        => '/\$pdo\b/',
    'pdo_class'      => '/\bPDO\b/i',
    'prepare'        => '/->prepare\s*\(/',
    'query'          => '/->query\s*\(/',
    'bind_param'     => '/->bind_param\s*\(/',
    'fetch_assoc'    => '/->fetch_assoc\s*\(/',
    'fetch_all'      => '/->fetch_all\s*\(/',
    'num_rows'       => '/->num_rows\s*\(/',
    'select'         => '/SELECT\b/i',
    'insert'         => '/INSERT\s+INTO\b/i',
    'update'         => '/\bUPDATE\b/i',
    'delete'         => '/\bDELETE\s+FROM\b/i',
    'create_table'   => '/CREATE\s+TABLE\b/i',
    'alter_table'    => '/ALTER\s+TABLE\b/i',
    'drop_table'     => '/DROP\s+TABLE\b/i',
];

$repositories = [];
$pages = [];
$services = [];
$helpers = [];
$middleware = [];
$controllers = [];
$security = [];
$routes = [];
$core = [];
$models = [];
$other = [];

function kvn_scan_files($dir, $root, $excludeDirs, $excludeFiles, &$bucket) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            if (in_array($item, $excludeDirs)) continue;
            kvn_scan_files($path, $root, $excludeDirs, $excludeFiles, $bucket);
        } elseif (is_file($path) && substr($item, -4) === '.php') {
            if (in_array($item, $excludeFiles)) continue;
            $rel = str_replace('\\', '/', substr($path, strlen($root) + 1));
            $bucket[] = $rel;
        }
    }
}

$allFiles = [];
kvn_scan_files($root, $root, $excludeDirs, $excludeFiles, $other); // into other initially
$allFiles = array_merge($allFiles, $other);

// Classify
$classified = [];
foreach ($allFiles as $rel) {
    if (strpos($rel, 'app/repositories/') === 0)       $classified['repositories'][] = $rel;
    elseif (strpos($rel, 'public/admin/') === 0)        $classified['pages'][] = $rel;
    elseif (strpos($rel, 'public/') === 0)              $classified['pages'][] = $rel;
    elseif (strpos($rel, 'app/services/') === 0)        $classified['services'][] = $rel;
    elseif (strpos($rel, 'helpers/') === 0)             $classified['helpers'][] = $rel;
    elseif (strpos($rel, 'middleware/') === 0)          $classified['middleware'][] = $rel;
    elseif (strpos($rel, 'app/controllers/') === 0)     $classified['controllers'][] = $rel;
    elseif (strpos($rel, 'app/security/') === 0)        $classified['security'][] = $rel;
    elseif (strpos($rel, 'routes/') === 0)              $classified['routes'][] = $rel;
    elseif (strpos($rel, 'core/') === 0)                $classified['core'][] = $rel;
    elseif (strpos($rel, 'app/models/') === 0)          $classified['models'][] = $rel;
    elseif (strpos($rel, 'app/Core/') === 0)            $classified['core'][] = $rel;
    elseif (strpos($rel, 'config/') === 0)              $classified['core'][] = $rel;
    elseif (strpos($rel, 'bootstrap/') === 0)           $classified['core'][] = $rel;
    elseif (strpos($rel, 'app/Requests/') === 0)        $classified['controllers'][] = $rel;
    elseif (strpos($rel, 'app/views/') === 0)           $classified['pages'][] = $rel;
    else                                                $classified['other'][] = $rel;
}

// Define allowed zones for SQL
function isAllowed($rel) {
    if (strpos($rel, 'app/repositories/') === 0) return true;
    if (strpos($rel, 'core/Repository.php') === 0) return true;
    if (strpos($rel, 'app/Core/') === 0) return true;
    if (strpos($rel, 'config/database.php') === 0) return true;
    if (strpos($rel, 'tests/') === 0) return true;
    if (strpos($rel, 'database/') === 0) return true;
    if (strpos($rel, 'reports/') === 0) return true;
    if (strpos($rel, 'app/security/') === 0 && strpos($rel, 'PdoDatabase') !== false) return true;
    return false;
}

$report = [];
echo "=== KVN PHP ARCHITECTURE AUDIT ===\n\n";

foreach ($classified as $zone => $files) {
    echo "--- ZONE: {$zone} ({count($files)} files) ---\n";
    foreach ($files as $rel) {
        $content = file_get_contents($root . '/' . $rel);
        $allowed = isAllowed($rel);
        $hits = [];
        foreach ($patterns as $pname => $pattern) {
            if (preg_match_all($pattern, $content, $m)) {
                $hits[$pname] = count($m[0]);
            }
        }
        if (count($hits) > 0) {
            $status = $allowed ? 'ALLOWED' : '** VIOLATION **';
            $report[] = ['file' => $rel, 'zone' => $zone, 'hits' => $hits, 'allowed' => $allowed];
            echo "  {$status} {$rel}\n";
            foreach ($hits as $h => $c) {
                echo "      {$h}: {$c}\n";
            }
        }
    }
    echo "\n";
}

echo "\n=== SUMMARY ===\n";
$violations = array_filter($report, fn($r) => !$r['allowed']);
echo "Total files with matches: " . count($report) . "\n";
echo "Total violations (SQL outside allowed zones): " . count($violations) . "\n\n";
foreach ($violations as $v) {
    echo "VIOLATION: [{$v['zone']}] {$v['file']}\n";
    foreach ($v['hits'] as $h => $c) echo "    {$h}: {$c}\n";
}

echo "\n=== LEGACY MODELS / WRAPPERS CHECK ===\n";
foreach ($classified['models'] as $rel) {
    echo "  model file: {$rel}\n";
}
foreach ($classified['other'] as $rel) {
    if (stripos($rel, 'PdoDatabase') !== false || stripos($rel, 'Model') !== false) {
        echo "  possible wrapper: {$rel}\n";
    }
}
