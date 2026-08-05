<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| SERVICES MANAGEMENT
|--------------------------------------------------------------------------
| File: /public/admin/services/index.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';
require_once '../../../helpers/security.php';
require_once '../../../helpers/formatter.php';
require_once '../../../includes/repositories.php';

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle = 'Services Management | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| DELETE SERVICE
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        $serviceRepo = repo('Service');
        if ($serviceRepo) {
            $serviceRepo->delete($id);
            $_SESSION['success'] = 'Service deleted successfully.';
        }
    } catch (Throwable $e) {
        error_log('Service delete error: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to delete service.';
    }
    redirect('admin/services/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH SERVICES
|--------------------------------------------------------------------------
*/

$services = [];

try {
    $serviceRepo = repo('Service');
    if ($serviceRepo) {
        $services = $serviceRepo->getAll();
    }
} catch (Throwable $e) {
    error_log('Services fetch error: ' . $e->getMessage());
    $_SESSION['error'] = 'Failed to fetch services.';
}

$totalServices = count($services);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo escape($pageTitle); ?>
    </title>

    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- Admin CSS -->

    <link
        rel="stylesheet"
        href="<?php echo base_url('assets/admin/css/admin.css'); ?>"
    >

</head>

<body>

<div class="admin-layout">

    <!-- ================================= -->
    <!-- SIDEBAR -->
    <!-- ================================= -->

    <?php include '../../../app/views/layouts/sidebar.php'; ?>

    <!-- ================================= -->
    <!-- MAIN -->
    <!-- ================================= -->

    <div class="admin-main">

        <!-- NAVBAR -->

        <?php include '../../../app/views/layouts/navbar.php'; ?>

        <!-- CONTENT -->

        <div class="admin-content">

            <!-- ============================== -->
            <!-- HEADER -->
            <!-- ============================== -->

            <div class="dashboard-header">

                <div>

                    <h1>
                        Services Management
                    </h1>

                    <p>
                        Manage construction services offered to clients.
                    </p>

                </div>

                <div>

                    <a
                        href="create.php"
                        class="btn-admin"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Add Service

                    </a>

                </div>

            </div>

            <!-- ============================== -->
            <!-- ALERTS -->
            <!-- ============================== -->

            <?php if(isset($_SESSION['success'])): ?>

                <div class="alert alert-success alert-auto-dismiss">

                    <?php
                    echo escape($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>

                </div>

            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>

                <div class="alert alert-danger alert-auto-dismiss">

                    <?php
                    echo escape($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>

                </div>

            <?php endif; ?>

            <!-- ============================== -->
            <!-- STATS -->
            <!-- ============================== -->

            <div class="row g-4 mb-4">

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div class="dashboard-icon bg-primary">

                            <i class="bi bi-grid"></i>

                        </div>

                        <div>

                            <h3>
                                <?php echo number_format($totalServices); ?>
                            </h3>

                            <p>
                                Total Services
                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ============================== -->
            <!-- SERVICES TABLE -->
            <!-- ============================== -->

            <div class="section-card">

                <div class="section-header">

                    <h4>
                        Service Records
                    </h4>

                </div>

                <!-- SEARCH -->

                <div class="row mb-4">

                    <div class="col-lg-4">

                        <input
                            type="text"
                            class="form-control table-search"
                            data-table="#servicesTable"
                            placeholder="Search services..."
                        >

                    </div>

                </div>

                <!-- TABLE -->

                <div class="table-responsive">

                    <table
                        class="table admin-table"
                        id="servicesTable"
                    >

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Title</th>

                                <th>Description</th>

                                <th>Image</th>

                                <th width="180">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($services)): ?>

                                <?php foreach($services as $service): ?>

                                    <tr>

                                        <td>
                                            #<?php echo (int)$service['id']; ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?php echo escape($service['title'] ?? ''); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <small>
                                                <?php
                                                $desc = $service['description'] ?? '';
                                                echo escape(mb_strimwidth($desc, 0, 100, '...'));
                                                ?>
                                            </small>
                                        </td>

                                        <td>
                                            <?php if(!empty($service['image'])): ?>
                                                <img
                                                    src="<?php echo escape(base_url($service['image'])); ?>"
                                                    alt="<?php echo escape($service['title'] ?? ''); ?>"
                                                    style="width:50px;height:50px;object-fit:cover;border-radius:8px;"
                                                >
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>

                                            <div class="d-flex gap-2">

                                                <!-- EDIT -->

                                                <a
                                                    href="edit.php?id=<?= (int)$service['id'] ?>"
                                                    class="btn btn-sm btn-primary"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>

                                                <!-- DELETE -->

                                                <a
                                                    href="delete.php?id=<?= (int)$service['id'] ?>"
                                                    class="btn btn-sm btn-danger btn-delete"
                                                >

                                                    <i class="bi bi-trash"></i>

                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="5">

                                        <div class="text-center py-5">

                                            <i
                                                class="bi bi-grid"
                                                style="font-size:50px;color:#d1d5db;"
                                            ></i>

                                            <p class="mt-3">
                                                No services found.
                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin JS -->

<script src="<?php echo base_url('assets/admin/js/admin.js'); ?>"></script>

</body>

</html>