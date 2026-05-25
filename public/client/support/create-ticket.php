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

        category ENUM(
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
| FILE UPLOAD SETUP
|--------------------------------------------------------------------------
*/

$uploadDir =
    '../../uploads/support/';

if (!is_dir($uploadDir)) {

    mkdir(
        $uploadDir,
        0777,
        true
    );
}

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

    $category =
        trim($_POST['category'] ?? '');

    $priority =
        trim($_POST['priority'] ?? '');

    $message =
        trim($_POST['message'] ?? '');

    $attachmentName = null;

    /*
    |--------------------------------------------------------------------------
    | FILE UPLOAD
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['attachment']) &&
        $_FILES['attachment']['error'] === 0
    ) {

        $fileName =
            time() .
            '_' .
            basename(
                $_FILES['attachment']['name']
            );

        $targetPath =
            $uploadDir .
            $fileName;

        move_uploaded_file(
            $_FILES['attachment']['tmp_name'],
            $targetPath
        );

        $attachmentName =
            $fileName;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($subject) ||
        empty($category) ||
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
                    category,
                    priority,
                    message,
                    attachment

                )

                VALUES
                (?, ?, ?, ?, ?, ?, ?)
                "
            );

        $stmt->bind_param(
            'issssss',
            $clientId,
            $ticketNumber,
            $subject,
            $category,
            $priority,
            $message,
            $attachmentName
        );

        if ($stmt->execute()) {

            $successMessage =
                'Support ticket created successfully.';
        }
        else {

            $errorMessage =
                'Failed to create support ticket.';
        }
    }
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
        Create Support Ticket
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

        .ticket-form{

            background:#fff;

            padding:35px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);

            max-width:900px;
        }

        .ticket-form h2{

            margin-bottom:25px;
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

            min-height:160px;

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

            transition:0.3s;
        }

        .submit-btn:hover{

            opacity:0.9;
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

        .info-box{

            background:#fff3cd;

            color:#856404;

            padding:18px;

            border-radius:12px;

            margin-bottom:25px;

            line-height:1.7;
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
        href="create-ticket.php"
        class="active"
    >
        Create Ticket
    </a>

    <a href="tickets.php">
        My Tickets
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
                Create Support Ticket
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

    <!-- INFO -->

    <div class="info-box">

        Create a support ticket for technical,
        billing, project, or general queries.
        Our support team will respond as soon
        as possible.

    </div>

    <!-- FORM -->

    <div class="ticket-form">

        <h2>
            Submit Ticket
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

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-grid">

                <div class="form-group">

                    <label>
                        Subject
                    </label>

                    <input
                        type="text"
                        name="subject"
                        placeholder="Enter ticket subject"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Category
                    </label>

                    <select
                        name="category"
                        required
                    >

                        <option value="">
                            Select Category
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

                <div class="form-group">

                    <label>
                        Attachment
                    </label>

                    <input
                        type="file"
                        name="attachment"
                    >

                </div>

                <div class="form-group full-width">

                    <label>
                        Detailed Message
                    </label>

                    <textarea
                        name="message"
                        placeholder="Describe your issue in detail..."
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

</div>

</body>

</html>