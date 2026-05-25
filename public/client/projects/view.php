<?php

declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| CLIENT AUTH CHECK
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
| VALIDATE PROJECT ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id'])) {

    header('Location: index.php');
    exit();
}

$projectId =
    (int) $_GET['id'];

$clientId =
    (int) $_SESSION['client_id'];

/*
|--------------------------------------------------------------------------
| CREATE TABLES
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS client_projects (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        project_name VARCHAR(255) NOT NULL,

        project_type VARCHAR(255) NOT NULL,

        project_location VARCHAR(255) NOT NULL,

        project_area VARCHAR(100) NOT NULL,

        budget DECIMAL(12,2) NOT NULL,

        progress INT NOT NULL DEFAULT 0,

        project_status ENUM(
            'Planning',
            'In Progress',
            'Completed',
            'On Hold'
        )
        NOT NULL DEFAULT 'Planning',

        start_date DATE NOT NULL,

        expected_handover DATE NOT NULL,

        description TEXT DEFAULT NULL,

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS project_updates (

        id INT AUTO_INCREMENT PRIMARY KEY,

        project_id INT NOT NULL,

        update_title VARCHAR(255) NOT NULL,

        update_description TEXT NOT NULL,

        update_date DATE NOT NULL,

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| FETCH PROJECT
|--------------------------------------------------------------------------
*/

$stmt =
    $conn->prepare(
        "
        SELECT *
        FROM client_projects
        WHERE id = ?
        AND client_id = ?
        LIMIT 1
        "
    );

$stmt->bind_param(
    'ii',
    $projectId,
    $clientId
);

$stmt->execute();

$result =
    $stmt->get_result();

if ($result->num_rows === 0) {

    header('Location: index.php');
    exit();
}

$project =
    $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| INSERT DEMO PROJECT UPDATES
|--------------------------------------------------------------------------
*/

$checkUpdates =
    $conn->query(
        "
        SELECT id
        FROM project_updates
        WHERE project_id = $projectId
        LIMIT 1
        "
    );

