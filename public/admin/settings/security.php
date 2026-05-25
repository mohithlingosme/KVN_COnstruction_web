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
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once '../../includes/db.php';

/*
|--------------------------------------------------------------------------
| CREATE SECURITY TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS security_settings (

        id INT AUTO_INCREMENT PRIMARY KEY,

        admin_username VARCHAR(100) NOT NULL,

        admin_email VARCHAR(150) NOT NULL,

        admin_password VARCHAR(255) NOT NULL,

        session_timeout INT NOT NULL DEFAULT 30,

        login_attempt_limit INT NOT NULL DEFAULT 5,

        two_factor_auth ENUM('enabled','disabled')
        NOT NULL DEFAULT 'disabled',

        maintenance_lock ENUM('enabled','disabled')
        NOT NULL DEFAULT 'disabled',

        updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| INSERT DEFAULT SETTINGS
|--------------------------------------------------------------------------
*/

$check =
    $conn->query(
        "
        SELECT id
        FROM security_settings
        LIMIT 1
        "
    );

if (
    $check &&
    $check->num_rows === 0
) {

    $defaultPassword =
        password_hash(
            'admin123',
            PASSWORD_DEFAULT
        );

    $stmt =
        $conn->prepare(
            "
            INSERT INTO security_settings
            (

                admin_username,
                admin_email,
                admin_password,

                session_timeout,
                login_attempt_limit,

                two_factor_auth,
                maintenance_lock

            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?
            )
            "
        );

    if ($stmt) {

        $username =
            'admin';

        $email =
            'admin@kvnconstruction.com';

        $sessionTimeout =
            30;

        $loginLimit =
            5;

        $twoFactor =
            'disabled';

        $maintenanceLock =
            'disabled';

        $stmt->bind_param(
            'sssisss',
            $username,
            $email,
            $defaultPassword,
            $sessionTimeout,
            $loginLimit,
            $twoFactor,
            $maintenanceLock
        );

        $stmt->execute();

        $stmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';

/*
|--------------------------------------------------------------------------
| UPDATE SECURITY SETTINGS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $adminUsername =
        trim($_POST['admin_username'] ?? '');

    $adminEmail =
        trim($_POST['admin_email'] ?? '');

    $newPassword =
        trim($_POST['new_password'] ?? '');

    $confirmPassword =
        trim($_POST['confirm_password'] ?? '');

    $sessionTimeout =
        (int) ($_POST['session_timeout'] ?? 30);

    $loginAttemptLimit =
        (int) ($_POST['login_attempt_limit'] ?? 5);

    $twoFactor =
        trim($_POST['two_factor_auth'] ?? 'disabled');

    $maintenanceLock =
        trim($_POST['maintenance_lock'] ?? 'disabled');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        $adminUsername === '' ||
        $adminEmail === ''

    ) {

        $error =
            'Please fill all required fields.';
    }

    /*
    |--------------------------------------------------------------------------
    | PASSWORD VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        $newPassword !== '' &&
        $newPassword !== $confirmPassword

    ) {

        $error =
            'Passwords do not match.';
    }

    /*
    |--------------------------------------------------------------------------
    | FETCH CURRENT PASSWORD
    |--------------------------------------------------------------------------
    */

    $currentPassword = '';

    $result =
        $conn->query(
            "
            SELECT admin_password
            FROM security_settings
            WHERE id = 1
            LIMIT 1
            "
        );

    if (
        $result &&
        $result->num_rows > 0
    ) {

        $row =
            $result->fetch_assoc();

        $currentPassword =
            (string) $row['admin_password'];
    }

    /*
    |--------------------------------------------------------------------------
    | HASH NEW PASSWORD
    |--------------------------------------------------------------------------
    */

    if ($newPassword !== '') {

        $currentPassword =
            password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE DATABASE
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $stmt =
                $conn->prepare(
                    "
                    UPDATE security_settings
                    SET

                        admin_username     = ?,
                        admin_email        = ?,
                        admin_password     = ?,

                        session_timeout    = ?,
                        login_attempt_limit = ?,

                        two_factor_auth    = ?,
                        maintenance_lock   = ?

                    WHERE id = 1
                    "
                );

            if ($stmt) {

                $stmt->bind_param(
                    'sssisss',
                    $adminUsername,
                    $adminEmail,
                    $currentPassword,
                    $sessionTimeout,
                    $loginAttemptLimit,
                    $twoFactor,
                    $maintenanceLock
                );

                $stmt->execute();

                $stmt->close();

                $success =
                    'Security settings updated successfully.';
            }

        } catch (Throwable $e) {

            $error =
                $e->getMessage();
        }
    }
}

