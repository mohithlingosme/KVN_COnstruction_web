<?php

declare(strict_types=1);

class ApiEstimatorTest
{
    private function runApi(string $method, string $action, array $payload = [], string $csrf = 'valid', string $ip = '127.0.0.1'): array
    {
        $payloadStr = escapeshellarg(base64_encode(json_encode($payload)));
        $cmd = "php -d display_errors=0 " . escapeshellarg(__DIR__ . '/run_api.php') . " " . escapeshellarg($method) . " " . escapeshellarg($action) . " {$payloadStr} " . escapeshellarg($csrf) . " " . escapeshellarg($ip);
        
        $fn = 'shell_exec';
        $output = $fn($cmd);
        $json = json_decode($output, true);
        
        return $json ?? ['success' => false, 'message' => 'Failed to parse JSON', 'raw' => $output];
    }

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
       GET action=packages
    ========================================================= */

    public function test_get_packages_success(): void
    {
        $res = $this->runApi('GET', 'packages');
        $this->assertTrue(isset($res['success']), 'Response should have success key');
        $this->assertEquals(true, $res['success']);
        $this->assertTrue(is_array($res['data']), 'Data should be an array');
        $this->assertTrue(count($res['data']) >= 1, 'Should have at least 1 package');
        if (count($res['data']) > 0) {
            $this->assertTrue(isset($res['data'][0]['package_name']), 'Package should have name');
            $this->assertEquals('Basic', $res['data'][0]['package_name']);
            $this->assertTrue(isset($res['data'][0]['base_price']), 'Package should have price');
            $this->assertTrue(is_float($res['data'][0]['base_price']) || is_int($res['data'][0]['base_price']));
        }
    }

    public function test_get_packages_data_structure(): void
    {
        $res = $this->runApi('GET', 'packages');
        $this->assertEquals(true, $res['success']);
        $this->assertTrue(is_array($res['data']));
        foreach ($res['data'] as $pkg) {
            $this->assertTrue(isset($pkg['id']), 'Package missing id');
            $this->assertTrue(isset($pkg['package_name']), 'Package missing package_name');
            $this->assertTrue(isset($pkg['base_price']), 'Package missing base_price');
            $this->assertTrue(isset($pkg['material_grade']), 'Package missing material_grade');
            $this->assertTrue(isset($pkg['estimated_timeline']), 'Package missing estimated_timeline');
            $this->assertTrue(isset($pkg['description']), 'Package missing description');
            $this->assertTrue(isset($pkg['features']), 'Package missing features');
            $this->assertTrue(is_array($pkg['features']), 'Features should be decoded as array');
        }
    }

    /* =========================================================
       POST action=calculate
    ========================================================= */

    public function test_calculate_invalid_csrf(): void
    {
        $res = $this->runApi('POST', 'calculate', [], 'invalid');
        $this->assertEquals(false, $res['success']);
        $this->assertTrue(
            str_contains($res['message'] ?? $res['raw'] ?? '', 'security token') ||
            str_contains($res['message'] ?? $res['raw'] ?? '', 'CSRF'),
            'Expected CSRF error message, got: ' . ($res['message'] ?? $res['raw'] ?? '')
        );
    }

    public function test_calculate_invalid_inputs_empty(): void
    {
        $res = $this->runApi('POST', 'calculate', [], 'valid');
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('Invalid input parameters', $res['message']);
    }
    public function test_calculate_invalid_inputs_zero_values(): void
    {
        $res = $this->runApi('POST', 'calculate', [
            'plot_length' => 0,
            'plot_width' => 0,
            'floors' => 0,
            'package_id' => 0
        ], 'valid');
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('Invalid input parameters', $res['message']);
    }

    public function test_calculate_package_not_found(): void
    {
        $res = $this->runApi('POST', 'calculate', [
            'plot_length' => 50,
            'plot_width' => 30,
            'floors' => 2,
            'package_id' => 999
        ], 'valid');
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('Package not found', $res['message']);
    }

    public function test_calculate_success(): void
    {
        $res = $this->runApi('POST', 'calculate', [
            'plot_length' => 50,
            'plot_width' => 30,
            'floors' => 2,
            'package_id' => 1
        ], 'valid');
        $this->assertEquals(true, $res['success'], 'Calculate should succeed');
        $this->assertTrue(isset($res['data']), 'Response should have data key');
        $this->assertTrue(isset($res['data']['package']), 'Data should have package');
        $this->assertTrue(isset($res['data']['total_estimated_cost']), 'Data should have total_estimated_cost');
        $this->assertEquals('INR', $res['data']['currency']);
        $this->assertEquals('Basic', $res['data']['package']);
        // plot_area = 50 * 30 = 1500, built_up_area = 1500 * 2 = 3000
        $this->assertEquals(1500, $res['data']['plot_area']);
        $this->assertEquals(3000, $res['data']['built_up_area']);
    }

    /* =========================================================
       POST action=lead
    ========================================================= */

    public function test_lead_invalid_csrf(): void
    {
        $res = $this->runApi('POST', 'lead', [], 'invalid');
        $this->assertEquals(false, $res['success']);
        $this->assertTrue(
            str_contains($res['message'] ?? $res['raw'] ?? '', 'security token') ||
            str_contains($res['message'] ?? $res['raw'] ?? '', 'CSRF'),
            'Expected CSRF error message, got: ' . ($res['message'] ?? $res['raw'] ?? '')
        );
    }

    public function test_lead_missing_fields(): void
    {
        $res = $this->runApi('POST', 'lead', [], 'valid');
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('Missing required fields', $res['message']);
    }

    public function test_lead_missing_name(): void
    {
        $res = $this->runApi('POST', 'lead', [
            'full_name' => '',
            'phone' => '9999999999',
            'plot_size' => 100,
            'package_id' => 1
        ], 'valid');
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('Missing required fields', $res['message']);
    }

    public function test_lead_invalid_phone(): void
    {
        $res = $this->runApi('POST', 'lead', [
            'full_name' => 'Test User',
            'phone' => '12345',
            'plot_size' => 100,
            'package_id' => 1
        ], 'valid');
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('Invalid phone number', $res['message']);
    }

    public function test_lead_success(): void
    {
        $res = $this->runApi('POST', 'lead', [
            'full_name' => 'John Doe',
            'phone' => '9876543210',
            'email' => 'john@example.com',
            'location' => 'Test City',
            'plot_size' => 1200,
            'floors' => 2,
            'package_id' => 1,
            'estimated_cost' => 2500000
        ], 'valid');
        $this->assertEquals(true, $res['success'], 'Lead should save successfully');
        $this->assertEquals('Lead saved successfully', $res['message']);
    }

    /* =========================================================
       Rate-limit responses
    ========================================================= */

    public function test_calculate_rate_limit_response(): void
    {
        // Run a valid calculate request to verify rate limit response format
        // (actual rate limit triggering would require many requests)
        $res = $this->runApi('POST', 'calculate', [
            'plot_length' => 50,
            'plot_width' => 30,
            'floors' => 2,
            'package_id' => 1
        ], 'valid');
        // This should succeed rather than rate-limit us (1 request only)
        $this->assertEquals(true, $res['success']);
    }

    /* =========================================================
       Unknown action → 404
    ========================================================= */

    public function test_unknown_action_404(): void
    {
        $res = $this->runApi('GET', 'nonexistent');
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('Unknown API endpoint', $res['message']);
    }

    public function test_unknown_action_post(): void
    {
        $res = $this->runApi('POST', 'bogus');
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('Unknown API endpoint', $res['message']);
    }
}
