<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| PROJECT MILESTONES MANAGEMENT
|--------------------------------------------------------------------------
| File: /public/admin/projects/milestones.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';
require_once '../../../helpers/security.php';
require_once '../../../helpers/formatter.php';
require_once '../../../helpers/csrf.php';

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

$pageTitle = 'Project Milestones | ' . APP_NAME;

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
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid security token.';
        redirect('admin/projects/milestones.php?project_id=' . $projectId);
    }

    try {
        switch ($action) {
            case 'add':
                $title = trim(sanitize($_POST['title'] ?? ''));
                $description = trim(sanitize($_POST['description'] ?? ''));
                $dueDate = trim($_POST['due_date'] ?? '');
                $amount = (float) ($_POST['amount'] ?? 0);

                if (empty($title) || empty($dueDate)) {
                    $_SESSION['error'] = 'Title and due date are required.';
                    break;
                }

                $insertQuery = "
                    INSERT INTO project_milestones
                        (project_id, title, description, due_date, amount, status, created_at)
                    VALUES
                        (:project_id, :title, :description, :due_date, :amount, 'pending', NOW())
                ";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->execute([
                    ':project_id' => $projectId,
                    ':title' => $title,
                    ':description' => $description,
                    ':due_date' => $dueDate,
                    ':amount' => $amount
                ]);

                $_SESSION['success'] = 'Milestone added successfully.';
                break;

            case 'update_status':
                $milestoneId = (int) ($_POST['milestone_id'] ?? 0);
                $status = trim(sanitize($_POST['status'] ?? ''));

                $validStatuses = ['pending', 'in_progress', 'completed', 'delayed'];
                if (!in_array($status, $validStatuses, true)) {
                    $_SESSION['error'] = 'Invalid status.';
                    break;
                }

                $updateQuery = "
                    UPDATE project_milestones
                    SET status = :status, updated_at = NOW()
                    WHERE id = :id AND project_id = :project_id
                ";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([
                    ':status' => $status,
                    ':id' => $milestoneId,
                    ':project_id' => $projectId
                ]);

                $_SESSION['success'] = 'Milestone status updated.';
                break;

            case 'delete':
                $milestoneId = (int) ($_POST['milestone_id'] ?? 0);

                $deleteQuery = "
                    DELETE FROM project_milestones
                    WHERE id = :id AND project_id = :project_id
                ";
                $deleteStmt = $conn->prepare($deleteQuery);
                $deleteStmt->execute([
                    ':id' => $milestoneId,
                    ':project_id' => $projectId
                ]);

                $_SESSION['success'] = 'Milestone deleted.';
                break;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Operation failed: ' . $e->getMessage();
    }

    redirect('admin/projects/milestones.php?project_id=' . $projectId);
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
                    <h1>Project Milestones</h1>
                    <p>Manage milestones for: <strong><?php echo escape($project['title'] ?? 'Project #' . $projectId); ?></strong></p>
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

            <div class="row">
                <!-- ADD MILESTONE FORM -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Add Milestone</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="add">

                                <div class="mb-3">
                                    <label class="form-label">Title *</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Due Date *</label>
                                    <input type="date" name="due_date" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Amount (₹)</label>
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0">
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle"></i> Add Milestone
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- MILESTONES LIST -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Milestones</h5>
                            <span class="badge bg-dark"><?php echo count($milestones); ?> total</span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($milestones)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-flag" style="font-size: 48px;"></i>
                                    <p class="mt-3">No milestones yet. Add your first milestone.</p>
                                </div>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($milestones as $milestone): 
                                        $statusBadge = match($milestone['status']) {
                                            'completed' => 'bg-success',
                                            'in_progress' => 'bg-primary',
                                            'delayed' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        $statusIcon = match($milestone['status']) {
                                            'completed' => 'bi-check-circle-fill text-success',
                                            'in_progress' => 'bi-arrow-right-circle-fill text-primary',
                                            'delayed' => 'bi-exclamation-circle-fill text-danger',
                                            default => 'bi-clock text-secondary'
                                        };
                                    ?>
                                        <div class="milestone-item mb-3 p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="<?php echo escape($statusIcon); ?> fs-4"></i>
                                                    <div>
                                                        <h6 class="mb-1"><?php echo escape($milestone['title']); ?></h6>
                                                        <?php if (!empty($milestone['description'])): ?>
                                                            <p class="mb-1 text-muted small"><?php echo escape($milestone['description']); ?></p>
                                                        <?php endif; ?>
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar"></i> Due: <?php echo date('d M Y', strtotime($milestone['due_date'])); ?>
                                                            <?php if ($milestone['amount'] > 0): ?>
                                                                | ₹<?php echo number_format($milestone['amount'], 2); ?>
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <span class="badge <?php echo escape($statusBadge); ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $milestone['status'])); ?>
                                                    </span>
                                                    <form method="POST" class="d-inline">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="milestone_id" value="<?php echo (int)$milestone['id']; ?>">
                                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                            <option value="pending" <?php echo ($milestone['status'] === 'pending' ? 'selected' : ''); ?>>Pending</option>
                                                            <option value="in_progress" <?php echo ($milestone['status'] === 'in_progress' ? 'selected' : ''); ?>>In Progress</option>
                                                            <option value="completed" <?php echo ($milestone['status'] === 'completed' ? 'selected' : ''); ?>>Completed</option>
                                                            <option value="delayed" <?php echo ($milestone['status'] === 'delayed' ? 'selected' : ''); ?>>Delayed</option>
                                                        </select>
                                                    </form>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this milestone?')">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="milestone_id" value="<?php echo (int)$milestone['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url('assets/admin/js/admin.js'); ?>"></script>
</body>
</html>