<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| LEADS PIPELINE
|--------------------------------------------------------------------------
| File:
| /public/admin/leads/pipeline.php
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
'Leads Pipeline | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FETCH LEADS BY STATUS
|--------------------------------------------------------------------------
*/

$pipelineStages = [

    'new' =>
    [],

    'pending' =>
    [],

    'follow_up' =>
    [],

    'converted' =>
    [],

    'closed' =>
    []
];

try {

    $query = "

        SELECT
            id,
            name,
            phone,
            email,
            budget,
            lead_type,
            lead_source,
            status,
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

    foreach($leads as $lead){

        $status =
        strtolower(
            $lead['status']
        );

        if(

            isset(
                $pipelineStages[$status]
            )
        ){

            $pipelineStages[$status][] =
            $lead;
        }
    }

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to load pipeline data.';
}

/*
|--------------------------------------------------------------------------
| TOTAL COUNTS
|--------------------------------------------------------------------------
*/

$totalLeads = 0;

foreach($pipelineStages as $stage){

    $totalLeads +=
    count($stage);
}

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

    <!-- SortableJS -->

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <!-- Admin CSS -->

    <link
        rel="stylesheet"
        href="<?php echo base_url('../assets/admin/css/admin.css'); ?>"
    >

    <style>

        .pipeline-wrapper{

            display:grid;

            grid-template-columns:
            repeat(5, 1fr);

            gap:20px;
        }

        .pipeline-column{

            background:#fff;

            border-radius:16px;

            padding:16px;

            min-height:600px;

            box-shadow:
            0 4px 20px rgba(0,0,0,0.06);
        }

        .pipeline-header{

            display:flex;

            align-items:center;

            justify-content:space-between;

            margin-bottom:20px;
        }

        .pipeline-card{

            background:#f9fafb;

            border-radius:14px;

            padding:16px;

            margin-bottom:16px;

            border:1px solid #eee;

            cursor:grab;

            transition:0.3s;
        }

        .pipeline-card:hover{

            transform:translateY(-3px);

            box-shadow:
            0 10px 25px rgba(0,0,0,0.08);
        }

        .pipeline-budget{

            font-size:18px;

            font-weight:700;

            color:#f59e0b;
        }

        .pipeline-badge{

            font-size:12px;

            padding:4px 10px;

            border-radius:20px;

            background:#111827;

            color:#fff;
        }

        .pipeline-empty{

            text-align:center;

            color:#9ca3af;

            padding:60px 20px;
        }

        @media(max-width:1400px){

            .pipeline-wrapper{

                grid-template-columns:
                repeat(2, 1fr);
            }
        }

        @media(max-width:768px){

            .pipeline-wrapper{

                grid-template-columns:
                1fr;
            }
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

            <!-- ================================= -->
            <!-- PAGE HEADER -->
            <!-- ================================= -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Leads Pipeline

                    </h1>

                    <p>

                        Visual CRM sales funnel and lead tracking system.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="create.php"
                        class="btn-admin"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Add Lead

                    </a>

                    <a
                        href="index.php"
                        class="btn btn-dark"
                    >

                        Table View

                    </a>

                </div>

            </div>

            <!-- ================================= -->
            <!-- STATS -->
            <!-- ================================= -->

            <div class="row g-4 mb-4">

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-primary"
                        >

                            <i class="bi bi-funnel-fill"></i>

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

                                echo count(
                                    $pipelineStages['converted']
                                );

                                ?>

                            </h3>

                            <p>

                                Converted Leads

                            </p>

                        </div>

                    </div>

                </div>

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

                                echo count(
                                    $pipelineStages['follow_up']
                                );

                                ?>

                            </h3>

                            <p>

                                Follow Ups

                            </p>

                        </div>

                    </div>

                </div>

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-danger"
                        >

                            <i class="bi bi-x-circle-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo count(
                                    $pipelineStages['closed']
                                );

                                ?>

                            </h3>

                            <p>

                                Closed Leads

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ================================= -->
            <!-- PIPELINE -->
            <!-- ================================= -->

            <div class="pipeline-wrapper">

                <!-- ================================= -->
                <!-- NEW -->
                <!-- ================================= -->

                <?php

                $columns = [

                    'new' =>
                    'New Leads',

                    'pending' =>
                    'Pending',

                    'follow_up' =>
                    'Follow Up',

                    'converted' =>
                    'Converted',

                    'closed' =>
                    'Closed'
                ];

                foreach($columns as $key => $title):

                ?>

                    <div class="pipeline-column">

                        <!-- HEADER -->

                        <div class="pipeline-header">

                            <h5>

                                <?php echo $title; ?>

                            </h5>

                            <span class="pipeline-badge">

                                <?php

                                echo count(
                                    $pipelineStages[$key]
                                );

                                ?>

                            </span>

                        </div>

                        <!-- LEADS -->

                        <div
                            class="pipeline-stage"
                            data-stage="<?php echo $key; ?>"
                        >

                            <?php if(!empty($pipelineStages[$key])): ?>

                                <?php foreach($pipelineStages[$key] as $lead): ?>

                                    <div
                                        class="pipeline-card"
                                        data-id="<?php echo $lead['id']; ?>"
                                    >

                                        <!-- NAME -->

                                        <h6>

                                            <?php

                                            echo escape(
                                                $lead['name']
                                            );

                                            ?>

                                        </h6>

                                        <!-- TYPE -->

                                        <small class="text-muted d-block mb-2">

                                            <?php

                                            echo ucfirst(

                                                escape(

                                                    $lead['lead_type']
                                                    ??
                                                    'General'
                                                )
                                            );

                                            ?>

                                        </small>

                                        <!-- CONTACT -->

                                        <div class="mb-2">

                                            <small>

                                                <i class="bi bi-telephone"></i>

                                                <?php

                                                echo escape(
                                                    $lead['phone']
                                                );

                                                ?>

                                            </small>

                                        </div>

                                        <!-- EMAIL -->

                                        <div class="mb-3">

                                            <small>

                                                <i class="bi bi-envelope"></i>

                                                <?php

                                                echo escape(

                                                    $lead['email']
                                                    ??
                                                    'N/A'
                                                );

                                                ?>

                                            </small>

                                        </div>

                                        <!-- BUDGET -->

                                        <div class="pipeline-budget mb-3">

                                            ₹<?php

                                            echo number_format(

                                                $lead['budget']
                                                ??
                                                0
                                            );

                                            ?>

                                        </div>

                                        <!-- ASSIGNED -->

                                        <div class="mb-3">

                                            <small class="text-muted">

                                                Assigned:

                                                <?php

                                                echo escape(

                                                    $lead['assigned_to']
                                                    ??
                                                    'Unassigned'
                                                );

                                                ?>

                                            </small>

                                        </div>

                                        <!-- ACTIONS -->

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

                                            <!-- CONVERT -->

                                            <?php if($key !== 'converted'): ?>

                                                <a
                                                    href="convert.php?id=<?php echo $lead['id']; ?>"
                                                    class="btn btn-sm btn-success"
                                                >

                                                    <i class="bi bi-check-circle"></i>

                                                </a>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <div class="pipeline-empty">

                                    <i
                                        class="bi bi-inbox"
                                        style="
                                            font-size:40px;
                                        "
                                    ></i>

                                    <p class="mt-3">

                                        No leads

                                    </p>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

    </div>

</div>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin JS -->

<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>

<script>

document.querySelectorAll('.pipeline-stage')

.forEach(stage => {

    new Sortable(stage, {

        group: 'pipeline',

        animation: 200,

        ghostClass: 'sortable-ghost',

        onEnd: function (evt) {

            let leadId =

            evt.item.dataset.id;

            let newStage =

            evt.to.dataset.stage;

            /*
            |--------------------------------------------------------------------------
            | AJAX STATUS UPDATE
            |--------------------------------------------------------------------------
            */

            fetch(

                'update-status.php',

                {

                    method: 'POST',

                    headers: {

                        'Content-Type':
                        'application/json'
                    },

                    body: JSON.stringify({

                        lead_id:
                        leadId,

                        status:
                        newStage
                    })
                }
            )

            .then(response => response.json())

            .then(data => {

                console.log(data);

            })

            .catch(error => {

                console.error(error);
            });
        }
    });
});

</script>

</body>

</html>