if (
    $checkUpdates &&
    $checkUpdates->num_rows === 0
) {

    $conn->query(
        "
        INSERT INTO project_updates
        (

            project_id,
            update_title,
            update_description,
            update_date

        )

        VALUES

        (
            $projectId,
            'Foundation Completed',
            'The foundation work has been successfully completed.',
            '2026-02-10'
        ),

        (
            $projectId,
            'Structural Work Ongoing',
            'Pillar and slab work is currently under progress.',
            '2026-04-15'
        ),

        (
            $projectId,
            'Electrical Planning',
            'Electrical line drawings and fittings finalized.',
            '2026-05-05'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH PROJECT UPDATES
|--------------------------------------------------------------------------
*/

$updates =
    $conn->query(
        "
        SELECT *
        FROM project_updates
        WHERE project_id = $projectId
        ORDER BY update_date DESC
        "
    );

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
        View Project
    </title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            font-family:Arial,sans-serif;

            background:#f4f6f9;

            color:#222;
        }

        .container{

            max-width:1400px;

            margin:auto;

            padding:40px 20px;
        }

        .topbar{

            display:flex;

            justify-content:space-between;

            align-items:center;

            margin-bottom:35px;
        }

        .back-btn{

            text-decoration:none;

            background:#111827;

            color:#fff;

            padding:12px 18px;

            border-radius:10px;

            font-weight:bold;
        }

        .logout-btn{

            text-decoration:none;

            background:#dc3545;

            color:#fff;

            padding:12px 18px;

            border-radius:10px;

            font-weight:bold;
        }

        .project-card{

            background:#fff;

            border-radius:20px;

            padding:35px;

            margin-bottom:35px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .header{

            display:flex;

            justify-content:space-between;

            align-items:center;

            flex-wrap:wrap;

            gap:15px;

            margin-bottom:30px;
        }

        .header h1{

            font-size:32px;
        }

        .badge{

            padding:10px 18px;

            border-radius:30px;

            font-size:13px;

            font-weight:bold;
        }

        .Planning{

            background:#d1ecf1;

            color:#0c5460;
        }

        .In.Progress{

            background:#fff3cd;

            color:#856404;
        }

        .Completed{

            background:#d4edda;

            color:#155724;
        }

        .On.Hold{

            background:#f8d7da;

            color:#721c24;
        }

        .details-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(280px,1fr));

            gap:25px;
        }

        .detail-box{

            background:#fafafa;

            border-radius:15px;

            padding:20px;
        }

        .detail-box h3{

            color:#666;

            margin-bottom:10px;

            font-size:15px;
        }

        .detail-box p{

            font-size:18px;

            font-weight:bold;
        }

        .description{

            margin-top:30px;

            background:#fafafa;

            padding:25px;

            border-radius:15px;
        }

        .description h2{

            margin-bottom:15px;
        }

        .progress-box{

            margin-top:30px;
        }

        .progress{

            width:100%;

            height:26px;

            background:#e5e7eb;

            border-radius:30px;

            overflow:hidden;

            margin-top:10px;
        }

        .progress-fill{

            height:100%;

            background:#28a745;

            color:#fff;

            font-size:13px;

            font-weight:bold;

            text-align:center;

            line-height:26px;
        }

        .updates-section{

            background:#fff;

            border-radius:20px;

            padding:35px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .updates-section h2{

            margin-bottom:25px;
        }

        .timeline{

            border-left:4px solid #f5b400;

            padding-left:25px;
        }

        .timeline-item{

            margin-bottom:30px;

            position:relative;
        }

        .timeline-item::before{

            content:'';

            width:16px;

            height:16px;

            background:#f5b400;

            border-radius:50%;

            position:absolute;

            left:-34px;

            top:5px;
        }

        .timeline-item h3{

            margin-bottom:8px;
        }

        .timeline-date{

            color:#777;

            font-size:14px;

            margin-bottom:10px;
        }

        .empty{

            text-align:center;

            padding:40px;

            color:#777;
        }

        @media(max-width:768px){

            .header{

                flex-direction:column;

                align-items:flex-start;
            }

            .topbar{

                flex-direction:column;

                gap:15px;

                align-items:flex-start;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <!-- TOPBAR -->

    <div class="topbar">

        <a
            href="index.php"
            class="back-btn"
        >
            ← Back to Projects
        </a>

        <a
            href="../logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

    <!-- PROJECT DETAILS -->

    <div class="project-card">

        <div class="header">

            <h1>

                <?php
                    echo htmlspecialchars(
                        (string)$project['project_name']
                    );
                ?>

            </h1>

            <span
                class="badge <?php echo str_replace(' ', '.', htmlspecialchars((string)$project['project_status'])); ?>"
            >

                <?php
                    echo htmlspecialchars(
                        (string)$project['project_status']
                    );
                ?>

            </span>

        </div>

        <div class="details-grid">

            <div class="detail-box">

                <h3>
                    Project Type
                </h3>

                <p>

                    <?php
                        echo htmlspecialchars(
                            (string)$project['project_type']
                        );
                    ?>

                </p>

            </div>

            <div class="detail-box">

                <h3>
                    Location
                </h3>

                <p>

                    <?php
                        echo htmlspecialchars(
                            (string)$project['project_location']
                        );
                    ?>

                </p>

            </div>

            <div class="detail-box">

                <h3>
                    Plot Size
                </h3>

                <p>

                    <?php
                        echo htmlspecialchars(
                            (string)$project['project_area']
                        );
                    ?>

                </p>

            </div>

            <div class="detail-box">

                <h3>
                    Budget
                </h3>

                <p>

                    ₹<?php
                        echo number_format(
                            (float)$project['budget'],
                            2
                        );
                    ?>

                </p>

            </div>

            <div class="detail-box">

                <h3>
                    Start Date
                </h3>

                <p>

                    <?php
                        echo htmlspecialchars(
                            (string)$project['start_date']
                        );
                    ?>

                </p>

            </div>

            <div class="detail-box">

                <h3>
                    Expected Handover
                </h3>

                <p>

                    <?php
                        echo htmlspecialchars(
                            (string)$project['expected_handover']
                        );
                    ?>

                </p>

            </div>

        </div>

        <!-- DESCRIPTION -->

        <div class="description">

            <h2>
                Project Description
            </h2>

            <p>

                <?php
                    echo nl2br(
                        htmlspecialchars(
                            (string)$project['description']
                        )
                    );
                ?>

            </p>

        </div>

        <!-- PROGRESS -->

        <div class="progress-box">

            <h2>
                Construction Progress
            </h2>

            <div class="progress">

                <div
                    class="progress-fill"
                    style="width: <?php echo (int)$project['progress']; ?>%;"
                >

                    <?php
                        echo (int)$project['progress'];
                    ?>%

                </div>

            </div>

        </div>

    </div>

    <!-- PROJECT TIMELINE -->

    <div class="updates-section">

        <h2>
            Project Updates Timeline
        </h2>

        <?php if ($updates && $updates->num_rows > 0): ?>

            <div class="timeline">

                <?php while ($update = $updates->fetch_assoc()): ?>

                    <div class="timeline-item">

                        <h3>

                            <?php
                                echo htmlspecialchars(
                                    (string)$update['update_title']
                                );
                            ?>

                        </h3>

                        <div class="timeline-date">

                            <?php
                                echo htmlspecialchars(
                                    (string)$update['update_date']
                                );
                            ?>

                        </div>

                        <p>

                            <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        (string)$update['update_description']
                                    )
                                );
                            ?>

                        </p>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="empty">

                No updates available for this project.

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>