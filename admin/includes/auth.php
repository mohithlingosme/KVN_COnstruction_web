<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    // /admin/login.php
    header("Location: login.php");
    exit();
}
?>
