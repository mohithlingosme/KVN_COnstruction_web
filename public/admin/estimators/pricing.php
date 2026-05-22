<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ESTIMATOR PRICING MANAGEMENT
|--------------------------------------------------------------------------
| File:
| /public/admin/estimators/pricing.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/csrf.php';

require_once '../../../helpers/session.php';

require_once '../../../helpers/rateLimiter.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
'Estimation Pricing Settings | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| CREATE TABLE IF NOT EXISTS
|--------------------------------------------------------------------------
*/

try {

    $conn->exec("

        CREATE TABLE IF NOT EXISTS estimator_pricing (

            id INT PRIMARY KEY AUTO_INCREMENT,

            package_name VARCHAR(255) NOT NULL,

            price_per_sqft DECIMAL(12,2) NOT NULL DEFAULT 0,

            description TEXT NULL,

            status ENUM(
                'active',
                'inactive'
            ) DEFAULT 'active',

            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP
        )
    ");

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| HANDLE CREATE PRICING
|--------------------------------------------------------------------------
*/

if (

    $_SERVER['REQUEST_METHOD'] === 'POST'

    &&

    isset($_POST['create_pricing'])
) {

    validateCsrf();

    if (

        !checkRateLimit(

            'pricing_create',

            10,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests.';

        redirect('admin/estimators/pricing.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $packageName =
    sanitize($_POST['package_name'] ?? '');

    $pricePerSqft =
    (float) ($_POST['price_per_sqft'] ?? 0);

    $description =
    sanitize($_POST['description'] ?? '');

    $status =
    sanitize($_POST['status'] ?? 'active');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        empty($packageName)

        ||

        $pricePerSqft <= 0
    ) {

        $_SESSION['error'] =
        'Package name and valid price are required.';

        redirect('admin/estimators/pricing.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            INSERT INTO estimator_pricing (

                package_name,
                price_per_sqft,
                description,
                status,
                created_at

            ) VALUES (

                :package_name,
                :price_per_sqft,
                :description,
                :status,
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':package_name' =>
            $packageName,

            ':price_per_sqft' =>
            $pricePerSqft,

            ':description' =>
            $description,

            ':status' =>
            $status
        ]);

        logSecurityEvent(

            currentUserId(),

            'pricing_package_created',

            'info',

            'Created estimator pricing package'
        );

        $_SESSION['success'] =
        'Pricing package created successfully.';

        redirect('admin/estimators/pricing.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to create pricing package.';
    }
}

/*
|--------------------------------------------------------------------------
| DELETE PACKAGE
|--------------------------------------------------------------------------
*/

if (

    isset($_GET['delete'])

    &&

    is_numeric($_GET['delete'])
) {

    $pricingId =
    (int) $_GET['delete'];

    try {

        $deleteQuery = "

            DELETE FROM estimator_pricing

            WHERE id = :id
        ";

        $deleteStmt =
        $conn->prepare($deleteQuery);

        $deleteStmt->execute([

            ':id' => $pricingId
        ]);

        $_SESSION['success'] =
        'Pricing package deleted successfully.';

        redirect('admin/estimators/pricing.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to delete package.';
    }
}

/*
|--------------------------------------------------------------------------
| UPDATE PACKAGE
|--------------------------------------------------------------------------
*/

if (

    $_SERVER['REQUEST_METHOD'] === 'POST'

    &&

    isset($_POST['update_pricing'])
) {

    validateCsrf();

    $pricingId =
    (int) ($_POST['pricing_id'] ?? 0);

    $packageName =
    sanitize($_POST['package_name'] ?? '');

    $pricePerSqft =
    (float) ($_POST['price_per_sqft'] ?? 0);

    $description =
    sanitize($_POST['description'] ?? '');

    $status =
    sanitize($_POST['status'] ?? 'active');

    try {

        $updateQuery = "

            UPDATE estimator_pricing

            SET

                package_name = :package_name,
                price_per_sqft = :price_per_sqft,
                description = :description,
                status = :status,
                updated_at = NOW()

            WHERE id = :id
        ";

        $updateStmt =
        $conn->prepare($updateQuery);

        $updateStmt->execute([

            ':package_name' =>
            $packageName,

            ':price_per_sqft' =>
            $pricePerSqft,

            ':description' =>
            $description,

            ':status' =>
            $status,

            ':id' =>
            $pricingId
        ]);

        $_SESSION['success'] =
        'Pricing updated successfully.';

        redirect('admin/estimators/pricing.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to update pricing.';
    }
}

/*
|--------------------------------------------------------------------------
| FETCH PRICING
|--------------------------------------------------------------------------
*/

$pricingPackages = [];

try {

    $query = "

        SELECT *

        FROM estimator_pricing

        ORDER BY id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute();

    $pricingPackages =
    $stmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalPackages =
count($pricingPackages);

$activePackages =
count(

    array_filter(

        $pricingPackages,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'active';
        }
    )
);

$avgPrice = 0;

if ($totalPackages > 0) {

    $avgPrice =
    array_sum(

        array_column(

            $pricingPackages,

            'price_per_sqft'
        )
    )

    / $totalPackages;
}

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
        href="<?php echo base_url('../assets/admin/css/admin.css'); ?>"
    >

</head>

<body>

<div class="admin-layout">

    <!-- SIDEBAR -->

    <?php include '../../../app/views/layouts/sidebar.php'; ?>

    <!-- MAIN -->

    <div class="admin-main">

        <!-- NAVBAR -->

        <?php include '../../../app/views/layouts/navbar.php'; ?>

        <!-- CONTENT -->

        <div class="admin-content">

            <!-- ================================= -->
            <!-- HEADER -->
            <!-- ================================= -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Estimator Pricing

                    </h1>

                    <p>

                        Configure construction pricing packages and sqft rates.

                    </p>

                </div>

            </div>

            <!-- ALERTS -->

            <?php if(isset($_SESSION['success'])): ?>

                <div class="alert alert-success">

                    <?php

                    echo escape(
                        $_SESSION['success']
                    );

                    unset($_SESSION['success']);

                    ?>

                </div>

            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>

                <div class="alert alert-danger">

                    <?php

                    echo escape(
                        $_SESSION['error']
                    );

                    unset($_SESSION['error']);

                    ?>

                </div>

            <?php endif; ?>

            <!-- ================================= -->
            <!-- STATS -->
            <!-- ================================= -->

            <div class="row g-4 mb-4">

                <!-- TOTAL -->

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-primary"
                        >

                            <i class="bi bi-box-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalPackages
                                );

                                ?>

                            </h3>

                            <p>

                                Total Packages

                            </p>

                        </div>

                    </div>

                </div>

                <!-- ACTIVE -->

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-success"
                        >

                            <i class="bi bi-check-circle-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $activePackages
                                );

                                ?>

                            </h3>

                            <p>

                                Active Packages

                            </p>

                        </div>

                    </div>

                </div>

                <!-- AVG -->

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-warning"
                        >

                            <i class="bi bi-currency-rupee"></i>

                        </div>

                        <div>

                            <h3>

                                ₹<?php

                                echo number_format(
                                    $avgPrice
                                );

                                ?>

                            </h3>

                            <p>

                                Avg Price / Sqft

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ================================= -->
            <!-- CREATE FORM -->
            <!-- ================================= -->

            <div class="section-card mb-4">

                <div class="section-header">

                    <h4>

                        Add Pricing Package

                    </h4>

                </div>

                <form method="POST">

                    <?php echo csrfField(); ?>

                    <input
                        type="hidden"
                        name="create_pricing"
                        value="1"
                    >

                    <div class="row">

                        <!-- PACKAGE -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Package Name

                            </label>

                            <input
                                type="text"
                                name="package_name"
                                class="form-control"
                                placeholder="Premium Package"
                                required
                            >

                        </div>

                        <!-- PRICE -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Price Per Sqft

                            </label>

                            <input
                                type="number"
                                name="price_per_sqft"
                                class="form-control"
                                min="0"
                                step="0.01"
                                required
                            >

                        </div>

                        <!-- STATUS -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Status

                            </label>

                            <select
                                name="status"
                                class="form-select"
                            >

                                <option value="active">

                                    Active

                                </option>

                                <option value="inactive">

                                    Inactive

                                </option>

                            </select>

                        </div>

                        <!-- DESCRIPTION -->

                        <div class="col-lg-12 mb-4">

                            <label class="form-label">

                                Description

                            </label>

                            <textarea
                                name="description"
                                rows="4"
                                class="form-control"
                            ></textarea>

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="btn-admin"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Add Pricing

                    </button>

                </form>

            </div>

            <!-- ================================= -->
            <!-- TABLE -->
            <!-- ================================= -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Pricing Packages

                    </h4>

                </div>

                <div class="table-responsive">

                    <table class="table admin-table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Package</th>

                                <th>Price / Sqft</th>

                                <th>Description</th>

                                <th>Status</th>

                                <th>Created</th>

                                <th width="220">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($pricingPackages)): ?>

                                <?php foreach($pricingPackages as $package): ?>

                                    <tr>

                                        <!-- ID -->

                                        <td>

                                            #<?php echo $package['id']; ?>

                                        </td>

                                        <!-- PACKAGE -->

                                        <td>

                                            <strong>

                                                <?php

                                                echo escape(

                                                    $package['package_name']
                                                );

                                                ?>

                                            </strong>

                                        </td>

                                        <!-- PRICE -->

                                        <td>

                                            ₹<?php

                                            echo number_format(

                                                $package['price_per_sqft'],

                                                2
                                            );

                                            ?>

                                        </td>

                                        <!-- DESCRIPTION -->

                                        <td>

                                            <?php

                                            echo escape(

                                                substr(

                                                    $package['description']
                                                    ??
                                                    '',

                                                    0,

                                                    80
                                                )
                                            );

                                            ?>

                                        </td>

                                        <!-- STATUS -->

                                        <td>

                                            <span class="badge

                                                <?php

                                                echo

                                                strtolower(

                                                    $package['status']
                                                )

                                                ===

                                                'active'

                                                ?

                                                'bg-success'

                                                :

                                                'bg-secondary';

                                                ?>
                                            ">

                                                <?php

                                                echo ucfirst(

                                                    escape(

                                                        $package['status']
                                                    )
                                                );

                                                ?>

                                            </span>

                                        </td>

                                        <!-- CREATED -->

                                        <td>

                                            <?php

                                            echo date(

                                                'd M Y',

                                                strtotime(

                                                    $package['created_at']
                                                )
                                            );

                                            ?>

                                        </td>

                                        <!-- ACTIONS -->

                                        <td>

                                            <div class="d-flex gap-2 flex-wrap">

                                                <!-- EDIT BUTTON -->

                                                <button
                                                    class="
                                                        btn
                                                        btn-sm
                                                        btn-primary
                                                    "

                                                    data-bs-toggle="modal"

                                                    data-bs-target="#editModal<?php

                                                    echo $package['id'];

                                                    ?>"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </button>

                                                <!-- DELETE -->

                                                <a
                                                    href="?delete=<?php

                                                    echo $package['id'];

                                                    ?>"
                                                    class="
                                                        btn
                                                        btn-sm
                                                        btn-danger
                                                        btn-delete
                                                    "
                                                >

                                                    <i class="bi bi-trash"></i>

                                                </a>

                                            </div>

                                            <!-- ================================= -->
                                            <!-- EDIT MODAL -->
                                            <!-- ================================= -->

                                            <div
                                                class="
                                                    modal
                                                    fade
                                                "

                                                id="editModal<?php

                                                echo $package['id'];

                                                ?>"

                                                tabindex="-1"
                                            >

                                                <div class="modal-dialog">

                                                    <div class="modal-content">

                                                        <div class="modal-header">

                                                            <h5>

                                                                Edit Pricing

                                                            </h5>

                                                            <button
                                                                class="
                                                                    btn-close
                                                                "
                                                                data-bs-dismiss="modal"
                                                            ></button>

                                                        </div>

                                                        <form method="POST">

                                                            <div class="modal-body">

                                                                <?php echo csrfField(); ?>

                                                                <input
                                                                    type="hidden"
                                                                    name="update_pricing"
                                                                    value="1"
                                                                >

                                                                <input
                                                                    type="hidden"
                                                                    name="pricing_id"
                                                                    value="<?php

                                                                    echo $package['id'];

                                                                    ?>"
                                                                >

                                                                <!-- PACKAGE -->

                                                                <div class="mb-3">

                                                                    <label class="form-label">

                                                                        Package Name

                                                                    </label>

                                                                    <input
                                                                        type="text"
                                                                        name="package_name"
                                                                        class="form-control"
                                                                        value="<?php

                                                                        echo escape(

                                                                            $package['package_name']
                                                                        );

                                                                        ?>"
                                                                    >

                                                                </div>

                                                                <!-- PRICE -->

                                                                <div class="mb-3">

                                                                    <label class="form-label">

                                                                        Price / Sqft

                                                                    </label>

                                                                    <input
                                                                        type="number"
                                                                        name="price_per_sqft"
                                                                        class="form-control"
                                                                        value="<?php

                                                                        echo escape(

                                                                            $package['price_per_sqft']
                                                                        );

                                                                        ?>"
                                                                    >

                                                                </div>

                                                                <!-- STATUS -->

                                                                <div class="mb-3">

                                                                    <label class="form-label">

                                                                        Status

                                                                    </label>

                                                                    <select
                                                                        name="status"
                                                                        class="form-select"
                                                                    >

                                                                        <option
                                                                            value="active"

                                                                            <?php

                                                                            if(

                                                                                $package['status']
                                                                                ===
                                                                                'active'
                                                                            ){

                                                                                echo 'selected';
                                                                            }

                                                                            ?>
                                                                        >

                                                                            Active

                                                                        </option>

                                                                        <option
                                                                            value="inactive"

                                                                            <?php

                                                                            if(

                                                                                $package['status']
                                                                                ===
                                                                                'inactive'
                                                                            ){

                                                                                echo 'selected';
                                                                            }

                                                                            ?>
                                                                        >

                                                                            Inactive

                                                                        </option>

                                                                    </select>

                                                                </div>

                                                                <!-- DESCRIPTION -->

                                                                <div class="mb-3">

                                                                    <label class="form-label">

                                                                        Description

                                                                    </label>

                                                                    <textarea
                                                                        name="description"
                                                                        rows="4"
                                                                        class="form-control"
                                                                    ><?php

                                                                    echo escape(

                                                                        $package['description']
                                                                    );

                                                                    ?></textarea>

                                                                </div>

                                                            </div>

                                                            <div class="modal-footer">

                                                                <button
                                                                    type="button"
                                                                    class="btn btn-secondary"
                                                                    data-bs-dismiss="modal"
                                                                >

                                                                    Close

                                                                </button>

                                                                <button
                                                                    type="submit"
                                                                    class="btn-admin"
                                                                >

                                                                    Update Pricing

                                                                </button>

                                                            </div>

                                                        </form>

                                                    </div>

                                                </div>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="7">

                                        <div class="text-center py-5">

                                            <i
                                                class="
                                                    bi
                                                    bi-currency-rupee
                                                "
                                                style="
                                                    font-size:60px;
                                                    color:#d1d5db;
                                                "
                                            ></i>

                                            <p class="mt-3">

                                                No pricing packages available.

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

<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>

</body>

</html>