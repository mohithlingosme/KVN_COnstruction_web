<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| BLOG MANAGEMENT
|--------------------------------------------------------------------------
| File:
| /public/admin/blogs/index.php
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
'Blog Management | ' . APP_NAME;

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

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT
    |--------------------------------------------------------------------------
    */

    if (

        !checkRateLimit(

            'delete_blog',

            10,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many delete requests.';

        redirect('admin/blogs/index.php');
    }

    $blogId =
    (int) $_GET['delete'];

    try {

        /*
        |--------------------------------------------------------------------------
        | FETCH IMAGE
        |--------------------------------------------------------------------------
        */

        $fetchQuery = "

            SELECT featured_image

            FROM blogs

            WHERE id = :id

            LIMIT 1
        ";

        $fetchStmt =
        $conn->prepare($fetchQuery);

        $fetchStmt->execute([

            ':id' => $blogId
        ]);

        $blog =
        $fetchStmt->fetch();

        /*
        |--------------------------------------------------------------------------
        | DELETE IMAGE
        |--------------------------------------------------------------------------
        */

        if (

            $blog

            &&

            !empty($blog['featured_image'])
        ) {

            $imagePath =
            ROOT_PATH
            .
            '/uploads/blogs/'
            .
            $blog['featured_image'];

            if(file_exists($imagePath)){

                unlink($imagePath);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE BLOG
        |--------------------------------------------------------------------------
        */

        $deleteQuery = "

            DELETE FROM blogs

            WHERE id = :id
        ";

        $deleteStmt =
        $conn->prepare($deleteQuery);

        $deleteStmt->execute([

            ':id' => $blogId
        ]);

        /*
        |--------------------------------------------------------------------------
        | LOG EVENT
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(

            currentUserId(),

            'blog_deleted',

            'warning',

            'Blog deleted'
        );

        $_SESSION['success'] =
        'Blog deleted successfully.';

        redirect('admin/blogs/index.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to delete blog.';
    }
}

/*
|--------------------------------------------------------------------------
| FETCH BLOGS
|--------------------------------------------------------------------------
*/

$blogs = [];

try {

    $query = "

        SELECT

            b.*,

            u.full_name AS author_name

        FROM blogs b

        LEFT JOIN users u
        ON b.author_id = u.id

        ORDER BY b.id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute();

    $blogs =
    $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to load blogs.';
}

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalBlogs =
count($blogs);

$publishedBlogs =
count(

    array_filter(

        $blogs,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'published';
        }
    )
);

$draftBlogs =
count(

    array_filter(

        $blogs,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'draft';
        }
    )
);

$totalViews =
array_sum(

    array_column(

        $blogs,

        'views'
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

        .blog-thumbnail{

            width:80px;

            height:60px;

            object-fit:cover;

            border-radius:10px;
        }

        .empty-state{

            text-align:center;

            padding:80px 20px;
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

                        Blog Management

                    </h1>

                    <p>

                        Manage blogs, articles and content publishing.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <!-- CREATE -->

                    <a
                        href="create.php"
                        class="btn-admin"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Create Blog

                    </a>

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

                            <i class="bi bi-journal-text"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalBlogs
                                );

                                ?>

                            </h3>

                            <p>

                                Total Blogs

                            </p>

                        </div>

                    </div>

                </div>

                <!-- PUBLISHED -->

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
                                    $publishedBlogs
                                );

                                ?>

                            </h3>

                            <p>

                                Published

                            </p>

                        </div>

                    </div>

                </div>

                <!-- DRAFT -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-warning"
                        >

                            <i class="bi bi-pencil-square"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $draftBlogs
                                );

                                ?>

                            </h3>

                            <p>

                                Drafts

                            </p>

                        </div>

                    </div>

                </div>

                <!-- VIEWS -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-danger"
                        >

                            <i class="bi bi-eye-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalViews
                                );

                                ?>

                            </h3>

                            <p>

                                Total Views

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- BLOG TABLE -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Blog Articles

                    </h4>

                </div>

                <!-- SEARCH -->

                <div class="row mb-4">

                    <div class="col-lg-4">

                        <input
                            type="text"
                            class="form-control table-search"
                            data-table="#blogsTable"
                            placeholder="Search blogs..."
                        >

                    </div>

                </div>

                <!-- TABLE -->

                <div class="table-responsive">

                    <table
                        class="table admin-table"
                        id="blogsTable"
                    >

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Thumbnail</th>

                                <th>Title</th>

                                <th>Category</th>

                                <th>Author</th>

                                <th>Status</th>

                                <th>Views</th>

                                <th>Date</th>

                                <th width="220">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($blogs)): ?>

                                <?php foreach($blogs as $blog): ?>

                                    <tr>

                                        <!-- ID -->

                                        <td>

                                            #<?php echo (int)$blog['id']; ?>

                                        </td>

                                        <!-- THUMBNAIL -->

                                        <td>

                                            <?php if(!empty($blog['featured_image'])): ?>

                                                <img
                                                    src="<?php

                                                    echo base_url(

                                                        '../uploads/blogs/'
                                                        .
                                                        $blog['featured_image']
                                                    );

                                                    ?>"
                                                    class="blog-thumbnail"
                                                    alt="Blog"
                                                >

                                            <?php else: ?>

                                                <div
                                                    class="
                                                        blog-thumbnail
                                                        bg-light
                                                        d-flex
                                                        align-items-center
                                                        justify-content-center
                                                    "
                                                >

                                                    <i
                                                        class="
                                                            bi
                                                            bi-image
                                                        "
                                                    ></i>

                                                </div>

                                            <?php endif; ?>

                                        </td>

                                        <!-- TITLE -->

                                        <td>

                                            <strong>

                                                <?php

                                                echo escape(

                                                    $blog['title']
                                                );

                                                ?>

                                            </strong>

                                            <br>

                                            <small class="text-muted">

                                                <?php

                                                echo escape(

                                                    substr(

                                                        strip_tags(

                                                            $blog['content']
                                                            ??
                                                            ''
                                                        ),

                                                        0,

                                                        80
                                                    )
                                                );

                                                ?>...

                                            </small>

                                        </td>

                                        <!-- CATEGORY -->

                                        <td>

                                            <span class="badge bg-dark">

                                                <?php

                                                echo escape(

                                                    $blog['category']
                                                    ??
                                                    'General'
                                                );

                                                ?>

                                            </span>

                                        </td>

                                        <!-- AUTHOR -->

                                        <td>

                                            <?php

                                            echo escape(

                                                $blog['author_name']
                                                ??
                                                'Admin'
                                            );

                                            ?>

                                        </td>

                                        <!-- STATUS -->

                                        <td>

                                            <?php

                                            $status =
                                            strtolower(

                                                $blog['status']
                                                ??
                                                'draft'
                                            );

                                            ?>

                                            <span class="badge

                                                <?php

                                                if($status === 'published'){

                                                    echo 'bg-success';

                                                }elseif($status === 'draft'){

                                                    echo 'bg-warning';

                                                }else{

                                                    echo 'bg-secondary';
                                                }

                                                ?>
                                            ">

                                                <?php

                                                echo ucfirst($status);

                                                ?>

                                            </span>

                                        </td>

                                        <!-- VIEWS -->

                                        <td>

                                            <?php

                                            echo number_format(

                                                $blog['views']
                                                ??
                                                0
                                            );

                                            ?>

                                        </td>

                                        <!-- DATE -->

                                        <td>

                                            <?php

                                            echo !empty(

                                                $blog['created_at']
                                            )

                                            ?

                                            date(

                                                'd M Y',

                                                strtotime(

                                                    $blog['created_at']
                                                )
                                            )

                                            :

                                            'N/A';

                                            ?>

                                        </td>

                                        <!-- ACTIONS -->

                                        <td>

                                            <div class="d-flex gap-2 flex-wrap">

                                                <!-- VIEW -->

                                                <a
                                                    href="../../blog.php?slug=<?php

                                                    echo urlencode(

                                                        $blog['slug']
                                                    );

                                                    ?>"
                                                    target="_blank"
                                                    class="btn btn-sm btn-dark"
                                                >

                                                    <i class="bi bi-eye"></i>

                                                </a>

                                                <!-- EDIT -->

                                                <a
                                                    href="edit.php?id=<?= (int)$blog['id'] ?>"
                                                    class="btn btn-sm btn-primary"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>

                                                <!-- DELETE -->

                                                <a
                                                    href="?delete=<?php

                                                    echo (int)$blog['id'];

                                                    ?>&csrf_token=<?php

                                                    echo csrfToken();

                                                    ?>"
                                                    class="
                                                        btn
                                                        btn-sm
                                                        btn-danger
                                                        btn-delete
                                                    "
                                                >

                                                    <i class="bi bi-trash"></i>

                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="9">

                                        <div class="empty-state">

                                            <i
                                                class="
                                                    bi
                                                    bi-journal-x
                                                "
                                                style="
                                                    font-size:70px;
                                                    color:#d1d5db;
                                                "
                                            ></i>

                                            <h4 class="mt-4">

                                                No Blogs Found

                                            </h4>

                                            <p class="text-muted">

                                                Start creating your first blog article.

                                            </p>

                                            <a
                                                href="create.php"
                                                class="btn-admin mt-3"
                                            >

                                                <i class="bi bi-plus-circle"></i>

                                                Create Blog

                                            </a>

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

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin JS -->

<script src="<?php echo base_url('assets/admin/js/admin.js'); ?>"></script>

</body>

</html>