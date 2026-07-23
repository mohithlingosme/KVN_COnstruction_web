<?php
session_start();

$method = $argv[1] ?? 'GET';
$action = $argv[2] ?? '';
$payload = $argv[3] ?? '{}';
// Arguments are parsed by cmd.exe on Windows, where single quotes do not
// protect JSON. Tests therefore pass the request body as base64.
$decodedPayload = base64_decode($payload, true);
if ($decodedPayload !== false) {
    $payload = $decodedPayload;
}
$csrfFlag = $argv[4] ?? 'valid';
$ip = $argv[5] ?? '127.0.0.1';

$_SERVER['REQUEST_METHOD'] = $method;
$_GET['action'] = $action;
$_SERVER['REMOTE_ADDR'] = $ip;
$_POST = json_decode($payload, true) ?? [];

// Disable display_errors so warnings don't corrupt JSON output
ini_set('display_errors', '0');
// Suppress constant redefinition warnings (PHP 8: E_WARNING) when app.php re-defines CONFIG_PATH etc.
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
define('CONFIG_PATH', __DIR__ . '/Fakes');
require_once __DIR__ . '/../config/app.php';
error_reporting(E_ALL);

// Mock DB - create in-memory SQLite tables
$pdo = new PDO('sqlite::memory:');
$pdo->sqliteCreateFunction('NOW', static fn (): string => gmdate('Y-m-d H:i:s'), 0);
$pdo->exec("CREATE TABLE estimator_packages (id INTEGER, package_name TEXT, base_price REAL, material_grade TEXT, estimated_timeline TEXT, description TEXT, features TEXT, status TEXT)");
$pdo->exec("INSERT INTO estimator_packages VALUES (1, 'Basic', 1500, 'Standard', '6 months', 'Basic package', '[]', 'Active')");
$pdo->exec("CREATE TABLE estimator_leads (id INTEGER, full_name TEXT, phone TEXT, email TEXT, location TEXT, plot_area REAL, floors INTEGER, package_id INTEGER, estimated_cost REAL, ip_address TEXT, created_at TEXT)");
$pdo->exec("CREATE TABLE IF NOT EXISTS rate_limits (identifier TEXT, action_type TEXT, route_name TEXT, attempts INTEGER DEFAULT 0, blocked_until TEXT, updated_at TEXT, created_at TEXT)");

$GLOBALS['conn'] = $pdo;

require_once __DIR__ . '/../helpers/csrf.php';

if ($csrfFlag === 'valid') {
    $_SERVER['HTTP_X_CSRF_TOKEN'] = generateCsrfToken();
} else {
    $_SERVER['HTTP_X_CSRF_TOKEN'] = 'invalid_token';
}

// Use a shutdown function to capture and JSON-wrap output even when exit() is called
$capturedOutput = '';
$shutdownRan = false;

register_shutdown_function(function () use (&$capturedOutput, &$shutdownRan) {
    $shutdownRan = true;
    // Get any buffered output
    if (ob_get_level()) {
        $capturedOutput = ob_get_clean();
    }
    // If output is not valid JSON, wrap it
    $decoded = json_decode($capturedOutput, true);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'success' => false,
            'message' => trim($capturedOutput),
            'raw' => $capturedOutput
        ]);
    } else {
        print $capturedOutput;
    }
});

// Clean the buffer started by config/app.php
if (ob_get_level()) {
    ob_clean();
}
ob_start();
require __DIR__ . '/../routes/api_estimator.php';
// If exit() wasn't called, capture output now
if (!$shutdownRan) {
    $capturedOutput = ob_get_clean();
    $decoded = json_decode($capturedOutput, true);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'success' => false,
            'message' => trim($capturedOutput),
            'raw' => $capturedOutput
        ]);
    } else {
        print $capturedOutput;
    }
}
