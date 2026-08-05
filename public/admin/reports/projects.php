<?php

declare(strict_types=1);

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../includes/repositories.php';

/*
|--------------------------------------------------------------------------
| REPOSITORY
|--------------------------------------------------------------------------
*/

$reportRepo = repo('Report');

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
'Project Reports | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| ADD PROJECT
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['add_project'])
) {

    $projectName =
        trim($_POST['project_name'] ?? '');

    $clientName =
        trim($_POST['client_name'] ?? '');

    $projectType =
        trim($_POST['project_type'] ?? '');

    $location =
        trim($_POST['location'] ?? '');

    $budget =
        trim($_POST['budget'] ?? '');

    $startDate =
        trim($_POST['start_date'] ?? '');

    $expectedCompletion =
        trim($_POST['expected_completion'] ?? '');

    $progress =
        trim($_POST['progress'] ?? '');

    $status =
        trim($_POST['status'] ?? '');

    $siteEngineer =
        trim($_POST['site_engineer'] ?? '');

    if (
        $projectName === '' ||
        $clientName === '' ||
        $projectType === '' ||
        $location === '' ||
        $budget === '' ||
        $startDate === '' ||
        $expectedCompletion === '' ||
        $progress === '' ||
        $status === '' ||
        $siteEngineer === ''
    ) {

        $error =
            'Please fill all required fields.';
    }

    if ($error === '') {

        try {
            $reportRepo
                ? $reportRepo->insertProjectReport([
                    'project_name'            => $projectName,
                    'client_name'             => $clientName,
                    'project_type'            => $projectType,
                    'location'                => $location,
                    'budget'                  => $budget,
                    'start_date'              => $startDate,
                    'expected_completion'     => $expectedCompletion,
                    'progress'                => (int)$progress,
                    'status'                  => $status,
                    'site_engineer'           => $siteEngineer,
                ])
                : false;

            $success =
                'Project added successfully.';
        } catch (Exception $e) {
            $error =
                'Failed to add project.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| DELETE PROJECT
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id =
        (int) $_GET['delete'];

    try {
        $reportRepo
            ? $reportRepo->deleteProjectReport($id)
            : false;

        header(
            'Location: projects.php'
        );

        exit();
    } catch (Exception $e) {
        $error = 'Failed to delete project.';
    }
}

/*
|--------------------------------------------------------------------------
| FETCH PROJECTS
|--------------------------------------------------------------------------
*/

$projects = [];

try {
    if ($reportRepo) {
        $projects = $reportRepo->getProjectReports();
    }
} catch (Exception $e) {
    $error = 'Failed to load projects.';
}

/*
|--------------------------------------------------------------------------
| PROJECT COUNTS
|--------------------------------------------------------------------------
*/

$totalProjects     = 0;
$planningProjects  = 0;
$progressProjects  = 0;
$completedProjects = 0;
$holdProjects      = 0;

foreach ($projects as $calc) {

    $totalProjects++;

    if ($calc['status'] === 'Planning') {

        $planningProjects++;
    }

    if ($calc['status'] === 'In Progress') {

        $progressProjects++;
    }

    if ($calc['status'] === 'Completed') {

        $completedProjects++;
    }

    if ($calc['status'] === 'On Hold') {

        $holdProjects++;
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
        Project Reports
    </title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            font-family:Arial,sans-serif;

            background:#f4f6f9;

            padding:40px;
        }

        .container{

            max-width:1600px;

            margin:auto;
        }

        h1{

            margin-bottom:30px;

            color:#222;
        }

        .stats{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(220px,1fr));

            gap:20px;

            margin-bottom:35px;
        }

        .card{

            background:#fff;

            padding:25px;

            border-radius:18px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .card h3{

            color:#666;

            margin-bottom:10px;

            font-size:15px;
        }

        .card h2{

            color:#111;
        }

        .form-box{

            background:#fff;

            padding:30px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);

            margin-bottom:35px;
        }

        .grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(250px,1fr));

            gap:20px;
        }

        .form-group{

            margin-bottom:20px;
        }

        label{

            display:block;

            margin-bottom:8px;

            font-weight:bold;
        }

        input,
        select{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:15px;
        }

        button{

            background:#f5b400;

            color:#fff;

            border:none;

            padding:14px 20px;

            border-radius:10px;

            font-size:15px;

            font-weight:bold;

            cursor:pointer;
        }

        button:hover{

            opacity:0.9;
        }

        .alert{

            padding:15px;

            border-radius:10px;

            margin-bottom:20px;

            font-weight:bold;
        }

        .success{

            background:#d4edda;

            color:#155724;
        }

        .error{

            background:#f8d7da;

            color:#721c24;
        }

        .table-box{

            background:#fff;

            padding:30px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        table{

            width:100%;

            border-collapse:collapse;
        }

        thead{

            background:#f5b400;

            color:#fff;
        }

        th,
        td{

            padding:15px;

            border-bottom:1px solid #eee;

            text-align:left;

            vertical-align:top;
        }

        tr:hover{

            background:#fafafa;
        }

        .badge{

            padding:8px 14px;

            border-radius:30px;

            font-size:12px;

            font-weight:bold;

            display:inline-block;
        }

        .Planning{

            background:#d1ecf1;

            color:#0c5460;
        }

        .In.Progress{

            background:#fff3cd;

            color:#856404;
        }

        .Completed{

            background:#d4edda;

            color:#155724;
        }

        .On.Hold{

            background:#f8d7da;

            color:#721c24;
        }

        .progress-bar{

            width:100%;

            background:#eee;

            border-radius:30px;

            overflow:hidden;

            height:18px;
        }

        .progress-fill{

            height:100%;

            background:#28a745;

            text-align:center;

            color:#fff;

            font-size:11px;

            line-height:18px;
        }

        .delete-btn{

            display:inline-block;

            background:#dc3545;

            color:#fff;

            padding:8px 12px;

            border-radius:8px;

            text-decoration:none;

            font-size:13px;

            font-weight:bold;
        }

        .delete-btn:hover{

            background:#b02a37;
        }

        .back{

            display:inline-block;

            margin-top:25px;

            text-decoration:none;

            font-weight:bold;

            color:#333;
        }

        .empty{

            text-align:center;

            padding:40px;

            color:#777;
        }

        @media(max-width:992px){

            table{

                display:block;

                overflow-x:auto;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <h1>
        Project Reports
    </h1>

    <!-- STATS -->

    <div class="stats">

        <div class="card">

            <h3>Total Projects</h3>

            <h2>
                <?= (int)$totalProjects ?>
            </h2>

        </div>

        <div class="card">

            <h3>Planning</h3>

            <h2>
                <?= (int)$planningProjects ?>
            </h2>

        </div>

        <div class="card">

            <h3>In Progress</h3>

            <h2>
                <?= (int)$progressProjects ?>
            </h2>

        </div>

        <div class="card">

            <h3>Completed</h3>

            <h2>
                <?= (int)$completedProjects ?>
            </h2>

        </div>

        <div class="card">

            <h3>On Hold</h3>

            <h2>
                <?= (int)$holdProjects ?>
            </h2>

        </div>

    </div>

    <!-- ALERTS -->

    <?php if ($success !== ''): ?>

        <div class="alert success">

            <?php echo htmlspecialchars($success); ?>

        </div>

    <?php endif; ?>

    <?php if ($error !== ''): ?>

        <div class="alert error">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>

    <!-- FORM -->

    <div class="form-box">

        <form method="POST">

            <div class="grid">

                <div class="form-group">

                    <label>
                        Project Name
                    </label>

                    <input
                        type="text"
                        name="project_name"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Client Name
                    </label>

                    <input
                        type="text"
                        name="client_name"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Project Type
                    </label>

                    <input
                        type="text"
                        name="project_type"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Location
                    </label>

                    <input
                        type="text"
                        name="location"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Budget
                    </label>

                    <input
                        type="number"
                        name="budget"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Start Date
                    </label>

                    <input
                        type="date"
                        name="start_date"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Expected Completion
                    </label>

                    <input
                        type="date"
                        name="expected_completion"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Progress (%)
                    </label>

                    <input
                        type="number"
                        name="progress"
                        min="0"
                        max="100"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Status
                    </label>

                    <select
                        name="status"
                        required
                    >

                        <option value="Planning">
                            Planning
                        </option>

                        <option value="In Progress">
                            In Progress
                        </option>

                        <option value="Completed">
                            Completed
                        </option>

                        <option value="On Hold">
                            On Hold
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label>
                        Site Engineer
                    </label>

                    <input
                        type="text"
                        name="site_engineer"
                        required
                    >

                </div>

            </div>

            <button
                type="submit"
                name="add_project"
            >
                Add Project
            </button>

        </form>

    </div>

    <!-- TABLE -->

    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Project</th>

                    <th>Client</th>

                    <th>Type</th>

                    <th>Location</th>

                    <th>Budget</th>

                    <th>Progress</th>

                    <th>Status</th>

                    <th>Engineer</th>

                    <th>Completion</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php if (!empty($projects)): ?>

                <?php foreach ($projects as $row): ?>

                    <tr>

                        <td>
                            <?php echo (int)$row['id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['project_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['client_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['project_type']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['location']); ?>
                        </td>

                        <td>
                            ₹<?php echo number_format((float)$row['budget'], 2); ?>
                        </td>

                        <td>

                            <div class="progress-bar">

                                <div
                                    class="progress-fill"
                                    style="width: <?php echo (int)$row['progress']; ?>%;"
                                >

                                    <?php echo (int)$row['progress']; ?>%

                                </div>

                            </div>

                        </td>

                        <td>

                            <span
                                class="badge <?php echo str_replace(' ', '.', htmlspecialchars((string)$row['status'])); ?>"
                            >

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['status']
                                    );
                                ?>

                            </span>

                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['site_engineer']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['expected_completion']); ?>
                        </td>

                        <td>

                            <a
                                href="?delete=<?php echo (int)$row['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Delete this project?')"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="11"
                        class="empty"
                    >

                        No projects found.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <a
        href="<?php echo base_url('admin/dashboard.php'); ?>"
        class="back"
    >
        ← Back to Dashboard
    </a>

</div>

</body>

</html>