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
    CREATE TABLE IF NOT EXISTS client_uploaded_videos (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        video_title VARCHAR(255) NOT NULL,

        project_name VARCHAR(255) NOT NULL,

        video_file VARCHAR(255) NOT NULL,

        thumbnail VARCHAR(255) DEFAULT NULL,

        video_size VARCHAR(50) NOT NULL,

        video_duration VARCHAR(50) NOT NULL,

        video_type VARCHAR(50) NOT NULL,

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
        FROM client_uploaded_videos
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
        INSERT INTO client_uploaded_videos
        (

            client_id,
            video_title,
            project_name,
            video_file,
            thumbnail,
            video_size,
            video_duration,
            video_type,
            upload_date,
            status,
            remarks

        )

        VALUES

        (
            $clientId,
            'Villa Site Progress Walkthrough',
            'Luxury Villa Project',
            'villa-site-progress.mp4',
            'villa-thumb.jpg',
            '145 MB',
            '08:24',
            'MP4',
            '2026-05-10',
            'Approved',
            'Weekly construction progress update.'
        ),

        (
            $clientId,
            'Kitchen Interior Animation',
            'Farm House Project',
            'kitchen-animation.mp4',
            'kitchen-thumb.jpg',
            '96 MB',
            '03:12',
            'MP4',
            '2026-05-12',
            'Pending',
            'Awaiting design approval.'
        ),

        (
            $clientId,
            'Parking Layout Demo',
            'Commercial Complex',
            'parking-layout.mov',
            'parking-thumb.jpg',
            '210 MB',
            '05:48',
            'MOV',
            '2026-05-14',
            'Rejected',
            'Incorrect resolution format.'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH VIDEOS
|--------------------------------------------------------------------------
*/

$videos =
    $conn->query(
        "
        SELECT *
        FROM client_uploaded_videos
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalVideos = 0;
$approvedVideos = 0;
$pendingVideos = 0;
$rejectedVideos = 0;

if ($videos && $videos->num_rows > 0) {

    while ($calc = $videos->fetch_assoc()) {

        $totalVideos++;

        if (
            $calc['status']
            === 'Approved'
        ) {

            $approvedVideos++;
        }

        if (
            $calc['status']
            === 'Pending'
        ) {

            $pendingVideos++;
        }

        if (
            $calc['status']
            === 'Rejected'
        ) {

            $rejectedVideos++;
        }
    }

    $videos->data_seek(0);
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
        Uploaded Videos
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

        .videos-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(360px,1fr));

            gap:25px;
        }

        .video-card{

            background:#fff;

            border-radius:20px;

            overflow:hidden;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .video-preview{

            height:220px;

            background:#1f2937;

            display:flex;

            align-items:center;

            justify-content:center;

            flex-direction:column;

            color:#fff;

            font-size:18px;
        }

        .play-icon{

            font-size:60px;

            margin-bottom:10px;
        }

        .video-content{

            padding:22px;
        }

        .video-content h3{

            margin-bottom:15px;

            color:#111827;
        }

        .video-content p{

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

        .watch-btn{

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

    <a href="images.php">
        Uploaded Images
    </a>

    <a
        href="videos.php"
        class="active"
    >
        Uploaded Videos
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
                Uploaded Videos
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
                Upload Video
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
                Total Videos
            </h4>

            <h2>

                <?php
                    echo $totalVideos;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Approved
            </h4>

            <h2>

                <?php
                    echo $approvedVideos;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Pending
            </h4>

            <h2>

                <?php
                    echo $pendingVideos;
                ?>

            </h2>

        </div>

        <div class="card">

            <h4>
                Rejected
            </h4>

            <h2>

                <?php
                    echo $rejectedVideos;
                ?>

            </h2>

        </div>

    </div>

    <!-- VIDEO LIST -->

    <?php if ($videos && $videos->num_rows > 0): ?>

        <div class="videos-grid">

            <?php while ($row = $videos->fetch_assoc()): ?>

                <div class="video-card">

                    <div class="video-preview">

                        <div class="play-icon">
                            ▶
                        </div>

                        Video Preview

                    </div>

                    <div class="video-content">

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
                                    (string)$row['video_title']
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
                                    (string)$row['video_file']
                                );
                            ?>

                        </p>

                        <p>

                            <strong>
                                Type:
                            </strong>

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['video_type']
                                );
                            ?>

                        </p>

                        <p>

                            <strong>
                                Duration:
                            </strong>

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['video_duration']
                                );
                            ?>

                        </p>

                        <p>

                            <strong>
                                Size:
                            </strong>

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['video_size']
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
                                class="btn watch-btn"
                            >
                                Watch Video
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

            No uploaded videos available.

        </div>

    <?php endif; ?>

</div>

</body>

</html>