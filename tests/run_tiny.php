<?php
// Tiny test: just try to load everything and write to file
file_put_contents(__DIR__ . '/debug_step1.txt', 'step1');
require_once __DIR__ . '/bootstrap.php';
file_put_contents(__DIR__ . '/debug_step2.txt', 'step2');
define('CONFIG_PATH', __DIR__ . '/Fakes');
file_put_contents(__DIR__ . '/debug_step3.txt', 'step3');
require_once ROOT_PATH . '/app/controllers/auth/AuthController.php';
file_put_contents(__DIR__ . '/debug_step4.txt', 'step4');
// Now try loading AdminController - step by step
file_put_contents(__DIR__ . '/debug_step4a.txt', 'step4a');
require_once ROOT_PATH . '/config/app.php';
file_put_contents(__DIR__ . '/debug_step4b.txt', 'step4b');

file_put_contents(__DIR__ . '/debug_step5.txt', 'step5');
file_put_contents(__DIR__ . '/debug_done.txt', 'ALL DONE');
echo "OK\n";

