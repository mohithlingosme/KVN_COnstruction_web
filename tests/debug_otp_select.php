<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/fixtures/otp_sqlite_fixture.php';

$pdo = new PDO(getenv('TEST_DSN') ?: 'sqlite::memory:');
buildOtpFixture($pdo);

$rows = $pdo->query("SELECT id, purpose, expires_at, attempts, is_used FROM user_otps ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

echo "Seeded user_otps (id desc):\n";
foreach ($rows as $r) {
    echo json_encode($r, JSON_UNESCAPED_SLASHES) . "\n";
}

echo "\nNow() comparison probe:\n";
$nowRow = $pdo->query('SELECT NOW() as now_value')->fetch(PDO::FETCH_ASSOC);
echo 'NOW() => ' . ($nowRow['now_value'] ?? '') . "\n";

$phone = '9999999999';
$stmt = $pdo->prepare('SELECT * FROM users WHERE phone = :phone AND deleted_at IS NULL LIMIT 1');
$stmt->execute([':phone' => trim($phone)]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "\nUser => " . json_encode($user, JSON_UNESCAPED_SLASHES) . "\n";

$verifyQuery = "
    SELECT *
    FROM user_otps
    WHERE user_id = :user_id
      AND purpose = 'login'
      AND is_used = 0
      AND expires_at > NOW()
    ORDER BY id DESC
    LIMIT 1
";

$otpStmt = $pdo->prepare($verifyQuery);
$otpStmt->execute([':user_id' => (int)($user['id'] ?? 0)]);
$otpRow = $otpStmt->fetch(PDO::FETCH_ASSOC);

echo "\nVerify selected otpRow => " . json_encode($otpRow, JSON_UNESCAPED_SLASHES) . "\n";

