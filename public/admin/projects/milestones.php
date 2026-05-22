<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| PROJECT MILESTONES
|--------------------------------------------------------------------------
| File:
| /public/admin/projects/milestones.php
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

$projectQuery = "

    SELECT
        id,
        project_name,
        project_type,
        status,
        progress

    FROM projects

    WHERE id = :id

    LIMIT 1
";

$projectStmt =
$conn->prepare($projectQuery);

$projectStmt->execute([

    ':id' => $projectId
]);

$project =
$projectStmt->fetch();

if (!$project) {

    $_SESSION['error'] =
    'Project not found.';

    redirect('admin/projects/index.php');
}

/*
|--------------------------------------------------------------------------
| HANDLE CREATE MILESTONE
|--------------------------------------------------------------------------
*/

if (

    $_SERVER['REQUEST_METHOD'] === 'POST'

    &&

    isset($_POST['create_milestone'])
) {

    validateCsrf();

    if (

        !checkRateLimit(

            'create_milestone',

            10,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests.';

        redirect(

            'admin/projects/milestones.php?id='
            .
            $projectId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $title =
    sanitize($_POST['title'] ?? '');

    $description =
    sanitize($_POST['description'] ?? '');

    $status =
    sanitize($_POST['status'] ?? 'pending');

    $completion =
    (int) ($_POST['completion'] ?? 0);

    $dueDate =
    $_POST['due_date'] ?? null;

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (empty($title)) {

        $_SESSION['error'] =
        'Milestone title is required.';

        redirect(

            'admin/projects/milestones.php?id='
            .
            $projectId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT MILESTONE
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            INSERT INTO project_milestones (

                project_id,
                title,
                description,
                status,
                completion,
                due_date,
                created_at

            ) VALUES (

                :project_id,
                :title,
                :description,
                :status,
                :completion,
                :due_date,
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':project_id' =>
            $projectId,

            ':title' =>
            $title,

            ':description' =>
            $description,

            ':status' =>
            $status,

            ':completion' =>
            $completion,

            ':due_date' =>
            $dueDate
        ]);

        /*
        |--------------------------------------------------------------------------
        | LOG
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(

            currentUserId(),

            'project_milestone_created',

            'info',

            'Milestone added to project'
        );

        $_SESSION['success'] =
        'Milestone created successfully.';

        redirect(

            'admin/projects/milestones.php?id='
            .
            $projectId
        );

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to create milestone.';
    }
}

/*
|--------------------------------------------------------------------------
| DELETE MILESTONE
|--------------------------------------------------------------------------
*/

if (

    isset($_GET['delete'])

    &&

    is_numeric($_GET['delete'])
) {

    $milestoneId =
    (int) $_GET['delete'];

    try {

        $deleteQuery = "

            DELETE FROM project_milestones

            WHERE id = :id
        ";

        $deleteStmt =
        $conn->prepare($deleteQuery);

        $deleteStmt->execute([

            ':id' => $milestoneId
        ]);

        $_SESSION['success'] =
        'Milestone deleted successfully.';

        redirect(

            'admin/projects/milestones.php?id='
            .
            $projectId
        );

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to delete milestone.';
    }
}

/*
|--------------------------------------------------------------------------
| FETCH MILESTONES
|--------------------------------------------------------------------------
*/

$milestones = [];

try {

    $milestoneQuery = "

        SELECT *

        FROM project_milestones

        WHERE project_id = :project_id

        ORDER BY id DESC
    ";

    $milestoneStmt =
    $conn->prepare($milestoneQuery);

    $milestoneStmt->execute([

        ':project_id' => $projectId
    ]);

    $milestones =
    $milestoneStmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
$project['project_name']
.
' Milestones';

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

    <style>

        .milestone-card{

            background:#fff;

            border-radius:18px;

            padding:24px;

            box-shadow:
            0 4px 20px rgba(0,0,0,0.06);

            margin-bottom:24px;

            border-left:5px solid #f59e0b;
        }

        .milestone-progress{

            height:10px;

            border-radius:30px;

            overflow:hidden;

            background:#e5e7eb;
        }

        .milestone-progress-bar{

            height:100%;

            background:#f59e0b;
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

            <!-- HEADER -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Project Milestones

                    </h1>

                    <p>

                        Track project phases, progress and deadlines.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="view.php?id=<?php echo $projectId; ?>"
                        class="btn btn-dark"
                    >

                        Back

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

            <!-- PROJECT INFO -->

            <div class="section-card mb-4">

                <h3>

                    <?php

                    echo escape(
                        $project['project_name']
                    );

                    ?>

                </h3>

                <div class="row mt-3">

                    <div class="col-md-4">

                        <strong>Type:</strong>

                        <?php

                        echo escape(
                            $project['project_type']
                        );

                        ?>

                    </div>

                    <div class="col-md-4">

                        <strong>Status:</strong>

                        <?php

                        echo ucfirst(

                            escape(
                                $project['status']
                            )
                        );

                        ?>

                    </div>

                    <div class="col-md-4">

                        <strong>Overall Progress:</strong>

                        <?php

                        echo (int)

                        ($project['progress']
                        ??
                        0);

                        ?>%

                    </div>

                </div>

            </div>

            <!-- CREATE FORM -->

            <div class="section-card mb-4">

                <div class="section-header">

                    <h4>

                        Create Milestone

                    </h4>

                </div>

                <form method="POST">

                    <?php echo csrfField(); ?>

                    <input
                        type="hidden"
                        name="create_milestone"
                        value="1"
                    >

                    <div class="row">

                        <!-- TITLE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Milestone Title

                            </label>

                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                required
                            >

                        </div>

                        <!-- STATUS -->

                        <div class="col-lg-3 mb-4">

                            <label class="form-label">

                                Status

                            </label>

                            <select
                                name="status"
                                class="form-select"
                            >

                                <option value="pending">

                                    Pending

                                </option>

                                <option value="ongoing">

                                    Ongoing

                                </option>

                                <option value="completed">

                                    Completed

                                </option>

                            </select>

                        </div>

                        <!-- COMPLETION -->

                        <div class="col-lg-3 mb-4">

                            <label class="form-label">

                                Completion %

                            </label>

                            <input
                                type="number"
                                name="completion"
                                class="form-control"
                                min="0"
                                max="100"
                                value="0"
                            >

                        </div>

                        <!-- DUE DATE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Due Date

                            </label>

                            <input
                                type="date"
                                name="due_date"
                                class="form-control"
                            >

                        </div>

                        <!-- DESCRIPTION -->

                        <div class="col-lg-12 mb-4">

                            <label class="form-label">

                                Description

                            </label>

                            <textarea
                                name="description"
                                rows="4"
                                class="form-control"
                            ></textarea>

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="btn-admin"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Create Milestone

                    </button>

                </form>

            </div>

            <!-- MILESTONES -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Milestones List

                    </h4>

                </div>

                <?php if(!empty($milestones)): ?>

                    <?php foreach($milestones as $milestone): ?>

                        <div class="milestone-card">

                            <div class="d-flex justify-content-between align-items-start mb-3">

                                <div>

                                    <h4>

                                        <?php

                                        echo escape(
                                            $milestone['title']
                                        );

                                        ?>

                                    </h4>

                                    <small class="text-muted">

                                        Due:

                                        <?php

                                        echo !empty(

                                            $milestone['due_date']
                                        )

                                        ?

                                        date(

                                            'd M Y',

                                            strtotime(

                                                $milestone['due_date']
                                            )
                                        )

                                        :

                                        'N/A';

                                        ?>

                                    </small>

                                </div>

                                <div class="d-flex gap-2">

                                    <span class="badge bg-primary">

                                        <?php

                                        echo ucfirst(

                                            escape(
                                                $milestone['status']
                                            )
                                        );

                                        ?>

                                    </span>

                                    <a
                                        href="?id=<?php

                                        echo $projectId;

                                        ?>&delete=<?php

                                        echo $milestone['id'];

                                        ?>"
                                        class="btn btn-sm btn-danger btn-delete"
                                    >

                                        <i class="bi bi-trash"></i>

                                    </a>

                                </div>

                            </div>

                            <!-- DESCRIPTION -->

                            <p>

                                <?php

                                echo nl2br(

                                    escape(

                                        $milestone['description']
                                        ??
                                        ''
                                    )
                                );

                                ?>

                            </p>

                            <!-- PROGRESS -->

                            <div class="mt-3">

                                <div class="d-flex justify-content-between mb-2">

                                    <small>

                                        Progress

                                    </small>

                                    <small>

                                        <?php

                                        echo (int)

                                        ($milestone['completion']
                                        ??
                                        0);

                                        ?>%

                                    </small>

                                </div>

                                <div class="milestone-progress">

                                    <div
                                        class="milestone-progress-bar"

                                        style="
                                            width:
                                            <?php

                                            echo (int)

                                            ($milestone['completion']
                                            ??
                                            0);

                                            ?>%;
                                        "
                                    ></div>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="text-center py-5">

                        <i
                            class="bi bi-flag"
                            style="
                                font-size:60px;
                                color:#d1d5db;
                            "
                        ></i>

                        <p class="mt-3">

                            No milestones created.

                        </p>

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