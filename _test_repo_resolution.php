<?php
require __DIR__ . '/config/app.php';
require __DIR__ . '/public/includes/repositories.php';

echo 'App\Repositories\QuotationRepository exists: ' . var_export(class_exists('\App\Repositories\QuotationRepository'), true) . PHP_EOL;
echo 'QuotationRepository (global) exists: ' . var_export(class_exists('\QuotationRepository'), true) . PHP_EOL;

$r = repo('Quotation');
echo 'repo(Quotation) class: ' . ($r !== null ? get_class($r) : 'NULL') . PHP_EOL;

$p = repo('Project');
echo 'repo(Project) class: ' . ($p !== null ? get_class($p) : 'NULL') . PHP_EOL;

$l = repo('Lead');
echo 'repo(Lead) class: ' . ($l !== null ? get_class($l) : 'NULL') . PHP_EOL;

$u = repo('User');
echo 'repo(User) class: ' . ($u !== null ? get_class($u) : 'NULL') . PHP_EOL;

$svc = repo('Service');
echo 'repo(Service) class: ' . ($svc !== null ? get_class($svc) : 'NULL') . PHP_EOL;

