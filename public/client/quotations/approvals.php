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
| CLIENT INFO
|--------------------------------------------------------------------------
*/

$clientId =
    (int) $_SESSION['client_id'];

$clientName =
    $_SESSION['client_name'] ?? 'Client';

/*
|--------------------------------------------------------------------------
| HANDLE APPROVAL ACTIONS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $quotationId =
        (int) ($_POST['quotation_id'] ?? 0);

    $action =
        trim($_POST['action'] ?? '');

    if (
        $quotationId > 0 &&
        in_array(
            $action,
            ['Approved', 'Rejected']
        )
    ) {

        $stmt =
            $conn->prepare(
                "
                UPDATE client_quotations
                SET quotation_status = ?
                WHERE id = ?
                AND client_id = ?
                "
            );

        $stmt->bind_param(
            "sii",
            $action,
            $quotationId,
            $clientId
        );

        $stmt->execute();

        header(
            "Location: approvals.php?success=1"
        );

        exit();
    }
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
| STATS
|--------------------------------------------------------------------------
*/

$totalCount = 0;
$approvedCount = 0;
$pendingCount = 0;
$rejectedCount = 0;

if ($quotations && $quotations->num_rows > 0) {

    while ($calc = $quotations->fetch_assoc()) {

        $totalCount++;

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

        if (
            $calc['quotation_status']
            === 'Rejected'
        ) {

            $rejectedCount++;
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
        Quotation Approvals
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

        .card h4{

            color:#666;

            margin-bottom:10px;
        }

        .card h2{

            font-size:30px;
        }

        .success-message{

            background:#d4edda;

            color:#155724;

            padding:16px 20px;

            border-radius:12px;

            margin-bottom:25px;

            font-weight:bold;
        }

        .quotation-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(350px,1fr));

            gap:25px;
        }

        .quotation-card{

            background:#fff;

            border-radius:20px;

            padding:25px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .quotation-card h3{

            margin-bottom:12px;

            color:#111827;
        }

        .quotation-card p{

            margin-bottom:10px;

            color:#555;
        }

        .amount{

            font-size:28px;

            font-weight:bold;

            margin:20px 0;

            color:#111827;
        }

        .badge{

            display:inline-block;

            padding:8px 16px;

            border-radius:30px;

            font-size:12px;

            font-weight:bold;

            margin-bottom:20px;
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

        .actions{

            display:flex;

            gap:12px;

            flex-wrap:wrap;

            margin-top:20px;
        }

        .btn{

            border:none;

            padding:12px 18px;

            border-radius:10px;

            cursor:pointer;

            font-weight:bold;

            transition:0.3s;
        }

        .approve-btn{

            background:#28a745;

            color:#fff;
        }

        .reject-btn{

            background:#dc3545;

            color:#fff;
        }

        .view-btn{

            background:#111827;

            color:#fff;

            text-decoration:none;

            display:inline-block;
        }

        .btn:hover{

            opacity:0.9;
        }

        .empty{

            background:#fff;

            padding:60px;

            text-align:center;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
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

    <a href="index.php">
        Quotations
    </a>

    <a
        href="approvals.php"
        class="active"
    >
        Approvals
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
                Quotation Approvals
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

    <!-- SUCCESS -->

    <?php if (isset($_GET['success'])): ?>

        <div class="success-message">

            Quotation status updated successfully.

        </div>

    <?php endif; ?>

    <!-- STATS -->

    <div class="stats">

        <div class="card">

            <h4>
                Total Quotations
            </h4>

            <h2>

                <?php
                    echo $totalCount;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Approved
            </h4>

            <h2>

                <?php
                    echo $approvedCount;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Pending
            </h4>

            <h2>

                <?php
                    echo $pendingCount;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Rejected
            </h4>

            <h2>

                <?php
                    echo $rejectedCount;
                ?>

            </h2>

        </div>

    </div>

    <!-- QUOTATION LIST -->

    <?php if ($quotations && $quotations->num_rows > 0): ?>

        <div class="quotation-grid">

            <?php while ($row = $quotations->fetch_assoc()): ?>

                <div class="quotation-card">

                    <span
                        class="badge <?php echo htmlspecialchars((string)$row['quotation_status']); ?>"
                    >

                        <?php
                            echo htmlspecialchars(
                                (string)$row['quotation_status']
                            );
                        ?>

                    </span>

                    <h3>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['project_name']
                            );
                        ?>

                    </h3>

                    <p>

                        <strong>
                            Quotation No:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['quotation_number']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Title:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['quotation_title']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Valid Till:
                        </strong>

                        <?php
                            echo date(
                                'd M Y',
                                strtotime(
                                    (string)$row['validity_date']
                                )
                            );
                        ?>

                    </p>

                    <div class="amount">

                        ₹<?php
                            echo number_format(
                                (float)$row['total_amount'],
                                2
                            );
                        ?>

                    </div>

                    <div class="actions">

                        <a
                            href="view.php?id=<?php echo (int)$row['id']; ?>"
                            class="btn view-btn"
                        >
                            View Details
                        </a>

                        <?php if ($row['quotation_status'] === 'Pending'): ?>

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="quotation_id"
                                    value="<?php echo (int)$row['id']; ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="Approved"
                                >

                                <button
                                    type="submit"
                                    class="btn approve-btn"
                                >
                                    Approve
                                </button>

                            </form>

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="quotation_id"
                                    value="<?php echo (int)$row['id']; ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="Rejected"
                                >

                                <button
                                    type="submit"
                                    class="btn reject-btn"
                                >
                                    Reject
                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            No quotations available for approval.

        </div>

    <?php endif; ?>

</div>

</body>

</html>