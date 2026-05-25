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
| FETCH DOCUMENTS ONLY
|--------------------------------------------------------------------------
*/

$documents = [];

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
            file_type LIKE 'application/%'
            OR
            file_name LIKE '%.pdf'
            OR
            file_name LIKE '%.doc'
            OR
            file_name LIKE '%.docx'
            OR
            file_name LIKE '%.xls'
            OR
            file_name LIKE '%.xlsx'
        ORDER BY id DESC
    ";

    $result =
        $conn->query($query);

    if ($result) {

        while (
            $row =
            $result->fetch_assoc()
        ) {

            $documents[] = $row;
        }
    }

} catch (Throwable $e) {

    die($e->getMessage());
}

/*
|--------------------------------------------------------------------------
| DELETE DOCUMENT
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id =
        (int) $_GET['delete'];

    try {

        /*
        |--------------------------------------------------------------------------
        | FETCH FILE
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

            $document =
                $result->fetch_assoc();

            $stmt->close();

            /*
            |--------------------------------------------------------------------------
            | DELETE FILE
            |--------------------------------------------------------------------------
            */

            if (
                $document &&
                file_exists(
                    '../../' .
                    $document['file_path']
                )
            ) {

                unlink(
                    '../../' .
                    $document['file_path']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE DATABASE RECORD
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

    header('Location: documents.php');
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
        Document Library
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

        .download{

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

            padding:30px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);

            transition:0.3s;
        }

        .card:hover{

            transform:translateY(-5px);
        }

        .icon{

            font-size:70px;

            margin-bottom:25px;
        }

        .title{

            font-size:24px;

            margin-bottom:18px;

            color:#222;
        }

        .meta{

            color:#666;

            line-height:1.8;

            margin-bottom:25px;
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
            Document Library
        </h1>

        <a
            href="uploads.php"
            class="btn upload"
        >
            + Upload Document
        </a>

    </div>

    <?php if (count($documents) > 0): ?>

        <div class="grid">

            <?php foreach ($documents as $document): ?>

                <div class="card">

                    <div class="icon">

                        📄

                    </div>

                    <div class="title">

                        <?php
                            echo htmlspecialchars(
                                (string)$document['title']
                            );
                        ?>

                    </div>

                    <div class="badge">

                        <?php
                            echo htmlspecialchars(
                                (string)$document['file_type']
                            );
                        ?>

                    </div>

                    <div class="meta">

                        <strong>File:</strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$document['file_name']
                            );
                        ?>

                        <br><br>

                        <strong>Uploaded:</strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$document['created_at']
                            );
                        ?>

                    </div>

                    <div class="actions">

                        <a
                            href="../../<?php echo htmlspecialchars((string)$document['file_path']); ?>"
                            target="_blank"
                            class="btn view"
                        >
                            View
                        </a>

                        <a
                            href="../../<?php echo htmlspecialchars((string)$document['file_path']); ?>"
                            download
                            class="btn download"
                        >
                            Download
                        </a>

                        <a
                            href="?delete=<?php echo (int)$document['id']; ?>"
                            class="btn delete"
                            onclick="return confirm('Delete this document?')"
                        >
                            Delete
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            <h2>
                No Documents Found
            </h2>

            <br>

            <p>
                Upload your first document.
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