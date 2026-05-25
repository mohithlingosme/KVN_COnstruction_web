<?php

declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['client_id'])) {

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
| CREATE TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS clients (

        id INT AUTO_INCREMENT PRIMARY KEY,

        full_name VARCHAR(255) NOT NULL,

        email VARCHAR(255) NOT NULL UNIQUE,

        password VARCHAR(255) NOT NULL,

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| CLIENT DETAILS
|--------------------------------------------------------------------------
*/

$clientId =
    (int) $_SESSION['client_id'];

$clientName =
    $_SESSION['client_name'] ?? 'Client';

/*
|--------------------------------------------------------------------------
| FETCH CLIENT
|--------------------------------------------------------------------------
*/

$stmt =
    $conn->prepare(
        "
        SELECT *
        FROM clients
        WHERE id = ?
        LIMIT 1
        "
    );

$stmt->bind_param(
    'i',
    $clientId
);

$stmt->execute();

$result =
    $stmt->get_result();

$client =
    $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| HANDLE PASSWORD UPDATE
|--------------------------------------------------------------------------
*/

$successMessage = '';
$errorMessage = '';

if (
    $_SERVER['REQUEST_METHOD']
    === 'POST'
) {

    $currentPassword =
        trim($_POST['current_password'] ?? '');

    $newPassword =
        trim($_POST['new_password'] ?? '');

    $confirmPassword =
        trim($_POST['confirm_password'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($currentPassword) ||
        empty($newPassword) ||
        empty($confirmPassword)
    ) {

        $errorMessage =
            'Please fill all fields.';
    }
    elseif (
        strlen($newPassword) < 6
    ) {

        $errorMessage =
            'New password must be at least 6 characters.';
    }
    elseif (
        $newPassword !== $confirmPassword
    ) {

        $errorMessage =
            'New password and confirm password do not match.';
    }
    elseif (
        !password_verify(
            $currentPassword,
            $client['password']
        )
    ) {

        $errorMessage =
            'Current password is incorrect.';
    }
    else {

        /*
        |--------------------------------------------------------------------------
        | UPDATE PASSWORD
        |--------------------------------------------------------------------------
        */

        $hashedPassword =
            password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );

        $stmt =
            $conn->prepare(
                "
                UPDATE clients
                SET password = ?
                WHERE id = ?
                "
            );

        $stmt->bind_param(
            'si',
            $hashedPassword,
            $clientId
        );

        if ($stmt->execute()) {

            $successMessage =
                'Password updated successfully.';
        }
        else {

            $errorMessage =
                'Failed to update password.';
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
        Change Password
    </title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            font-family:Arial,sans-serif;

            background:#f3f4f6;

            color:#222;
        }

        .sidebar{

            width:260px;

            height:100vh;

            background:#111827;

            position:fixed;

            top:0;

            left:0;

            padding:30px 20px;

            overflow:auto;
        }

        .sidebar h2{

            color:#f5b400;

            margin-bottom:35px;
        }

        .sidebar a{

            display:block;

            text-decoration:none;

            color:#fff;

            padding:14px 16px;

            border-radius:10px;

            margin-bottom:10px;

            transition:0.3s;
        }

        .sidebar a:hover,
        .sidebar .active{

            background:#f5b400;

            color:#111;
        }

        .main{

            margin-left:260px;

            padding:40px;
        }

        .topbar{

            display:flex;

            justify-content:space-between;

            align-items:center;

            flex-wrap:wrap;

            margin-bottom:35px;
        }

        .logout-btn{

            text-decoration:none;

            background:#dc3545;

            color:#fff;

            padding:12px 18px;

            border-radius:10px;

            font-weight:bold;
        }

        .password-card{

            background:#fff;

            max-width:700px;

            padding:35px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .password-card h2{

            margin-bottom:25px;
        }

        .form-group{

            margin-bottom:22px;
        }

        .form-group label{

            display:block;

            margin-bottom:8px;

            font-weight:bold;
        }

        .form-group input{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:15px;
        }

        .submit-btn{

            background:#111827;

            color:#fff;

            border:none;

            padding:15px 25px;

            border-radius:10px;

            font-size:16px;

            font-weight:bold;

            cursor:pointer;
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

        .tips{

            background:#fff3cd;

            color:#856404;

            padding:18px;

            border-radius:12px;

            margin-bottom:25px;

            line-height:1.7;
        }

        @media(max-width:992px){

            .sidebar{

                width:100%;

                height:auto;

                position:relative;
            }

            .main{

                margin-left:0;
            }
        }

    </style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <h2>
        KVN Client
    </h2>

    <a href="../dashboard.php">
        Dashboard
    </a>

    <a href="index.php">
        My Profile
    </a>

    <a
        href="password.php"
        class="active"
    >
        Change Password
    </a>

    <a href="../projects/index.php">
        Projects
    </a>

    <a href="../support/tickets.php">
        Support
    </a>

    <a href="../logout.php">
        Logout
    </a>

</div>

<!-- MAIN -->

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <div>

            <h1>
                Change Password
            </h1>

            <p>

                Welcome,
                <?php
                    echo htmlspecialchars(
                        (string)$clientName
                    );
                ?>

            </p>

        </div>

        <a
            href="../logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

    <!-- PASSWORD CARD -->

    <div class="password-card">

        <div class="tips">

            Use a strong password with a mix of
            uppercase letters, lowercase letters,
            numbers, and symbols for better security.

        </div>

        <h2>
            Update Your Password
        </h2>

        <!-- ALERTS -->

        <?php if (!empty($successMessage)): ?>

            <div class="success">
                <?php echo $successMessage; ?>
            </div>

        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>

            <div class="error">
                <?php echo $errorMessage; ?>
            </div>

        <?php endif; ?>

        <!-- FORM -->

        <form method="POST">

            <div class="form-group">

                <label>
                    Current Password
                </label>

                <input
                    type="password"
                    name="current_password"
                    placeholder="Enter current password"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    New Password
                </label>

                <input
                    type="password"
                    name="new_password"
                    placeholder="Enter new password"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Confirm New Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm new password"
                    required
                >

            </div>

            <button
                type="submit"
                class="submit-btn"
            >
                Update Password
            </button>

        </form>

    </div>

</div>

</body>

</html>