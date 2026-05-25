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
    CREATE TABLE IF NOT EXISTS client_feedback (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        project_name VARCHAR(255) NOT NULL,

        feedback_type ENUM(
            'Complaint',
            'Suggestion',
            'Appreciation',
            'Issue Report'
        )
        NOT NULL,

        subject VARCHAR(255) NOT NULL,

        message TEXT NOT NULL,

        rating INT DEFAULT NULL,

        attachment VARCHAR(255) DEFAULT NULL,

        status ENUM(
            'Pending',
            'Reviewed',
            'Resolved'
        )
        NOT NULL DEFAULT 'Pending',

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

    $projectName =
        trim($_POST['project_name'] ?? '');

    $feedbackType =
        trim($_POST['feedback_type'] ?? '');

    $subject =
        trim($_POST['subject'] ?? '');

    $message =
        trim($_POST['message'] ?? '');

    $rating =
        (int) ($_POST['rating'] ?? 0);

    if (
        empty($projectName) ||
        empty($feedbackType) ||
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
                INSERT INTO client_feedback
                (

                    client_id,
                    project_name,
                    feedback_type,
                    subject,
                    message,
                    rating

                )

                VALUES
                (?, ?, ?, ?, ?, ?)
                "
            );

        $stmt->bind_param(
            'issssi',
            $clientId,
            $projectName,
            $feedbackType,
            $subject,
            $message,
            $rating
        );

        if ($stmt->execute()) {

            $successMessage =
                'Feedback submitted successfully.';
        }
        else {

            $errorMessage =
                'Something went wrong.';
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
        FROM client_feedback
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
        INSERT INTO client_feedback
        (

            client_id,
            project_name,
            feedback_type,
            subject,
            message,
            rating,
            status,
            admin_reply

        )

        VALUES

        (
            $clientId,
            'Luxury Villa Project',
            'Appreciation',
            'Excellent Site Coordination',
            'The project team handled everything professionally.',
            5,
            'Resolved',
            'Thank you for your valuable feedback.'
        ),

        (
            $clientId,
            'Farm House Project',
            'Suggestion',
            'Need More Material Updates',
            'Please provide more frequent material updates.',
            4,
            'Reviewed',
            'We will improve update frequency.'
        ),

        (
            $clientId,
            'Commercial Complex',
            'Complaint',
            'Delay in Plumbing Work',
            'Plumbing work is getting delayed.',
            2,
            'Pending',
            NULL
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH FEEDBACK
|--------------------------------------------------------------------------
*/

$feedbacks =
    $conn->query(
        "
        SELECT *
        FROM client_feedback
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalFeedback = 0;
$pendingFeedback = 0;
$reviewedFeedback = 0;
$resolvedFeedback = 0;

if ($feedbacks && $feedbacks->num_rows > 0) {

    while ($calc = $feedbacks->fetch_assoc()) {

        $totalFeedback++;

        if (
            $calc['status']
            === 'Pending'
        ) {

            $pendingFeedback++;
        }

        if (
            $calc['status']
            === 'Reviewed'
        ) {

            $reviewedFeedback++;
        }

        if (
            $calc['status']
            === 'Resolved'
        ) {

            $resolvedFeedback++;
        }
    }

    $feedbacks->data_seek(0);
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
        Client Feedback
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

        .form-section h2{

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

        .feedback-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(360px,1fr));

            gap:25px;
        }

        .feedback-card{

            background:#fff;

            border-radius:20px;

            padding:25px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .feedback-card h3{

            margin-bottom:15px;

            color:#111827;
        }

        .feedback-card p{

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

        .Pending{

            background:#fff3cd;

            color:#856404;
        }

        .Reviewed{

            background:#d1ecf1;

            color:#0c5460;
        }

        .Resolved{

            background:#d4edda;

            color:#155724;
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

    <a href="images.php">
        Uploaded Images
    </a>

    <a href="videos.php">
        Uploaded Videos
    </a>

    <a
        href="feedback.php"
        class="active"
    >
        Feedback
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
                Client Feedback
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
                Total Feedback
            </h4>

            <h2>
                <?php echo $totalFeedback; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Pending
            </h4>

            <h2>
                <?php echo $pendingFeedback; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Reviewed
            </h4>

            <h2>
                <?php echo $reviewedFeedback; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Resolved
            </h4>

            <h2>
                <?php echo $resolvedFeedback; ?>
            </h2>

        </div>

    </div>

    <!-- FORM -->

    <div class="form-section">

        <h2>
            Submit Feedback
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
                        Project Name
                    </label>

                    <input
                        type="text"
                        name="project_name"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Feedback Type
                    </label>

                    <select
                        name="feedback_type"
                        required
                    >

                        <option value="">
                            Select Type
                        </option>

                        <option value="Complaint">
                            Complaint
                        </option>

                        <option value="Suggestion">
                            Suggestion
                        </option>

                        <option value="Appreciation">
                            Appreciation
                        </option>

                        <option value="Issue Report">
                            Issue Report
                        </option>

                    </select>

                </div>

                <div class="form-group full-width">

                    <label>
                        Subject
                    </label>

                    <input
                        type="text"
                        name="subject"
                        required
                    >

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
                        Rating (1-5)
                    </label>

                    <select name="rating">

                        <option value="1">
                            1 Star
                        </option>

                        <option value="2">
                            2 Stars
                        </option>

                        <option value="3">
                            3 Stars
                        </option>

                        <option value="4">
                            4 Stars
                        </option>

                        <option value="5">
                            5 Stars
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label>
                        Submit
                    </label>

                    <button
                        type="submit"
                        class="submit-btn"
                    >
                        Send Feedback
                    </button>

                </div>

            </div>

        </form>

    </div>

    <!-- FEEDBACK LIST -->

    <div class="feedback-grid">

        <?php if ($feedbacks && $feedbacks->num_rows > 0): ?>

            <?php while ($row = $feedbacks->fetch_assoc()): ?>

                <div class="feedback-card">

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
                                (string)$row['subject']
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
                            Type:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['feedback_type']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Rating:
                        </strong>

                        <?php
                            echo (int)$row['rating'];
                        ?>/5

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
                            Submitted:
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