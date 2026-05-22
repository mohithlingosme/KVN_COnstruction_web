<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ESTIMATOR PACKAGES
|--------------------------------------------------------------------------
| File:
| /public/admin/estimators/packages.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/csrf.php';

require_once '../../../helpers/session.php';

require_once '../../../helpers/rateLimiter.php';

require_once '../../../helpers/upload.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
'Estimation Packages | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| CREATE TABLE IF NOT EXISTS
|--------------------------------------------------------------------------
*/

try {

    $conn->exec("

        CREATE TABLE IF NOT EXISTS estimator_packages (

            id INT PRIMARY KEY AUTO_INCREMENT,

            package_name VARCHAR(255) NOT NULL,

            slug VARCHAR(255) NULL,

            price_per_sqft DECIMAL(12,2) NOT NULL DEFAULT 0,

            package_image VARCHAR(255) NULL,

            short_description TEXT NULL,

            features LONGTEXT NULL,

            specifications LONGTEXT NULL,

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
| HANDLE CREATE PACKAGE
|--------------------------------------------------------------------------
*/

if (

    $_SERVER['REQUEST_METHOD'] === 'POST'

    &&

    isset($_POST['create_package'])
) {

    validateCsrf();

    if (

        !checkRateLimit(

            'create_estimator_package',

            10,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests.';

        redirect('admin/estimators/packages.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $packageName =
    sanitize($_POST['package_name'] ?? '');

    $slug =
    strtolower(

        preg_replace(

            '/[^A-Za-z0-9-]+/',

            '-',

            $packageName
        )
    );

    $pricePerSqft =
    (float) ($_POST['price_per_sqft'] ?? 0);

    $shortDescription =
    sanitize($_POST['short_description'] ?? '');

    $features =
    sanitize($_POST['features'] ?? '');

    $specifications =
    sanitize($_POST['specifications'] ?? '');

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
        'Package name and valid pricing required.';

        redirect('admin/estimators/packages.php');
    }

    /*
    |--------------------------------------------------------------------------
    | IMAGE UPLOAD
    |--------------------------------------------------------------------------
    */

    $packageImage = null;

    if (

        isset($_FILES['package_image'])

        &&

        $_FILES['package_image']['error'] === 0
    ) {

        $upload =
        uploadFile(

            $_FILES['package_image'],

            ROOT_PATH . '/uploads/estimator-packages/',

            [

                'jpg',
                'jpeg',
                'png',
                'webp'
            ]
        );

        if ($upload['success']) {

            $packageImage =
            $upload['filename'];

        } else {

            $_SESSION['error'] =
            $upload['message'];

            redirect('admin/estimators/packages.php');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT PACKAGE
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            INSERT INTO estimator_packages (

                package_name,
                slug,
                price_per_sqft,
                package_image,
                short_description,
                features,
                specifications,
                status,
                created_at

            ) VALUES (

                :package_name,
                :slug,
                :price_per_sqft,
                :package_image,
                :short_description,
                :features,
                :specifications,
                :status,
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':package_name' =>
            $packageName,

            ':slug' =>
            $slug,

            ':price_per_sqft' =>
            $pricePerSqft,

            ':package_image' =>
            $packageImage,

            ':short_description' =>
            $shortDescription,

            ':features' =>
            $features,

            ':specifications' =>
            $specifications,

            ':status' =>
            $status
        ]);

        logSecurityEvent(

            currentUserId(),

            'estimator_package_created',

            'info',

            'Estimator package created'
        );

        $_SESSION['success'] =
        'Estimator package created successfully.';

        redirect('admin/estimators/packages.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to create package.';
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

    $packageId =
    (int) $_GET['delete'];

    try {

        /*
        |--------------------------------------------------------------------------
        | FETCH IMAGE
        |--------------------------------------------------------------------------
        */

        $fetchQuery = "

            SELECT package_image

            FROM estimator_packages

            WHERE id = :id

            LIMIT 1
        ";

        $fetchStmt =
        $conn->prepare($fetchQuery);

        $fetchStmt->execute([

            ':id' => $packageId
        ]);

        $package =
        $fetchStmt->fetch();

        /*
        |--------------------------------------------------------------------------
        | DELETE IMAGE
        |--------------------------------------------------------------------------
        */

        if (

            $package

            &&

            !empty($package['package_image'])
        ) {

            $imagePath =
            ROOT_PATH
            .
            '/uploads/estimator-packages/'
            .
            $package['package_image'];

            if (file_exists($imagePath)) {

                unlink($imagePath);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE RECORD
        |--------------------------------------------------------------------------
        */

        $deleteQuery = "

            DELETE FROM estimator_packages

            WHERE id = :id
        ";

        $deleteStmt =
        $conn->prepare($deleteQuery);

        $deleteStmt->execute([

            ':id' => $packageId
        ]);

        $_SESSION['success'] =
        'Package deleted successfully.';

        redirect('admin/estimators/packages.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to delete package.';
    }
}

/*
|--------------------------------------------------------------------------
| FETCH PACKAGES
|--------------------------------------------------------------------------
*/

$packages = [];

try {

    $query = "

        SELECT *

        FROM estimator_packages

        ORDER BY id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute();

    $packages =
    $stmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalPackages =
count($packages);

$activePackages =
count(

    array_filter(

        $packages,

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

    <style>

        .package-grid{

            display:grid;

            grid-template-columns:
            repeat(auto-fill,minmax(320px,1fr));

            gap:24px;
        }

        .package-card{

            background:#fff;

            border-radius:18px;

            overflow:hidden;

            box-shadow:
            0 4px 20px rgba(0,0,0,0.08);

            transition:0.3s;
        }

        .package-card:hover{

            transform:translateY(-5px);
        }

        .package-image{

            width:100%;

            height:220px;

            object-fit:cover;
        }

        .package-content{

            padding:22px;
        }

        .package-price{

            font-size:28px;

            font-weight:700;

            color:#f59e0b;
        }

    </style>

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

                        Estimator Packages

                    </h1>

                    <p>

                        Manage pricing packages displayed in the public estimator.

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

                <div class="col-lg-6">

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

                <div class="col-lg-6">

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

            </div>

            <!-- CREATE FORM -->

            <div class="section-card mb-4">

                <div class="section-header">

                    <h4>

                        Add Estimator Package

                    </h4>

                </div>

                <form
                    method="POST"
                    enctype="multipart/form-data"
                >

                    <?php echo csrfField(); ?>

                    <input
                        type="hidden"
                        name="create_package"
                        value="1"
                    >

                    <div class="row">

                        <!-- PACKAGE NAME -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Package Name

                            </label>

                            <input
                                type="text"
                                name="package_name"
                                class="form-control"
                                required
                            >

                        </div>

                        <!-- PRICE -->

                        <div class="col-lg-3 mb-4">

                            <label class="form-label">

                                Price / Sqft

                            </label>

                            <input
                                type="number"
                                name="price_per_sqft"
                                class="form-control"
                                step="0.01"
                                required
                            >

                        </div>

                        <!-- STATUS -->

                        <div class="col-lg-3 mb-4">

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

                        <!-- IMAGE -->

                        <div class="col-lg-12 mb-4">

                            <label class="form-label">

                                Package Image

                            </label>

                            <input
                                type="file"
                                name="package_image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                        </div>

                        <!-- SHORT DESCRIPTION -->

                        <div class="col-lg-12 mb-4">

                            <label class="form-label">

                                Short Description

                            </label>

                            <textarea
                                name="short_description"
                                rows="3"
                                class="form-control"
                            ></textarea>

                        </div>

                        <!-- FEATURES -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Features

                            </label>

                            <textarea
                                name="features"
                                rows="6"
                                class="form-control"
                                placeholder="
- RCC Structure
- Premium Tiles
- Modular Kitchen
"
                            ></textarea>

                        </div>

                        <!-- SPECIFICATIONS -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Specifications

                            </label>

                            <textarea
                                name="specifications"
                                rows="6"
                                class="form-control"
                            ></textarea>

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="btn-admin"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Create Package

                    </button>

                </form>

            </div>

            <!-- PACKAGES -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Existing Packages

                    </h4>

                </div>

                <?php if(!empty($packages)): ?>

                    <div class="package-grid">

                        <?php foreach($packages as $package): ?>

                            <div class="package-card">

                                <!-- IMAGE -->

                                <?php if(!empty($package['package_image'])): ?>

                                    <img
                                        src="<?php

                                        echo base_url(

                                            '../uploads/estimator-packages/'
                                            .
                                            $package['package_image']
                                        );

                                        ?>"
                                        class="package-image"
                                        alt="Package"
                                    >

                                <?php else: ?>

                                    <div
                                        class="
                                            package-image
                                            d-flex
                                            align-items-center
                                            justify-content-center
                                            bg-light
                                        "
                                    >

                                        <i
                                            class="
                                                bi
                                                bi-box-fill
                                            "
                                            style="
                                                font-size:60px;
                                                color:#d1d5db;
                                            "
                                        ></i>

                                    </div>

                                <?php endif; ?>

                                <!-- CONTENT -->

                                <div class="package-content">

                                    <div class="d-flex justify-content-between align-items-start mb-3">

                                        <div>

                                            <h4>

                                                <?php

                                                echo escape(

                                                    $package['package_name']
                                                );

                                                ?>

                                            </h4>

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

                                        </div>

                                        <div class="package-price">

                                            ₹<?php

                                            echo number_format(

                                                $package['price_per_sqft'],

                                                2
                                            );

                                            ?>

                                        </div>

                                    </div>

                                    <!-- DESCRIPTION -->

                                    <p class="text-muted">

                                        <?php

                                        echo nl2br(

                                            escape(

                                                $package['short_description']
                                                ??
                                                ''
                                            )
                                        );

                                        ?>

                                    </p>

                                    <!-- FEATURES -->

                                    <?php if(!empty($package['features'])): ?>

                                        <div class="mb-3">

                                            <strong>

                                                Features

                                            </strong>

                                            <div class="small text-muted mt-2">

                                                <?php

                                                echo nl2br(

                                                    escape(

                                                        $package['features']
                                                    )
                                                );

                                                ?>

                                            </div>

                                        </div>

                                    <?php endif; ?>

                                    <!-- ACTIONS -->

                                    <div class="d-flex gap-2 mt-4">

                                        <!-- EDIT -->

                                        <a
                                            href="edit-package.php?id=<?php

                                            echo $package['id'];

                                            ?>"
                                            class="btn btn-primary btn-sm"
                                        >

                                            <i class="bi bi-pencil"></i>

                                        </a>

                                        <!-- DELETE -->

                                        <a
                                            href="?delete=<?php

                                            echo $package['id'];

                                            ?>"
                                            class="
                                                btn
                                                btn-danger
                                                btn-sm
                                                btn-delete
                                            "
                                        >

                                            <i class="bi bi-trash"></i>

                                        </a>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5">

                        <i
                            class="
                                bi
                                bi-box-fill
                            "
                            style="
                                font-size:60px;
                                color:#d1d5db;
                            "
                        ></i>

                        <p class="mt-3">

                            No estimator packages created.

                        </p>

                    </div>

                <?php endif; ?>

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