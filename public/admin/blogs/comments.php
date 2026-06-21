<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| BLOG COMMENTS MANAGEMENT
|--------------------------------------------------------------------------
| File:
| /public/admin/blogs/comments.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/session.php';

require_once '../../../helpers/csrf.php';

require_once '../../../helpers/rateLimiter.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
'Blog Comments | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| CREATE TABLE IF NOT EXISTS
|--------------------------------------------------------------------------
*/

try {

    $conn->exec("

        CREATE TABLE IF NOT EXISTS blog_comments (

            id INT PRIMARY KEY AUTO_INCREMENT,

            blog_id INT NOT NULL,

            user_name VARCHAR(255) NOT NULL,

            user_email VARCHAR(255) NOT NULL,

            comment TEXT NOT NULL,

            status ENUM(
                'pending',
                'approved',
                'spam',
                'rejected'
            ) DEFAULT 'pending',

            ip_address VARCHAR(100) NULL,

            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP
        )
    ");

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| HANDLE STATUS UPDATE
|--------------------------------------------------------------------------
*/

if (

    $_SERVER['REQUEST_METHOD'] === 'POST'

    &&

    isset($_POST['comment_id'])
) {

    validateCsrf();

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT
    |--------------------------------------------------------------------------
    */

    if (

        !checkRateLimit(

            'manage_blog_comment',

            20,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests.';

        redirect('admin/blogs/comments.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $commentId =
    (int) ($_POST['comment_id'] ?? 0);

    $status =
    sanitize($_POST['status'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    $allowedStatuses = [

        'approved',
        'pending',
        'spam',
        'rejected'
    ];

    if (

        !in_array(

            $status,

            $allowedStatuses
        )
    ) {

        $_SESSION['error'] =
        'Invalid status selected.';

        redirect('admin/blogs/comments.php');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            UPDATE blog_comments

            SET

                status = :status,
                updated_at = NOW()

            WHERE id = :id
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':status' =>
            $status,

            ':id' =>
            $commentId
        ]);

        /*
        |--------------------------------------------------------------------------
        | LOG EVENT
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(

            currentUserId(),

            'blog_comment_updated',

            'info',

            'Comment moderation updated'
        );

        $_SESSION['success'] =
        'Comment status updated successfully.';

        redirect('admin/blogs/comments.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to update comment.';
    }
}

/*
|--------------------------------------------------------------------------
| HANDLE DELETE
|--------------------------------------------------------------------------
*/

if (

    isset($_GET['delete'])

    &&

    is_numeric($_GET['delete'])
) {

    validateCsrf();

    $commentId =
    (int) $_GET['delete'];

    try {

        $deleteQuery = "

            DELETE FROM blog_comments

            WHERE id = :id
        ";

        $deleteStmt =
        $conn->prepare($deleteQuery);

        $deleteStmt->execute([

            ':id' => $commentId
        ]);

        logSecurityEvent(

            currentUserId(),

            'blog_comment_deleted',

            'warning',

            'Blog comment deleted'
        );

        $_SESSION['success'] =
        'Comment deleted successfully.';

        redirect('admin/blogs/comments.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to delete comment.';
    }
}

/*
|--------------------------------------------------------------------------
| FETCH COMMENTS
|--------------------------------------------------------------------------
*/

$comments = [];

try {

    $query = "

        SELECT

            bc.*,

            b.title AS blog_title,

            b.slug AS blog_slug

        FROM blog_comments bc

        LEFT JOIN blogs b
        ON bc.blog_id = b.id

        ORDER BY bc.id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute();

    $comments =
    $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to load comments.';
}

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalComments =
count($comments);

$approvedComments =
count(

    array_filter(

        $comments,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'approved';
        }
    )
);

$pendingComments =
count(

    array_filter(

        $comments,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'pending';
        }
    )
);

$spamComments =
count(

    array_filter(

        $comments,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'spam';
        }
    )
);

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

    <style>

        .comment-card{

            background:#fff;

            border-radius:20px;

            padding:24px;

            box-shadow:
            0 4px 20px rgba(0,0,0,0.06);

            margin-bottom:24px;
        }

        .comment-text{

            background:#f9fafb;

            padding:18px;

            border-radius:14px;

            border-left:4px solid #f59e0b;

            margin-top:14px;
        }

    </style>

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

                        Blog Comments

                    </h1>

                    <p>

                        Moderate user comments and manage blog discussions.

                    </p>

                </div>

            </div>

            <!-- ALERTS -->

            <?php if(isset($_SESSION['success'])): ?>

                <div class="alert alert-success">

                    <?php

                    echo escape(
                        $_SESSION['success']
                    );

                    unset($_SESSION['success']);

                    ?>

                </div>

            <?php endif; ?>

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

            <!-- STATS -->

            <div class="row g-4 mb-4">

                <!-- TOTAL -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-primary"
                        >

                            <i class="bi bi-chat-dots-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalComments
                                );

                                ?>

                            </h3>

                            <p>

                                Total Comments

                            </p>

                        </div>

                    </div>

                </div>

                <!-- APPROVED -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-success"
                        >

                            <i class="bi bi-check-circle-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $approvedComments
                                );

                                ?>

                            </h3>

                            <p>

                                Approved

                            </p>

                        </div>

                    </div>

                </div>

                <!-- PENDING -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-warning"
                        >

                            <i class="bi bi-clock-history"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $pendingComments
                                );

                                ?>

                            </h3>

                            <p>

                                Pending

                            </p>

                        </div>

                    </div>

                </div>

                <!-- SPAM -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-danger"
                        >

                            <i class="bi bi-shield-fill-exclamation"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $spamComments
                                );

                                ?>

                            </h3>

                            <p>

                                Spam

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- COMMENTS -->

            <?php if(!empty($comments)): ?>

                <?php foreach($comments as $comment): ?>

                    <div class="comment-card">

                        <div class="row">

                            <!-- COMMENT INFO -->

                            <div class="col-lg-8">

                                <div class="d-flex justify-content-between align-items-start">

                                    <div>

                                        <h5>

                                            <?php

                                            echo escape(

                                                $comment['user_name']
                                            );

                                            ?>

                                        </h5>

                                        <p class="text-muted mb-1">

                                            <?php

                                            echo escape(

                                                $comment['user_email']
                                            );

                                            ?>

                                        </p>

                                        <p class="text-muted mb-0">

                                            On Blog:

                                            <strong>

                                                <?php

                                                echo escape(

                                                    $comment['blog_title']
                                                    ??
                                                    'Deleted Blog'
                                                );

                                                ?>

                                            </strong>

                                        </p>

                                    </div>

                                    <div>

                                        <?php

                                        $status =
                                        strtolower(

                                            $comment['status']
                                            ??
                                            'pending'
                                        );

                                        ?>

                                        <span class="badge

                                            <?php

                                            if($status === 'approved'){

                                                echo 'bg-success';

                                            }elseif($status === 'spam'){

                                                echo 'bg-danger';

                                            }elseif($status === 'rejected'){

                                                echo 'bg-dark';

                                            }else{

                                                echo 'bg-warning';
                                            }

                                            ?>
                                        ">

                                            <?php

                                            echo strtoupper($status);

                                            ?>

                                        </span>

                                    </div>

                                </div>

                                <!-- COMMENT -->

                                <div class="comment-text">

                                    <?php

                                    echo nl2br(

                                        escape(

                                            $comment['comment']
                                        )
                                    );

                                    ?>

                                </div>

                                <!-- FOOTER -->

                                <div class="mt-3">

                                    <small class="text-muted">

                                        <i class="bi bi-clock"></i>

                                        <?php

                                        echo date(

                                            'd M Y h:i A',

                                            strtotime(

                                                $comment['created_at']
                                            )
                                        );

                                        ?>

                                    </small>

                                    <?php if(!empty($comment['ip_address'])): ?>

                                        <small class="text-muted ms-3">

                                            <i class="bi bi-globe"></i>

                                            <?php

                                            echo escape(

                                                $comment['ip_address']
                                            );

                                            ?>

                                        </small>

                                    <?php endif; ?>

                                </div>

                            </div>

                            <!-- ACTIONS -->

                            <div class="col-lg-4">

                                <form method="POST">

                                    <?php echo csrfField(); ?>

                                    <input
                                        type="hidden"
                                        name="comment_id"
                                        value="<?php

                                        echo (int)$comment['id'];

                                        ?>"
                                    >

                                    <!-- STATUS -->

                                    <div class="mb-3">

                                        <label class="form-label">

                                            Moderation Status

                                        </label>

                                        <select
                                            name="status"
                                            class="form-select"
                                        >

                                            <option
                                                value="pending"

                                                <?php

                                                if(

                                                    $status
                                                    ===
                                                    'pending'
                                                ){

                                                    echo 'selected';
                                                }

                                                ?>
                                            >

                                                Pending

                                            </option>

                                            <option
                                                value="approved"

                                                <?php

                                                if(

                                                    $status
                                                    ===
                                                    'approved'
                                                ){

                                                    echo 'selected';
                                                }

                                                ?>
                                            >

                                                Approved

                                            </option>

                                            <option
                                                value="spam"

                                                <?php

                                                if(

                                                    $status
                                                    ===
                                                    'spam'
                                                ){

                                                    echo 'selected';
                                                }

                                                ?>
                                            >

                                                Spam

                                            </option>

                                            <option
                                                value="rejected"

                                                <?php

                                                if(

                                                    $status
                                                    ===
                                                    'rejected'
                                                ){

                                                    echo 'selected';
                                                }

                                                ?>
                                            >

                                                Rejected

                                            </option>

                                        </select>

                                    </div>

                                    <!-- BUTTONS -->

                                    <div class="d-flex gap-2 flex-wrap">

                                        <!-- UPDATE -->

                                        <button
                                            type="submit"
                                            class="btn-admin"
                                        >

                                            <i class="bi bi-check-circle"></i>

                                            Update

                                        </button>

                                        <!-- VIEW BLOG -->

                                        <?php if(!empty($comment['blog_slug'])): ?>

                                            <a
                                                href="../../blog.php?slug=<?php

                                                echo urlencode(

                                                    $comment['blog_slug']
                                                );

                                                ?>"
                                                target="_blank"
                                                class="btn btn-dark"
                                            >

                                                <i class="bi bi-eye"></i>

                                            </a>

                                        <?php endif; ?>

                                        <!-- DELETE -->

                                        <a
                                            href="?delete=<?php

                                            echo (int)$comment['id'];

                                            ?>&csrf_token=<?php

                                            echo csrfToken();

                                            ?>"
                                            class="
                                                btn
                                                btn-danger
                                                btn-delete
                                            "
                                        >

                                            <i class="bi bi-trash"></i>

                                        </a>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="section-card text-center py-5">

                    <i
                        class="
                            bi
                            bi-chat-square-dots
                        "
                        style="
                            font-size:70px;
                            color:#d1d5db;
                        "
                    ></i>

                    <h4 class="mt-4">

                        No Comments Found

                    </h4>

                    <p class="text-muted">

                        Blog comments will appear here for moderation.

                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin JS -->

<script src="<?php echo base_url('assets/admin/js/admin.js'); ?>"></script>

</body>

</html>