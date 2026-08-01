<?php

declare(strict_types=1);

require_once '../config/app.php';

$pageTitle = 'Construction Packages | ' . APP_NAME;

$publicController = new \App\Controllers\PublicController();
$data = $publicController->packages();
$packages = $data['packages'] ?? [];

require_once '../app/views/layouts/header.php';
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Construction Packages</h1>
        <p class="text-muted">Transparent pricing and specs for home construction in Bengaluru</p>
    </div>
    
    <div class="row g-4 justify-content-center">
        <?php if (!empty($packages)): ?>
            <?php foreach ($packages as $index => $pkg): ?>
                <div class="col-md-4">
                    <div class="card p-4 text-center border-0 shadow-sm rounded-4 h-100 <?php echo $index === 1 ? 'bg-dark text-white' : ''; ?>">
                        <h3 class="fw-bold"><?php echo escape($pkg['package_name'] ?? 'Package'); ?></h3>
                        <p class="<?php echo $index === 1 ? 'text-warning' : 'text-primary'; ?> fs-4 fw-bold">₹<?php echo number_format((float)($pkg['base_price'] ?? 0)); ?> / sq.ft.</p>
                        <p class="small opacity-75"><?php echo escape($pkg['description'] ?? ''); ?></p>
                        <div class="mt-auto">
                            <a href="<?php echo base_url('estimator.php'); ?>" class="btn <?php echo $index === 1 ? 'btn-warning' : 'btn-outline-primary'; ?> w-100 mt-3 py-2 fw-bold">Get Detailed Estimate</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-md-4">
                <div class="card p-4 text-center border-0 shadow-sm">
                    <h3>Basic Package</h3>
                    <p class="text-muted">₹1,850 / sq.ft.</p>
                    <a href="<?php echo base_url('estimator.php'); ?>" class="btn btn-outline-dark mt-3">Get Estimate</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center border-0 shadow-sm bg-dark text-white">
                    <h3>Premium Package</h3>
                    <p class="text-warning">₹2,250 / sq.ft.</p>
                    <a href="<?php echo base_url('estimator.php'); ?>" class="btn btn-warning mt-3">Get Estimate</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center border-0 shadow-sm">
                    <h3>Luxury Package</h3>
                    <p class="text-muted">₹2,850 / sq.ft.</p>
                    <a href="<?php echo base_url('estimator.php'); ?>" class="btn btn-outline-dark mt-3">Get Estimate</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
