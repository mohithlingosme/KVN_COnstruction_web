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
| CREATE PERMITS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS client_permits (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        permit_title VARCHAR(255) NOT NULL,

        permit_number VARCHAR(100) NOT NULL,

        authority_name VARCHAR(255) NOT NULL,

        project_name VARCHAR(255) NOT NULL,

        issue_date DATE NOT NULL,

        expiry_date DATE DEFAULT NULL,

        status ENUM(
            'Approved',
            'Pending',
            'Rejected',
            'Expired'
        )
        NOT NULL DEFAULT 'Pending',

        file_name VARCHAR(255) DEFAULT NULL,

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
        FROM client_permits
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
        INSERT INTO client_permits
        (

            client_id,
            permit_title,
            permit_number,
            authority_name,
            project_name,
            issue_date,
            expiry_date,
            status,
            file_name,
            remarks

        )

        VALUES

        (
            $clientId,
            'Building Construction Permit',
            'PRM-2026-001',
            'BBMP Bangalore',
            'Luxury Villa Project',
            '2026-01-15',
            '2027-01-15',
            'Approved',
            'building-permit.pdf',
            'Construction permit approved successfully.'
        ),

        (
            $clientId,
            'Electrical Approval',
            'PRM-2026-002',
            'BESCOM',
            'Farm House Project',
            '2026-03-10',
            '2027-03-10',
            'Pending',
            'electrical-approval.pdf',
            'Awaiting final authority verification.'
        ),

        (
            $clientId,
            'Water Connection Permit',
            'PRM-2025-003',
            'BWSSB',
            'Commercial Complex',
            '2025-05-01',
            '2026-05-01',
            'Expired',
            'water-permit.pdf',
            'Permit expired and renewal required.'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH PERMITS
|--------------------------------------------------------------------------
*/

$permits =
    $conn->query(
        "
        SELECT *
        FROM client_permits
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalPermits = 0;
$approvedPermits = 0;
$pendingPermits = 0;
$expiredPermits = 0;

if ($permits && $permits->num_rows > 0) {

    while ($calc = $permits->fetch_assoc()) {

        $totalPermits++;

        if (
            $calc['status']
            === 'Approved'
        ) {

            $approvedPermits++;
        }

        if (
            $calc['status']
            === 'Pending'
        ) {

            $pendingPermits++;
        }

        if (
            $calc['status']
            === 'Expired'
        ) {

            $expiredPermits++;
        }
    }

    $permits->data_seek(0);
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
        Client Permits
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

        .permits-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(380px,1fr));

            gap:25px;
        }

        .permit-card{

            background:#fff;

            border-radius:20px;

            padding:25px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .permit-card h3{

            margin-bottom:15px;

            color:#111827;
        }

        .permit-card p{

            margin-bottom:10px;

            color:#555;
        }

        .badge{

            display:inline-block;

            padding:8px 16px;

            border-radius:30px;

            font-size:12px;

            font-weight:bold;

            margin-bottom:18px;
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

            margin-top:20px;

            display:flex;

            gap:12px;

            flex-wrap:wrap;
        }

        .btn{

            text-decoration:none;

            padding:12px 18px;

            border-radius:10px;

            font-weight:bold;

            transition:0.3s;
        }

        .download-btn{

            background:#111827;

            color:#fff;
        }

        .view-btn{

            background:#f5b400;

            color:#111;
        }

        .btn:hover{

            opacity:0.9;
        }

        .empty{

            background:#fff;

            padding:60px;

            border-radius:20px;

            text-align:center;

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

    <a href="../projects/index.php">
        Projects
    </a>

    <a href="../quotations/index.php">
        Quotations
    </a>

    <a href="index.php">
        Documents
    </a>

    <a
        href="permits.php"
        class="active"
    >
        Permits
    </a>

    <a href="agreements.php">
        Agreements
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
                Project Permits
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
                Total Permits
            </h4>

            <h2>

                <?php
                    echo $totalPermits;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Approved
            </h4>

            <h2>

                <?php
                    echo $approvedPermits;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Pending
            </h4>

            <h2>

                <?php
                    echo $pendingPermits;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Expired
            </h4>

            <h2>

                <?php
                    echo $expiredPermits;
                ?>

            </h2>

        </div>

    </div>

    <!-- PERMITS -->

    <?php if ($permits && $permits->num_rows > 0): ?>

        <div class="permits-grid">

            <?php while ($row = $permits->fetch_assoc()): ?>

                <div class="permit-card">

                    <span
                        class="badge <?php echo htmlspecialchars((string)$row['status']); ?>"
                    >

                        <?php
                            echo htmlspecialchars(
                                (string)$row['status']
                            );
                        ?>

                    </span>

                    <h3>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['permit_title']
                            );
                        ?>

                    </h3>

                    <p>

                        <strong>
                            Permit No:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['permit_number']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Authority:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['authority_name']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Project:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['project_name']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Issue Date:
                        </strong>

                        <?php
                            echo date(
                                'd M Y',
                                strtotime(
                                    (string)$row['issue_date']
                                )
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Expiry Date:
                        </strong>

                        <?php
                            echo date(
                                'd M Y',
                                strtotime(
                                    (string)$row['expiry_date']
                                )
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            File:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['file_name']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Remarks:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['remarks']
                            );
                        ?>

                    </p>

                    <div class="actions">

                        <a
                            href="#"
                            class="btn view-btn"
                        >
                            View Permit
                        </a>

                        <a
                            href="#"
                            class="btn download-btn"
                        >
                            Download
                        </a>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            No permits available.

        </div>

    <?php endif; ?>

</div>

</body>

</html>