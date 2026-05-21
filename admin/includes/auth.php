<?php

declare(strict_types=1);

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Verify session is valid (basic check)
if (!isset($_SESSION['login_time'])) {
    $_SESSION['login_time'] = time();
}

// Optional: Implement session timeout (30 minutes)
$session_timeout = 30 * 60;
if (time() - $_SESSION['login_time'] > $session_timeout) {
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}

// Update last activity
$_SESSION['login_time'] = time();
?>
