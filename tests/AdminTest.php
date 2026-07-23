<?php

declare(strict_types=1);

class AdminTest
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

    /* =========================================================
       AdminController::dashboard() tests
    ========================================================= */

    public function test_admin_dashboard_returns_counts(): void
    {
        $pdo = new PDO('sqlite::memory:');

        // Create tables that AdminController dashboard queries
        $pdo->exec('CREATE TABLE users (id INTEGER, full_name TEXT, status TEXT, deleted_at TEXT)');
        $pdo->exec('CREATE TABLE projects (id INTEGER, deleted_at TEXT)');
        $pdo->exec('CREATE TABLE blogs (id INTEGER, status TEXT)');
        $pdo->exec('CREATE TABLE testimonials (id INTEGER, status TEXT)');
        $pdo->exec('CREATE TABLE quotations (id INTEGER)');
        $pdo->exec('CREATE TABLE estimator_leads (id INTEGER)');
        $pdo->exec('CREATE TABLE leads (id INTEGER, name TEXT, status TEXT, assigned_to INTEGER, created_at TEXT)');

        // Seed data
        $pdo->exec("INSERT INTO users (id, status) VALUES (1, 'active')");
        $pdo->exec("INSERT INTO users (id, status) VALUES (2, 'inactive')");
        $pdo->exec("INSERT INTO projects (id, deleted_at) VALUES (1, NULL)");
        $pdo->exec("INSERT INTO projects (id, deleted_at) VALUES (2, NULL)");
        $pdo->exec("INSERT INTO blogs (id, status) VALUES (1, 'published')");
        $pdo->exec("INSERT INTO testimonials (id, status) VALUES (1, 'approved')");
        $pdo->exec("INSERT INTO quotations (id) VALUES (1)");
        $pdo->exec("INSERT INTO estimator_leads (id) VALUES (1)");
        $pdo->exec("INSERT INTO leads (id, name, status) VALUES (1, 'Test Lead', 'new')");

        $controller = new AdminController($pdo);
        $data = $controller->dashboard();

        $this->assertTrue(isset($data['totalUsers']), 'totalUsers key missing');
        $this->assertTrue(isset($data['totalProjects']), 'totalProjects key missing');
        $this->assertTrue(isset($data['totalBlogs']), 'totalBlogs key missing');
        $this->assertTrue(isset($data['totalTestimonials']), 'totalTestimonials key missing');
        $this->assertTrue(isset($data['totalQuotations']), 'totalQuotations key missing');
        $this->assertTrue(isset($data['totalEstimatorRequests']), 'totalEstimatorRequests key missing');
        $this->assertTrue(isset($data['totalLeads']), 'totalLeads key missing');
        $this->assertTrue(isset($data['recentLeads']), 'recentLeads key missing');
        $this->assertTrue(isset($data['recentProjects']), 'recentProjects key missing');
        $this->assertTrue(isset($data['recentBlogs']), 'recentBlogs key missing');

        $this->assertEquals(2, $data['totalUsers'], 'totalUsers should be 2');
        $this->assertEquals(2, $data['totalProjects'], 'totalProjects should be 2');
        $this->assertEquals(1, $data['totalBlogs'], 'totalBlogs should be 1');
        $this->assertEquals(1, $data['totalTestimonials'], 'totalTestimonials should be 1');
        $this->assertEquals(1, $data['totalQuotations'], 'totalQuotations should be 1');
        $this->assertEquals(1, $data['totalEstimatorRequests'], 'totalEstimatorRequests should be 1');

        $this->assertTrue(is_array($data['recentLeads']), 'recentLeads should be array');
        $this->assertTrue(is_array($data['recentProjects']), 'recentProjects should be array');
        $this->assertTrue(is_array($data['recentBlogs']), 'recentBlogs should be array');
    }



    public function test_admin_dashboard_empty_db(): void
    {
        $pdo = new PDO('sqlite::memory:');

        // Create empty tables
        $pdo->exec('CREATE TABLE users (id INTEGER, full_name TEXT, status TEXT, deleted_at TEXT)');
        $pdo->exec('CREATE TABLE projects (id INTEGER, deleted_at TEXT)');
        $pdo->exec('CREATE TABLE blogs (id INTEGER, status TEXT)');
        $pdo->exec('CREATE TABLE testimonials (id INTEGER, status TEXT)');
        $pdo->exec('CREATE TABLE quotations (id INTEGER)');
        $pdo->exec('CREATE TABLE estimator_leads (id INTEGER)');
        $pdo->exec('CREATE TABLE leads (id INTEGER, name TEXT, status TEXT, assigned_to INTEGER, created_at TEXT)');

        $controller = new AdminController($pdo);
        $data = $controller->dashboard();

        $this->assertEquals(0, $data['totalUsers']);
        $this->assertEquals(0, $data['totalProjects']);
        $this->assertEquals(0, $data['totalBlogs']);
        $this->assertEquals(0, $data['totalTestimonials']);
        $this->assertEquals(0, $data['totalQuotations']);
        $this->assertEquals(0, $data['totalEstimatorRequests']);
        $this->assertEquals(0, $data['totalLeads']);
    }

    public function test_admin_dashboard_getLatest_invalid_table(): void
    {
        // Directly test the private getLatest method via reflection
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE users (id INTEGER, title TEXT, name TEXT, status TEXT, created_at TEXT, updated_at TEXT)');
        $pdo->exec("INSERT INTO users (id, title, name, status) VALUES (1, 'Test', 'User', 'active')");

        $controller = new AdminController($pdo);
        $ref = new ReflectionMethod($controller, 'getLatest');
        $ref->setAccessible(true);

        // Non-whitelisted table should return empty array
        $result = $ref->invoke($controller, 'nonexistent_table', 5);
        $this->assertEquals([], $result);
    }

    /* =========================================================
       AuthController::adminLogin() tests
    ========================================================= */

    public function test_adminLogin_empty_credentials(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE IF NOT EXISTS rate_limits (identifier TEXT, action_type TEXT, route_name TEXT, attempts INTEGER DEFAULT 0, blocked_until TEXT, updated_at TEXT, created_at TEXT)');
        // users table needed by User model
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, phone TEXT, email TEXT, full_name TEXT, status TEXT, role TEXT, password TEXT, locked_until TEXT, failed_attempts INTEGER DEFAULT 0, deleted_at TEXT, created_at TEXT, updated_at TEXT, last_login TEXT)");

        if (function_exists('registerSqliteNow')) {
            registerSqliteNow($pdo);
        }

        $GLOBALS['conn'] = $pdo;
        $_SERVER['REQUEST_URI'] = '/admin/login';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'unit-test';

        $controller = new AuthController($pdo);
        $res = $controller->adminLogin('', '');

        $this->assertEquals(false, $res['status']);
        $this->assertTrue(str_contains($res['message'], 'credentials'), 'Expected credentials message');
    }

    public function test_adminLogin_invalid_email(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE IF NOT EXISTS rate_limits (identifier TEXT, action_type TEXT, route_name TEXT, attempts INTEGER DEFAULT 0, blocked_until TEXT, updated_at TEXT, created_at TEXT)');
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, phone TEXT, email TEXT, full_name TEXT, status TEXT, role TEXT, password TEXT, locked_until TEXT, failed_attempts INTEGER DEFAULT 0, deleted_at TEXT, created_at TEXT, updated_at TEXT, last_login TEXT)");

        if (function_exists('registerSqliteNow')) {
            registerSqliteNow($pdo);
        }

        $GLOBALS['conn'] = $pdo;
        $_SERVER['REQUEST_URI'] = '/admin/login';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'unit-test';

        $controller = new AuthController($pdo);
        $res = $controller->adminLogin('nonexistent@example.com', 'password123');

        $this->assertEquals(false, $res['status']);
        $this->assertTrue(str_contains($res['message'], 'credentials'), 'Expected credentials message');
    }

    public function test_adminLogin_success(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE IF NOT EXISTS rate_limits (identifier TEXT, action_type TEXT, route_name TEXT, attempts INTEGER DEFAULT 0, blocked_until TEXT, updated_at TEXT, created_at TEXT)');
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, phone TEXT, email TEXT, full_name TEXT, status TEXT, role TEXT, password TEXT, locked_until TEXT, failed_attempts INTEGER DEFAULT 0, deleted_at TEXT, created_at TEXT, updated_at TEXT, last_login TEXT)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, action TEXT, details TEXT, ip_address TEXT, user_agent TEXT, created_at TEXT)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS security_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, event_type TEXT, severity TEXT, details TEXT, ip_address TEXT, user_agent TEXT, created_at TEXT)");

        if (function_exists('registerSqliteNow')) {
            registerSqliteNow($pdo);
        }

        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
        $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO users (phone, email, full_name, status, role, password, locked_until, failed_attempts, created_at, updated_at)
            VALUES (:phone, :email, :full_name, :status, :role, :password, :locked_until, :failed_attempts, :created_at, :updated_at)");
        $stmt->execute([
            ':phone' => '9999999999',
            ':email' => 'admin@example.com',
            ':full_name' => 'Admin User',
            ':status' => 'active',
            ':role' => 'admin',
            ':password' => $hashedPassword,
            ':locked_until' => null,
            ':failed_attempts' => 0,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        $GLOBALS['conn'] = $pdo;
        $_SERVER['REQUEST_URI'] = '/admin/login';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'unit-test';

        $controller = new AuthController($pdo);
        $res = $controller->adminLogin('admin@example.com', 'admin123');

        $this->assertEquals(true, $res['status']);
        $this->assertTrue(str_contains($res['message'], 'successful'), 'Expected success message');
    }
}

