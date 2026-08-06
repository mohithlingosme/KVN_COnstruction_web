<?php

require __DIR__ . '/smoke/bootstrap.php';

$pdo = smoke_pdo();

$tables = ['users', 'clients', 'projects', 'leads', 'blogs', 'portfolio', 'services', 'testimonials', 'construction_packages', 'faqs', 'settings'];

foreach ($tables as $t) {
    try {
        $count = (int) $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        echo "OK   {$t}: {$count} rows\n";
    } catch (Throwable $e) {
        echo "FAIL {$t}: " . $e->getMessage() . "\n";
    }
}