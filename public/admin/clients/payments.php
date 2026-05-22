<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CLIENT PAYMENTS
|--------------------------------------------------------------------------
| File:
| /public/admin/clients/payments.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/formatter.php';

/*
|--------------------------------------------------------------------------
| VALIDATE CLIENT ID
|--------------------------------------------------------------------------
*/

$clientId =
(int) ($_GET['id'] ?? 0);

if ($clientId <= 0) {

    $_SESSION['error'] =
    'Invalid client ID.';

    redirect('admin/clients/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH CLIENT
|--------------------------------------------------------------------------
*/

$clientQuery = "

    SELECT
        id,
        full_name,
        email,
        phone,
        profile_image

    FROM users

    WHERE id = :id

    AND role = 'client'

    LIMIT 1
";

$clientStmt =
$conn->prepare($clientQuery);

$clientStmt->execute([

    ':id' => $clientId
]);

$client =
$clientStmt->fetch();

if (!$client) {

    $_SESSION['error'] =
    'Client not found.';

    redirect('admin/clients/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH PAYMENTS
|--------------------------------------------------------------------------
*/

$payments = [];

try {

    $query = "

        SELECT
            id,
            project_id,
            amount,
            payment_method,
            payment_status,
            transaction_id,
            notes,
            payment_date,
            created_at

        FROM payments

        WHERE client_id = :client_id

        ORDER BY id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':client_id' => $clientId
    ]);

    $payments =
    $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to fetch payments.';
}

/*
|--------------------------------------------------------------------------
| PAYMENT STATS
|--------------------------------------------------------------------------
*/

$totalPayments =
count($payments);

$totalPaid =
0;

$totalPending =
0;

foreach($payments as $payment){

    if(

        strtolower(
            $payment['payment_status']
        )

        ===

        'paid'
    ){

        $totalPaid +=
        (float) $payment['amount'];

    } else {

        $totalPending +=
        (float) $payment['amount'];
    }
}

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
$client['full_name']
.
' Payments | '
.
APP_NAME;

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

                        Client Payments

                    </h1>

                    <p>

                        Payment history and transaction records for

                        <strong>

                            <?php

                            echo escape(
                                $client['full_name']
                            );

                            ?>

                        </strong>

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="../payments/create.php?client_id=<?php echo $clientId; ?>"
                        class="btn-admin"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Add Payment

                    </a>

                    <a
                        href="view.php?id=<?php echo $clientId; ?>"
                        class="btn btn-dark"
                    >

                        Back

                    </a>

                </div>

            </div>

            <!-- ================================= -->
            <!-- CLIENT CARD -->
            <!-- ================================= -->

            <div class="section-card mb-4">

                <div class="d-flex align-items-center gap-4">

                    <!-- IMAGE -->

                    <img
                        src="<?php

                        echo !empty(
                            $client['profile_image']
                        )

                        ?

                        base_url(

                            '../uploads/users/'
                            .
                            $client['profile_image']
                        )

                        :

                        'https://via.placeholder.com/100';

                        ?>"
                        alt="Client"
                        class="rounded-circle"
                        style="
                            width:100px;
                            height:100px;
                            object-fit:cover;
                        "
                    >

                    <!-- DETAILS -->

                    <div>

                        <h3>

                            <?php

                            echo escape(
                                $client['full_name']
                            );

                            ?>

                        </h3>

                        <p class="mb-1">

                            <i class="bi bi-envelope"></i>

                            <?php

                            echo escape(
                                $client['email']
                            );

                            ?>

                        </p>

                        <p>

                            <i class="bi bi-telephone"></i>

                            <?php

                            echo escape(
                                $client['phone']
                                ??
                                'N/A'
                            );

                            ?>

                        </p>

                    </div>

                </div>

            </div>

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

                            <i class="bi bi-credit-card"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalPayments
                                );

                                ?>

                            </h3>

                            <p>

                                Total Payments

                            </p>

                        </div>

                    </div>

                </div>

                <!-- PAID -->

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-success"
                        >

                            <i class="bi bi-currency-rupee"></i>

                        </div>

                        <div>

                            <h3>

                                ₹<?php

                                echo number_format(
                                    $totalPaid
                                );

                                ?>

                            </h3>

                            <p>

                                Total Paid

                            </p>

                        </div>

                    </div>

                </div>

                <!-- PENDING -->

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-warning"
                        >

                            <i class="bi bi-hourglass-split"></i>

                        </div>

                        <div>

                            <h3>

                                ₹<?php

                                echo number_format(
                                    $totalPending
                                );

                                ?>

                            </h3>

                            <p>

                                Pending Amount

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ================================= -->
            <!-- PAYMENT TABLE -->
            <!-- ================================= -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Payment History

                    </h4>

                </div>

                <!-- SEARCH -->

                <div class="row mb-4">

                    <div class="col-lg-4">

                        <input
                            type="text"
                            class="form-control table-search"
                            data-table="#paymentsTable"
                            placeholder="Search payments..."
                        >

                    </div>

                </div>

                <!-- TABLE -->

                <div class="table-responsive">

                    <table
                        class="table admin-table"
                        id="paymentsTable"
                    >

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Project</th>

                                <th>Amount</th>

                                <th>Method</th>

                                <th>Status</th>

                                <th>Transaction ID</th>

                                <th>Payment Date</th>

                                <th>Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($payments)): ?>

                                <?php foreach($payments as $payment): ?>

                                    <tr>

                                        <!-- ID -->

                                        <td>

                                            #<?php echo $payment['id']; ?>

                                        </td>

                                        <!-- PROJECT -->

                                        <td>

                                            #<?php

                                            echo escape(
                                                $payment['project_id']
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

                                                    $payment['amount']
                                                    ??
                                                    0
                                                );

                                                ?>

                                            </strong>

                                        </td>

                                        <!-- METHOD -->

                                        <td>

                                            <?php

                                            echo ucfirst(

                                                escape(

                                                    $payment['payment_method']
                                                    ??
                                                    'N/A'
                                                )
                                            );

                                            ?>

                                        </td>

                                        <!-- STATUS -->

                                        <td>

                                            <?php

                                            $status =
                                            strtolower(

                                                $payment['payment_status']
                                                ??
                                                'pending'
                                            );

                                            ?>

                                            <span class="badge

                                                <?php

                                                if($status === 'paid'){

                                                    echo 'bg-success';

                                                }elseif($status === 'pending'){

                                                    echo 'bg-warning';

                                                }else{

                                                    echo 'bg-danger';
                                                }

                                                ?>
                                            ">

                                                <?php

                                                echo ucfirst($status);

                                                ?>

                                            </span>

                                        </td>

                                        <!-- TRANSACTION -->

                                        <td>

                                            <?php

                                            echo escape(

                                                $payment['transaction_id']
                                                ??
                                                'N/A'
                                            );

                                            ?>

                                        </td>

                                        <!-- DATE -->

                                        <td>

                                            <?php

                                            echo !empty(

                                                $payment['payment_date']
                                            )

                                            ?

                                            date(

                                                'd M Y',

                                                strtotime(

                                                    $payment['payment_date']
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
                                                    href="../payments/view.php?id=<?php echo $payment['id']; ?>"
                                                    class="btn btn-sm btn-dark"
                                                >

                                                    <i class="bi bi-eye"></i>

                                                </a>

                                                <!-- EDIT -->

                                                <a
                                                    href="../payments/edit.php?id=<?php echo $payment['id']; ?>"
                                                    class="btn btn-sm btn-primary"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>

                                                <!-- RECEIPT -->

                                                <a
                                                    href="../payments/receipt.php?id=<?php echo $payment['id']; ?>"
                                                    class="btn btn-sm btn-success"
                                                >

                                                    <i class="bi bi-receipt"></i>

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
                                                class="bi bi-credit-card"
                                                style="
                                                    font-size:50px;
                                                    color:#d1d5db;
                                                "
                                            ></i>

                                            <p class="mt-3">

                                                No payments found.

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