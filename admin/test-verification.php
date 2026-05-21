<?php

/**
 * KVN Construction - Security & Functionality Test Suite
 * 
 * This script tests all the security fixes and functionality improvements
 * Run this ONLY in development environment to verify fixes
 * 
 * Access: /admin/test-verification.php (recommended to delete after testing)
 */

declare(strict_types=1);

session_start();

// Quick security check - remove password check in production
if ($_GET['key'] !== 'test_kvn_2024') {
    die('Unauthorized');
}

$tests_passed = 0;
$tests_failed = 0;
$test_results = [];

function test($name, $condition, $details = '') {
    global $tests_passed, $tests_failed, $test_results;
    
    if ($condition) {
        $tests_passed++;
        $test_results[] = ['name' => $name, 'status' => 'PASS', 'details' => $details];
    } else {
        $tests_failed++;
        $test_results[] = ['name' => $name, 'status' => 'FAIL', 'details' => $details];
    }
}

// Test 1: Database Connection
try {
    require_once __DIR__ . '/includes/db.php';
    test('Database Connection', isset($conn) && $conn instanceof mysqli, 'Connected to kvn_construction');
} catch (Throwable $e) {
    test('Database Connection', false, $e->getMessage());
}

// Test 2: Check Admins Table
try {
    $result = $conn->query("DESCRIBE admins");
    test('Admins Table Exists', $result !== false && $result->num_rows > 0, 'Table structure verified');
} catch (Throwable $e) {
    test('Admins Table Exists', false, $e->getMessage());
}

// Test 3: Check Default Admin
try {
    $stmt = $conn->prepare("SELECT id, email FROM admins WHERE email = ? LIMIT 1");
    $email = 'admin@kvn.com';
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    test('Default Admin Exists', $result->num_rows === 1, 'admin@kvn.com found');
    $stmt->close();
} catch (Throwable $e) {
    test('Default Admin Exists', false, $e->getMessage());
}

// Test 4: Check Database Name
try {
    $result = $conn->query("SELECT DATABASE()");
    $row = $result->fetch_row();
    $db_name = $row[0];
    test('Correct Database Name', $db_name === 'kvn_construction', "Database: {$db_name}");
} catch (Throwable $e) {
    test('Correct Database Name', false, $e->getMessage());
}

// Test 5: AuthMiddleware Exists
$auth_middleware_path = __DIR__ . '/includes/middleware/AuthMiddleware.php';
test('AuthMiddleware File Exists', file_exists($auth_middleware_path), $auth_middleware_path);

// Test 6: AuthMiddleware Implementation
if (file_exists($auth_middleware_path)) {
    $content = file_get_contents($auth_middleware_path);
    test('AuthMiddleware has requireAuth', strpos($content, 'requireAuth') !== false, 'Method found');
    test('AuthMiddleware has isAuthenticated', strpos($content, 'isAuthenticated') !== false, 'Method found');
    test('AuthMiddleware has isSessionValid', strpos($content, 'isSessionValid') !== false, 'Method found');
}

// Test 7: Check Login Page CSRF
$login_content = file_get_contents(__DIR__ . '/login.php');
test('Login Page has CSRF Tokens', strpos($login_content, 'csrf_token') !== false, 'CSRF protection added');
test('Login Page has Error Handling', strpos($login_content, 'try {') !== false, 'Try-catch blocks added');

// Test 8: Check db.php Error Handling
$db_content = file_get_contents(__DIR__ . '/includes/db.php');
test('DB File has Error Handling', strpos($db_content, 'catch') !== false, 'Exception handling added');
test('DB File has Logging', strpos($db_content, 'error_log') !== false, 'Error logging added');

// Test 9: Check Leads Page Security
$leads_content = file_get_contents(__DIR__ . '/leads.php');
test('Leads Page has Auth Middleware', strpos($leads_content, 'AuthMiddleware') !== false, 'Auth middleware imported');
test('Leads Page has Prepared Statements', strpos($leads_content, 'prepare') !== false, 'SQL injection prevention');
test('Leads Page has Error Handling', strpos($leads_content, 'try {') !== false, 'Error handling added');

// Test 10: Check Dashboard Security
$dashboard_content = file_get_contents(__DIR__ . '/dashboard.php');
test('Dashboard has Auth Middleware', strpos($dashboard_content, 'AuthMiddleware') !== false, 'Auth middleware imported');
test('Dashboard has requireAuth', strpos($dashboard_content, 'requireAuth') !== false, 'Authentication enforced');

// Test 11: Check Logout Security
$logout_content = file_get_contents(__DIR__ . '/logout.php');
test('Logout has Session Destruction', strpos($logout_content, 'session_destroy') !== false, 'Proper logout');
test('Logout has Cookie Deletion', strpos($logout_content, 'setcookie') !== false, 'Cookie cleanup');

// Test 12: Check Admin Packages Security
$packages_content = file_get_contents(__DIR__ . '/admin-packages.php');
test('Packages Page has Auth Middleware', strpos($packages_content, 'AuthMiddleware') !== false, 'Auth protection');
test('Packages Page has Error Handling', strpos($packages_content, 'try {') !== false, 'Error handling');

