<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| DELETE USER
|--------------------------------------------------------------------------
| File: /public/admin/users/delete.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../includes/repositories.php';

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle =
'Delete User | ' . APP_NAME;

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
| CONFIRM DELETE
|--------------------------------------------------------------------------
*/

if (isset($_GET['confirm'])) {

    $result = $userService->deleteUser(
        $userId,
        currentUserId()
    );

    if ($result['success']) {

        $_SESSION['success'] =
        $result['message'];

    } else {

        $_SESSION['error'] =
        $result['message'];
    }

    redirect('admin/users/index.php');
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

                        Delete User

                    </h1>

                    <p>

                        Confirm user deletion.

                    </p>

                </div>

            </div>

            <!-- DELETE CONFIRM CARD -->

            <div class="section-card">

                <div class="text-center py-5">

                    <!-- ICON -->

                    <div class="mb-4">

                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:64px;"></i>

                    </div>

                    <h3 class="text-danger mb-3">

                        Are you sure?

                    </h3>

                    <p class="mb-4" style="max-width:500px; margin:0 auto;">

                        You are about to delete the user account for

                        <strong>

                            <?php echo escape($user['full_name']); ?>

                        </strong>

                        (<?php echo escape($user['email']); ?>).

                        This action will permanently remove this user and all associated data.

                        This cannot be undone.

                    </p>

                    <!-- USER INFO -->

                    <div class="d-flex justify-content-center gap-4 mb-4">

                        <div>

                            <small class="text-muted">

                                Role

                            </small>

                            <br>

                            <strong>

                                <?php echo ucfirst(escape($user['role'])); ?>

                            </strong>

                        </div>

                        <div>

                            <small class="text-muted">

                                Status

                            </small>

                            <br>

                            <span class="badge

                                <?php

                                if($user['status'] === 'active'){

                                    echo 'bg-success';

                                }else{

                                    echo 'bg-warning';
                                }

                                ?>
                            ">

                                <?php echo ucfirst(escape($user['status'])); ?>

                            </span>

                        </div>

                        <div>

                            <small class="text-muted">

                                Joined

                            </small>

                            <br>

                            <strong>

                                <?php echo date('d M Y', strtotime($user['created_at'])); ?>

                            </strong>

                        </div>

                    </div>

                    <!-- ACTIONS -->

                    <div class="d-flex justify-content-center gap-3">

                        <a
                            href="?id=<?= (int)$user['id'] ?>&confirm=1"
                            class="btn btn-danger btn-lg delete-confirm"
                        >

                            <i class="bi bi-trash-fill"></i>

                            Yes, Delete User

                        </a>

                        <a
                            href="view.php?id=<?= (int)$user['id'] ?>"
                            class="btn btn-dark btn-lg"
                        >

                            <i class="bi bi-x-circle"></i>

                            Cancel

                        </a>

                    </div>

                </div>

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
