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
| VALIDATE PROJECT ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['project_id'])) {

    header('Location: index.php');
    exit();
}

$projectId =
    (int) $_GET['project_id'];

$clientId =
    (int) $_SESSION['client_id'];

/*
|--------------------------------------------------------------------------
| CREATE PROJECT TABLE
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

        progress INT NOT NULL DEFAULT 0,

        project_status ENUM(
            'Planning',
            'In Progress',
            'Completed',
            'On Hold'
        )
        NOT NULL DEFAULT 'Planning',

        expected_handover DATE NOT NULL,

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| CREATE MILESTONES TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS project_milestones (

        id INT AUTO_INCREMENT PRIMARY KEY,

        project_id INT NOT NULL,

        milestone_title VARCHAR(255) NOT NULL,

        milestone_description TEXT DEFAULT NULL,

        milestone_status ENUM(
            'Pending',
            'In Progress',
            'Completed'
        )
        NOT NULL DEFAULT 'Pending',

        completion_percentage INT NOT NULL DEFAULT 0,

        start_date DATE DEFAULT NULL,

        expected_date DATE DEFAULT NULL,

        completed_date DATE DEFAULT NULL,

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
| INSERT DEMO MILESTONES
|--------------------------------------------------------------------------
*/

$checkMilestones =
    $conn->query(
        "
        SELECT id
        FROM project_milestones
        WHERE project_id = $projectId
        LIMIT 1
        "
    );

