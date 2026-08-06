<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../app/Core/routes.php';
\App\Core\Router::dispatch();