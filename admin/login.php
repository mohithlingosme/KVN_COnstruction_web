<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| DEVELOPMENT ERRORS
|--------------------------------------------------------------------------
*/

ini_set('display_errors', '1');
error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

session_start();

// Regenerate session ID on new session for security
if (!isset($_SESSION['_initialized'])) {
    session_regenerate_id(true);
    $_SESSION['_initialized'] = true;
}

/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/db.php';

/*
|--------------------------------------------------------------------------
| DEFAULT ERROR & SUCCESS
|--------------------------------------------------------------------------
*/

$error = '';
$success = '';

/*
|--------------------------------------------------------------------------
| REDIRECT IF ALREADY LOGGED IN
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| CSRF TOKEN GENERATION
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

/*
|--------------------------------------------------------------------------
| HANDLE LOGIN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | VERIFY CSRF TOKEN
    |--------------------------------------------------------------------------
    */

    $post_csrf = $_POST['csrf_token'] ?? '';
    if (empty($post_csrf) || !hash_equals($csrf_token, $post_csrf)) {
        $error = 'Security token mismatch. Please try again.';
    } else {

        /*
        |--------------------------------------------------------------------------
        | GET INPUTS
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
            $error = 'Please fill all fields.';
        } else {

            try {

                /*
                |--------------------------------------------------------------------------
                | PREPARE QUERY
                |--------------------------------------------------------------------------
                */

                $stmt = $conn->prepare(
                    "SELECT
                        id,
                        name,
                        email,
                        password
                     FROM admins
                     WHERE email = ?
                     LIMIT 1"
                );

                /*
                |--------------------------------------------------------------------------
                | CHECK QUERY
                |--------------------------------------------------------------------------
                */

                if (!$stmt) {
                    $error = 'Database query failed.';
                    error_log('Database prepare error: ' . $conn->error);
                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | BIND EMAIL
                    |--------------------------------------------------------------------------
                    */

                    $stmt->bind_param('s', $email);

                    /*
                    |--------------------------------------------------------------------------
                    | EXECUTE QUERY
                    |--------------------------------------------------------------------------
                    */

                    $stmt->execute();

                    /*
                    |--------------------------------------------------------------------------
                    | STORE RESULT
                    |--------------------------------------------------------------------------
                    */

                    $stmt->store_result();

                    /*
                    |--------------------------------------------------------------------------
                    | CHECK ACCOUNT
                    |--------------------------------------------------------------------------
                    */

                    if ($stmt->num_rows === 1) {

                        /*
                        |--------------------------------------------------------------------------
                        | BIND RESULT
                        |--------------------------------------------------------------------------
                        */

                        $stmt->bind_result(
                            $admin_id,
                            $admin_name,
                            $admin_email,
                            $admin_password
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | FETCH DATA
                        |--------------------------------------------------------------------------
                        */

                        $stmt->fetch();

                        /*
                        |--------------------------------------------------------------------------
                        | VERIFY PASSWORD
                        |--------------------------------------------------------------------------
                        */

                        if (password_verify($password, $admin_password)) {

                            /*
                            |--------------------------------------------------------------------------
                            | SECURE SESSION
                            |--------------------------------------------------------------------------
                            */

                            session_regenerate_id(true);

                            /*
                            |--------------------------------------------------------------------------
                            | STORE SESSION
                            |--------------------------------------------------------------------------
                            */

                            $_SESSION['admin_id'] = (int)$admin_id;
                            $_SESSION['admin_name'] = (string)$admin_name;
                            $_SESSION['admin_email'] = (string)$admin_email;
                            $_SESSION['login_time'] = time();

                            error_log("Admin login successful: {$admin_email}");

                            /*
                            |--------------------------------------------------------------------------
                            | REDIRECT
                            |--------------------------------------------------------------------------
                            */

                            header('Location: dashboard.php');
                            exit();

                        } else {
                            $error = 'Invalid email or password.';
                            error_log("Failed login attempt for: {$email}");
                        }

                    } else {
                        $error = 'Invalid email or password.';
                        error_log("Login attempt for non-existent email: {$email}");
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CLOSE STATEMENT
                    |--------------------------------------------------------------------------
                    */

                    $stmt->close();
                }

            } catch (Throwable $e) {
                $error = 'Login failed due to a system error.';
                error_log('Login exception: ' . $e->getMessage());
            }
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
        Admin Login
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

            min-height:100vh;

            display:flex;

            justify-content:center;

            align-items:center;
        }

        .login-box{

            width:100%;

            max-width:400px;

            background:#fff;

            padding:40px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        h2{

            margin-bottom:10px;

            color:#222;
        }

        p{

            color:#666;

            margin-bottom:25px;
        }

        input{

            width:100%;

            padding:14px;

            margin-bottom:20px;

            border:1px solid #ddd;

            border-radius:10px;

            outline:none;

            font-size:15px;
        }

        input:focus{

            border-color:#f5b400;
        }

        button{

            width:100%;

            padding:14px;

            background:#f5b400;

            border:none;

            color:#fff;

            border-radius:10px;

            font-weight:bold;

            font-size:15px;

            cursor:pointer;
        }

        button:hover{

            background:#d89d00;
        }

        .error{

            background:#ffe5e5;

            color:#d10000;

            padding:12px;

            border-radius:10px;

            margin-bottom:20px;
        }

    </style>

</head>

<body>

<div class="login-box">

    <h2>
        KVN Construction
    </h2>

    <p>
        Admin Panel Login
    </p>

    <?php if ($error !== '') : ?>

        <div class="error">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>

    <form method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($csrf_token); ?>"
        >

        <input
            type="email"
            name="email"
            placeholder="Email Address"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
        >

        <button type="submit">
            Login
        </button>

    </form>

</div>

</body>

</html>