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
| CREATE SMS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS sms_settings (

        id INT AUTO_INCREMENT PRIMARY KEY,

        sms_provider VARCHAR(100) NOT NULL,

        api_key VARCHAR(255) NOT NULL,

        sender_id VARCHAR(100) NOT NULL,

        auth_token VARCHAR(255) NOT NULL,

        api_url VARCHAR(255) NOT NULL,

        admin_mobile VARCHAR(20) NOT NULL,

        sms_status ENUM('enabled','disabled')
        NOT NULL DEFAULT 'enabled',

        notify_contact_form ENUM('yes','no')
        NOT NULL DEFAULT 'yes',

        notify_new_lead ENUM('yes','no')
        NOT NULL DEFAULT 'yes',

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
        FROM sms_settings
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
            INSERT INTO sms_settings
            (

                sms_provider,
                api_key,
                sender_id,
                auth_token,
                api_url,

                admin_mobile,

                sms_status,
                notify_contact_form,
                notify_new_lead

            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
            "
        );

    if ($stmt) {

        $provider =
            'Twilio';

        $apiKey =
            'your-api-key';

        $senderId =
            'KVNCON';

        $authToken =
            'your-auth-token';

        $apiUrl =
            'https://api.twilio.com';

        $adminMobile =
            '+919876543210';

        $smsStatus =
            'enabled';

        $notifyContact =
            'yes';

        $notifyLead =
            'yes';

        $stmt->bind_param(
            'sssssssss',
            $provider,
            $apiKey,
            $senderId,
            $authToken,
            $apiUrl,
            $adminMobile,
            $smsStatus,
            $notifyContact,
            $notifyLead
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

    $provider =
        trim($_POST['sms_provider'] ?? '');

    $apiKey =
        trim($_POST['api_key'] ?? '');

    $senderId =
        trim($_POST['sender_id'] ?? '');

    $authToken =
        trim($_POST['auth_token'] ?? '');

    $apiUrl =
        trim($_POST['api_url'] ?? '');

    $adminMobile =
        trim($_POST['admin_mobile'] ?? '');

    $smsStatus =
        trim($_POST['sms_status'] ?? 'enabled');

    $notifyContact =
        trim($_POST['notify_contact_form'] ?? 'yes');

    $notifyLead =
        trim($_POST['notify_new_lead'] ?? 'yes');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        $provider === '' ||
        $apiKey === '' ||
        $senderId === '' ||
        $authToken === '' ||
        $apiUrl === '' ||
        $adminMobile === ''

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
                    UPDATE sms_settings
                    SET

                        sms_provider       = ?,
                        api_key            = ?,
                        sender_id          = ?,
                        auth_token         = ?,
                        api_url            = ?,

                        admin_mobile       = ?,

                        sms_status         = ?,
                        notify_contact_form = ?,
                        notify_new_lead    = ?

                    WHERE id = 1
                    "
                );

            if ($stmt) {

                $stmt->bind_param(
                    'sssssssss',
                    $provider,
                    $apiKey,
                    $senderId,
                    $authToken,
                    $apiUrl,
                    $adminMobile,
                    $smsStatus,
                    $notifyContact,
                    $notifyLead
                );

                $stmt->execute();

                $stmt->close();

                $success =
                    'SMS settings updated successfully.';
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

    'sms_provider'        => '',
    'api_key'             => '',
    'sender_id'           => '',
    'auth_token'          => '',
    'api_url'             => '',

    'admin_mobile'        => '',

    'sms_status'          => 'enabled',
    'notify_contact_form' => 'yes',
    'notify_new_lead'     => 'yes'

];

$result =
    $conn->query(
        "
        SELECT *
        FROM sms_settings
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
        SMS Settings
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
        SMS Settings
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

        <!-- PROVIDER -->

        <div class="section">

            <h2>
                SMS Provider Configuration
            </h2>

            <div class="form-group">

                <label>
                    SMS Provider
                </label>

                <input
                    type="text"
                    name="sms_provider"
                    value="<?php echo htmlspecialchars((string)$data['sms_provider']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    API Key
                </label>

                <input
                    type="text"
                    name="api_key"
                    value="<?php echo htmlspecialchars((string)$data['api_key']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Sender ID
                </label>

                <input
                    type="text"
                    name="sender_id"
                    value="<?php echo htmlspecialchars((string)$data['sender_id']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Auth Token
                </label>

                <input
                    type="password"
                    name="auth_token"
                    value="<?php echo htmlspecialchars((string)$data['auth_token']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    API URL
                </label>

                <input
                    type="text"
                    name="api_url"
                    value="<?php echo htmlspecialchars((string)$data['api_url']); ?>"
                    required
                >

            </div>

        </div>

        <!-- ADMIN -->

        <div class="section">

            <h2>
                Admin Notification
            </h2>

            <div class="form-group">

                <label>
                    Admin Mobile Number
                </label>

                <input
                    type="text"
                    name="admin_mobile"
                    value="<?php echo htmlspecialchars((string)$data['admin_mobile']); ?>"
                    required
                >

            </div>

        </div>

        <!-- SETTINGS -->

        <div class="section">

            <h2>
                SMS Options
            </h2>

            <div class="form-group">

                <label>
                    SMS Status
                </label>

                <select name="sms_status">

                    <option
                        value="enabled"
                        <?php echo $data['sms_status'] === 'enabled' ? 'selected' : ''; ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo $data['sms_status'] === 'disabled' ? 'selected' : ''; ?>
                    >
                        Disabled
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>
                    Notify on Contact Form Submission
                </label>

                <select name="notify_contact_form">

                    <option
                        value="yes"
                        <?php echo $data['notify_contact_form'] === 'yes' ? 'selected' : ''; ?>
                    >
                        Yes
                    </option>

                    <option
                        value="no"
                        <?php echo $data['notify_contact_form'] === 'no' ? 'selected' : ''; ?>
                    >
                        No
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>
                    Notify on New Leads
                </label>

                <select name="notify_new_lead">

                    <option
                        value="yes"
                        <?php echo $data['notify_new_lead'] === 'yes' ? 'selected' : ''; ?>
                    >
                        Yes
                    </option>

                    <option
                        value="no"
                        <?php echo $data['notify_new_lead'] === 'no' ? 'selected' : ''; ?>
                    >
                        No
                    </option>

                </select>

            </div>

        </div>

        <button type="submit">

            Save SMS Settings

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