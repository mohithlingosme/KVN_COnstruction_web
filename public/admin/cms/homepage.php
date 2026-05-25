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
| DATABASE
|--------------------------------------------------------------------------
*/

require_once '../../includes/db.php';

/*
|--------------------------------------------------------------------------
| CREATE TABLE IF NOT EXISTS
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS homepage_content (

        id INT AUTO_INCREMENT PRIMARY KEY,

        hero_title VARCHAR(255) NOT NULL,

        hero_subtitle TEXT NOT NULL,

        hero_button_text VARCHAR(100) NOT NULL,

        hero_button_link VARCHAR(255) NOT NULL,

        section2_title VARCHAR(255) NOT NULL,

        section2_content TEXT NOT NULL,

        services_title VARCHAR(255) NOT NULL,

        services_content TEXT NOT NULL,

        cta_title VARCHAR(255) NOT NULL,

        cta_button_text VARCHAR(100) NOT NULL,

        cta_button_link VARCHAR(255) NOT NULL,

        updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| INSERT DEFAULT CONTENT
|--------------------------------------------------------------------------
*/

$check =
    $conn->query(
        "
        SELECT id
        FROM homepage_content
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
            INSERT INTO homepage_content
            (

                hero_title,
                hero_subtitle,
                hero_button_text,
                hero_button_link,

                section2_title,
                section2_content,

                services_title,
                services_content,

                cta_title,
                cta_button_text,
                cta_button_link

            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
            "
        );

    if ($stmt) {

        $heroTitle =
            'Build Your Dream Home';

        $heroSubtitle =
            'KVN Constructions provides complete turnkey and end-to-end construction services.';

        $heroButtonText =
            'Contact Us';

        $heroButtonLink =
            'contact.php';

        $section2Title =
            'About KVN Constructions';

        $section2Content =
            'We provide premium residential construction with complete in-house experts and guaranteed timely delivery.';

        $servicesTitle =
            'Our Services';

        $servicesContent =
            'Architecture, Design, Construction, Interiors, Planning, Elevation, Plumbing, Electrical and Complete Key Handover Solutions.';

        $ctaTitle =
            'Let’s Build Your Dream Home';

        $ctaButtonText =
            'Get Free Consultation';

        $ctaButtonLink =
            'contact.php';

        $stmt->bind_param(
            'sssssssssss',
            $heroTitle,
            $heroSubtitle,
            $heroButtonText,
            $heroButtonLink,
            $section2Title,
            $section2Content,
            $servicesTitle,
            $servicesContent,
            $ctaTitle,
            $ctaButtonText,
            $ctaButtonLink
        );

        $stmt->execute();

        $stmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| DEFAULT VALUES
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';

/*
|--------------------------------------------------------------------------
| UPDATE CONTENT
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $heroTitle =
        trim($_POST['hero_title'] ?? '');

    $heroSubtitle =
        trim($_POST['hero_subtitle'] ?? '');

    $heroButtonText =
        trim($_POST['hero_button_text'] ?? '');

    $heroButtonLink =
        trim($_POST['hero_button_link'] ?? '');

    $section2Title =
        trim($_POST['section2_title'] ?? '');

    $section2Content =
        trim($_POST['section2_content'] ?? '');

    $servicesTitle =
        trim($_POST['services_title'] ?? '');

    $servicesContent =
        trim($_POST['services_content'] ?? '');

    $ctaTitle =
        trim($_POST['cta_title'] ?? '');

    $ctaButtonText =
        trim($_POST['cta_button_text'] ?? '');

    $ctaButtonLink =
        trim($_POST['cta_button_link'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        $heroTitle === '' ||
        $heroSubtitle === '' ||
        $heroButtonText === '' ||
        $heroButtonLink === '' ||
        $section2Title === '' ||
        $section2Content === '' ||
        $servicesTitle === '' ||
        $servicesContent === '' ||
        $ctaTitle === '' ||
        $ctaButtonText === '' ||
        $ctaButtonLink === ''

    ) {

        $error =
            'All fields are required.';
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
                    UPDATE homepage_content
                    SET

                        hero_title        = ?,
                        hero_subtitle     = ?,
                        hero_button_text  = ?,
                        hero_button_link  = ?,

                        section2_title    = ?,
                        section2_content  = ?,

                        services_title    = ?,
                        services_content  = ?,

                        cta_title         = ?,
                        cta_button_text   = ?,
                        cta_button_link   = ?

                    WHERE id = 1
                    "
                );

            if ($stmt) {

                $stmt->bind_param(
                    'sssssssssss',
                    $heroTitle,
                    $heroSubtitle,
                    $heroButtonText,
                    $heroButtonLink,
                    $section2Title,
                    $section2Content,
                    $servicesTitle,
                    $servicesContent,
                    $ctaTitle,
                    $ctaButtonText,
                    $ctaButtonLink
                );

                $stmt->execute();

                $stmt->close();

                $success =
                    'Homepage updated successfully.';
            }

        } catch (Throwable $e) {

            $error =
                $e->getMessage();
        }
    }
}

/*
|--------------------------------------------------------------------------
| FETCH DATA
|--------------------------------------------------------------------------
*/

$data = [

    'hero_title'        => '',
    'hero_subtitle'     => '',
    'hero_button_text'  => '',
    'hero_button_link'  => '',

    'section2_title'    => '',
    'section2_content'  => '',

    'services_title'    => '',
    'services_content'  => '',

    'cta_title'         => '',
    'cta_button_text'   => '',
    'cta_button_link'   => ''

];

$result =
    $conn->query(
        "
        SELECT *
        FROM homepage_content
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
        Homepage CMS
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

            margin-bottom:45px;
        }

        .form-group{

            margin-bottom:25px;
        }

        label{

            display:block;

            margin-bottom:10px;

            font-weight:bold;

            color:#333;
        }

        input,
        textarea{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:15px;
        }

        textarea{

            min-height:140px;

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

            padding:16px 20px;

            border-radius:10px;

            margin-bottom:30px;

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
        Homepage CMS
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

        <!-- HERO -->

        <div class="section">

            <h2>
                Hero Section
            </h2>

            <div class="form-group">

                <label>
                    Hero Title
                </label>

                <input
                    type="text"
                    name="hero_title"
                    value="<?php echo htmlspecialchars((string)$data['hero_title']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Hero Subtitle
                </label>

                <textarea
                    name="hero_subtitle"
                    required
                ><?php echo htmlspecialchars((string)$data['hero_subtitle']); ?></textarea>

            </div>

            <div class="form-group">

                <label>
                    Hero Button Text
                </label>

                <input
                    type="text"
                    name="hero_button_text"
                    value="<?php echo htmlspecialchars((string)$data['hero_button_text']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Hero Button Link
                </label>

                <input
                    type="text"
                    name="hero_button_link"
                    value="<?php echo htmlspecialchars((string)$data['hero_button_link']); ?>"
                    required
                >

            </div>

        </div>

        <!-- ABOUT -->

        <div class="section">

            <h2>
                About Section
            </h2>

            <div class="form-group">

                <label>
                    About Title
                </label>

                <input
                    type="text"
                    name="section2_title"
                    value="<?php echo htmlspecialchars((string)$data['section2_title']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    About Content
                </label>

                <textarea
                    name="section2_content"
                    required
                ><?php echo htmlspecialchars((string)$data['section2_content']); ?></textarea>

            </div>

        </div>

        <!-- SERVICES -->

        <div class="section">

            <h2>
                Services Section
            </h2>

            <div class="form-group">

                <label>
                    Services Title
                </label>

                <input
                    type="text"
                    name="services_title"
                    value="<?php echo htmlspecialchars((string)$data['services_title']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Services Content
                </label>

                <textarea
                    name="services_content"
                    required
                ><?php echo htmlspecialchars((string)$data['services_content']); ?></textarea>

            </div>

        </div>

        <!-- CTA -->

        <div class="section">

            <h2>
                Call To Action Section
            </h2>

            <div class="form-group">

                <label>
                    CTA Title
                </label>

                <input
                    type="text"
                    name="cta_title"
                    value="<?php echo htmlspecialchars((string)$data['cta_title']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    CTA Button Text
                </label>

                <input
                    type="text"
                    name="cta_button_text"
                    value="<?php echo htmlspecialchars((string)$data['cta_button_text']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    CTA Button Link
                </label>

                <input
                    type="text"
                    name="cta_button_link"
                    value="<?php echo htmlspecialchars((string)$data['cta_button_link']); ?>"
                    required
                >

            </div>

        </div>

        <button type="submit">

            Save Homepage Content

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