<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ESTIMATOR MATERIALS MANAGEMENT
|--------------------------------------------------------------------------
| File:
| /public/admin/estimators/materials.php
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
'Estimation Materials | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| CREATE TABLE IF NOT EXISTS
|--------------------------------------------------------------------------
*/

try {

    $conn->exec("

        CREATE TABLE IF NOT EXISTS estimator_materials (

            id INT PRIMARY KEY AUTO_INCREMENT,

            material_name VARCHAR(255) NOT NULL,

            category VARCHAR(255) NULL,

            unit VARCHAR(50) DEFAULT 'sqft',

            unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,

            supplier VARCHAR(255) NULL,

            brand VARCHAR(255) NULL,

            status ENUM(
                'active',
                'inactive'
            ) DEFAULT 'active',

            description TEXT NULL,

            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP
        )
    ");

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| HANDLE CREATE MATERIAL
|--------------------------------------------------------------------------
*/

if (

    $_SERVER['REQUEST_METHOD'] === 'POST'

    &&

    isset($_POST['create_material'])
) {

    validateCsrf();

    if (

        !checkRateLimit(

            'create_material',

            10,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests.';

        redirect('admin/estimators/materials.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $materialName =
    sanitize($_POST['material_name'] ?? '');

    $category =
    sanitize($_POST['category'] ?? '');

    $unit =
    sanitize($_POST['unit'] ?? 'sqft');

    $unitPrice =
    (float) ($_POST['unit_price'] ?? 0);

    $supplier =
    sanitize($_POST['supplier'] ?? '');

    $brand =
    sanitize($_POST['brand'] ?? '');

    $status =
    sanitize($_POST['status'] ?? 'active');

    $description =
    sanitize($_POST['description'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        empty($materialName)

        ||

        $unitPrice <= 0
    ) {

        $_SESSION['error'] =
        'Material name and valid price are required.';

        redirect('admin/estimators/materials.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT MATERIAL
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            INSERT INTO estimator_materials (

                material_name,
                category,
                unit,
                unit_price,
                supplier,
                brand,
                status,
                description,
                created_at

            ) VALUES (

                :material_name,
                :category,
                :unit,
                :unit_price,
                :supplier,
                :brand,
                :status,
                :description,
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':material_name' =>
            $materialName,

            ':category' =>
            $category,

            ':unit' =>
            $unit,

            ':unit_price' =>
            $unitPrice,

            ':supplier' =>
            $supplier,

            ':brand' =>
            $brand,

            ':status' =>
            $status,

            ':description' =>
            $description
        ]);

        logSecurityEvent(

            currentUserId(),

            'material_created',

            'info',

            'Estimator material added'
        );

        $_SESSION['success'] =
        'Material added successfully.';

        redirect('admin/estimators/materials.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to add material.';
    }
}

/*
|--------------------------------------------------------------------------
| DELETE MATERIAL
|--------------------------------------------------------------------------
*/

if (

    isset($_GET['delete'])

    &&

    is_numeric($_GET['delete'])
) {

    $materialId =
    (int) $_GET['delete'];

    try {

        $deleteQuery = "

            DELETE FROM estimator_materials

            WHERE id = :id
        ";

        $deleteStmt =
        $conn->prepare($deleteQuery);

        $deleteStmt->execute([

            ':id' => $materialId
        ]);

        $_SESSION['success'] =
        'Material deleted successfully.';

        redirect('admin/estimators/materials.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to delete material.';
    }
}

/*
|--------------------------------------------------------------------------
| FETCH MATERIALS
|--------------------------------------------------------------------------
*/

$materials = [];

try {

    $query = "

        SELECT *

        FROM estimator_materials

        ORDER BY id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute();

    $materials =
    $stmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalMaterials =
count($materials);

$activeMaterials =
count(

    array_filter(

        $materials,

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

$totalMaterialCost =
array_sum(

    array_column(

        $materials,

        'unit_price'
    )
);

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

            <!-- HEADER -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Construction Materials

                    </h1>

                    <p>

                        Manage material pricing and estimator calculations.

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

            <!-- STATS -->

            <div class="row g-4 mb-4">

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-primary"
                        >

                            <i class="bi bi-box-seam"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalMaterials
                                );

                                ?>

                            </h3>

                            <p>

                                Total Materials

                            </p>

                        </div>

                    </div>

                </div>

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
                                    $activeMaterials
                                );

                                ?>

                            </h3>

                            <p>

                                Active Materials

                            </p>

                        </div>

                    </div>

                </div>

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
                                    $totalMaterialCost
                                );

                                ?>

                            </h3>

                            <p>

                                Total Pricing Value

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- CREATE FORM -->

            <div class="section-card mb-4">

                <div class="section-header">

                    <h4>

                        Add Material

                    </h4>

                </div>

                <form method="POST">

                    <?php echo csrfField(); ?>

                    <input
                        type="hidden"
                        name="create_material"
                        value="1"
                    >

                    <div class="row">

                        <!-- MATERIAL -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Material Name

                            </label>

                            <input
                                type="text"
                                name="material_name"
                                class="form-control"
                                required
                            >

                        </div>

                        <!-- CATEGORY -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Category

                            </label>

                            <input
                                type="text"
                                name="category"
                                class="form-control"
                                placeholder="Cement / Steel / Paint"
                            >

                        </div>

                        <!-- UNIT -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Unit

                            </label>

                            <input
                                type="text"
                                name="unit"
                                class="form-control"
                                value="sqft"
                            >

                        </div>

                        <!-- PRICE -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Unit Price

                            </label>

                            <input
                                type="number"
                                step="0.01"
                                name="unit_price"
                                class="form-control"
                                required
                            >

                        </div>

                        <!-- SUPPLIER -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Supplier

                            </label>

                            <input
                                type="text"
                                name="supplier"
                                class="form-control"
                            >

                        </div>

                        <!-- BRAND -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Brand

                            </label>

                            <input
                                type="text"
                                name="brand"
                                class="form-control"
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

                        Add Material

                    </button>

                </form>

            </div>

            <!-- MATERIALS TABLE -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Materials List

                    </h4>

                </div>

                <div class="table-responsive">

                    <table class="table admin-table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Material</th>

                                <th>Category</th>

                                <th>Unit</th>

                                <th>Price</th>

                                <th>Brand</th>

                                <th>Supplier</th>

                                <th>Status</th>

                                <th width="150">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($materials)): ?>

                                <?php foreach($materials as $material): ?>

                                    <tr>

                                        <td>

                                            #<?php echo $material['id']; ?>

                                        </td>

                                        <td>

                                            <strong>

                                                <?php

                                                echo escape(

                                                    $material['material_name']
                                                );

                                                ?>

                                            </strong>

                                        </td>

                                        <td>

                                            <?php

                                            echo escape(

                                                $material['category']
                                                ??
                                                'N/A'
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <?php

                                            echo escape(

                                                $material['unit']
                                                ??
                                                'N/A'
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            ₹<?php

                                            echo number_format(

                                                $material['unit_price'],

                                                2
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <?php

                                            echo escape(

                                                $material['brand']
                                                ??
                                                'N/A'
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <?php

                                            echo escape(

                                                $material['supplier']
                                                ??
                                                'N/A'
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <span class="badge

                                                <?php

                                                echo

                                                strtolower(

                                                    $material['status']
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

                                                        $material['status']
                                                    )
                                                );

                                                ?>

                                            </span>

                                        </td>

                                        <td>

                                            <div class="d-flex gap-2">

                                                <!-- EDIT -->

                                                <a
                                                    href="edit-material.php?id=<?php

                                                    echo $material['id'];

                                                    ?>"
                                                    class="
                                                        btn
                                                        btn-sm
                                                        btn-primary
                                                    "
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>

                                                <!-- DELETE -->

                                                <a
                                                    href="?delete=<?php

                                                    echo $material['id'];

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

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="9">

                                        <div class="text-center py-5">

                                            <i
                                                class="
                                                    bi
                                                    bi-box-seam
                                                "
                                                style="
                                                    font-size:60px;
                                                    color:#d1d5db;
                                                "
                                            ></i>

                                            <p class="mt-3">

                                                No materials found.

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