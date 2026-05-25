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
| DATABASE
|--------------------------------------------------------------------------
*/

require_once '../../includes/db.php';

/*
|--------------------------------------------------------------------------
| CREATE TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS project_schedules (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        project_name VARCHAR(255) NOT NULL,

        activity_name VARCHAR(255) NOT NULL,

        description TEXT DEFAULT NULL,

        assigned_team VARCHAR(255) DEFAULT NULL,

        schedule_date DATE NOT NULL,

        start_time TIME DEFAULT NULL,

        end_time TIME DEFAULT NULL,

        status ENUM(
            'Upcoming',
            'Ongoing',
            'Completed',
            'Cancelled'
        )
        NOT NULL DEFAULT 'Upcoming',

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

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
| INSERT DEMO DATA
|--------------------------------------------------------------------------
*/

$check =
    $conn->query(
        "
        SELECT id
        FROM project_schedules
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
        INSERT INTO project_schedules
        (

            client_id,
            project_name,
            activity_name,
            description,
            assigned_team,
            schedule_date,
            start_time,
            end_time,
            status

        )

        VALUES

        (
            $clientId,
            'Luxury Villa Construction',
            'Foundation Inspection',
            'Inspection of foundation and structural alignment.',
            'Structural Team',
            '2026-05-25',
            '09:00:00',
            '11:00:00',
            'Completed'
        ),

        (
            $clientId,
            'Luxury Villa Construction',
            'Electrical Wiring',
            'Internal electrical wiring setup for ground floor.',
            'Electrical Team',
            '2026-05-28',
            '10:00:00',
            '05:00:00',
            'Ongoing'
        ),

        (
            $clientId,
            'Luxury Villa Construction',
            'Interior Design Meeting',
            'Discussion regarding modular kitchen and living room design.',
            'Interior Team',
            '2026-06-02',
            '02:00:00',
            '04:00:00',
            'Upcoming'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH SCHEDULES
|--------------------------------------------------------------------------
*/

$schedules =
    $conn->query(
        "
        SELECT *
        FROM project_schedules
        WHERE client_id = $clientId
        ORDER BY schedule_date ASC
        "
    );

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalSchedules = 0;
$upcomingSchedules = 0;
$completedSchedules = 0;
$ongoingSchedules = 0;

if (
    $schedules &&
    $schedules->num_rows > 0
) {

    while (
        $calc =
        $schedules->fetch_assoc()
    ) {

        $totalSchedules++;

        if (
            $calc['status']
            === 'Upcoming'
        ) {

            $upcomingSchedules++;
        }

        if (
            $calc['status']
            === 'Completed'
        ) {

            $completedSchedules++;
        }

        if (
            $calc['status']
            === 'Ongoing'
        ) {

            $ongoingSchedules++;
        }
    }

    $schedules->data_seek(0);
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
        Project Schedules
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

        .schedule-list{

            display:flex;

            flex-direction:column;

            gap:20px;
        }

        .schedule-card{

            background:#fff;

            padding:25px;

            border-radius:18px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .schedule-top{

            display:flex;

            justify-content:space-between;

            align-items:center;

            flex-wrap:wrap;

            gap:10px;

            margin-bottom:18px;
        }

        .badge{

            padding:8px 14px;

            border-radius:30px;

            font-size:12px;

            font-weight:bold;
        }

        .Upcoming{

            background:#fff3cd;

            color:#856404;
        }

        .Ongoing{

            background:#d1ecf1;

            color:#0c5460;
        }

        .Completed{

            background:#d4edda;

            color:#155724;
        }

        .Cancelled{

            background:#f8d7da;

            color:#721c24;
        }

        .schedule-card h3{

            margin-bottom:15px;

            color:#111827;
        }

        .schedule-card p{

            margin-bottom:10px;

            line-height:1.7;

            color:#555;
        }

        .empty-box{

            background:#fff;

            padding:40px;

            border-radius:18px;

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
        Timeline
    </a>

    <a
        href="schedules.php"
        class="active"
    >
        Schedules
    </a>

    <a href="../projects/index.php">
        Projects
    </a>

    <a href="../payments/index.php">
        Payments
    </a>

    <a href="../support/tickets.php">
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

        <div>

            <h1>
                Project Schedules
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
                Total Schedules
            </h4>

            <h2>
                <?php echo $totalSchedules; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Upcoming
            </h4>

            <h2>
                <?php echo $upcomingSchedules; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Ongoing
            </h4>

            <h2>
                <?php echo $ongoingSchedules; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Completed
            </h4>

            <h2>
                <?php echo $completedSchedules; ?>
            </h2>

        </div>

    </div>

    <!-- SCHEDULES -->

    <div class="schedule-list">

        <?php if ($schedules && $schedules->num_rows > 0): ?>

            <?php while ($row = $schedules->fetch_assoc()): ?>

                <div class="schedule-card">

                    <div class="schedule-top">

                        <h3>

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['activity_name']
                                );
                            ?>

                        </h3>

                        <span
                            class="badge <?php echo htmlspecialchars((string)$row['status']); ?>"
                        >

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['status']
                                );
                            ?>

                        </span>

                    </div>

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
                            Description:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['description']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Assigned Team:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['assigned_team']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Schedule Date:
                        </strong>

                        <?php
                            echo date(
                                'd M Y',
                                strtotime(
                                    (string)$row['schedule_date']
                                )
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Time:
                        </strong>

                        <?php
                            echo date(
                                'h:i A',
                                strtotime(
                                    (string)$row['start_time']
                                )
                            );
                        ?>

                        -

                        <?php
                            echo date(
                                'h:i A',
                                strtotime(
                                    (string)$row['end_time']
                                )
                            );
                        ?>

                    </p>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty-box">

                <h2>
                    No Schedules Found
                </h2>

                <p>
                    There are currently no schedules available.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>