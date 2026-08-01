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

$milestones = $clientService->getProjectMilestones($projectId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Milestones</title>
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
        .timeline{ position:relative; padding-left:40px; }
        .timeline::before{ content:''; position:absolute; left:15px; top:0; bottom:0; width:4px; background:#e5e7eb; border-radius:4px; }
        .milestone{ position:relative; margin-bottom:30px; background:#fff; border-radius:16px; padding:20px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        .milestone::before{ content:''; position:absolute; left:-32px; top:24px; width:20px; height:20px; border-radius:50%; background:#f5b400; border:4px solid #fff; }
        .milestone.completed::before{ background:#10b981; }
        .milestone h3{ margin-bottom:8px; }
        .badge{ padding:6px 12px; border-radius:30px; font-size:12px; font-weight:bold; display:inline-block; }
        .Completed{ background:#d4edda; color:#155724; }
        .In-Progress{ background:#d1ecf1; color:#0c5460; }
        .Pending{ background:#fff3cd; color:#856404; }
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
        <h1>Milestones - <?php echo htmlspecialchars((string)($project['title'] ?? '')); ?></h1>
        <a href="<?php echo base_url('logout.php'); ?>" class="logout-btn">Logout</a>
    </div>
    <?php if (count($milestones) > 0): ?>
        <div class="timeline">
            <?php foreach ($milestones as $m): ?>
                <div class="milestone <?php echo ($m['status'] ?? '') === 'Completed' ? 'completed' : ''; ?>">
                    <h3><?php echo htmlspecialchars((string)($m['title'] ?? '')); ?></h3>
                    <p><span class="badge <?php echo htmlspecialchars((string)($m['status'] ?? '')); ?>"><?php echo htmlspecialchars((string)($m['status'] ?? '')); ?></span></p>
                    <p><?php echo nl2br(htmlspecialchars((string)($m['description'] ?? ''))); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty"><h2>No Milestones</h2><p>No milestones defined for this project yet.</p></div>
    <?php endif; ?>
</div>
</body>
</html>
