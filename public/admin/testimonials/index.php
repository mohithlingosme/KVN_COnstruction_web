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
        Testimonials Management
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

            background:#f5b400;

            color:#fff;

            text-decoration:none;

            padding:14px 24px;

            border-radius:10px;

            font-weight:bold;
        }

        .btn:hover{

            background:#d89d00;
        }

        .grid{

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

            height:220px;

            object-fit:cover;
        }

        .card-content{

            padding:25px;
        }

        .client-role{

            color:#777;

            margin-bottom:15px;

            font-size:14px;
        }

        .message{

            color:#444;

            line-height:1.7;

            margin-bottom:20px;
        }

        .rating{

            color:#f5b400;

            font-size:18px;

            margin-bottom:20px;
        }

        .actions{

            display:flex;

            gap:15px;
        }

        .edit{

            background:#007bff;
        }

        .delete{

            background:#dc3545;
        }

        .edit:hover{

            background:#0069d9;
        }

        .delete:hover{

            background:#c82333;
        }

        .empty{

            background:#fff;

            padding:40px;

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
            Testimonials Management
        </h1>

        <a
            href="create.php"
            class="btn"
        >
            + Add Testimonial
        </a>

    </div>

    <?php if (count($testimonials) > 0): ?>

        <div class="grid">

            <?php foreach ($testimonials as $testimonial): ?>

                <div class="card">

                    <img
                        src="<?php echo htmlspecialchars((string)$testimonial['image']); ?>"
                        alt="Client"
                    >

                    <div class="card-content">

                        <h2>

                            <?php
                                echo htmlspecialchars(
                                    (string)$testimonial['client_name']
                                );
                            ?>

                        </h2>

                        <div class="client-role">

                            <?php
                                echo htmlspecialchars(
                                    (string)$testimonial['client_role']
                                );
                            ?>

                        </div>

                        <div class="message">

                            <?php
                                echo htmlspecialchars(
                                    (string)$testimonial['message']
                                );
                            ?>

                        </div>

                        <div class="rating">

                            <?php

                            $rating =
                                (int)$testimonial['rating'];

                            for ($i = 1; $i <= 5; $i++) {

                                echo $i <= $rating
                                    ? '★'
                                    : '☆';
                            }

                            ?>

                        </div>

                        <div class="actions">

                            <a
                                href="edit.php?id=<?php echo (int)$testimonial['id']; ?>"
                                class="btn edit"
                            >
                                Edit
                            </a>

                            <a
                                href="delete.php?id=<?php echo (int)$testimonial['id']; ?>"
                                class="btn delete"
                                onclick="return confirm('Delete this testimonial?')"
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
                No Testimonials Found
            </h2>

            <br>

            <p>
                Start by adding your first testimonial.
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