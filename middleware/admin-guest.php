<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once HELPER_PATH . '/session.php';
require_once HELPER_PATH . '/security.php';

if (isLoggedIn() && validateSession()) {
    if (isAdmin()) {
        header('Location: ' . APP_URL . '/admin/dashboard.php');
        exit;
    }

    header('Location: ' . APP_URL . '/client/dashboard.php');
    exit;
}
