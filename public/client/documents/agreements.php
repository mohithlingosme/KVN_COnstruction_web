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
| CREATE AGREEMENTS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS client_agreements (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        agreement_title VARCHAR(255) NOT NULL,

        agreement_number VARCHAR(100) NOT NULL,

        project_name VARCHAR(255) NOT NULL,

        agreement_type VARCHAR(100) NOT NULL,

        start_date DATE NOT NULL,

        end_date DATE DEFAULT NULL,

        agreement_value DECIMAL(15,2)
        DEFAULT 0.00,

        status ENUM(
            'Active',
            'Completed',
            'Expired',
            'Pending'
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
        FROM client_agreements
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
        INSERT INTO client_agreements
        (

            client_id,
            agreement_title,
            agreement_number,
            project_name,
            agreement_type,
            start_date,
            end_date,
            agreement_value,
            status,
            file_name,
            remarks

        )

        VALUES

        (
            $clientId,
            'Luxury Villa Construction Agreement',
            'AGR-2026-001',
            'Luxury Villa Project',
            'Turnkey Construction',
            '2026-01-10',
            '2027-01-10',
            8500000.00,
            'Active',
            'villa-agreement.pdf',
            'Main construction agreement approved and signed.'
        ),

        (
            $clientId,
            'Farm House Interior Agreement',
            'AGR-2026-002',
            'Farm House Project',
            'Interior Design',
            '2026-03-01',
            '2026-09-01',
            2400000.00,
            'Pending',
            'farmhouse-interior.pdf',
            'Pending final signature from client.'
        ),

        (
            $clientId,
            'Commercial Building Contract',
            'AGR-2025-003',
            'Commercial Complex',
            'Commercial Construction',
            '2025-01-15',
            '2026-01-15',
            18500000.00,
            'Completed',
            'commercial-contract.pdf',
            'Project completed successfully.'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH AGREEMENTS
|--------------------------------------------------------------------------
*/

$agreements =
    $conn->query(
        "
        SELECT *
        FROM client_agreements
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalAgreements = 0;
$activeAgreements = 0;
$pendingAgreements = 0;
$completedAgreements = 0;

if ($agreements && $agreements->num_rows > 0) {

    while ($calc = $agreements->fetch_assoc()) {

        $totalAgreements++;

        if (
            $calc['status']
            === 'Active'
        ) {

            $activeAgreements++;
        }

        if (
            $calc['status']
            === 'Pending'
        ) {

            $pendingAgreements++;
        }

        if (
            $calc['status']
            === 'Completed'
        ) {

            $completedAgreements++;
        }
    }

    $agreements->data_seek(0);
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
        Client Agreements
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

        .agreements-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(380px,1fr));

            gap:25px;
        }

        .agreement-card{

            background:#fff;

            border-radius:20px;

            padding:25px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .agreement-card h3{

            margin-bottom:15px;

            color:#111827;
        }

        .agreement-card p{

            margin-bottom:10px;

            color:#555;
        }

        .agreement-value{

            font-size:28px;

            font-weight:bold;

            margin:18px 0;

            color:#111827;
        }

        .badge{

            display:inline-block;

            padding:8px 16px;

            border-radius:30px;

            font-size:12px;

            font-weight:bold;

            margin-bottom:18px;
        }

        .Active{

            background:#d4edda;

            color:#155724;
        }

        .Pending{

            background:#fff3cd;

            color:#856404;
        }

        .Completed{

            background:#d1ecf1;

            color:#0c5460;
        }

        .Expired{

            background:#f8d7da;

            color:#721c24;
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

    <a
        href="agreements.php"
        class="active"
    >
        Agreements
    </a>

    <a href="index.php">
        Documents
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
                Project Agreements
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
                Total Agreements
            </h4>

            <h2>

                <?php
                    echo $totalAgreements;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Active
            </h4>

            <h2>

                <?php
                    echo $activeAgreements;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Pending
            </h4>

            <h2>

                <?php
                    echo $pendingAgreements;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Completed
            </h4>

            <h2>

                <?php
                    echo $completedAgreements;
                ?>

            </h2>

        </div>

    </div>

    <!-- AGREEMENTS -->

    <?php if ($agreements && $agreements->num_rows > 0): ?>

        <div class="agreements-grid">

            <?php while ($row = $agreements->fetch_assoc()): ?>

                <div class="agreement-card">

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
                                (string)$row['agreement_title']
                            );
                        ?>

                    </h3>

                    <p>

                        <strong>
                            Agreement No:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['agreement_number']
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
                            Agreement Type:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['agreement_type']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Start Date:
                        </strong>

                        <?php
                            echo date(
                                'd M Y',
                                strtotime(
                                    (string)$row['start_date']
                                )
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            End Date:
                        </strong>

                        <?php
                            echo date(
                                'd M Y',
                                strtotime(
                                    (string)$row['end_date']
                                )
                            );
                        ?>

                    </p>

                    <div class="agreement-value">

                        ₹<?php
                            echo number_format(
                                (float)$row['agreement_value'],
                                2
                            );
                        ?>

                    </div>

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
                            View Agreement
                        </a>

                        <a
                            href="#"
                            class="btn download-btn"
                        >
                            Download PDF
                        </a>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            No agreements available.

        </div>

    <?php endif; ?>

</div>

</body>

</html>