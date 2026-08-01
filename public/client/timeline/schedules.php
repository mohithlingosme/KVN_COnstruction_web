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

$schedules = $clientService->getSchedules($clientId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Schedules</title>
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
        .grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:25px; }
        .card{ background:#fff; border-radius:20px; padding:25px; box-shadow:0 5px 20px rgba(0,0,0,0.08); border-top:5px solid #f5b400; }
        .card h3{ margin-bottom:8px; }
        .card p{ color:#555; margin-bottom:8px; }
        .badge{ padding:6px 12px; border-radius:30px; font-size:12px; font-weight:bold; display:inline-block; }
        .Scheduled{ background:#d1ecf1; color:#0c5460; }
        .In-Progress{ background:#fff3cd; color:#856404; }
        .Completed{ background:#d4edda; color:#155724; }
        .Delayed{ background:#f8d7da; color:#721c24; }
        .empty{ text-align:center; padding:80px; color:#777; background:#fff; border-radius:20px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        .back-btn{ display:inline-block; text-decoration:none; color:#333; font-weight:bold; margin-bottom:20px; }
        @media(max-width:992px){ .sidebar{ width:100%; height:auto; position:relative; } .main{ margin-left:0; } }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>KVN Client</h2>
    <a href="<?php echo base_url('client/dashboard.php'); ?>">Dashboard</a>
    <a href="index.php">Timeline</a>
    <a href="schedules.php" class="active">Schedule</a>
    <a href="<?php echo base_url('logout.php'); ?>">Logout</a>
</div>
<div class="main">
    <a href="index.php" class="back-btn">← Back to Timeline</a>
    <div class="topbar">
        <h1>Project Schedules</h1>
        <a href="<?php echo base_url('logout.php'); ?>" class="logout-btn">Logout</a>
    </div>
    <?php if (count($schedules) > 0): ?>
        <div class="grid">
            <?php foreach ($schedules as $s): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars((string)($s['title'] ?? $s['event_name'] ?? '')); ?></h3>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars((string)($s['date'] ?? $s['scheduled_date'] ?? '')); ?></p>
                    <p><strong>Status:</strong> <span class="badge <?php echo htmlspecialchars((string)($s['status'] ?? '')); ?>"><?php echo htmlspecialchars((string)($s['status'] ?? '')); ?></span></p>
                    <p><?php echo nl2br(htmlspecialchars((string)($s['description'] ?? $s['notes'] ?? ''))); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty"><h2>No Schedules</h2><p>No schedules have been set for your projects yet.</p></div>
    <?php endif; ?>
</div>
</body>
</html>
