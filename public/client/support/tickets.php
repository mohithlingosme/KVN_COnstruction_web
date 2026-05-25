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
    CREATE TABLE IF NOT EXISTS support_tickets (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        ticket_number VARCHAR(50) NOT NULL UNIQUE,

        subject VARCHAR(255) NOT NULL,

        department ENUM(
            'Technical',
            'Billing',
            'Project',
            'Sales',
            'General'
        )
        NOT NULL,

        priority ENUM(
            'Low',
            'Medium',
            'High',
            'Urgent'
        )
        NOT NULL DEFAULT 'Medium',

        message TEXT NOT NULL,

        attachment VARCHAR(255) DEFAULT NULL,

        status ENUM(
            'Open',
            'In Progress',
            'Resolved',
            'Closed'
        )
        NOT NULL DEFAULT 'Open',

        admin_reply TEXT DEFAULT NULL,

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

$successMessage = '';
$errorMessage = '';

if (
    $_SERVER['REQUEST_METHOD']
    === 'POST'
) {

    $subject =
        trim($_POST['subject'] ?? '');

    $department =
        trim($_POST['department'] ?? '');

    $priority =
        trim($_POST['priority'] ?? '');

    $message =
        trim($_POST['message'] ?? '');

    if (
        empty($subject) ||
        empty($department) ||
        empty($priority) ||
        empty($message)
    ) {

        $errorMessage =
            'Please fill all required fields.';
    }
    else {

        $ticketNumber =
            'KVN-' .
            strtoupper(
                substr(
                    md5(
                        uniqid(
                            (string) rand(),
                            true
                        )
                    ),
                    0,
                    8
                )
            );

        $stmt =
            $conn->prepare(
                "
                INSERT INTO support_tickets
                (

                    client_id,
                    ticket_number,
                    subject,
                    department,
                    priority,
                    message

                )

                VALUES
                (?, ?, ?, ?, ?, ?)
                "
            );

        $stmt->bind_param(
            'isssss',
            $clientId,
            $ticketNumber,
            $subject,
            $department,
            $priority,
            $message
        );

        if ($stmt->execute()) {

            $successMessage =
                'Support ticket created successfully.';
        }
        else {

            $errorMessage =
                'Unable to create ticket.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| INSERT DEMO DATA
|--------------------------------------------------------------------------
*/

$check =
    $conn->query(
        "
        SELECT id
        FROM support_tickets
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
        INSERT INTO support_tickets
        (

            client_id,
            ticket_number,
            subject,
            department,
            priority,
            message,
            status,
            admin_reply

        )

        VALUES

        (
            $clientId,
            'KVN-A1B2C3D4',
            'Need Updated Construction Timeline',
            'Project',
            'High',
            'Please provide the updated project completion timeline.',
            'In Progress',
            'Project manager will update the timeline shortly.'
        ),

        (
            $clientId,
            'KVN-X8Y7Z6P5',
            'Invoice Payment Clarification',
            'Billing',
            'Medium',
            'Need clarification regarding latest payment invoice.',
            'Resolved',
            'Billing details shared via email.'
        ),

        (
            $clientId,
            'KVN-Q2W3E4R5',
            'Unable to Download Documents',
            'Technical',
            'Urgent',
            'Document download button is not working.',
            'Open',
            NULL
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH TICKETS
|--------------------------------------------------------------------------
*/

$tickets =
    $conn->query(
        "
        SELECT *
        FROM support_tickets
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalTickets = 0;
$openTickets = 0;
$progressTickets = 0;
$resolvedTickets = 0;

if (
    $tickets &&
    $tickets->num_rows > 0
) {

    while (
        $calc =
        $tickets->fetch_assoc()
    ) {

        $totalTickets++;

        if (
            $calc['status']
            === 'Open'
        ) {

            $openTickets++;
        }

        if (
            $calc['status']
            === 'In Progress'
        ) {

            $progressTickets++;
        }

        if (
            $calc['status']
            === 'Resolved'
        ) {

            $resolvedTickets++;
        }
    }

    $tickets->data_seek(0);
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
        Support Tickets
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

        .form-section{

            background:#fff;

            padding:30px;

            border-radius:20px;

            margin-bottom:35px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .form-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(280px,1fr));

            gap:20px;
        }

        .form-group{

            display:flex;

            flex-direction:column;
        }

        .form-group label{

            margin-bottom:8px;

            font-weight:bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea{

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:15px;
        }

        textarea{

            min-height:130px;

            resize:vertical;
        }

        .full-width{

            grid-column:1 / -1;
        }

        .submit-btn{

            background:#111827;

            color:#fff;

            border:none;

            padding:15px 25px;

            border-radius:10px;

            font-size:16px;

            font-weight:bold;

            cursor:pointer;
        }

        .success{

            background:#d4edda;

            color:#155724;

            padding:15px;

            border-radius:10px;

            margin-bottom:20px;
        }

        .error{

            background:#f8d7da;

            color:#721c24;

            padding:15px;

            border-radius:10px;

            margin-bottom:20px;
        }

        .ticket-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(360px,1fr));

            gap:25px;
        }

        .ticket-card{

            background:#fff;

            border-radius:20px;

            padding:25px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .ticket-card h3{

            margin-bottom:15px;

            color:#111827;
        }

        .ticket-card p{

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

        .Open{

            background:#fff3cd;

            color:#856404;
        }

        .In\ Progress{

            background:#d1ecf1;

            color:#0c5460;
        }

        .Resolved{

            background:#d4edda;

            color:#155724;
        }

        .Closed{

            background:#e2e3e5;

            color:#383d41;
        }

        .priority{

            padding:6px 12px;

            border-radius:20px;

            font-size:12px;

            font-weight:bold;

            margin-left:10px;
        }

        .Low{

            background:#d4edda;

            color:#155724;
        }

        .Medium{

            background:#fff3cd;

            color:#856404;
        }

        .High{

            background:#f8d7da;

            color:#721c24;
        }

        .Urgent{

            background:#111827;

            color:#fff;
        }

        .reply-box{

            margin-top:18px;

            background:#f8f9fa;

            padding:15px;

            border-radius:10px;
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
        href="tickets.php"
        class="active"
    >
        Support Tickets
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
                Support Tickets
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
                Total Tickets
            </h4>

            <h2>
                <?php echo $totalTickets; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Open
            </h4>

            <h2>
                <?php echo $openTickets; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                In Progress
            </h4>

            <h2>
                <?php echo $progressTickets; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Resolved
            </h4>

            <h2>
                <?php echo $resolvedTickets; ?>
            </h2>

        </div>

    </div>

    <!-- CREATE TICKET -->

    <div class="form-section">

        <h2 style="margin-bottom:25px;">
            Create Support Ticket
        </h2>

        <?php if (!empty($successMessage)): ?>

            <div class="success">
                <?php echo $successMessage; ?>
            </div>

        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>

            <div class="error">
                <?php echo $errorMessage; ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-grid">

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
                        Department
                    </label>

                    <select
                        name="department"
                        required
                    >

                        <option value="">
                            Select Department
                        </option>

                        <option value="Technical">
                            Technical
                        </option>

                        <option value="Billing">
                            Billing
                        </option>

                        <option value="Project">
                            Project
                        </option>

                        <option value="Sales">
                            Sales
                        </option>

                        <option value="General">
                            General
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label>
                        Priority
                    </label>

                    <select
                        name="priority"
                        required
                    >

                        <option value="Low">
                            Low
                        </option>

                        <option value="Medium">
                            Medium
                        </option>

                        <option value="High">
                            High
                        </option>

                        <option value="Urgent">
                            Urgent
                        </option>

                    </select>

                </div>

                <div class="form-group full-width">

                    <label>
                        Message
                    </label>

                    <textarea
                        name="message"
                        required
                    ></textarea>

                </div>

                <div class="form-group">

                    <label>
                        Submit
                    </label>

                    <button
                        type="submit"
                        class="submit-btn"
                    >
                        Create Ticket
                    </button>

                </div>

            </div>

        </form>

    </div>

    <!-- TICKETS -->

    <div class="ticket-grid">

        <?php if ($tickets && $tickets->num_rows > 0): ?>

            <?php while ($row = $tickets->fetch_assoc()): ?>

                <div class="ticket-card">

                    <span
                        class="badge <?php echo str_replace(' ', '-', htmlspecialchars((string)$row['status'])); ?>"
                    >

                        <?php
                            echo htmlspecialchars(
                                (string)$row['status']
                            );
                        ?>

                    </span>

                    <span
                        class="priority <?php echo htmlspecialchars((string)$row['priority']); ?>"
                    >

                        <?php
                            echo htmlspecialchars(
                                (string)$row['priority']
                            );
                        ?>

                    </span>

                    <h3>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['subject']
                            );
                        ?>

                    </h3>

                    <p>

                        <strong>
                            Ticket ID:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['ticket_number']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Department:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['department']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Message:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['message']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Created:
                        </strong>

                        <?php
                            echo date(
                                'd M Y h:i A',
                                strtotime(
                                    (string)$row['created_at']
                                )
                            );
                        ?>

                    </p>

                    <?php if (!empty($row['admin_reply'])): ?>

                        <div class="reply-box">

                            <strong>
                                Admin Reply:
                            </strong>

                            <p>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['admin_reply']
                                    );
                                ?>

                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            <?php endwhile; ?>

        <?php endif; ?>

    </div>

</div>

</body>

</html>