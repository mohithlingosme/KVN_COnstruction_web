<?php

$basePath = dirname(__DIR__);

// Helper to list files recursively
function getFiles($dir) {
    $results = [];
    if (!is_dir($dir)) return $results;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isDir()) continue;
        if ($file->getExtension() === 'php') {
            $results[] = $file->getPathname();
        }
    }
    return $results;
}

$report = "# Codebase Audit Report\n\n";

// 1. Route Inventory
$report .= "## Route Inventory\n";
$routesFiles = getFiles($basePath . '/routes');
foreach ($routesFiles as $file) {
    $content = file_get_contents($file);
    preg_match_all('/\$router->(get|post|put|delete|patch|match)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*(?:,\s*.*?)?\)/i', $content, $matches);
    $report .= "### " . basename($file) . "\n";
    if (!empty($matches[0])) {
        for ($i = 0; $i < count($matches[0]); $i++) {
            $report .= "- `" . strtoupper($matches[1][$i]) . " " . $matches[2][$i] . "` -> `" . $matches[3][$i] . "`\n";
        }
    } else {
        $report .= "- No explicit routes found.\n";
    }
}
$report .= "\n";

// 2. Controller Inventory
$report .= "## Controller Inventory\n";
$controllerFiles = getFiles($basePath . '/app/controllers');
foreach ($controllerFiles as $file) {
    $content = file_get_contents($file);
    $className = basename($file, '.php');
    preg_match_all('/public\s+function\s+([a-zA-Z0-9_]+)\s*\(/i', $content, $matches);
    $report .= "### $className\n";
    if (!empty($matches[1])) {
        foreach ($matches[1] as $method) {
            if ($method !== '__construct') {
                $report .= "- `$method()`\n";
            }
        }
    } else {
        $report .= "- No public methods found.\n";
    }
}
$report .= "\n";

// 3. Model Inventory
$report .= "## Model Inventory\n";
$modelFiles = getFiles($basePath . '/app/models');
foreach ($modelFiles as $file) {
    $report .= "- `" . basename($file, '.php') . "`\n";
}
$report .= "\n";

// 4. Repository & Service Inventory
$report .= "## Repository Inventory\n";
$repoFiles = getFiles($basePath . '/app/repositories');
foreach ($repoFiles as $file) {
    $report .= "- `" . basename($file, '.php') . "`\n";
}
$report .= "\n";

$report .= "## Service Inventory\n";
$serviceFiles = getFiles($basePath . '/app/services');
foreach ($serviceFiles as $file) {
    $report .= "- `" . basename($file, '.php') . "`\n";
}
$report .= "\n";

// 5. Database Inventory
$report .= "## Database Inventory\n";
$sqlFile = $basePath . '/database/migration/Kvnc_platform.sql';
if (file_exists($sqlFile)) {
    $sqlContent = file_get_contents($sqlFile);
    preg_match_all('/CREATE\s+TABLE\s+`?([a-zA-Z0-9_]+)`?/i', $sqlContent, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $table) {
            $report .= "- `$table`\n";
        }
    } else {
        $report .= "- No tables found in SQL dump.\n";
    }
} else {
    $report .= "- SQL dump not found.\n";
}
$report .= "\n";

file_put_contents($basePath . '/audit-report/inventory.md', $report);
echo "Inventory generated successfully at audit-report/inventory.md\n";
