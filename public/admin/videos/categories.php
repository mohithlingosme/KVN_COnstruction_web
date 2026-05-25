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
| CREATE CATEGORY
|--------------------------------------------------------------------------
*/

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name =
        trim($_POST['name'] ?? '');

    $slug =
        trim($_POST['slug'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | AUTO GENERATE SLUG
    |--------------------------------------------------------------------------
    */

    if ($slug === '') {

        $slug = strtolower($name);

        $slug = preg_replace(
            '/[^a-z0-9]+/',
            '-',
            $slug
        );

        $slug = trim($slug, '-');
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($name === '') {

        $error =
            'Category name is required.';

    } else {

        try {

            $stmt = $conn->prepare(
                "
                INSERT INTO video_categories
                (
                    name,
                    slug
                )
                VALUES
                (
                    ?, ?
                )
                "
            );

            if ($stmt) {

                $stmt->bind_param(
                    'ss',
                    $name,
                    $slug
                );

                $stmt->execute();

                $stmt->close();

                $success =
                    'Category added successfully.';

                $name = '';
                $slug = '';

            } else {

                $error =
                    'Database query failed.';
            }

        } catch (Throwable $e) {

            $error =
                $e->getMessage();
        }
    }
}

/*
|--------------------------------------------------------------------------
| DELETE CATEGORY
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id =
        (int) $_GET['delete'];

    try {

        $stmt = $conn->prepare(
            "
            DELETE FROM video_categories
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

    header('Location: categories.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH CATEGORIES
|--------------------------------------------------------------------------
*/

$categories = [];

try {

    $query = "
        SELECT
            id,
            name,
            slug,
            created_at
        FROM video_categories
        ORDER BY id DESC
    ";

    $result = $conn->query($query);

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            $categories[] = $row;
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
        Video Categories
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

            max-width:1100px;

            margin:auto;
        }

        .grid{

            display:grid;

            grid-template-columns:
                400px 1fr;

            gap:30px;
        }

        .card{

            background:#fff;

            border-radius:20px;

            padding:30px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        h1{

            margin-bottom:30px;

            color:#222;
        }

        h2{

            margin-bottom:25px;

            color:#222;
        }

        .alert{

            padding:14px 18px;

            border-radius:10px;

            margin-bottom:20px;

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

            margin-bottom:20px;
        }

        label{

            display:block;

            margin-bottom:8px;

            font-weight:bold;
        }

        input{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;
        }

        button{

            width:100%;

            padding:15px;

            border:none;

            border-radius:10px;

            background:#f5b400;

            color:#fff;

            font-size:16px;

            font-weight:bold;

            cursor:pointer;
        }

        button:hover{

            background:#d89d00;
        }

        table{

            width:100%;

            border-collapse:collapse;
        }

        th{

            background:#f5b400;

            color:#fff;

            padding:16px;

            text-align:left;
        }

        td{

            padding:16px;

            border-bottom:1px solid #eee;
        }

        .badge{

            display:inline-block;

            padding:8px 14px;

            background:#222;

            color:#fff;

            border-radius:30px;

            font-size:13px;
        }

        .delete{

            display:inline-block;

            padding:10px 14px;

            background:#dc3545;

            color:#fff;

            border-radius:10px;

            text-decoration:none;

            font-size:14px;

            font-weight:bold;
        }

        .delete:hover{

            background:#c82333;
        }

        .back{

            display:inline-block;

            margin-top:25px;

            text-decoration:none;

            color:#333;

            font-weight:bold;
        }

        @media(max-width:900px){

            .grid{

                grid-template-columns:1fr;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <h1>
        Video Categories
    </h1>

    <div class="grid">

        <div class="card">

            <h2>
                Add Category
            </h2>

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
                        Category Name *
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="<?php echo htmlspecialchars($name ?? ''); ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Slug
                    </label>

                    <input
                        type="text"
                        name="slug"
                        value="<?php echo htmlspecialchars($slug ?? ''); ?>"
                        placeholder="auto-generated"
                    >

                </div>

                <button type="submit">

                    Add Category

                </button>

            </form>

        </div>

        <div class="card">

            <h2>
                Existing Categories
            </h2>

            <?php if (count($categories) > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>
                                Name
                            </th>

                            <th>
                                Slug
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($categories as $category): ?>

                            <tr>

                                <td>

                                    <?php
                                        echo htmlspecialchars(
                                            (string)$category['name']
                                        );
                                    ?>

                                </td>

                                <td>

                                    <span class="badge">

                                        <?php
                                            echo htmlspecialchars(
                                                (string)$category['slug']
                                            );
                                        ?>

                                    </span>

                                </td>

                                <td>

                                    <a
                                        href="?delete=<?php echo (int)$category['id']; ?>"
                                        class="delete"
                                        onclick="return confirm('Delete this category?')"
                                    >
                                        Delete
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <p>
                    No categories found.
                </p>

            <?php endif; ?>

        </div>

    </div>

    <a
        href="index.php"
        class="back"
    >
        ← Back to Videos
    </a>

</div>

</body>

</html>