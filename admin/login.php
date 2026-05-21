<?php

declare(strict_types=1);
ini_set('display_errors', 1);
error_reporting(E_ALL);
/*
|--------------------------------------------------------------------------
| SESSION SECURITY
|--------------------------------------------------------------------------
*/

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

session_start();

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/includes/db.php";

/*
|--------------------------------------------------------------------------
| REDIRECT IF ALREADY LOGGED IN
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['admin_id'])) {

    header("Location: dashboard.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| ERROR VARIABLE
|--------------------------------------------------------------------------
*/

$error = "";

/*
|--------------------------------------------------------------------------
| HANDLE LOGIN
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    |--------------------------------------------------------------------------
    | GET FORM DATA
    |--------------------------------------------------------------------------
    */

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($email === '' || $password === '') {

        $error = "Please fill all fields.";

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | FIND ADMIN
            |--------------------------------------------------------------------------
            */
            $stmt = $conn->prepare(
    "SELECT id, name, email, password
     FROM admins
     WHERE email = ?
     LIMIT 1"
);

if (!$stmt) {

    $error = "Database query failed.";

} else {

    /*
    |--------------------------------------------------------------------------
    | BIND EMAIL
    |--------------------------------------------------------------------------
    */

    $stmt->bind_param(
        "s",
        $email
    );

    /*
    |--------------------------------------------------------------------------
    | EXECUTE QUERY
    |--------------------------------------------------------------------------
    */

    $stmt->execute();

    /*
    |--------------------------------------------------------------------------
    | GET RESULT
    |--------------------------------------------------------------------------
    */

    $result = $stmt->get_result();

    /*
    |--------------------------------------------------------------------------
    | CHECK ADMIN EXISTS
    |--------------------------------------------------------------------------
    */

    if ($result->num_rows === 1) {

        $admin = $result->fetch_assoc();

        /*
        |--------------------------------------------------------------------------
        | VERIFY PASSWORD
        |--------------------------------------------------------------------------
        */

        if (
            password_verify(
                $password,
                $admin['password']
            )
        ) {

            /*
            |--------------------------------------------------------------------------
            | REGENERATE SESSION
            |--------------------------------------------------------------------------
            */

            session_regenerate_id(true);

            /*
            |--------------------------------------------------------------------------
            | STORE SESSION
            |--------------------------------------------------------------------------
            */

            $_SESSION['admin_id'] =
                (int)$admin['id'];

            $_SESSION['admin_name'] =
                (string)$admin['name'];

            $_SESSION['admin_email'] =
                (string)$admin['email'];

            /*
            |--------------------------------------------------------------------------
            | REDIRECT
            |--------------------------------------------------------------------------
            */

            header(
                "Location: dashboard.php"
            );

            exit();

        } else {

            $error = "Invalid password.";
        }

    } else {

        $error = "Admin account not found.";
    }

    /*
    |--------------------------------------------------------------------------
    | CLOSE STATEMENT
    |--------------------------------------------------------------------------
    */

    $stmt->close();
}
           
        } catch (Throwable $e) {

            $error =
                "Login failed: " .
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
        Admin Login | KVN Construction
    </title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            font-family:Arial,sans-serif;

            background:#f5f7fb;

            height:100vh;

            display:flex;

            justify-content:center;

            align-items:center;
        }

        .login-box{

            width:100%;

            max-width:420px;

            background:#fff;

            padding:40px;

            border-radius:20px;

            box-shadow:
                0 10px 40px rgba(0,0,0,0.08);
        }

        .logo{

            text-align:center;

            margin-bottom:25px;
        }

        .logo h1{

            font-size:28px;

            color:#222;
        }

        .logo p{

            margin-top:8px;

            color:#777;

            font-size:14px;
        }

        .error{

            background:#ffe5e5;

            color:#d10000;

            padding:12px;

            border-radius:10px;

            margin-bottom:20px;

            font-size:14px;
        }

        .input-group{

            margin-bottom:20px;
        }

        .input-group label{

            display:block;

            margin-bottom:8px;

            font-size:14px;

            font-weight:600;

            color:#333;
        }

        .input-group input{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:12px;

            font-size:15px;

            outline:none;
        }

        .input-group input:focus{

            border-color:#f5b400;
        }

        button{

            width:100%;

            padding:15px;

            background:#f5b400;

            color:#fff;

            border:none;

            border-radius:12px;

            font-size:16px;

            font-weight:bold;

            cursor:pointer;
        }

        button:hover{

            background:#d99d00;
        }

        .footer{

            margin-top:20px;

            text-align:center;

            font-size:13px;

            color:#777;
        }

    </style>

</head>

<body>

<div class="login-box">

    <div class="logo">

        <h1>
            KVN Construction
        </h1>

        <p>
            Admin Panel Login
        </p>

    </div>

    <?php if ($error !== "") : ?>

        <div class="error">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>

    <form method="POST">

        <div class="input-group">

            <label>
                Email Address
            </label>

            <input
                type="email"
                name="email"
                required
            >

        </div>

        <div class="input-group">

            <label>
                Password
            </label>

            <input
                type="password"
                name="password"
                required
            >

        </div>

        <button type="submit">

            Login

        </button>

    </form>

    <div class="footer">

        © <?php echo date('Y'); ?>
        KVN Construction

    </div>

</div>

</body>

</html>