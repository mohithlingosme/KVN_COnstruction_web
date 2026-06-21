<?php
$reportFile = __DIR__ . '/audit-report/security-report.txt';
$lines = file($reportFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$fixes = [];
$currentFile = null;
$currentLine = null;

foreach ($lines as $line) {
    if (strpos($line, 'File    :') !== false) {
        $currentFile = trim(substr($line, strpos($line, ':') + 1));
    } elseif (strpos($line, 'Line    :') !== false) {
        $currentLine = (int)trim(substr($line, strpos($line, ':') + 1));
    } elseif (strpos($line, 'Snippet :') !== false) {
        if ($currentFile && $currentLine) {
            $fixes[$currentFile][] = ['line' => $currentLine];
            $currentFile = null;
            $currentLine = null;
        }
    }
}

$totalFixed = 0;

foreach ($fixes as $file => $items) {
    if (!file_exists($file)) {
        continue;
    }
    
    // Skip View.php
    if (strpos($file, 'core\View.php') !== false || strpos($file, 'core/View.php') !== false) {
        continue;
    }

    $fileLines = file($file);
    $modified = false;
    
    foreach ($items as $item) {
        $idx = $item['line'] - 1;
        if (!isset($fileLines[$idx])) continue;
        
        $orig = $fileLines[$idx];
        $new = $orig;
        
        // 1. Array 'id' keys: $row['id'], $project['id'], etc
        $new = preg_replace('/echo\s+\$([a-zA-Z0-9_]+)\[\'id\'\];/', 'echo (int)$$1[\'id\'];', $new);
        
        // 2. Specific count/id variables: $totalProjects, $projectId, etc
        $new = preg_replace('/echo\s+\$([a-zA-Z0-9_]+Id);/', 'echo (int)$$1;', $new);
        $new = preg_replace('/echo\s+\$([a-zA-Z0-9_]+Count);/', 'echo (int)$$1;', $new);
        $new = preg_replace('/echo\s+\$([a-zA-Z0-9_]+Files);/', 'echo (int)$$1;', $new);
        
        $prefixes = ['total', 'pending', 'approved', 'rejected', 'completed', 'active', 'ongoing', 'paid', 'archived', 'expired', 'upcoming', 'featured', 'unread', 'admin', 'client', 'open', 'progress', 'resolved', 'reviewed', 'planning', 'available', 'success', 'verified'];
        foreach ($prefixes as $prefix) {
            $new = preg_replace('/echo\s+\$' . $prefix . '([A-Z][a-zA-Z0-9_]*);/', 'echo (int)$' . $prefix . '$1;', $new);
            $new = preg_replace('/echo\s+\$' . $prefix . '([a-z][a-zA-Z0-9_]*);/', 'echo (int)$' . $prefix . '$1;', $new); // For lower case ones if any
        }
        
        // 3. String variables (escape)
        $new = preg_replace('/echo\s+\$successMessage;/', 'echo escape($successMessage);', $new);
        $new = preg_replace('/echo\s+\$errorMessage;/', 'echo escape($errorMessage);', $new);
        $new = preg_replace('/echo\s+\$title;/', 'echo escape($title);', $new);
        $new = preg_replace('/echo\s+\$company([a-zA-Z0-9_]*);/', 'echo escape($company$1);', $new);
        $new = preg_replace('/echo\s+\$category;/', 'echo escape($category);', $new);
        $new = preg_replace('/echo\s+\$type;/', 'echo escape($type);', $new);
        
        // 4. Ternary and comparisons
        $new = preg_replace('/echo\s+(\$i\s*<=\s*\$rating\s*\?\s*\'[^\']+\'\s*:\s*\'[^\']+\')/', 'echo escape($1)', $new);
        $new = preg_replace('/echo\s+(\$i\s*<=\s*\$rating)/', 'echo (int)($1)', $new); // The ones without ternary
        $new = preg_replace('/echo\s+(\$row\[\'is_read\'\]\s*===[^;]+)/', 'echo escape($1)', $new);
        $new = preg_replace('/echo\s+(\$data\[\'[a-zA-Z0-9_]+\'\]\s*===[^;]+)/', 'echo escape($1)', $new);
        $new = preg_replace('/echo\s+(\$status\s*===[^;]+)/', 'echo escape($1)', $new);
        
        // 5. Arithmetic
        $new = preg_replace('/echo\s+\$index\s*\+\s*1/', 'echo (int)($index + 1)', $new);
        
        // 6. CSS classes
        if (strpos($orig, 'class="') !== false || strpos($orig, 'class=\'') !== false || strpos($orig, 'badge') !== false) {
             $new = preg_replace('/echo\s+\$status;/', 'echo escapeCssClass($status);', $new);
             $new = preg_replace('/echo\s+\$row\[\'status\'\];/', 'echo escapeCssClass($row[\'status\']);', $new);
        } else {
             $new = preg_replace('/echo\s+\$status;/', 'echo escape($status);', $new);
             $new = preg_replace('/echo\s+\$row\[\'status\'\];/', 'echo escape($row[\'status\']);', $new);
        }
        
        // 7. General array attributes (docs/projects.php)
        if (strpos($orig, 'project_image') !== false || strpos($orig, 'src=') !== false) {
            $new = preg_replace('/echo\s+\$row\[\'project_image\'\];/', 'echo escapeAttr($row[\'project_image\']);', $new);
        } else {
            $new = preg_replace('/echo\s+\$row\[\'([^\']+)\'\];/', 'echo escape($row[\'$1\']);', $new);
        }
        
        if ($new !== $orig) {
            $fileLines[$idx] = $new;
            $modified = true;
            $totalFixed++;
        } else {
            echo "Failed: $file:$idx -> " . trim($orig) . "\n";
        }
    }
    
    if ($modified) {
        file_put_contents($file, implode("", $fileLines));
    }
}

echo "\nFixed $totalFixed issues.\n";
