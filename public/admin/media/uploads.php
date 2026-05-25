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
| CREATE UPLOAD DIRECTORY
|--------------------------------------------------------------------------
*/

$uploadDir =
    '../../uploads/media/';

if (!is_dir($uploadDir)) {

    mkdir(
        $uploadDir,
        0777,
        true
    );
}

/*
|--------------------------------------------------------------------------
| DEFAULTS
|--------------------------------------------------------------------------
*/

$error = '';
$success = '';

/*
|--------------------------------------------------------------------------
| HANDLE FILE UPLOAD
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title =
        trim($_POST['title'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($title === '') {

        $error =
            'Please enter media title.';

    } elseif (
        !isset($_FILES['media_file']) ||
        $_FILES['media_file']['error'] !== 0
    ) {

        $error =
            'Please select a valid file.';
    }

    /*
    |--------------------------------------------------------------------------
    | PROCESS UPLOAD
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $originalName =
                $_FILES['media_file']['name'];

            $tmpPath =
                $_FILES['media_file']['tmp_name'];

            $fileSize =
                $_FILES['media_file']['size'];

            $extension =
                strtolower(
                    pathinfo(
                        $originalName,
                        PATHINFO_EXTENSION
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | ALLOWED FILE TYPES
            |--------------------------------------------------------------------------
            */

            $allowedExtensions = [

                'jpg',
                'jpeg',
                'png',
                'gif',
                'webp',
                'mp4',
                'pdf',
                'doc',
                'docx'

            ];

            if (
                !in_array(
                    $extension,
                    $allowedExtensions,
                    true
                )
            ) {

                $error =
                    'Invalid file type.';
            }

            /*
            |--------------------------------------------------------------------------
            | FILE SIZE LIMIT
            |--------------------------------------------------------------------------
            */

            if (
                $fileSize > 20 * 1024 * 1024
            ) {

                $error =
                    'File size exceeds 20MB.';
            }

            /*
            |--------------------------------------------------------------------------
            | SAVE FILE
            |--------------------------------------------------------------------------
            */

            if ($error === '') {

                $newFileName =
                    time() .
                    '_' .
                    uniqid() .
                    '.' .
                    $extension;

                $destination =
                    $uploadDir .
                    $newFileName;

                if (
                    move_uploaded_file(
                        $tmpPath,
                        $destination
                    )
                ) {

                    $mimeType =
                        mime_content_type(
                            $destination
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | STORE DATABASE
                    |--------------------------------------------------------------------------
                    */

                    $stmt =
                        $conn->prepare(
                            "
                            INSERT INTO media
                            (
                                title,
                                file_name,
                                file_path,
                                file_type
                            )
                            VALUES
                            (
                                ?, ?, ?, ?
                            )
                            "
                        );

                    if ($stmt) {

                        $relativePath =
                            'uploads/media/' .
                            $newFileName;

                        $stmt->bind_param(
                            'ssss',
                            $title,
                            $newFileName,
                            $relativePath,
                            $mimeType
                        );

                        $stmt->execute();

                        $stmt->close();

                        $success =
                            'File uploaded successfully.';

                    } else {

                        $error =
                            'Database query failed.';
                    }

                } else {

                    $error =
                        'Failed to upload file.';
                }
            }

        } catch (Throwable $e) {

            $error =
                $e->getMessage();
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
        Upload Media
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

            max-width:700px;

            margin:auto;

            background:#fff;

            padding:40px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        h1{

            margin-bottom:30px;

            color:#222;
        }

        .alert{

            padding:15px 20px;

            border-radius:10px;

            margin-bottom:25px;

            font-weight:bold;
        }

        .error{

            background:#ffe5e5;

            color:#d8000c;
        }

        .success{

            background:#e7f9ed;

            color:#1e7e34;
        }

        .form-group{

            margin-bottom:25px;
        }

        label{

            display:block;

            margin-bottom:10px;

            font-weight:bold;

            color:#333;
        }

        input{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:15px;
        }

        input[type="file"]{

            padding:12px;

            background:#fafafa;
        }

        button{

            width:100%;

            padding:16px;

            border:none;

            border-radius:10px;

            background:#f5b400;

            color:#fff;

            font-size:16px;

            font-weight:bold;

            cursor:pointer;

            transition:0.3s;
        }

        button:hover{

            background:#d99f00;
        }

        .info{

            background:#f8f9fa;

            padding:20px;

            border-radius:12px;

            margin-bottom:25px;

            line-height:1.8;

            color:#555;
        }

        .back{

            display:inline-block;

            margin-top:25px;

            text-decoration:none;

            color:#333;

            font-weight:bold;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>
        Upload Media
    </h1>

    <div class="info">

        Allowed Files:
        JPG, PNG, GIF, WEBP,
        MP4, PDF, DOC, DOCX

        <br><br>

        Maximum Upload Size:
        20MB

    </div>

    <?php if ($error !== ''): ?>

        <div class="alert error">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>

    <?php if ($success !== ''): ?>

        <div class="alert success">

            <?php echo htmlspecialchars($success); ?>

        </div>

    <?php endif; ?>

    <form
        method="POST"
        enctype="multipart/form-data"
    >

        <div class="form-group">

            <label>
                Media Title *
            </label>

            <input
                type="text"
                name="title"
                required
            >

        </div>

        <div class="form-group">

            <label>
                Select File *
            </label>

            <input
                type="file"
                name="media_file"
                required
            >

        </div>

        <button type="submit">

            Upload File

        </button>

    </form>

    <a
        href="index.php"
        class="back"
    >
        ← Back to Media Library
    </a>

</div>

</body>

</html>