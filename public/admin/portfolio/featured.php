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
| FETCH FEATURED PROJECTS
|--------------------------------------------------------------------------
*/

$projects = [];

try {

    $query = "
        SELECT
            id,
            title,
            category,
            image,
            is_featured,
            created_at
        FROM portfolio
        ORDER BY id DESC
    ";

    $result = $conn->query($query);

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            $projects[] = $row;
        }
    }

} catch (Throwable $e) {

    die($e->getMessage());
}

/*
|--------------------------------------------------------------------------
| TOGGLE FEATURED
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['toggle']) &&
    isset($_GET['id'])
) {

    $id =
        (int) $_GET['id'];

    $toggle =
        (int) $_GET['toggle'];

    try {

        $stmt = $conn->prepare(
            "
            UPDATE portfolio
            SET is_featured = ?
            WHERE id = ?
            "
        );

        if ($stmt) {

            $stmt->bind_param(
                'ii',
                $toggle,
                $id
            );

            $stmt->execute();

            $stmt->close();
        }

        header('Location: featured.php');
        exit();

    } catch (Throwable $e) {

        die($e->getMessage());
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
        Featured Portfolio Projects
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

            max-width:1200px;

            margin:auto;
        }

        h1{

            margin-bottom:30px;

            color:#222;
        }

        table{

            width:100%;

            border-collapse:collapse;

            background:#fff;

            border-radius:20px;

            overflow:hidden;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        th{

            background:#f5b400;

            color:#fff;

            padding:18px;

            text-align:left;
        }

        td{

            padding:18px;

            border-bottom:1px solid #eee;

            vertical-align:middle;
        }

        tr:hover{

            background:#fafafa;
        }

        img{

            width:120px;

            height:80px;

            object-fit:cover;

            border-radius:10px;
        }

        .badge{

            display:inline-block;

            padding:8px 14px;

            border-radius:30px;

            font-size:13px;

            font-weight:bold;
        }

        .featured{

            background:#28a745;

            color:#fff;
        }

        .not-featured{

            background:#dc3545;

            color:#fff;
        }

        .btn{

            display:inline-block;

            padding:10px 18px;

            border-radius:10px;

            text-decoration:none;

            color:#fff;

            font-weight:bold;
        }

        .enable{

            background:#28a745;
        }

        .disable{

            background:#dc3545;
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
        Featured Portfolio Projects
    </h1>

    <table>

        <thead>

            <tr>

                <th>
                    Image
                </th>

                <th>
                    Title
                </th>

                <th>
                    Category
                </th>

                <th>
                    Status
                </th>

                <th>
                    Created
                </th>

                <th>
                    Action
                </th>

            </tr>

        </thead>

        <tbody>

            <?php if (count($projects) > 0): ?>

                <?php foreach ($projects as $project): ?>

                    <tr>

                        <td>

                            <img
                                src="<?php echo htmlspecialchars((string)$project['image']); ?>"
                                alt="Project"
                            >

                        </td>

                        <td>

                            <?php
                                echo htmlspecialchars(
                                    (string)$project['title']
                                );
                            ?>

                        </td>

                        <td>

                            <?php
                                echo htmlspecialchars(
                                    (string)$project['category']
                                );
                            ?>

                        </td>

                        <td>

                            <?php if ((int)$project['is_featured'] === 1): ?>

                                <span class="badge featured">

                                    Featured

                                </span>

                            <?php else: ?>

                                <span class="badge not-featured">

                                    Not Featured

                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <?php
                                echo htmlspecialchars(
                                    (string)$project['created_at']
                                );
                            ?>

                        </td>

                        <td>

                            <?php if ((int)$project['is_featured'] === 1): ?>

                                <a
                                    href="?toggle=0&id=<?php echo (int)$project['id']; ?>"
                                    class="btn disable"
                                >
                                    Remove
                                </a>

                            <?php else: ?>

                                <a
                                    href="?toggle=1&id=<?php echo (int)$project['id']; ?>"
                                    class="btn enable"
                                >
                                    Feature
                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="6">

                        No portfolio projects found.

                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

    <a
        href="index.php"
        class="back"
    >
        ← Back to Portfolio
    </a>

</div>

</body>

</html>