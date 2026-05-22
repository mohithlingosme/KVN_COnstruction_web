<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| VIEW LEAD
|--------------------------------------------------------------------------
| File:
| /public/admin/leads/view.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/formatter.php';

/*
|--------------------------------------------------------------------------
| VALIDATE LEAD ID
|--------------------------------------------------------------------------
*/

$leadId =
(int) ($_GET['id'] ?? 0);

if ($leadId <= 0) {

    $_SESSION['error'] =
    'Invalid lead ID.';

    redirect('admin/leads/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH LEAD
|--------------------------------------------------------------------------
*/

$query = "

    SELECT *

    FROM leads

    WHERE id = :id

    LIMIT 1
";

$stmt =
$conn->prepare($query);

$stmt->execute([

    ':id' => $leadId
]);

$lead =
$stmt->fetch();

if (!$lead) {

    $_SESSION['error'] =
    'Lead not found.';

    redirect('admin/leads/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH LEAD ACTIVITIES
|--------------------------------------------------------------------------
*/

$activities = [];

try {

    $activityQuery = "

        SELECT
            id,
            activity_type,
            description,
            created_at

        FROM lead_activities

        WHERE lead_id = :lead_id

        ORDER BY id DESC
    ";

    $activityStmt =
    $conn->prepare($activityQuery);

    $activityStmt->execute([

        ':lead_id' => $leadId
    ]);

    $activities =
    $activityStmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| FETCH RELATED PROJECTS
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

        WHERE lead_id = :lead_id

        ORDER BY id DESC
    ";

    $projectStmt =
    $conn->prepare($projectQuery);

    $projectStmt->execute([

        ':lead_id' => $leadId
    ]);

    $projects =
    $projectStmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
$lead['name']
.
' | Lead Details';

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

                        Lead Details

                    </h1>

                    <p>

                        CRM lead profile and interaction history.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="edit.php?id=<?php echo $lead['id']; ?>"
                        class="btn-admin"
                    >

                        <i class="bi bi-pencil-square"></i>

                        Edit Lead

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
            <!-- LEAD PROFILE -->
            <!-- ================================= -->

            <div class="row g-4">

                <!-- PROFILE CARD -->

                <div class="col-lg-4">

                    <div class="profile-card">

                        <div
                            class="admin-avatar mx-auto mb-4"
                            style="
                                width:100px;
                                height:100px;
                                font-size:32px;
                            "
                        >

                            <?php

                            echo strtoupper(

                                substr(

                                    $lead['name'],

                                    0,

                                    1
                                )
                            );

                            ?>

                        </div>

                        <h3>

                            <?php

                            echo escape(
                                $lead['name']
                            );

                            ?>

                        </h3>

                        <p class="text-muted">

                            CRM Lead

                        </p>

                        <!-- STATUS -->

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

                            echo ucfirst(

                                str_replace(
                                    '_',
                                    ' ',
                                    $status
                                )
                            );

                            ?>

                        </span>

                    </div>

                </div>

                <!-- DETAILS -->

                <div class="col-lg-8">

                    <div class="section-card">

                        <div class="section-header">

                            <h4>

                                Lead Information

                            </h4>

                        </div>

                        <div class="row">

                            <!-- NAME -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Full Name

                                </label>

                                <h6>

                                    <?php

                                    echo escape(
                                        $lead['name']
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- EMAIL -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Email Address

                                </label>

                                <h6>

                                    <?php

                                    echo escape(

                                        $lead['email']
                                        ??
                                        'N/A'
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- PHONE -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Phone Number

                                </label>

                                <h6>

                                    <?php

                                    echo escape(

                                        $lead['phone']
                                        ??
                                        'N/A'
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- TYPE -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Lead Type

                                </label>

                                <h6>

                                    <?php

                                    echo ucfirst(

                                        escape(

                                            $lead['lead_type']
                                            ??
                                            'General'
                                        )
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- SOURCE -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Lead Source

                                </label>

                                <h6>

                                    <?php

                                    echo ucfirst(

                                        escape(

                                            $lead['lead_source']
                                            ??
                                            'Website'
                                        )
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- BUDGET -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Estimated Budget

                                </label>

                                <h6>

                                    ₹<?php

                                    echo number_format(

                                        $lead['budget']
                                        ??
                                        0
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- ASSIGNED -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Assigned To

                                </label>

                                <h6>

                                    <?php

                                    echo escape(

                                        $lead['assigned_to']
                                        ??
                                        'Unassigned'
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- CREATED -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Created Date

                                </label>

                                <h6>

                                    <?php

                                    echo date(

                                        'd M Y h:i A',

                                        strtotime(

                                            $lead['created_at']
                                        )
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- MESSAGE -->

                            <div class="col-lg-12 mb-4">

                                <label class="text-muted">

                                    Notes / Message

                                </label>

                                <div class="lead-message-box">

                                    <?php

                                    echo nl2br(

                                        escape(

                                            $lead['message']
                                            ??
                                            'No notes available.'
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
            <!-- LEAD ACTIVITIES -->
            <!-- ================================= -->

            <div class="section-card mt-4">

                <div class="section-header">

                    <h4>

                        Lead Activities

                    </h4>

                </div>

                <?php if(!empty($activities)): ?>

                    <div class="activity-list">

                        <?php foreach($activities as $activity): ?>

                            <div class="activity-item">

                                <div class="activity-icon">

                                    <i class="bi bi-clock-history"></i>

                                </div>

                                <div class="activity-content">

                                    <h6>

                                        <?php

                                        echo escape(

                                            $activity['activity_type']
                                        );

                                        ?>

                                    </h6>

                                    <p>

                                        <?php

                                        echo escape(

                                            $activity['description']
                                        );

                                        ?>

                                    </p>

                                    <small class="text-muted">

                                        <?php

                                        echo date(

                                            'd M Y h:i A',

                                            strtotime(

                                                $activity['created_at']
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

                        No activities found.

                    </div>

                <?php endif; ?>

            </div>

            <!-- ================================= -->
            <!-- RELATED PROJECTS -->
            <!-- ================================= -->

            <div class="section-card mt-4">

                <div class="section-header">

                    <h4>

                        Related Projects

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

                        No projects linked to this lead.

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