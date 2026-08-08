<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/controllers/AuthController.php';

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'auth-verify';
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$results = [];
function chk(string $label, bool $ok, array &$results, string $detail = ''): void {
    $results[] = ['label' => $label, 'ok' => $ok, 'detail' => $detail];
}

// Admin credentials from system seed (first admin user). Find via DB.
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT email FROM users WHERE role IN ('admin','super_admin') AND status='active' ORDER BY id ASC LIMIT 1");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
chk('Seeded admin user exists', (bool)$admin, $results, $admin ? $admin['email'] : 'none');

// The seed admin password is not known; check that the AuthController facade
// instantiates and that the wrong-password path returns a controlled error
// (proving the facade + AuthService chain resolves without class-not-found).
$controller = new AuthController();
chk('AuthController facade instantiates', true, $results);

if ($admin) {
    $res = $controller->adminLogin($admin['email'], 'definitely-wrong-password');
    chk('adminLogin returns controlled status:false on bad creds', isset($res['status']) && $res['status'] === false, $results, $res['message'] ?? '');
    chk('adminLogin message present', !empty($res['message']), $results, $res['message'] ?? '');
}

// phone-login facade
$res2 = $controller->sendLoginOtp('9999999999');
chk('sendLoginOtp returns controlled status (phone not found => false)', $res2['status'] === false, $results, $res2['message'] ?? '');

echo "=== Auth Controller Facade Verification ===\n";
$pass = 0;
foreach ($results as $r) {
    $mark = $r['ok'] ? 'PASS' : 'FAIL';
    if ($r['ok']) $pass++;
    printf("  [%s] %s%s\n", $mark, $r['label'], $r['detail'] !== '' ? ' — ' . $r['detail'] : '');
}
printf("\nTotal: %d/%d passed\n", $pass, count($results));
exit($pass === count($results) ? 0 : 1);
