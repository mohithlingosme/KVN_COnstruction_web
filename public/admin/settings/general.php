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
| CREATE SETTINGS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS general_settings (

        id INT AUTO_INCREMENT PRIMARY KEY,

        site_name VARCHAR(255) NOT NULL,

        site_tagline VARCHAR(255) NOT NULL,

        admin_email VARCHAR(150) NOT NULL,

        support_email VARCHAR(150) NOT NULL,

        phone VARCHAR(50) NOT NULL,

        whatsapp VARCHAR(50) NOT NULL,

        address TEXT NOT NULL,

        facebook_link VARCHAR(255) NOT NULL,

        instagram_link VARCHAR(255) NOT NULL,

        youtube_link VARCHAR(255) NOT NULL,

        linkedin_link VARCHAR(255) NOT NULL,

        logo VARCHAR(255) NOT NULL,

        favicon VARCHAR(255) NOT NULL,

        footer_text TEXT NOT NULL,

        maintenance_mode ENUM('on','off')
        NOT NULL DEFAULT 'off',

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
        FROM general_settings
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
            INSERT INTO general_settings
            (

                site_name,
                site_tagline,

                admin_email,
                support_email,

                phone,
                whatsapp,

                address,

                facebook_link,
                instagram_link,
                youtube_link,
                linkedin_link,

                logo,
                favicon,

                footer_text,

                maintenance_mode

            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
            "
        );

    if ($stmt) {

        $siteName =
            'KVN Construction';

        $siteTagline =
            'Dream Homes Built with Quality';

        $adminEmail =
            'admin@kvnconstruction.com';

        $supportEmail =
            'support@kvnconstruction.com';

        $phone =
            '+91 9876543210';

        $whatsapp =
            '+91 9876543210';

        $address =
            'Bangalore, Karnataka, India';

        $facebook =
            'https://facebook.com';

        $instagram =
            'https://instagram.com';

        $youtube =
            'https://youtube.com';

        $linkedin =
            'https://linkedin.com';

        $logo =
            'uploads/logo.png';

        $favicon =
            'uploads/favicon.ico';

        $footer =
            '© 2026 KVN Construction. All Rights Reserved.';

        $maintenance =
            'off';

        $stmt->bind_param(
            'sssssssssssssss',
            $siteName,
            $siteTagline,
            $adminEmail,
            $supportEmail,
            $phone,
            $whatsapp,
            $address,
            $facebook,
            $instagram,
            $youtube,
            $linkedin,
            $logo,
            $favicon,
            $footer,
            $maintenance
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

    $siteName =
        trim($_POST['site_name'] ?? '');

    $siteTagline =
        trim($_POST['site_tagline'] ?? '');

    $adminEmail =
        trim($_POST['admin_email'] ?? '');

    $supportEmail =
        trim($_POST['support_email'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $whatsapp =
        trim($_POST['whatsapp'] ?? '');

    $address =
        trim($_POST['address'] ?? '');

    $facebook =
        trim($_POST['facebook_link'] ?? '');

    $instagram =
        trim($_POST['instagram_link'] ?? '');

    $youtube =
        trim($_POST['youtube_link'] ?? '');

    $linkedin =
        trim($_POST['linkedin_link'] ?? '');

    $logo =
        trim($_POST['logo'] ?? '');

    $favicon =
        trim($_POST['favicon'] ?? '');

    $footer =
        trim($_POST['footer_text'] ?? '');

    $maintenance =
        trim($_POST['maintenance_mode'] ?? 'off');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        $siteName === '' ||
        $siteTagline === '' ||
        $adminEmail === '' ||
        $supportEmail === '' ||
        $phone === '' ||
        $whatsapp === '' ||
        $address === '' ||
        $facebook === '' ||
        $instagram === '' ||
        $youtube === '' ||
        $linkedin === '' ||
        $logo === '' ||
        $favicon === '' ||
        $footer === ''

    ) {

        $error =
            'Please fill all fields.';
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
                    UPDATE general_settings
                    SET

                        site_name        = ?,
                        site_tagline     = ?,

                        admin_email      = ?,
                        support_email    = ?,

                        phone            = ?,
                        whatsapp         = ?,

                        address          = ?,

                        facebook_link    = ?,
                        instagram_link   = ?,
                        youtube_link     = ?,
                        linkedin_link    = ?,

                        logo             = ?,
                        favicon          = ?,

                        footer_text      = ?,

                        maintenance_mode = ?

                    WHERE id = 1
                    "
                );

            if ($stmt) {

                $stmt->bind_param(
                    'sssssssssssssss',
                    $siteName,
                    $siteTagline,
                    $adminEmail,
                    $supportEmail,
                    $phone,
                    $whatsapp,
                    $address,
                    $facebook,
                    $instagram,
                    $youtube,
                    $linkedin,
                    $logo,
                    $favicon,
                    $footer,
                    $maintenance
                );

                $stmt->execute();

                $stmt->close();

                $success =
                    'General settings updated successfully.';
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

];

$result =
    $conn->query(
        "
        SELECT *
        FROM general_settings
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
                        <?php echo $data['maintenance_mode'] === 'off' ? 'selected' : ''; ?>
                    >
                        OFF
                    </option>

                    <option
                        value="on"
                        <?php echo $data['maintenance_mode'] === 'on' ? 'selected' : ''; ?>
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
        href="../dashboard.php"
        class="back"
    >
        ← Back to Dashboard
    </a>

</div>

</body>

</html>