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
| CREATE TABLES
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS support_messages (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        sender_type ENUM(
            'Client',
            'Admin'
        )
        NOT NULL,

        sender_name VARCHAR(255) NOT NULL,

        subject VARCHAR(255) NOT NULL,

        message TEXT NOT NULL,

        is_read ENUM(
            'Yes',
            'No'
        )
        NOT NULL DEFAULT 'No',

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| HANDLE NEW MESSAGE
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

    $message =
        trim($_POST['message'] ?? '');

    if (
        empty($subject) ||
        empty($message)
    ) {

        $errorMessage =
            'Please fill all required fields.';
    }
    else {

        $stmt =
            $conn->prepare(
                "
                INSERT INTO support_messages
                (

                    client_id,
                    sender_type,
                    sender_name,
                    subject,
                    message

                )

                VALUES
                (?, 'Client', ?, ?, ?)
                "
            );

        $stmt->bind_param(
            'isss',
            $clientId,
            $clientName,
            $subject,
            $message
        );

        if ($stmt->execute()) {

            $successMessage =
                'Message sent successfully.';
        }
        else {

            $errorMessage =
                'Unable to send message.';
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
        FROM support_messages
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
        INSERT INTO support_messages
        (

            client_id,
            sender_type,
            sender_name,
            subject,
            message,
            is_read

        )

        VALUES

        (
            $clientId,
            'Admin',
            'KVN Support Team',
            'Project Timeline Update',
            'Your project timeline has been updated successfully.',
            'Yes'
        ),

        (
            $clientId,
            'Client',
            '$clientName',
            'Need Billing Clarification',
            'Please clarify the latest invoice details.',
            'Yes'
        ),

        (
            $clientId,
            'Admin',
            'KVN Accounts Team',
            'Invoice Clarification',
            'The invoice breakdown has been mailed to your registered email.',
            'No'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH MESSAGES
|--------------------------------------------------------------------------
*/

$messages =
    $conn->query(
        "
        SELECT *
        FROM support_messages
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalMessages = 0;
$unreadMessages = 0;
$adminMessages = 0;
$clientMessages = 0;

if (
    $messages &&
    $messages->num_rows > 0
) {

    while (
        $calc =
        $messages->fetch_assoc()
    ) {

        $totalMessages++;

        if (
            $calc['is_read']
            === 'No'
        ) {

            $unreadMessages++;
        }

        if (
            $calc['sender_type']
            === 'Admin'
        ) {

            $adminMessages++;
        }

        if (
            $calc['sender_type']
            === 'Client'
        ) {

            $clientMessages++;
        }
    }

    $messages->data_seek(0);
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
        Support Messages
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

        .message-form{

            background:#fff;

            padding:30px;

            border-radius:20px;

            margin-bottom:35px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .message-form h2{

            margin-bottom:25px;
        }

        .form-group{

            margin-bottom:20px;
        }

        .form-group label{

            display:block;

            margin-bottom:8px;

            font-weight:bold;
        }

        .form-group input,
        .form-group textarea{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:15px;
        }

        textarea{

            min-height:140px;

            resize:vertical;
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

        .messages-list{

            display:flex;

            flex-direction:column;

            gap:20px;
        }

        .message-card{

            background:#fff;

            padding:25px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .message-top{

            display:flex;

            justify-content:space-between;

            align-items:center;

            flex-wrap:wrap;

            gap:10px;

            margin-bottom:15px;
        }

        .badge{

            padding:8px 14px;

            border-radius:30px;

            font-size:12px;

            font-weight:bold;
        }

        .Admin{

            background:#d1ecf1;

            color:#0c5460;
        }

        .Client{

            background:#d4edda;

            color:#155724;
        }

        .Unread{

            background:#f8d7da;

            color:#721c24;
        }

        .Read{

            background:#d4edda;

            color:#155724;
        }

        .message-card h3{

            margin-bottom:12px;

            color:#111827;
        }

        .message-card p{

            margin-bottom:10px;

            line-height:1.7;

            color:#555;
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

    <a href="tickets.php">
        Support Tickets
    </a>

    <a
        href="messages.php"
        class="active"
    >
        Messages
    </a>

    <a href="create-ticket.php">
        Create Ticket
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
                Support Messages
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
                Total Messages
            </h4>

            <h2>
                <?php echo $totalMessages; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Unread
            </h4>

            <h2>
                <?php echo $unreadMessages; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Admin Messages
            </h4>

            <h2>
                <?php echo $adminMessages; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Your Messages
            </h4>

            <h2>
                <?php echo $clientMessages; ?>
            </h2>

        </div>

    </div>

    <!-- SEND MESSAGE -->

    <div class="message-form">

        <h2>
            Send New Message
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

            <div class="form-group">

                <label>
                    Subject
                </label>

                <input
                    type="text"
                    name="subject"
                    placeholder="Enter message subject"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Message
                </label>

                <textarea
                    name="message"
                    placeholder="Write your message..."
                    required
                ></textarea>

            </div>

            <button
                type="submit"
                class="submit-btn"
            >
                Send Message
            </button>

        </form>

    </div>

    <!-- MESSAGES -->

    <div class="messages-list">

        <?php if ($messages && $messages->num_rows > 0): ?>

            <?php while ($row = $messages->fetch_assoc()): ?>

                <div class="message-card">

                    <div class="message-top">

                        <span
                            class="badge <?php echo htmlspecialchars((string)$row['sender_type']); ?>"
                        >

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['sender_type']
                                );
                            ?>

                        </span>

                        <span
                            class="badge <?php echo $row['is_read'] === 'Yes' ? 'Read' : 'Unread'; ?>"
                        >

                            <?php
                                echo $row['is_read'] === 'Yes'
                                    ? 'Read'
                                    : 'Unread';
                            ?>

                        </span>

                    </div>

                    <h3>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['subject']
                            );
                        ?>

                    </h3>

                    <p>

                        <strong>
                            Sender:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['sender_name']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Message:
                        </strong>

                        <?php
                            echo nl2br(
                                htmlspecialchars(
                                    (string)$row['message']
                                )
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Date:
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

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="message-card">

                <h3>
                    No Messages Found
                </h3>

                <p>
                    You currently have no support messages.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>