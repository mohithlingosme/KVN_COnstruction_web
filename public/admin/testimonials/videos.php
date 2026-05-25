<?php

declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin_id'])) {

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
| DELETE VIDEO
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id =
        (int) $_GET['delete'];

    try {

        $stmt = $conn->prepare(
            "
            DELETE FROM testimonial_videos
            WHERE id = ?
            "
        );

        if ($stmt) {

            $stmt->bind_param(
                'i',
                $id
            );

            $stmt->execute();

            $stmt->close();
        }

    } catch (Throwable $e) {

        die($e->getMessage());
    }

    header('Location: videos.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH VIDEOS
|--------------------------------------------------------------------------
*/

$videos = [];

try {

    $query = "
        SELECT
            id,
            client_name,
            title,
            video_url,
            thumbnail,
            created_at
        FROM testimonial_videos
        ORDER BY id DESC
    ";

    $result = $conn->query($query);

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            $videos[] = $row;
        }
    }

} catch (Throwable $e) {

    die($e->getMessage());
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
        Testimonial Videos
    </title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            font-family:Arial,sans-serif;

            background:#f5f5f5;

            padding:40px;
        }

        .container{

            max-width:1200px;

            margin:auto;
        }

        .top-bar{

            display:flex;

            justify-content:space-between;

            align-items:center;

            margin-bottom:40px;

            flex-wrap:wrap;

            gap:20px;
        }

        h1{

            color:#222;
        }

        .btn{

            display:inline-block;

            background:#f5b400;

            color:#fff;

            text-decoration:none;

            padding:14px 24px;

            border-radius:10px;

            font-weight:bold;
        }

        .btn:hover{

            background:#d89d00;
        }

        .grid{

            display:grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(320px,1fr)
                );

            gap:30px;
        }

        .card{

            background:#fff;

            border-radius:20px;

            overflow:hidden;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);

            transition:0.3s;
        }

        .card:hover{

            transform:translateY(-5px);
        }

        .thumbnail{

            position:relative;
        }

        .thumbnail img{

            width:100%;

            height:220px;

            object-fit:cover;
        }

        .play-btn{

            position:absolute;

            top:50%;

            left:50%;

            transform:
                translate(-50%, -50%);

            width:70px;

            height:70px;

            background:rgba(0,0,0,0.7);

            color:#fff;

            border-radius:50%;

            display:flex;

            justify-content:center;

            align-items:center;

            font-size:28px;
        }

        .content{

            padding:25px;
        }

        .title{

            font-size:22px;

            margin-bottom:10px;

            color:#222;
        }

        .client{

            color:#777;

            margin-bottom:20px;
        }

        .actions{

            display:flex;

            gap:15px;

            flex-wrap:wrap;
        }

        .watch{

            background:#007bff;
        }

        .delete{

            background:#dc3545;
        }

        .watch:hover{

            background:#0069d9;
        }

        .delete:hover{

            background:#c82333;
        }

        .empty{

            background:#fff;

            padding:40px;

            border-radius:20px;

            text-align:center;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .back{

            display:inline-block;

            margin-top:30px;

            text-decoration:none;

            color:#333;

            font-weight:bold;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="top-bar">

        <h1>
            Testimonial Videos
        </h1>

        <a
            href="create-video.php"
            class="btn"
        >
            + Add Video
        </a>

    </div>

    <?php if (count($videos) > 0): ?>

        <div class="grid">

            <?php foreach ($videos as $video): ?>

                <div class="card">

                    <div class="thumbnail">

                        <img
                            src="<?php echo htmlspecialchars((string)$video['thumbnail']); ?>"
                            alt="Thumbnail"
                        >

                        <div class="play-btn">

                            ▶

                        </div>

                    </div>

                    <div class="content">

                        <div class="title">

                            <?php
                                echo htmlspecialchars(
                                    (string)$video['title']
                                );
                            ?>

                        </div>

                        <div class="client">

                            Client:
                            <?php
                                echo htmlspecialchars(
                                    (string)$video['client_name']
                                );
                            ?>

                        </div>

                        <div class="actions">

                            <a
                                href="<?php echo htmlspecialchars((string)$video['video_url']); ?>"
                                target="_blank"
                                class="btn watch"
                            >
                                Watch Video
                            </a>

                            <a
                                href="?delete=<?php echo (int)$video['id']; ?>"
                                class="btn delete"
                                onclick="return confirm('Delete this video?')"
                            >
                                Delete
                            </a>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            <h2>
                No Testimonial Videos Found
            </h2>

            <br>

            <p>
                Add your first testimonial video.
            </p>

        </div>

    <?php endif; ?>

    <a
        href="index.php"
        class="back"
    >
        ← Back to Testimonials
    </a>

</div>

</body>

</html>