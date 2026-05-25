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
| CREATE TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS client_uploaded_images (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        image_title VARCHAR(255) NOT NULL,

        project_name VARCHAR(255) NOT NULL,

        image_file VARCHAR(255) NOT NULL,

        image_size VARCHAR(50) NOT NULL,

        image_type VARCHAR(50) NOT NULL,

        upload_date DATE NOT NULL,

        status ENUM(
            'Approved',
            'Pending',
            'Rejected'
        )
        NOT NULL DEFAULT 'Pending',

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
        FROM client_uploaded_images
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
        INSERT INTO client_uploaded_images
        (

            client_id,
            image_title,
            project_name,
            image_file,
            image_size,
            image_type,
            upload_date,
            status,
            remarks

        )

        VALUES

        (
            $clientId,
            'Front Elevation Design',
            'Luxury Villa Project',
            'front-elevation.jpg',
            '3.2 MB',
            'JPG',
            '2026-05-10',
            'Approved',
            'Approved by architectural team.'
        ),

        (
            $clientId,
            'Kitchen Interior Reference',
            'Farm House Project',
            'kitchen-reference.png',
            '2.1 MB',
            'PNG',
            '2026-05-12',
            'Pending',
            'Awaiting designer review.'
        ),

        (
            $clientId,
            'Bedroom Ceiling Concept',
            'Commercial Complex',
            'ceiling-concept.jpeg',
            '4.6 MB',
            'JPEG',
            '2026-05-14',
            'Rejected',
            'Image quality too low.'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH IMAGES
|--------------------------------------------------------------------------
*/

$images =
    $conn->query(
        "
        SELECT *
        FROM client_uploaded_images
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalImages = 0;
$approvedImages = 0;
$pendingImages = 0;
$rejectedImages = 0;

if ($images && $images->num_rows > 0) {

    while ($calc = $images->fetch_assoc()) {

        $totalImages++;

        if (
            $calc['status']
            === 'Approved'
        ) {

            $approvedImages++;
        }

        if (
            $calc['status']
            === 'Pending'
        ) {

            $pendingImages++;
        }

        if (
            $calc['status']
            === 'Rejected'
        ) {

            $rejectedImages++;
        }
    }

    $images->data_seek(0);
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
        Uploaded Images
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

        .upload-btn{

            text-decoration:none;

            background:#111827;

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

        .images-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(340px,1fr));

            gap:25px;
        }

        .image-card{

            background:#fff;

            border-radius:20px;

            overflow:hidden;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .image-preview{

            height:220px;

            background:#ddd;

            display:flex;

            align-items:center;

            justify-content:center;

            font-size:18px;

            color:#666;
        }

        .image-content{

            padding:22px;
        }

        .image-content h3{

            margin-bottom:15px;

            color:#111827;
        }

        .image-content p{

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

        .view-btn{

            background:#f5b400;

            color:#111;
        }

        .download-btn{

            background:#111827;

            color:#fff;
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

    <a href="../documents/index.php">
        Documents
    </a>

    <a
        href="images.php"
        class="active"
    >
        Uploaded Images
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
                Uploaded Images
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

        <div>

            <a
                href="#"
                class="upload-btn"
            >
                Upload Image
            </a>

            <a
                href="../logout.php"
                class="logout-btn"
            >
                Logout
            </a>

        </div>

    </div>

    <!-- STATS -->

    <div class="stats">

        <div class="card">

            <h4>
                Total Images
            </h4>

            <h2>

                <?php
                    echo $totalImages;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Approved
            </h4>

            <h2>

                <?php
                    echo $approvedImages;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Pending
            </h4>

            <h2>

                <?php
                    echo $pendingImages;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Rejected
            </h4>

            <h2>

                <?php
                    echo $rejectedImages;
                ?>

            </h2>

        </div>

    </div>

    <!-- IMAGE LIST -->

    <?php if ($images && $images->num_rows > 0): ?>

        <div class="images-grid">

            <?php while ($row = $images->fetch_assoc()): ?>

                <div class="image-card">

                    <div class="image-preview">

                        🖼️ Image Preview

                    </div>

                    <div class="image-content">

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
                                    (string)$row['image_title']
                                );
                            ?>

                        </h3>

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
                                File:
                            </strong>

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['image_file']
                                );
                            ?>

                        </p>

                        <p>

                            <strong>
                                Type:
                            </strong>

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['image_type']
                                );
                            ?>

                        </p>

                        <p>

                            <strong>
                                Size:
                            </strong>

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['image_size']
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

                            <a
                                href="#"
                                class="btn download-btn"
                            >
                                Download
                            </a>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            No uploaded images available.

        </div>

    <?php endif; ?>

</div>

</body>

</html>