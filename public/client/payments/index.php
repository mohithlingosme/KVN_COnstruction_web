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
| FETCH PAYMENT DATA
|--------------------------------------------------------------------------
*/

$data = $clientService->getPaymentData($clientId);
$payments = $data['payments'];
$totalInvoices = $data['totalInvoices'];
$totalPaid = $data['totalPaid'];
$totalPending = $data['totalPending'];
$totalBalance = $data['totalBalance'];

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
        Client Payments
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

            left:0;

            top:0;

            padding:30px 20px;

            overflow:auto;
        }

        .sidebar h2{

            color:#f5b400;

            margin-bottom:35px;
        }

        .sidebar a{

            display:block;

            color:#fff;

            text-decoration:none;

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

            text-align:left;

            border-bottom:1px solid #eee;
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

        .Partial{

            background:#fff3cd;

            color:#856404;
        }

        .Pending{

            background:#f8d7da;

            color:#721c24;
        }

        .Overdue{

            background:#f5c6cb;

            color:#721c24;
        }

        .amount{

            font-weight:bold;
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

    <a
        href="index.php"
        class="active"
    >
        Payments
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
                Payment Dashboard
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
                Total Paid
            </h4>

            <h2>

                ₹<?php
                    echo number_format(
                        $totalPaid,
                        2
                    );
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Pending Invoices
            </h4>

            <h2>

                <?php
                    echo (int)$totalPending;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Outstanding Balance
            </h4>

            <h2>

                ₹<?php
                    echo number_format(
                        $totalBalance,
                        2
                    );
                ?>

            </h2>

        </div>

    </div>

    <!-- PAYMENTS TABLE -->

    <div class="table-wrapper">

        <?php if (!empty($payments)): ?>

            <table>

                <thead>

                    <tr>

                        <th>
                            Invoice
                        </th>

                        <th>
                            Project
                        </th>

                        <th>
                            Payment Type
                        </th>

                        <th>
                            Total
                        </th>

                        <th>
                            Paid
                        </th>

                        <th>
                            Balance
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Due Date
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($payments as $row): ?>

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
                                        (string)$row['payment_type']
                                    );
                                ?>

                            </td>

                            <td class="amount">

                                ₹<?php
                                    echo number_format(
                                        (float)$row['total_amount'],
                                        2
                                    );
                                ?>

                            </td>

                            <td class="amount">

                                ₹<?php
                                    echo number_format(
                                        (float)$row['paid_amount'],
                                        2
                                    );
                                ?>

                            </td>

                            <td class="amount">

                                ₹<?php
                                    echo number_format(
                                        (float)$row['balance_amount'],
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
                                        (string)$row['due_date']
                                    );
                                ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">

                No payment records found.

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>