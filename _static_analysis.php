<?php
/**
 * KVN Construction - Complete Static Analysis
 * Finds every 404/500 source without requiring a running server
 */
 
$docRoot = __DIR__;
$errors = [
    'missing_assets' => [],
    'broken_references' => [],
    'potential_500' => [],
    'missing_files' => [],
    'syntax_errors' => [],
];

echo "=== KVN Construction Static Analysis ===\n\n";

// =========================================================
// 1. Check ALL referenced assets exist
// =========================================================
echo "--- Checking Assets ---\n";
$publicDir = $docRoot . '/public';
$assetDir = $docRoot . '/public/assets';

$assetFolders = [
    'css' => 'css/style.css',
    'js' => 'js/app.js',
    'images' => 'images/favicon.png',
    'images/og' => 'images/og-image.jpg',
    'images/default' => 'images/default-user.png',
    'images/contact' => 'images/contact/contact-hero.jpg',
];

foreach ($assetFolders as $desc => $path) {
    $full = "$assetDir/$path";
    if (!file_exists($full)) {
        echo "  MISSING: $full\n";
        $errors['missing_assets'][] = $full;
    }
}

// =========================================================
// 2. Check all referenced PHP files exist
// =========================================================
echo "\n--- Checking Referenced Files ---\n";

// All includes/requires used across the codebase
$referencedFiles = [
    '../config/app.php' => __DIR__ . '/config/app.php',
    '../helpers/functions.php' => __DIR__ . '/helpers/functions.php',
    '../helpers/csrf.php' => __DIR__ . '/helpers/csrf.php',
    '../helpers/security.php' => __DIR__ . '/helpers/security.php',
    '../helpers/session.php' => __DIR__ . '/helpers/session.php',
    '../helpers/rateLimiter.php' => __DIR__ . '/helpers/rateLimiter.php',
    '../helpers/upload.php' => __DIR__ . '/helpers/upload.php',
    '../helpers/formatter.php' => __DIR__ . '/helpers/formatter.php',
    '../helpers/seo.php' => __DIR__ . '/helpers/seo.php',
    '../helpers/auth.php' => __DIR__ . '/helpers/auth.php',
    '../helpers/api_response.php' => __DIR__ . '/helpers/api_response.php',
    '../helpers/mail.php' => __DIR__ . '/helpers/mail.php',
    '../helpers/sms.php' => __DIR__ . '/helpers/sms.php',
    '../helpers/otp.php' => __DIR__ . '/helpers/otp.php',
    '../core/Router.php' => __DIR__ . '/core/Router.php',
    '../core/Controller.php' => __DIR__ . '/core/Controller.php',
    '../core/View.php' => __DIR__ . '/core/View.php',
    '../core/Model.php' => __DIR__ . '/core/Model.php',
    '../core/Repository.php' => __DIR__ . '/core/Repository.php',
    '../core/Service.php' => __DIR__ . '/core/Service.php',
    '../core/Event.php' => __DIR__ . '/core/Event.php',
    '../middleware/guest.php' => __DIR__ . '/middleware/guest.php',
    '../middleware/auth.php' => __DIR__ . '/middleware/auth.php',
    '../middleware/admin.php' => __DIR__ . '/middleware/admin.php',
    '../middleware/admin-auth.php' => __DIR__ . '/middleware/admin-auth.php',
    '../middleware/admin-guest.php' => __DIR__ . '/middleware/admin-guest.php',
    '../middleware/client.php' => __DIR__ . '/middleware/client.php',
    '../middleware/clients.php' => __DIR__ . '/middleware/clients.php',
    '../middleware/security.php' => __DIR__ . '/middleware/security.php',
    '../app/controllers/auth/AuthController.php' => __DIR__ . '/app/controllers/auth/AuthController.php',
    '../app/models/User.php' => __DIR__ . '/app/models/User.php',
    '../app/services/AuthService.php' => __DIR__ . '/app/services/AuthService.php',
    '../app/services/QuotationService.php' => __DIR__ . '/app/services/QuotationService.php',
    '../app/repositories/UserRepository.php' => __DIR__ . '/app/repositories/UserRepository.php',
    '../bootstrap/providers/ServiceProvider.php' => __DIR__ . '/bootstrap/providers/ServiceProvider.php',
    '../app/views/layouts/header.php' => __DIR__ . '/app/views/layouts/header.php',
    '../app/views/layouts/footer.php' => __DIR__ . '/app/views/layouts/footer.php',
    '../../../config/app.php' => __DIR__ . '/config/app.php',
];

$missingFiles = [];
foreach ($referencedFiles as $name => $path) {
    if (!file_exists($path)) {
        echo "  MISSING: $name\n";
        $missingFiles[] = $name;
    }
}

if (empty($missingFiles)) {
    echo "  All referenced PHP files exist.\n";
}

// =========================================================
// 3. Check for broken references in code
// =========================================================
echo "\n--- Checking for Broken References in Code ---\n";

// Search for hardcoded Windows paths
$searchDirs = [$docRoot . '/public', $docRoot . '/app', $docRoot . '/helpers', $docRoot . '/config', $docRoot . '/core', $docRoot . '/middleware'];
$brokenPaths = [];

