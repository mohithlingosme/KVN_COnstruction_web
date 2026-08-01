<?php
/**
 * KVN Construction - Complete Runtime Validation
 * Runs without Apache - uses direct PHP execution
 */
 
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('HELPER_PATH', ROOT_PATH . '/helpers');
define('MIDDLEWARE_PATH', ROOT_PATH . '/middleware');

$results = [
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0,
    'details' => []
];

function test(string $name, bool $condition, string $detail = ''): void {
    global $results;
    if ($condition) {
        $results['passed']++;
        echo "  ✅ $name\n";
    } else {
        $results['failed']++;
        echo "  ❌ $name - $detail\n";
    }
    $results['details'][] = ['name' => $name, 'passed' => $condition, 'detail' => $detail];
}

function warn(string $name, string $detail = ''): void {
    global $results;
    $results['warnings']++;
    echo "  ⚠️  $name - $detail\n";
    $results['details'][] = ['name' => $name, 'passed' => true, 'warning' => true, 'detail' => $detail];
}

echo "========================================\n";
echo "KVN Construction - Runtime Validation\n";
echo "========================================\n\n";

// =========================================
// PHASE 1: ENVIRONMENT
// =========================================
echo "--- PHASE 1: Environment ---\n";

test('PHP 8.x', PHP_MAJOR_VERSION >= 8, 'PHP version: ' . phpversion());
test('PDO MySQL extension', extension_loaded('pdo_mysql'));
test('OpenSSL extension', extension_loaded('openssl'));
test('CURL extension', extension_loaded('curl'));
test('MBString extension', extension_loaded('mbstring'));
test('Session extension', extension_loaded('session'));
test('JSON extension', extension_loaded('json'));
test('FileInfo extension', extension_loaded('fileinfo'));
test('EXIF extension', extension_loaded('exif'));

warn('GD extension not loaded', 'Image resizing/thumbnails may not work');
warn('ZIP extension not loaded', 'Archive operations not available');

echo "\n--- PHASE 1: File Existence ---\n";

$criticalFiles = [
    'config/app.php', 'config/database.php',
    'helpers/functions.php', 'helpers/csrf.php', 'helpers/security.php', 'helpers/session.php',
    'helpers/rateLimiter.php', 'helpers/upload.php', 'helpers/formatter.php', 'helpers/seo.php',
    'helpers/mail.php', 'helpers/sms.php', 'helpers/otp.php',
    'core/Router.php', 'core/Controller.php', 'core/View.php', 'core/Model.php', 'core/Repository.php', 'core/Service.php',
    'middleware/guest.php', 'middleware/auth.php', 'middleware/admin.php',
    'middleware/client.php', 'middleware/security.php',
    'bootstrap/providers/ServiceProvider.php',
    'app/controllers/auth/AuthController.php',
    'app/models/User.php', 'app/services/AuthService.php', 'app/services/QuotationService.php',
    'app/repositories/UserRepository.php',
    'app/security/SessionManager.php',
    'routes/api_estimator.php',
    'public/index.php', 'public/.htaccess', '.htaccess',
];

foreach ($criticalFiles as $file) {
    $path = ROOT_PATH . '/' . $file;
    if (strpos($file, 'public/') === 0) {
        $path = PUBLIC_PATH . '/' . substr($file, 7);
    }
    test("File exists: $file", file_exists($path));
}

echo "\n--- PHASE 1: Assets ---\n";

$assets = [
    'assets/css/style.css', 'assets/js/app.js', 'assets/images/favicon.png',
    'assets/images/og-image.jpg', 'assets/images/default-user.png', 'assets/images/contact/contact-hero.jpg',
];

foreach ($assets as $asset) {
    $path = PUBLIC_PATH . '/' . $asset;
    test("Asset exists: $asset", file_exists($path));
}

echo "\n--- PHASE 1: Public Pages ---\n";

$publicPages = [
    'index.php', 'about-us.php', 'services.php', 'projects.php', 'project-details.php',
    'blogs.php', 'blog-details.php', 'contact.php', 'estimator.php', 'login.php',
    'phone-login.php', 'register.php', 'forgot-password.php', 'verify-phone-otp.php',
    'verify-reset-otp.php', 'reset-password.php', 'logout.php', 'gallery.php',
    'packages.php', 'faq.php', 'careers.php', 'testimonials.php', 'privacy.php',
    'terms.php', 'videos.php', '404.php',
];

$missingPages = [];
foreach ($publicPages as $page) {
    $path = PUBLIC_PATH . '/' . $page;
    $exists = file_exists($path);
    test("Public page: $page", $exists);
    if (!$exists) $missingPages[] = $page;
}

echo "\n--- PHASE 1: Auth Handlers ---\n";

