<?php

declare(strict_types=1);

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';
require_once '../../../helpers/security.php';
require_once '../../../helpers/csrf.php';
require_once '../../../helpers/session.php';
require_once '../../../helpers/functions.php';
require_once '../../includes/repositories.php';

/*
|--------------------------------------------------------------------------
| ADMIN SETTINGS SERVICE
|--------------------------------------------------------------------------
*/

$settingsService = new \App\Services\AdminSettingsService();

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

    $result = $settingsService->saveIntegrationSettings($_POST);

    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

/*
|--------------------------------------------------------------------------
| FETCH SETTINGS
|--------------------------------------------------------------------------
*/

$data = array_merge([

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

], $settingsService->getIntegrationSettings());

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
                        <?php echo escape($data['chatbot_status'] === 'enabled' ? 'selected' : ''); ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo escape($data['chatbot_status'] === 'disabled' ? 'selected' : ''); ?>
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
                        <?php echo escape($data['recaptcha_status'] === 'enabled' ? 'selected' : ''); ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo escape($data['recaptcha_status'] === 'disabled' ? 'selected' : ''); ?>
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
                        <?php echo escape($data['whatsapp_chat_status'] === 'enabled' ? 'selected' : ''); ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo escape($data['whatsapp_chat_status'] === 'disabled' ? 'selected' : ''); ?>
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
        href="<?php echo base_url('admin/dashboard.php'); ?>"
        class="back"
    >
        ← Back to Dashboard
    </a>

</div>

</body>

</html>

