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
| CREATE INTEGRATIONS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS integration_settings (

        id INT AUTO_INCREMENT PRIMARY KEY,

        google_maps_api VARCHAR(255) NOT NULL,

        google_recaptcha_site_key VARCHAR(255) NOT NULL,

        google_recaptcha_secret_key VARCHAR(255) NOT NULL,

        facebook_pixel_id VARCHAR(255) NOT NULL,

        whatsapp_number VARCHAR(30) NOT NULL,

        youtube_channel VARCHAR(255) NOT NULL,

        instagram_url VARCHAR(255) NOT NULL,

        linkedin_url VARCHAR(255) NOT NULL,

        telegram_url VARCHAR(255) NOT NULL,

        chatbot_status ENUM('enabled','disabled')
        NOT NULL DEFAULT 'disabled',

        recaptcha_status ENUM('enabled','disabled')
        NOT NULL DEFAULT 'enabled',

        whatsapp_chat_status ENUM('enabled','disabled')
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
        FROM integration_settings
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
            INSERT INTO integration_settings
            (

                google_maps_api,

                google_recaptcha_site_key,
                google_recaptcha_secret_key,

                facebook_pixel_id,

                whatsapp_number,

                youtube_channel,
                instagram_url,
                linkedin_url,
                telegram_url,

                chatbot_status,
                recaptcha_status,
                whatsapp_chat_status

            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
            "
        );

    if ($stmt) {

        $googleMapsApi =
            '';

        $siteKey =
            '';

        $secretKey =
            '';

        $facebookPixel =
            '';

        $whatsapp =
            '+919876543210';

        $youtube =
            'https://youtube.com/';

        $instagram =
            'https://instagram.com/';

        $linkedin =
            'https://linkedin.com/';

        $telegram =
            'https://t.me/';

        $chatbot =
            'disabled';

        $recaptcha =
            'enabled';

        $whatsappStatus =
            'enabled';

        $stmt->bind_param(
            'ssssssssssss',
            $googleMapsApi,
            $siteKey,
            $secretKey,
            $facebookPixel,
            $whatsapp,
            $youtube,
            $instagram,
            $linkedin,
            $telegram,
            $chatbot,
            $recaptcha,
            $whatsappStatus
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

    $googleMapsApi =
        trim($_POST['google_maps_api'] ?? '');

    $siteKey =
        trim($_POST['google_recaptcha_site_key'] ?? '');

    $secretKey =
        trim($_POST['google_recaptcha_secret_key'] ?? '');

    $facebookPixel =
        trim($_POST['facebook_pixel_id'] ?? '');

    $whatsapp =
        trim($_POST['whatsapp_number'] ?? '');

    $youtube =
        trim($_POST['youtube_channel'] ?? '');

    $instagram =
        trim($_POST['instagram_url'] ?? '');

    $linkedin =
        trim($_POST['linkedin_url'] ?? '');

    $telegram =
        trim($_POST['telegram_url'] ?? '');

    $chatbot =
        trim($_POST['chatbot_status'] ?? 'disabled');

    $recaptcha =
        trim($_POST['recaptcha_status'] ?? 'enabled');

    $whatsappStatus =
        trim($_POST['whatsapp_chat_status'] ?? 'enabled');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        $whatsapp === '' ||
        $youtube === '' ||
        $instagram === '' ||
        $linkedin === ''

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
                    UPDATE integration_settings
                    SET

                        google_maps_api                = ?,

                        google_recaptcha_site_key     = ?,
                        google_recaptcha_secret_key   = ?,

                        facebook_pixel_id             = ?,

                        whatsapp_number               = ?,

                        youtube_channel               = ?,
                        instagram_url                 = ?,
                        linkedin_url                  = ?,
                        telegram_url                  = ?,

                        chatbot_status                = ?,
                        recaptcha_status              = ?,
                        whatsapp_chat_status          = ?

                    WHERE id = 1
                    "
                );

            if ($stmt) {

                $stmt->bind_param(
                    'ssssssssssss',
                    $googleMapsApi,
                    $siteKey,
                    $secretKey,
                    $facebookPixel,
                    $whatsapp,
                    $youtube,
                    $instagram,
                    $linkedin,
                    $telegram,
                    $chatbot,
                    $recaptcha,
                    $whatsappStatus
                );

                $stmt->execute();

                $stmt->close();

                $success =
                    'Integration settings updated successfully.';
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

    'google_maps_api'              => '',

    'google_recaptcha_site_key'   => '',
    'google_recaptcha_secret_key' => '',

    'facebook_pixel_id'           => '',

    'whatsapp_number'             => '',

    'youtube_channel'             => '',
    'instagram_url'               => '',
    'linkedin_url'                => '',
    'telegram_url'                => '',

    'chatbot_status'              => 'disabled',
    'recaptcha_status'            => 'enabled',
    'whatsapp_chat_status'        => 'enabled'

];

$result =
    $conn->query(
        "
        SELECT *
        FROM integration_settings
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
        Integration Settings
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
        Integration Settings
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

        <!-- GOOGLE -->

        <div class="section">

            <h2>
                Google Integrations
            </h2>

            <div class="form-group">

                <label>
                    Google Maps API Key
                </label>

                <input
                    type="text"
                    name="google_maps_api"
                    value="<?php echo htmlspecialchars((string)$data['google_maps_api']); ?>"
                >

            </div>

            <div class="form-group">

                <label>
                    Google reCAPTCHA Site Key
                </label>

                <input
                    type="text"
                    name="google_recaptcha_site_key"
                    value="<?php echo htmlspecialchars((string)$data['google_recaptcha_site_key']); ?>"
                >

            </div>

            <div class="form-group">

                <label>
                    Google reCAPTCHA Secret Key
                </label>

                <input
                    type="text"
                    name="google_recaptcha_secret_key"
                    value="<?php echo htmlspecialchars((string)$data['google_recaptcha_secret_key']); ?>"
                >

            </div>

        </div>

        <!-- SOCIAL -->

        <div class="section">

            <h2>
                Social Media Integrations
            </h2>

            <div class="form-group">

                <label>
                    Facebook Pixel ID
                </label>

                <input
                    type="text"
                    name="facebook_pixel_id"
                    value="<?php echo htmlspecialchars((string)$data['facebook_pixel_id']); ?>"
                >

            </div>

            <div class="form-group">

                <label>
                    WhatsApp Number
                </label>

                <input
                    type="text"
                    name="whatsapp_number"
                    value="<?php echo htmlspecialchars((string)$data['whatsapp_number']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    YouTube Channel URL
                </label>

                <input
                    type="text"
                    name="youtube_channel"
                    value="<?php echo htmlspecialchars((string)$data['youtube_channel']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Instagram URL
                </label>

                <input
                    type="text"
                    name="instagram_url"
                    value="<?php echo htmlspecialchars((string)$data['instagram_url']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    LinkedIn URL
                </label>

                <input
                    type="text"
                    name="linkedin_url"
                    value="<?php echo htmlspecialchars((string)$data['linkedin_url']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Telegram URL
                </label>

                <input
                    type="text"
                    name="telegram_url"
                    value="<?php echo htmlspecialchars((string)$data['telegram_url']); ?>"
                >

            </div>

        </div>

        <!-- STATUS -->

        <div class="section">

            <h2>
                Feature Controls
            </h2>

            <div class="form-group">

                <label>
                    Chatbot Status
                </label>

                <select name="chatbot_status">

                    <option
                        value="enabled"
                        <?php echo $data['chatbot_status'] === 'enabled' ? 'selected' : ''; ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo $data['chatbot_status'] === 'disabled' ? 'selected' : ''; ?>
                    >
                        Disabled
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>
                    reCAPTCHA Status
                </label>

                <select name="recaptcha_status">

                    <option
                        value="enabled"
                        <?php echo $data['recaptcha_status'] === 'enabled' ? 'selected' : ''; ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo $data['recaptcha_status'] === 'disabled' ? 'selected' : ''; ?>
                    >
                        Disabled
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>
                    WhatsApp Chat Status
                </label>

                <select name="whatsapp_chat_status">

                    <option
                        value="enabled"
                        <?php echo $data['whatsapp_chat_status'] === 'enabled' ? 'selected' : ''; ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo $data['whatsapp_chat_status'] === 'disabled' ? 'selected' : ''; ?>
                    >
                        Disabled
                    </option>

                </select>

            </div>

        </div>

        <button type="submit">

            Save Integration Settings

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