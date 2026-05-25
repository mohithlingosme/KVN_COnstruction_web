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
| HANDLE APPROVAL ACTION
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['action']) &&
    isset($_GET['id'])
) {

    $id =
        (int) $_GET['id'];

    $action =
        trim($_GET['action']);

    /*
    |--------------------------------------------------------------------------
    | VALID ACTIONS
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'approve' ||
        $action === 'reject'
    ) {

        $status =
            $action === 'approve'
            ? 'approved'
            : 'rejected';

        try {

            $stmt = $conn->prepare(
                "
                UPDATE testimonials
                SET status = ?
                WHERE id = ?
                "
            );

            if ($stmt) {

                $stmt->bind_param(
                    'si',
                    $status,
                    $id
                );

                $stmt->execute();

                $stmt->close();
            }

        } catch (Throwable $e) {

            die($e->getMessage());
        }
    }

    header('Location: approvals.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH TESTIMONIALS
|--------------------------------------------------------------------------
*/

$testimonials = [];

try {

    $query = "
        SELECT
            id,
            client_name,
            client_role,
            message,
            rating,
            image,
            status,
            created_at
        FROM testimonials
        ORDER BY id DESC
    ";

    $result = $conn->query($query);

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            $testimonials[] = $row;
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
        Testimonial Approvals
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

            width:80px;

            height:80px;

            object-fit:cover;

            border-radius:50%;
        }

        .message{

            max-width:350px;

            line-height:1.6;

            color:#444;
        }

        .rating{

            color:#f5b400;

            font-size:18px;
        }

        .badge{

            display:inline-block;

            padding:8px 14px;

            border-radius:30px;

            font-size:13px;

            font-weight:bold;
        }

        .pending{

            background:#ffc107;

            color:#222;
        }

        .approved{

            background:#28a745;

            color:#fff;
        }

        .rejected{

            background:#dc3545;

            color:#fff;
        }

        .actions{

            display:flex;

            gap:10px;

            flex-wrap:wrap;
        }

        .btn{

            display:inline-block;

            padding:10px 16px;

            border-radius:10px;

            text-decoration:none;

            color:#fff;

            font-weight:bold;

            font-size:14px;
        }

        .approve{

            background:#28a745;
        }

        .reject{

            background:#dc3545;
        }

        .approve:hover{

            background:#218838;
        }

        .reject:hover{

            background:#c82333;
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
        Testimonial Approvals
    </h1>

    <table>

        <thead>

            <tr>

                <th>
                    Client
                </th>

                <th>
                    Name
                </th>

                <th>
                    Role
                </th>

                <th>
                    Testimonial
                </th>

                <th>
                    Rating
                </th>

                <th>
                    Status
                </th>

                <th>
                    Action
                </th>

            </tr>

        </thead>

        <tbody>

            <?php if (count($testimonials) > 0): ?>

                <?php foreach ($testimonials as $testimonial): ?>

                    <tr>

                        <td>

                            <img
                                src="<?php echo htmlspecialchars((string)$testimonial['image']); ?>"
                                alt="Client"
                            >

                        </td>

                        <td>

                            <?php
                                echo htmlspecialchars(
                                    (string)$testimonial['client_name']
                                );
                            ?>

                        </td>

                        <td>

                            <?php
                                echo htmlspecialchars(
                                    (string)$testimonial['client_role']
                                );
                            ?>

                        </td>

                        <td class="message">

                            <?php
                                echo htmlspecialchars(
                                    (string)$testimonial['message']
                                );
                            ?>

                        </td>

                        <td class="rating">

                            <?php

                            $rating =
                                (int)$testimonial['rating'];

                            for ($i = 1; $i <= 5; $i++) {

                                echo $i <= $rating
                                    ? '★'
                                    : '☆';
                            }

                            ?>

                        </td>

                        <td>

                            <?php

                            $status =
                                (string)$testimonial['status'];

                            ?>

                            <span class="badge <?php echo htmlspecialchars($status); ?>">

                                <?php echo ucfirst($status); ?>

                            </span>

                        </td>

                        <td>

                            <div class="actions">

                                <a
                                    href="?action=approve&id=<?php echo (int)$testimonial['id']; ?>"
                                    class="btn approve"
                                >
                                    Approve
                                </a>

                                <a
                                    href="?action=reject&id=<?php echo (int)$testimonial['id']; ?>"
                                    class="btn reject"
                                >
                                    Reject
                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="7">

                        No testimonials found.

                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

    <a
        href="index.php"
        class="back"
    >
        ← Back to Testimonials
    </a>

</div>

</body>

</html>