<?php

declare(strict_types=1);

require_once '../../config/app.php';
require_once ROOT_PATH . '/app/services/AuthService.php';
require_once ROOT_PATH . '/helpers/mail.php';
require_once ROOT_PATH . '/helpers/sms.php';

if (request_method() !== 'POST') {
    redirect('login.php');
}

$service = new AuthService();
$result = $service->adminLogin(
    (string) ($_POST['email'] ?? ''),
    (string) ($_POST['password'] ?? ''),
    !empty($_POST['remember_me'])
);

$_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];

if ($result['success']) {
    redirect('admin/dashboard.php');
}

redirect('admin/login.php');
