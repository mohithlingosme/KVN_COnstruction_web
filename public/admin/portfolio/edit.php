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
| REPOSITORY BOOTSTRAP
|--------------------------------------------------------------------------
*/

require_once '../../includes/repositories.php';

/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

$id =
    isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {

    die('Invalid portfolio ID.');
}

/*
|--------------------------------------------------------------------------
| DEFAULT VALUES
|--------------------------------------------------------------------------
*/

$error   = '';
$success = '';

$title    = '';
$category = '';
$image    = '';

/*
|--------------------------------------------------------------------------
| FETCH EXISTING PROJECT
|--------------------------------------------------------------------------
*/

try {

    $portfolioRepo = repo('Portfolio');

    if (!$portfolioRepo) {

        die('Portfolio repository unavailable.');
    }

    $project = $portfolioRepo->findById($id);

    if (!$project) {

        die('Portfolio project not found.');
    }

    $title =
        (string) ($project['title'] ?? '');

    $category =
        (string) ($project['project_type'] ?? $project['category'] ?? '');

    $image =
        (string) ($project['featured_image'] ?? $project['image'] ?? '');

} catch (Throwable $e) {

    die($e->getMessage());
}

/*
|--------------------------------------------------------------------------
| UPDATE PROJECT
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
    | UPDATE QUERY
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $ok = $portfolioRepo->update($id, [
                'title'         => $title,
                'project_type'  => $category,
                'featured_image' => $image,
                'slug'          => strtolower(str_replace(' ', '-', $title)),
            ]);

            if ($ok) {

                $success =
                    'Portfolio project updated successfully.';

            } else {

                $error =
                    'Failed to update portfolio project.';
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
        Edit Portfolio Project
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
        Edit Portfolio Project
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

            Update Project

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