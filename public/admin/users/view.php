<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| VIEW USER
|--------------------------------------------------------------------------
| File:
| /public/admin/users/view.php
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
'View User | ' . APP_NAME;

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
| FETCH USER
|--------------------------------------------------------------------------
*/

$query = "

    SELECT *

    FROM users

    WHERE id = :id

    LIMIT 1
";

$stmt =
$conn->prepare($query);

$stmt->execute([

    ':id' => $userId
]);

$user =
$stmt->fetch();

if (!$user) {

    $_SESSION['error'] =
    'User not found.';

    redirect('admin/users/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH USER ACTIVITY
|--------------------------------------------------------------------------
*/

$activities = [];

try {

    $activityQuery = "

        SELECT *

        FROM security_logs

        WHERE user_id = :user_id

        ORDER BY created_at DESC

        LIMIT 10
    ";

    $activityStmt =
    $conn->prepare($activityQuery);

    $activityStmt->execute([

        ':user_id' => $userId
    ]);

    $activities =
    $activityStmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| PROFILE IMAGE
|--------------------------------------------------------------------------
*/

$profileImage =
!empty($user['profile_image'])

?

base_url(

    '../uploads/users/'
    .
    $user['profile_image']
)

:

'https://via.placeholder.com/150';

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

                        User Profile

                    </h1>

                    <p>

                        Detailed user information and activity.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="edit.php?id=<?php echo $user['id']; ?>"
                        class="btn-admin"
                    >

                        <i class="bi bi-pencil-square"></i>

                        Edit User

                    </a>

                    <a
                        href="index.php"
                        class="btn btn-dark"
                    >

                        Back

                    </a>

                </div>

            </div>

            <!-- ============================== -->
            <!-- PROFILE SECTION -->
            <!-- ============================== -->

            <div class="row g-4">

                <!-- PROFILE CARD -->

                <div class="col-lg-4">

                    <div class="profile-card">

                        <img
                            src="<?php echo escape($profileImage); ?>"
                            alt="Profile"
                            class="img-fluid rounded-circle mb-4"
                            style="
                                width:150px;
                                height:150px;
                                object-fit:cover;
                            "
                        >

                        <h3>

                            <?php

                            echo escape(
                                $user['full_name']
                            );

                            ?>

                        </h3>

                        <p class="text-muted mb-3">

                            <?php

                            echo ucfirst(

                                escape(
                                    $user['role']
                                )
                            );

                            ?>

                        </p>

                        <!-- STATUS -->

                        <span class="badge

                            <?php

                            if($user['status'] === 'active'){

                                echo 'bg-success';

                            }else{

                                echo 'bg-warning';
                            }

                            ?>
                        ">

                            <?php

                            echo ucfirst(

                                escape(
                                    $user['status']
                                )
                            );

                            ?>

                        </span>

                        <!-- PHONE VERIFIED -->

                        <div class="mt-4">

                            <?php if(!empty($user['phone_verified'])): ?>

                                <span class="badge bg-primary">

                                    <i class="bi bi-patch-check-fill"></i>

                                    Phone Verified

                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

                <!-- DETAILS -->

                <div class="col-lg-8">

                    <div class="section-card">

                        <div class="section-header">

                            <h4>

                                User Details

                            </h4>

                        </div>

                        <div class="row">

                            <!-- USER ID -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    User ID

                                </label>

                                <h6>

                                    #<?php echo $user['id']; ?>

                                </h6>

                            </div>

                            <!-- EMAIL -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Email Address

                                </label>

                                <h6>

                                    <?php

                                    echo escape(
                                        $user['email']
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- PHONE -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Phone Number

                                </label>

                                <h6>

                                    <?php

                                    echo escape(
                                        $user['phone']
                                        ??
                                        'N/A'
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- ROLE -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Role

                                </label>

                                <h6>

                                    <?php

                                    echo ucfirst(

                                        escape(
                                            $user['role']
                                        )
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- STATUS -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Account Status

                                </label>

                                <h6>

                                    <?php

                                    echo ucfirst(

                                        escape(
                                            $user['status']
                                        )
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- CREATED -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Joined Date

                                </label>

                                <h6>

                                    <?php

                                    echo date(

                                        'd M Y h:i A',

                                        strtotime(

                                            $user['created_at']
                                        )
                                    );

                                    ?>

                                </h6>

                            </div>

                            <!-- LAST LOGIN -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Last Login

                                </label>

                                <h6>

                                    <?php

                                    echo !empty($user['last_login'])

                                    ?

                                    date(

                                        'd M Y h:i A',

                                        strtotime(
                                            $user['last_login']
                                        )
                                    )

                                    :

                                    'Never';

                                    ?>

                                </h6>

                            </div>

                            <!-- LAST IP -->

                            <div class="col-md-6 mb-4">

                                <label class="text-muted">

                                    Last IP Address

                                </label>

                                <h6>

                                    <?php

                                    echo escape(
                                        $user['last_ip']
                                        ??
                                        'N/A'
                                    );

                                    ?>

                                </h6>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ============================== -->
            <!-- ACTIVITY LOG -->
            <!-- ============================== -->

            <div class="section-card mt-4">

                <div class="section-header">

                    <h4>

                        Recent Security Activity

                    </h4>

                </div>

                <?php if(!empty($activities)): ?>

                    <div class="activity-list">

                        <?php foreach($activities as $activity): ?>

                            <div class="activity-item">

                                <div class="activity-icon">

                                    <i class="bi bi-shield-lock"></i>

                                </div>

                                <div class="activity-content">

                                    <h6>

                                        <?php

                                        echo escape(
                                            $activity['event']
                                        );

                                        ?>

                                    </h6>

                                    <small>

                                        <?php

                                        echo escape(
                                            $activity['description']
                                            ??
                                            'No description'
                                        );

                                        ?>

                                    </small>

                                    <br>

                                    <small class="text-muted">

                                        <?php

                                        echo date(

                                            'd M Y h:i A',

                                            strtotime(

                                                $activity['created_at']
                                            )
                                        );

                                        ?>

                                    </small>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="text-center py-4">

                        No recent activity found.

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin JS -->

<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>

</body>

</html>