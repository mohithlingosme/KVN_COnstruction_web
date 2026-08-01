<?php

declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['client_id'])) {
    header('Location: ../login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| CLIENT SERVICE
|--------------------------------------------------------------------------
*/

require_once '../../includes/repositories.php';

$clientService = new \App\Services\ClientService();

$clientId = (int) $_SESSION['client_id'];
$clientName = $_SESSION['client_name'] ?? 'Client';

/*
|--------------------------------------------------------------------------
| FETCH NOTIFICATIONS
|--------------------------------------------------------------------------
*/

$notifications = $clientService->getNotifications($clientId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
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
        .card{ background:#fff; border-radius:20px; padding:25px; margin-bottom:20px; box-shadow:0 5px 20px rgba(0,0,0,0.08); display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap; }
        .card-content{ flex:1; }
        .card-content h3{ margin-bottom:8px; color:#111827; }
        .card-content p{ color:#555; }
        .badge{ padding:8px 14px; border-radius:30px; font-size:12px; font-weight:bold; }
        .Project{ background:#d1ecf1; color:#0c5460; }
        .Payment{ background:#d4edda; color:#155724; }
        .Support{ background:#fff3cd; color:#856404; }
        .General{ background:#e2e3e5; color:#383d41; }
        .Yes{ background:#d4edda; color:#155724; }
        .No{ background:#f8d7da; color:#721c24; }
        .empty{ text-align:center; padding:60px; color:#777; background:#fff; border-radius:20px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        @media(max-width:992px){ .sidebar{ width:100%; height:auto; position:relative; } .main{ margin-left:0; } }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>KVN Client</h2>
    <a href="<?php echo base_url('client/dashboard.php'); ?>">Dashboard</a>
    <a href="index.php">My Profile</a>
    <a href="notifications.php" class="active">Notifications</a>
    <a href="<?php echo base_url('client/projects/index.php'); ?>">Projects</a>
    <a href="<?php echo base_url('client/support/tickets.php'); ?>">Support</a>
    <a href="<?php echo base_url('logout.php'); ?>">Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <div>
            <h1>Notifications</h1>
            <p>Welcome, <?php echo htmlspecialchars((string)$clientName); ?></p>
        </div>
        <a href="<?php echo base_url('logout.php'); ?>" class="logout-btn">Logout</a>
    </div>
    <?php if (count($notifications) > 0): ?>
        <?php foreach ($notifications as $row): ?>
            <div class="card">
                <div class="card-content">
                    <h3><?php echo htmlspecialchars((string)$row['title']); ?></h3>
                    <p><?php echo htmlspecialchars((string)$row['message']); ?></p>
                    <p><strong>Type:</strong> <span class="badge <?php echo htmlspecialchars((string)$row['type']); ?>"><?php echo htmlspecialchars((string)$row['type']); ?></span></p>
                    <p><strong>Read:</strong> <span class="badge <?php echo htmlspecialchars(($row['is_read'] ?? 'No') === 'Yes' ? 'Yes' : 'No'); ?>"><?php echo htmlspecialchars($row['is_read'] ?? 'No'); ?></span></p>
                </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty"><h2>No Notifications</h2><p>You have no notifications yet.</p></div>
    <?php endif; ?>
</div>
</body>
</html>
