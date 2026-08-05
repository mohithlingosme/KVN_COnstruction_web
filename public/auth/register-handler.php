<?php

declare(strict_types=1);

require_once '../../config/app.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/session.php';
require_once ROOT_PATH . '/app/repositories/UserRepository.php';
require_once ROOT_PATH . '/app/repositories/SessionRepository.php';
require_once ROOT_PATH . '/app/repositories/AuditRepository.php';
require_once ROOT_PATH . '/app/services/AuthService.php';
require_once ROOT_PATH . '/bootstrap/providers/ServiceProvider.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register.php');
}

if (!verifyCsrfToken($_POST['_token'] ?? $_POST['csrf_token'] ?? null)) {
    $_SESSION['error'] = 'Invalid security token.';
    redirect('register.php');
}

$authService = ServiceProvider::get('AuthService');
$result = $authService->register($_POST);

if (!$result['success']) {
    $_SESSION['error'] = $result['message'];
    redirect('register.php');
}

$_SESSION['success'] = $result['message'];
redirect('login.php');