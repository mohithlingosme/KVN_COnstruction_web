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
| DEFAULT VALUES
|--------------------------------------------------------------------------
*/

$error   = '';
$success = '';

$title       = '';
$category    = '';
$image       = '';

/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMIT
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title =
        trim($_POST['title'] ?? '');

    $category =
        trim($_POST['category'] ?? '');

    $image =
        trim($_POST['image'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        $title === '' ||
        $category === '' ||
        $image === ''
    ) {

        $error =
            'Please fill all fields.';
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT PROJECT
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $stmt = $conn->prepare(
                "
                INSERT INTO portfolio
                (
                    title,
                    category,
                    image
                )
                VALUES
                (
                    ?,
                    ?,
                    ?
                )
                "
            );

            if (!$stmt) {

                $error =
                    'Database query failed.';
            } else {

                $stmt->bind_param(
                    'sss',
                    $title,
                    $category,
                    $image
                );

                if ($stmt->execute()) {

                    $success =
                        'Portfolio project created successfully.';

                    $title    = '';
                    $category = '';
                    $image    = '';

                } else {

                    $error =
                        'Failed to create portfolio project.';
                }

                $stmt->close();
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
        Create Portfolio Project
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

        .form-group{

            margin-bottom:25px;
        }

        label{

            display:block;

            margin-bottom:10px;

            font-weight:bold;
        }

        input{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:16px;
        }

        button{

            background:#f5b400;

            color:#fff;

            border:none;

            padding:15px 30px;

            border-radius:10px;

            cursor:pointer;

            font-size:16px;

            font-weight:bold;
        }

        button:hover{

            background:#d89d00;
        }

        .preview{

            width:100%;

            margin-top:20px;

            border-radius:15px;

            border:1px solid #ddd;
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
        Add Portfolio Project
    </h1>

    <?php if ($success !== ''): ?>

        <div class="success">

            <?php echo htmlspecialchars($success); ?>

        </div>

    <?php endif; ?>

    <?php if ($error !== ''): ?>

        <div class="error">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>

    <form method="POST">

        <div class="form-group">

            <label>
                Project Title
            </label>

            <input
                type="text"
                name="title"
                value="<?php echo htmlspecialchars($title); ?>"
                required
            >

        </div>

        <div class="form-group">

            <label>
                Category
            </label>

            <input
                type="text"
                name="category"
                value="<?php echo htmlspecialchars($category); ?>"
                placeholder="Residential / Commercial / Interior"
                required
            >

        </div>

        <div class="form-group">

            <label>
                Image URL
            </label>

            <input
                type="text"
                name="image"
                value="<?php echo htmlspecialchars($image); ?>"
                placeholder="https://example.com/image.jpg"
                required
            >

            <?php if ($image !== ''): ?>

                <img
                    src="<?php echo htmlspecialchars($image); ?>"
                    alt="Preview"
                    class="preview"
                >

            <?php endif; ?>

        </div>

        <button type="submit">

            Create Project

        </button>

    </form>

    <a
        href="index.php"
        class="back"
    >
        ← Back to Portfolio
    </a>

</div>

</body>

</html>