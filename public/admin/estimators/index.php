<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| COST ESTIMATOR REQUESTS
|--------------------------------------------------------------------------
| File:
| /public/admin/estimators/index.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/formatter.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
'Estimation Requests | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FETCH ESTIMATOR REQUESTS
|--------------------------------------------------------------------------
*/

$estimators = [];

try {

    $query = "

        SELECT *

        FROM estimators

        ORDER BY id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute();

    $estimators =
    $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to fetch estimator requests.';
}

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalEstimators =
count($estimators);

$totalBudget =
array_sum(

    array_column(

        $estimators,

        'estimated_cost'
    )
);

$newRequests =
count(

    array_filter(

        $estimators,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'new';
        }
    )
);

$convertedRequests =
count(

    array_filter(

        $estimators,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'converted';
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

            <!-- ================================= -->
            <!-- PAGE HEADER -->
            <!-- ================================= -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Cost Estimator Requests

                    </h1>

                    <p>

                        Manage online construction cost estimator inquiries.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="export.php"
                        class="btn-admin"
                    >

                        <i class="bi bi-download"></i>

                        Export Data

                    </a>

                </div>

            </div>

            <!-- ================================= -->
            <!-- ALERTS -->
            <!-- ================================= -->

            <?php if(isset($_SESSION['success'])): ?>

                <div class="alert alert-success alert-auto-dismiss">

                    <?php

                    echo escape(
                        $_SESSION['success']
                    );

                    unset($_SESSION['success']);

                    ?>

                </div>

            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>

                <div class="alert alert-danger alert-auto-dismiss">

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

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-primary"
                        >

                            <i class="bi bi-calculator-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalEstimators
                                );

                                ?>

                            </h3>

                            <p>

                                Total Requests

                            </p>

                        </div>

                    </div>

                </div>

                <!-- NEW -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-warning"
                        >

                            <i class="bi bi-clock-history"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $newRequests
                                );

                                ?>

                            </h3>

                            <p>

                                New Requests

                            </p>

                        </div>

                    </div>

                </div>

                <!-- CONVERTED -->

                <div class="col-lg-3">

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
                                    $convertedRequests
                                );

                                ?>

                            </h3>

                            <p>

                                Converted Leads

                            </p>

                        </div>

                    </div>

                </div>

                <!-- BUDGET -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-danger"
                        >

                            <i class="bi bi-currency-rupee"></i>

                        </div>

                        <div>

                            <h3>

                                ₹<?php

                                echo number_format(
                                    $totalBudget
                                );

                                ?>

                            </h3>

                            <p>

                                Estimated Revenue

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ================================= -->
            <!-- TABLE -->
            <!-- ================================= -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Estimator Requests

                    </h4>

                </div>

                <!-- SEARCH -->

                <div class="row mb-4">

                    <div class="col-lg-4">

                        <input
                            type="text"
                            class="form-control table-search"
                            data-table="#estimatorsTable"
                            placeholder="Search requests..."
                        >

                    </div>

                </div>

                <!-- TABLE -->

                <div class="table-responsive">

                    <table
                        class="table admin-table"
                        id="estimatorsTable"
                    >

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Customer</th>

                                <th>Phone</th>

                                <th>Location</th>

                                <th>Area</th>

                                <th>Package</th>

                                <th>Estimated Cost</th>

                                <th>Status</th>

                                <th>Date</th>

                                <th width="220">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($estimators)): ?>

                                <?php foreach($estimators as $item): ?>

                                    <tr>

                                        <!-- ID -->

                                        <td>

                                            #<?php echo $item['id']; ?>

                                        </td>

                                        <!-- CUSTOMER -->

                                        <td>

                                            <div>

                                                <strong>

                                                    <?php

                                                    echo escape(

                                                        $item['name']
                                                        ??
                                                        'N/A'
                                                    );

                                                    ?>

                                                </strong>

                                                <br>

                                                <small class="text-muted">

                                                    <?php

                                                    echo escape(

                                                        $item['email']
                                                        ??
                                                        'No email'
                                                    );

                                                    ?>

                                                </small>

                                            </div>

                                        </td>

                                        <!-- PHONE -->

                                        <td>

                                            <?php

                                            echo escape(

                                                $item['phone']
                                                ??
                                                'N/A'
                                            );

                                            ?>

                                        </td>

                                        <!-- LOCATION -->

                                        <td>

                                            <?php

                                            echo escape(

                                                $item['location']
                                                ??
                                                'N/A'
                                            );

                                            ?>

                                        </td>

                                        <!-- AREA -->

                                        <td>

                                            <?php

                                            echo number_format(

                                                $item['area']
                                                ??
                                                0
                                            );

                                            ?>

                                            sqft

                                        </td>

                                        <!-- PACKAGE -->

                                        <td>

                                            <span class="badge bg-dark">

                                                <?php

                                                echo ucfirst(

                                                    escape(

                                                        $item['package']
                                                        ??
                                                        'Basic'
                                                    )
                                                );

                                                ?>

                                            </span>

                                        </td>

                                        <!-- ESTIMATE -->

                                        <td>

                                            <strong>

                                                ₹<?php

                                                echo number_format(

                                                    $item['estimated_cost']
                                                    ??
                                                    0
                                                );

                                                ?>

                                            </strong>

                                        </td>

                                        <!-- STATUS -->

                                        <td>

                                            <?php

                                            $status =
                                            strtolower(

                                                $item['status']
                                                ??
                                                'new'
                                            );

                                            ?>

                                            <span class="badge

                                                <?php

                                                if($status === 'converted'){

                                                    echo 'bg-success';

                                                }elseif($status === 'new'){

                                                    echo 'bg-primary';

                                                }elseif($status === 'follow_up'){

                                                    echo 'bg-warning';

                                                }else{

                                                    echo 'bg-secondary';
                                                }

                                                ?>
                                            ">

                                                <?php

                                                echo ucfirst(

                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $status
                                                    )
                                                );

                                                ?>

                                            </span>

                                        </td>

                                        <!-- DATE -->

                                        <td>

                                            <?php

                                            echo !empty(

                                                $item['created_at']
                                            )

                                            ?

                                            date(

                                                'd M Y',

                                                strtotime(

                                                    $item['created_at']
                                                )
                                            )

                                            :

                                            'N/A';

                                            ?>

                                        </td>

                                        <!-- ACTIONS -->

                                        <td>

                                            <div class="d-flex gap-2 flex-wrap">

                                                <!-- VIEW -->

                                                <a
                                                    href="view.php?id=<?php echo $item['id']; ?>"
                                                    class="btn btn-sm btn-dark"
                                                >

                                                    <i class="bi bi-eye"></i>

                                                </a>

                                                <!-- EDIT -->

                                                <a
                                                    href="edit.php?id=<?php echo $item['id']; ?>"
                                                    class="btn btn-sm btn-primary"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>

                                                <!-- CONVERT -->

                                                <a
                                                    href="convert.php?id=<?php echo $item['id']; ?>"
                                                    class="btn btn-sm btn-success"
                                                >

                                                    <i class="bi bi-check-circle"></i>

                                                </a>

                                                <!-- DELETE -->

                                                <a
                                                    href="delete.php?id=<?php echo $item['id']; ?>"
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

                                    <td colspan="10">

                                        <div class="text-center py-5">

                                            <i
                                                class="bi bi-calculator"
                                                style="
                                                    font-size:60px;
                                                    color:#d1d5db;
                                                "
                                            ></i>

                                            <p class="mt-3">

                                                No estimator requests found.

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