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
    CREATE TABLE IF NOT EXISTS client_testimonials (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        client_full_name VARCHAR(255) NOT NULL,

        company_name VARCHAR(255) DEFAULT NULL,

        project_name VARCHAR(255) NOT NULL,

        testimonial_title VARCHAR(255) NOT NULL,

        testimonial_message TEXT NOT NULL,

        rating INT NOT NULL DEFAULT 5,

        client_image VARCHAR(255) DEFAULT NULL,

        video_testimonial VARCHAR(255) DEFAULT NULL,

        featured ENUM(
            'Yes',
            'No'
        )
        NOT NULL DEFAULT 'No',

        status ENUM(
            'Pending',
            'Approved',
            'Rejected'
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

    $fullName =
        trim($_POST['client_full_name'] ?? '');

    $companyName =
        trim($_POST['company_name'] ?? '');

    $projectName =
        trim($_POST['project_name'] ?? '');

    $testimonialTitle =
        trim($_POST['testimonial_title'] ?? '');

    $testimonialMessage =
        trim($_POST['testimonial_message'] ?? '');

    $rating =
        (int) ($_POST['rating'] ?? 5);

    if (
        empty($fullName) ||
        empty($projectName) ||
        empty($testimonialTitle) ||
        empty($testimonialMessage)
    ) {

        $errorMessage =
            'Please fill all required fields.';
    }
    else {

        $stmt =
            $conn->prepare(
                "
                INSERT INTO client_testimonials
                (

                    client_id,
                    client_full_name,
                    company_name,
                    project_name,
                    testimonial_title,
                    testimonial_message,
                    rating

                )

                VALUES
                (?, ?, ?, ?, ?, ?, ?)
                "
            );

        $stmt->bind_param(
            'isssssi',
            $clientId,
            $fullName,
            $companyName,
            $projectName,
            $testimonialTitle,
            $testimonialMessage,
            $rating
        );

        if ($stmt->execute()) {

            $successMessage =
                'Testimonial submitted successfully.';
        }
        else {

            $errorMessage =
                'Unable to submit testimonial.';
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
        FROM client_testimonials
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
        INSERT INTO client_testimonials
        (

            client_id,
            client_full_name,
            company_name,
            project_name,
            testimonial_title,
            testimonial_message,
            rating,
            featured,
            status,
            admin_reply

        )

        VALUES

        (
            $clientId,
            'Rajesh Kumar',
            'RK Developers',
            'Luxury Villa Project',
            'Outstanding Construction Quality',
            'KVN Construction delivered exceptional quality and completed the project on time.',
            5,
            'Yes',
            'Approved',
            'Thank you for your valuable feedback.'
        ),

        (
            $clientId,
            'Anita Sharma',
            'Sharma Interiors',
            'Farm House Project',
            'Professional Team Support',
            'The project coordination and communication were excellent throughout the project.',
            4,
            'No',
            'Pending',
            NULL
        ),

        (
            $clientId,
            'Vikram Reddy',
            'VR Group',
            'Commercial Complex',
            'Need Better Timeline Updates',
            'Overall work is good but regular timeline updates are required.',
            3,
            'No',
            'Rejected',
            'Please revise and resubmit the testimonial.'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH TESTIMONIALS
|--------------------------------------------------------------------------
*/

$testimonials =
    $conn->query(
        "
        SELECT *
        FROM client_testimonials
        WHERE client_id = $clientId
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalTestimonials = 0;
$approvedTestimonials = 0;
$pendingTestimonials = 0;
$featuredTestimonials = 0;

if (
    $testimonials &&
    $testimonials->num_rows > 0
) {

    while (
        $calc =
        $testimonials->fetch_assoc()
    ) {

        $totalTestimonials++;

        if (
            $calc['status']
            === 'Approved'
        ) {

            $approvedTestimonials++;
        }

        if (
            $calc['status']
            === 'Pending'
        ) {

            $pendingTestimonials++;
        }

        if (
            $calc['featured']
            === 'Yes'
        ) {

            $featuredTestimonials++;
        }
    }

    $testimonials->data_seek(0);
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
        Client Testimonials
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
        .form-group textarea,
        .form-group select{

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

        .testimonial-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(360px,1fr));

            gap:25px;
        }

        .testimonial-card{

            background:#fff;

            border-radius:20px;

            padding:25px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .testimonial-card h3{

            margin-bottom:15px;

            color:#111827;
        }

        .testimonial-card p{

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

        .featured{

            background:#111827;

            color:#fff;

            padding:5px 12px;

            border-radius:20px;

            font-size:12px;

            margin-left:10px;
        }

        .reply-box{

            margin-top:18px;

            background:#f8f9fa;

            padding:15px;

            border-radius:10px;
        }

        .stars{

            color:#f5b400;

            font-size:18px;

            margin-bottom:15px;
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

    <a href="images.php">
        Uploaded Images
    </a>

    <a href="videos.php">
        Uploaded Videos
    </a>

    <a href="feedback.php">
        Feedback
    </a>

    <a
        href="testimonials.php"
        class="active"
    >
        Testimonials
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
                Client Testimonials
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
                Total Testimonials
            </h4>

            <h2>
                <?php echo $totalTestimonials; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Approved
            </h4>

            <h2>
                <?php echo $approvedTestimonials; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Pending
            </h4>

            <h2>
                <?php echo $pendingTestimonials; ?>
            </h2>

        </div>

        <div class="card">

            <h4>
                Featured
            </h4>

            <h2>
                <?php echo $featuredTestimonials; ?>
            </h2>

        </div>

    </div>

    <!-- FORM -->

    <div class="form-section">

        <h2>
            Submit Testimonial
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
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="client_full_name"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Company Name
                    </label>

                    <input
                        type="text"
                        name="company_name"
                    >

                </div>

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
                        Rating
                    </label>

                    <select name="rating">

                        <option value="5">
                            5 Stars
                        </option>

                        <option value="4">
                            4 Stars
                        </option>

                        <option value="3">
                            3 Stars
                        </option>

                        <option value="2">
                            2 Stars
                        </option>

                        <option value="1">
                            1 Star
                        </option>

                    </select>

                </div>

                <div class="form-group full-width">

                    <label>
                        Testimonial Title
                    </label>

                    <input
                        type="text"
                        name="testimonial_title"
                        required
                    >

                </div>

                <div class="form-group full-width">

                    <label>
                        Testimonial Message
                    </label>

                    <textarea
                        name="testimonial_message"
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
                        Submit Testimonial
                    </button>

                </div>

            </div>

        </form>

    </div>

    <!-- TESTIMONIALS -->

    <div class="testimonial-grid">

        <?php if ($testimonials && $testimonials->num_rows > 0): ?>

            <?php while ($row = $testimonials->fetch_assoc()): ?>

                <div class="testimonial-card">

                    <span
                        class="badge <?php echo htmlspecialchars((string)$row['status']); ?>"
                    >

                        <?php
                            echo htmlspecialchars(
                                (string)$row['status']
                            );
                        ?>

                    </span>

                    <?php if ($row['featured'] === 'Yes'): ?>

                        <span class="featured">
                            Featured
                        </span>

                    <?php endif; ?>

                    <h3>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['testimonial_title']
                            );
                        ?>

                    </h3>

                    <div class="stars">

                        <?php
                            echo str_repeat(
                                '⭐',
                                (int)$row['rating']
                            );
                        ?>

                    </div>

                    <p>

                        <strong>
                            Client:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['client_full_name']
                            );
                        ?>

                    </p>

                    <p>

                        <strong>
                            Company:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['company_name']
                            );
                        ?>

                    </p>

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
                            Message:
                        </strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['testimonial_message']
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