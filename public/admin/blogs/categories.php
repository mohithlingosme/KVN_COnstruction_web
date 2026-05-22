<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| BLOG CATEGORIES MANAGEMENT
|--------------------------------------------------------------------------
| File:
| /public/admin/blogs/categories.php
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
'Blog Categories | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| CREATE TABLE IF NOT EXISTS
|--------------------------------------------------------------------------
*/

try {

    $conn->exec("

        CREATE TABLE IF NOT EXISTS blog_categories (

            id INT PRIMARY KEY AUTO_INCREMENT,

            category_name VARCHAR(255) NOT NULL,

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
| HANDLE CREATE CATEGORY
|--------------------------------------------------------------------------
*/

if (

    $_SERVER['REQUEST_METHOD'] === 'POST'

    &&

    isset($_POST['create_category'])
) {

    validateCsrf();

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT
    |--------------------------------------------------------------------------
    */

    if (

        !checkRateLimit(

            'create_blog_category',

            10,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests.';

        redirect('admin/blogs/categories.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $categoryName =
    sanitize($_POST['category_name'] ?? '');

    $slug =
    strtolower(

        trim(

            preg_replace(

                '/[^A-Za-z0-9-]+/',

                '-',

                $categoryName
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

    if (empty($categoryName)) {

        $_SESSION['error'] =
        'Category name is required.';

        redirect('admin/blogs/categories.php');
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE
    |--------------------------------------------------------------------------
    */

    try {

        $checkQuery = "

            SELECT id

            FROM blog_categories

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
            'Category already exists.';

            redirect('admin/blogs/categories.php');
        }

    } catch(Exception $e){}

    /*
    |--------------------------------------------------------------------------
    | INSERT CATEGORY
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            INSERT INTO blog_categories (

                category_name,
                slug,
                description,
                status,
                created_at

            ) VALUES (

                :category_name,
                :slug,
                :description,
                :status,
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':category_name' =>
            $categoryName,

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

            'blog_category_created',

            'info',

            'Blog category created'
        );

        $_SESSION['success'] =
        'Category created successfully.';

        redirect('admin/blogs/categories.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to create category.';
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

    $categoryId =
    (int) $_GET['delete'];

    try {

        /*
        |--------------------------------------------------------------------------
        | CHECK BLOG COUNT
        |--------------------------------------------------------------------------
        */

        $countQuery = "

            SELECT COUNT(*) AS total

            FROM blogs

            WHERE category = (

                SELECT category_name

                FROM blog_categories

                WHERE id = :id
            )
        ";

        $countStmt =
        $conn->prepare($countQuery);

        $countStmt->execute([

            ':id' => $categoryId
        ]);

        $blogCount =
        $countStmt->fetch();

        if (

            !empty($blogCount['total'])
        ) {

            $_SESSION['error'] =
            'Cannot delete category with blogs assigned.';

            redirect('admin/blogs/categories.php');
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE CATEGORY
        |--------------------------------------------------------------------------
        */

        $deleteQuery = "

            DELETE FROM blog_categories

            WHERE id = :id
        ";

        $deleteStmt =
        $conn->prepare($deleteQuery);

        $deleteStmt->execute([

            ':id' => $categoryId
        ]);

        logSecurityEvent(

            currentUserId(),

            'blog_category_deleted',

            'warning',

            'Blog category deleted'
        );

        $_SESSION['success'] =
        'Category deleted successfully.';

        redirect('admin/blogs/categories.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to delete category.';
    }
}

/*
|--------------------------------------------------------------------------
| FETCH CATEGORIES
|--------------------------------------------------------------------------
*/

$categories = [];

try {

    $query = "

        SELECT
            bc.*,

            (
                SELECT COUNT(*)

                FROM blogs b

                WHERE b.category = bc.category_name
            ) AS blog_count

        FROM blog_categories bc

        ORDER BY bc.id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute();

    $categories =
    $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to load categories.';
}

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalCategories =
count($categories);

$activeCategories =
count(

    array_filter(

        $categories,

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

$totalBlogs =
array_sum(

    array_column(

        $categories,

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

                        Blog Categories

                    </h1>

                    <p>

                        Manage blog categories and organize articles.

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

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-primary"
                        >

                            <i class="bi bi-folder-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalCategories
                                );

                                ?>

                            </h3>

                            <p>

                                Total Categories

                            </p>

                        </div>

                    </div>

                </div>

                <!-- ACTIVE -->

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
                                    $activeCategories
                                );

                                ?>

                            </h3>

                            <p>

                                Active Categories

                            </p>

                        </div>

                    </div>

                </div>

                <!-- BLOGS -->

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
                                    $totalBlogs
                                );

                                ?>

                            </h3>

                            <p>

                                Assigned Blogs

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- CREATE FORM -->

            <div class="section-card mb-4">

                <div class="section-header">

                    <h4>

                        Create Category

                    </h4>

                </div>

                <form method="POST">

                    <?php echo csrfField(); ?>

                    <input
                        type="hidden"
                        name="create_category"
                        value="1"
                    >

                    <div class="row">

                        <!-- CATEGORY NAME -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Category Name

                            </label>

                            <input
                                type="text"
                                name="category_name"
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

                        Create Category

                    </button>

                </form>

            </div>

            <!-- CATEGORY TABLE -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Categories List

                    </h4>

                </div>

                <div class="table-responsive">

                    <table class="table admin-table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Category</th>

                                <th>Slug</th>

                                <th>Blogs</th>

                                <th>Status</th>

                                <th>Created</th>

                                <th width="150">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($categories)): ?>

                                <?php foreach($categories as $category): ?>

                                    <tr>

                                        <!-- ID -->

                                        <td>

                                            #<?php echo $category['id']; ?>

                                        </td>

                                        <!-- NAME -->

                                        <td>

                                            <strong>

                                                <?php

                                                echo escape(

                                                    $category['category_name']
                                                );

                                                ?>

                                            </strong>

                                            <br>

                                            <small class="text-muted">

                                                <?php

                                                echo escape(

                                                    $category['description']
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

                                                $category['slug']
                                            );

                                            ?>

                                        </td>

                                        <!-- BLOG COUNT -->

                                        <td>

                                            <span class="badge bg-dark">

                                                <?php

                                                echo number_format(

                                                    $category['blog_count']
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

                                                    $category['status']
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

                                                        $category['status']
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

                                                    $category['created_at']
                                                )
                                            );

                                            ?>

                                        </td>

                                        <!-- ACTIONS -->

                                        <td>

                                            <div class="d-flex gap-2">

                                                <!-- DELETE -->

                                                <a
                                                    href="?delete=<?php

                                                    echo $category['id'];

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

                                    <td colspan="7">

                                        <div class="text-center py-5">

                                            <i
                                                class="
                                                    bi
                                                    bi-folder-x
                                                "
                                                style="
                                                    font-size:70px;
                                                    color:#d1d5db;
                                                "
                                            ></i>

                                            <h4 class="mt-4">

                                                No Categories Found

                                            </h4>

                                            <p class="text-muted">

                                                Create categories to organize blog content.

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