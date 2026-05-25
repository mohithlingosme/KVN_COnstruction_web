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
| CREATE TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS contact_page (

        id INT AUTO_INCREMENT PRIMARY KEY,

        hero_title VARCHAR(255) NOT NULL,

        hero_description TEXT NOT NULL,

        phone VARCHAR(50) NOT NULL,

        email VARCHAR(150) NOT NULL,

        office_address TEXT NOT NULL,

        office_hours VARCHAR(255) NOT NULL,

        google_map_link TEXT NOT NULL,

        form_title VARCHAR(255) NOT NULL,

        form_description TEXT NOT NULL,

        why_choose_title VARCHAR(255) NOT NULL,

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

$checkQuery =
    $conn->query(
        "
        SELECT id
        FROM contact_page
        LIMIT 1
        "
    );

if (
    $checkQuery &&
    $checkQuery->num_rows === 0
) {

    $stmt =
        $conn->prepare(
            "
            INSERT INTO contact_page
            (

                hero_title,
                hero_description,

                phone,
                email,
                office_address,
                office_hours,
                google_map_link,

                form_title,
                form_description,

                why_choose_title,
                why_choose_content

            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
            "
        );

    if ($stmt) {

        $heroTitle =
            'Let’s Build Your Dream Home';

        $heroDescription =
            'KVN Constructions provides complete turnkey and end-to-end services for your home construction needs.';

        $phone =
            '+91 9876543210';

        $email =
            'info@kvnconstructions.com';

        $officeAddress =
            'Bangalore, Karnataka, India';

        $officeHours =
            'Monday to Saturday | 9:00 AM to 6:00 PM';

        $googleMapLink =
            'https://maps.google.com';

        $formTitle =
            'Send Us a Message';

        $formDescription =
            'Share your project details and our team will contact you shortly.';

        $whyChooseTitle =
            'Why Partner With KVN Constructions?';

        $whyChooseContent =
            'Dedicated site engineers, premium quality construction, in-house experts, vastu-compliant designs, and timely project handover.';

        $stmt->bind_param(
            'sssssssssss',
            $heroTitle,
            $heroDescription,
            $phone,
            $email,
            $officeAddress,
            $officeHours,
            $googleMapLink,
            $formTitle,
            $formDescription,
            $whyChooseTitle,
            $whyChooseContent
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
| UPDATE CONTENT
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $heroTitle =
        trim($_POST['hero_title'] ?? '');

    $heroDescription =
        trim($_POST['hero_description'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $officeAddress =
        trim($_POST['office_address'] ?? '');

    $officeHours =
        trim($_POST['office_hours'] ?? '');

    $googleMapLink =
        trim($_POST['google_map_link'] ?? '');

    $formTitle =
        trim($_POST['form_title'] ?? '');

    $formDescription =
        trim($_POST['form_description'] ?? '');

    $whyChooseTitle =
        trim($_POST['why_choose_title'] ?? '');

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
        $phone === '' ||
        $email === '' ||
        $officeAddress === '' ||
        $officeHours === '' ||
        $googleMapLink === '' ||
        $formTitle === '' ||
        $formDescription === '' ||
        $whyChooseTitle === '' ||
        $whyChooseContent === ''

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
                    UPDATE contact_page
                    SET

                        hero_title         = ?,
                        hero_description   = ?,

                        phone              = ?,
                        email              = ?,
                        office_address     = ?,
                        office_hours       = ?,
                        google_map_link    = ?,

                        form_title         = ?,
                        form_description   = ?,

                        why_choose_title   = ?,
                        why_choose_content = ?

                    WHERE id = 1
                    "
                );

            if ($stmt) {

                $stmt->bind_param(
                    'sssssssssss',
                    $heroTitle,
                    $heroDescription,
                    $phone,
                    $email,
                    $officeAddress,
                    $officeHours,
                    $googleMapLink,
                    $formTitle,
                    $formDescription,
                    $whyChooseTitle,
                    $whyChooseContent
                );

                $stmt->execute();

                $stmt->close();

                $success =
                    'Contact page updated successfully.';
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

    'phone'              => '',
    'email'              => '',
    'office_address'     => '',
    'office_hours'       => '',
    'google_map_link'    => '',

    'form_title'         => '',
    'form_description'   => '',

    'why_choose_title'   => '',
    'why_choose_content' => ''

];

$result =
    $conn->query(
        "
        SELECT *
        FROM contact_page
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
        Contact Page CMS
    </title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            font-family:Arial,sans-serif;

            background:#f4f4f4;

            padding:40px;
        }

        .container{

            max-width:1100px;

            margin:auto;

            background:#ffffff;

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

            padding:15px 20px;

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
        Contact Page CMS
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
                    Hero Description
                </label>

                <textarea
                    name="hero_description"
                    required
                ><?php echo htmlspecialchars((string)$data['hero_description']); ?></textarea>

            </div>

        </div>

        <!-- CONTACT DETAILS -->

        <div class="section">

            <h2>
                Contact Information
            </h2>

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
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    value="<?php echo htmlspecialchars((string)$data['email']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Office Address
                </label>

                <textarea
                    name="office_address"
                    required
                ><?php echo htmlspecialchars((string)$data['office_address']); ?></textarea>

            </div>

            <div class="form-group">

                <label>
                    Office Hours
                </label>

                <input
                    type="text"
                    name="office_hours"
                    value="<?php echo htmlspecialchars((string)$data['office_hours']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Google Map Link
                </label>

                <input
                    type="text"
                    name="google_map_link"
                    value="<?php echo htmlspecialchars((string)$data['google_map_link']); ?>"
                    required
                >

            </div>

        </div>

        <!-- FORM SECTION -->

        <div class="section">

            <h2>
                Contact Form Section
            </h2>

            <div class="form-group">

                <label>
                    Form Title
                </label>

                <input
                    type="text"
                    name="form_title"
                    value="<?php echo htmlspecialchars((string)$data['form_title']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Form Description
                </label>

                <textarea
                    name="form_description"
                    required
                ><?php echo htmlspecialchars((string)$data['form_description']); ?></textarea>

            </div>

        </div>

        <!-- WHY CHOOSE -->

        <div class="section">

            <h2>
                Why Choose Us
            </h2>

            <div class="form-group">

                <label>
                    Section Title
                </label>

                <input
                    type="text"
                    name="why_choose_title"
                    value="<?php echo htmlspecialchars((string)$data['why_choose_title']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Section Content
                </label>

                <textarea
                    name="why_choose_content"
                    required
                ><?php echo htmlspecialchars((string)$data['why_choose_content']); ?></textarea>

            </div>

        </div>

        <button type="submit">

            Save Contact Page

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