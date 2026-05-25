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
            DELETE FROM videos
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

    header('Location: index.php');
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
            title,
            category,
            thumbnail,
            video_url,
            description,
            created_at
        FROM videos
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
        Videos Management
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

            max-width:1300px;

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

            padding:14px 24px;

            border-radius:10px;

            text-decoration:none;

            color:#fff;

            font-weight:bold;
        }

        .add{

            background:#f5b400;
        }

        .edit{

            background:#007bff;
        }

        .delete{

            background:#dc3545;
        }

        .watch{

            background:#28a745;
        }

        .btn:hover{

            opacity:0.9;
        }

        .grid{

            display:grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(340px,1fr)
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

            height:230px;

            object-fit:cover;
        }

        .play{

            position:absolute;

            top:50%;

            left:50%;

            transform:
                translate(-50%, -50%);

            width:70px;

            height:70px;

            border-radius:50%;

            background:
                rgba(0,0,0,0.7);

            color:#fff;

            display:flex;

            justify-content:center;

            align-items:center;

            font-size:30px;
        }

        .content{

            padding:25px;
        }

        .category{

            display:inline-block;

            background:#f5b400;

            color:#fff;

            padding:8px 14px;

            border-radius:30px;

            font-size:13px;

            margin-bottom:15px;
        }

        .title{

            font-size:24px;

            color:#222;

            margin-bottom:15px;
        }

        .description{

            color:#555;

            line-height:1.7;

            margin-bottom:20px;
        }

        .actions{

            display:flex;

            gap:12px;

            flex-wrap:wrap;
        }

        .empty{

            background:#fff;

            padding:50px;

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
            Videos Management
        </h1>

        <a
            href="create.php"
            class="btn add"
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

                        <div class="play">

                            ▶

                        </div>

                    </div>

                    <div class="content">

                        <div class="category">

                            <?php
                                echo htmlspecialchars(
                                    (string)$video['category']
                                );
                            ?>

                        </div>

                        <div class="title">

                            <?php
                                echo htmlspecialchars(
                                    (string)$video['title']
                                );
                            ?>

                        </div>

                        <div class="description">

                            <?php
                                echo htmlspecialchars(
                                    (string)$video['description']
                                );
                            ?>

                        </div>

                        <div class="actions">

                            <a
                                href="<?php echo htmlspecialchars((string)$video['video_url']); ?>"
                                target="_blank"
                                class="btn watch"
                            >
                                Watch
                            </a>

                            <a
                                href="edit.php?id=<?php echo (int)$video['id']; ?>"
                                class="btn edit"
                            >
                                Edit
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
                No Videos Found
            </h2>

            <br>

            <p>
                Start by adding your first project video.
            </p>

        </div>

    <?php endif; ?>

    <a
        href="../dashboard.php"
        class="back"
    >
        ← Back to Dashboard
    </a>

</div>

</body>

</html>