<?php
/**
 * Dependency graph: find references to legacy/dead-code candidates.
 * Only reports references so we can prove zero references before deletion.
 */
$root = __DIR__;
$excludeDirs = ['vendor', 'node_modules', '.git', 'audit-report', 'uploads', 'storage'];
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

// Candidate dead-code targets
$candidates = [
    'app/controllers/AuthController.php'         => ['AuthController', 'OTPService'],
    'app/controllers/ClientController.php'       => ['ClientController'],
    'app/controllers/EstimatorController.php'    => ['EstimatorController'],
    'app/controllers/PublicController.php'       => ['PublicController'],
    'app/controllers/auth/AdminAuthController.php' => ['AdminAuthController'],
    'app/controllers/admin/MediaController.php'  => ['MediaController'],
    'app/controllers/admin/ProjectController.php' => ['\ProjectController', 'App\Controllers\ProjectController'],
    'app/security/SessionManager.php'            => ['class SessionManager'],
    'core/Service.php'                           => ['Service'],
    'core/Repository.php'                        => ['extends Repository'],
];

echo "=== DEAD CODE CANDIDATE REFERENCES ===\n";
foreach ($candidates as $file => $needles) {
    $fileExists = file_exists($root . '/' . $file) ? 'EXISTS' : 'MISSING';
    echo "\n[{$file}] ({$fileExists})\n";
    foreach ($needles as $needle) {
        echo "  Searching: '{$needle}'\n";
        $refs = [];
        foreach ($allFiles as $f) {
            if ($f === $file) continue; // skip self
            $content = @file_get_contents($root . '/' . $f);
            if ($content === false) continue;
            // skip docs/markdown
            if (substr($f, -4) !== '.php') continue;
            if (strpos($content, $needle) !== false) {
                $refs[] = $f;
            }
        }
        if (empty($refs)) {
            echo "    -> NO references (DEAD)\n";
        } else {
            echo "    -> Referenced by:\n";
            foreach ($refs as $r) echo "       {$r}\n";
        }
    }
}

echo "\n=== OTPService FILE CHECK ===\n";
if (file_exists($root . '/app/services/OtpService.php')) echo "  app/services/OtpService.php exists\n";
if (file_exists($root . '/app/services/OTPService.php')) echo "  app/services/OTPService.php exists (uppercase)\n";
$grep = [];
foreach ($allFiles as $f) {
    $content = @file_get_contents($root . '/' . $f);
    if ($content !== false && strpos($content, 'OTPService') !== false) $grep[] = $f;
}
echo "  Files referencing 'OTPService': " . (empty($grep) ? 'NONE' : implode(', ', $grep)) . "\n";

echo "\n=== ANY 'App\\Models' REFERENCES ===\n";
$modelsRefs = [];
foreach ($allFiles as $f) {
    $content = @file_get_contents($root . '/' . $f);
    if ($content !== false && strpos($content, 'App\\Models') !== false) $modelsRefs[] = $f;
}
echo "  References: " . (empty($modelsRefs) ? 'NONE' : implode(', ', $modelsRefs)) . "\n";

echo "\n=== NEW USER MODEL / OLD MODEL REFERENCES ===\n";
$newUserModel = [];
foreach ($allFiles as $f) {
    $content = @file_get_contents($root . '/' . $f);
    if ($content !== false) {
        if (preg_match('/new\s+User\s*\(/', $content) || preg_match('/extends\s+User\b/', $content)) $newUserModel[] = $f;
    }
}
echo "  'new User(' refs: " . (empty($newUserModel) ? 'NONE' : implode(', ', $newUserModel)) . "\n";

