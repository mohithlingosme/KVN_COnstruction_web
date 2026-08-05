<?php

declare(strict_types=1);

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../includes/repositories.php';

/*
|
|--------------------------------------------------------------------------
| REPOSITORY
|
|--------------------------------------------------------------------------
*/

$reportRepo = repo('Report');

/*
|
|--------------------------------------------------------------------------
| PAGE TITLE
|
|--------------------------------------------------------------------------
*/

$pageTitle =
'Estimator Reports | ' . APP_NAME;

/*
|
|--------------------------------------------------------------------------
| ADD ESTIMATION
|
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['add_estimation'])
) {

    $customerName =
        trim($_POST['customer_name'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $projectType =
        trim($_POST['project_type'] ?? '');

    $location =
        trim($_POST['location'] ?? '');

    $plotSize =
        trim($_POST['plot_size'] ?? '');

    $floors =
        trim($_POST['floors'] ?? '');

    $estimatedCost =
        trim($_POST['estimated_cost'] ?? '');

    $estimatedDuration =
        trim($_POST['estimated_duration'] ?? '');

    $status =
        trim($_POST['status'] ?? '');

    if (
        $customerName === '' ||
        $phone === '' ||
        $email === '' ||
        $projectType === '' ||
        $location === '' ||
        $plotSize === '' ||
        $floors === '' ||
        $estimatedCost === '' ||
        $estimatedDuration === '' ||
        $status === ''
    ) {

        $error =
            'Please fill all required fields.';
    }

    if ($error === '') {

        try {
            $reportRepo
                ? $reportRepo->insertEstimatorReport([
                    'customer_name'     => $customerName,
                    'phone'             => $phone,
                    'email'             => $email,
                    'project_type'      => $projectType,
                    'location'          => $location,
                    'plot_size'         => $plotSize,
                    'floors'            => (int)$floors,
                    'estimated_cost'    => $estimatedCost,
                    'estimated_duration'=> $estimatedDuration,
                    'status'            => $status,
                ])
                : false;

            $success =
                'Estimation added successfully.';
        } catch (Exception $e) {
            $error =
                'Failed to add estimation.';
        }
    }
}

/*
|
|--------------------------------------------------------------------------
| DELETE ESTIMATION
|
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id =
        (int) $_GET['delete'];

    try {
        $reportRepo
            ? $reportRepo->deleteEstimatorReport($id)
            : false;

        header(
            'Location: estimators.php'
        );

        exit();
    } catch (Exception $e) {
        $error = 'Failed to delete estimation.';
    }
}

/*
|
|--------------------------------------------------------------------------
| FETCH ESTIMATORS
|
|--------------------------------------------------------------------------
*/

$estimators = [];

try {
    if ($reportRepo) {
        $estimators = $reportRepo->getEstimatorReports();
    }
} catch (Exception $e) {
    $error = 'Failed to load estimators.';
}

/*
|
|--------------------------------------------------------------------------
| STATS
|
|--------------------------------------------------------------------------
*/

$totalEstimations  = 0;
$approvedCount     = 0;
$pendingCount      = 0;
$rejectedCount     = 0;
$totalEstimatedAmt = 0;

foreach ($estimators as $calc) {

    $totalEstimations++;

    $totalEstimatedAmt +=
        (float)$calc['estimated_cost'];

    if ($calc['status'] === 'Approved') {

        $approvedCount++;
    }

    if ($calc['status'] === 'Pending') {

        $pendingCount++;
    }

    if ($calc['status'] === 'Rejected') {

        $rejectedCount++;
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
        Estimator Reports
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

        .Approved{

            background:#d4edda;

            color:#155724;
        }

        .Pending{

            background:#fff3cd;

            color:#856404;
        }

        .Rejected{

            background:#f8d7da;

            color:#721c24;
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
        Estimator Reports
    </h1>

    <!-- STATS -->

    <div class="stats">

        <div class="card">

            <h3>Total Estimations</h3>

            <h2>
                <?= (int)$totalEstimations ?>
            </h2>

        </div>

        <div class="card">

            <h3>Approved</h3>

            <h2>
                <?= (int)$approvedCount ?>
            </h2>

        </div>

        <div class="card">

            <h3>Pending</h3>

            <h2>
                <?= (int)$pendingCount ?>
            </h2>

        </div>

        <div class="card">

            <h3>Rejected</h3>

            <h2>
                <?= (int)$rejectedCount ?>
            </h2>

        </div>

        <div class="card">

            <h3>Total Estimated Value</h3>

            <h2>
                ₹<?php
                    echo number_format(
                        $totalEstimatedAmt,
                        2
                    );
                ?>
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
                        Customer Name
                    </label>

                    <input
                        type="text"
                        name="customer_name"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Phone Number
                    </label>

                    <input
                        type="text"
                        name="phone"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
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
                        Plot Size
                    </label>

                    <input
                        type="text"
                        name="plot_size"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Number of Floors
                    </label>

                    <input
                        type="number"
                        name="floors"
                        min="1"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Estimated Cost
                    </label>

                    <input
                        type="number"
                        name="estimated_cost"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Estimated Duration
                    </label>

                    <input
                        type="text"
                        name="estimated_duration"
                        placeholder="Example: 12 Months"
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

                        <option value="Pending">
                            Pending
                        </option>

                        <option value="Approved">
                            Approved
                        </option>

                        <option value="Rejected">
                            Rejected
                        </option>

                    </select>

                </div>

            </div>

            <button
                type="submit"
                name="add_estimation"
            >
                Add Estimation
            </button>

        </form>

    </div>

    <!-- TABLE -->

    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Customer</th>

                    <th>Phone</th>

                    <th>Email</th>

                    <th>Project Type</th>

                    <th>Location</th>

                    <th>Plot Size</th>

                    <th>Floors</th>

                    <th>Estimated Cost</th>

                    <th>Duration</th>

                    <th>Status</th>

                    <th>Created</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php if (!empty($estimators)): ?>

                <?php foreach ($estimators as $row): ?>

                    <tr>

                        <td>
                            <?php echo (int)$row['id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['customer_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['phone']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['email']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['project_type']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['location']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['plot_size']); ?>
                        </td>

                        <td>
                            <?php echo (int)$row['floors']; ?>
                        </td>

                        <td>
                            ₹<?php echo number_format((float)$row['estimated_cost'], 2); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['estimated_duration']); ?>
                        </td>

                        <td>

                            <span
                                class="badge <?php echo htmlspecialchars((string)$row['status']); ?>"
                            >

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['status']
                                    );
                                ?>

                            </span>

                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['created_at']); ?>
                        </td>

                        <td>

                            <a
                                href="?delete=<?php echo (int)$row['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Delete this estimation?')"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="13"
                        class="empty"
                    >

                        No estimations found.

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