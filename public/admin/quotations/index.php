<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| QUOTATIONS MANAGEMENT
|--------------------------------------------------------------------------
| File:
| /public/admin/quotations/index.php
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
'Quotation Management | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FETCH QUOTATIONS
|--------------------------------------------------------------------------
*/

$quotations = [];

try {

    $query = "

        SELECT
            q.*,

            u.full_name AS client_name,

            p.project_name

        FROM quotations q

        LEFT JOIN users u
        ON q.client_id = u.id

        LEFT JOIN projects p
        ON q.project_id = p.id

        ORDER BY q.id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute();

    $quotations =
    $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to load quotations.';
}

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalQuotations =
count($quotations);

$totalValue =
array_sum(

    array_column(

        $quotations,

        'grand_total'
    )
);

$approvedQuotations =
count(

    array_filter(

        $quotations,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'approved';
        }
    )
);

$pendingQuotations =
count(

    array_filter(

        $quotations,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'pending';
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

                        Quotation Management

                    </h1>

                    <p>

                        Manage customer quotations, approvals and invoices.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="create.php"
                        class="btn-admin"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Create Quotation

                    </a>

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

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-primary"
                        >

                            <i class="bi bi-file-earmark-text-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalQuotations
                                );

                                ?>

                            </h3>

                            <p>

                                Total Quotations

                            </p>

                        </div>

                    </div>

                </div>

                <!-- APPROVED -->

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
                                    $approvedQuotations
                                );

                                ?>

                            </h3>

                            <p>

                                Approved

                            </p>

                        </div>

                    </div>

                </div>

                <!-- PENDING -->

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
                                    $pendingQuotations
                                );

                                ?>

                            </h3>

                            <p>

                                Pending

                            </p>

                        </div>

                    </div>

                </div>

                <!-- VALUE -->

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
                                    $totalValue
                                );

                                ?>

                            </h3>

                            <p>

                                Total Value

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

                        Quotations List

                    </h4>

                </div>

                <!-- SEARCH -->

                <div class="row mb-4">

                    <div class="col-lg-4">

                        <input
                            type="text"
                            class="form-control table-search"
                            data-table="#quotationTable"
                            placeholder="Search quotations..."
                        >

                    </div>

                </div>

                <!-- TABLE -->

                <div class="table-responsive">

                    <table
                        class="table admin-table"
                        id="quotationTable"
                    >

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Quotation No</th>

                                <th>Client</th>

                                <th>Project</th>

                                <th>Amount</th>

                                <th>Status</th>

                                <th>Date</th>

                                <th width="220">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($quotations)): ?>

                                <?php foreach($quotations as $quotation): ?>

                                    <tr>

                                        <!-- ID -->

                                        <td>

                                            #<?php echo $quotation['id']; ?>

                                        </td>

                                        <!-- QUOTATION NUMBER -->

                                        <td>

                                            <strong>

                                                <?php

                                                echo escape(

                                                    $quotation['quotation_number']
                                                    ??
                                                    'N/A'
                                                );

                                                ?>

                                            </strong>

                                        </td>

                                        <!-- CLIENT -->

                                        <td>

                                            <?php

                                            echo escape(

                                                $quotation['client_name']
                                                ??
                                                'N/A'
                                            );

                                            ?>

                                        </td>

                                        <!-- PROJECT -->

                                        <td>

                                            <?php

                                            echo escape(

                                                $quotation['project_name']
                                                ??
                                                'N/A'
                                            );

                                            ?>

                                        </td>

                                        <!-- AMOUNT -->

                                        <td>

                                            <strong>

                                                ₹<?php

                                                echo number_format(

                                                    $quotation['grand_total']
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

                                                $quotation['status']
                                                ??
                                                'pending'
                                            );

                                            ?>

                                            <span class="badge

                                                <?php

                                                if($status === 'approved'){

                                                    echo 'bg-success';

                                                }elseif($status === 'pending'){

                                                    echo 'bg-warning';

                                                }elseif($status === 'rejected'){

                                                    echo 'bg-danger';

                                                }else{

                                                    echo 'bg-secondary';
                                                }

                                                ?>
                                            ">

                                                <?php

                                                echo ucfirst($status);

                                                ?>

                                            </span>

                                        </td>

                                        <!-- DATE -->

                                        <td>

                                            <?php

                                            echo !empty(

                                                $quotation['created_at']
                                            )

                                            ?

                                            date(

                                                'd M Y',

                                                strtotime(

                                                    $quotation['created_at']
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
                                                    href="view.php?id=<?php echo $quotation['id']; ?>"
                                                    class="btn btn-sm btn-dark"
                                                >

                                                    <i class="bi bi-eye"></i>

                                                </a>

                                                <!-- EDIT -->

                                                <a
                                                    href="edit.php?id=<?php echo $quotation['id']; ?>"
                                                    class="btn btn-sm btn-primary"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>

                                                <!-- PDF -->

                                                <a
                                                    href="pdf.php?id=<?php echo $quotation['id']; ?>"
                                                    target="_blank"
                                                    class="btn btn-sm btn-warning"
                                                >

                                                    <i class="bi bi-file-earmark-pdf"></i>

                                                </a>

                                                <!-- DELETE -->

                                                <a
                                                    href="delete.php?id=<?php echo $quotation['id']; ?>"
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

                                    <td colspan="8">

                                        <div class="text-center py-5">

                                            <i
                                                class="
                                                    bi
                                                    bi-file-earmark-text
                                                "
                                                style="
                                                    font-size:60px;
                                                    color:#d1d5db;
                                                "
                                            ></i>

                                            <p class="mt-3">

                                                No quotations found.

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