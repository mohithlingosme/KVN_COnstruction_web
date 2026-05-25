<?php

declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['client_id'])) {

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
    CREATE TABLE IF NOT EXISTS clients (

        id INT AUTO_INCREMENT PRIMARY KEY,

        full_name VARCHAR(255) NOT NULL,

        email VARCHAR(255) NOT NULL UNIQUE,

        phone VARCHAR(20) DEFAULT NULL,

        company_name VARCHAR(255) DEFAULT NULL,

        address TEXT DEFAULT NULL,

        city VARCHAR(100) DEFAULT NULL,

        state VARCHAR(100) DEFAULT NULL,

        pincode VARCHAR(20) DEFAULT NULL,

        profile_image VARCHAR(255) DEFAULT NULL,

        password VARCHAR(255) DEFAULT NULL,

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| SESSION DETAILS
|--------------------------------------------------------------------------
*/

$clientId =
    (int) $_SESSION['client_id'];

$clientName =
    $_SESSION['client_name'] ?? 'Client';

/*
|--------------------------------------------------------------------------
| UPLOAD DIRECTORY
|--------------------------------------------------------------------------
*/

$uploadDir =
    '../../uploads/profile/';

if (!is_dir($uploadDir)) {

    mkdir(
        $uploadDir,
        0777,
        true
    );
}

/*
|--------------------------------------------------------------------------
| DEMO CLIENT INSERT
|--------------------------------------------------------------------------
*/

$checkClient =
    $conn->query(
        "
        SELECT id
        FROM clients
        WHERE id = $clientId
        LIMIT 1
        "
    );

if (
    $checkClient &&
    $checkClient->num_rows === 0
) {

    $demoPassword =
        password_hash(
            '123456',
            PASSWORD_DEFAULT
        );

    $stmt =
        $conn->prepare(
            "
            INSERT INTO clients
            (

                id,
                full_name,
                email,
                phone,
                company_name,
                address,
                city,
                state,
                pincode,
                password

            )

            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            "
        );

    $email =
        'client@kvnconstruction.com';

    $phone =
        '9876543210';

    $company =
        'KVN Client Group';

    $address =
        'MG Road';

    $city =
        'Bangalore';

    $state =
        'Karnataka';

    $pincode =
        '560001';

    $stmt->bind_param(
        'isssssssss',
        $clientId,
        $clientName,
        $email,
        $phone,
        $company,
        $address,
        $city,
        $state,
        $pincode,
        $demoPassword
    );

    $stmt->execute();
}

/*
|--------------------------------------------------------------------------
| UPDATE PROFILE
|--------------------------------------------------------------------------
*/

$successMessage = '';
$errorMessage = '';

if (
    $_SERVER['REQUEST_METHOD']
    === 'POST'
) {

    $fullName =
        trim($_POST['full_name'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $companyName =
        trim($_POST['company_name'] ?? '');

    $address =
        trim($_POST['address'] ?? '');

    $city =
        trim($_POST['city'] ?? '');

    $state =
        trim($_POST['state'] ?? '');

    $pincode =
        trim($_POST['pincode'] ?? '');

    $profileImage = null;

    /*
    |--------------------------------------------------------------------------
    | IMAGE UPLOAD
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['profile_image']) &&
        $_FILES['profile_image']['error'] === 0
    ) {

        $fileName =
            time() .
            '_' .
            basename(
                $_FILES['profile_image']['name']
            );

        $targetFile =
            $uploadDir .
            $fileName;

        move_uploaded_file(
            $_FILES['profile_image']['tmp_name'],
            $targetFile
        );

        $profileImage =
            $fileName;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($fullName) ||
        empty($email)
    ) {

        $errorMessage =
            'Full name and email are required.';
    }
    else {

        if ($profileImage !== null) {

            $stmt =
                $conn->prepare(
                    "
                    UPDATE clients
                    SET

                        full_name = ?,
                        email = ?,
                        phone = ?,
                        company_name = ?,
                        address = ?,
                        city = ?,
                        state = ?,
                        pincode = ?,
                        profile_image = ?

                    WHERE id = ?
                    "
                );

            $stmt->bind_param(
                'sssssssssi',
                $fullName,
                $email,
                $phone,
                $companyName,
                $address,
                $city,
                $state,
                $pincode,
                $profileImage,
                $clientId
            );
        }
        else {

            $stmt =
                $conn->prepare(
                    "
                    UPDATE clients
                    SET

                        full_name = ?,
                        email = ?,
                        phone = ?,
                        company_name = ?,
                        address = ?,
                        city = ?,
                        state = ?,
                        pincode = ?

                    WHERE id = ?
                    "
                );

            $stmt->bind_param(
                'ssssssssi',
                $fullName,
                $email,
                $phone,
                $companyName,
                $address,
                $city,
                $state,
                $pincode,
                $clientId
            );
        }

        if ($stmt->execute()) {

            $_SESSION['client_name'] =
                $fullName;

            $successMessage =
                'Profile updated successfully.';
        }
        else {

            $errorMessage =
                'Failed to update profile.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| FETCH PROFILE
|--------------------------------------------------------------------------
*/

$stmt =
    $conn->prepare(
        "
        SELECT *
        FROM clients
        WHERE id = ?
        LIMIT 1
        "
    );

$stmt->bind_param(
    'i',
    $clientId
);

$stmt->execute();

$result =
    $stmt->get_result();

$client =
    $result->fetch_assoc();

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
        Client Profile
    </title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            font-family:Arial,sans-serif;

            background:#f3f4f6;

            color:#222;
        }

        .sidebar{

            width:260px;

            height:100vh;

            background:#111827;

            position:fixed;

            top:0;

            left:0;

            padding:30px 20px;

            overflow:auto;
        }

        .sidebar h2{

            color:#f5b400;

            margin-bottom:35px;
        }

        .sidebar a{

            display:block;

            text-decoration:none;

            color:#fff;

            padding:14px 16px;

            border-radius:10px;

            margin-bottom:10px;

            transition:0.3s;
        }

        .sidebar a:hover,
        .sidebar .active{

            background:#f5b400;

            color:#111;
        }

        .main{

            margin-left:260px;

            padding:40px;
        }

        .topbar{

            display:flex;

            justify-content:space-between;

            align-items:center;

            flex-wrap:wrap;

            gap:15px;

            margin-bottom:35px;
        }

        .logout-btn{

            text-decoration:none;

            background:#dc3545;

            color:#fff;

            padding:12px 18px;

            border-radius:10px;

            font-weight:bold;
        }

        .profile-card{

            background:#fff;

            padding:35px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);

            max-width:1000px;
        }

        .profile-header{

            display:flex;

            align-items:center;

            gap:25px;

            margin-bottom:35px;

            flex-wrap:wrap;
        }

        .profile-image{

            width:130px;

            height:130px;

            border-radius:50%;

            object-fit:cover;

            border:5px solid #f5b400;
        }

        .default-avatar{

            width:130px;

            height:130px;

            border-radius:50%;

            background:#111827;

            color:#fff;

            display:flex;

            align-items:center;

            justify-content:center;

            font-size:40px;

            font-weight:bold;
        }

        .profile-header h1{

            margin-bottom:10px;
        }

        .form-grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(280px,1fr));

            gap:20px;
        }

        .form-group{

            display:flex;

            flex-direction:column;
        }

        .form-group label{

            margin-bottom:8px;

            font-weight:bold;
        }

        .form-group input,
        .form-group textarea{

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:15px;
        }

        textarea{

            min-height:120px;

            resize:vertical;
        }

        .full-width{

            grid-column:1 / -1;
        }

        .submit-btn{

            background:#111827;

            color:#fff;

            border:none;

            padding:15px 25px;

            border-radius:10px;

            font-size:16px;

            font-weight:bold;

            cursor:pointer;
        }

        .success{

            background:#d4edda;

            color:#155724;

            padding:15px;

            border-radius:10px;

            margin-bottom:20px;
        }

        .error{

            background:#f8d7da;

            color:#721c24;

            padding:15px;

            border-radius:10px;

            margin-bottom:20px;
        }

        @media(max-width:992px){

            .sidebar{

                width:100%;

                height:auto;

                position:relative;
            }

            .main{

                margin-left:0;
            }
        }

    </style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <h2>
        KVN Client
    </h2>

    <a href="../dashboard.php">
        Dashboard
    </a>

    <a
        href="index.php"
        class="active"
    >
        My Profile
    </a>

    <a href="../projects/index.php">
        Projects
    </a>

    <a href="../payments/index.php">
        Payments
    </a>

    <a href="../support/tickets.php">
        Support
    </a>

    <a href="../logout.php">
        Logout
    </a>

</div>

<!-- MAIN -->

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <div>

            <h1>
                Client Profile
            </h1>

            <p>

                Welcome,
                <?php
                    echo htmlspecialchars(
                        (string)$clientName
                    );
                ?>

            </p>

        </div>

        <a
            href="../logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

    <!-- PROFILE CARD -->

    <div class="profile-card">

        <!-- PROFILE HEADER -->

        <div class="profile-header">

            <?php if (!empty($client['profile_image'])): ?>

                <img
                    src="../../uploads/profile/<?php echo htmlspecialchars((string)$client['profile_image']); ?>"
                    class="profile-image"
                    alt="Profile"
                >

            <?php else: ?>

                <div class="default-avatar">

                    <?php
                        echo strtoupper(
                            substr(
                                htmlspecialchars(
                                    (string)$client['full_name']
                                ),
                                0,
                                1
                            )
                        );
                    ?>

                </div>

            <?php endif; ?>

            <div>

                <h1>

                    <?php
                        echo htmlspecialchars(
                            (string)$client['full_name']
                        );
                    ?>

                </h1>

                <p>

                    <?php
                        echo htmlspecialchars(
                            (string)$client['email']
                        );
                    ?>

                </p>

            </div>

        </div>

        <!-- ALERTS -->

        <?php if (!empty($successMessage)): ?>

            <div class="success">
                <?php echo $successMessage; ?>
            </div>

        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>

            <div class="error">
                <?php echo $errorMessage; ?>
            </div>

        <?php endif; ?>

        <!-- FORM -->

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-grid">

                <div class="form-group">

                    <label>
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="full_name"
                        value="<?php echo htmlspecialchars((string)$client['full_name']); ?>"
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
                        value="<?php echo htmlspecialchars((string)$client['email']); ?>"
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
                        value="<?php echo htmlspecialchars((string)$client['phone']); ?>"
                    >

                </div>

                <div class="form-group">

                    <label>
                        Company Name
                    </label>

                    <input
                        type="text"
                        name="company_name"
                        value="<?php echo htmlspecialchars((string)$client['company_name']); ?>"
                    >

                </div>

                <div class="form-group">

                    <label>
                        City
                    </label>

                    <input
                        type="text"
                        name="city"
                        value="<?php echo htmlspecialchars((string)$client['city']); ?>"
                    >

                </div>

                <div class="form-group">

                    <label>
                        State
                    </label>

                    <input
                        type="text"
                        name="state"
                        value="<?php echo htmlspecialchars((string)$client['state']); ?>"
                    >

                </div>

                <div class="form-group">

                    <label>
                        Pincode
                    </label>

                    <input
                        type="text"
                        name="pincode"
                        value="<?php echo htmlspecialchars((string)$client['pincode']); ?>"
                    >

                </div>

                <div class="form-group">

                    <label>
                        Profile Image
                    </label>

                    <input
                        type="file"
                        name="profile_image"
                    >

                </div>

                <div class="form-group full-width">

                    <label>
                        Address
                    </label>

                    <textarea
                        name="address"
                    ><?php echo htmlspecialchars((string)$client['address']); ?></textarea>

                </div>

                <div class="form-group">

                    <label>
                        Save Changes
                    </label>

                    <button
                        type="submit"
                        class="submit-btn"
                    >
                        Update Profile
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

</body>

</html>