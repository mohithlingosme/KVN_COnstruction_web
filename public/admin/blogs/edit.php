<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| EDIT BLOG
|--------------------------------------------------------------------------
| File:
| /public/admin/blogs/edit.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/session.php';

require_once '../../../helpers/csrf.php';

require_once '../../../helpers/rateLimiter.php';

require_once '../../../helpers/upload.php';

/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

$blogId =
(int) ($_GET['id'] ?? 0);

if ($blogId <= 0) {

    $_SESSION['error'] =
    'Invalid blog ID.';

    redirect('admin/blogs/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH BLOG
|--------------------------------------------------------------------------
*/

try {

    $query = "

        SELECT *

        FROM blogs

        WHERE id = :id

        LIMIT 1
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':id' => $blogId
    ]);

    $blog =
    $stmt->fetch();

    if (!$blog) {

        $_SESSION['error'] =
        'Blog not found.';

        redirect('admin/blogs/index.php');
    }

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to load blog.';

    redirect('admin/blogs/index.php');
}

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
'Edit Blog | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| HANDLE UPDATE
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

            'edit_blog',

            10,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many update attempts.';

        redirect(

            'admin/blogs/edit.php?id='
            .
            $blogId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $title =
    sanitize($_POST['title'] ?? '');

    $slug =
    strtolower(

        trim(

            preg_replace(

                '/[^A-Za-z0-9-]+/',

                '-',

                $title
            ),

            '-'
        )
    );

    $category =
    sanitize($_POST['category'] ?? 'General');

    $excerpt =
    sanitize($_POST['excerpt'] ?? '');

    $content =
    trim($_POST['content'] ?? '');

    $metaTitle =
    sanitize($_POST['meta_title'] ?? '');

    $metaDescription =
    sanitize($_POST['meta_description'] ?? '');

    $tags =
    sanitize($_POST['tags'] ?? '');

    $status =
    sanitize($_POST['status'] ?? 'draft');

    $isFeatured =
    isset($_POST['is_featured'])
    ? 1
    : 0;

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        empty($title)

        ||

        empty($content)
    ) {

        $_SESSION['error'] =
        'Title and content are required.';

        redirect(

            'admin/blogs/edit.php?id='
            .
            $blogId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK SLUG DUPLICATE
    |--------------------------------------------------------------------------
    */

    try {

        $slugQuery = "

            SELECT id

            FROM blogs

            WHERE slug = :slug

            AND id != :id

            LIMIT 1
        ";

        $slugStmt =
        $conn->prepare($slugQuery);

        $slugStmt->execute([

            ':slug' => $slug,

            ':id' => $blogId
        ]);

        if($slugStmt->fetch()){

            $slug .=
            '-' .
            time();
        }

    } catch(Exception $e){}

    /*
    |--------------------------------------------------------------------------
    | FEATURED IMAGE
    |--------------------------------------------------------------------------
    */

    $featuredImage =
    $blog['featured_image'];

    if (

        isset($_FILES['featured_image'])

        &&

        $_FILES['featured_image']['error'] === 0
    ) {

        $upload =
        uploadFile(

            $_FILES['featured_image'],

            ROOT_PATH . '/uploads/blogs/',

            [

                'jpg',
                'jpeg',
                'png',
                'webp'
            ]
        );

        if($upload['success']){

            /*
            |--------------------------------------------------------------------------
            | DELETE OLD IMAGE
            |--------------------------------------------------------------------------
            */

            if (

                !empty($blog['featured_image'])
            ) {

                $oldImage =
                ROOT_PATH
                .
                '/uploads/blogs/'
                .
                $blog['featured_image'];

                if(file_exists($oldImage)){

                    unlink($oldImage);
                }
            }

            $featuredImage =
            $upload['filename'];

        } else {

            $_SESSION['error'] =
            $upload['message'];

            redirect(

                'admin/blogs/edit.php?id='
                .
                $blogId
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE BLOG
    |--------------------------------------------------------------------------
    */

    try {

        $updateQuery = "

            UPDATE blogs

            SET

                title = :title,
                slug = :slug,
                category = :category,
                excerpt = :excerpt,
                content = :content,
                featured_image = :featured_image,
                meta_title = :meta_title,
                meta_description = :meta_description,
                tags = :tags,
                status = :status,
                is_featured = :is_featured,
                updated_at = NOW()

            WHERE id = :id
        ";

        $updateStmt =
        $conn->prepare($updateQuery);

        $updateStmt->execute([

            ':title' =>
            $title,

            ':slug' =>
            $slug,

            ':category' =>
            $category,

            ':excerpt' =>
            $excerpt,

            ':content' =>
            $content,

            ':featured_image' =>
            $featuredImage,

            ':meta_title' =>
            $metaTitle,

            ':meta_description' =>
            $metaDescription,

            ':tags' =>
            $tags,

            ':status' =>
            $status,

            ':is_featured' =>
            $isFeatured,

            ':id' =>
            $blogId
        ]);

        /*
        |--------------------------------------------------------------------------
        | LOG EVENT
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(

            currentUserId(),

            'blog_updated',

            'info',

            'Blog updated'
        );

        $_SESSION['success'] =
        'Blog updated successfully.';

        redirect('admin/blogs/index.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to update blog.';
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

    <!-- Summernote -->

    <link
        href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css"
        rel="stylesheet"
    >

    <!-- Admin CSS -->

    <link
        rel="stylesheet"
        href="<?php echo base_url('../assets/admin/css/admin.css'); ?>"
    >

    <style>

        .note-editor{

            border-radius:16px !important;

            overflow:hidden;
        }

        .blog-preview{

            width:100%;

            max-height:300px;

            object-fit:cover;

            border-radius:18px;

            border:1px solid #e5e7eb;
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

                        Edit Blog

                    </h1>

                    <p>

                        Update blog content, SEO and publishing settings.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="index.php"
                        class="btn btn-dark"
                    >

                        Back

                    </a>

                    <a
                        href="../../blog.php?slug=<?php

                        echo urlencode(
                            $blog['slug']
                        );

                        ?>"
                        target="_blank"
                        class="btn btn-primary"
                    >

                        Preview

                    </a>

                </div>

            </div>

            <!-- ALERT -->

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

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <?php echo csrfField(); ?>

                <!-- BLOG DETAILS -->

                <div class="section-card mb-4">

                    <div class="section-header">

                        <h4>

                            Blog Information

                        </h4>

                    </div>

                    <div class="row">

                        <!-- TITLE -->

                        <div class="col-lg-8 mb-4">

                            <label class="form-label">

                                Blog Title

                            </label>

                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                value="<?php

                                echo escape(
                                    $blog['title']
                                );

                                ?>"
                                required
                            >

                        </div>

                        <!-- CATEGORY -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Category

                            </label>

                            <select
                                name="category"
                                class="form-select"
                            >

                                <?php

                                $categories = [

                                    'Construction',
                                    'Interior',
                                    'Architecture',
                                    'Tips',
                                    'News'
                                ];

                                foreach($categories as $category):

                                ?>

                                    <option
                                        value="<?php echo $category; ?>"

                                        <?php

                                        if(

                                            $blog['category']
                                            ===
                                            $category
                                        ){

                                            echo 'selected';
                                        }

                                        ?>
                                    >

                                        <?php

                                        echo $category;

                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <!-- EXCERPT -->

                        <div class="col-lg-12 mb-4">

                            <label class="form-label">

                                Excerpt

                            </label>

                            <textarea
                                name="excerpt"
                                rows="4"
                                class="form-control"
                            ><?php

                            echo escape(
                                $blog['excerpt']
                            );

                            ?></textarea>

                        </div>

                        <!-- FEATURED IMAGE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Featured Image

                            </label>

                            <input
                                type="file"
                                name="featured_image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
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

                                <option
                                    value="draft"

                                    <?php

                                    if(

                                        $blog['status']
                                        ===
                                        'draft'
                                    ){

                                        echo 'selected';
                                    }

                                    ?>
                                >

                                    Draft

                                </option>

                                <option
                                    value="published"

                                    <?php

                                    if(

                                        $blog['status']
                                        ===
                                        'published'
                                    ){

                                        echo 'selected';
                                    }

                                    ?>
                                >

                                    Published

                                </option>

                            </select>

                        </div>

                        <!-- FEATURED -->

                        <div class="col-lg-3 mb-4">

                            <label class="form-label d-block">

                                Featured Blog

                            </label>

                            <div class="form-check form-switch mt-2">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="is_featured"
                                    value="1"

                                    <?php

                                    if(

                                        !empty(
                                            $blog['is_featured']
                                        )
                                    ){

                                        echo 'checked';
                                    }

                                    ?>
                                >

                                <label class="form-check-label">

                                    Mark as featured

                                </label>

                            </div>

                        </div>

                        <!-- PREVIEW -->

                        <?php if(!empty($blog['featured_image'])): ?>

                            <div class="col-lg-12">

                                <img
                                    src="<?php

                                    echo base_url(

                                        '../uploads/blogs/'
                                        .
                                        $blog['featured_image']
                                    );

                                    ?>"
                                    class="blog-preview"
                                    alt="Blog Image"
                                >

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

                <!-- CONTENT -->

                <div class="section-card mb-4">

                    <div class="section-header">

                        <h4>

                            Blog Content

                        </h4>

                    </div>

                    <textarea
                        name="content"
                        id="blogEditor"
                    ><?php

                    echo htmlspecialchars(
                        $blog['content']
                    );

                    ?></textarea>

                </div>

                <!-- SEO -->

                <div class="section-card mb-4">

                    <div class="section-header">

                        <h4>

                            SEO Settings

                        </h4>

                    </div>

                    <div class="row">

                        <!-- META TITLE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Meta Title

                            </label>

                            <input
                                type="text"
                                name="meta_title"
                                class="form-control"
                                value="<?php

                                echo escape(
                                    $blog['meta_title']
                                );

                                ?>"
                            >

                        </div>

                        <!-- TAGS -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Tags

                            </label>

                            <input
                                type="text"
                                name="tags"
                                class="form-control"
                                value="<?php

                                echo escape(
                                    $blog['tags']
                                );

                                ?>"
                            >

                        </div>

                        <!-- META DESCRIPTION -->

                        <div class="col-lg-12 mb-4">

                            <label class="form-label">

                                Meta Description

                            </label>

                            <textarea
                                name="meta_description"
                                rows="4"
                                class="form-control"
                            ><?php

                            echo escape(
                                $blog['meta_description']
                            );

                            ?></textarea>

                        </div>

                    </div>

                </div>

                <!-- BUTTON -->

                <button
                    type="submit"
                    class="btn-admin"
                >

                    <i class="bi bi-check-circle"></i>

                    Update Blog

                </button>

            </form>

        </div>

    </div>

</div>

<!-- JQuery -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Summernote -->

<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>

<script>

$(document).ready(function(){

    $('#blogEditor').summernote({

        height:500,

        placeholder:
        'Write your blog content here...',

        toolbar: [

            ['style', ['style']],

            ['font', ['bold', 'underline', 'clear']],

            ['fontname', ['fontname']],

            ['para', ['ul', 'ol', 'paragraph']],

            ['table', ['table']],

            ['insert', ['link', 'picture', 'video']],

            ['view', ['fullscreen', 'codeview']]
        ]
    });
});

</script>

<!-- Admin JS -->

<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>

</body>

</html>