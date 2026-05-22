<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ADMIN USERS MANAGEMENT
|--------------------------------------------------------------------------
| File:
| /public/admin/users/index.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/formatter.php';

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle =
'Users Management | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FETCH USERS
|--------------------------------------------------------------------------
*/

$users = [];

try {

    $query = "

        SELECT
            id,
            full_name,
            email,
            phone,
            role,
            status,
            created_at

        FROM users

        ORDER BY id DESC
    ";

    $stmt = $conn->prepare($query);

    $stmt->execute();

    $users = $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to fetch users.';
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

    <!-- ================================= -->
    <!-- SIDEBAR -->
    <!-- ================================= -->

    <?php include '../../../app/views/layouts/sidebar.php'; ?>

    <!-- ================================= -->
    <!-- MAIN -->
    <!-- ================================= -->

    <div class="admin-main">

        <!-- NAVBAR -->

        <?php include '../../../app/views/layouts/navbar.php'; ?>

        <!-- CONTENT -->

        <div class="admin-content">

            <!-- ============================== -->
            <!-- HEADER -->
            <!-- ============================== -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Users Management

                    </h1>

                    <p>

                        Manage admins, clients and platform users.

                    </p>

                </div>

                <div>

                    <a
                        href="create.php"
                        class="btn-admin"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Add User

                    </a>

                </div>

            </div>

            <!-- ============================== -->
            <!-- ALERTS -->
            <!-- ============================== -->

            <?php if(isset($_SESSION['success'])): ?>

                <div class="alert alert-success alert-auto-dismiss">

                    <?php

                    echo escape(
                        $_SESSION['success']
                    );

                    unset($_SESSION['success']);

                    ?>

                </div>

            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>

                <div class="alert alert-danger alert-auto-dismiss">

                    <?php

                    echo escape(
                        $_SESSION['error']
                    );

                    unset($_SESSION['error']);

                    ?>

                </div>

            <?php endif; ?>

            <!-- ============================== -->
            <!-- SEARCH -->
            <!-- ============================== -->

            <div class="section-card">

                <div class="row mb-4">

                    <div class="col-lg-4">

                        <input
                            type="text"
                            class="form-control table-search"
                            data-table="#usersTable"
                            placeholder="Search users..."
                        >

                    </div>

                </div>

                <!-- ============================== -->
                <!-- TABLE -->
                <!-- ============================== -->

                <div class="table-responsive">

                    <table
                        class="table admin-table"
                        id="usersTable"
                    >

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>User</th>

                                <th>Email</th>

                                <th>Phone</th>

                                <th>Role</th>

                                <th>Status</th>

                                <th>Joined</th>

                                <th width="180">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($users)): ?>

                                <?php foreach($users as $user): ?>

                                    <tr>

                                        <td>

                                            #<?php echo $user['id']; ?>

                                        </td>

                                        <td>

                                            <div class="d-flex align-items-center gap-3">

                                                <div class="admin-avatar">

                                                    <?php

                                                    echo strtoupper(

                                                        substr(

                                                            $user['full_name'],

                                                            0,

                                                            1
                                                        )
                                                    );

                                                    ?>

                                                </div>

                                                <div>

                                                    <strong>

                                                        <?php

                                                        echo escape(
                                                            $user['full_name']
                                                        );

                                                        ?>

                                                    </strong>

                                                </div>

                                            </div>

                                        </td>

                                        <td>

                                            <?php

                                            echo escape(
                                                $user['email']
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <?php

                                            echo escape(
                                                $user['phone']
                                                ??
                                                'N/A'
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <?php

                                            $role =
                                            strtolower(
                                                $user['role']
                                            );

                                            ?>

                                            <span class="badge

                                                <?php

                                                if($role === 'admin'){

                                                    echo 'bg-danger';

                                                }elseif($role === 'client'){

                                                    echo 'bg-primary';

                                                }else{

                                                    echo 'bg-secondary';
                                                }

                                                ?>
                                            ">

                                                <?php

                                                echo ucfirst($role);

                                                ?>

                                            </span>

                                        </td>

                                        <td>

                                            <?php

                                            $status =
                                            strtolower(
                                                $user['status']
                                            );

                                            ?>

                                            <span class="badge

                                                <?php

                                                if($status === 'active'){

                                                    echo 'bg-success';

                                                }else{

                                                    echo 'bg-warning';
                                                }

                                                ?>
                                            ">

                                                <?php

                                                echo ucfirst($status);

                                                ?>

                                            </span>

                                        </td>

                                        <td>

                                            <?php

                                            echo date(

                                                'd M Y',

                                                strtotime(

                                                    $user['created_at']
                                                )
                                            );

                                            ?>

                                        </td>

                                        <td>

                                            <div class="d-flex gap-2">

                                                <!-- VIEW -->

                                                <a
                                                    href="view.php?id=<?php echo $user['id']; ?>"
                                                    class="btn btn-sm btn-dark"
                                                >

                                                    <i class="bi bi-eye"></i>

                                                </a>

                                                <!-- EDIT -->

                                                <a
                                                    href="edit.php?id=<?php echo $user['id']; ?>"
                                                    class="btn btn-sm btn-primary"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>

                                                <!-- DELETE -->

                                                <?php if($user['id'] != currentUserId()): ?>

                                                    <a
                                                        href="delete.php?id=<?php echo $user['id']; ?>"
                                                        class="btn btn-sm btn-danger btn-delete"
                                                    >

                                                        <i class="bi bi-trash"></i>

                                                    </a>

                                                <?php endif; ?>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="8">

                                        <div class="text-center py-4">

                                            No users found.

                                        </div>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- ================================= -->
<!-- LOADER -->
<!-- ================================= -->

<div
    id="globalLoader"
    style="
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,0.4);
        z-index:9999;
        align-items:center;
        justify-content:center;
    "
>

    <div class="spinner-border text-warning">

    </div>

</div>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin JS -->

<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>

</body>

</html>