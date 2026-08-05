<?php

declare(strict_types=1);

require_once '../../config/app.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/app/repositories/UserRepository.php';
require_once ROOT_PATH . '/app/repositories/SessionRepository.php';
require_once ROOT_PATH . '/app/repositories/AuditRepository.php';
require_once ROOT_PATH . '/app/services/AuthService.php';
require_once ROOT_PATH . '/bootstrap/providers/ServiceProvider.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
    exit;
}

if (!verifyCsrfToken($_POST['_token'] ?? $_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid security token.'
    ]);
    exit;
}

$phone = (string) ($_SESSION['otp_phone'] ?? '');

if (empty($phone)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No OTP session found.'
    ]);
    exit;
}

$authService = ServiceProvider::get('AuthService');
$result = $authService->sendOtp($phone);

echo json_encode([
    'success' => (bool) $result['success'],
    'message' => $result['message']
]);