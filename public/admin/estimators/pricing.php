<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ESTIMATOR PRICING MANAGEMENT
|--------------------------------------------------------------------------
| File: /public/admin/estimators/pricing.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';
require_once '../../../helpers/security.php';
require_once '../../../helpers/csrf.php';
require_once '../../../helpers/session.php';
require_once '../../../helpers/rateLimiter.php';
require_once '../../../includes/repositories.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle = 'Estimation Pricing Settings | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| REPOSITORY
|--------------------------------------------------------------------------
*/

$estimatorRepo = repo('Estimator');

/*
|--------------------------------------------------------------------------
| HANDLE CREATE PRICING
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_pricing'])) {
    validateCsrf();

    if (!checkRateLimit('pricing_create', 10, 300)) {
        $_SESSION['error'] = 'Too many requests.';
        redirect('admin/estimators/pricing.php');
    }

    $packageName = sanitize($_POST['package_name'] ?? '');
    $pricePerSqft = (float) ($_POST['price_per_sqft'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');

    if (empty($packageName) || $pricePerSqft <= 0) {
        $_SESSION['error'] = 'Package name and valid price are required.';
        redirect('admin/estimators/pricing.php');
    }

    try {
        $data = [
            'package_name' => $packageName,
            'price_per_sqft' => $pricePerSqft,
            'description' => $description,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $ok = $estimatorRepo && $estimatorRepo->insertPricing($data);

        if ($ok) {
            logSecurityEvent(currentUserId(), 'pricing_package_created', 'info', 'Created estimator pricing package');
            $_SESSION['success'] = 'Pricing package created successfully.';
        } else {
            $_SESSION['error'] = 'Failed to create pricing package.';
        }
        redirect('admin/estimators/pricing.php');
    } catch(Exception $e){
        $_SESSION['error'] = 'Failed to create pricing package.';
    }
}

/*
|--------------------------------------------------------------------------
| DELETE PACKAGE
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pricingId = (int) $_GET['delete'];
    try {
        if ($estimatorRepo) {
            $estimatorRepo->deletePricing($pricingId);
        }
        $_SESSION['success'] = 'Pricing package deleted successfully.';
        redirect('admin/estimators/pricing.php');
    } catch(Exception $e){
        $_SESSION['error'] = 'Failed to delete package.';
    }
}

/*
|--------------------------------------------------------------------------
| UPDATE PACKAGE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_pricing'])) {
    validateCsrf();

    $pricingId = (int) ($_POST['pricing_id'] ?? 0);
    $packageName = sanitize($_POST['package_name'] ?? '');
    $pricePerSqft = (float) ($_POST['price_per_sqft'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');

    try {
        $data = [
            'package_name' => $packageName,
            'price_per_sqft' => $pricePerSqft,
            'description' => $description,
            'status' => $status
        ];
        $ok = $estimatorRepo && $estimatorRepo->updatePricing($pricingId, $data);

        if ($ok) {
            $_SESSION['success'] = 'Pricing updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update pricing.';
        }
        redirect('admin/estimators/pricing.php');
    } catch(Exception $e){
        $_SESSION['error'] = 'Failed to update pricing.';
    }
}

/*
|--------------------------------------------------------------------------
| FETCH PRICING VIA REPOSITORY
|--------------------------------------------------------------------------
*/

$pricingPackages = [];
try {
    if ($estimatorRepo) {
        $pricingPackages = $estimatorRepo->getAllPricing();
    }
} catch(Exception $e){}

