<?php
$pageTitle = '404 - Page Not Found';
require_once '../app/views/layouts/header.php';
?>
<div class="container text-center" style="padding: 100px 0;">
    <h1 style="font-size: 80px; color: #f5b400;">404</h1>
    <h2>Page Not Found</h2>
    <p class="text-muted mt-3">The page you are looking for doesn't exist or has been moved.</p>
    <a href="<?php echo base_url('index.php'); ?>" class="btn btn-dark mt-4">Go to Homepage</a>
</div>
<?php require_once '../app/views/layouts/footer.php'; ?>
