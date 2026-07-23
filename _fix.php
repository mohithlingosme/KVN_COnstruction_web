<?php
$path = 'c:\xampp\htdocs\KVN_Construction\config\app.php';
$content = file_get_contents($path);

// Only wrap single-line define() calls with if (!defined(...)) guards
$content = preg_replace_callback(
    '/^(\s*)define\(\s*\'([A-Z_]+)\'\s*,\s*(.+?)\s*\)\s*;\s*$/m',
    function ($m) {
        return "if (!defined('{$m[2]}')) { define('{$m[2]}', {$m[3]}); }";
    },
    $content
);

file_put_contents($path, $content);
echo "Done\n";
