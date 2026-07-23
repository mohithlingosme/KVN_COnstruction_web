<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CLIENT PROJECT TIMELINE & PROGRESS
|--------------------------------------------------------------------------
| File: /public/client/timeline/progress.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once '../../../middleware/client.php';
require_once '../../../helpers/security.php';
require_once '../../../helpers/formatter.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle = 'Project Progress | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| CLIENT INFO
|--------------------------------------------------------------------------
*/

$clientId = (int) ($_SESSION['user_id'] ?? 0);
$clientName = $_SESSION['user_name'] ?? 'Client';

/*
|--------------------------------------------------------------------------
| PROJECT ID
|--------------------------------------------------------------------------
*/

$projectId = (int) ($_GET['project_id'] ?? 0);

if ($projectId <= 0) {
    $_SESSION['error'] = 'Invalid project ID.';
    redirect('client/dashboard.php');
}

/*
|--------------------------------------------------------------------------
| FETCH PROJECT
|--------------------------------------------------------------------------
*/

$project = null;
try {
    $query = "
        SELECT p.*, 
               (SELECT COUNT(*) FROM project_milestones WHERE project_id = p.id AND status = 'completed') as completed_milestones,
               (SELECT COUNT(*) FROM project_milestones WHERE project_id = p.id) as total_milestones
        FROM projects p
        WHERE p.id = :project_id AND p.client_id = :client_id
        LIMIT 1
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute([':project_id' => $projectId, ':client_id' => $clientId]);
    $project = $stmt->fetch();

    if (!$project) {
        $_SESSION['error'] = 'Project not found or access denied.';
        redirect('client/dashboard.php');
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Failed to load project.';
    redirect('client/dashboard.php');
}

/*
|--------------------------------------------------------------------------
| FETCH MILESTONES
|--------------------------------------------------------------------------
*/

$milestones = [];
try {
    $query = "
        SELECT *
        FROM project_milestones
        WHERE project_id = :project_id
        ORDER BY due_date ASC, id ASC
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute([':project_id' => $projectId]);
    $milestones = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Milestone fetch error: ' . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| CALCULATE PROGRESS
|--------------------------------------------------------------------------
*/

$totalMilestones = count($milestones);
$completedMilestones = 0;
$totalProgress = 0;

foreach ($milestones as $ms) {
    if ($ms['status'] === 'completed') {
        $completedMilestones++;
    }
}

if ($totalMilestones > 0) {
    $totalProgress = round(($completedMilestones / $totalMilestones) * 100);
}

// Also check if project has a manual progress percentage
$projectProgress = !empty($project['progress_percentage']) ? (int) $project['progress_percentage'] : $totalProgress;

/*
|--------------------------------------------------------------------------
| FETCH RECENT UPDATES
|--------------------------------------------------------------------------
*/

$updates = [];
try {
    $query = "
        SELECT * FROM project_updates
        WHERE project_id = :project_id
        ORDER BY created_at DESC
        LIMIT 20
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute([':project_id' => $projectId]);
    $updates = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Updates fetch error: ' . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| FETCH MEDIA
|--------------------------------------------------------------------------
*/

$mediaItems = [];
try {
    $query = "
        SELECT * FROM project_media
        WHERE project_id = :project_id
        ORDER BY created_at DESC
        LIMIT 12
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute([':project_id' => $projectId]);
    $mediaItems = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Media fetch error: ' . $e->getMessage());
}

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
    <link rel="stylesheet" href="<?php echo base_url('assets/client/css/client.css'); ?>">
    <style>
        .progress-ring { width: 180px; height: 180px; margin: 0 auto; position: relative; }
        .progress-ring .percent { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 36px; font-weight: 700; }
        .timeline-item { position: relative; padding-left: 40px; padding-bottom: 30px; }
        .timeline-item::before { content: ''; position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background: #e5e7eb; }
        .timeline-item:last-child::before { display: none; }
        .timeline-dot { position: absolute; left: 8px; top: 4px; width: 16px; height: 16px; border-radius: 50%; border: 3px solid; }
        .timeline-dot.completed { background: #10b981; border-color: #10b981; }
        .timeline-dot.in-progress { background: #3b82f6; border-color: #3b82f6; animation: pulse 2s infinite; }
        .timeline-dot.pending { background: #fff; border-color: #d1d5db; }
        .timeline-dot.delayed { background: #ef4444; border-color: #ef4444; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(59,130,246,0.4); } 70% { box-shadow: 0 0 0 10px rgba(59,130,246,0); } 100% { box-shadow: 0 0 0 0 rgba(59,130,246,0); } }
        .gallery-img { height: 150px; object-fit: cover; border-radius: 8px; cursor: pointer; transition: 0.3s; }
        .gallery-img:hover { transform: scale(1.05); }
    </style>
</head>
<body>
<div class="client-layout">
    <?php include '../../../app/views/layouts/client-sidebar.php'; ?>
    <div class="client-main">
        <?php include '../../../app/views/layouts/client-navbar.php'; ?>
        <div class="client-content">

            <!-- PAGE HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1"><?php echo escape($project['title'] ?? 'Project Progress'); ?></h1>
                    <p class="text-muted mb-0">Track your project milestones and progress</p>
                </div>
                <a href="../dashboard.php" class="btn btn-outline-dark">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo escape($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo escape($success); ?></div>
            <?php endif; ?>

            <!-- PROJECT OVERVIEW -->
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="progress-ring">
                                <svg width="180" height="180" viewBox="0 0 180 180">
                                    <circle cx="90" cy="90" r="80" fill="none" stroke="#e5e7eb" stroke-width="12"/>
                                    <circle cx="90" cy="90" r="80" fill="none" stroke="#f5b400" stroke-width="12"
                                            stroke-dasharray="<?php echo 2 * pi() * 80; ?>"
                                            stroke-dashoffset="<?php echo 2 * pi() * 80 * (1 - $projectProgress / 100); ?>"
                                            stroke-linecap="round" transform="rotate(-90 90 90)"/>
                                </svg>
                                <div class="percent"><?php echo escape($projectProgress); ?>%</div>
                            </div>
                            <h5 class="mt-3">Overall Progress</h5>
                            <p class="text-muted small">
                                <?php echo escape($completedMilestones); ?>/<?php echo escape($totalMilestones); ?> milestones completed
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Project Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <td class="text-muted">Project ID</td>
                                            <td><strong>#<?php echo (int)$project['id']; ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Status</td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($project['status'] ?? '') {
                                                        'completed' => 'success',
                                                        'in_progress' => 'primary',
                                                        'on_hold' => 'warning',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $project['status'] ?? 'pending')); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Start Date</td>
                                            <td><?php echo !empty($project['start_date']) ? date('d M Y', strtotime($project['start_date'])) : 'N/A'; ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <td class="text-muted">Expected Completion</td>
                                            <td><?php echo !empty($project['expected_completion']) ? date('d M Y', strtotime($project['expected_completion'])) : 'N/A'; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Budget</td>
                                            <td><strong>₹<?php echo number_format($project['budget'] ?? 0); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Location</td>
                                            <td><?php echo escape($project['location'] ?? 'N/A'); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php if (!empty($project['description'])): ?>
                                <p class="mt-2"><?php echo nl2br(escape($project['description'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MILESTONES TIMELINE -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-flag"></i> Project Milestones</h5>
                    <span class="badge bg-dark"><?php echo escape($totalMilestones); ?> milestones</span>
                </div>
                <div class="card-body">
                    <?php if (empty($milestones)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-flag" style="font-size: 48px;"></i>
                            <p class="mt-3">No milestones defined yet. Check back later for updates.</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($milestones as $milestone): 
                                $dotClass = match($milestone['status']) {
                                    'completed' => 'completed',
                                    'in_progress' => 'in-progress',
                                    'delayed' => 'delayed',
                                    default => 'pending'
                                };
                                $statusBadge = match($milestone['status']) {
                                    'completed' => 'bg-success',
                                    'in_progress' => 'bg-primary',
                                    'delayed' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot <?php echo escape($dotClass); ?>"></div>
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo escape($milestone['title']); ?></h6>
                                            <?php if (!empty($milestone['description'])): ?>
                                                <p class="mb-1 text-muted small"><?php echo escape($milestone['description']); ?></p>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar"></i> 
                                                <?php echo date('d M Y', strtotime($milestone['due_date'])); ?>
                                                <?php if ($milestone['amount'] > 0): ?>
                                                    | ₹<?php echo number_format($milestone['amount']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <span class="badge <?php echo escape($statusBadge); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $milestone['status'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RECENT UPDATES -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-megaphone"></i> Recent Updates</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($updates)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-chat-dots" style="font-size: 36px;"></i>
                            <p class="mt-2">No updates yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($updates as $update): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo escape($update['title'] ?? 'Update'); ?></strong>
                                    <small class="text-muted"><?php echo date('d M Y h:i A', strtotime($update['created_at'])); ?></small>
                                </div>
                                <p class="mb-0 mt-1"><?php echo nl2br(escape($update['message'] ?? '')); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- GALLERY -->
            <?php if (!empty($mediaItems)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-images"></i> Project Gallery</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($mediaItems as $media): ?>
                                <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                                    <a href="<?php echo base_url($media['file_path']); ?>" target="_blank">
                                        <img src="<?php echo base_url($media['file_path']); ?>" 
                                             alt="<?php echo escape($media['original_name']); ?>"
                                             class="gallery-img w-100">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url('assets/client/js/client.js'); ?>"></script>
</body>
</html>