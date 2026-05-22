<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| BLOG TAGS MANAGEMENT
|--------------------------------------------------------------------------
| File:
| /public/admin/blogs/tags.php
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
'Blog Tags | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| CREATE TABLE IF NOT EXISTS
|--------------------------------------------------------------------------
*/

try {

    $conn->exec("

        CREATE TABLE IF NOT EXISTS blog_tags (

            id INT PRIMARY KEY AUTO_INCREMENT,

            tag_name VARCHAR(255) NOT NULL,

            slug VARCHAR(255) NOT NULL UNIQUE,

            description TEXT NULL,

            status ENUM(
                'active',
                'inactive'
            ) DEFAULT 'active',

            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP
        )
    ");

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| HANDLE CREATE TAG
|--------------------------------------------------------------------------
*/

if (

    $_SERVER['REQUEST_METHOD'] === 'POST'

    &&

    isset($_POST['create_tag'])
) {

    validateCsrf();

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT
    |--------------------------------------------------------------------------
    */

    if (

        !checkRateLimit(

            'create_blog_tag',

            10,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests.';

        redirect('admin/blogs/tags.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $tagName =
    sanitize($_POST['tag_name'] ?? '');

    $slug =
    strtolower(

        trim(

            preg_replace(

                '/[^A-Za-z0-9-]+/',

                '-',

                $tagName
            ),

            '-'
        )
    );

    $description =
    sanitize($_POST['description'] ?? '');

    $status =
    sanitize($_POST['status'] ?? 'active');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (empty($tagName)) {

        $_SESSION['error'] =
        'Tag name is required.';

        redirect('admin/blogs/tags.php');
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE
    |--------------------------------------------------------------------------
    */

    try {

        $checkQuery = "

            SELECT id

            FROM blog_tags

            WHERE slug = :slug

            LIMIT 1
        ";

        $checkStmt =
        $conn->prepare($checkQuery);

        $checkStmt->execute([

            ':slug' => $slug
        ]);

        if($checkStmt->fetch()){

            $_SESSION['error'] =
            'Tag already exists.';

            redirect('admin/blogs/tags.php');
        }

    } catch(Exception $e){}

    /*
    |--------------------------------------------------------------------------
    | INSERT TAG
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            INSERT INTO blog_tags (

                tag_name,
                slug,
                description,
                status,
                created_at

            ) VALUES (

                :tag_name,
                :slug,
                :description,
                :status,
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':tag_name' =>
            $tagName,

            ':slug' =>
            $slug,

            ':description' =>
            $description,

            ':status' =>
            $status
        ]);

        /*
        |--------------------------------------------------------------------------
        | LOG EVENT
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(

            currentUserId(),

            'blog_tag_created',

            'info',

            'Blog tag created'
        );

        $_SESSION['success'] =
        'Tag created successfully.';

        redirect('admin/blogs/tags.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to create tag.';
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

    $tagId =
    (int) $_GET['delete'];

    try {

        /*
        |--------------------------------------------------------------------------
        | FETCH TAG
        |--------------------------------------------------------------------------
        */

        $tagQuery = "

            SELECT tag_name

            FROM blog_tags

            WHERE id = :id

            LIMIT 1
        ";

        $tagStmt =
        $conn->prepare($tagQuery);

        $tagStmt->execute([

            ':id' => $tagId
        ]);

        $tag =
        $tagStmt->fetch();

        /*
        |--------------------------------------------------------------------------
        | CHECK BLOG USAGE
        |--------------------------------------------------------------------------
        */

        if($tag){

            $countQuery = "

                SELECT COUNT(*) AS total

                FROM blogs

                WHERE tags LIKE :tag
            ";

            $countStmt =
            $conn->prepare($countQuery);

            $countStmt->execute([

                ':tag' =>
                '%' .
                $tag['tag_name']
                .
                '%'
            ]);

            $usage =
            $countStmt->fetch();

            if (

                !empty($usage['total'])
            ) {

                $_SESSION['error'] =
                'Cannot delete tag assigned to blogs.';

                redirect('admin/blogs/tags.php');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE TAG
        |--------------------------------------------------------------------------
        */

        $deleteQuery = "

            DELETE FROM blog_tags

            WHERE id = :id
        ";

        $deleteStmt =
        $conn->prepare($deleteQuery);

        $deleteStmt->execute([

            ':id' => $tagId
        ]);

        logSecurityEvent(

            currentUserId(),

            'blog_tag_deleted',

            'warning',

            'Blog tag deleted'
        );

        $_SESSION['success'] =
        'Tag deleted successfully.';

        redirect('admin/blogs/tags.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to delete tag.';
    }
}

/*
|--------------------------------------------------------------------------
| FETCH TAGS
|--------------------------------------------------------------------------
*/

$tags = [];

try {

    $query = "

        SELECT
            bt.*,

            (
                SELECT COUNT(*)

                FROM blogs b

                WHERE b.tags LIKE CONCAT(
                    '%',
                    bt.tag_name,
                    '%'
                )
            ) AS blog_count

        FROM blog_tags bt

        ORDER BY bt.id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute();

    $tags =
    $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to load tags.';
}

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalTags =
count($tags);

$activeTags =
count(

    array_filter(

        $tags,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'active';
        }
    )
);

$totalTaggedBlogs =
array_sum(

    array_column(

        $tags,

        'blog_count'
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

                        Blog Tags

                    </h1>

                    <p>

                        Manage SEO tags and blog keywords.

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

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-primary"
                        >

                            <i class="bi bi-tags-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalTags
                                );

                                ?>

                            </h3>

                            <p>

                                Total Tags

                            </p>

                        </div>

                    </div>

                </div>

                <div class="col-lg-4">

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
                                    $activeTags
                                );

                                ?>

                            </h3>

                            <p>

                                Active Tags

                            </p>

                        </div>

                    </div>

                </div>

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-warning"
                        >

                            <i class="bi bi-journal-text"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalTaggedBlogs
                                );

                                ?>

                            </h3>

                            <p>

                                Tagged Blogs

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- CREATE TAG -->

            <div class="section-card mb-4">

                <div class="section-header">

                    <h4>

                        Create Tag

                    </h4>

                </div>

                <form method="POST">

                    <?php echo csrfField(); ?>

                    <input
                        type="hidden"
                        name="create_tag"
                        value="1"
                    >

                    <div class="row">

                        <!-- TAG NAME -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Tag Name

                            </label>

                            <input
                                type="text"
                                name="tag_name"
                                class="form-control"
                                required
                            >

                        </div>

                        <!-- STATUS -->

                        <div class="col-lg-3 mb-4">

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

                        <!-- DESCRIPTION -->

                        <div class="col-lg-12 mb-4">

                            <label class="form-label">

                                Description

                            </label>

                            <textarea
                                name="description"
                                rows="4"
                                class="form-control"
                            ></textarea>

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="btn-admin"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Create Tag

                    </button>

                </form>

            </div>

            <!-- TAGS TABLE -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Tags List

                    </h4>

                </div>

                <div class="table-responsive">

                    <table class="table admin-table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Tag</th>

                                <th>Slug</th>

                                <th>Usage</th>

                                <th>Status</th>

                                <th>Created</th>

                                <th width="150">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($tags)): ?>

                                <?php foreach($tags as $tag): ?>

                                    <tr>

                                        <!-- ID -->

                                        <td>

                                            #<?php echo $tag['id']; ?>

                                        </td>

                                        <!-- TAG -->

                                        <td>

                                            <strong>

                                                #<?php

                                                echo escape(

                                                    $tag['tag_name']
                                                );

                                                ?>

                                            </strong>

                                            <br>

                                            <small class="text-muted">

                                                <?php

                                                echo escape(

                                                    $tag['description']
                                                    ??
                                                    'No description'
                                                );

                                                ?>

                                            </small>

                                        </td>

                                        <!-- SLUG -->

                                        <td>

                                            <?php

                                            echo escape(

                                                $tag['slug']
                                            );

                                            ?>

                                        </td>

                                        <!-- COUNT -->

                                        <td>

                                            <span class="badge bg-dark">

                                                <?php

                                                echo number_format(

                                                    $tag['blog_count']
                                                );

                                                ?>

                                            </span>

                                        </td>

                                        <!-- STATUS -->

                                        <td>

                                            <span class="badge

                                                <?php

                                                echo

                                                strtolower(

                                                    $tag['status']
                                                )

                                                ===

                                                'active'

                                                ?

                                                'bg-success'

                                                :

                                                'bg-secondary';

                                                ?>
                                            ">

                                                <?php

                                                echo ucfirst(

                                                    escape(

                                                        $tag['status']
                                                    )
                                                );

                                                ?>

                                            </span>

                                        </td>

                                        <!-- DATE -->

                                        <td>

                                            <?php

                                            echo date(

                                                'd M Y',

                                                strtotime(

                                                    $tag['created_at']
                                                )
                                            );

                                            ?>

                                        </td>

                                        <!-- ACTIONS -->

                                        <td>

                                            <a
                                                href="?delete=<?php

                                                echo $tag['id'];

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

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="7">

                                        <div class="text-center py-5">

                                            <i
                                                class="
                                                    bi
                                                    bi-tags
                                                "
                                                style="
                                                    font-size:70px;
                                                    color:#d1d5db;
                                                "
                                            ></i>

                                            <h4 class="mt-4">

                                                No Tags Found

                                            </h4>

                                            <p class="text-muted">

                                                Create tags to improve SEO and blog discoverability.

                                            </p>

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

<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>

</body>

</html>