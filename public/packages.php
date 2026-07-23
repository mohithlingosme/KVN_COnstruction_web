<?php
$pageTitle = 'Packages | ' . APP_NAME;
require_once '../app/views/layouts/header.php';
?>
<div class="container" style="padding: 100px 0;">
    <h1 class="text-center mb-5">Construction Packages</h1>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card p-4 text-center border-0 shadow-sm">
                <h3>Basic</h3>
                <p class="text-muted">₹1,850 / sq.ft.</p>
                <a href="<?php echo base_url('estimator.php'); ?>" class="btn btn-outline-dark mt-3">Get Estimate</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 text-center border-0 shadow-sm" style="background:#111; color:#fff;">
                <h3>Premium</h3>
                <p style="color:#f5b400;">₹2,250 / sq.ft.</p>
                <a href="<?php echo base_url('estimator.php'); ?>" class="btn btn-warning mt-3">Get Estimate</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 text-center border-0 shadow-sm">
                <h3>Luxury</h3>
                <p class="text-muted">₹2,850 / sq.ft.</p>
                <a href="<?php echo base_url('estimator.php'); ?>" class="btn btn-outline-dark mt-3">Get Estimate</a>
            </div>
        </div>
    </div>
</div>
<?php require_once '../app/views/layouts/footer.php'; ?>
