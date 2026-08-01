<?php

declare(strict_types=1);

require_once '../config/app.php';

$pageTitle = 'Videos | ' . APP_NAME;

$publicController = new \App\Controllers\PublicController();
$videos = $publicController->videos()['videos'] ?? [];

require_once '../app/views/layouts/header.php';
?>

<div class="container py-5">
    <h1 class="text-center mb-4 display-5 fw-bold">Project & Site Walkthrough Videos</h1>
    <p class="text-center text-muted mb-5">Explore behind-the-scenes video tours and completed project showcases.</p>

    <div class="row g-4">
        <?php if (!empty($videos)): ?>
            <?php foreach ($videos as $video): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="ratio ratio-16x9">
                            <iframe src="<?php echo escape($video['youtube_url'] ?? ''); ?>" title="<?php echo escape($video['title'] ?? 'Video'); ?>" allowfullscreen></iframe>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?php echo escape($video['title'] ?? ''); ?></h5>
                            <p class="card-text text-muted small"><?php echo escape($video['description'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted py-5">
                <i class="bi bi-film fs-1"></i>
                <p class="mt-3 fs-5">Project videos are currently being uploaded. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
