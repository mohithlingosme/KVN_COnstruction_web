<?php

declare(strict_types=1);

require_once '../config/app.php';

if (isLoggedIn()) {
    logSecurityEvent(currentUserId(), 'user_logout', 'info', 'User logged out successfully');
}

logout();
