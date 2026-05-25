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
| CREATE SEO TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS seo_settings (

        id INT AUTO_INCREMENT PRIMARY KEY,

        meta_title VARCHAR(255) NOT NULL,

        meta_description TEXT NOT NULL,

        meta_keywords TEXT NOT NULL,

        canonical_url VARCHAR(255) NOT NULL,

        robots_meta VARCHAR(100) NOT NULL,

        google_analytics TEXT NOT NULL,

        google_search_console TEXT NOT NULL,

        facebook_meta_title VARCHAR(255) NOT NULL,

        facebook_meta_description TEXT NOT NULL,

        twitter_meta_title VARCHAR(255) NOT NULL,

        twitter_meta_description TEXT NOT NULL,

        sitemap_status ENUM('enabled','disabled')
        NOT NULL DEFAULT 'enabled',

        seo_status ENUM('enabled','disabled')
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
        FROM seo_settings
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
            INSERT INTO seo_settings
            (

                meta_title,
                meta_description,
                meta_keywords,

                canonical_url,
                robots_meta,

                google_analytics,
                google_search_console,

                facebook_meta_title,
                facebook_meta_description,

                twitter_meta_title,
                twitter_meta_description,

                sitemap_status,
                seo_status

            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
            "
        );

    if ($stmt) {

        $metaTitle =
            'KVN Construction | Home Construction Experts';

        $metaDescription =
            'KVN Construction provides complete turnkey and home construction solutions with premium quality and timely delivery.';

        $metaKeywords =
            'KVN Construction, Home Construction, Builders, Architects, Turnkey Projects';

        $canonicalUrl =
            'https://www.kvnconstruction.com';

        $robotsMeta =
            'index, follow';

        $googleAnalytics =
            'UA-XXXXXXXXX-X';

        $googleSearchConsole =
            '<meta name="google-site-verification" content="">';

        $facebookTitle =
            'KVN Construction';

        $facebookDescription =
            'Premium home construction and turnkey services.';

        $twitterTitle =
            'KVN Construction';

        $twitterDescription =
            'Expert home construction solutions with quality assurance.';

        $sitemapStatus =
            'enabled';

        $seoStatus =
            'enabled';

        $stmt->bind_param(
            'sssssssssssss',
            $metaTitle,
            $metaDescription,
            $metaKeywords,
            $canonicalUrl,
            $robotsMeta,
            $googleAnalytics,
            $googleSearchConsole,
            $facebookTitle,
            $facebookDescription,
            $twitterTitle,
            $twitterDescription,
            $sitemapStatus,
            $seoStatus
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

    $metaTitle =
        trim($_POST['meta_title'] ?? '');

    $metaDescription =
        trim($_POST['meta_description'] ?? '');

    $metaKeywords =
        trim($_POST['meta_keywords'] ?? '');

    $canonicalUrl =
        trim($_POST['canonical_url'] ?? '');

    $robotsMeta =
        trim($_POST['robots_meta'] ?? '');

    $googleAnalytics =
        trim($_POST['google_analytics'] ?? '');

    $googleSearchConsole =
        trim($_POST['google_search_console'] ?? '');

    $facebookTitle =
        trim($_POST['facebook_meta_title'] ?? '');

    $facebookDescription =
        trim($_POST['facebook_meta_description'] ?? '');

    $twitterTitle =
        trim($_POST['twitter_meta_title'] ?? '');

    $twitterDescription =
        trim($_POST['twitter_meta_description'] ?? '');

    $sitemapStatus =
        trim($_POST['sitemap_status'] ?? 'enabled');

    $seoStatus =
        trim($_POST['seo_status'] ?? 'enabled');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        $metaTitle === '' ||
        $metaDescription === '' ||
        $metaKeywords === '' ||
        $canonicalUrl === ''

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
                    UPDATE seo_settings
                    SET

                        meta_title                 = ?,
                        meta_description           = ?,
                        meta_keywords              = ?,

                        canonical_url              = ?,
                        robots_meta                = ?,

                        google_analytics           = ?,
                        google_search_console      = ?,

                        facebook_meta_title        = ?,
                        facebook_meta_description  = ?,

                        twitter_meta_title         = ?,
                        twitter_meta_description   = ?,

                        sitemap_status             = ?,
                        seo_status                 = ?

                    WHERE id = 1
                    "
                );

            if ($stmt) {

                $stmt->bind_param(
                    'sssssssssssss',
                    $metaTitle,
                    $metaDescription,
                    $metaKeywords,
                    $canonicalUrl,
                    $robotsMeta,
                    $googleAnalytics,
                    $googleSearchConsole,
                    $facebookTitle,
                    $facebookDescription,
                    $twitterTitle,
                    $twitterDescription,
                    $sitemapStatus,
                    $seoStatus
                );

                $stmt->execute();

                $stmt->close();

                $success =
                    'SEO settings updated successfully.';
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

];

$result =
    $conn->query(
        "
        SELECT *
        FROM seo_settings
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
                        <?php echo $data['sitemap_status'] === 'enabled' ? 'selected' : ''; ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo $data['sitemap_status'] === 'disabled' ? 'selected' : ''; ?>
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
                        <?php echo $data['seo_status'] === 'enabled' ? 'selected' : ''; ?>
                    >
                        Enabled
                    </option>

                    <option
                        value="disabled"
                        <?php echo $data['seo_status'] === 'disabled' ? 'selected' : ''; ?>
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
        href="../dashboard.php"
        class="back"
    >
        ← Back to Dashboard
    </a>

</div>

</body>

</html>