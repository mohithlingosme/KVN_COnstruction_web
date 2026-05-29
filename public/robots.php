<?php

declare(strict_types=1);

require_once '../config/app.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /client/\n";
echo "Sitemap: " . APP_URL . "/sitemap.xml\n";
