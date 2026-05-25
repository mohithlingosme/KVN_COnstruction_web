<?php

declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| CLIENT AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['client_id'])) {

    header('Location: login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once '../includes/db.php';

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

        location VARCHAR(255) NOT NULL,

        progress INT NOT NULL DEFAULT 0,

        status ENUM(
            'Planning',
            'In Progress',
            'Completed',
            'On Hold'
        )
        NOT NULL DEFAULT 'Planning',

        start_date DATE NOT NULL,

        expected_handover DATE NOT NULL,

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS client_payments (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        amount DECIMAL(12,2) NOT NULL,

        payment_type VARCHAR(255) NOT NULL,

        payment_status ENUM(
            'Paid',
            'Pending',
            'Failed'
        )
        NOT NULL DEFAULT 'Pending',

        payment_date DATE NOT NULL,

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS client_messages (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        subject VARCHAR(255) NOT NULL,

        message TEXT NOT NULL,

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

$checkProject =
    $conn->query(
        "
        SELECT id
        FROM client_projects
        WHERE client_id = $clientId
        LIMIT 1
        "
    );

if (
    $checkProject &&
    $checkProject->num_rows === 0
) {

    $conn->query(
        "
        INSERT INTO client_projects
        (

            client_id,
            project_name,
            project_type,
            location,
            progress,
            status,
            start_date,
            expected_handover

        )

        VALUES

        (
            $clientId,
            'Luxury Villa',
            'Residential',
            'Bangalore',
            65,
            'In Progress',
            '2026-01-10',
            '2026-12-20'
        ),

        (
            $clientId,
            'Farm House',
            'Premium Construction',
            'Mysore',
            100,
            'Completed',
            '2025-02-10',
            '2025-11-15'
        )
        "
    );
}

$checkPayment =
    $conn->query(
        "
        SELECT id
        FROM client_payments
        WHERE client_id = $clientId
        LIMIT 1
        "
    );

if (
    $checkPayment &&
    $checkPayment->num_rows === 0
) {

    $conn->query(
        "
        INSERT INTO client_payments
        (

            client_id,
            amount,
            payment_type,
            payment_status,
            payment_date

        )

        VALUES

        (
            $clientId,
            2500000,
            'Initial Advance',
            'Paid',
            '2026-01-15'
        ),

        (
            $clientId,
            1500000,
            'Construction Phase',
            'Pending',
            '2026-06-15'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| SEND MESSAGE
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['send_message'])
) {

    $subject =
        trim($_POST['subject'] ?? '');

    $message =
        trim($_POST['message'] ?? '');

    if (
        $subject === '' ||
        $message === ''
    ) {

        $error =
            'Please fill all fields.';
    }

    if ($error === '') {

        $stmt =
            $conn->prepare(
                "
                INSERT INTO client_messages
                (

                    client_id,
                    subject,
                    message

                )

                VALUES

                (?, ?, ?)
                "
            );

        if ($stmt) {

            $stmt->bind_param(
                'iss',
                $clientId,
                $subject,
                $message
            );

            $stmt->execute();

            $stmt->close();

            $success =
                'Message sent successfully.';
        }
    }
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
| FETCH PAYMENTS
|--------------------------------------------------------------------------
*/

$payments =
    $conn->query(
        "
        SELECT *
        FROM client_payments
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
$totalPaid = 0;

if ($projects && $projects->num_rows > 0) {

    while ($calc = $projects->fetch_assoc()) {

        $totalProjects++;

        if (
            $calc['status'] === 'Completed'
        ) {

            $completedProjects++;
        }

        if (
            $calc['status'] === 'In Progress'
        ) {

            $ongoingProjects++;
        }
    }

    $projects->data_seek(0);
}

if ($payments && $payments->num_rows > 0) {

    while ($pay = $payments->fetch_assoc()) {

        if (
            $pay['payment_status'] === 'Paid'
        ) {

            $totalPaid +=
                (float)$pay['amount'];
        }
    }

    $payments->data_seek(0);
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
        Client Dashboard
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

        .sidebar a:hover{

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

        .cards{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(250px,1fr));

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
        }

        .card h2{

            color:#111;
        }

        .section{

            background:#fff;

            padding:30px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);

            margin-bottom:35px;
        }

        .section h2{

            margin-bottom:20px;
        }

        table{

            width:100%;

            border-collapse:collapse;
        }

        thead{

            background:#f5b400;

            color:#fff;
        }

        th,
        td{

            padding:15px;

            border-bottom:1px solid #eee;

            text-align:left;
        }

        tr:hover{

            background:#fafafa;
        }

        .badge{

            padding:8px 14px;

            border-radius:30px;

            font-size:12px;

            font-weight:bold;

            display:inline-block;
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

        .Paid{

            background:#d4edda;

            color:#155724;
        }

        .Pending{

            background:#fff3cd;

            color:#856404;
        }

        .Failed{

            background:#f8d7da;

            color:#721c24;
        }

        .progress{

            width:100%;

            height:18px;

            background:#eee;

            border-radius:30px;

            overflow:hidden;
        }

        .progress-fill{

            height:100%;

            background:#28a745;

            color:#fff;

            text-align:center;

            font-size:11px;

            line-height:18px;
        }

        .form-group{

            margin-bottom:20px;
        }

        label{

            display:block;

            margin-bottom:8px;

            font-weight:bold;
        }

        input,
        textarea{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:15px;
        }

        textarea{

            min-height:120px;

            resize:vertical;
        }

        button{

            background:#f5b400;

            color:#fff;

            border:none;

            padding:14px 20px;

            border-radius:10px;

            font-size:15px;

            font-weight:bold;

            cursor:pointer;
        }

        button:hover{

            opacity:0.9;
        }

        .alert{

            padding:15px;

            border-radius:10px;

            margin-bottom:20px;

            font-weight:bold;
        }

        .success{

            background:#d4edda;

            color:#155724;
        }

        .error{

            background:#f8d7da;

            color:#721c24;
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

            table{

                display:block;

                overflow-x:auto;
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

    <a href="#">
        Dashboard
    </a>

    <a href="#projects">
        My Projects
    </a>

    <a href="#payments">
        Payments
    </a>

    <a href="#support">
        Support
    </a>

    <a href="logout.php">
        Logout
    </a>

</div>

<!-- MAIN -->

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <div class="welcome">

            <h1>
                Welcome,
                <?php
                    echo htmlspecialchars(
                        (string)$clientName
                    );
                ?>
            </h1>

            <p>
                Track your projects,
                payments and updates.
            </p>

        </div>

        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

    <!-- STATS -->

    <div class="cards">

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
                Total Paid
            </h3>

            <h2>
                ₹<?php
                    echo number_format(
                        $totalPaid,
                        2
                    );
                ?>
            </h2>

        </div>

    </div>

    <!-- PROJECTS -->

    <div
        class="section"
        id="projects"
    >

        <h2>
            My Projects
        </h2>

        <table>

            <thead>

                <tr>

                    <th>
                        Project
                    </th>

                    <th>
                        Type
                    </th>

                    <th>
                        Location
                    </th>

                    <th>
                        Progress
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Handover
                    </th>

                </tr>

            </thead>

            <tbody>

            <?php if ($projects && $projects->num_rows > 0): ?>

                <?php while ($row = $projects->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php
                                echo htmlspecialchars(
                                    (string)$row['project_name']
                                );
                            ?>
                        </td>

                        <td>
                            <?php
                                echo htmlspecialchars(
                                    (string)$row['project_type']
                                );
                            ?>
                        </td>

                        <td>
                            <?php
                                echo htmlspecialchars(
                                    (string)$row['location']
                                );
                            ?>
                        </td>

                        <td>

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

                        </td>

                        <td>

                            <span
                                class="badge <?php echo str_replace(' ', '.', htmlspecialchars((string)$row['status'])); ?>"
                            >

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['status']
                                    );
                                ?>

                            </span>

                        </td>

                        <td>
                            <?php
                                echo htmlspecialchars(
                                    (string)$row['expected_handover']
                                );
                            ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <!-- PAYMENTS -->

    <div
        class="section"
        id="payments"
    >

        <h2>
            Payment History
        </h2>

        <table>

            <thead>

                <tr>

                    <th>
                        Payment Type
                    </th>

                    <th>
                        Amount
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Payment Date
                    </th>

                </tr>

            </thead>

            <tbody>

            <?php if ($payments && $payments->num_rows > 0): ?>

                <?php while ($pay = $payments->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php
                                echo htmlspecialchars(
                                    (string)$pay['payment_type']
                                );
                            ?>
                        </td>

                        <td>
                            ₹<?php
                                echo number_format(
                                    (float)$pay['amount'],
                                    2
                                );
                            ?>
                        </td>

                        <td>

                            <span
                                class="badge <?php echo htmlspecialchars((string)$pay['payment_status']); ?>"
                            >

                                <?php
                                    echo htmlspecialchars(
                                        (string)$pay['payment_status']
                                    );
                                ?>

                            </span>

                        </td>

                        <td>
                            <?php
                                echo htmlspecialchars(
                                    (string)$pay['payment_date']
                                );
                            ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <!-- SUPPORT -->

    <div
        class="section"
        id="support"
    >

        <h2>
            Contact Support
        </h2>

        <?php if ($success !== ''): ?>

            <div class="alert success">

                <?php echo htmlspecialchars($success); ?>

            </div>

        <?php endif; ?>

        <?php if ($error !== ''): ?>

            <div class="alert error">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label>
                    Subject
                </label>

                <input
                    type="text"
                    name="subject"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Message
                </label>

                <textarea
                    name="message"
                    required
                ></textarea>

            </div>

            <button
                type="submit"
                name="send_message"
            >
                Send Message
            </button>

        </form>

    </div>

</div>

</body>

</html>