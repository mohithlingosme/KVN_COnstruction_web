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
| FETCH IMAGES ONLY
|--------------------------------------------------------------------------
*/

$images = [];

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
            file_type LIKE 'image/%'
        ORDER BY id DESC
    ";

    $result =
        $conn->query($query);

    if ($result) {

        while (
            $row =
            $result->fetch_assoc()
        ) {

            $images[] = $row;
        }
    }

} catch (Throwable $e) {

    die($e->getMessage());
}

/*
|--------------------------------------------------------------------------
| DELETE IMAGE
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id =
        (int) $_GET['delete'];

    try {

        /*
        |--------------------------------------------------------------------------
        | FETCH IMAGE
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

            $image =
                $result->fetch_assoc();

            $stmt->close();

            /*
            |--------------------------------------------------------------------------
            | DELETE FILE
            |--------------------------------------------------------------------------
            */

            if (
                $image &&
                file_exists(
                    '../../' .
                    $image['file_path']
                )
            ) {

                unlink(
                    '../../' .
                    $image['file_path']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE DB
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

    header('Location: images.php');
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
        Image Gallery
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

        .card img{

            width:100%;

            height:260px;

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
            Image Gallery
        </h1>

        <a
            href="uploads.php"
            class="btn upload"
        >
            + Upload Images
        </a>

    </div>

    <?php if (count($images) > 0): ?>

        <div class="gallery">

            <?php foreach ($images as $image): ?>

                <div class="card">

                    <img
                        src="../../<?php echo htmlspecialchars((string)$image['file_path']); ?>"
                        alt="Image"
                    >

                    <div class="content">

                        <div class="title">

                            <?php
                                echo htmlspecialchars(
                                    (string)$image['title']
                                );
                            ?>

                        </div>

                        <div class="badge">

                            <?php
                                echo htmlspecialchars(
                                    (string)$image['file_type']
                                );
                            ?>

                        </div>

                        <div class="meta">

                            <strong>File:</strong>

                            <?php
                                echo htmlspecialchars(
                                    (string)$image['file_name']
                                );
                            ?>

                            <br><br>

                            <strong>Uploaded:</strong>

                            <?php
                                echo htmlspecialchars(
                                    (string)$image['created_at']
                                );
                            ?>

                        </div>

                        <div class="actions">

                            <a
                                href="../../<?php echo htmlspecialchars((string)$image['file_path']); ?>"
                                target="_blank"
                                class="btn view"
                            >
                                View
                            </a>

                            <a
                                href="?delete=<?php echo (int)$image['id']; ?>"
                                class="btn delete"
                                onclick="return confirm('Delete this image?')"
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
                No Images Found
            </h2>

            <br>

            <p>
                Upload your first image.
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