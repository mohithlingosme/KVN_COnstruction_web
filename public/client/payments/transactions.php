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
| CREATE TRANSACTIONS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS payment_transactions (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        transaction_code VARCHAR(100) NOT NULL,

        project_name VARCHAR(255) NOT NULL,

        invoice_number VARCHAR(100) NOT NULL,

        payment_method VARCHAR(100) NOT NULL,

        bank_name VARCHAR(255) DEFAULT NULL,

        account_holder VARCHAR(255) DEFAULT NULL,

        transaction_reference VARCHAR(255) DEFAULT NULL,

        amount DECIMAL(12,2) NOT NULL,

        transaction_status ENUM(
            'Success',
            'Pending',
            'Failed',
            'Refunded'
        )
        NOT NULL DEFAULT 'Pending',

        transaction_date DATETIME NOT NULL,

        remarks TEXT DEFAULT NULL,

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
        FROM payment_transactions
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
        INSERT INTO payment_transactions
        (

            client_id,
            transaction_code,
            project_name,
            invoice_number,
            payment_method,
            bank_name,
            account_holder,
            transaction_reference,
            amount,
            transaction_status,
            transaction_date,
            remarks

        )

        VALUES

        (
            $clientId,
            'TXN-10001',
            'Luxury Villa',
            'INV-2026-001',
            'Bank Transfer',
            'HDFC Bank',
            'Mohith Kumar',
            'REF987654321',
            1770000,
            'Success',
            '2026-01-25 11:30:00',
            'Foundation stage payment completed successfully.'
        ),

        (
            $clientId,
            'TXN-10002',
            'Luxury Villa',
            'INV-2026-002',
            'UPI Payment',
            'Google Pay',
            'Mohith Kumar',
            'UPI123456789',
            1500000,
            'Success',
            '2026-04-10 14:20:00',
            'Partial structural work payment.'
        ),

        (
            $clientId,
            'TXN-10003',
            'Farm House',
            'INV-2026-003',
            'Cheque',
            'ICICI Bank',
            'Mohith Kumar',
            'CHQ908070',
            500000,
            'Pending',
            '2026-05-12 10:00:00',
            'Cheque under bank verification.'
        ),

        (
            $clientId,
            'TXN-10004',
            'Commercial Complex',
            'INV-2026-004',
            'NEFT',
            'Axis Bank',
            'Mohith Kumar',
            'NEFT998877',
            2500000,
            'Failed',
            '2026-06-01 16:40:00',
            'Transaction failed due to bank timeout.'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH TRANSACTIONS
|--------------------------------------------------------------------------
*/

$transactions =
    $conn->query(
        "
        SELECT *
        FROM payment_transactions
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| CALCULATE STATS
|--------------------------------------------------------------------------
*/

$totalTransactions = 0;
$totalAmount = 0;
$successCount = 0;
$pendingCount = 0;

if ($transactions && $transactions->num_rows > 0) {

    while ($calc = $transactions->fetch_assoc()) {

        $totalTransactions++;

        $totalAmount +=
            (float)$calc['amount'];

        if (
            $calc['transaction_status']
            === 'Success'
        ) {

            $successCount++;
        }

        if (
            $calc['transaction_status']
            === 'Pending'
        ) {

            $pendingCount++;
        }
    }

    $transactions->data_seek(0);
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
        Payment Transactions
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

        .Success{

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

        .Refunded{

            background:#d1ecf1;

            color:#0c5460;
        }

        .btn{

            display:inline-block;

            text-decoration:none;

            padding:10px 14px;

            border-radius:8px;

            font-size:13px;

            font-weight:bold;
        }

        .view-btn{

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

    <a href="receipts.php">
        Receipts
    </a>

    <a
        href="transactions.php"
        class="active"
    >
        Transactions
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
                Transaction History
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
                Total Transactions
            </h4>

            <h2>

                <?php
                    echo $totalTransactions;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Total Transaction Amount
            </h4>

            <h2>

                ₹<?php
                    echo number_format(
                        $totalAmount,
                        2
                    );
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Successful Transactions
            </h4>

            <h2>

                <?php
                    echo $successCount;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Pending Transactions
            </h4>

            <h2>

                <?php
                    echo $pendingCount;
                ?>

            </h2>

        </div>

    </div>

    <!-- TRANSACTIONS TABLE -->

    <div class="table-wrapper">

        <?php if ($transactions && $transactions->num_rows > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>
                            Transaction ID
                        </th>

                        <th>
                            Project
                        </th>

                        <th>
                            Invoice
                        </th>

                        <th>
                            Payment Method
                        </th>

                        <th>
                            Bank
                        </th>

                        <th>
                            Amount
                        </th>

                        <th>
                            Date
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

                    <?php while ($row = $transactions->fetch_assoc()): ?>

                        <tr>

                            <td>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['transaction_code']
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
                                        (string)$row['invoice_number']
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
                                        (string)$row['bank_name']
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

                                <?php
                                    echo date(
                                        'd M Y h:i A',
                                        strtotime(
                                            (string)$row['transaction_date']
                                        )
                                    );
                                ?>

                            </td>

                            <td>

                                <span
                                    class="badge <?php echo htmlspecialchars((string)$row['transaction_status']); ?>"
                                >

                                    <?php
                                        echo htmlspecialchars(
                                            (string)$row['transaction_status']
                                        );
                                    ?>

                                </span>

                            </td>

                            <td>

                                <a
                                    href="#"
                                    class="btn view-btn"
                                >
                                    View
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">

                No transaction history found.

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>