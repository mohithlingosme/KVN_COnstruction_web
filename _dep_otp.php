<?php
/**
 * Dependency verification for OtpService/OTPService deletion decision.
 * Look for references to class OtpService (exact) and OTPService anywhere.
 */
$root = __DIR__;
$excludeDirs = ['vendor', 'node_modules', '.git', 'audit-report', 'uploads', 'storage', 'reports', 'database'];
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

echo "Scanning " . count($allFiles) . " files for OtpService / OTPService references\n\n";

$exactOtpLowerPatterns = [
    'new OtpService'      => '/new\s+OtpService\b/',
    'OtpService::'        => '/\bOtpService\s*::/',
    'use OtpService'      => '/use\s+([A-Za-z0-9_\\\\]*\\)?OtpService\b/',
    'param OtpService'    => '/OtpService\s*\$[A-Za-z_]/',
];

$otpUpperPatterns = [
    'new OTPService'      => '/new\s+OTPService\b/',
    'OTPService::'        => '/\bOTPService\s*::/',
    'use OTPService'      => '/use\s+([A-Za-z0-9_\\\\]*\\)?OTPService\b/',
    'param OTPService'    => '/OTPService\s*\$[A-Za-z_]/',
    'string OTPService'   => '/[\'"]OTPService[\'"]/',
];

$refsLower = [];
$refsUpper = [];
foreach ($allFiles as $f) {
    $content = file_get_contents($root . '/' . $f);
    if (!$content) continue;
    // skip the two service files themselves
    if ($f === 'app/services/OtpService.php' || $f === 'app/services/OTPService.php') continue;

    foreach ($exactOtpLowerPatterns as $name => $pat) {
        if (preg_match($pat, $content)) {
            $refsLower[$f][] = $name;
        }
    }
    foreach ($otpUpperPatterns as $name => $pat) {
        if (preg_match($pat, $content)) {
            $refsUpper[$f][] = $name;
        }
    }
}

echo "=== REFERENCES TO LOWERCASE 'OtpService' (exact) ===\n";
if (empty($refsLower)) {
    echo "ZERO references to 'OtpService' (lowercase) -- DEAD\n";
} else {
    foreach ($refsLower as $f => $patterns) {
        echo "{$f}: " . implode(', ', $patterns) . "\n";
    }
}

echo "\n=== REFERENCES TO 'OTPService' (uppercase) ===\n";
if (empty($refsUpper)) {
    echo "ZERO references to 'OTPService'\n";
} else {
    foreach ($refsUpper as $f => $patterns) {
        echo "{$f}: " . implode(', ', $patterns) . "\n";
    }
}

echo "\n=== FILE CONTENT HASH COMPARISON ===\n";
$lower = $root . '/app/services/OtpService.php';
$upper = $root . '/app/services/OTPService.php';
if (file_exists($lower) && file_exists($upper)) {
    $h1 = md5_file($lower);
    $h2 = md5_file($upper);
    echo "OtpService.php md5:   {$h1}\n";
    echo "OTPService.php md5:   {$h2}\n";
    echo "Identical: " . ($h1 === $h2 ? 'YES' : 'NO') . "\n";

    // Show class declaration in each
    $c1 = preg_match('/class\s+(\w+)/', file_get_contents($lower), $m1) ? $m1[1] : 'NONE';
    $c2 = preg_match('/class\s+(\w+)/', file_get_contents($upper), $m2) ? $m2[1] : 'NONE';
    echo "Class in OtpService.php:  {$c1}\n";
    echo "Class in OTPService.php:  {$c2}\n";
}

