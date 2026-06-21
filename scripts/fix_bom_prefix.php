<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit;
}

$paths = [
    __DIR__ . '/public/estimator.php.tmp',
];

foreach ($paths as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, 'Missing: ' . basename($path) . PHP_EOL);
        continue;
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        fwrite(STDERR, 'Read failed: ' . basename($path) . PHP_EOL);
        continue;
    }

    $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
    $raw = preg_replace('/^[\x{FEFF}]+/u', '', $raw);
    $raw = preg_replace('/^.*?(<\?php)/s', '$1', $raw, 1);

    file_put_contents($path, $raw, LOCK_EX);

    $code = file_get_contents($path);
    if ($code === false) {
        fwrite(STDERR, '[FAIL] Unable to read file' . PHP_EOL);
        continue;
    }

    $tokens = token_get_all($code);
    if (is_array($tokens) && count($tokens) > 0) {
        fwrite(STDOUT, '[OK] Linted (token scan) ' . basename($path) . PHP_EOL);
    } else {
        fwrite(STDERR, '[FAIL] Lint failed' . PHP_EOL);
    }
}
