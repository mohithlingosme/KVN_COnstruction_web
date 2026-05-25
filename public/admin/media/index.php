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
| DELETE MEDIA
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id =
        (int) $_GET['delete'];

    try {

        /*
        |--------------------------------------------------------------------------
        | GET FILE PATH
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
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

            $media =
                $result->fetch_assoc();

            $stmt->close();

            /*
            |--------------------------------------------------------------------------
            | DELETE FILE
            |--------------------------------------------------------------------------
            */

            if (
                $media &&
                file_exists(
                    $media['file_path']
                )
            ) {

                unlink(
                    $media['file_path']
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

    header('Location: index.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH MEDIA
|--------------------------------------------------------------------------
*/

$mediaFiles = [];

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
        ORDER BY id DESC
    ";

    $result = $conn->query($query);

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            $mediaFiles[] = $row;
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
        Media Library
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

        .grid{

            display:grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(300px,1fr)
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

        .preview{

            width:100%;

            height:240px;

            background:#eee;

            display:flex;

            justify-content:center;

            align-items:center;

            overflow:hidden;
        }

        .preview img{

            width:100%;

            height:100%;

            object-fit:cover;
        }

        .file-icon{

            font-size:70px;
        }

        .content{

            padding:25px;
        }

        .title{

            font-size:22px;

            margin-bottom:12px;

            color:#222;
        }

        .meta{

            color:#777;

            margin-bottom:20px;

            line-height:1.6;
        }

        .badge{

            display:inline-block;

            padding:8px 14px;

            border-radius:30px;

            background:#222;

            color:#fff;

            font-size:13px;

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
            Media Library
        </h1>

        <a
            href="upload.php"
            class="btn upload"
        >
            + Upload Media
        </a>

    </div>

    <?php if (count($mediaFiles) > 0): ?>

        <div class="grid">

            <?php foreach ($mediaFiles as $media): ?>

                <div class="card">

                    <div class="preview">

                        <?php
                            $type =
                                strtolower(
                                    (string)$media['file_type']
                                );
                        ?>

                        <?php if (
                            str_contains($type, 'image')
                        ): ?>

                            <img
                                src="<?php echo htmlspecialchars((string)$media['file_path']); ?>"
                                alt="Media"
                            >

                        <?php else: ?>

                            <div class="file-icon">

                                📄

                            </div>

                        <?php endif; ?>

                    </div>

                    <div class="content">

                        <div class="title">

                            <?php
                                echo htmlspecialchars(
                                    (string)$media['title']
                                );
                            ?>

                        </div>

                        <div class="badge">

                            <?php
                                echo htmlspecialchars(
                                    (string)$media['file_type']
                                );
                            ?>

                        </div>

                        <div class="meta">

                            <strong>File:</strong>

                            <?php
                                echo htmlspecialchars(
                                    (string)$media['file_name']
                                );
                            ?>

                        </div>

                        <div class="actions">

                            <a
                                href="<?php echo htmlspecialchars((string)$media['file_path']); ?>"
                                target="_blank"
                                class="btn view"
                            >
                                View
                            </a>

                            <a
                                href="?delete=<?php echo (int)$media['id']; ?>"
                                class="btn delete"
                                onclick="return confirm('Delete this file?')"
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
                No Media Files Found
            </h2>

            <br>

            <p>
                Upload your first media file.
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