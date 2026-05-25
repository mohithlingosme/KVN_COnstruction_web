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
    CREATE TABLE IF NOT EXISTS about_page (

        id INT AUTO_INCREMENT PRIMARY KEY,

        hero_title VARCHAR(255) NOT NULL,

        hero_description TEXT NOT NULL,

        mission_title VARCHAR(255) NOT NULL,

        mission_content TEXT NOT NULL,

        vision_title VARCHAR(255) NOT NULL,

        vision_content TEXT NOT NULL,

        process_content TEXT NOT NULL,

        why_choose_content TEXT NOT NULL,

        updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| INSERT DEFAULT DATA
|--------------------------------------------------------------------------
*/

$check =
    $conn->query(
        "
        SELECT id
        FROM about_page
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
            INSERT INTO about_page
            (
                hero_title,
                hero_description,
                mission_title,
                mission_content,
                vision_title,
                vision_content,
                process_content,
                why_choose_content
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?
            )
            "
        );

    if ($stmt) {

        $heroTitle =
            'About KVN Constructions';

        $heroDescription =
            'KVN Constructions provides complete turnkey and end-to-end home construction solutions with premium quality and timely delivery.';

        $missionTitle =
            'Our Mission';

        $missionContent =
            'To deliver quality construction with transparency, trust, and customer satisfaction.';

        $visionTitle =
            'Our Vision';

        $visionContent =
            'To become the most trusted home construction company in India through innovation and quality.';

        $processContent =
            'Requirement Analysis, Planning, Design, Construction, Quality Checks, and Key Handover.';

        $whyChooseContent =
            'Dedicated site engineers, in-house experts, vastu-compliant designs, premium quality construction, and timely delivery.';

        $stmt->bind_param(
            'ssssssss',
            $heroTitle,
            $heroDescription,
            $missionTitle,
            $missionContent,
            $visionTitle,
            $visionContent,
            $processContent,
            $whyChooseContent
        );

        $stmt->execute();

        $stmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| DEFAULTS
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';

/*
|--------------------------------------------------------------------------
| UPDATE ABOUT PAGE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $heroTitle =
        trim($_POST['hero_title'] ?? '');

    $heroDescription =
        trim($_POST['hero_description'] ?? '');

    $missionTitle =
        trim($_POST['mission_title'] ?? '');

    $missionContent =
        trim($_POST['mission_content'] ?? '');

    $visionTitle =
        trim($_POST['vision_title'] ?? '');

    $visionContent =
        trim($_POST['vision_content'] ?? '');

    $processContent =
        trim($_POST['process_content'] ?? '');

    $whyChooseContent =
        trim($_POST['why_choose_content'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        $heroTitle === '' ||
        $heroDescription === '' ||
        $missionTitle === '' ||
        $missionContent === '' ||
        $visionTitle === '' ||
        $visionContent === '' ||
        $processContent === '' ||
        $whyChooseContent === ''

    ) {

        $error =
            'All fields are required.';
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE DATA
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $stmt =
                $conn->prepare(
                    "
                    UPDATE about_page
                    SET

                        hero_title        = ?,
                        hero_description  = ?,
                        mission_title     = ?,
                        mission_content   = ?,
                        vision_title      = ?,
                        vision_content    = ?,
                        process_content   = ?,
                        why_choose_content= ?

                    WHERE id = 1
                    "
                );

            if ($stmt) {

                $stmt->bind_param(
                    'ssssssss',
                    $heroTitle,
                    $heroDescription,
                    $missionTitle,
                    $missionContent,
                    $visionTitle,
                    $visionContent,
                    $processContent,
                    $whyChooseContent
                );

                $stmt->execute();

                $stmt->close();

                $success =
                    'About page updated successfully.';
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

    'hero_title'         => '',
    'hero_description'   => '',
    'mission_title'      => '',
    'mission_content'    => '',
    'vision_title'       => '',
    'vision_content'     => '',
    'process_content'    => '',
    'why_choose_content' => ''

];

$result =
    $conn->query(
        "
        SELECT *
        FROM about_page
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
        About Page CMS
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

            min-height:160px;

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
        About Page CMS
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
                    Hero Description
                </label>

                <textarea
                    name="hero_description"
                    required
                ><?php echo htmlspecialchars((string)$data['hero_description']); ?></textarea>

            </div>

        </div>

        <div class="section">

            <h2>
                Mission Section
            </h2>

            <div class="form-group">

                <label>
                    Mission Title
                </label>

                <input
                    type="text"
                    name="mission_title"
                    value="<?php echo htmlspecialchars((string)$data['mission_title']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Mission Content
                </label>

                <textarea
                    name="mission_content"
                    required
                ><?php echo htmlspecialchars((string)$data['mission_content']); ?></textarea>

            </div>

        </div>

        <div class="section">

            <h2>
                Vision Section
            </h2>

            <div class="form-group">

                <label>
                    Vision Title
                </label>

                <input
                    type="text"
                    name="vision_title"
                    value="<?php echo htmlspecialchars((string)$data['vision_title']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Vision Content
                </label>

                <textarea
                    name="vision_content"
                    required
                ><?php echo htmlspecialchars((string)$data['vision_content']); ?></textarea>

            </div>

        </div>

        <div class="section">

            <h2>
                Construction Process
            </h2>

            <div class="form-group">

                <label>
                    Process Content
                </label>

                <textarea
                    name="process_content"
                    required
                ><?php echo htmlspecialchars((string)$data['process_content']); ?></textarea>

            </div>

        </div>

        <div class="section">

            <h2>
                Why Choose Us
            </h2>

            <div class="form-group">

                <label>
                    Why Choose KVN
                </label>

                <textarea
                    name="why_choose_content"
                    required
                ><?php echo htmlspecialchars((string)$data['why_choose_content']); ?></textarea>

            </div>

        </div>

        <button type="submit">

            Save About Page

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