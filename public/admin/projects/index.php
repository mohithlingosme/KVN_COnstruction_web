<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| PROJECTS MANAGEMENT
|--------------------------------------------------------------------------
| File:
| /public/admin/projects/index.php
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
'Projects Management | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FETCH PROJECTS
|--------------------------------------------------------------------------
*/

$projects = [];

try {

    $query = "

        SELECT
            p.*,
            u.full_name AS client_name

        FROM projects p

        LEFT JOIN users u
        ON p.client_id = u.id

        ORDER BY p.id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute();

    $projects =
    $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to fetch projects.';
}

/*
|--------------------------------------------------------------------------
| PROJECT STATS
|--------------------------------------------------------------------------
*/

$totalProjects =
count($projects);

$ongoingProjects =
count(

    array_filter(

        $projects,

        function($project){

            return

            strtolower(
                $project['status']
            )

            ===

            'ongoing';
        }
    )
);

$completedProjects =
count(

    array_filter(

        $projects,

        function($project){

            return

            strtolower(
                $project['status']
            )

            ===

            'completed';
        }
    )
);

$pendingProjects =
count(

    array_filter(

        $projects,

        function($project){

            return

            strtolower(
                $project['status']
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
            <!-- PAGE HEADER -->
            <!-- ================================= -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Projects Management

                    </h1>

                    <p>

                        Manage construction projects, timelines and progress.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="create.php"
                        class="btn-admin"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Add Project

                    </a>

                    <a
                        href="kanban.php"
                        class="btn btn-dark"
                    >

                        Kanban View

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

                            <i class="bi bi-building"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalProjects
                                );

                                ?>

                            </h3>

                            <p>

                                Total Projects

                            </p>

                        </div>

                    </div>

                </div>

                <!-- ONGOING -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-warning"
                        >

                            <i class="bi bi-tools"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $ongoingProjects
                                );

                                ?>

                            </h3>

                            <p>

                                Ongoing Projects

                            </p>

                        </div>

                    </div>

                </div>

                <!-- COMPLETED -->

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
                                    $completedProjects
                                );

                                ?>

                            </h3>

                            <p>

                                Completed Projects

                            </p>

                        </div>

                    </div>

                </div>

                <!-- PENDING -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-danger"
                        >

                            <i class="bi bi-hourglass-split"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $pendingProjects
                                );

                                ?>

                            </h3>

                            <p>

                                Pending Projects

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ================================= -->
            <!-- PROJECTS TABLE -->
            <!-- ================================= -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Project Records

                    </h4>

                </div>

                <!-- SEARCH -->

                <div class="row mb-4">

                    <div class="col-lg-4">

                        <input
                            type="text"
                            class="form-control table-search"
                            data-table="#projectsTable"
                            placeholder="Search projects..."
                        >

                    </div>

                </div>

                <!-- TABLE -->

                <div class="table-responsive">

                    <table
                        class="table admin-table"
                        id="projectsTable"
                    >

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Project</th>

                                <th>Client</th>

                                <th>Location</th>

                                <th>Budget</th>

                                <th>Status</th>

                                <th>Progress</th>

                                <th>Timeline</th>

                                <th width="220">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($projects)): ?>

                                <?php foreach($projects as $project): ?>

                                    <tr>

                                        <!-- ID -->

                                        <td>

                                            #<?php echo $project['id']; ?>

                                        </td>

                                        <!-- PROJECT -->

                                        <td>

                                            <div>

                                                <strong>

                                                    <?php

                                                    echo escape(

                                                        $project['project_name']
                                                    );

                                                    ?>

                                                </strong>

                                                <br>

                                                <small class="text-muted">

                                                    <?php

                                                    echo escape(

                                                        $project['project_type']
                                                        ??
                                                        'General'
                                                    );

                                                    ?>

                                                </small>

                                            </div>

                                        </td>

                                        <!-- CLIENT -->

                                        <td>

                                            <?php

                                            echo escape(

                                                $project['client_name']
                                                ??
                                                'N/A'
                                            );

                                            ?>

                                        </td>

                                        <!-- LOCATION -->

                                        <td>

                                            <?php

                                            echo escape(

                                                $project['location']
                                                ??
                                                'N/A'
                                            );

                                            ?>

                                        </td>

                                        <!-- BUDGET -->

                                        <td>

                                            ₹<?php

                                            echo number_format(

                                                $project['budget']
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

                                        </td>

                                        <!-- PROGRESS -->

                                        <td style="min-width:180px;">

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

                                        </td>

                                        <!-- TIMELINE -->

                                        <td>

                                            <small>

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

                                                '-';

                                                ?>

                                            </small>

                                            <br>

                                            <small>

                                                →

                                            </small>

                                            <br>

                                            <small>

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

                                                '-';

                                                ?>

                                            </small>

                                        </td>

                                        <!-- ACTIONS -->

                                        <td>

                                            <div class="d-flex gap-2 flex-wrap">

                                                <!-- VIEW -->

                                                <a
                                                    href="view.php?id=<?php echo $project['id']; ?>"
                                                    class="btn btn-sm btn-dark"
                                                >

                                                    <i class="bi bi-eye"></i>

                                                </a>

                                                <!-- EDIT -->

                                                <a
                                                    href="edit.php?id=<?php echo $project['id']; ?>"
                                                    class="btn btn-sm btn-primary"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>

                                                <!-- TASKS -->

                                                <a
                                                    href="tasks.php?id=<?php echo $project['id']; ?>"
                                                    class="btn btn-sm btn-success"
                                                >

                                                    <i class="bi bi-list-check"></i>

                                                </a>

                                                <!-- DELETE -->

                                                <a
                                                    href="delete.php?id=<?php echo $project['id']; ?>"
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

                                    <td colspan="9">

                                        <div class="text-center py-5">

                                            <i
                                                class="bi bi-building"
                                                style="
                                                    font-size:50px;
                                                    color:#d1d5db;
                                                "
                                            ></i>

                                            <p class="mt-3">

                                                No projects found.

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