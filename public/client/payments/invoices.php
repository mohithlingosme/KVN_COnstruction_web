<?php

declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['client_id'])) {

    header('Location: ../login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| CLIENT DETAILS
|--------------------------------------------------------------------------
*/

$clientId =
    (int) $_SESSION['client_id'];

$clientName =
    $_SESSION['client_name'] ?? 'Client';

/*
|--------------------------------------------------------------------------
| SERVICE LAYER
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once __DIR__ . '/../../includes/repositories.php';

$clientService = new \App\Services\ClientService();

/*
|--------------------------------------------------------------------------
| FETCH INVOICE DATA
|--------------------------------------------------------------------------
*/

$data = $clientService->getInvoiceData($clientId);
$invoices = $data['invoices'];
$totalInvoices = $data['totalInvoices'];
$totalValue = $data['totalValue'];
$paidInvoices = $data['paidInvoices'];
$pendingInvoices = $data['pendingInvoices'];

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
        Client Invoices
    </title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            font-family:Arial,sans-serif;

            background:#f3f4f6;

            color:#222;
        }

        .sidebar{

            width:260px;

            height:100vh;

            background:#111827;

            position:fixed;

            top:0;

            left:0;

            padding:30px 20px;

            overflow:auto;
        }

        .sidebar h2{

            color:#f5b400;

            margin-bottom:35px;
        }

        .sidebar a{

            display:block;

            text-decoration:none;

            color:#fff;

            padding:14px 16px;

            border-radius:10px;

            margin-bottom:10px;

            transition:0.3s;
        }

        .sidebar a:hover,
        .sidebar .active{

            background:#f5b400;

            color:#111;
        }

        .main{

            margin-left:260px;

            padding:40px;
        }

        .topbar{

            display:flex;

            justify-content:space-between;

            align-items:center;

            flex-wrap:wrap;

            gap:15px;

            margin-bottom:35px;
        }

        .logout-btn{

            text-decoration:none;

            background:#dc3545;

            color:#fff;

            padding:12px 18px;

            border-radius:10px;

            font-weight:bold;
        }

        .stats{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(240px,1fr));

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

        .card h4{

            color:#666;

            margin-bottom:10px;
        }

        .card h2{

            font-size:30px;
        }

        .table-wrapper{

            background:#fff;

            border-radius:20px;

            overflow:auto;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        table{

            width:100%;

            border-collapse:collapse;
        }

        table thead{

            background:#111827;

            color:#fff;
        }

        table th,
        table td{

            padding:18px;

            border-bottom:1px solid #eee;

            text-align:left;
        }

        table tr:hover{

            background:#fafafa;
        }

        .badge{

            padding:8px 14px;

            border-radius:30px;

            font-size:12px;

            font-weight:bold;
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

        .Overdue{

            background:#f5c6cb;

            color:#721c24;
        }

        .btn{

            display:inline-block;

            text-decoration:none;

            padding:10px 14px;

            border-radius:8px;

            font-size:13px;

            font-weight:bold;
        }

        .download-btn{

            background:#111827;

            color:#fff;
        }

        .empty{

            text-align:center;

            padding:60px;

            color:#777;
        }

        @media(max-width:992px){

            .sidebar{

                width:100%;

                height:auto;

                position:relative;
            }

            .main{

                margin-left:0;
            }
        }

    </style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <h2>
        KVN Client
    </h2>

    <a href="<?php echo base_url('admin/dashboard.php'); ?>">
        Dashboard
    </a>

    <a href="<?php echo base_url('admin/projects/index.php'); ?>">
        Projects
    </a>

    <a href="index.php">
        Payments
    </a>

    <a
        href="invoices.php"
        class="active"
    >
        Invoices
    </a>

    <a href="<?php echo base_url('logout.php'); ?>">
        Logout
    </a>

</div>

<!-- MAIN -->

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <div>

            <h1>
                Invoice Management
            </h1>

            <p>

                Welcome,
                <?php
                    echo htmlspecialchars(
                        (string)$clientName
                    );
                ?>

            </p>

        </div>

        <a
            href="<?php echo base_url('logout.php'); ?>"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

    <!-- STATS -->

    <div class="stats">

        <div class="card">

            <h4>
                Total Invoices
            </h4>

            <h2>

                <?php
                    echo (int)$totalInvoices;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Paid Invoices
            </h4>

            <h2>

                <?php
                    echo (int)$paidInvoices;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Pending Invoices
            </h4>

            <h2>

                <?php
                    echo (int)$pendingInvoices;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Total Invoice Value
            </h4>

            <h2>

                ₹<?php
                    echo number_format(
                        $totalValue,
                        2
                    );
                ?>

            </h2>

        </div>

    </div>

    <!-- TABLE -->

    <div class="table-wrapper">

        <?php if (!empty($invoices)): ?>

            <table>

                <thead>

                    <tr>

                        <th>
                            Invoice No
                        </th>

                        <th>
                            Project
                        </th>

                        <th>
                            Invoice Title
                        </th>

                        <th>
                            Invoice Date
                        </th>

                        <th>
                            Due Date
                        </th>

                        <th>
                            Total Amount
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($invoices as $row): ?>

                        <tr>

                            <td>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['invoice_number']
                                    );
                                ?>

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
                                        (string)$row['invoice_title']
                                    );
                                ?>

                            </td>

                            <td>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['invoice_date']
                                    );
                                ?>

                            </td>

                            <td>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['due_date']
                                    );
                                ?>

                            </td>

                            <td>

                                ₹<?php
                                    echo number_format(
                                        (float)$row['total_amount'],
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

                                <a
                                    href="<?php echo !empty($row['pdf_file']) ? base_url('uploads/invoices/' . htmlspecialchars((string)$row['pdf_file'])) : 'javascript:window.print()'; ?>"
                                    class="btn download-btn"
                                    <?php echo !empty($row['pdf_file']) ? 'download' : ''; ?>
                                >
                                    Download
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">

                No invoices available.

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>