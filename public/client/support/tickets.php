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

$tickets = $clientService->getSupportTickets($clientId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets</title>
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
        .create-btn{ display:inline-block; text-decoration:none; background:#111827; color:#fff; padding:14px 22px; border-radius:12px; font-weight:bold; }
        .grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:25px; }
        .card{ background:#fff; border-radius:20px; padding:25px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        .card h3{ margin-bottom:8px; }
        .card p{ color:#555; margin-bottom:8px; }
        .badge{ padding:6px 12px; border-radius:30px; font-size:12px; font-weight:bold; display:inline-block; }
        .Open{ background:#d1ecf1; color:#0c5460; }
        .In-Progress{ background:#fff3cd; color:#856404; }
        .Closed{ background:#e2e3e5; color:#383d41; }
        .Resolved{ background:#d4edda; color:#155724; }
        .view-btn{ display:inline-block; text-decoration:none; background:#111827; color:#fff; border:none; padding:10px 18px; border-radius:10px; font-size:13px; cursor:pointer; margin-top:10px; }
        .empty{ text-align:center; padding:80px; color:#777; background:#fff; border-radius:20px; }
        @media(max-width:992px){ .sidebar{ width:100%; height:auto; position:relative; } .main{ margin-left:0; } }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>KVN Client</h2>
    <a href="<?php echo base_url('client/dashboard.php'); ?>">Dashboard</a>
    <a href="<?php echo base_url('client/projects/index.php'); ?>">Projects</a>
    <a href="tickets.php" class="active">Support</a>
    <a href="<?php echo base_url('client/payments/index.php'); ?>">Payments</a>
    <a href="<?php echo base_url('logout.php'); ?>">Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <div>
            <h1>Support Tickets</h1>
            <p>Welcome, <?php echo htmlspecialchars((string)$clientName); ?></p>
        </div>
        <div>
            <a href="create-ticket.php" class="create-btn">+ New Ticket</a>
            <a href="<?php echo base_url('logout.php'); ?>" class="logout-btn" style="margin-left:10px;">Logout</a>
        </div>
    <?php if (count($tickets) > 0): ?>
        <div class="grid">
            <?php foreach ($tickets as $t): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars((string)($t['subject'] ?? $t['title'] ?? '')); ?></h3>
                    <p><strong>Status:</strong> <span class="badge <?php echo htmlspecialchars((string)($t['status'] ?? '')); ?>"><?php echo htmlspecialchars((string)($t['status'] ?? '')); ?></span></p>
                    <p><strong>Priority:</strong> <?php echo htmlspecialchars((string)($t['priority'] ?? 'Normal')); ?></p>
                    <p><strong>Created:</strong> <?php echo htmlspecialchars((string)($t['created_at'] ?? '')); ?></p>
                    <a href="messages.php?id=<?php echo (int)($t['id']); ?>" class="view-btn">View & Reply</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            <h2>No Support Tickets</h2>
            <p>You haven't raised any support tickets yet.</p>
            <a href="create-ticket.php" style="display:inline-block; margin-top:20px; background:#111827; color:#fff; padding:14px 22px; border-radius:12px; text-decoration:none; font-weight:bold;">+ Create Ticket</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