if (
    $checkMilestones &&
    $checkMilestones->num_rows === 0
) {

    $conn->query(
        "
        INSERT INTO project_milestones
        (

            project_id,
            milestone_title,
            milestone_description,
            milestone_status,
            completion_percentage,
            start_date,
            expected_date,
            completed_date

        )

        VALUES

        (
            $projectId,
            'Planning & Approval',
            'Architectural planning and approvals completed.',
            'Completed',
            100,
            '2026-01-01',
            '2026-01-20',
            '2026-01-18'
        ),

        (
            $projectId,
            'Foundation Work',
            'Excavation and foundation work.',
            'Completed',
            100,
            '2026-01-21',
            '2026-02-28',
            '2026-02-25'
        ),

        (
            $projectId,
            'Structural Construction',
            'Pillars, slab and block work.',
            'In Progress',
            65,
            '2026-03-01',
            '2026-06-30',
            NULL
        ),

        (
            $projectId,
            'Electrical & Plumbing',
            'Internal electrical and plumbing works.',
            'Pending',
            0,
            '2026-07-01',
            '2026-08-15',
            NULL
        ),

        (
            $projectId,
            'Interior & Finishing',
            'Tiles, painting and interiors.',
            'Pending',
            0,
            '2026-08-16',
            '2026-10-20',
            NULL
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH MILESTONES
|--------------------------------------------------------------------------
*/

$milestones =
    $conn->query(
        "
        SELECT *
        FROM project_milestones
        WHERE project_id = $projectId
        ORDER BY id ASC
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
        Project Milestones
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

            flex-wrap:wrap;

            gap:15px;
        }

        .btn{

            text-decoration:none;

            padding:12px 18px;

            border-radius:10px;

            font-weight:bold;

            color:#fff;
        }

        .back-btn{

            background:#111827;
        }

        .logout-btn{

            background:#dc3545;
        }

        .project-header{

            background:#fff;

            padding:35px;

            border-radius:20px;

            margin-bottom:35px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .project-header h1{

            margin-bottom:12px;

            font-size:34px;
        }

        .project-meta{

            display:flex;

            flex-wrap:wrap;

            gap:20px;

            margin-top:20px;
        }

        .meta-box{

            background:#f9fafb;

            padding:18px;

            border-radius:12px;

            min-width:220px;
        }

        .meta-box h4{

            color:#666;

            margin-bottom:8px;
        }

        .meta-box p{

            font-weight:bold;

            font-size:18px;
        }

        .milestone-wrapper{

            background:#fff;

            border-radius:20px;

            padding:35px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .milestone-wrapper h2{

            margin-bottom:30px;
        }

        .timeline{

            position:relative;

            margin-left:20px;

            border-left:4px solid #f5b400;

            padding-left:35px;
        }

        .milestone{

            position:relative;

            margin-bottom:40px;
        }

        .milestone::before{

            content:'';

            width:20px;

            height:20px;

            background:#f5b400;

            border-radius:50%;

            position:absolute;

            left:-47px;

            top:5px;
        }

        .milestone-card{

            background:#fafafa;

            border-radius:16px;

            padding:25px;
        }

        .milestone-top{

            display:flex;

            justify-content:space-between;

            align-items:center;

            flex-wrap:wrap;

            gap:15px;

            margin-bottom:18px;
        }

        .milestone-top h3{

            font-size:24px;
        }

        .badge{

            padding:8px 16px;

            border-radius:30px;

            font-size:12px;

            font-weight:bold;
        }

        .Completed{

            background:#d4edda;

            color:#155724;
        }

        .Pending{

            background:#f8d7da;

            color:#721c24;
        }

        .In.Progress{

            background:#fff3cd;

            color:#856404;
        }

        .milestone-info{

            margin-bottom:10px;

            color:#444;
        }

        .progress{

            width:100%;

            height:24px;

            background:#e5e7eb;

            border-radius:30px;

            overflow:hidden;

            margin-top:12px;
        }

        .progress-fill{

            height:100%;

            background:#28a745;

            color:#fff;

            font-size:12px;

            font-weight:bold;

            text-align:center;

            line-height:24px;
        }

        .empty{

            text-align:center;

            padding:50px;

            color:#777;
        }

        @media(max-width:768px){

            .timeline{

                margin-left:10px;

                padding-left:25px;
            }

            .milestone::before{

                left:-37px;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <!-- TOPBAR -->

    <div class="topbar">

        <a
            href="view.php?id=<?php echo $projectId; ?>"
            class="btn back-btn"
        >
            ← Back to Project
        </a>

        <a
            href="../logout.php"
            class="btn logout-btn"
        >
            Logout
        </a>

    </div>

    <!-- PROJECT HEADER -->

    <div class="project-header">

        <h1>

            <?php
                echo htmlspecialchars(
                    (string)$project['project_name']
                );
            ?>

        </h1>

        <p>
            Detailed milestone tracking and construction phases.
        </p>

        <div class="project-meta">

            <div class="meta-box">

                <h4>
                    Project Type
                </h4>

                <p>

                    <?php
                        echo htmlspecialchars(
                            (string)$project['project_type']
                        );
                    ?>

                </p>

            </div>

            <div class="meta-box">

                <h4>
                    Location
                </h4>

                <p>

                    <?php
                        echo htmlspecialchars(
                            (string)$project['project_location']
                        );
                    ?>

                </p>

            </div>

            <div class="meta-box">

                <h4>
                    Overall Progress
                </h4>

                <p>

                    <?php
                        echo (int)$project['progress'];
                    ?>%

                </p>

            </div>

            <div class="meta-box">

                <h4>
                    Expected Handover
                </h4>

                <p>

                    <?php
                        echo htmlspecialchars(
                            (string)$project['expected_handover']
                        );
                    ?>

                </p>

            </div>

        </div>

    </div>

    <!-- MILESTONES -->

    <div class="milestone-wrapper">

        <h2>
            Project Milestones
        </h2>

        <?php if ($milestones && $milestones->num_rows > 0): ?>

            <div class="timeline">

                <?php while ($row = $milestones->fetch_assoc()): ?>

                    <div class="milestone">

                        <div class="milestone-card">

                            <div class="milestone-top">

                                <h3>

                                    <?php
                                        echo htmlspecialchars(
                                            (string)$row['milestone_title']
                                        );
                                    ?>

                                </h3>

                                <span
                                    class="badge <?php echo str_replace(' ', '.', htmlspecialchars((string)$row['milestone_status'])); ?>"
                                >

                                    <?php
                                        echo htmlspecialchars(
                                            (string)$row['milestone_status']
                                        );
                                    ?>

                                </span>

                            </div>

                            <div class="milestone-info">

                                <strong>Description:</strong>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['milestone_description']
                                    );
                                ?>

                            </div>

                            <div class="milestone-info">

                                <strong>Start Date:</strong>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['start_date']
                                    );
                                ?>

                            </div>

                            <div class="milestone-info">

                                <strong>Expected Completion:</strong>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['expected_date']
                                    );
                                ?>

                            </div>

                            <?php if (!empty($row['completed_date'])): ?>

                                <div class="milestone-info">

                                    <strong>Completed On:</strong>

                                    <?php
                                        echo htmlspecialchars(
                                            (string)$row['completed_date']
                                        );
                                    ?>

                                </div>

                            <?php endif; ?>

                            <div class="progress">

                                <div
                                    class="progress-fill"
                                    style="width: <?php echo (int)$row['completion_percentage']; ?>%;"
                                >

                                    <?php
                                        echo (int)$row['completion_percentage'];
                                    ?>%

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="empty">

                No milestones available.

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>