<?php

declare(strict_types=1);

require_once '../../config/app.php';

if (isLoggedIn()) {
    logSecurityEvent(currentUserId(), 'client_logout', 'info', 'Client logged out');
}

logout();