foreach ($searchDirs as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;
        $content = file_get_contents($file->getPathname());
        
        // Check for hardcoded Windows paths
        if (preg_match('/[A-Z]:\\\\xampp/', $content)) {
            echo "  BROKEN WINDOWS PATH: {$file->getPathname()}\n";
            $errors['broken_references'][] = ['file' => $file->getPathname(), 'type' => 'hardcoded_windows_path'];
        }
        
        // Check for broken include paths that don't exist
        preg_match_all('/(?:include|require|require_once)\s*\(?\s*[\'"]([^\'"]+)[\'"]/', $content, $matches);
        foreach ($matches[1] as $includePath) {
            if (strpos($includePath, '..') === 0 || strpos($includePath, '/') === 0) {
                $resolved = realpath(dirname($file->getPathname()) . '/' . $includePath);
                if ($resolved === false && strpos($includePath, '.php') !== false) {
                    echo "  BROKEN INCLUDE in {$file->getFilename()}: $includePath\n";
                    $errors['broken_references'][] = ['file' => $file->getPathname(), 'type' => 'broken_include', 'path' => $includePath];
                }
            }
        }
    }
}

if (empty($errors['broken_references'])) {
    echo "  No broken references found.\n";
}

// =========================================================
// 4. Check for potential 500 errors (undefined functions, etc.)
// =========================================================
echo "\n--- Checking for Potential 500 Errors ---\n";

// Check that all called functions exist
$functionCalls = [];
foreach ($searchDirs as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;
        $content = file_get_contents($file->getPathname());
        // Extract function calls
        preg_match_all('/(?<![a-zA-Z0-9_])(' . implode('|', [
            'base_url', 'redirect', 'csrfField', 'csrfToken', 'escape', 'sanitize',
            'sanitize_html', 'escapeAttr', 'escapeCssClass', 'is_logged_in', 'is_admin',
            'is_client', 'auth_user', 'securityHeaders', 'isValidEmail', 'sanitizeInput',
            'generateCsrfToken', 'verifyCsrfToken', 'validateCsrf', 'regenerateCsrfToken',
            'checkRateLimit', 'incrementRateLimit', 'isAjaxRequest', 'json_response',
            'logSecurityEvent', 'limitText', 'e', 'asset', 'projectImageFallback',
            'sendPasswordResetEmail', 'uploadDocument'
        ]) . ')\s*\(/', $content, $matches);
        foreach ($matches[1] as $func) {
            if (!function_exists($func) && !in_array($func, $functionCalls)) {
                $functionCalls[] = $func;
            }
        }
    }
}

// Check which functions are defined
$definedFuncs = get_defined_functions()['user'];
foreach ($functionCalls as $func) {
    if (!in_array($func, $definedFuncs)) {
        echo "  POTENTIAL 500: Function '$func' called but may not be defined in all contexts\n";
        $errors['potential_500'][] = ['function' => $func, 'reason' => 'May not be defined in all contexts'];
    }
}

// Check for duplicate function definitions
echo "\n--- Checking for Duplicate Function Definitions ---\n";
$funcDefs = [];
foreach ($searchDirs as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;
        $content = file_get_contents($file->getPathname());
        preg_match_all('/function\s+(isLoggedIn|isAdmin|isClient|destroySession|escape)\s*\(/', $content, $matches);
        foreach ($matches[1] as $func) {
            $funcDefs[] = ['func' => $func, 'file' => $file->getPathname()];
        }
    }
}

// Check for duplicates
$seen = [];
foreach ($funcDefs as $def) {
    if (isset($seen[$def['func']])) {
        echo "  DUPLICATE FUNCTION: {$def['func']} in {$def['file']} (also in {$seen[$def['func']]})\n";
        $errors['potential_500'][] = ['function' => $def['func'], 'reason' => 'Duplicate definition'];
    }
    $seen[$def['func']] = $def['file'];
}

// =========================================================
// 5. Check for undefined variables that cause 500 errors
// =========================================================
echo "\n--- Checking for Undefined Variable Patterns ---\n";

$criticalVariables = ['$conn', '$pageTitle', '$metaDescription', '$metaImage', '$projects', '$blogs', '$testimonials', '$packages'];
foreach ($searchDirs as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;
        $content = file_get_contents($file->getPathname());
        foreach ($criticalVariables as $var) {
            // Check if accessed before defined (simplified check)
                    if (preg_match('/' . preg_quote($var, '/') . '\s*\[/', $content) && 
                preg_match('/' . preg_quote($var, '/') . '\s*=\s*\$conn/', $content) === 0 &&
                preg_match('/fetch\w+\(' . preg_quote($var, '/') . '/', $content) === 0) {
                $pos = strpos($content, $var);
                if (strpos($content, "isset(" . $var . ")") === false && strpos($content, $var . " ??") === false) {
                    echo "  POTENTIAL UNDEFINED: $var in {$file->getFilename()}\n";
                }
            }
        }
    }
}

// =========================================================
// SUMMARY
// =========================================================
echo "\n============================\n";
echo "ANALYSIS RESULTS\n";
echo "============================\n";
echo "Missing Assets: " . count($errors['missing_assets']) . "\n";
echo "Broken References: " . count($errors['broken_references']) . "\n";
echo "Potential 500 Issues: " . count($errors['potential_500']) . "\n";

echo "\n--- ISSUES TO FIX ---\n";
if (!empty($errors['missing_assets'])) {
    echo "Missing asset files (create these):\n";
    foreach ($errors['missing_assets'] as $a) echo "  - $a\n";
}
if (!empty($errors['broken_references'])) {
    echo "Broken references:\n";
    foreach ($errors['broken_references'] as $r) echo "  - {$r['file']}: {$r['type']} (" . ($r['path'] ?? '') . ")\n";
}
if (!empty($errors['potential_500'])) {
    echo "Potential 500 errors to investigate:\n";
    foreach ($errors['potential_500'] as $e) echo "  - Function '{$e['function']}': {$e['reason']}\n";
}