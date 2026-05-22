<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| PROJECT GALLERY
|--------------------------------------------------------------------------
| File:
| /public/admin/projects/gallery.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/csrf.php';

require_once '../../../helpers/upload.php';

require_once '../../../helpers/session.php';

/*
|--------------------------------------------------------------------------
| VALIDATE PROJECT ID
|--------------------------------------------------------------------------
*/

$projectId =
(int) ($_GET['id'] ?? 0);

if ($projectId <= 0) {

    $_SESSION['error'] =
    'Invalid project ID.';

    redirect('admin/projects/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH PROJECT
|--------------------------------------------------------------------------
*/

$projectQuery = "

    SELECT
        id,
        project_name,
        project_type,
        location

    FROM projects

    WHERE id = :id

    LIMIT 1
";

$projectStmt =
$conn->prepare($projectQuery);

$projectStmt->execute([

    ':id' => $projectId
]);

$project =
$projectStmt->fetch();

if (!$project) {

    $_SESSION['error'] =
    'Project not found.';

    redirect('admin/projects/index.php');
}

/*
|--------------------------------------------------------------------------
| HANDLE IMAGE UPLOAD
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validateCsrf();

    /*
    |--------------------------------------------------------------------------
    | VALIDATE FILE
    |--------------------------------------------------------------------------
    */

    if (

        !isset($_FILES['gallery_image'])

        ||

        $_FILES['gallery_image']['error'] !== 0
    ) {

        $_SESSION['error'] =
        'Please select an image.';

        redirect(

            'admin/projects/gallery.php?id='
            .
            $projectId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | IMAGE UPLOAD
    |--------------------------------------------------------------------------
    */

    $upload =
    uploadFile(

        $_FILES['gallery_image'],

        ROOT_PATH . '/uploads/project-gallery/',

        [

            'jpg',
            'jpeg',
            'png',
            'webp'
        ]
    );

    if (!$upload['success']) {

        $_SESSION['error'] =
        $upload['message'];

        redirect(

            'admin/projects/gallery.php?id='
            .
            $projectId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT IMAGE
    |--------------------------------------------------------------------------
    */

    try {

        $title =
        sanitize($_POST['title'] ?? '');

        $description =
        sanitize($_POST['description'] ?? '');

        $query = "

            INSERT INTO project_gallery (

                project_id,
                image,
                title,
                description,
                created_at

            ) VALUES (

                :project_id,
                :image,
                :title,
                :description,
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':project_id' =>
            $projectId,

            ':image' =>
            $upload['filename'],

            ':title' =>
            $title,

            ':description' =>
            $description
        ]);

        /*
        |--------------------------------------------------------------------------
        | LOG EVENT
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(

            currentUserId(),

            'project_gallery_upload',

            'info',

            'Uploaded project gallery image'
        );

        $_SESSION['success'] =
        'Gallery image uploaded successfully.';

        redirect(

            'admin/projects/gallery.php?id='
            .
            $projectId
        );

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to upload gallery image.';

        redirect(

            'admin/projects/gallery.php?id='
            .
            $projectId
        );
    }
}

/*
|--------------------------------------------------------------------------
| DELETE IMAGE
|--------------------------------------------------------------------------
*/

if (

    isset($_GET['delete'])

    &&

    is_numeric($_GET['delete'])
) {

    $galleryId =
    (int) $_GET['delete'];

    try {

        /*
        |--------------------------------------------------------------------------
        | FETCH IMAGE
        |--------------------------------------------------------------------------
        */

        $fetchQuery = "

            SELECT image

            FROM project_gallery

            WHERE id = :id

            LIMIT 1
        ";

        $fetchStmt =
        $conn->prepare($fetchQuery);

        $fetchStmt->execute([

            ':id' => $galleryId
        ]);

        $image =
        $fetchStmt->fetch();

        /*
        |--------------------------------------------------------------------------
        | DELETE FILE
        |--------------------------------------------------------------------------
        */

        if (

            $image

            &&

            !empty($image['image'])
        ) {

            $filePath =
            ROOT_PATH
            .
            '/uploads/project-gallery/'
            .
            $image['image'];

            if (file_exists($filePath)) {

                unlink($filePath);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE RECORD
        |--------------------------------------------------------------------------
        */

        $deleteQuery = "

            DELETE FROM project_gallery

            WHERE id = :id
        ";

        $deleteStmt =
        $conn->prepare($deleteQuery);

        $deleteStmt->execute([

            ':id' => $galleryId
        ]);

        $_SESSION['success'] =
        'Gallery image deleted successfully.';

        redirect(

            'admin/projects/gallery.php?id='
            .
            $projectId
        );

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to delete image.';
    }
}

/*
|--------------------------------------------------------------------------
| FETCH GALLERY
|--------------------------------------------------------------------------
*/

$gallery = [];

try {

    $galleryQuery = "

        SELECT *

        FROM project_gallery

        WHERE project_id = :project_id

        ORDER BY id DESC
    ";

    $galleryStmt =
    $conn->prepare($galleryQuery);

    $galleryStmt->execute([

        ':project_id' => $projectId
    ]);

    $gallery =
    $galleryStmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
$project['project_name']
.
' Gallery | '
.
APP_NAME;

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

    <style>

        .gallery-grid{

            display:grid;

            grid-template-columns:
            repeat(auto-fill,minmax(280px,1fr));

            gap:24px;
        }

        .gallery-card{

            background:#fff;

            border-radius:18px;

            overflow:hidden;

            box-shadow:
            0 4px 20px rgba(0,0,0,0.08);

            transition:0.3s;
        }

        .gallery-card:hover{

            transform:translateY(-5px);
        }

        .gallery-image{

            width:100%;

            height:220px;

            object-fit:cover;
        }

        .gallery-content{

            padding:18px;
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

            <!-- ================================= -->
            <!-- HEADER -->
            <!-- ================================= -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Project Gallery

                    </h1>

                    <p>

                        Manage project images and site progress gallery.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="view.php?id=<?php echo $projectId; ?>"
                        class="btn btn-dark"
                    >

                        Back

                    </a>

                </div>

            </div>

            <!-- ================================= -->
            <!-- ALERTS -->
            <!-- ================================= -->

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

            <!-- ================================= -->
            <!-- PROJECT CARD -->
            <!-- ================================= -->

            <div class="section-card mb-4">

                <h3>

                    <?php

                    echo escape(
                        $project['project_name']
                    );

                    ?>

                </h3>

                <p class="mb-1">

                    <strong>Type:</strong>

                    <?php

                    echo escape(
                        $project['project_type']
                    );

                    ?>

                </p>

                <p>

                    <strong>Location:</strong>

                    <?php

                    echo escape(
                        $project['location']
                    );

                    ?>

                </p>

            </div>

            <!-- ================================= -->
            <!-- UPLOAD FORM -->
            <!-- ================================= -->

            <div class="section-card mb-4">

                <div class="section-header">

                    <h4>

                        Upload Gallery Image

                    </h4>

                </div>

                <form
                    method="POST"
                    enctype="multipart/form-data"
                >

                    <?php echo csrfField(); ?>

                    <div class="row">

                        <!-- TITLE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Image Title

                            </label>

                            <input
                                type="text"
                                name="title"
                                class="form-control"
                            >

                        </div>

                        <!-- IMAGE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Select Image

                            </label>

                            <input
                                type="file"
                                name="gallery_image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                                required
                            >

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

                        <i class="bi bi-upload"></i>

                        Upload Image

                    </button>

                </form>

            </div>

            <!-- ================================= -->
            <!-- GALLERY -->
            <!-- ================================= -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Project Gallery

                    </h4>

                </div>

                <?php if(!empty($gallery)): ?>

                    <div class="gallery-grid">

                        <?php foreach($gallery as $image): ?>

                            <div class="gallery-card">

                                <!-- IMAGE -->

                                <img
                                    src="<?php

                                    echo base_url(

                                        '../uploads/project-gallery/'
                                        .
                                        $image['image']
                                    );

                                    ?>"
                                    alt="Gallery"
                                    class="gallery-image"
                                >

                                <!-- CONTENT -->

                                <div class="gallery-content">

                                    <h5>

                                        <?php

                                        echo escape(

                                            $image['title']
                                            ??
                                            'Project Image'
                                        );

                                        ?>

                                    </h5>

                                    <p class="text-muted">

                                        <?php

                                        echo nl2br(

                                            escape(

                                                $image['description']
                                                ??
                                                ''
                                            )
                                        );

                                        ?>

                                    </p>

                                    <small class="text-muted">

                                        Uploaded:

                                        <?php

                                        echo date(

                                            'd M Y h:i A',

                                            strtotime(

                                                $image['created_at']
                                            )
                                        );

                                        ?>

                                    </small>

                                    <!-- ACTIONS -->

                                    <div class="mt-3 d-flex gap-2">

                                        <!-- VIEW -->

                                        <a
                                            href="<?php

                                            echo base_url(

                                                '../uploads/project-gallery/'
                                                .
                                                $image['image']
                                            );

                                            ?>"
                                            target="_blank"
                                            class="btn btn-sm btn-dark"
                                        >

                                            <i class="bi bi-eye"></i>

                                        </a>

                                        <!-- DELETE -->

                                        <a
                                            href="?id=<?php

                                            echo $projectId;

                                            ?>&delete=<?php

                                            echo $image['id'];

                                            ?>"
                                            class="btn btn-sm btn-danger btn-delete"
                                        >

                                            <i class="bi bi-trash"></i>

                                        </a>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5">

                        <i
                            class="bi bi-images"
                            style="
                                font-size:60px;
                                color:#d1d5db;
                            "
                        ></i>

                        <p class="mt-3">

                            No gallery images uploaded.

                        </p>

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