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

$timelines = $clientService->getTimelines($clientId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Timeline</title>
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
        .timeline::before{ content:''; position:absolute; left:15px; top:0; bottom:0; width:3px; background:#f5b400; }
        .timeline-item{ position:relative; margin-bottom:35px; background:#fff; border-radius:16px; padding:20px 25px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        .timeline-item::before{ content:''; position:absolute; left:-33px; top:25px; width:14px; height:14px; border-radius:50%; background:#f5b400; border:3px solid #fff; box-shadow:0 0 0 3px #f5b400; }
        .timeline-item h3{ margin-bottom:5px; }
        .timeline-item small{ color:#888; }
        .timeline-item p{ margin-top:10px; color:#555; }
        .badge{ padding:4px 10px; border-radius:20px; font-size:11px; font-weight:bold; display:inline-block; }
        .Completed{ background:#d4edda; color:#155724; }
        .In-Progress{ background:#fff3cd; color:#856404; }
        .Pending{ background:#e2e3e5; color:#383d41; }
        .empty{ text-align:center; padding:80px; color:#777; background:#fff; border-radius:20px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        @media(max-width:992px){ .sidebar{ width:100%; height:auto; position:relative; } .main{ margin-left:0; } }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>KVN Client</h2>
    <a href="<?php echo base_url('client/dashboard.php'); ?>">Dashboard</a>
    <a href="index.php" class="active">Timeline</a>
    <a href="schedules.php">Schedule</a>
    <a href="<?php echo base_url('logout.php'); ?>">Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <div>
            <h1>Project Timeline</h1>
            <p>Welcome, <?php echo htmlspecialchars((string)$clientName); ?></p>
        </div>
        <a href="<?php echo base_url('logout.php'); ?>" class="logout-btn">Logout</a>
    </div>
    <?php if (count($timelines) > 0): ?>
        <div class="timeline">
            <?php foreach ($timelines as $t): ?>
                <div class="timeline-item">
                    <h3><?php echo htmlspecialchars((string)($t['title'] ?? $t['event'] ?? '')); ?></h3>
                    <small><?php echo htmlspecialchars((string)($t['date'] ?? $t['created_at'] ?? '')); ?></small>
                    <p><?php echo nl2br(htmlspecialchars((string)($t['description'] ?? $t['content'] ?? ''))); ?></p>
                    <?php if (!empty($t['status'])): ?>
                        <span class="badge <?php echo htmlspecialchars((string)$t['status']); ?>"><?php echo htmlspecialchars((string)$t['status']); ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty"><h2>No Timeline Events</h2><p>There are no timeline events for your projects yet.</p></div>
    <?php endif; ?>
</div>
</body>
</html>