$totalPackages = count($pricingPackages);
$activePackages = count(array_filter($pricingPackages, function($item){
    return strtolower($item['status'] ?? '') === 'active';
}));
$avgPrice = 0;
if ($totalPackages > 0) {
    $avgPrice = array_sum(array_column($pricingPackages, 'price_per_sqft')) / $totalPackages;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/admin.css'); ?>">
</head>
<body>
<div class="admin-layout">
    <?php include '../../../app/views/layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include '../../../app/views/layouts/navbar.php'; ?>
        <div class="admin-content">
            <div class="dashboard-header">
                <div>
                    <h1>Estimator Pricing</h1>
                    <p>Configure construction pricing packages and sqft rates.</p>
                </div>
            </div>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo escape($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo escape($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="dashboard-card">
                        <div class="dashboard-icon bg-primary"><i class="bi bi-box-fill"></i></div>
                        <div><h3><?php echo number_format($totalPackages); ?></h3><p>Total Packages</p></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="dashboard-card">
                        <div class="dashboard-icon bg-success"><i class="bi bi-check-circle-fill"></i></div>
                        <div><h3><?php echo number_format($activePackages); ?></h3><p>Active Packages</p></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="dashboard-card">
                        <div class="dashboard-icon bg-warning"><i class="bi bi-currency-rupee"></i></div>
                        <div><h3>₹<?php echo number_format($avgPrice); ?></h3><p>Avg Price / Sqft</p></div>
                    </div>
                </div>
            </div>
            <div class="section-card mb-4">
                <div class="section-header"><h4>Add Pricing Package</h4></div>
                <form method="POST">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="create_pricing" value="1">
                    <div class="row">
                        <div class="col-lg-4 mb-4">
                            <label class="form-label">Package Name</label>
                            <input type="text" name="package_name" class="form-control" placeholder="Premium Package" required>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <label class="form-label">Price Per Sqft</label>
                            <input type="number" name="price_per_sqft" class="form-control" min="0" step="0.01" required>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-lg-12 mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="4" class="form-control"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn-admin"><i class="bi bi-plus-circle"></i> Add Pricing</button>
                </form>
            </div>
            <div class="section-card">
                <div class="section-header"><h4>Pricing Packages</h4></div>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr><th>#</th><th>Package</th><th>Price / Sqft</th><th>Description</th><th>Status</th><th>Created</th><th width="220">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($pricingPackages)): ?>
                                <?php foreach($pricingPackages as $package): ?>
                                    <tr>
                                        <td>#<?php echo (int)$package['id']; ?></td>
                                        <td><strong><?php echo escape($package['package_name']); ?></strong></td>
                                        <td>₹<?php echo number_format($package['price_per_sqft'], 2); ?></td>
                                        <td><?php echo escape(substr($package['description'] ?? '', 0, 80)); ?></td>
                                        <td>
                                            <span class="badge <?php echo strtolower($package['status'] ?? '') === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo ucfirst(escape($package['status'] ?? '')); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($package['created_at'])); ?></td>
                                        <td>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo (int)$package['id']; ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <a href="?delete=<?php echo (int)$package['id']; ?>" class="btn btn-sm btn-danger btn-delete"><i class="bi bi-trash"></i></a>
                                            </div>
                                            <div class="modal fade" id="editModal<?php echo (int)$package['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5>Edit Pricing</h5>
                                                            <button class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <?php echo csrfField(); ?>
                                                                <input type="hidden" name="update_pricing" value="1">
                                                                <input type="hidden" name="pricing_id" value="<?php echo (int)$package['id']; ?>">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Package Name</label>
                                                                    <input type="text" name="package_name" class="form-control" value="<?php echo escape($package['package_name']); ?>">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Price / Sqft</label>
                                                                    <input type="number" name="price_per_sqft" class="form-control" value="<?php echo escape($package['price_per_sqft']); ?>">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Status</label>
                                                                    <select name="status" class="form-select">
                                                                        <option value="active" <?php echo ($package['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                                                        <option value="inactive" <?php echo ($package['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Description</label>
                                                                    <textarea name="description" rows="4" class="form-control"><?php echo escape($package['description'] ?? ''); ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn-admin">Update Pricing</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7"><div class="text-center py-5"><i class="bi bi-currency-rupee" style="font-size:60px;color:#d1d5db;"></i><p class="mt-3">No pricing packages available.</p></div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url('assets/admin/js/admin.js'); ?>"></script>
</body>
</html>
