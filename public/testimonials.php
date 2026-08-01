<?php

declare(strict_types=1);

require_once '../config/app.php';

$pageTitle = 'Client Testimonials | ' . APP_NAME;

$publicController = new \App\Controllers\PublicController();
$testimonials = $publicController->testimonials()['testimonials'] ?? [];

require_once '../app/views/layouts/header.php';
?>

<div class="container py-5">
    <h1 class="text-center mb-5 display-5 fw-bold">Client Testimonials</h1>
    <div class="row g-4">
        <?php if (!empty($testimonials)): ?>
            <?php foreach ($testimonials as $item): ?>
                <div class="col-md-6">
                    <div class="card p-4 shadow-sm border-0 rounded-4 h-100">
                        <p class="fs-5 text-muted">"<?php echo escape($item['review'] ?? ''); ?>"</p>
                        <div class="mt-auto">
                            <strong class="text-dark">- <?php echo escape($item['client_name'] ?? 'Happy Client'); ?></strong>
                            <?php if (!empty($item['client_location'])): ?>
                                <span class="text-muted small">(<?php echo escape($item['client_location']); ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-md-6">
                <div class="card p-4 shadow-sm border-0 rounded-4">
                    <p class="fs-5 text-muted">"KVN Construction delivered our dream home on time and within budget. Highly recommended!"</p>
                    <strong class="text-dark">- Rahul S. (Bengaluru)</strong>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4 shadow-sm border-0 rounded-4">
                    <p class="fs-5 text-muted">"Excellent quality of work and very professional team. The interior finishing is top notch."</p>
                    <strong class="text-dark">- Priya M. (Bengaluru)</strong>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