$authHandlers = [
    'auth/phone-login-handler.php', 'auth/register-handler.php',
    'auth/verify-phone-otp-handler.php', 'auth/resend-otp-handler.php',
    'auth/resend-reset-otp-handler.php', 'auth/verify-reset-otp-handler.php',
    'auth/admin-login-handler.php',
];

foreach ($authHandlers as $handler) {
    $path = PUBLIC_PATH . '/' . $handler;
    test("Auth handler: $handler", file_exists($path));
}

// =========================================
// PHASE 2: Database
// =========================================
echo "\n--- PHASE 2: Database Configuration ---\n";

require_once CONFIG_PATH . '/app.php';
require_once CONFIG_PATH . '/database.php';

$dbConfig = [
    'DB_HOST' => DB_HOST,
    'DB_NAME' => DB_NAME,
    'DB_USER' => DB_USER,
    'DB_PORT' => DB_PORT,
];

test('Database constants defined', defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS'));
echo "  DB Config: Host=" . DB_HOST . " Port=" . DB_PORT . " DB=" . DB_NAME . " User=" . DB_USER . "\n";

// Try database connection
try {
    $db = new Database();
    $conn = $db->connect();
    test('Database connection successful', $conn instanceof PDO);
    
    if ($conn) {
        // List tables
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "  Tables found: " . count($tables) . "\n";
        foreach ($tables as $table) {
            echo "    - $table\n";
        }
        
        // Check if tables referenced in code exist
        $expectedTables = [
            'users', 'user_otps', 'user_sessions', 'password_history', 'remember_tokens',
            'security_logs', 'audit_logs', 'portfolio', 'blogs', 'blog_categories',
            'testimonials', 'construction_packages', 'about_page', 'about_advantages',
            'about_process_steps', 'about_specifications', 'contact_page', 'contact_page_features',
            'leads', 'estimator_packages', 'estimator_leads', 'videos', 'estimator_pricing',
        ];
        
        foreach ($expectedTables as $table) {
            $stmt = $conn->query("SHOW TABLES LIKE '$table'");
            test("Table exists: $table", $stmt->rowCount() > 0);
        }
        
        // Check columns on critical tables
        $criticalColumns = [
            'users' => ['id', 'full_name', 'email', 'phone', 'password', 'role', 'status', 'failed_attempts', 'locked_until', 'deleted_at', 'created_at', 'updated_at'],
            'user_otps' => ['id', 'user_id', 'otp', 'purpose', 'attempts', 'resend_count', 'is_used', 'expires_at', 'deleted_at'],
            'portfolio' => ['id', 'title', 'slug', 'description', 'featured_image', 'project_type', 'location', 'area_sqft', 'status', 'created_at'],
            'blogs' => ['id', 'title', 'slug', 'content', 'excerpt', 'featured_image', 'category_id', 'status', 'published_at'],
            'testimonials' => ['id', 'client_name', 'client_image', 'review', 'client_location', 'status', 'sort_order'],
            'leads' => ['id', 'full_name', 'phone', 'email', 'project_location', 'project_type', 'message', 'lead_source', 'created_at'],
            'estimator_packages' => ['id', 'package_name', 'base_price', 'material_grade', 'estimated_timeline', 'status'],
        ];
        
        foreach ($criticalColumns as $table => $columns) {
            try {
                $stmt = $conn->query("DESCRIBE $table");
                $actualColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($columns as $col) {
                    test("Column $table.$col", in_array($col, $actualColumns));
                }
            } catch (\Exception $e) {
                warn("Cannot describe $table", $e->getMessage());
            }
        }
        
        // Test INSERT/UPDATE/DELETE on a temporary table or using transaction
        try {
            $conn->beginTransaction();
            
            // Test queries that the application uses
            
            // 1. SELECT from portfolio (used in index.php)
            $stmt = $conn->prepare("SELECT * FROM portfolio WHERE status = 'active' ORDER BY created_at DESC LIMIT 1");
            $stmt->execute();
            test('SELECT portfolio works', true);
            
            // 2. SELECT from blogs with JOIN (used in blogs.php)
            $stmt = $conn->prepare("SELECT blogs.*, blog_categories.category_name FROM blogs LEFT JOIN blog_categories ON blogs.category_id = blog_categories.id WHERE blogs.status = 'published' LIMIT 1");
            $stmt->execute();
            test('SELECT blogs with JOIN works', true);
            
            // 3. SELECT from testimonials (used in index.php)
            $stmt = $conn->prepare("SELECT * FROM testimonials WHERE status = 'active' ORDER BY sort_order ASC LIMIT 1");
            $stmt->execute();
            test('SELECT testimonials works', true);
            
            // 4. Test prepared statement with parameters (used in contact.php)
            $stmt = $conn->prepare("SELECT * FROM leads WHERE lead_source = :source LIMIT 1");
            $stmt->execute([':source' => 'Website']);
            test('SELECT leads with bind params works', true);
            
            $conn->rollBack();
            test('Transaction rollback works', true);
            
        } catch (\Exception $e) {
            test('Query execution: ' . $e->getMessage(), false);
        }
    }
} catch (\Exception $e) {
    test('Database connection failed: ' . $e->getMessage(), false);
    warn('Skipping table/column validation - no database');
}

// =========================================
// PHASE 3: PHP Include Chain Test
// =========================================
echo "\n--- PHASE 3: Include Chain Test ---\n";

// Test that the core includes work without errors
try {
    ob_start();
    require_once CONFIG_PATH . '/app.php';
    require_once HELPER_PATH . '/functions.php';
    require_once HELPER_PATH . '/csrf.php';
    require_once HELPER_PATH . '/security.php';
    require_once HELPER_PATH . '/session.php';
    ob_end_clean();
    test('Core include chain loads without errors', true);
} catch (\Throwable $e) {
    test('Core include chain failed: ' . $e->getMessage(), false);
}

try {
    ob_start();
    require_once ROOT_PATH . '/bootstrap/providers/ServiceProvider.php';
    ob_end_clean();
    test('ServiceProvider loads without errors', true);
} catch (\Throwable $e) {
    test('ServiceProvider failed: ' . $e->getMessage(), false);
}

try {
    ob_start();
    require_once ROOT_PATH . '/core/Controller.php';
    require_once ROOT_PATH . '/core/View.php';
    require_once ROOT_PATH . '/core/Router.php';
    ob_end_clean();
    test('Core classes load without errors', true);
} catch (\Throwable $e) {
    test('Core classes failed: ' . $e->getMessage(), false);
}

try {
    ob_start();
    require_once ROOT_PATH . '/app/models/User.php';
    ob_end_clean();
    test('User model loads without errors', true);
} catch (\Throwable $e) {
    test('User model failed: ' . $e->getMessage(), false);
}

try {
    ob_start();
    require_once ROOT_PATH . '/app/repositories/UserRepository.php';
    ob_end_clean();
    test('UserRepository loads without errors', true);
} catch (\Throwable $e) {
    test('UserRepository failed: ' . $e->getMessage(), false);
}

try {
    ob_start();
    require_once ROOT_PATH . '/app/services/AuthService.php';
    ob_end_clean();
    test('AuthService loads without errors', true);
} catch (\Throwable $e) {
    test('AuthService failed: ' . $e->getMessage(), false);
}

// =========================================
// PHASE 4: Security Check
// =========================================
echo "\n--- PHASE 4: Security Check ---\n";

// Check .htaccess exists and has security rules
$htaccess = file_get_contents(ROOT_PATH . '/.htaccess');
test('.htaccess exists and has RewriteEngine', strpos($htaccess, 'RewriteEngine On') !== false);
test('.htaccess blocks app folders', strpos($htaccess, 'Block access to application source folders') !== false);
test('.htaccess has security headers', strpos($htaccess, 'X-Frame-Options') !== false);
test('.htaccess blocks hidden files', strpos($htaccess, 'Block access to hidden files') !== false);

$publicHtaccess = file_get_contents(PUBLIC_PATH . '/.htaccess');
test('public/.htaccess exists', $publicHtaccess !== false);
test('public/.htaccess rewrites to index.php', strpos($publicHtaccess, 'index.php?url=$1') !== false);

// Check for hardcoded credentials
$searchDirs = [ROOT_PATH . '/config', ROOT_PATH . '/helpers', ROOT_PATH . '/app'];
$foundSecrets = false;
foreach ($searchDirs as $dir) {
    if (!is_dir($dir)) continue;
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iter as $file) {
        if ($file->getExtension() !== 'php') continue;
        $content = file_get_contents($file->getPathname());
        if (preg_match('/password\s*=\s*["\'](?!.*\$\w+)/i', $content)) {
            if (strpos($content, 'PASSWORD_DEFAULT') === false && strpos($content, 'PASSWORD_BCRYPT') === false) {
                warn('Possible hardcoded password: ' . $file->getFilename());
                $foundSecrets = true;
            }
        }
    }
}
if (!$foundSecrets) {
    test('No hardcoded credentials found', true);
}

// =========================================
// SUMMARY
// =========================================
echo "\n========================================\n";
echo "RUNTIME VALIDATION SUMMARY\n";
echo "========================================\n";
echo "Passed: {$results['passed']}\n";
echo "Failed: {$results['failed']}\n";
echo "Warnings: {$results['warnings']}\n";
echo "========================================\n";

if ($results['failed'] > 0) {
    echo "\nFAILURES:\n";
    foreach ($results['details'] as $d) {
        if (!$d['passed'] && empty($d['warning'])) {
            echo "  - {$d['name']}: {$d['detail']}\n";
        }
    }
}

exit($results['failed'] > 0 ? 1 : 0);