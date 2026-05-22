<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CLIENT VIEW PAGE
|--------------------------------------------------------------------------
| File:
| /public/admin/clients/view.php
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

$query = "

    SELECT *

    FROM users

    WHERE id = :id

    AND role = 'client'

    LIMIT 1
";

$stmt =
$conn->prepare($query);

$stmt->execute([

    ':id' => $clientId
]);

$client =
$stmt->fetch();

if (!$client) {

    $_SESSION['error'] =
    'Client not found.';

    redirect('admin/clients/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH CLIENT PROJECTS
|--------------------------------------------------------------------------
*/

$projects = [];

try {

    $projectQuery = "

        SELECT
            id,
            project_name,
            status,
            budget,
            created_at

        FROM projects

        WHERE client_id = :client_id

        ORDER BY id DESC
    ";

    $projectStmt =
    $conn->prepare($projectQuery);

    $projectStmt->execute([

        ':client_id' => $clientId
    ]);

    $projects =
    $projectStmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| FETCH PAYMENTS
|--------------------------------------------------------------------------
*/

$payments = [];

try {

    $paymentQuery = "

        SELECT
            id,
            amount,
            payment_status,
            payment_date

        FROM payments

        WHERE client_id = :client_id

        ORDER BY id DESC

        LIMIT 10
    ";

    $paymentStmt =
    $conn->prepare($paymentQuery);

    $paymentStmt->execute([

        ':client_id' => $clientId
    ]);

    $payments =
    $paymentStmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| FETCH SECURITY LOGS
|--------------------------------------------------------------------------
*/

$logs = [];

try {

    $logQuery = "

        SELECT
            event,
            severity,
            description,
            created_at

        FROM security_logs

        WHERE user_id = :user_id

        ORDER BY id DESC

        LIMIT 10
    ";

    $logStmt =
    $conn->prepare($logQuery);

    $logStmt->execute([

        ':user_id' => $clientId
    ]);

    $logs =
    $logStmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| PROFILE IMAGE
|--------------------------------------------------------------------------
*/

$profileImage =
!empty($client['profile_image'])

?

base_url(

    '../uploads/users/'
    .
    $client['profile_image']
)

:

'https://via.placeholder.com/150';

$pageTitle =
$client['full_name']
.
' | Client Profile';

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

                        Client Profile

                    </h1>

                    <p>

                        Complete client overview and activity.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="../users/edit.php?id=<?php echo $client['id']; ?>"
                        class="btn-admin"
                    >

                        <i class="bi bi-pencil-square"></i>

                        Edit Client

                    </a>

                    <a
                        href="index.php"
                        class="btn btn-dark"
                    >

                        Back

                    </a>

                </div>

            </div>

            <!-- ================================= -->
            <!-- PROFILE SECTION -->
            <!-- ================================= -->

            <div class="row g-4">

                <!-- PROFILE -->

                <div class="col-lg-4">

                    <div class="profile-card">

                        <img
                            src="<?php echo escape($profileImage); ?>"
                            alt="Client"
                            class="img-fluid rounded-circle mb-4"
                            style="
                                width:150px;
                                height:150px;
                                object-fit:cover;
                            "
                        >

                        <h3>

                            <?php

                            echo escape(
                                $client['full_name']
                            );

                            ?>

                        </h3>

                        <p class="text-muted">

                            Client Account

                        </p>

                        <!-- STATUS -->

                        <span class="badge

                            <?php

                            if($client['status'] === 'active'){

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

                        <!-- PHONE VERIFIED -->

                        <div class="mt-3">

                            <?php if(!empty($client['phone_verified'])): ?>

                                <span class="badge bg-primary">

                                    <i class="bi bi-patch-check-fill"></i>

                                    Phone Verified

                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

                <!-- DETAILS -->

                <div class="col-lg-8">

                    <div class="section-card">

                        <div class="section-header">

                            <h4>

                                Client Details

                            </h4>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Full Name

                                </label>

                                <h6>

                                    <?php

                                    echo escape(
                                        $client['full_name']
                                    );

                                    ?>

                                </h6>

                            </div>

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Email Address

                                </label>

                                <h6>

                                    <?php

                                    echo escape(
                                        $client['email']
                                    );

                                    ?>

                                </h6>

                            </div>

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Phone Number

                                </label>

                                <h6>

                                    <?php

                                    echo escape(
                                        $client['phone']
                                        ??
                                        'N/A'
                                    );

                                    ?>

                                </h6>

                            </div>

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Joined Date

                                </label>

                                <h6>

                                    <?php

                                    echo date(

                                        'd M Y h:i A',

                                        strtotime(

                                            $client['created_at']
                                        )
                                    );

                                    ?>

                                </h6>

                            </div>

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Last Login

                                </label>

                                <h6>

                                    <?php

                                    echo !empty(
                                        $client['last_login']
                                    )

                                    ?

                                    date(

                                        'd M Y h:i A',

                                        strtotime(

                                            $client['last_login']
                                        )
                                    )

                                    :

                                    'Never';

                                    ?>

                                </h6>

                            </div>

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Last IP

                                </label>

                                <h6>

                                    <?php

                                    echo escape(
                                        $client['last_ip']
                                        ??
                                        'N/A'
                                    );

                                    ?>

                                </h6>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ================================= -->
            <!-- PROJECTS -->
            <!-- ================================= -->

            <div class="section-card mt-4">

                <div class="section-header">

                    <h4>

                        Client Projects

                    </h4>

                </div>

                <?php if(!empty($projects)): ?>

                    <div class="table-responsive">

                        <table class="table admin-table">

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>Project</th>

                                    <th>Status</th>

                                    <th>Budget</th>

                                    <th>Created</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach($projects as $project): ?>

                                    <tr>

                                        <td>

                                            #<?php echo $project['id']; ?>

                                        </td>

                                        <td>

                                            <?php

                                            echo escape(
                                                $project['project_name']
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <span class="badge bg-primary">

                                                <?php

                                                echo ucfirst(

                                                    escape(
                                                        $project['status']
                                                    )
                                                );

                                                ?>

                                            </span>

                                        </td>

                                        <td>

                                            ₹<?php

                                            echo number_format(

                                                $project['budget']
                                                ??
                                                0
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <?php

                                            echo date(

                                                'd M Y',

                                                strtotime(

                                                    $project['created_at']
                                                )
                                            );

                                            ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5">

                        No projects found.

                    </div>

                <?php endif; ?>

            </div>

            <!-- ================================= -->
            <!-- PAYMENTS -->
            <!-- ================================= -->

            <div class="section-card mt-4">

                <div class="section-header">

                    <h4>

                        Recent Payments

                    </h4>

                </div>

                <?php if(!empty($payments)): ?>

                    <div class="table-responsive">

                        <table class="table admin-table">

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>Amount</th>

                                    <th>Status</th>

                                    <th>Date</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach($payments as $payment): ?>

                                    <tr>

                                        <td>

                                            #<?php echo $payment['id']; ?>

                                        </td>

                                        <td>

                                            ₹<?php

                                            echo number_format(

                                                $payment['amount']
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <span class="badge bg-success">

                                                <?php

                                                echo ucfirst(

                                                    escape(
                                                        $payment['payment_status']
                                                    )
                                                );

                                                ?>

                                            </span>

                                        </td>

                                        <td>

                                            <?php

                                            echo date(

                                                'd M Y',

                                                strtotime(

                                                    $payment['payment_date']
                                                )
                                            );

                                            ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5">

                        No payment history found.

                    </div>

                <?php endif; ?>

            </div>

            <!-- ================================= -->
            <!-- SECURITY LOGS -->
            <!-- ================================= -->

            <div class="section-card mt-4">

                <div class="section-header">

                    <h4>

                        Security Logs

                    </h4>

                </div>

                <?php if(!empty($logs)): ?>

                    <div class="activity-list">

                        <?php foreach($logs as $log): ?>

                            <div class="activity-item">

                                <div class="activity-icon">

                                    <i class="bi bi-shield-lock"></i>

                                </div>

                                <div class="activity-content">

                                    <h6>

                                        <?php

                                        echo escape(
                                            $log['event']
                                        );

                                        ?>

                                    </h6>

                                    <small>

                                        <?php

                                        echo escape(
                                            $log['description']
                                            ??
                                            'No description'
                                        );

                                        ?>

                                    </small>

                                    <br>

                                    <small class="text-muted">

                                        <?php

                                        echo date(

                                            'd M Y h:i A',

                                            strtotime(

                                                $log['created_at']
                                            )
                                        );

                                        ?>

                                    </small>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5">

                        No security logs found.

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