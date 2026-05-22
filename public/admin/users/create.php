<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CREATE USER
|--------------------------------------------------------------------------
| File:
| /public/admin/users/create.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/csrf.php';

require_once '../../../helpers/session.php';

require_once '../../../helpers/rateLimiter.php';

require_once '../../../helpers/upload.php';

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle =
'Create User | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validateCsrf();

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT
    |--------------------------------------------------------------------------
    */

    if (

        !checkRateLimit(

            'create_user',

            10,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests. Please try again later.';

        redirect('admin/users/create.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $full_name =
    sanitize($_POST['full_name'] ?? '');

    $email =
    sanitize($_POST['email'] ?? '');

    $phone =
    sanitizePhone($_POST['phone'] ?? '');

    $password =
    $_POST['password'] ?? '';

    $role =
    sanitize($_POST['role'] ?? 'client');

    $status =
    sanitize($_POST['status'] ?? 'active');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        empty($full_name)

        ||

        empty($email)

        ||

        empty($password)
    ) {

        $_SESSION['error'] =
        'Please fill all required fields.';

        redirect('admin/users/create.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['error'] =
        'Invalid email address.';

        redirect('admin/users/create.php');
    }

    if (strlen($password) < 8) {

        $_SESSION['error'] =
        'Password must be minimum 8 characters.';

        redirect('admin/users/create.php');
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK EMAIL EXISTS
    |--------------------------------------------------------------------------
    */

    $checkQuery = "

        SELECT id

        FROM users

        WHERE email = :email

        LIMIT 1
    ";

    $checkStmt =
    $conn->prepare($checkQuery);

    $checkStmt->execute([

        ':email' => $email
    ]);

    if ($checkStmt->fetch()) {

        $_SESSION['error'] =
        'Email already exists.';

        redirect('admin/users/create.php');
    }

    /*
    |--------------------------------------------------------------------------
    | PROFILE IMAGE
    |--------------------------------------------------------------------------
    */

    $profile_image = null;

    if (

        isset($_FILES['profile_image'])

        &&

        $_FILES['profile_image']['error'] === 0
    ) {

        $upload =
        uploadFile(

            $_FILES['profile_image'],

            'users'
        );

        if ($upload['success']) {

            $profile_image =
            $upload['file_name'];

        } else {

            $_SESSION['error'] =
            $upload['message'];

            redirect('admin/users/create.php');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT USER
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            INSERT INTO users (

                full_name,
                email,
                phone,
                password,
                role,
                status,
                profile_image,
                created_at

            ) VALUES (

                :full_name,
                :email,
                :phone,
                :password,
                :role,
                :status,
                :profile_image,
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':full_name' =>
            $full_name,

            ':email' =>
            $email,

            ':phone' =>
            $phone,

            ':password' =>
            password_hash(

                $password,

                PASSWORD_BCRYPT
            ),

            ':role' =>
            $role,

            ':status' =>
            $status,

            ':profile_image' =>
            $profile_image
        ]);

        /*
        |--------------------------------------------------------------------------
        | SECURITY LOG
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(

            currentUserId(),

            'user_created',

            'info',

            'Created user: ' . $email
        );

        $_SESSION['success'] =
        'User created successfully.';

        redirect('admin/users/index.php');

    } catch(Exception $e) {

        $_SESSION['error'] =
        'Failed to create user.';

        redirect('admin/users/create.php');
    }
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

        <?php echo escape($pageTitle); ?>

    </title>

    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- Admin CSS -->

    <link
        rel="stylesheet"
        href="<?php echo base_url('../assets/admin/css/admin.css'); ?>"
    >

</head>

<body>

<div class="admin-layout">

    <!-- SIDEBAR -->

    <?php include '../../../app/views/layouts/sidebar.php'; ?>

    <!-- MAIN -->

    <div class="admin-main">

        <!-- NAVBAR -->

        <?php include '../../../app/views/layouts/navbar.php'; ?>

        <!-- CONTENT -->

        <div class="admin-content">

            <!-- HEADER -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Create User

                    </h1>

                    <p>

                        Add new admin or client user.

                    </p>

                </div>

            </div>

            <!-- ALERTS -->

            <?php if(isset($_SESSION['error'])): ?>

                <div class="alert alert-danger">

                    <?php

                    echo escape(
                        $_SESSION['error']
                    );

                    unset($_SESSION['error']);

                    ?>

                </div>

            <?php endif; ?>

            <!-- FORM -->

            <div class="section-card">

                <form
                    method="POST"
                    enctype="multipart/form-data"
                    class="needs-validation"
                    novalidate
                >

                    <?php echo csrfField(); ?>

                    <div class="row">

                        <!-- NAME -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Full Name

                            </label>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                required
                            >

                        </div>

                        <!-- EMAIL -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Email Address

                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                required
                            >

                        </div>

                        <!-- PHONE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Phone Number

                            </label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                            >

                        </div>

                        <!-- PASSWORD -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Password

                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                required
                            >

                        </div>

                        <!-- ROLE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Role

                            </label>

                            <select
                                name="role"
                                class="form-select"
                            >

                                <option value="client">

                                    Client

                                </option>

                                <option value="admin">

                                    Admin

                                </option>

                            </select>

                        </div>

                        <!-- STATUS -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Status

                            </label>

                            <select
                                name="status"
                                class="form-select"
                            >

                                <option value="active">

                                    Active

                                </option>

                                <option value="inactive">

                                    Inactive

                                </option>

                            </select>

                        </div>

                        <!-- PROFILE IMAGE -->

                        <div class="col-lg-12 mb-4">

                            <label class="form-label">

                                Profile Image

                            </label>

                            <input
                                type="file"
                                name="profile_image"
                                class="form-control image-input"
                            >

                        </div>

                    </div>

                    <!-- BUTTONS -->

                    <div class="d-flex gap-3">

                        <button
                            type="submit"
                            class="btn-admin"
                        >

                            <i class="bi bi-check-circle"></i>

                            Create User

                        </button>

                        <a
                            href="index.php"
                            class="btn btn-dark"
                        >

                            Cancel

                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<!-- JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>

</body>

</html>