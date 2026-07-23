<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| PROJECT GALLERY MANAGEMENT
|--------------------------------------------------------------------------
| File: /public/admin/projects/gallery.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';
require_once '../../../helpers/security.php';
require_once '../../../helpers/formatter.php';
require_once '../../../helpers/csrf.php';
require_once '../../../app/controllers/admin/MediaController.php';

/*
|--------------------------------------------------------------------------
| PROJECT ID
|--------------------------------------------------------------------------
*/

$projectId = (int) ($_GET['project_id'] ?? 0);

if ($projectId <= 0) {
    $_SESSION['error'] = 'Invalid project ID.';
    redirect('admin/projects/index.php');
}

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle = 'Project Gallery | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FETCH PROJECT
|--------------------------------------------------------------------------
*/

try {
    require_once '../../../app/models/Project.php';
    $projectModel = new Project($conn);
    $project = $projectModel->find($projectId);

    if (!$project) {
        $_SESSION['error'] = 'Project not found.';
        redirect('admin/projects/index.php');
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Failed to load project.';
    redirect('admin/projects/index.php');
}

/*
|--------------------------------------------------------------------------
| MEDIA CONTROLLER
|--------------------------------------------------------------------------
*/

$mediaController = new MediaController($conn);

/*
|--------------------------------------------------------------------------
| HANDLE UPLOAD
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media'])) {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid security token.';
        redirect('admin/projects/gallery.php?project_id=' . $projectId);
    }

    $files = $_FILES['media'];
    $uploadCount = 0;
    $errorCount = 0;

    // Handle single or multiple files
    if (is_array($files['name'])) {
        $fileCount = count($files['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];

            $result = $mediaController->uploadProjectMedia($projectId, $file);
            if ($result['success']) {
                $uploadCount++;
            } else {
                $errorCount++;
            }
        }
    } else {
        if ($files['error'] !== UPLOAD_ERR_NO_FILE) {
            $result = $mediaController->uploadProjectMedia($projectId, $files);
            if ($result['success']) {
                $uploadCount++;
            } else {
                $errorCount++;
            }
        }
    }

    if ($uploadCount > 0) {
        $_SESSION['success'] = "$uploadCount file(s) uploaded successfully.";
    }
    if ($errorCount > 0) {
        $_SESSION['error'] = "$errorCount file(s) failed to upload.";
    }

    redirect('admin/projects/gallery.php?project_id=' . $projectId);
}

/*
|--------------------------------------------------------------------------
| HANDLE DELETE
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {
    if (!validateCsrf($_GET['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid security token.';
        redirect('admin/projects/gallery.php?project_id=' . $projectId);
    }

    $mediaId = (int) $_GET['delete'];
    if ($mediaController->deleteMedia($mediaId)) {
        $_SESSION['success'] = 'File deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete file.';
    }

    redirect('admin/projects/gallery.php?project_id=' . $projectId);
}

/*
|--------------------------------------------------------------------------
| FETCH MEDIA
|--------------------------------------------------------------------------
*/

$mediaItems = $mediaController->getProjectMedia($projectId);

/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/admin.css'); ?>">
</head>
<body>
<div class="admin-layout">
    <?php include '../../../app/views/layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include '../../../app/views/layouts/navbar.php'; ?>
        <div class="admin-content">

            <!-- PAGE HEADER -->
            <div class="dashboard-header">
                <div>
                    <h1>Project Gallery</h1>
                    <p>Manage images for: <strong><?php echo escape($project['title'] ?? 'Project #' . $projectId); ?></strong></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="../projects/view.php?id=<?php echo escape($projectId); ?>" class="btn btn-dark">
                        <i class="bi bi-arrow-left"></i> Back to Project
                    </a>
                </div>
            </div>

            <!-- ALERTS -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo escape($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo escape($success); ?></div>
            <?php endif; ?>

            <!-- UPLOAD FORM -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-upload"></i> Upload Images</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?php echo csrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label">Select Images (JPG, PNG, WebP, GIF - Max 5MB each)</label>
                            <input type="file" name="media[]" class="form-control" multiple accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-upload"></i> Upload Files
                        </button>
                    </form>
                </div>
            </div>

            <!-- GALLERY GRID -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Gallery</h5>
                    <span class="badge bg-dark"><?php echo count($mediaItems); ?> files</span>
                </div>
                <div class="card-body">
                    <?php if (empty($mediaItems)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-images" style="font-size: 48px;"></i>
                            <p class="mt-3">No images uploaded yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($mediaItems as $item): ?>
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="card h-100">
                                        <img src="<?php echo base_url($item['file_path']); ?>"
                                             class="card-img-top"
                                             alt="<?php echo escape($item['original_name']); ?>"
                                             style="height: 200px; object-fit: cover;">
                                        <div class="card-body p-2">
                                            <small class="text-muted d-block text-truncate">
                                                <?php echo escape($item['original_name']); ?>
                                            </small>
                                            <small class="text-muted d-block">
                                                <?php echo date('d M Y', strtotime($item['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div class="card-footer p-2 bg-transparent border-top-0">
                                            <a href="?project_id=<?php echo escape($projectId); ?>&delete=<?php echo (int)$item['id']; ?>&csrf_token=<?php echo csrfToken(); ?>"
                                               class="btn btn-sm btn-danger w-100"
                                               onclick="return confirm('Delete this image?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url('assets/admin/js/admin.js'); ?>"></script>
</body>
</html>