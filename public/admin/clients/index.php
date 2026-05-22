<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CLIENTS MANAGEMENT
|--------------------------------------------------------------------------
| File:
| /public/admin/clients/index.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/formatter.php';

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle =
'Clients Management | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FETCH CLIENTS
|--------------------------------------------------------------------------
*/

$clients = [];

try {

    $query = "

        SELECT
            id,
            full_name,
            email,
            phone,
            status,
            phone_verified,
            created_at,
            last_login

        FROM users

        WHERE role = 'client'

        ORDER BY id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute();

    $clients =
    $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to fetch clients.';
}

/*
|--------------------------------------------------------------------------
| TOTAL CLIENTS
|--------------------------------------------------------------------------
*/

$totalClients =
count($clients);

/*
|--------------------------------------------------------------------------
| ACTIVE CLIENTS
|--------------------------------------------------------------------------
*/

$activeClients =
count(

    array_filter(

        $clients,

        function($client){

            return

            $client['status']
            ===
            'active';
        }
    )
);

/*
|--------------------------------------------------------------------------
| VERIFIED CLIENTS
|--------------------------------------------------------------------------
*/

$verifiedClients =
count(

    array_filter(

        $clients,

        function($client){

            return

            !empty(
                $client['phone_verified']
            );
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

            <!-- ============================== -->
            <!-- HEADER -->
            <!-- ============================== -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Clients Management

                    </h1>

                    <p>

                        Manage construction clients and portal users.

                    </p>

                </div>

                <div>

                    <a
                        href="../users/create.php"
                        class="btn-admin"
                    >

                        <i class="bi bi-person-plus-fill"></i>

                        Add Client

                    </a>

                </div>

            </div>

            <!-- ============================== -->
            <!-- STATS -->
            <!-- ============================== -->

            <div class="row g-4 mb-4">

                <!-- TOTAL -->

                <div class="col-lg-4">

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
                                    $totalClients
                                );

                                ?>

                            </h3>

                            <p>

                                Total Clients

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
                                    $activeClients
                                );

                                ?>

                            </h3>

                            <p>

                                Active Clients

                            </p>

                        </div>

                    </div>

                </div>

                <!-- VERIFIED -->

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-warning"
                        >

                            <i class="bi bi-patch-check-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $verifiedClients
                                );

                                ?>

                            </h3>

                            <p>

                                Verified Clients

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ============================== -->
            <!-- ALERTS -->
            <!-- ============================== -->

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

            <!-- ============================== -->
            <!-- CLIENT TABLE -->
            <!-- ============================== -->

            <div class="section-card">

                <!-- SEARCH -->

                <div class="row mb-4">

                    <div class="col-lg-4">

                        <input
                            type="text"
                            class="form-control table-search"
                            data-table="#clientsTable"
                            placeholder="Search clients..."
                        >

                    </div>

                </div>

                <!-- TABLE -->

                <div class="table-responsive">

                    <table
                        class="table admin-table"
                        id="clientsTable"
                    >

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Client</th>

                                <th>Contact</th>

                                <th>Status</th>

                                <th>Verification</th>

                                <th>Last Login</th>

                                <th>Joined</th>

                                <th width="220">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($clients)): ?>

                                <?php foreach($clients as $client): ?>

                                    <tr>

                                        <!-- ID -->

                                        <td>

                                            #<?php echo $client['id']; ?>

                                        </td>

                                        <!-- CLIENT -->

                                        <td>

                                            <div class="d-flex align-items-center gap-3">

                                                <div class="admin-avatar">

                                                    <?php

                                                    echo strtoupper(

                                                        substr(

                                                            $client['full_name'],

                                                            0,

                                                            1
                                                        )
                                                    );

                                                    ?>

                                                </div>

                                                <div>

                                                    <strong>

                                                        <?php

                                                        echo escape(

                                                            $client['full_name']
                                                        );

                                                        ?>

                                                    </strong>

                                                </div>

                                            </div>

                                        </td>

                                        <!-- CONTACT -->

                                        <td>

                                            <div>

                                                <div>

                                                    <?php

                                                    echo escape(

                                                        $client['email']
                                                    );

                                                    ?>

                                                </div>

                                                <small class="text-muted">

                                                    <?php

                                                    echo escape(

                                                        $client['phone']
                                                        ??
                                                        'N/A'
                                                    );

                                                    ?>

                                                </small>

                                            </div>

                                        </td>

                                        <!-- STATUS -->

                                        <td>

                                            <span class="badge

                                                <?php

                                                if(

                                                    $client['status']
                                                    ===
                                                    'active'
                                                ){

                                                    echo 'bg-success';

                                                }else{

                                                    echo 'bg-warning';
                                                }

                                                ?>
                                            ">

                                                <?php

                                                echo ucfirst(

                                                    escape(

                                                        $client['status']
                                                    )
                                                );

                                                ?>

                                            </span>

                                        </td>

                                        <!-- VERIFICATION -->

                                        <td>

                                            <?php if(!empty($client['phone_verified'])): ?>

                                                <span class="badge bg-primary">

                                                    Verified

                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-danger">

                                                    Unverified

                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <!-- LAST LOGIN -->

                                        <td>

                                            <?php

                                            echo !empty(

                                                $client['last_login']
                                            )

                                            ?

                                            date(

                                                'd M Y',

                                                strtotime(

                                                    $client['last_login']
                                                )
                                            )

                                            :

                                            'Never';

                                            ?>

                                        </td>

                                        <!-- JOINED -->

                                        <td>

                                            <?php

                                            echo date(

                                                'd M Y',

                                                strtotime(

                                                    $client['created_at']
                                                )
                                            );

                                            ?>

                                        </td>

                                        <!-- ACTIONS -->

                                        <td>

                                            <div class="d-flex gap-2 flex-wrap">

                                                <!-- VIEW -->

                                                <a
                                                    href="../users/view.php?id=<?php echo $client['id']; ?>"
                                                    class="btn btn-sm btn-dark"
                                                >

                                                    <i class="bi bi-eye"></i>

                                                </a>

                                                <!-- EDIT -->

                                                <a
                                                    href="../users/edit.php?id=<?php echo $client['id']; ?>"
                                                    class="btn btn-sm btn-primary"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>

                                                <!-- PROJECTS -->

                                                <a
                                                    href="projects.php?id=<?php echo $client['id']; ?>"
                                                    class="btn btn-sm btn-success"
                                                >

                                                    <i class="bi bi-building"></i>

                                                </a>

                                                <!-- PAYMENTS -->

                                                <a
                                                    href="payments.php?id=<?php echo $client['id']; ?>"
                                                    class="btn btn-sm btn-warning"
                                                >

                                                    <i class="bi bi-credit-card"></i>

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
                                                class="bi bi-people"
                                                style="
                                                    font-size:50px;
                                                    color:#d1d5db;
                                                "
                                            ></i>

                                            <p class="mt-3">

                                                No clients found.

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

<!-- ================================= -->
<!-- GLOBAL LOADER -->
<!-- ================================= -->

<div
    id="globalLoader"
    style="
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,0.4);
        z-index:9999;
        align-items:center;
        justify-content:center;
    "
>

    <div class="spinner-border text-warning">

    </div>

</div>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin JS -->

<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>

</body>

</html>