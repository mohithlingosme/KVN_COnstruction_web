<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| EDIT USER
|--------------------------------------------------------------------------
| File: /public/admin/users/edit.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/csrf.php';

require_once '../../../helpers/session.php';

require_once '../../../helpers/rateLimiter.php';

require_once '../../../helpers/upload.php';

require_once '../../../includes/repositories.php';

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle =
'Edit User | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| VALIDATE USER ID
|--------------------------------------------------------------------------
*/

$userId =
(int) ($_GET['id'] ?? 0);

if ($userId <= 0) {

    $_SESSION['error'] =
    'Invalid user ID.';

    redirect('admin/users/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH USER VIA SERVICE
|--------------------------------------------------------------------------
*/

$userService = new \App\Services\AdminUserService();

$user = $userService->getUserById($userId);

if (!$user) {

    $_SESSION['error'] =
    'User not found.';

    redirect('admin/users/index.php');
}

/*
|--------------------------------------------------------------------------
| UPDATE USER
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

            'edit_user',

            15,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests.';

        redirect(

            'admin/users/edit.php?id='
            .
            $userId
        );
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

    $role =
    sanitize($_POST['role'] ?? 'client');

    $status =
    sanitize($_POST['status'] ?? 'active');

    $password =
    trim($_POST['password'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATE VIA SERVICE
    |--------------------------------------------------------------------------
    */

    $validation = $userService->validateUserData([
        'full_name' => $full_name,
        'email'     => $email,
        'password'  => $password,
    ], true, $userId);

    if (!$validation['valid']) {

        $_SESSION['error'] =
        implode(' ', $validation['errors']);

        redirect(

            'admin/users/edit.php?id='
            .
            $userId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PROFILE IMAGE
    |--------------------------------------------------------------------------
    */

    $profile_image =
    $user['profile_image'] ?? null;

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

            redirect(

                'admin/users/edit.php?id='
                .
                $userId
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE USER VIA SERVICE
    |--------------------------------------------------------------------------
    */

    $updateData = [
        'full_name'     => $full_name,
        'email'         => $email,
        'phone'         => $phone,
        'role'          => $role,
        'status'        => $status,
        'profile_image' => $profile_image,
        '_admin_id'     => currentUserId(),
    ];

    if (!empty($password)) {
        $updateData['password'] = $password;
    }

    $result = $userService->updateUser($userId, $updateData);

    if ($result['success']) {

        $_SESSION['success'] =
        $result['message'];

        redirect('admin/users/index.php');

    } else {

        $_SESSION['error'] =
        $result['message'];

        redirect(

            'admin/users/edit.php?id='
            .
            $userId
        );
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
        href="<?php echo base_url('assets/admin/css/admin.css'); ?>"
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

                        Edit User

                    </h1>

                    <p>

                        Update user details and permissions.

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
                                value="<?php echo escape($user['full_name']); ?>"
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
                                value="<?php echo escape($user['email']); ?>"
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
                                value="<?php echo escape($user['phone']); ?>"
                            >

                        </div>

                        <!-- PASSWORD -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                New Password

                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Leave blank to keep current password"
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

                                <option
                                    value="client"

                                    <?php

                                    if($user['role'] === 'client'){

                                        echo 'selected';
                                    }

                                    ?>
                                >

                                    Client

                                </option>

                                <option
                                    value="admin"

                                    <?php

                                    if($user['role'] === 'admin'){

                                        echo 'selected';
                                    }

                                    ?>
                                >

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

                                <option
                                    value="active"

                                    <?php

                                    if($user['status'] === 'active'){

                                        echo 'selected';
                                    }

                                    ?>
                                >

                                    Active

                                </option>

                                <option
                                    value="inactive"

                                    <?php

                                    if($user['status'] === 'inactive'){

                                        echo 'selected';
                                    }

                                    ?>
                                >

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

                        <!-- CURRENT IMAGE -->

                        <?php if(!empty($user['profile_image'])): ?>

                            <div class="col-lg-12 mb-4">

                                <img
                                    src="<?php echo base_url('../uploads/users/' . $user['profile_image']); ?>"
                                    alt="Profile"
                                    width="100"
                                    class="rounded"
                                >

                            </div>

                        <?php endif; ?>

                    </div>

                    <!-- BUTTONS -->

                    <div class="d-flex gap-3">

                        <button
                            type="submit"
                            class="btn-admin"
                        >

                            <i class="bi bi-check-circle"></i>

                            Update User

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

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin JS -->

<script src="<?php echo base_url('assets/admin/js/admin.js'); ?>"></script>

</body>

</html>
