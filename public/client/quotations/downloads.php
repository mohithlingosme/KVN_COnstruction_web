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
| CREATE DOWNLOADS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS quotation_downloads (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        quotation_id INT NOT NULL,

        quotation_number VARCHAR(100) NOT NULL,

        project_name VARCHAR(255) NOT NULL,

        file_name VARCHAR(255) NOT NULL,

        file_type VARCHAR(50) NOT NULL,

        file_size VARCHAR(50) NOT NULL,

        download_status ENUM(
            'Available',
            'Expired',
            'Removed'
        )
        NOT NULL DEFAULT 'Available',

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
        FROM quotation_downloads
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
        INSERT INTO quotation_downloads
        (

            client_id,
            quotation_id,
            quotation_number,
            project_name,
            file_name,
            file_type,
            file_size,
            download_status

        )

        VALUES

        (
            $clientId,
            1,
            'QTN-2026-001',
            'Luxury Villa',
            'Luxury-Villa-Quotation.pdf',
            'PDF',
            '2.4 MB',
            'Available'
        ),

        (
            $clientId,
            2,
            'QTN-2026-002',
            'Farm House',
            'Farm-House-Quotation.pdf',
            'PDF',
            '1.8 MB',
            'Available'
        ),

        (
            $clientId,
            3,
            'QTN-2026-003',
            'Commercial Complex',
            'Commercial-Quotation.pdf',
            'PDF',
            '3.1 MB',
            'Expired'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH DOWNLOAD FILES
|--------------------------------------------------------------------------
*/

$downloads =
    $conn->query(
        "
        SELECT *
        FROM quotation_downloads
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalFiles = 0;
$availableFiles = 0;
$expiredFiles = 0;

if ($downloads && $downloads->num_rows > 0) {

    while ($calc = $downloads->fetch_assoc()) {

        $totalFiles++;

        if (
            $calc['download_status']
            === 'Available'
        ) {

            $availableFiles++;
        }

        if (
            $calc['download_status']
            === 'Expired'
        ) {

            $expiredFiles++;
        }
    }

    $downloads->data_seek(0);
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
        Quotation Downloads
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

        .downloads-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(350px,1fr));

            gap:25px;
        }

        .download-card{

            background:#fff;

            border-radius:20px;

            padding:25px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .download-card h3{

            margin-bottom:15px;

            color:#111827;
        }

        .download-card p{

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

        .Available{

            background:#d4edda;

            color:#155724;
        }

        .Expired{

            background:#fff3cd;

            color:#856404;
        }

        .Removed{

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

    <a href="index.php">
        Quotations
    </a>

    <a href="approvals.php">
        Approvals
    </a>

    <a
        href="downloads.php"
        class="active"
    >
        Downloads
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
                Download Quotations
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
                Total Files
            </h4>

            <h2>

                <?php
                    echo $totalFiles;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Available Files
            </h4>

            <h2>

                <?php
                    echo $availableFiles;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Expired Files
            </h4>

            <h2>

                <?php
                    echo $expiredFiles;
                ?>

            </h2>

        </div>

    </div>

    <!-- DOWNLOAD LIST -->

    <?php if ($downloads && $downloads->num_rows > 0): ?>

        <div class="downloads-grid">

            <?php while ($row = $downloads->fetch_assoc()): ?>

                <div class="download-card">

                    <span
                        class="badge <?php echo htmlspecialchars((string)$row['download_status']); ?>"
                    >

                        <?php
                            echo htmlspecialchars(
                                (string)$row['download_status']
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
                            File Name:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['file_name']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            File Type:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['file_type']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            File Size:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['file_size']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Uploaded:
                        </strong>

                        <?php
                            echo date(
                                'd M Y',
                                strtotime(
                                    (string)$row['created_at']
                                )
                            );
                        ?>

                    </p>

                    <div class="actions">

                        <a
                            href="view.php?id=<?php echo (int)$row['quotation_id']; ?>"
                            class="btn view-btn"
                        >
                            View
                        </a>

                        <?php if ($row['download_status'] === 'Available'): ?>

                            <a
                                href="#"
                                class="btn download-btn"
                            >
                                Download PDF
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            No quotation files available.

        </div>

    <?php endif; ?>

</div>

</body>

</html>