<?php

/**
 * SQL Injection Fixer - converts unsafe string interpolation to PDO positional params.
 *
 * Detects patterns like:
 *   WHERE client_id = $clientId          -> WHERE client_id = ?
 *   AND status = '$status'               -> AND status = ?
 *   VALUES ($clientId, 'text')           -> VALUES (?, 'text')
 *   AND id = $_GET['id']                 -> AND id = ?
 *
 * Usage: php _fix_sql_injection.php [--dry-run] [--file=path/to/file.php]
 */

declare(strict_types=1);

$dryRun = in_array('--dry-run', $argv ?? []);
$targetFile = null;
foreach ($argv ?? [] as $arg) {
    if (str_starts_with($arg, '--file=')) {
        $targetFile = substr($arg, 7);
    }
}

// ============================================================================
// FIX FUNCTIONS FOR SPECIFIC FILES
// ============================================================================

function fixFile(string $path, bool $dryRun): array
{
    $changes = [];
    $content = file_get_contents($path);
    if ($content === false) {
        return ["ERROR: Cannot read $path"];
    }

    $original = $content;
    $filename = basename($path);

    // Pattern 1: $conn->query("...$var...") with session variable
    // Replace $clientId and $clientId with ? inside query strings
    $patterns = [
        // Session variables used inside queries
        '/\$clientId/' => '?',
        '/\$client_name/' => '?',  // intentional - will be caught
        // Common $_GET and $_POST patterns - these must be extracted first
    ];

    // Pattern 2: $var inside SQL string without quotes
    // e.g. "WHERE client_id = $clientId" -> "WHERE client_id = ?"
    $content = preg_replace_callback(
        '/(query|execute)\(\s*("|\')(.*?)\2\s*\)/s',
        function ($matches) use ($filename, &$changes) {
            $func = $matches[1];
            $quote = $matches[2];
            $sql = $matches[3];
            $fixedSql = $sql;
            $paramCount = 0;

            // Find all PHP variable interpolations inside the SQL
            // e.g., $clientId, $_SESSION['client_id'], $_GET['id'], etc.
            $fixedSql = preg_replace_callback(
                '/\$\w+(?:\[\'(?:[^\']+)\'\])?/',
                function ($varMatch) use (&$paramCount) {
                    $paramCount++;
                    return '?';
                },
                $fixedSql
            );

            if ($paramCount > 0 && $fixedSql !== $sql) {
                $changes[] = "  - $filename: Replaced $paramCount variable(s) with ? placeholders";
                return "$func({$quote}{$fixedSql}{$quote})";
            }

            return $matches[0];
        },
        $content
    );

    // Pattern 3: ->num_rows === 0 or ->num_rows > 0 (property to method)
    $content = preg_replace(
        '/->num_rows\b(?!\s*\()/',
        '->num_rows()',
        $content
    );

    if ($content !== $original) {
        if (!$dryRun) {
            file_put_contents($path, $content);
        }
        return $changes;
    }

    return [];
}

// ============================================================================
// FIND ALL FILES WITH SQL INTERPOLATION
// ============================================================================

$baseDir = __DIR__;
$filesToFix = [];

if ($targetFile) {
    $filesToFix[] = $targetFile;
} else {
    // Find all PHP files that have $conn->query or $conn->execute with variables
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;
        $path = $file->getPathname();

        // Skip vendor, tests, storage
        if (str_contains($path, '/vendor/') || str_contains($path, '/tests/') || str_contains($path, '/storage/')) {
            continue;
        }

        $content = file_get_contents($path);
        if ($content === false) continue;

        // Check for patterns indicating SQL injection risk
        if (preg_match('/->query\(\s*["\'].*?\$|->execute\(\s*["\'].*?\$/', $content)) {
            $filesToFix[] = $path;
        }
    }
}

// ============================================================================
// FIX FILES
// ============================================================================

$totalChanges = 0;
$fixedFiles = 0;
$skippedFiles = 0;

echo "SQL Injection Auto-Fixer\n";
echo "=======================\n";
echo "Mode: " . ($dryRun ? "DRY RUN (no changes)" : "LIVE") . "\n\n";

foreach ($filesToFix as $path) {
    $relativePath = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $path);
    echo "Scanning: $relativePath\n";

    $changes = fixFile($path, $dryRun);

    if (empty($changes)) {
        echo "  - No fixable patterns found\n";
        $skippedFiles++;
    } else {
        foreach ($changes as $change) {
            echo "$change\n";
            $totalChanges++;
        }
        $fixedFiles++;
    }
}

echo "\n";
echo "Summary:\n";
echo "  Files scanned: " . count($filesToFix) . "\n";
echo "  Files with fixes: $fixedFiles\n";
echo "  Files skipped: $skippedFiles\n";
echo "  Total changes: $totalChanges\n";

if ($dryRun) {
    echo "\nRun without --dry-run to apply fixes.\n";
}