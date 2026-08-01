<?php
/**
 * KVN Construction - Complete Crawl Test
 * Tests every page, endpoint, and asset for 404/500 errors
 */

$baseUrl = 'http://localhost/KVN_Construction/public/';
$docRoot = 'c:/xampp/htdocs/KVN_Construction';
$report = ['404' => [], '500' => [], 'assets' => [], 'includes' => []];

// =========================================================
// PHASE 1: Test all PHP pages directly
// =========================================================

$pages = [
    '/index.php',
    '/about-us.php',
    '/services.php',
    '/projects.php',
    '/project-details.php?slug=test',
    '/blogs.php',
    '/blog-details.php?slug=test',
    '/contact.php',
    '/estimator.php',
    '/login.php',
    '/phone-login.php',
    '/register.php',
    '/forgot-password.php',
    '/verify-phone-otp.php',
    '/verify-reset-otp.php',
    '/reset-password.php',
    '/logout.php',
    '/gallery.php',
    '/packages.php',
    '/faq.php',
    '/careers.php',
    '/testimonials.php',
    '/privacy.php',
    '/terms.php',
    '/videos.php',
    '/404.php',
];

echo "=== Testing PHP Pages ===\n";
foreach ($pages as $page) {
    $url = $baseUrl . ltrim($page, '/');
    $context = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "FAIL: $page - Connection failed\n";
        $report['404'][] = ['page' => $page, 'error' => 'Connection failed'];
        continue;
    }
    
    // Check HTTP response code
    $httpCode = 0;
    if (isset($http_response_header[0])) {
        preg_match('/\d{3}/', $http_response_header[0], $matches);
        $httpCode = (int)($matches[0] ?? 0);
    }
    
    if ($httpCode === 404) {
        echo "404: $page\n";
        $report['404'][] = ['page' => $page, 'code' => 404];
    } elseif ($httpCode >= 500) {
        echo "500: $page\n";
        $report['500'][] = ['page' => $page, 'code' => $httpCode];
    } else {
        echo "OK: $page ($httpCode)\n";
    }
}

// =========================================================
// PHASE 2: Check asset files existence
// =========================================================

echo "\n=== Testing Asset Files ===\n";
$assets = [
    'public/assets/css/style.css',
    'public/assets/js/app.js',
    'public/assets/images/favicon.png',
    'public/assets/images/og-image.jpg',
    'public/assets/images/default-user.png',
    'public/assets/images/contact/contact-hero.jpg',
];

foreach ($assets as $asset) {
    $fullPath = $docRoot . '/' . $asset;
    if (file_exists($fullPath)) {
        echo "OK: $asset\n";
    } else {
        echo "MISSING: $asset\n";
        $report['assets'][] = ['asset' => $asset, 'error' => 'File not found'];
    }
}

// =========================================================
// PHASE 3: Check include paths
// =========================================================

echo "\n=== Testing Include Paths ===\n";
$includeFiles = [
    'config/app.php',
    'config/database.php',
    'helpers/functions.php',
    'helpers/functions_security.php',
    'helpers/csrf.php',
    'helpers/session.php',
    'helpers/security.php',
    'helpers/rateLimiter.php',
    'helpers/upload.php',
    'helpers/formatter.php',
    'helpers/seo.php',
    'helpers/auth.php',
    'helpers/api_response.php',
    'helpers/mail.php',
    'helpers/sms.php',
    'helpers/otp.php',
    'core/Router.php',
    'core/Controller.php',
    'core/View.php',
    'core/Model.php',
    'core/Repository.php',
    'core/Service.php',
    'core/Event.php',
    'middleware/guest.php',
    'middleware/auth.php',
    'middleware/admin.php',
    'middleware/admin-auth.php',
    'middleware/admin-guest.php',
    'middleware/client.php',
    'middleware/clients.php',
    'middleware/security.php',
    'routes/api_estimator.php',
    'app/controllers/auth/AuthController.php',
    'app/models/User.php',
    'app/services/AuthService.php',
    'app/services/QuotationService.php',
    'app/repositories/UserRepository.php',
    'bootstrap/providers/ServiceProvider.php',
];

foreach ($includeFiles as $file) {
    $fullPath = $docRoot . '/' . $file;
    if (file_exists($fullPath)) {
        echo "OK: $file\n";
    } else {
        echo "MISSING: $file\n";
        $report['includes'][] = ['file' => $file, 'error' => 'File not found'];
    }
}

// =========================================================
// PHASE 4: PHP Syntax Check
// =========================================================

echo "\n=== PHP Syntax Check ===\n";
$phpFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($docRoot . '/public'),
    RecursiveIteratorIterator::SELF_FIRST
);

$syntaxErrors = [];
foreach ($phpFiles as $file) {
    if ($file->getExtension() !== 'php') continue;
    $output = shell_exec("php -l \"{$file->getPathname()}\" 2>&1");
    if (strpos($output, 'No syntax errors') === false) {
        echo "SYNTAX ERROR: {$file->getPathname()}\n";
        echo "  $output\n";
        $syntaxErrors[] = $file->getPathname();
    }
}

// Also test app files
$appFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($docRoot . '/app'),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($appFiles as $file) {
    if ($file->getExtension() !== 'php') continue;
    $output = shell_exec("php -l \"{$file->getPathname()}\" 2>&1");
    if (strpos($output, 'No syntax errors') === false) {
        echo "SYNTAX ERROR: {$file->getPathname()}\n";
        echo "  $output\n";
        $syntaxErrors[] = $file->getPathname();
    }
}

// Also test core files
$coreFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($docRoot . '/core'),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($coreFiles as $file) {
    if ($file->getExtension() !== 'php') continue;
    $output = shell_exec("php -l \"{$file->getPathname()}\" 2>&1");
    if (strpos($output, 'No syntax errors') === false) {
        echo "SYNTAX ERROR: {$file->getPathname()}\n";
        echo "  $output\n";
        $syntaxErrors[] = $file->getPathname();
    }
}

if (empty($syntaxErrors)) {
    echo "All PHP files pass syntax check.\n";
}

// =========================================================
// PHASE 5: Report Summary
// =========================================================

echo "\n============================\n";
echo "CRAWL TEST RESULTS\n";
echo "============================\n";
echo "404 Errors: " . count($report['404']) . "\n";
echo "500 Errors: " . count($report['500']) . "\n";
echo "Missing Assets: " . count($report['assets']) . "\n";
echo "Missing Includes: " . count($report['includes']) . "\n";
echo "Syntax Errors: " . count($syntaxErrors) . "\n";

if (!empty($report['404'])) {
    echo "\n--- 404 REPORT ---\n";
    foreach ($report['404'] as $e) echo "  {$e['page']}: {$e['error']}\n";
}
if (!empty($report['500'])) {
    echo "\n--- 500 REPORT ---\n";
    foreach ($report['500'] as $e) echo "  {$e['page']} (Code: {$e['code']})\n";
}
if (!empty($report['assets'])) {
    echo "\n--- MISSING ASSETS ---\n";
    foreach ($report['assets'] as $a) echo "  {$a['asset']}\n";
}
if (!empty($report['includes'])) {
    echo "\n--- MISSING INCLUDES ---\n";
    foreach ($report['includes'] as $i) echo "  {$i['file']}\n";
}