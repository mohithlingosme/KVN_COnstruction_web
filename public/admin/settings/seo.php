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

    $result = $settingsService->saveSeoSettings($_POST);

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

    'meta_title'                => '',
    'meta_description'          => '',
    'meta_keywords'             => '',

    'canonical_url'             => '',
    'robots_meta'               => '',

    'google_analytics'          => '',
    'google_search_console'     => '',

    'facebook_meta_title'       => '',
    'facebook_meta_description' => '',

    'twitter_meta_title'        => '',
    'twitter_meta_description'  => '',

    'sitemap_status'            => 'enabled',
    'seo_status'                => 'enabled'

], $settingsService->getSeoSettings());

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
        SEO Settings
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
        SEO Settings
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

        <!-- BASIC SEO -->

        <div class="section">

            <h2>
                Basic SEO
            </h2>

            <div class="form-group">

                <label>
                    Meta Title
                </label>

                <input
                    type="text"
                    name="meta_title"
                    value="<?php echo htmlspecialchars((string)$data['meta_title']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Meta Description
                </label>

                <textarea
                    name="meta_description"
                    required
                ><?php echo htmlspecialchars((string)$data['meta_description']); ?></textarea>

            </div>

            <div class="form-group">

                <label>
                    Meta Keywords
                </label>

                <textarea
                    name="meta_keywords"
                    required
                ><?php echo htmlspecialchars((string)$data['meta_keywords']); ?></textarea>

            </div>

            <div class="form-group">

                <label>
                    Canonical URL
                </label>

                <input
                    type="text"
                    name="canonical_url"
                    value="<?php echo htmlspecialchars((string)$data['canonical_url']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Robots Meta
                </label>

                <input
                    type="text"
                    name="robots_meta"
                    value="<?php echo htmlspecialchars((string)$data['robots_meta']); ?>"
                >

            </div>

        </div>

        <!-- GOOGLE -->

        <div class="section">

            <h2>
                Google Integration
            </h2>

            <div class="form-group">

                <label>
                    Google Analytics Code
                </label>

                <textarea
                    name="google_analytics"
                ><?php echo htmlspecialchars((string)$data['google_analytics']); ?></textarea>

            </div>

            <div class="form-group">

                <label>
                    Google Search Console Verification
                </label>

                <textarea
                    name="google_search_console"
                ><?php echo htmlspecialchars((string)$data['google_search_console']); ?></textarea>

            </div>

        </div>

        <!-- FACEBOOK -->

        <div class="section">

            <h2>
                Facebook Open Graph
            </h2>

            <div class="form-group">

                <label>
                    Facebook Meta Title
                </label>

                <input
                    type="text"
                    name="facebook_meta_title"
                    value="<?php echo htmlspecialchars((string)$data['facebook_meta_title']); ?>"
                >

            </div>

            <div class="form-group">

                <label>
                    Facebook Meta Description
                </label>

                <textarea
                    name="facebook_meta_description"
                ><?php echo htmlspecialchars((string)$data['facebook_meta_description']); ?></textarea>

            </div>

        </div>

        <!-- TWITTER -->

        <div class="section">

            <h2>
                Twitter Meta Tags
            </h2>

            <div class="form-group">

                <label>
                    Twitter Meta Title
                </label>

                <input
                    type="text"
                    name="twitter_meta_title"
                    value="<?php echo htmlspecialchars((string)$data['twitter_meta_title']); ?>"
                >

            </div>

            <div class="form-group">

                <label>
                    Twitter Meta Description
                </label>

                <textarea
                    name="twitter_meta_description"
                ><?php echo htmlspecialchars((string)$data['twitter_meta_description']); ?></textarea>

            </div>

        </div>

        <!-- STATUS -->

        <div class="section">

            <h2>
                SEO Status
            </h2>

            <div class="form-group">

                <label>
                    Sitemap Status
                </label>

                <select name="sitemap_status">

                    <option
                        value="enabled"
                        <?php echo escape($data['sitemap_status'] === 'enabled' ? 'selected' : ''); ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo escape($data['sitemap_status'] === 'disabled' ? 'selected' : ''); ?>
                    >
                        Disabled
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>
                    SEO Status
                </label>

                <select name="seo_status">

                    <option
                        value="enabled"
                        <?php echo escape($data['seo_status'] === 'enabled' ? 'selected' : ''); ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo escape($data['seo_status'] === 'disabled' ? 'selected' : ''); ?>
                    >
                        Disabled
                    </option>

                </select>

            </div>

        </div>

        <button type="submit">

            Save SEO Settings

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

