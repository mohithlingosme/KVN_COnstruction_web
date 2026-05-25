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

        project_location VARCHAR(255) NOT NULL,

        progress INT NOT NULL DEFAULT 0,

        project_status ENUM(
            'Planning',
            'In Progress',
            'Completed',
            'On Hold'
        )
        NOT NULL DEFAULT 'Planning',

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| CREATE GALLERY TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS project_gallery (

        id INT AUTO_INCREMENT PRIMARY KEY,

        project_id INT NOT NULL,

        image_title VARCHAR(255) NOT NULL,

        image_path VARCHAR(500) NOT NULL,

        image_category VARCHAR(255) DEFAULT NULL,

        uploaded_at TIMESTAMP
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
| INSERT DEMO GALLERY IMAGES
|--------------------------------------------------------------------------
*/

$checkGallery =
    $conn->query(
        "
        SELECT id
        FROM project_gallery
        WHERE project_id = $projectId
        LIMIT 1
        "
    );

if (
    $checkGallery &&
    $checkGallery->num_rows === 0
) {

    $conn->query(
        "
        INSERT INTO project_gallery
        (

            project_id,
            image_title,
            image_path,
            image_category

        )

        VALUES

        (
            $projectId,
            'Foundation Work',
            'https://images.unsplash.com/photo-1504307651254-35680f356dfd',
            'Construction'
        ),

        (
            $projectId,
            'Structural Progress',
            'https://images.unsplash.com/photo-1541888946425-d81bb19240f5',
            'Structure'
        ),

        (
            $projectId,
            'Interior Finishing',
            'https://images.unsplash.com/photo-1484154218962-a197022b5858',
            'Interior'
        ),

        (
            $projectId,
            'Site Overview',
            'https://images.unsplash.com/photo-1503387762-592deb58ef4e',
            'Site'
        ),

        (
            $projectId,
            'Modern Elevation',
            'https://images.unsplash.com/photo-1512917774080-9991f1c4c750',
            'Elevation'
        ),

        (
            $projectId,
            'Luxury Living Space',
            'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85',
            'Interior'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| FETCH GALLERY
|--------------------------------------------------------------------------
*/

$gallery =
    $conn->query(
        "
        SELECT *
        FROM project_gallery
        WHERE project_id = $projectId
        ORDER BY id DESC
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
        Project Gallery
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

            max-width:1500px;

            margin:auto;

            padding:40px 20px;
        }

        .topbar{

            display:flex;

            justify-content:space-between;

            align-items:center;

            flex-wrap:wrap;

            gap:15px;

            margin-bottom:35px;
        }

        .btn{

            text-decoration:none;

            color:#fff;

            padding:12px 18px;

            border-radius:10px;

            font-weight:bold;
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

            font-size:34px;

            margin-bottom:12px;
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

            border-radius:14px;

            min-width:220px;
        }

        .meta-box h4{

            color:#666;

            margin-bottom:8px;
        }

        .meta-box p{

            font-size:18px;

            font-weight:bold;
        }

        .gallery-section{

            background:#fff;

            padding:35px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .gallery-section h2{

            margin-bottom:30px;
        }

        .gallery-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(320px,1fr));

            gap:25px;
        }

        .gallery-card{

            background:#fafafa;

            border-radius:18px;

            overflow:hidden;

            transition:0.3s;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.05);
        }

        .gallery-card:hover{

            transform:translateY(-5px);
        }

        .gallery-image{

            width:100%;

            height:260px;

            object-fit:cover;
        }

        .gallery-content{

            padding:20px;
        }

        .gallery-content h3{

            margin-bottom:12px;

            font-size:22px;
        }

        .category{

            display:inline-block;

            background:#f5b400;

            color:#111;

            padding:7px 14px;

            border-radius:30px;

            font-size:12px;

            font-weight:bold;

            margin-bottom:12px;
        }

        .uploaded{

            color:#777;

            font-size:14px;
        }

        .empty{

            text-align:center;

            padding:50px;

            color:#777;
        }

        @media(max-width:768px){

            .topbar{

                flex-direction:column;

                align-items:flex-start;
            }

            .project-header h1{

                font-size:28px;
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
            Construction progress images and site updates.
        </p>

        <div class="project-meta">

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
                    Progress
                </h4>

                <p>

                    <?php
                        echo (int)$project['progress'];
                    ?>%

                </p>

            </div>

            <div class="meta-box">

                <h4>
                    Status
                </h4>

                <p>

                    <?php
                        echo htmlspecialchars(
                            (string)$project['project_status']
                        );
                    ?>

                </p>

            </div>

        </div>

    </div>

    <!-- GALLERY -->

    <div class="gallery-section">

        <h2>
            Project Gallery
        </h2>

        <?php if ($gallery && $gallery->num_rows > 0): ?>

            <div class="gallery-grid">

                <?php while ($row = $gallery->fetch_assoc()): ?>

                    <div class="gallery-card">

                        <img
                            src="<?php echo htmlspecialchars((string)$row['image_path']); ?>"
                            alt="Project Image"
                            class="gallery-image"
                        >

                        <div class="gallery-content">

                            <span class="category">

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['image_category']
                                    );
                                ?>

                            </span>

                            <h3>

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['image_title']
                                    );
                                ?>

                            </h3>

                            <div class="uploaded">

                                Uploaded:
                                <?php
                                    echo date(
                                        'd M Y',
                                        strtotime(
                                            (string)$row['uploaded_at']
                                        )
                                    );
                                ?>

                            </div>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="empty">

                No gallery images available.

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>