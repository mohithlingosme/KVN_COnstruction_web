<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CREATE PROJECT
|--------------------------------------------------------------------------
| File:
| /public/admin/projects/create.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/csrf.php';

require_once '../../../helpers/session.php';

require_once '../../../helpers/rateLimiter.php';

require_once '../../../helpers/upload.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
'Create Project | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FETCH CLIENTS
|--------------------------------------------------------------------------
*/

$clients = [];

try {

    $clientQuery = "

        SELECT
            id,
            full_name,
            phone

        FROM users

        WHERE role = 'client'

        ORDER BY full_name ASC
    ";

    $clientStmt =
    $conn->prepare($clientQuery);

    $clientStmt->execute();

    $clients =
    $clientStmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validateCsrf();

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT
    |--------------------------------------------------------------------------
    */

    if (

        !checkRateLimit(

            'create_project',

            10,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests. Please try again later.';

        redirect('admin/projects/create.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $clientId =
    (int) ($_POST['client_id'] ?? 0);

    $leadId =
    (int) ($_POST['lead_id'] ?? 0);

    $projectName =
    sanitize($_POST['project_name'] ?? '');

    $projectType =
    sanitize($_POST['project_type'] ?? '');

    $location =
    sanitize($_POST['location'] ?? '');

    $description =
    sanitize($_POST['description'] ?? '');

    $budget =
    (float) ($_POST['budget'] ?? 0);

    $status =
    sanitize($_POST['status'] ?? 'pending');

    $progress =
    (int) ($_POST['progress'] ?? 0);

    $startDate =
    $_POST['start_date'] ?? null;

    $endDate =
    $_POST['end_date'] ?? null;

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        empty($projectName)

        ||

        empty($projectType)

        ||

        empty($location)
    ) {

        $_SESSION['error'] =
        'Please fill all required fields.';

        redirect('admin/projects/create.php');
    }

    /*
    |--------------------------------------------------------------------------
    | PROJECT IMAGE
    |--------------------------------------------------------------------------
    */

    $projectImage = null;

    if (

        isset($_FILES['project_image'])

        &&

        $_FILES['project_image']['error'] === 0
    ) {

        $upload =
        uploadFile(

            $_FILES['project_image'],

            ROOT_PATH . '/uploads/projects/',

            [

                'jpg',
                'jpeg',
                'png',
                'webp'
            ]
        );

        if ($upload['success']) {

            $projectImage =
            $upload['filename'];

        } else {

            $_SESSION['error'] =
            $upload['message'];

            redirect('admin/projects/create.php');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT PROJECT
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            INSERT INTO projects (

                client_id,
                lead_id,
                project_name,
                project_type,
                location,
                description,
                budget,
                status,
                progress,
                start_date,
                end_date,
                project_image,
                created_at

            ) VALUES (

                :client_id,
                :lead_id,
                :project_name,
                :project_type,
                :location,
                :description,
                :budget,
                :status,
                :progress,
                :start_date,
                :end_date,
                :project_image,
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':client_id' =>
            $clientId,

            ':lead_id' =>
            $leadId,

            ':project_name' =>
            $projectName,

            ':project_type' =>
            $projectType,

            ':location' =>
            $location,

            ':description' =>
            $description,

            ':budget' =>
            $budget,

            ':status' =>
            $status,

            ':progress' =>
            $progress,

            ':start_date' =>
            $startDate,

            ':end_date' =>
            $endDate,

            ':project_image' =>
            $projectImage
        ]);

        /*
        |--------------------------------------------------------------------------
        | SECURITY LOG
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(

            currentUserId(),

            'project_created',

            'info',

            'Project created: ' . $projectName
        );

        $_SESSION['success'] =
        'Project created successfully.';

        redirect('admin/projects/index.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to create project.';

        redirect('admin/projects/create.php');
    }
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

                        Create Project

                    </h1>

                    <p>

                        Add new construction project.

                    </p>

                </div>

                <div>

                    <a
                        href="index.php"
                        class="btn btn-dark"
                    >

                        Back

                    </a>

                </div>

            </div>

            <!-- ================================= -->
            <!-- ALERT -->
            <!-- ================================= -->

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

            <!-- ================================= -->
            <!-- FORM -->
            <!-- ================================= -->

            <div class="section-card">

                <form
                    method="POST"
                    enctype="multipart/form-data"
                >

                    <?php echo csrfField(); ?>

                    <div class="row">

                        <!-- CLIENT -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Select Client

                            </label>

                            <select
                                name="client_id"
                                class="form-select"
                            >

                                <option value="">

                                    Select Client

                                </option>

                                <?php foreach($clients as $client): ?>

                                    <option
                                        value="<?php echo $client['id']; ?>"
                                    >

                                        <?php

                                        echo escape(

                                            $client['full_name']
                                        );

                                        ?>

                                        -

                                        <?php

                                        echo escape(
                                            $client['phone']
                                        );

                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <!-- PROJECT NAME -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Project Name

                            </label>

                            <input
                                type="text"
                                name="project_name"
                                class="form-control"
                                required
                            >

                        </div>

                        <!-- PROJECT TYPE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Project Type

                            </label>

                            <select
                                name="project_type"
                                class="form-select"
                                required
                            >

                                <option value="">

                                    Select Type

                                </option>

                                <option value="Residential">

                                    Residential

                                </option>

                                <option value="Commercial">

                                    Commercial

                                </option>

                                <option value="Interior">

                                    Interior

                                </option>

                                <option value="Renovation">

                                    Renovation

                                </option>

                                <option value="Villa">

                                    Villa

                                </option>

                            </select>

                        </div>

                        <!-- LOCATION -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Project Location

                            </label>

                            <input
                                type="text"
                                name="location"
                                class="form-control"
                                required
                            >

                        </div>

                        <!-- BUDGET -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Budget

                            </label>

                            <input
                                type="number"
                                name="budget"
                                class="form-control"
                                min="0"
                            >

                        </div>

                        <!-- STATUS -->

                        <div class="col-lg-6 mb-4">

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

                                <option value="cancelled">

                                    Cancelled

                                </option>

                            </select>

                        </div>

                        <!-- PROGRESS -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Progress %

                            </label>

                            <input
                                type="number"
                                name="progress"
                                class="form-control"
                                min="0"
                                max="100"
                                value="0"
                            >

                        </div>

                        <!-- START DATE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Start Date

                            </label>

                            <input
                                type="date"
                                name="start_date"
                                class="form-control"
                            >

                        </div>

                        <!-- END DATE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                End Date

                            </label>

                            <input
                                type="date"
                                name="end_date"
                                class="form-control"
                            >

                        </div>

                        <!-- IMAGE -->

                        <div class="col-lg-12 mb-4">

                            <label class="form-label">

                                Project Image

                            </label>

                            <input
                                type="file"
                                name="project_image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                        </div>

                        <!-- DESCRIPTION -->

                        <div class="col-lg-12 mb-4">

                            <label class="form-label">

                                Description

                            </label>

                            <textarea
                                name="description"
                                rows="6"
                                class="form-control"
                            ></textarea>

                        </div>

                    </div>

                    <!-- BUTTONS -->

                    <div class="d-flex gap-3">

                        <button
                            type="submit"
                            class="btn-admin"
                        >

                            <i class="bi bi-check-circle"></i>

                            Create Project

                        </button>

                        <a
                            href="index.php"
                            class="btn btn-dark"
                        >

                            Cancel

                        </a>

                    </div>

                </form>

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