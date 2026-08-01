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
| FETCH DOCUMENT DATA
|--------------------------------------------------------------------------
*/

$data = $clientService->getDocumentData($clientId);
$documents = $data['documents'];
$totalDocuments = $data['totalDocuments'];
$activeDocuments = $data['activeDocuments'];
$pendingDocuments = $data['pendingDocuments'];
$archivedDocuments = $data['archivedDocuments'];

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

    <a href="<?php echo base_url('admin/dashboard.php'); ?>">
        Dashboard
    </a>

    <a href="<?php echo base_url('admin/projects/index.php'); ?>">
        Projects
    </a>

    <a href="<?php echo base_url('admin/quotations/index.php'); ?>">
        Quotations
    </a>

    <a
        href="index.php"
        class="active"
    >
        Documents
    </a>

    <a href="<?php echo base_url('admin/payments/index.php'); ?>">
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
                Total Documents
            </h4>

            <h2>

                <?php
                    echo (int)$totalDocuments;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Active Documents
            </h4>

            <h2>

                <?php
                    echo (int)$activeDocuments;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Pending Review
            </h4>

            <h2>

                <?php
                    echo (int)$pendingDocuments;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Archived
            </h4>

            <h2>

                <?php
                    echo (int)$archivedDocuments;
                ?>

            </h2>

        </div>

    </div>

    <!-- DOCUMENT LIST -->

    <?php if (!empty($documents)): ?>

        <div class="documents-grid">

            <?php foreach ($documents as $row): ?>

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
                            href="<?php echo base_url('uploads/client-documents/' . htmlspecialchars((string)$row['file_name'])); ?>"
                            class="btn view-btn"
                            target="_blank"
                        >
                            View
                        </a>

                        <?php if ($row['status'] !== 'Archived'): ?>

                            <a
                                href="<?php echo base_url('uploads/client-documents/' . htmlspecialchars((string)$row['file_name'])); ?>"
                                class="btn download-btn"
                                download
                            >
                                Download
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            No documents available.

        </div>

    <?php endif; ?>

</div>

</body>

</html>