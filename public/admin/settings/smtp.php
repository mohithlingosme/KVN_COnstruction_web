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
| CREATE SMTP TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS smtp_settings (

        id INT AUTO_INCREMENT PRIMARY KEY,

        smtp_host VARCHAR(255) NOT NULL,

        smtp_port INT NOT NULL,

        smtp_username VARCHAR(255) NOT NULL,

        smtp_password VARCHAR(255) NOT NULL,

        smtp_encryption ENUM('tls','ssl','none')
        NOT NULL DEFAULT 'tls',

        from_email VARCHAR(255) NOT NULL,

        from_name VARCHAR(255) NOT NULL,

        reply_to_email VARCHAR(255) NOT NULL,

        mail_driver ENUM('smtp','mail')
        NOT NULL DEFAULT 'smtp',

        smtp_status ENUM('enabled','disabled')
        NOT NULL DEFAULT 'enabled',

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
        FROM smtp_settings
        LIMIT 1
        "
    );

if (
    $check &&
    $check->num_rows === 0
) {

    $stmt =
        $conn->prepare(
            "
            INSERT INTO smtp_settings
            (

                smtp_host,
                smtp_port,

                smtp_username,
                smtp_password,

                smtp_encryption,

                from_email,
                from_name,

                reply_to_email,

                mail_driver,
                smtp_status

            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
            "
        );

    if ($stmt) {

        $smtpHost =
            'smtp.gmail.com';

        $smtpPort =
            587;

        $smtpUsername =
            'your-email@gmail.com';

        $smtpPassword =
            'your-app-password';

        $smtpEncryption =
            'tls';

        $fromEmail =
            'your-email@gmail.com';

        $fromName =
            'KVN Construction';

        $replyTo =
            'support@kvnconstruction.com';

        $mailDriver =
            'smtp';

        $smtpStatus =
            'enabled';

        $stmt->bind_param(
            'sissssssss',
            $smtpHost,
            $smtpPort,
            $smtpUsername,
            $smtpPassword,
            $smtpEncryption,
            $fromEmail,
            $fromName,
            $replyTo,
            $mailDriver,
            $smtpStatus
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
| UPDATE SETTINGS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $smtpHost =
        trim($_POST['smtp_host'] ?? '');

    $smtpPort =
        (int) ($_POST['smtp_port'] ?? 587);

    $smtpUsername =
        trim($_POST['smtp_username'] ?? '');

    $smtpPassword =
        trim($_POST['smtp_password'] ?? '');

    $smtpEncryption =
        trim($_POST['smtp_encryption'] ?? 'tls');

    $fromEmail =
        trim($_POST['from_email'] ?? '');

    $fromName =
        trim($_POST['from_name'] ?? '');

    $replyTo =
        trim($_POST['reply_to_email'] ?? '');

    $mailDriver =
        trim($_POST['mail_driver'] ?? 'smtp');

    $smtpStatus =
        trim($_POST['smtp_status'] ?? 'enabled');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        $smtpHost === '' ||
        $smtpPort <= 0 ||
        $smtpUsername === '' ||
        $smtpPassword === '' ||
        $fromEmail === '' ||
        $fromName === '' ||
        $replyTo === ''

    ) {

        $error =
            'Please fill all required fields.';
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
                    UPDATE smtp_settings
                    SET

                        smtp_host       = ?,
                        smtp_port       = ?,

                        smtp_username   = ?,
                        smtp_password   = ?,

                        smtp_encryption = ?,

                        from_email      = ?,
                        from_name       = ?,

                        reply_to_email  = ?,

                        mail_driver     = ?,
                        smtp_status     = ?

                    WHERE id = 1
                    "
                );

            if ($stmt) {

                $stmt->bind_param(
                    'sissssssss',
                    $smtpHost,
                    $smtpPort,
                    $smtpUsername,
                    $smtpPassword,
                    $smtpEncryption,
                    $fromEmail,
                    $fromName,
                    $replyTo,
                    $mailDriver,
                    $smtpStatus
                );

                $stmt->execute();

                $stmt->close();

                $success =
                    'SMTP settings updated successfully.';
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

    'smtp_host'       => '',
    'smtp_port'       => 587,

    'smtp_username'   => '',
    'smtp_password'   => '',

    'smtp_encryption' => 'tls',

    'from_email'      => '',
    'from_name'       => '',

    'reply_to_email'  => '',

    'mail_driver'     => 'smtp',

    'smtp_status'     => 'enabled'

];

$result =
    $conn->query(
        "
        SELECT *
        FROM smtp_settings
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
        SMTP Settings
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
        SMTP Settings
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

        <!-- SMTP -->

        <div class="section">

            <h2>
                SMTP Configuration
            </h2>

            <div class="form-group">

                <label>
                    SMTP Host
                </label>

                <input
                    type="text"
                    name="smtp_host"
                    value="<?php echo htmlspecialchars((string)$data['smtp_host']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    SMTP Port
                </label>

                <input
                    type="number"
                    name="smtp_port"
                    value="<?php echo (int)$data['smtp_port']; ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    SMTP Username
                </label>

                <input
                    type="text"
                    name="smtp_username"
                    value="<?php echo htmlspecialchars((string)$data['smtp_username']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    SMTP Password
                </label>

                <input
                    type="password"
                    name="smtp_password"
                    value="<?php echo htmlspecialchars((string)$data['smtp_password']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Encryption
                </label>

                <select name="smtp_encryption">

                    <option
                        value="tls"
                        <?php echo $data['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>
                    >
                        TLS
                    </option>

                    <option
                        value="ssl"
                        <?php echo $data['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>
                    >
                        SSL
                    </option>

                    <option
                        value="none"
                        <?php echo $data['smtp_encryption'] === 'none' ? 'selected' : ''; ?>
                    >
                        None
                    </option>

                </select>

            </div>

        </div>

        <!-- EMAIL -->

        <div class="section">

            <h2>
                Sender Information
            </h2>

            <div class="form-group">

                <label>
                    From Email
                </label>

                <input
                    type="email"
                    name="from_email"
                    value="<?php echo htmlspecialchars((string)$data['from_email']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    From Name
                </label>

                <input
                    type="text"
                    name="from_name"
                    value="<?php echo htmlspecialchars((string)$data['from_name']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Reply To Email
                </label>

                <input
                    type="email"
                    name="reply_to_email"
                    value="<?php echo htmlspecialchars((string)$data['reply_to_email']); ?>"
                    required
                >

            </div>

        </div>

        <!-- MAIL -->

        <div class="section">

            <h2>
                Mail Settings
            </h2>

            <div class="form-group">

                <label>
                    Mail Driver
                </label>

                <select name="mail_driver">

                    <option
                        value="smtp"
                        <?php echo $data['mail_driver'] === 'smtp' ? 'selected' : ''; ?>
                    >
                        SMTP
                    </option>

                    <option
                        value="mail"
                        <?php echo $data['mail_driver'] === 'mail' ? 'selected' : ''; ?>
                    >
                        PHP Mail
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>
                    SMTP Status
                </label>

                <select name="smtp_status">

                    <option
                        value="enabled"
                        <?php echo $data['smtp_status'] === 'enabled' ? 'selected' : ''; ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo $data['smtp_status'] === 'disabled' ? 'selected' : ''; ?>
                    >
                        Disabled
                    </option>

                </select>

            </div>

        </div>

        <button type="submit">

            Save SMTP Settings

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