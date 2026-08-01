<?php
$c = file_get_contents('c:/xampp/htdocs/KVN_Construction/database/migration/Kvnc_platform.sql');

foreach (['videos', 'testimonials', 'services', 'media'] as $t) {
    $s = strpos($c, 'CREATE TABLE `' . $t . '`');
    echo "===== TABLE: $t =====\n";
    echo $s !== false ? substr($c, $s, 1500) : 'not found';
    echo "\n\n";
}

