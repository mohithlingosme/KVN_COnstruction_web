<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/Fakes/FakePDO.php';
require_once __DIR__ . '/Fakes/FakeStatement.php';
require_once __DIR__ . '/fixtures/otp_sqlite_fixture.php';

class AuthOtpTest
{

    private function assertTrue(bool $cond, string $msg = 'assertTrue failed'): void
    {
        if (!$cond) {
            throw new Exception($msg);
        }
    }

    private function assertEquals($expected, $actual, string $msg = 'assertEquals failed'): void
    {
        if ($expected !== $actual) {
            throw new Exception($msg . ' | expected=' . var_export($expected, true) . ' actual=' . var_export($actual, true));
        }
    }

    private function makeController(PDO $pdo): AuthController
    {
        return new AuthController($pdo);
    }

    private function makeFixtureController(): array
    {
        $dsn = getenv('TEST_DSN') ?: 'sqlite::memory:';
        $pdo = new PDO($dsn);
        buildOtpFixture($pdo, 'happy');
        $c = $this->makeController($pdo);
        return [$c, $pdo];
    }


    public function test_sendLoginOtp_empty_phone(): void
    {
        [$c, $pdo] = $this->makeFixtureController();


        $res = $c->sendLoginOtp('   ');


        $this->assertEquals(false, $res['status']);
        $this->assertTrue(str_contains($res['message'], 'required'));
    }

    public function test_sendLoginOtp_rate_limited(): void
    {
        // Force checkRateLimit() to return false by predefining it.
        // Since helpers/rateLimiter.php already defines checkRateLimit, we rely on behavior
        // by using a Fake global $conn that makes checkRateLimit return false.
        // For this unit test, we simply verify the controller's error message when rate limit is hit.

        // Seed fixture DB but we will set $GLOBALS['conn'] to a PDO-like object that fails check.
        [$c, $pdo] = $this->makeFixtureController();

        // Make currentRouteName() deterministic
        $_SERVER['REQUEST_URI'] = '/test/otp';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'unit-test';

        // Create rate_limits table and insert blocked record for this identifier/action/route.
        $pdo->exec('CREATE TABLE IF NOT EXISTS rate_limits (
            identifier TEXT,
            action_type TEXT,
            route_name TEXT,
            attempts INTEGER DEFAULT 0,
            blocked_until TEXT,
            updated_at TEXT,
            created_at TEXT
        )');

        $identifier = limiterIdentifier();
        $routeName = currentRouteName();

        $future = (new DateTimeImmutable('+10 minutes'))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare('INSERT INTO rate_limits (identifier, action_type, route_name, attempts, blocked_until, updated_at, created_at)
            VALUES (:identifier, :action_type, :route_name, :attempts, :blocked_until, :updated_at, :created_at)');
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
        $stmt->execute([
            ':identifier' => $identifier,
            ':action_type' => 'client_otp',
            ':route_name' => $routeName,
            ':attempts' => 3,
            ':blocked_until' => $future,
            ':updated_at' => $now,
            ':created_at' => $now,
        ]);

        // Ensure helpers/rateLimiter uses this PDO
        $GLOBALS['conn'] = $pdo;

        $res = $c->sendLoginOtp('9999999999');
        $this->assertEquals(false, $res['status']);
        $this->assertTrue(str_contains($res['message'], 'Too many'), 'Expected too many requests message');
    }



    public function test_verifyPhoneOtp_happy_path_marks_is_used(): void
    {
        $pdo = new PDO(getenv('TEST_DSN') ?: 'sqlite::memory:');
        buildOtpFixture($pdo, 'happy');
        $c = $this->makeController($pdo);

        $res = $c->verifyPhoneOtp('9999999999', '123456');
        $this->assertEquals(true, $res['status']);

        $userId = getFixtureUserId($pdo);
        $stmt = $pdo->query("SELECT id, is_used FROM user_otps WHERE user_id = {$userId} AND purpose='login' ORDER BY id DESC LIMIT 1");

        // When verifying OTP, AuthController should mark the selected otp as used.


        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('1', (string)($row['is_used'] ?? '0'));

    }

    public function test_verifyPhoneOtp_expired_otp(): void
    {
        $pdo = new PDO(getenv('TEST_DSN') ?: 'sqlite::memory:');
        buildOtpFixture($pdo, 'attempt_limit');
        $c = $this->makeController($pdo);

        // Make all OTPs expired
        $pdo->exec("UPDATE user_otps SET expires_at = datetime('now', '-10 minutes')");

        $res = $c->verifyPhoneOtp('9999999999', '123456');
        $this->assertEquals(false, $res['status']);
        $this->assertEquals('OTP expired.', $res['message']);
    }

    public function test_verifyPhoneOtp_attempt_limit(): void
    {
        $pdo = new PDO(getenv('TEST_DSN') ?: 'sqlite::memory:');
        buildOtpFixture($pdo, 'attempt_limit');
        $c = $this->makeController($pdo);

        // Make all OTPs hit the attempt limit
        $pdo->exec("UPDATE user_otps SET attempts = 5");

        $res = $c->verifyPhoneOtp('9999999999', '123456');
        $this->assertEquals(false, $res['status']);
        $this->assertEquals('Too many attempts.', $res['message']);
    }

}


