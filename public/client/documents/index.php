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
| CREATE DOCUMENTS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS client_documents (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        document_title VARCHAR(255) NOT NULL,

        document_category VARCHAR(100) NOT NULL,

        file_name VARCHAR(255) NOT NULL,

        file_type VARCHAR(50) NOT NULL,

        file_size VARCHAR(50) NOT NULL,

        upload_date DATE NOT NULL,

        status ENUM(
            'Active',
            'Archived',
            'Pending'
        )
        NOT NULL DEFAULT 'Active',

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
        FROM client_documents
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
        INSERT INTO client_documents
        (

            client_id,
            document_title,
            document_category,
            file_name,
            file_type,
            file_size,
            upload_date,
            status,
            remarks

        )

        VALUES

        (
            $clientId,
            'Construction Agreement',
            'Legal',
            'construction-agreement.pdf',
            'PDF',
            '2.3 MB',
            '2026-01-15',
            'Active',
            'Signed project agreement document.'
        ),

        (
            $clientId,
            'Project Floor Plan',
            'Architecture',
            'floor-plan-v1.pdf',
            'PDF',
            '5.1 MB',
            '2026-02-01',
            'Active',
            'Approved architectural floor plan.'
        ),

        (
            $clientId,
            'Structural Drawings',
            'Engineering',
            'structural-drawings.zip',
            'ZIP',
            '18.6 MB',
            '2026-03-10',
            'Pending',
            'Final structural review pending.'
        ),

        (
            $clientId,
            'Electrical Layout',
            'MEP',
            'electrical-layout.pdf',
            'PDF',
            '3.7 MB',
            '2026-04-05',
            'Archived',
            'Old electrical layout version.'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH DOCUMENTS
|--------------------------------------------------------------------------
*/

$documents =
    $conn->query(
        "
        SELECT *
        FROM client_documents
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalDocuments = 0;
$activeDocuments = 0;
$pendingDocuments = 0;
$archivedDocuments = 0;

if ($documents && $documents->num_rows > 0) {

    while ($calc = $documents->fetch_assoc()) {

        $totalDocuments++;

        if (
            $calc['status']
            === 'Active'
        ) {

            $activeDocuments++;
        }

        if (
            $calc['status']
            === 'Pending'
        ) {

            $pendingDocuments++;
        }

        if (
            $calc['status']
            === 'Archived'
        ) {

            $archivedDocuments++;
        }
    }

    $documents->data_seek(0);
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
        Client Documents
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

        .documents-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(350px,1fr));

            gap:25px;
        }

        .document-card{

            background:#fff;

            border-radius:20px;

            padding:25px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .document-card h3{

            margin-bottom:15px;

            color:#111827;
        }

        .document-card p{

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

        .Active{

            background:#d4edda;

            color:#155724;
        }

        .Pending{

            background:#fff3cd;

            color:#856404;
        }

        .Archived{

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

    <a
        href="index.php"
        class="active"
    >
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
                Project Documents
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
                Total Documents
            </h4>

            <h2>

                <?php
                    echo $totalDocuments;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Active Documents
            </h4>

            <h2>

                <?php
                    echo $activeDocuments;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Pending Review
            </h4>

            <h2>

                <?php
                    echo $pendingDocuments;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Archived
            </h4>

            <h2>

                <?php
                    echo $archivedDocuments;
                ?>

            </h2>

        </div>

    </div>

    <!-- DOCUMENT LIST -->

    <?php if ($documents && $documents->num_rows > 0): ?>

        <div class="documents-grid">

            <?php while ($row = $documents->fetch_assoc()): ?>

                <div class="document-card">

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
                                (string)$row['document_title']
                            );
                        ?>

                    </h3>

                    <p>

                        <strong>
                            Category:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['document_category']
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
                            Upload Date:
                        </strong>

                        <?php
                            echo date(
                                'd M Y',
                                strtotime(
                                    (string)$row['upload_date']
                                )
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
                            View
                        </a>

                        <?php if ($row['status'] !== 'Archived'): ?>

                            <a
                                href="#"
                                class="btn download-btn"
                            >
                                Download
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            No documents available.

        </div>

    <?php endif; ?>

</div>

</body>

</html>