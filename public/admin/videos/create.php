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
| DEFAULTS
|--------------------------------------------------------------------------
*/

$error = '';
$success = '';

/*
|--------------------------------------------------------------------------
| CREATE VIDEO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title =
        trim($_POST['title'] ?? '');

    $category =
        trim($_POST['category'] ?? '');

    $description =
        trim($_POST['description'] ?? '');

    $thumbnail =
        trim($_POST['thumbnail'] ?? '');

    $video_url =
        trim($_POST['video_url'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        $title === '' ||
        $category === '' ||
        $thumbnail === '' ||
        $video_url === ''
    ) {

        $error =
            'Please fill all required fields.';

    } else {

        try {

            $stmt = $conn->prepare(
                "
                INSERT INTO videos
                (
                    title,
                    category,
                    description,
                    thumbnail,
                    video_url
                )
                VALUES
                (
                    ?, ?, ?, ?, ?
                )
                "
            );

            if (!$stmt) {

                $error =
                    'Database query failed.';

            } else {

                $stmt->bind_param(
                    'sssss',
                    $title,
                    $category,
                    $description,
                    $thumbnail,
                    $video_url
                );

                $stmt->execute();

                $stmt->close();

                $success =
                    'Video added successfully.';

                /*
                |--------------------------------------------------------------------------
                | CLEAR FORM
                |--------------------------------------------------------------------------
                */

                $title = '';
                $category = '';
                $description = '';
                $thumbnail = '';
                $video_url = '';
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
        Add Video
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

        input,
        textarea,
        select{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:15px;
        }

        textarea{

            resize:vertical;

            min-height:140px;
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
        Add New Video
    </h1>

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

    <form method="POST">

        <div class="form-group">

            <label>
                Video Title *
            </label>

            <input
                type="text"
                name="title"
                value="<?php echo htmlspecialchars($title ?? ''); ?>"
                required
            >

        </div>

        <div class="form-group">

            <label>
                Category *
            </label>

            <select
                name="category"
                required
            >

                <option value="">
                    Select Category
                </option>

                <option value="Residential">
                    Residential
                </option>

                <option value="Commercial">
                    Commercial
                </option>

                <option value="Interior">
                    Interior
                </option>

                <option value="Architecture">
                    Architecture
                </option>

            </select>

        </div>

        <div class="form-group">

            <label>
                Thumbnail URL *
            </label>

            <input
                type="text"
                name="thumbnail"
                value="<?php echo htmlspecialchars($thumbnail ?? ''); ?>"
                placeholder="https://example.com/image.jpg"
                required
            >

        </div>

        <div class="form-group">

            <label>
                Video URL *
            </label>

            <input
                type="text"
                name="video_url"
                value="<?php echo htmlspecialchars($video_url ?? ''); ?>"
                placeholder="https://youtube.com/..."
                required
            >

        </div>

        <div class="form-group">

            <label>
                Description
            </label>

            <textarea
                name="description"
                placeholder="Enter video description..."
            ><?php echo htmlspecialchars($description ?? ''); ?></textarea>

        </div>

        <button type="submit">

            Add Video

        </button>

    </form>

    <a
        href="index.php"
        class="back"
    >
        ← Back to Videos
    </a>

</div>

</body>

</html>