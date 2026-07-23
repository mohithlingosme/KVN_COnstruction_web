<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/fixtures/otp_sqlite_fixture.php';

$pdo = new PDO(getenv('TEST_DSN') ?: 'sqlite::memory:');
buildOtpFixture($pdo);

$rows = $pdo->query('SELECT id, purpose, expires_at, attempts, is_used, otp FROM user_otps ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);

echo "Rows (ASC):\n";
foreach ($rows as $r) {
    echo json_encode(['id'=>$r['id'],'purpose'=>$r['purpose'],'expires_at'=>$r['expires_at'],'attempts'=>$r['attempts'],'is_used'=>$r['is_used']], JSON_UNESCAPED_SLASHES) . "\n";
}

$now = $pdo->query('SELECT NOW() AS now_value')->fetch(PDO::FETCH_ASSOC);
echo "\nNOW() => " . ($now['now_value'] ?? '') . "\n";

