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
| CREATE QUOTATIONS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS client_quotations (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        quotation_number VARCHAR(100) NOT NULL,

        project_name VARCHAR(255) NOT NULL,

        quotation_title VARCHAR(255) NOT NULL,

        quotation_date DATE NOT NULL,

        validity_date DATE NOT NULL,

        estimated_amount DECIMAL(12,2) NOT NULL,

        tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0,

        total_amount DECIMAL(12,2) NOT NULL,

        quotation_status ENUM(
            'Approved',
            'Pending',
            'Rejected',
            'Expired'
        )
        NOT NULL DEFAULT 'Pending',

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
        FROM client_quotations
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
        INSERT INTO client_quotations
        (

            client_id,
            quotation_number,
            project_name,
            quotation_title,
            quotation_date,
            validity_date,
            estimated_amount,
            tax_amount,
            total_amount,
            quotation_status,
            notes

        )

        VALUES

        (
            $clientId,
            'QTN-2026-001',
            'Luxury Villa',
            'Complete Turnkey Construction',
            '2026-01-05',
            '2026-02-05',
            8500000,
            1530000,
            10030000,
            'Approved',
            'Quotation approved and project initiated.'
        ),

        (
            $clientId,
            'QTN-2026-002',
            'Farm House',
            'Interior & Elevation Works',
            '2026-03-10',
            '2026-04-10',
            2500000,
            450000,
            2950000,
            'Pending',
            'Awaiting client approval.'
        ),

        (
            $clientId,
            'QTN-2026-003',
            'Commercial Complex',
            'Structural & Civil Works',
            '2026-04-15',
            '2026-05-15',
            15000000,
            2700000,
            17700000,
            'Rejected',
            'Client requested revised pricing.'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH QUOTATIONS
|--------------------------------------------------------------------------
*/

$quotations =
    $conn->query(
        "
        SELECT *
        FROM client_quotations
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| CALCULATE STATS
|--------------------------------------------------------------------------
*/

$totalQuotations = 0;
$totalValue = 0;
$approvedCount = 0;
$pendingCount = 0;

if ($quotations && $quotations->num_rows > 0) {

    while ($calc = $quotations->fetch_assoc()) {

        $totalQuotations++;

        $totalValue +=
            (float)$calc['total_amount'];

        if (
            $calc['quotation_status']
            === 'Approved'
        ) {

            $approvedCount++;
        }

        if (
            $calc['quotation_status']
            === 'Pending'
        ) {

            $pendingCount++;
        }
    }

    $quotations->data_seek(0);
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
        Client Quotations
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

        .Expired{

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

    <a
        href="index.php"
        class="active"
    >
        Quotations
    </a>

    <a href="../payments/index.php">
        Payments
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
                Project Quotations
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
                Total Quotations
            </h4>

            <h2>

                <?php
                    echo $totalQuotations;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Approved Quotations
            </h4>

            <h2>

                <?php
                    echo $approvedCount;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Pending Quotations
            </h4>

            <h2>

                <?php
                    echo $pendingCount;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Total Quotation Value
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

    <!-- QUOTATIONS TABLE -->

    <div class="table-wrapper">

        <?php if ($quotations && $quotations->num_rows > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>
                            Quotation No
                        </th>

                        <th>
                            Project
                        </th>

                        <th>
                            Title
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Valid Till
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

                    <?php while ($row = $quotations->fetch_assoc()): ?>

                        <tr>

                            <td>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['quotation_number']
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
                                        (string)$row['quotation_title']
                                    );
                                ?>

                            </td>

                            <td>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['quotation_date']
                                    );
                                ?>

                            </td>

                            <td>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['validity_date']
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
                                    class="badge <?php echo htmlspecialchars((string)$row['quotation_status']); ?>"
                                >

                                    <?php
                                        echo htmlspecialchars(
                                            (string)$row['quotation_status']
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

                No quotations available.

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>