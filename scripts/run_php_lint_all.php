<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit;
}

$root = __DIR__;

$dirIter = new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS);
$it = new RecursiveIteratorIterator($dirIter);

$phpFiles = [];
foreach ($it as $file) {
    $path = (string) $file;
    if (is_file($path) && str_ends_with(strtolower($path), '.php')) {
        $phpFiles[] = $path;
    }
}

sort($phpFiles);

$fail = 0;
foreach ($phpFiles as $file) {
    $out = '';
    $code = file_get_contents($file);
    if ($code === false) {
        $out = '[FAIL] Unable to read file';
        fwrite(STDERR, '[FAIL] ' . $file . PHP_EOL);
        fwrite(STDERR, $out . PHP_EOL);
        $fail++;
        continue;
    }

    try {
        token_get_all($code);
        $out = '[OK] Linted (token scan) ' . $file;
    } catch (Throwable $e) {
        $out = '[FAIL] Token scan failed: ' . $e->getMessage();
    }

    if (stripos($out, 'No syntax errors detected') !== false || str_starts_with($out, '[OK]')) {
        fwrite(STDOUT, '[OK] ' . $file . PHP_EOL);
    } else {
        $fail++;
        fwrite(STDERR, '[FAIL] ' . $file . PHP_EOL);
        fwrite(STDERR, $out . PHP_EOL);
    }
}

exit($fail === 0 ? 0 : 1);
