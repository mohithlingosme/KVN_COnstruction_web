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
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once '../../includes/db.php';

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
| CREATE RECEIPTS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS payment_receipts (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        receipt_number VARCHAR(100) NOT NULL,

        invoice_number VARCHAR(100) NOT NULL,

        project_name VARCHAR(255) NOT NULL,

        payment_method VARCHAR(100) NOT NULL,

        transaction_id VARCHAR(255) DEFAULT NULL,

        paid_amount DECIMAL(12,2) NOT NULL,

        payment_date DATE NOT NULL,

        receipt_status ENUM(
            'Verified',
            'Pending',
            'Failed'
        )
        NOT NULL DEFAULT 'Verified',

        notes TEXT DEFAULT NULL,

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| INSERT DEMO DATA
|--------------------------------------------------------------------------
*/

$check =
    $conn->query(
        "
        SELECT id
        FROM payment_receipts
        WHERE client_id = $clientId
        LIMIT 1
        "
    );

if (
    $check &&
    $check->num_rows === 0
) {

    $conn->query(
        "
        INSERT INTO payment_receipts
        (

            client_id,
            receipt_number,
            invoice_number,
            project_name,
            payment_method,
            transaction_id,
            paid_amount,
            payment_date,
            receipt_status,
            notes

        )

        VALUES

        (
            $clientId,
            'RCPT-1001',
            'INV-2026-001',
            'Luxury Villa',
            'Bank Transfer',
            'TXN987654321',
            1770000,
            '2026-01-25',
            'Verified',
            'Foundation payment received successfully.'
        ),

        (
            $clientId,
            'RCPT-1002',
            'INV-2026-002',
            'Luxury Villa',
            'UPI Payment',
            'UPI123456789',
            1500000,
            '2026-04-10',
            'Verified',
            'Partial structural work payment.'
        ),

        (
            $clientId,
            'RCPT-1003',
            'INV-2026-003',
            'Farm House',
            'Cheque',
            'CHQ908070',
            500000,
            '2026-05-12',
            'Pending',
            'Cheque clearance pending.'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH RECEIPTS
|--------------------------------------------------------------------------
*/

$receipts =
    $conn->query(
        "
        SELECT *
        FROM payment_receipts
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| CALCULATE STATS
|--------------------------------------------------------------------------
*/

$totalReceipts = 0;
$totalReceived = 0;
$verifiedCount = 0;
$pendingCount = 0;

if ($receipts && $receipts->num_rows > 0) {

    while ($calc = $receipts->fetch_assoc()) {

        $totalReceipts++;

        $totalReceived +=
            (float)$calc['paid_amount'];

        if (
            $calc['receipt_status']
            === 'Verified'
        ) {

            $verifiedCount++;
        }

        if (
            $calc['receipt_status']
            === 'Pending'
        ) {

            $pendingCount++;
        }
    }

    $receipts->data_seek(0);
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
        Payment Receipts
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

        .Verified{

            background:#d4edda;

            color:#155724;
        }

        .Pending{

            background:#fff3cd;

            color:#856404;
        }

        .Failed{

            background:#f8d7da;

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

    <a href="../dashboard.php">
        Dashboard
    </a>

    <a href="../projects/index.php">
        Projects
    </a>

    <a href="index.php">
        Payments
    </a>

    <a href="invoices.php">
        Invoices
    </a>

    <a
        href="receipts.php"
        class="active"
    >
        Receipts
    </a>

    <a href="../logout.php">
        Logout
    </a>

</div>

<!-- MAIN -->

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <div>

            <h1>
                Payment Receipts
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
            href="../logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

    <!-- STATS -->

    <div class="stats">

        <div class="card">

            <h4>
                Total Receipts
            </h4>

            <h2>

                <?php
                    echo $totalReceipts;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Total Received
            </h4>

            <h2>

                ₹<?php
                    echo number_format(
                        $totalReceived,
                        2
                    );
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Verified Receipts
            </h4>

            <h2>

                <?php
                    echo $verifiedCount;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Pending Verification
            </h4>

            <h2>

                <?php
                    echo $pendingCount;
                ?>

            </h2>

        </div>

    </div>

    <!-- RECEIPTS TABLE -->

    <div class="table-wrapper">

        <?php if ($receipts && $receipts->num_rows > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>
                            Receipt No
                        </th>

                        <th>
                            Invoice No
                        </th>

                        <th>
                            Project
                        </th>

                        <th>
                            Payment Method
                        </th>

                        <th>
                            Transaction ID
                        </th>

                        <th>
                            Paid Amount
                        </th>

                        <th>
                            Payment Date
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

                    <?php while ($row = $receipts->fetch_assoc()): ?>

                        <tr>

                            <td>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['receipt_number']
                                    );
                                ?>

                            </td>

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
                                        (string)$row['payment_method']
                                    );
                                ?>

                            </td>

                            <td>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['transaction_id']
                                    );
                                ?>

                            </td>

                            <td>

                                ₹<?php
                                    echo number_format(
                                        (float)$row['paid_amount'],
                                        2
                                    );
                                ?>

                            </td>

                            <td>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['payment_date']
                                    );
                                ?>

                            </td>

                            <td>

                                <span
                                    class="badge <?php echo htmlspecialchars((string)$row['receipt_status']); ?>"
                                >

                                    <?php
                                        echo htmlspecialchars(
                                            (string)$row['receipt_status']
                                        );
                                    ?>

                                </span>

                            </td>

                            <td>

                                <a
                                    href="#"
                                    class="btn download-btn"
                                >
                                    Download
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">

                No receipts available.

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>