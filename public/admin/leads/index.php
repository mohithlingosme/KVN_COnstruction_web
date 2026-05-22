<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| LEADS MANAGEMENT
|--------------------------------------------------------------------------
| File:
| /public/admin/leads/index.php
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
'Leads Management | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FETCH LEADS
|--------------------------------------------------------------------------
*/

$leads = [];

try {

    $query = "

        SELECT
            id,
            name,
            email,
            phone,
            lead_source,
            lead_type,
            status,
            budget,
            assigned_to,
            created_at

        FROM leads

        ORDER BY id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute();

    $leads =
    $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to fetch leads.';
}

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalLeads =
count($leads);

$newLeads =
count(

    array_filter(

        $leads,

        function($lead){

            return

            strtolower(
                $lead['status']
            )

            ===

            'new';
        }
    )
);

$convertedLeads =
count(

    array_filter(

        $leads,

        function($lead){

            return

            strtolower(
                $lead['status']
            )

            ===

            'converted';
        }
    )
);

$pendingLeads =
count(

    array_filter(

        $leads,

        function($lead){

            return

            strtolower(
                $lead['status']
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
            <!-- HEADER -->
            <!-- ================================= -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Leads Management

                    </h1>

                    <p>

                        Manage CRM leads, inquiries and estimator requests.

                    </p>

                </div>

                <div>

                    <a
                        href="create.php"
                        class="btn-admin"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Add Lead

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

                            <i class="bi bi-people-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalLeads
                                );

                                ?>

                            </h3>

                            <p>

                                Total Leads

                            </p>

                        </div>

                    </div>

                </div>

                <!-- NEW -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-success"
                        >

                            <i class="bi bi-person-plus-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $newLeads
                                );

                                ?>

                            </h3>

                            <p>

                                New Leads

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

                            <i class="bi bi-hourglass-split"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $pendingLeads
                                );

                                ?>

                            </h3>

                            <p>

                                Pending Leads

                            </p>

                        </div>

                    </div>

                </div>

                <!-- CONVERTED -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-info"
                        >

                            <i class="bi bi-check-circle-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $convertedLeads
                                );

                                ?>

                            </h3>

                            <p>

                                Converted Leads

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ================================= -->
            <!-- LEADS TABLE -->
            <!-- ================================= -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Lead Records

                    </h4>

                </div>

                <!-- SEARCH -->

                <div class="row mb-4">

                    <div class="col-lg-4">

                        <input
                            type="text"
                            class="form-control table-search"
                            data-table="#leadsTable"
                            placeholder="Search leads..."
                        >

                    </div>

                </div>

                <!-- TABLE -->

                <div class="table-responsive">

                    <table
                        class="table admin-table"
                        id="leadsTable"
                    >

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Lead</th>

                                <th>Contact</th>

                                <th>Type</th>

                                <th>Source</th>

                                <th>Budget</th>

                                <th>Status</th>

                                <th>Assigned To</th>

                                <th>Created</th>

                                <th width="180">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($leads)): ?>

                                <?php foreach($leads as $lead): ?>

                                    <tr>

                                        <!-- ID -->

                                        <td>

                                            #<?php echo $lead['id']; ?>

                                        </td>

                                        <!-- LEAD -->

                                        <td>

                                            <strong>

                                                <?php

                                                echo escape(
                                                    $lead['name']
                                                );

                                                ?>

                                            </strong>

                                        </td>

                                        <!-- CONTACT -->

                                        <td>

                                            <div>

                                                <div>

                                                    <?php

                                                    echo escape(
                                                        $lead['email']
                                                        ??
                                                        'N/A'
                                                    );

                                                    ?>

                                                </div>

                                                <small class="text-muted">

                                                    <?php

                                                    echo escape(
                                                        $lead['phone']
                                                        ??
                                                        'N/A'
                                                    );

                                                    ?>

                                                </small>

                                            </div>

                                        </td>

                                        <!-- TYPE -->

                                        <td>

                                            <?php

                                            echo ucfirst(

                                                escape(

                                                    $lead['lead_type']
                                                    ??
                                                    'General'
                                                )
                                            );

                                            ?>

                                        </td>

                                        <!-- SOURCE -->

                                        <td>

                                            <?php

                                            echo ucfirst(

                                                escape(

                                                    $lead['lead_source']
                                                    ??
                                                    'Website'
                                                )
                                            );

                                            ?>

                                        </td>

                                        <!-- BUDGET -->

                                        <td>

                                            ₹<?php

                                            echo number_format(

                                                $lead['budget']
                                                ??
                                                0
                                            );

                                            ?>

                                        </td>

                                        <!-- STATUS -->

                                        <td>

                                            <?php

                                            $status =
                                            strtolower(
                                                $lead['status']
                                            );

                                            ?>

                                            <span class="badge

                                                <?php

                                                if($status === 'new'){

                                                    echo 'bg-primary';

                                                }elseif($status === 'converted'){

                                                    echo 'bg-success';

                                                }elseif($status === 'pending'){

                                                    echo 'bg-warning';

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

                                        <!-- ASSIGNED -->

                                        <td>

                                            <?php

                                            echo escape(

                                                $lead['assigned_to']
                                                ??
                                                'Unassigned'
                                            );

                                            ?>

                                        </td>

                                        <!-- CREATED -->

                                        <td>

                                            <?php

                                            echo date(

                                                'd M Y',

                                                strtotime(

                                                    $lead['created_at']
                                                )
                                            );

                                            ?>

                                        </td>

                                        <!-- ACTIONS -->

                                        <td>

                                            <div class="d-flex gap-2 flex-wrap">

                                                <!-- VIEW -->

                                                <a
                                                    href="view.php?id=<?php echo $lead['id']; ?>"
                                                    class="btn btn-sm btn-dark"
                                                >

                                                    <i class="bi bi-eye"></i>

                                                </a>

                                                <!-- EDIT -->

                                                <a
                                                    href="edit.php?id=<?php echo $lead['id']; ?>"
                                                    class="btn btn-sm btn-primary"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>

                                                <!-- DELETE -->

                                                <a
                                                    href="delete.php?id=<?php echo $lead['id']; ?>"
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
                                                class="bi bi-people"
                                                style="
                                                    font-size:50px;
                                                    color:#d1d5db;
                                                "
                                            ></i>

                                            <p class="mt-3">

                                                No leads found.

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