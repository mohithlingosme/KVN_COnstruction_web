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
| CLIENT DETAILS
|--------------------------------------------------------------------------
*/

$clientId =
    (int) $_SESSION['client_id'];

$clientName =
    $_SESSION['client_name'] ?? 'Client';

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

/*
|--------------------------------------------------------------------------
| INSERT DEMO DATA
|--------------------------------------------------------------------------
*/

$check =
    $conn->query(
        "
        SELECT id
        FROM client_projects
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
        INSERT INTO client_projects
        (

            client_id,
            project_name,
            project_type,
            project_location,
            project_area,
            budget,
            progress,
            project_status,
            start_date,
            expected_handover,
            description

        )

        VALUES

        (
            $clientId,
            'Luxury Villa',
            'Residential Construction',
            'Bangalore',
            '40x60',
            8500000,
            65,
            'In Progress',
            '2026-01-10',
            '2026-12-20',
            'Premium turnkey villa project.'
        ),

        (
            $clientId,
            'Farm House',
            'Premium Construction',
            'Mysore',
            '50x80',
            12500000,
            100,
            'Completed',
            '2025-02-10',
            '2025-11-15',
            'Luxury farmhouse with landscaping.'
        ),

        (
            $clientId,
            'Commercial Complex',
            'Commercial Project',
            'Hyderabad',
            '80x120',
            35000000,
            25,
            'Planning',
            '2026-05-01',
            '2027-10-15',
            'Commercial office construction.'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH PROJECTS
|--------------------------------------------------------------------------
*/

$projects =
    $conn->query(
        "
        SELECT *
        FROM client_projects
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalProjects = 0;
$completedProjects = 0;
$ongoingProjects = 0;
$planningProjects = 0;
$totalBudget = 0;

if ($projects && $projects->num_rows > 0) {

    while ($calc = $projects->fetch_assoc()) {

        $totalProjects++;

        $totalBudget +=
            (float)$calc['budget'];

        if (
            $calc['project_status']
            === 'Completed'
        ) {

            $completedProjects++;
        }

        if (
            $calc['project_status']
            === 'In Progress'
        ) {

            $ongoingProjects++;
        }

        if (
            $calc['project_status']
            === 'Planning'
        ) {

            $planningProjects++;
        }
    }

    $projects->data_seek(0);
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
        My Projects
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

        .sidebar{

            width:260px;

            background:#111827;

            height:100vh;

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

            color:#fff;

            text-decoration:none;

            padding:14px 16px;

            margin-bottom:10px;

            border-radius:10px;

            transition:0.3s;
        }

        .sidebar a:hover,
        .sidebar .active{

            background:#f5b400;
        }

        .main{

            margin-left:260px;

            padding:40px;
        }

        .topbar{

            display:flex;

            justify-content:space-between;

            align-items:center;

            margin-bottom:35px;
        }

        .welcome h1{

            margin-bottom:8px;
        }

        .logout{

            background:#dc3545;

            color:#fff;

            padding:12px 18px;

            border-radius:10px;

            text-decoration:none;

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

        .card h3{

            color:#666;

            margin-bottom:10px;

            font-size:15px;
        }

        .card h2{

            color:#111;
        }

        .projects-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(350px,1fr));

            gap:25px;
        }

        .project-card{

            background:#fff;

            border-radius:20px;

            padding:25px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .project-header{

            display:flex;

            justify-content:space-between;

            align-items:center;

            margin-bottom:20px;
        }

        .project-header h2{

            font-size:22px;
        }

        .badge{

            padding:8px 14px;

            border-radius:30px;

            font-size:12px;

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

        .project-info{

            margin-bottom:15px;
        }

        .project-info strong{

            display:inline-block;

            width:150px;
        }

        .progress-box{

            margin-top:20px;
        }

        .progress{

            width:100%;

            height:22px;

            background:#eee;

            border-radius:30px;

            overflow:hidden;

            margin-top:8px;
        }

        .progress-fill{

            height:100%;

            background:#28a745;

            color:#fff;

            text-align:center;

            line-height:22px;

            font-size:12px;

            font-weight:bold;
        }

        .empty{

            background:#fff;

            padding:60px;

            border-radius:20px;

            text-align:center;

            color:#777;

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

    <a
        href="index.php"
        class="active"
    >
        My Projects
    </a>

    <a href="../dashboard.php#payments">
        Payments
    </a>

    <a href="../dashboard.php#support">
        Support
    </a>

    <a href="../logout.php">
        Logout
    </a>

</div>

<!-- MAIN -->

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <div class="welcome">

            <h1>
                My Projects
            </h1>

            <p>
                Welcome back,
                <?php
                    echo htmlspecialchars(
                        (string)$clientName
                    );
                ?>
            </p>

        </div>

        <a
            href="../logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

    <!-- STATS -->

    <div class="stats">

        <div class="card">

            <h3>
                Total Projects
            </h3>

            <h2>
                <?php echo $totalProjects; ?>
            </h2>

        </div>

        <div class="card">

            <h3>
                Ongoing Projects
            </h3>

            <h2>
                <?php echo $ongoingProjects; ?>
            </h2>

        </div>

        <div class="card">

            <h3>
                Completed Projects
            </h3>

            <h2>
                <?php echo $completedProjects; ?>
            </h2>

        </div>

        <div class="card">

            <h3>
                Planning Stage
            </h3>

            <h2>
                <?php echo $planningProjects; ?>
            </h2>

        </div>

        <div class="card">

            <h3>
                Total Project Value
            </h3>

            <h2>
                ₹<?php
                    echo number_format(
                        $totalBudget,
                        2
                    );
                ?>
            </h2>

        </div>

    </div>

    <!-- PROJECT CARDS -->

    <?php if ($projects && $projects->num_rows > 0): ?>

        <div class="projects-grid">

            <?php while ($row = $projects->fetch_assoc()): ?>

                <div class="project-card">

                    <div class="project-header">

                        <h2>

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['project_name']
                                );
                            ?>

                        </h2>

                        <span
                            class="badge <?php echo str_replace(' ', '.', htmlspecialchars((string)$row['project_status'])); ?>"
                        >

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['project_status']
                                );
                            ?>

                        </span>

                    </div>

                    <div class="project-info">

                        <strong>
                            Project Type:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['project_type']
                            );
                        ?>

                    </div>

                    <div class="project-info">

                        <strong>
                            Location:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['project_location']
                            );
                        ?>

                    </div>

                    <div class="project-info">

                        <strong>
                            Plot Size:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['project_area']
                            );
                        ?>

                    </div>

                    <div class="project-info">

                        <strong>
                            Budget:
                        </strong>

                        ₹<?php
                            echo number_format(
                                (float)$row['budget'],
                                2
                            );
                        ?>

                    </div>

                    <div class="project-info">

                        <strong>
                            Start Date:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['start_date']
                            );
                        ?>

                    </div>

                    <div class="project-info">

                        <strong>
                            Expected Handover:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['expected_handover']
                            );
                        ?>

                    </div>

                    <div class="project-info">

                        <strong>
                            Description:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['description']
                            );
                        ?>

                    </div>

                    <div class="progress-box">

                        <strong>
                            Construction Progress
                        </strong>

                        <div class="progress">

                            <div
                                class="progress-fill"
                                style="width: <?php echo (int)$row['progress']; ?>%;"
                            >

                                <?php
                                    echo (int)$row['progress'];
                                ?>%

                            </div>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            <h2>
                No Projects Found
            </h2>

            <p>
                Your construction projects
                will appear here.
            </p>

        </div>

    <?php endif; ?>

</div>

</body>

</html>