<?php

declare(strict_types=1);

require_once '../config/app.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/csrf.php';
require_once ROOT_PATH . '/helpers/rateLimiter.php';

securityHeaders();

$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!checkRateLimit('estimator', 20, 3600)) {
    http_response_code(429);
    die('Too many estimator requests. Please try again later.');
}

$estimatorService = new \App\Services\EstimatorService();
$packages = $estimatorService->getPackages();

$successMessage = '';
$errorMessage = '';
$estimatedCost = 0;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!empty($_POST['website'] ?? '')) {
        die('Spam detected.');
    }

    if (!function_exists('validateCsrf') || !validateCsrf($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    $result = $estimatorService->processLeadSubmission($_POST, $clientIp);
    if ($result['success'] ?? false) {
        $successMessage = $result['message'];
        $estimatedCost = $result['estimated_cost'];
    } else {
        $errorMessage = $result['message'] ?? 'An error occurred.';
    }
}

$pageTitle = 'Smart Cost Estimator | ' . APP_NAME;
include '../app/views/layouts/header.php';

?>

<section class="hero bg-primary text-white py-5">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">Smart Construction Estimator</h1>
        <p class="lead">Calculate instant construction estimates customized for your Bengaluru plot.</p>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($successMessage): ?>
                    <div class="alert alert-success shadow-sm">
                        <h4 class="alert-heading">Estimate Generated!</h4>
                        <p><?php echo escape($successMessage); ?></p>
                        <hr>
                        <h3 class="mb-0">Estimated Cost: ₹<?php echo number_format($estimatedCost, 2); ?></h3>
                    </div>
                <?php endif; ?>

                <?php if ($errorMessage): ?>
                    <div class="alert alert-danger shadow-sm">
                        <?php echo escape($errorMessage); ?>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <form method="POST" action="estimator.php">
                            <?php echo csrfField(); ?>
                            <input type="text" name="website" style="display:none;" tabindex="-1" autocomplete="off">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Full Name *</label>
                                    <input type="text" name="full_name" class="form-control" required value="<?php echo escape($_POST['full_name'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Phone Number *</label>
                                    <input type="tel" name="phone" class="form-control" required pattern="[0-9]{10}" value="<?php echo escape($_POST['phone'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo escape($_POST['email'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Location *</label>
                                    <input type="text" name="location" class="form-control" placeholder="e.g. Indiranagar, Bengaluru" required value="<?php echo escape($_POST['location'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Plot Area (sqft) *</label>
                                    <input type="number" name="plot_size" class="form-control" min="100" max="50000" required value="<?php echo escape((string)($_POST['plot_size'] ?? 1200)); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Number of Floors *</label>
                                    <select name="floors" class="form-select" required>
                                        <option value="1">Ground Floor (G)</option>
                                        <option value="2" selected>G + 1 Floor</option>
                                        <option value="3">G + 2 Floors</option>
                                        <option value="4">G + 3 Floors</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-weight-bold">Package Grade *</label>
                                    <select name="package_id" class="form-select" required>
                                        <?php foreach ($packages as $pkg): ?>
                                            <option value="<?php echo (int)($pkg['id'] ?? 0); ?>">
                                                <?php echo escape($pkg['package_name'] ?? 'Package'); ?> (₹<?php echo number_format((float)($pkg['base_price'] ?? 0)); ?>/sqft)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3 font-weight-bold">Calculate & Request Detailed Proposal</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../app/views/layouts/footer.php'; ?>
