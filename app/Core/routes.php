<?php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\PublicController;

// Home page route
Router::get('/', function () {
    $controller = new PublicController();
    $data = $controller->index();
    $projects = $data['projects'] ?? [];
    $blogs = $data['blogs'] ?? [];
    $testimonials = $data['testimonials'] ?? [];
    $packages = $data['packages'] ?? [];

    // Include the view that renders the full homepage
    require __DIR__ . '/../views/home.php';
});

// Additional routes can be defined here, e.g.:
// Router::get('/about', function() { require __DIR__ . '/../views/about.php'; });
?>