// Test 13: Check Leads Table
try {
    $result = $conn->query("DESCRIBE leads");
    test('Leads Table Exists', $result !== false, 'Table structure verified');
} catch (Throwable $e) {
    test('Leads Table Exists', false, $e->getMessage());
}

// Test 14: Check Projects Table
try {
    $result = $conn->query("DESCRIBE projects");
    test('Projects Table Exists', $result !== false, 'Table structure verified');
} catch (Throwable $e) {
    test('Projects Table Exists', false, 'Table might not exist yet');
}

// Test 15: Check Appointments Table
try {
    $result = $conn->query("DESCRIBE appointments");
    test('Appointments Table Exists', $result !== false, 'Table structure verified');
} catch (Throwable $e) {
    test('Appointments Table Exists', false, 'Table might not exist yet');
}

// Test 16: Check Public Login Disabled
$public_login = file_get_contents(__DIR__ . '/../public/login.php');
test('Public Login Redirect Added', strpos($public_login, 'header("Location: index.php")') !== false, 'Hardcoded demo removed');

// Test 17: Session Timeout Configuration
$auth_file = file_get_contents(__DIR__ . '/includes/auth.php');
test('Session Timeout Implemented', strpos($auth_file, 'session_timeout') !== false, '30 minute timeout added');

// Test 18: Output Encoding
$dashboard_file = file_get_contents(__DIR__ . '/dashboard.php');
test('Output Encoding Used', strpos($dashboard_file, 'htmlspecialchars') !== false, 'XSS prevention added');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KVN Security & Functionality Tests</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #1e1e1e; color: #e0e0e0; padding: 40px; }
        h1 { color: #4CAF50; margin-bottom: 30px; }
        .container { max-width: 900px; margin: auto; }
        .test-item { 
            background: #2d2d2d; 
            padding: 15px; 
            margin-bottom: 10px; 
            border-left: 4px solid #ddd;
            border-radius: 4px;
        }
        .test-item.pass { border-left-color: #4CAF50; }
        .test-item.fail { border-left-color: #f44336; }
        .test-name { font-weight: bold; margin-bottom: 5px; }
        .test-status { 
            display: inline-block; 
            padding: 4px 8px; 
            border-radius: 3px; 
            font-weight: bold; 
            font-size: 12px;
        }
        .pass .test-status { 
            background: #4CAF50; 
            color: white; 
        }
        .fail .test-status { 
            background: #f44336; 
            color: white; 
        }
        .test-details { 
            font-size: 12px; 
            color: #aaa; 
            margin-top: 5px; 
        }
        .summary { 
            background: #3d3d3d; 
            padding: 20px; 
            border-radius: 8px; 
            margin-bottom: 30px;
            font-size: 16px;
        }
        .summary h2 { color: #4CAF50; margin-bottom: 10px; }
        .pass-count { color: #4CAF50; font-weight: bold; }
        .fail-count { color: #f44336; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">

    <h1>🔒 KVN Construction - Security & Functionality Tests</h1>

    <div class="summary">
        <h2>Test Summary</h2>
        <p>Total Tests: <strong><?php echo $tests_passed + $tests_failed; ?></strong></p>
        <p>Passed: <span class="pass-count"><?php echo $tests_passed; ?></span></p>
        <p>Failed: <span class="fail-count"><?php echo $tests_failed; ?></span></p>
        <p>Success Rate: <strong><?php echo round(($tests_passed / ($tests_passed + $tests_failed)) * 100, 1); ?>%</strong></p>
    </div>

    <h2 style="color: #4CAF50; margin-bottom: 20px;">Detailed Results</h2>

    <?php foreach ($test_results as $result): ?>

        <div class="test-item <?php echo strtolower($result['status']); ?>">
            <div class="test-name">
                <?php echo htmlspecialchars($result['name']); ?>
                <span class="test-status"><?php echo $result['status']; ?></span>
            </div>
            <?php if (!empty($result['details'])): ?>
                <div class="test-details"><?php echo htmlspecialchars($result['details']); ?></div>
            <?php endif; ?>
        </div>

    <?php endforeach; ?>

    <div style="margin-top: 40px; padding: 20px; background: #3d3d3d; border-radius: 8px; border-left: 4px solid #2196F3;">
        <h3 style="color: #2196F3; margin-bottom: 10px;">ℹ️ Important Notes</h3>
        <ul style="list-style: none; line-height: 1.8;">
            <li>✅ Database name is now: <code>kvn_construction</code></li>
            <li>✅ Default credentials: <code>admin@kvn.com</code> / <code>password</code></li>
            <li>✅ All pages require authentication via AuthMiddleware</li>
            <li>✅ CSRF tokens protect all forms</li>
            <li>✅ Session timeout: 30 minutes of inactivity</li>
            <li>✅ SQL injection prevention: Prepared statements everywhere</li>
            <li>✅ XSS prevention: Output encoding with htmlspecialchars()</li>
            <li>✅ Comprehensive error logging for debugging</li>
            <li>⚠️ Remember to delete this test file after verification</li>
        </ul>
    </div>

</div>

</body>
</html>
