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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Gallery</title>
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
        .gallery-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:20px; }
        .gallery-item{ background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        .gallery-item img{ width:100%; height:220px; object-fit:cover; }
        .gallery-item .caption{ padding:15px; }
        .back-btn{ display:inline-block; text-decoration:none; color:#333; font-weight:bold; margin-bottom:20px; }
        .empty{ text-align:center; padding:80px; color:#777; background:#fff; border-radius:20px; }
        @media(max-width:992px){ .sidebar{ width:100%; height:auto; position:relative; } .main{ margin-left:0; } }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>KVN Client</h2>
    <a href="<?php echo base_url('client/dashboard.php'); ?>">Dashboard</a>
    <a href="index.php">Projects</a>
    <a href="<?php echo base_url('client/payments/index.php'); ?>">Payments</a>
    <a href="<?php echo base_url('logout.php'); ?>">Logout</a>
</div>
<div class="main">
    <a href="view.php?id=<?php echo (int)$projectId; ?>" class="back-btn">← Back to Project</a>
    <div class="topbar">
        <h1>Gallery - <?php echo htmlspecialchars((string)($project['title'] ?? '')); ?></h1>
        <a href="<?php echo base_url('logout.php'); ?>" class="logout-btn">Logout</a>
    </div>
    <?php if (count($gallery) > 0): ?>
        <div class="gallery-grid">
            <?php foreach ($gallery as $img): ?>
                <div class="gallery-item">
                    <img src="../../uploads/<?php echo htmlspecialchars((string)($img['image_url'] ?? $img['file_path'] ?? '')); ?>" alt="Gallery">
                    <div class="caption"><?php echo htmlspecialchars((string)($img['caption'] ?? $img['title'] ?? '')); ?></div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty"><h2>No Gallery Images</h2><p>No images have been added to this project yet.</p></div>
    <?php endif; ?>
</div>
</body>
</html>
