<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['client_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../../includes/repositories.php';

$clientService = new \App\Services\ClientService();
$clientId = (int) $_SESSION['client_id'];
$clientName = $_SESSION['client_name'] ?? 'Client';

$projectId = (int) ($_GET['id'] ?? 0);
$project = $clientService->getProjectById($projectId);

if (!$project) {
    header('Location: index.php');
    exit();
}

$gallery = $clientService->getProjectGallery($projectId);
$milestones = $clientService->getProjectMilestones($projectId);
$updates = $clientService->getProjectUpdates($projectId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars((string)($project['title'] ?? '')); ?></title>
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:Arial,sans-serif; background:#f3f4f6; color:#222; }
        .sidebar{ width:260px; height:100vh; background:#111827; position:fixed; top:0; left:0; padding:30px 20px; overflow:auto; }
        .sidebar h2{ color:#f5b400; margin-bottom:35px; }
        .sidebar a{ display:block; text-decoration:none; color:#fff; padding:14px 16px; border-radius:10px; margin-bottom:10px; transition:0.3s; }
        .sidebar a:hover, .sidebar .active{ background:#f5b400; color:#111; }
        .main{ margin-left:260px; padding:40px; }
        .topbar{ display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:35px; }
        .logout-btn{ text-decoration:none; background:#dc3545; color:#fff; padding:12px 18px; border-radius:10px; font-weight:bold; }
        .project-header{ background:#fff; border-radius:20px; padding:30px; margin-bottom:25px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        .project-header h1{ margin-bottom:15px; }
        .info-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; margin-bottom:20px; }
        .info-item strong{ display:block; color:#555; font-size:13px; }
        .info-item span{ font-size:16px; font-weight:bold; }
        .section{ background:#fff; border-radius:20px; padding:25px; margin-bottom:25px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        .section h2{ margin-bottom:20px; }
        .gallery-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:15px; }
        .gallery-grid img{ width:100%; height:180px; object-fit:cover; border-radius:12px; }
        .milestone-card{ padding:15px; border:1px solid #eee; border-radius:12px; margin-bottom:15px; }
        .update-card{ padding:15px; border-left:4px solid #f5b400; margin-bottom:15px; }
        .badge{ padding:6px 12px; border-radius:30px; font-size:12px; font-weight:bold; display:inline-block; }
        .In-Progress{ background:#d1ecf1; color:#0c5460; }
        .Completed{ background:#d4edda; color:#155724; }
        .Pending{ background:#fff3cd; color:#856404; }
        .back-btn{ display:inline-block; text-decoration:none; color:#333; font-weight:bold; margin-bottom:20px; }
        @media(max-width:992px){ .sidebar{ width:100%; height:auto; position:relative; } .main{ margin-left:0; } }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>KVN Client</h2>
    <a href="<?php echo base_url('client/dashboard.php'); ?>">Dashboard</a>
    <a href="index.php" class="active">Projects</a>
    <a href="<?php echo base_url('client/payments/index.php'); ?>">Payments</a>
    <a href="<?php echo base_url('client/support/tickets.php'); ?>">Support</a>
    <a href="<?php echo base_url('logout.php'); ?>">Logout</a>
</div>
<div class="main">
    <a href="index.php" class="back-btn">← Back to Projects</a>
    <div class="project-header">
        <h1><?php echo htmlspecialchars((string)($project['title'] ?? '')); ?></h1>
        <div class="info-grid">

            <div class="info-item"><strong>Status</strong><span class="badge <?php echo htmlspecialchars((string)($project['status'] ?? '')); ?>"><?php echo htmlspecialchars((string)($project['status'] ?? '')); ?></span></div>

            <div class="info-item"><strong>Start Date</strong><span><?php echo htmlspecialchars((string)($project['start_date'] ?? 'N/A')); ?></span></div>

            <div class="info-item"><strong>End Date</strong><span><?php echo htmlspecialchars((string)($project['end_date'] ?? 'TBD')); ?></span></div>

        </div>

        <p><?php echo nl2br(htmlspecialchars((string)($project['description'] ?? ''))); ?></p>

    </div>
    <?php if (count($gallery) > 0): ?>
    <div class="section">
        <h2>Gallery</h2>
        <div class="gallery-grid">
            <?php foreach ($gallery as $img): ?>
                <img src="../../uploads/<?php echo htmlspecialchars((string)($img['image_url'] ?? $img['file_path'] ?? '')); ?>" alt="Gallery Image">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (count($milestones) > 0): ?>
    <div class="section">
        <h2>Milestones</h2>
        <?php foreach ($milestones as $m): ?>
            <div class="milestone-card">
                <strong><?php echo htmlspecialchars((string)($m['title'] ?? '')); ?></strong>
                <p><span class="badge <?php echo htmlspecialchars((string)($m['status'] ?? '')); ?>"><?php echo htmlspecialchars((string)($m['status'] ?? '')); ?></span></p>
                <p><?php echo nl2br(htmlspecialchars((string)($m['description'] ?? ''))); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if (count($updates) > 0): ?>
    <div class="section">
        <h2>Updates</h2>
        <?php foreach ($updates as $u): ?>
            <div class="update-card">
                <strong><?php echo htmlspecialchars((string)($u['title'] ?? '')); ?></strong>
                <small><?php echo htmlspecialchars((string)($u['created_at'] ?? '')); ?></small>
                <p><?php echo nl2br(htmlspecialchars((string)($u['message'] ?? $u['content'] ?? ''))); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
