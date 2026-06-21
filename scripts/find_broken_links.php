<?php
$dirs = ['public', 'app/views'];
$links = [];
foreach($dirs as $dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../' . $dir));
    foreach($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            preg_match_all('/(?:href|action)=[\"\']([^\"\']+)[\"\']/i', $content, $matches);
            foreach($matches[1] as $match) {
                if(strpos($match, 'http') === 0 || strpos($match, '#') === 0 || strpos($match, 'mailto:') === 0 || strpos($match, 'tel:') === 0 || strpos($match, 'javascript:') === 0 || strpos($match, '<?') !== false) continue;
                $clean = explode('?', $match)[0];
                $links[$clean][] = $file->getPathname();
            }
        }
    }
}
$broken = [];
foreach($links as $link => $sources) {
    $target = __DIR__ . '/../public/' . ltrim($link, '/');
    if(!file_exists($target) && !is_dir($target)) {
        $broken[$link] = array_unique($sources);
    }
}
foreach($broken as $link => $sources) {
    echo "BROKEN: $link (found in " . count($sources) . " files)\n";
    foreach($sources as $s) echo "  - " . basename($s) . "\n";
}
