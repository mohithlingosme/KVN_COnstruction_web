<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CLIENT PROJECTS
|--------------------------------------------------------------------------
| File:
| /public/admin/clients/projects.php
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
| FETCH PROJECTS
|--------------------------------------------------------------------------
*/

$projects = [];

try {

    $query = "

        SELECT
            id,
            project_name,
            project_type,
            location,
            budget,
            status,
            start_date,
            end_date,
            progress,
            created_at

        FROM projects

        WHERE client_id = :client_id

        ORDER BY id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':client_id' => $clientId
    ]);

    $projects =
    $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to fetch projects.';
}

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
$client['full_name']
.
' Projects | '
.
APP_NAME;

/*
|--------------------------------------------------------------------------
| PROJECT COUNTS
|--------------------------------------------------------------------------
*/

$totalProjects =
count($projects);

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
            <!-- PAGE HEADER -->
            <!-- ================================= -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Client Projects

                    </h1>

                    <p>

                        Manage construction projects for

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
                        href="../projects/create.php?client_id=<?php echo $clientId; ?>"
                        class="btn-admin"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Add Project

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

                <div class="col-lg-4">

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

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-success"
                        >

                            <i class="bi bi-check-circle"></i>

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

            </div>

            <!-- ================================= -->
            <!-- PROJECT TABLE -->
            <!-- ================================= -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Project List

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

                                <th>Type</th>

                                <th>Location</th>

                                <th>Budget</th>

                                <th>Status</th>

                                <th>Progress</th>

                                <th>Timeline</th>

                                <th>Actions</th>

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

                                        <!-- NAME -->

                                        <td>

                                            <strong>

                                                <?php

                                                echo escape(
                                                    $project['project_name']
                                                );

                                                ?>

                                            </strong>

                                        </td>

                                        <!-- TYPE -->

                                        <td>

                                            <?php

                                            echo escape(
                                                $project['project_type']
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

                                                }else{

                                                    echo 'bg-secondary';
                                                }

                                                ?>
                                            ">

                                                <?php

                                                echo ucfirst(
                                                    $status
                                                );

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
                                                    href="../projects/view.php?id=<?php echo $project['id']; ?>"
                                                    class="btn btn-sm btn-dark"
                                                >

                                                    <i class="bi bi-eye"></i>

                                                </a>

                                                <!-- EDIT -->

                                                <a
                                                    href="../projects/edit.php?id=<?php echo $project['id']; ?>"
                                                    class="btn btn-sm btn-primary"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>

                                                <!-- MILESTONES -->

                                                <a
                                                    href="../projects/milestones.php?id=<?php echo $project['id']; ?>"
                                                    class="btn btn-sm btn-success"
                                                >

                                                    <i class="bi bi-flag"></i>

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