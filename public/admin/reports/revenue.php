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
'Revenue Reports | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| ADD REVENUE ENTRY
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['add_revenue'])
) {

    $projectName =
        trim($_POST['project_name'] ?? '');

    $clientName =
        trim($_POST['client_name'] ?? '');

    $projectType =
        trim($_POST['project_type'] ?? '');

    $amount =
        trim($_POST['amount'] ?? '');

    $paymentStatus =
        trim($_POST['payment_status'] ?? '');

    $paymentDate =
        trim($_POST['payment_date'] ?? '');

    if (
        $projectName === '' ||
        $clientName === '' ||
        $projectType === '' ||
        $amount === '' ||
        $paymentStatus === ''
    ) {

        $error =
            'Please fill all required fields.';
    }

    if ($error === '') {

        try {
            $reportRepo
                ? $reportRepo->insertRevenueReport([
                    'project_name'   => $projectName,
                    'client_name'    => $clientName,
                    'project_type'   => $projectType,
                    'amount'         => $amount,
                    'payment_status' => $paymentStatus,
                    'payment_date'   => $paymentDate !== '' ? $paymentDate : null,
                ])
                : false;

            $success =
                'Revenue record added successfully.';
        } catch (Exception $e) {
            $error =
                'Failed to add revenue record.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| DELETE RECORD
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id =
        (int) $_GET['delete'];

    try {
        $reportRepo
            ? $reportRepo->deleteRevenueReport($id)
            : false;

        header(
            'Location: revenue.php'
        );

        exit();
    } catch (Exception $e) {
        $error =
            'Failed to delete record.';
    }
}

/*
|--------------------------------------------------------------------------
| FETCH REVENUE DATA
|--------------------------------------------------------------------------
*/

$revenues = [];

try {
    if ($reportRepo) {
        $revenues = $reportRepo->getRevenueReports();
    }
} catch (Exception $e) {
    $error = 'Failed to load revenue records.';
}

/*
|--------------------------------------------------------------------------
| TOTAL CALCULATIONS
|--------------------------------------------------------------------------
*/

$totalRevenue =
    0;

$totalPaid =
    0;

$totalPending =
    0;

$totalPartial =
    0;

foreach ($revenues as $calc) {

    $amount =
        (float) $calc['amount'];

    $totalRevenue +=
        $amount;

    if ($calc['payment_status'] === 'Paid') {

        $totalPaid +=
            $amount;
    }

    if ($calc['payment_status'] === 'Pending') {

        $totalPending +=
            $amount;
    }

    if ($calc['payment_status'] === 'Partial') {

        $totalPartial +=
            $amount;
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
        Revenue Reports
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

            max-width:1450px;

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

            color:#777;

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

        .Paid{

            background:#d4edda;

            color:#155724;
        }

        .Pending{

            background:#f8d7da;

            color:#721c24;
        }

        .Partial{

            background:#fff3cd;

            color:#856404;
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

        .empty{

            text-align:center;

            padding:40px;

            color:#777;
        }

        .back{

            display:inline-block;

            margin-top:25px;

            text-decoration:none;

            font-weight:bold;

            color:#333;
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
        Revenue Reports
    </h1>

    <!-- STATS -->

    <div class="stats">

        <div class="card">

            <h3>Total Revenue</h3>

            <h2>
                ₹<?php echo number_format($totalRevenue, 2); ?>
            </h2>

        </div>

        <div class="card">

            <h3>Total Paid</h3>

            <h2>
                ₹<?php echo number_format($totalPaid, 2); ?>
            </h2>

        </div>

        <div class="card">

            <h3>Total Pending</h3>

            <h2>
                ₹<?php echo number_format($totalPending, 2); ?>
            </h2>

        </div>

        <div class="card">

            <h3>Total Partial</h3>

            <h2>
                ₹<?php echo number_format($totalPartial, 2); ?>
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
                        Amount
                    </label>

                    <input
                        type="number"
                        name="amount"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Payment Status
                    </label>

                    <select
                        name="payment_status"
                        required
                    >

                        <option value="Paid">
                            Paid
                        </option>

                        <option value="Pending">
                            Pending
                        </option>

                        <option value="Partial">
                            Partial
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label>
                        Payment Date
                    </label>

                    <input
                        type="date"
                        name="payment_date"
                    >

                </div>

            </div>

            <button
                type="submit"
                name="add_revenue"
            >
                Add Revenue Record
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

                    <th>Amount</th>

                    <th>Status</th>

                    <th>Payment Date</th>

                    <th>Created</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php if (!empty($revenues)): ?>

                <?php foreach ($revenues as $row): ?>

                    <tr>

                        <td>

                            <?php echo (int)$row['id']; ?>

                        </td>

                        <td>

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['project_name']
                                );
                            ?>

                        </td>

                        <td>

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['client_name']
                                );
                            ?>

                        </td>

                        <td>

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['project_type']
                                );
                            ?>

                        </td>

                        <td>

                            ₹<?php
                                echo number_format(
                                    (float)$row['amount'],
                                    2
                                );
                            ?>

                        </td>

                        <td>

                            <span
                                class="badge <?php echo htmlspecialchars((string)$row['payment_status']); ?>"
                            >

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['payment_status']
                                    );
                                ?>

                            </span>

                        </td>

                        <td>

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['payment_date']
                                );
                            ?>

                        </td>

                        <td>

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['created_at']
                                );
                            ?>

                        </td>

                        <td>

                            <a
                                href="?delete=<?php echo (int)$row['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Delete this record?')"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="9"
                        class="empty"
                    >

                        No revenue records found.

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

</html></arg_value></tool_call>