/*
|--------------------------------------------------------------------------
| FETCH SETTINGS
|--------------------------------------------------------------------------
*/

$data = [

    'admin_username'      => '',
    'admin_email'         => '',

    'session_timeout'     => 30,
    'login_attempt_limit' => 5,

    'two_factor_auth'     => 'disabled',
    'maintenance_lock'    => 'disabled'

];

$result =
    $conn->query(
        "
        SELECT *
        FROM security_settings
        LIMIT 1
        "
    );

if (
    $result &&
    $result->num_rows > 0
) {

    $data =
        $result->fetch_assoc();
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
        Security Settings
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

            background:#fff;

            padding:40px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        h1{

            margin-bottom:35px;

            color:#222;
        }

        h2{

            margin-bottom:20px;

            color:#444;
        }

        .section{

            margin-bottom:40px;
        }

        .form-group{

            margin-bottom:20px;
        }

        label{

            display:block;

            margin-bottom:10px;

            font-weight:bold;

            color:#333;
        }

        input,
        select{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:15px;
        }

        button{

            width:100%;

            padding:16px;

            border:none;

            border-radius:10px;

            background:#f5b400;

            color:#fff;

            font-size:16px;

            font-weight:bold;

            cursor:pointer;

            transition:0.3s;
        }

        button:hover{

            background:#d99f00;
        }

        .alert{

            padding:15px 20px;

            border-radius:10px;

            margin-bottom:25px;

            font-weight:bold;
        }

        .success{

            background:#e7f9ed;

            color:#1e7e34;
        }

        .error{

            background:#ffe5e5;

            color:#d8000c;
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

    <h1>
        Security Settings
    </h1>

    <?php if ($success !== ''): ?>

        <div class="alert success">

            <?php
                echo htmlspecialchars($success);
            ?>

        </div>

    <?php endif; ?>

    <?php if ($error !== ''): ?>

        <div class="alert error">

            <?php
                echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>

    <form method="POST">

        <!-- ADMIN -->

        <div class="section">

            <h2>
                Admin Credentials
            </h2>

            <div class="form-group">

                <label>
                    Admin Username
                </label>

                <input
                    type="text"
                    name="admin_username"
                    value="<?php echo htmlspecialchars((string)$data['admin_username']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Admin Email
                </label>

                <input
                    type="email"
                    name="admin_email"
                    value="<?php echo htmlspecialchars((string)$data['admin_email']); ?>"
                    required
                >

            </div>

        </div>

        <!-- PASSWORD -->

        <div class="section">

            <h2>
                Change Password
            </h2>

            <div class="form-group">

                <label>
                    New Password
                </label>

                <input
                    type="password"
                    name="new_password"
                >

            </div>

            <div class="form-group">

                <label>
                    Confirm Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                >

            </div>

        </div>

        <!-- LOGIN -->

        <div class="section">

            <h2>
                Login Security
            </h2>

            <div class="form-group">

                <label>
                    Session Timeout (Minutes)
                </label>

                <input
                    type="number"
                    name="session_timeout"
                    value="<?php echo (int)$data['session_timeout']; ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Login Attempt Limit
                </label>

                <input
                    type="number"
                    name="login_attempt_limit"
                    value="<?php echo (int)$data['login_attempt_limit']; ?>"
                    required
                >

            </div>

        </div>

        <!-- EXTRA -->

        <div class="section">

            <h2>
                Advanced Security
            </h2>

            <div class="form-group">

                <label>
                    Two Factor Authentication
                </label>

                <select name="two_factor_auth">

                    <option
                        value="enabled"
                        <?php echo $data['two_factor_auth'] === 'enabled' ? 'selected' : ''; ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo $data['two_factor_auth'] === 'disabled' ? 'selected' : ''; ?>
                    >
                        Disabled
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>
                    Maintenance Lock
                </label>

                <select name="maintenance_lock">

                    <option
                        value="enabled"
                        <?php echo $data['maintenance_lock'] === 'enabled' ? 'selected' : ''; ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo $data['maintenance_lock'] === 'disabled' ? 'selected' : ''; ?>
                    >
                        Disabled
                    </option>

                </select>

            </div>

        </div>

        <button type="submit">

            Save Security Settings

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