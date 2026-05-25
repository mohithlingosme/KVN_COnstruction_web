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
| FETCH VIDEOS ONLY
|--------------------------------------------------------------------------
*/

$videos = [];

try {

    $query = "
        SELECT
            id,
            title,
            file_name,
            file_path,
            file_type,
            created_at
        FROM media
        WHERE
            file_type LIKE 'video/%'
        ORDER BY id DESC
    ";

    $result =
        $conn->query($query);

    if ($result) {

        while (
            $row =
            $result->fetch_assoc()
        ) {

            $videos[] = $row;
        }
    }

} catch (Throwable $e) {

    die($e->getMessage());
}

/*
|--------------------------------------------------------------------------
| DELETE VIDEO
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id =
        (int) $_GET['delete'];

    try {

        /*
        |--------------------------------------------------------------------------
        | FETCH VIDEO
        |--------------------------------------------------------------------------
        */

        $stmt =
            $conn->prepare(
                "
                SELECT file_path
                FROM media
                WHERE id = ?
                "
            );

        if ($stmt) {

            $stmt->bind_param(
                'i',
                $id
            );

            $stmt->execute();

            $result =
                $stmt->get_result();

            $video =
                $result->fetch_assoc();

            $stmt->close();

            /*
            |--------------------------------------------------------------------------
            | DELETE FILE
            |--------------------------------------------------------------------------
            */

            if (
                $video &&
                file_exists(
                    '../../' .
                    $video['file_path']
                )
            ) {

                unlink(
                    '../../' .
                    $video['file_path']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE DB RECORD
            |--------------------------------------------------------------------------
            */

            $delete =
                $conn->prepare(
                    "
                    DELETE FROM media
                    WHERE id = ?
                    "
                );

            if ($delete) {

                $delete->bind_param(
                    'i',
                    $id
                );

                $delete->execute();

                $delete->close();
            }
        }

    } catch (Throwable $e) {

        die($e->getMessage());
    }

    header('Location: videos.php');
    exit();
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
        Video Library
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

            max-width:1400px;

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

            padding:14px 22px;

            border-radius:10px;

            text-decoration:none;

            color:#fff;

            font-weight:bold;
        }

        .upload{

            background:#f5b400;
        }

        .delete{

            background:#dc3545;
        }

        .view{

            background:#007bff;
        }

        .btn:hover{

            opacity:0.9;
        }

        .gallery{

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

        .video-box{

            width:100%;

            height:240px;

            background:#000;
        }

        video{

            width:100%;

            height:100%;

            object-fit:cover;
        }

        .content{

            padding:25px;
        }

        .title{

            font-size:22px;

            margin-bottom:15px;

            color:#222;
        }

        .meta{

            color:#666;

            margin-bottom:20px;

            line-height:1.7;
        }

        .badge{

            display:inline-block;

            padding:8px 14px;

            border-radius:30px;

            background:#222;

            color:#fff;

            font-size:13px;

            margin-bottom:18px;
        }

        .actions{

            display:flex;

            gap:12px;

            flex-wrap:wrap;
        }

        .empty{

            background:#fff;

            padding:60px;

            text-align:center;

            border-radius:20px;

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
            Video Library
        </h1>

        <a
            href="uploads.php"
            class="btn upload"
        >
            + Upload Video
        </a>

    </div>

    <?php if (count($videos) > 0): ?>

        <div class="gallery">

            <?php foreach ($videos as $video): ?>

                <div class="card">

                    <div class="video-box">

                        <video controls>

                            <source
                                src="../../<?php echo htmlspecialchars((string)$video['file_path']); ?>"
                                type="<?php echo htmlspecialchars((string)$video['file_type']); ?>"
                            >

                        </video>

                    </div>

                    <div class="content">

                        <div class="title">

                            <?php
                                echo htmlspecialchars(
                                    (string)$video['title']
                                );
                            ?>

                        </div>

                        <div class="badge">

                            <?php
                                echo htmlspecialchars(
                                    (string)$video['file_type']
                                );
                            ?>

                        </div>

                        <div class="meta">

                            <strong>File:</strong>

                            <?php
                                echo htmlspecialchars(
                                    (string)$video['file_name']
                                );
                            ?>

                            <br><br>

                            <strong>Uploaded:</strong>

                            <?php
                                echo htmlspecialchars(
                                    (string)$video['created_at']
                                );
                            ?>

                        </div>

                        <div class="actions">

                            <a
                                href="../../<?php echo htmlspecialchars((string)$video['file_path']); ?>"
                                target="_blank"
                                class="btn view"
                            >
                                View
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
                Upload your first video.
            </p>

        </div>

    <?php endif; ?>

    <a
        href="index.php"
        class="back"
    >
        ← Back to Media Library
    </a>

</div>

</body>

</html>