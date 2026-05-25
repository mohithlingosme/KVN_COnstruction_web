<?php

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

$message = '';
$error   = '';

$title       = '';
$description = '';
$image       = '';

/*
|--------------------------------------------------------------------------
| FORM SUBMIT
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title =
        trim($_POST['title'] ?? '');

    $description =
        trim($_POST['description'] ?? '');

    $image =
        trim($_POST['image'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        $title === '' ||
        $description === '' ||
        $image === ''
    ) {

        $error =
            'Please fill all fields.';
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT SERVICE
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $stmt = $conn->prepare(
                "
                INSERT INTO services
                (
                    title,
                    description,
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
                    'Database prepare failed.';

            } else {

                $stmt->bind_param(
                    'sss',
                    $title,
                    $description,
                    $image
                );

                if ($stmt->execute()) {

                    $message =
                        'Service created successfully.';

                    $title       = '';
                    $description = '';
                    $image       = '';

                } else {

                    $error =
                        'Failed to create service.';
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
        Create Service
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

        input,
        textarea{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:16px;
        }

        textarea{

            resize:vertical;

            min-height:140px;
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

            background:#d99c00;
        }

        .back{

            display:inline-block;

            margin-top:20px;

            text-decoration:none;

            color:#333;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>
        Create New Service
    </h1>

    <?php if ($message !== ''): ?>

        <div class="success">

            <?php echo htmlspecialchars($message); ?>

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
                Service Title
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
                Description
            </label>

            <textarea
                name="description"
                required
            ><?php echo htmlspecialchars($description); ?></textarea>

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

        </div>

        <button type="submit">

            Create Service

        </button>

    </form>

    <a
        href="../dashboard.php"
        class="back"
    >
        ← Back to Dashboard
    </a>

</div>

</body>

</html>