<?php

declare(strict_types=1);

require_once '../config/app.php';

$pageTitle = 'Our Gallery | ' . APP_NAME;

$publicController = new \App\Controllers\PublicController();
$projects = $publicController->projects()['projects'] ?? [];

require_once '../app/views/layouts/header.php';
?>

<div class="container py-5">
    <h1 class="text-center mb-5 display-5 fw-bold">Project Gallery</h1>
    <div class="row g-4">
        <?php if (!empty($projects)): ?>
            <?php foreach ($projects as $project): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <img src="<?php echo base_url($project['featured_image'] ?? 'assets/images/favicon.png'); ?>" class="card-img-top" style="height: 250px; object-fit: cover;" alt="<?php echo escape($project['title'] ?? 'Gallery Image'); ?>">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?php echo escape($project['title'] ?? ''); ?></h5>
                            <p class="card-text text-muted small"><?php echo escape($project['location'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=600&auto=format&fit=crop" class="card-img-top" alt="Project Image">
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?q=80&w=600&auto=format&fit=crop" class="card-img-top" alt="Project Image">
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1448630360428-65456885c650?q=80&w=600&auto=format&fit=crop" class="card-img-top" alt="Project Image">
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
