<?php
require 'c:\xampp\htdocs\KVN_Construction\tests\bootstrap.php';
echo "1: bootstrap loaded\n";

if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', 'c:\xampp\htdocs\KVN_Construction\tests\Fakes');
}
echo "2: CONFIG_PATH set\n";

$path1 = 'c:\xampp\htdocs\KVN_Construction\app\controllers\auth\AuthController.php';
echo "loading: $path1\n";
require $path1;
echo "3: AuthController loaded\n";

$path2 = 'c:\xampp\htdocs\KVN_Construction\app\controllers\admin\AdminController.php';
echo "loading: $path2\n";
require $path2;
echo "4: AdminController loaded\n";

$path3 = 'c:\xampp\htdocs\KVN_Construction\app\models\Lead.php';
echo "loading: $path3\n";
require $path3;
echo "5: Lead loaded\n";

echo "All loaded successfully\n";
