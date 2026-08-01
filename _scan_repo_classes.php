<?php
$files = glob(__DIR__ . '/app/repositories/*.php');
sort($files);
foreach ($files as $f) {
    $c = file_get_contents($f);
    $ns = preg_match('/namespace\s+([^;]+);/', $c, $m) ? $m[1] : 'GLOBAL';
    $class = preg_match('/(?:abstract\s+)?class\s+(\w+)/', $c, $m2) ? $m2[1] : '?';
    $ctor = 'default';
    if (preg_match('/function\s+__construct\s*\(([^)]*)\)/', $c, $m3)) {
        $ctor = trim($m3[1]);
    }
    echo str_pad(basename($f), 32)
        . ' | ns: ' . str_pad($ns, 30)
        . ' | class: ' . str_pad($class, 26)
        . ' | ctor: ' . $ctor . PHP_EOL;
}

