<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| VIEW PROJECT
|--------------------------------------------------------------------------
| File:
| /public/admin/projects/view.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/formatter.php';

require_once '../../../includes/repositories.php';

/*
|--------------------------------------------------------------------------
| VALIDATE PROJECT ID
|--------------------------------------------------------------------------
*/

$projectId =
(int) ($_GET['id'] ?? 0);

if ($projectId <= 0) {

    $_SESSION['error'] =
    'Invalid project ID.';

    redirect('admin/projects/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH PROJECT
|--------------------------------------------------------------------------
*/

$projectRepo = repo('Project');

$project =
$projectRepo ? $projectRepo->findByIdWithClient($projectId) : null;

if (!$project) {

    $_SESSION['error'] =
    'Project not found.';

    redirect('admin/projects/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH PROJECT TASKS
|--------------------------------------------------------------------------
*/

$tasks = [];

try {

    if ($projectRepo) {
        $tasks = $projectRepo->getTasks($projectId);
    }

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| FETCH PROJECT PAYMENTS
|--------------------------------------------------------------------------
*/

$payments = [];

try {

    if ($projectRepo) {
        $payments = $projectRepo->getPayments($projectId);
    }

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| FETCH PROJECT FILES
|--------------------------------------------------------------------------
*/

$files = [];

try {

    if ($projectRepo) {
        $files = $projectRepo->getFiles($projectId);
    }

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
$project['project_name']
.
' | Project Details';

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

                        Project Details

                    </h1>

                    <p>

                        Complete project overview and tracking.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="edit.php?id=<?= (int)$project['id'] ?>"
                        class="btn-admin"
                    >

                        <i class="bi bi-pencil-square"></i>

                        Edit Project

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
            <!-- PROJECT OVERVIEW -->
            <!-- ================================= -->

            <div class="row g-4">

                <!-- IMAGE -->

                <div class="col-lg-4">

                    <div class="profile-card">

                        <?php if(!empty($project['project_image'])): ?>

                            <img
                                src="<?php

                                echo base_url(

                                    '../uploads/projects/'
                                    .
                                    $project['project_image']
                                );

                                ?>"
                                alt="Project"
                                class="img-fluid rounded mb-4"
                            >

                        <?php else: ?>

                            <div
                                class="dashboard-icon bg-primary mx-auto"
                                style="
                                    width:120px;
                                    height:120px;
                                    font-size:42px;
                                "
                            >

                                <i class="bi bi-building"></i>

                            </div>

                        <?php endif; ?>

                        <h3>

                            <?php

                            echo escape(
                                $project['project_name']
                            );

                            ?>

                        </h3>

                        <p class="text-muted">

                            <?php

                            echo escape(
                                $project['project_type']
                            );

                            ?>

                        </p>

                        <!-- STATUS -->

                        <?php

                        $status =
                        strtolower(
                            $project['status']
                        );

                        ?>

                        <span class="badge

                            <?php

                            if($status === 'completed'){

                                echo 'bg-success';

                            }elseif($status === 'ongoing'){

                                echo 'bg-warning';

                            }elseif($status === 'pending'){

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

                    </div>

                </div>

                <!-- DETAILS -->

                <div class="col-lg-8">

                    <div class="section-card">

                        <div class="section-header">

                            <h4>

                                Project Information

                            </h4>

                        </div>

                        <div class="row">

                            <!-- CLIENT -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Client Name

                                </label>

                                <h6>

                                    <?php

                                    echo escape(

                                        $project['client_name']
                                        ??
                                        'N/A'
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- EMAIL -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Client Email

                                </label>

                                <h6>

                                    <?php

                                    echo escape(

                                        $project['client_email']
                                        ??
                                        'N/A'
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- PHONE -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Client Phone

                                </label>

                                <h6>

                                    <?php

                                    echo escape(

                                        $project['client_phone']
                                        ??
                                        'N/A'
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- LOCATION -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Project Location

                                </label>

                                <h6>

                                    <?php

                                    echo escape(
                                        $project['location']
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- BUDGET -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Budget

                                </label>

                                <h6>

                                    ₹<?php

                                    echo number_format(

                                        $project['budget']
                                        ??
                                        0
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- PROGRESS -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Progress

                                </label>

                                <div class="progress">

                                    <div
                                        class="progress-bar"

                                        role="progressbar"

                                        style="
                                            width:
                                            <?php

                                            echo (int)

                                            ($project['progress']
                                            ??
                                            0);

                                            ?>%;
                                        "
                                    >

                                        <?php

                                        echo (int)

                                        ($project['progress']
                                        ??
                                        0);

                                        ?>%

                                    </div>

                                </div>

                            </div>

                            <!-- START DATE -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Start Date

                                </label>

                                <h6>

                                    <?php

                                    echo !empty(

                                        $project['start_date']
                                    )

                                    ?

                                    date(

                                        'd M Y',

                                        strtotime(

                                            $project['start_date']
                                        )
                                    )

                                    :

                                    'N/A';

                                    ?>

                                </h6>

                            </div>

                            <!-- END DATE -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    End Date

                                </label>

                                <h6>

                                    <?php

                                    echo !empty(

                                        $project['end_date']
                                    )

                                    ?

                                    date(

                                        'd M Y',

                                        strtotime(

                                            $project['end_date']
                                        )
                                    )

                                    :

                                    'N/A';

                                    ?>

                                </h6>

                            </div>

                            <!-- DESCRIPTION -->

                            <div class="col-lg-12 mb-4">

                                <label class="text-muted">

                                    Description

                                </label>

                                <div class="lead-message-box">

                                    <?php

                                    echo nl2br(

                                        escape(

                                            $project['description']
                                            ??
                                            'No description available.'
                                        )
                                    );

                                    ?>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ================================= -->
            <!-- TASKS -->
            <!-- ================================= -->

            <div class="section-card mt-4">

                <div class="section-header">

                    <h4>

                        Project Tasks

                    </h4>

                </div>

                <?php if(!empty($tasks)): ?>

                    <div class="table-responsive">

                        <table class="table admin-table">

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>Task</th>

                                    <th>Assigned To</th>

                                    <th>Status</th>

                                    <th>Due Date</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach($tasks as $task): ?>

                                    <tr>

                                        <td>

                                            #<?php echo (int)$task['id']; ?>

                                        </td>

                                        <td>

                                            <?php

                                            echo escape(
                                                $task['task_name']
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <?php

                                            echo escape(

                                                $task['assigned_to']
                                                ??
                                                'N/A'
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <span class="badge bg-primary">

                                                <?php

                                                echo ucfirst(

                                                    escape(
                                                        $task['status']
                                                    )
                                                );

                                                ?>

                                            </span>

                                        </td>

                                        <td>

                                            <?php

                                            echo !empty(

                                                $task['due_date']
                                            )

                                            ?

                                            date(

                                                'd M Y',

                                                strtotime(

                                                    $task['due_date']
                                                )
                                            )

                                            :

                                            'N/A';

                                            ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5">

                        No tasks found.

                    </div>

                <?php endif; ?>

            </div>

            <!-- ================================= -->
            <!-- PAYMENTS -->
            <!-- ================================= -->

            <div class="section-card mt-4">

                <div class="section-header">

                    <h4>

                        Project Payments

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

                                    <th>Method</th>

                                    <th>Date</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach($payments as $payment): ?>

                                    <tr>

                                        <td>

                                            #<?php echo (int)$payment['id']; ?>

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

                                            echo ucfirst(

                                                escape(

                                                    $payment['payment_method']
                                                    ??
                                                    'N/A'
                                                )
                                            );

                                            ?>

                                        </td>

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

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5">

                        No payment records found.

                    </div>

                <?php endif; ?>

            </div>

            <!-- ================================= -->
            <!-- FILES -->
            <!-- ================================= -->

            <div class="section-card mt-4">

                <div class="section-header">

                    <h4>

                        Project Files

                    </h4>

                </div>

                <?php if(!empty($files)): ?>

                    <div class="table-responsive">

                        <table class="table admin-table">

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>File</th>

                                    <th>Uploaded By</th>

                                    <th>Uploaded At</th>

                                    <th>Download</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach($files as $file): ?>

                                    <tr>

                                        <td>

                                            #<?php echo (int)$file['id']; ?>

                                        </td>

                                        <td>

                                            <?php

                                            echo escape(
                                                $file['file_name']
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <?php

                                            echo escape(

                                                $file['uploaded_by']
                                                ??
                                                'Admin'
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <?php

                                            echo date(

                                                'd M Y h:i A',

                                                strtotime(

                                                    $file['created_at']
                                                )
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <a
                                                href="<?php

                                                echo base_url(

                                                    '../uploads/project-files/'
                                                    .
                                                    $file['file_name']
                                                );

                                                ?>"
                                                class="btn btn-sm btn-primary"
                                                download
                                            >

                                                <i class="bi bi-download"></i>

                                            </a>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5">

                        No files uploaded.

                    </div>

                <?php endif; ?>

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