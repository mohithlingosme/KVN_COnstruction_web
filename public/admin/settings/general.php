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

    $result = $settingsService->saveGeneralSettings($_POST);

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

    'site_name'        => '',
    'site_tagline'     => '',

    'admin_email'      => '',
    'support_email'    => '',

    'phone'            => '',
    'whatsapp'         => '',

    'address'          => '',

    'facebook_link'    => '',
    'instagram_link'   => '',
    'youtube_link'     => '',
    'linkedin_link'    => '',

    'logo'             => '',
    'favicon'          => '',

    'footer_text'      => '',

    'maintenance_mode' => 'off'

], $settingsService->getGeneralSettings());

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
        General Settings
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
        textarea,
        select{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:15px;
        }

        textarea{

            min-height:120px;

            resize:vertical;
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
        General Settings
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

        <!-- SITE -->

        <div class="section">

            <h2>
                Website Information
            </h2>

            <div class="form-group">

                <label>
                    Site Name
                </label>

                <input
                    type="text"
                    name="site_name"
                    value="<?php echo htmlspecialchars((string)$data['site_name']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Site Tagline
                </label>

                <input
                    type="text"
                    name="site_tagline"
                    value="<?php echo htmlspecialchars((string)$data['site_tagline']); ?>"
                    required
                >

            </div>

        </div>

        <!-- EMAIL -->

        <div class="section">

            <h2>
                Contact Details
            </h2>

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

            <div class="form-group">

                <label>
                    Support Email
                </label>

                <input
                    type="email"
                    name="support_email"
                    value="<?php echo htmlspecialchars((string)$data['support_email']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Phone Number
                </label>

                <input
                    type="text"
                    name="phone"
                    value="<?php echo htmlspecialchars((string)$data['phone']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    WhatsApp Number
                </label>

                <input
                    type="text"
                    name="whatsapp"
                    value="<?php echo htmlspecialchars((string)$data['whatsapp']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Address
                </label>

                <textarea
                    name="address"
                    required
                ><?php echo htmlspecialchars((string)$data['address']); ?></textarea>

            </div>

        </div>

        <!-- SOCIAL -->

        <div class="section">

            <h2>
                Social Media Links
            </h2>

            <div class="form-group">

                <label>
                    Facebook Link
                </label>

                <input
                    type="text"
                    name="facebook_link"
                    value="<?php echo htmlspecialchars((string)$data['facebook_link']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Instagram Link
                </label>

                <input
                    type="text"
                    name="instagram_link"
                    value="<?php echo htmlspecialchars((string)$data['instagram_link']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    YouTube Link
                </label>

                <input
                    type="text"
                    name="youtube_link"
                    value="<?php echo htmlspecialchars((string)$data['youtube_link']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    LinkedIn Link
                </label>

                <input
                    type="text"
                    name="linkedin_link"
                    value="<?php echo htmlspecialchars((string)$data['linkedin_link']); ?>"
                    required
                >

            </div>

        </div>

        <!-- BRANDING -->

        <div class="section">

            <h2>
                Branding
            </h2>

            <div class="form-group">

                <label>
                    Logo Path
                </label>

                <input
                    type="text"
                    name="logo"
                    value="<?php echo htmlspecialchars((string)$data['logo']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Favicon Path
                </label>

                <input
                    type="text"
                    name="favicon"
                    value="<?php echo htmlspecialchars((string)$data['favicon']); ?>"
                    required
                >

            </div>

        </div>

        <!-- FOOTER -->

        <div class="section">

            <h2>
                Footer
            </h2>

            <div class="form-group">

                <label>
                    Footer Text
                </label>

                <textarea
                    name="footer_text"
                    required
                ><?php echo htmlspecialchars((string)$data['footer_text']); ?></textarea>

            </div>

        </div>

        <!-- MAINTENANCE -->

        <div class="section">

            <h2>
                Maintenance Mode
            </h2>

            <div class="form-group">

                <label>
                    Website Status
                </label>

                <select name="maintenance_mode">

                    <option
                        value="off"
                        <?php echo escape($data['maintenance_mode'] === 'off' ? 'selected' : ''); ?>
                    >
                        OFF
                    </option>

                    <option
                        value="on"
                        <?php echo escape($data['maintenance_mode'] === 'on' ? 'selected' : ''); ?>
                    >
                        ON
                    </option>

                </select>

            </div>

        </div>

        <button type="submit">

            Save Settings

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

