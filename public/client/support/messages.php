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

$ticketId = (int) ($_GET['id'] ?? 0);
$ticket = $clientService->getSupportTicket($ticketId);

if (!$ticket) {
    header('Location: tickets.php');
    exit();
}

$messages = $clientService->getSupportMessages($ticketId);

/*
|--------------------------------------------------------------------------
| HANDLE REPLY
|--------------------------------------------------------------------------
*/

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');

    if (empty($message)) {
        $errorMessage = 'Please enter a message.';
    } else {
        $data = [
            'ticket_id' => $ticketId,
            'client_id' => $clientId,
            'message' => $message,
            'sender_type' => 'client',
        ];
        if ($clientService->createSupportMessage($data)) {
            $messages = $clientService->getSupportMessages($ticketId);
        } else {
            $errorMessage = 'Failed to send message.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Messages</title>
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
        .back-btn{ display:inline-block; text-decoration:none; color:#333; font-weight:bold; margin-bottom:20px; }
        .card{ background:#fff; border-radius:20px; padding:25px; margin-bottom:25px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        .msg{ padding:18px; border-radius:14px; margin-bottom:15px; max-width:80%; }
        .client-msg{ background:#e8f4fd; margin-left:auto; }
        .admin-msg{ background:#f3f4f6; }
        .msg small{ display:block; color:#888; margin-top:5px; }
        .reply-box{ background:#fff; border-radius:20px; padding:25px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        .reply-box textarea{ width:100%; padding:14px; border:1px solid #ddd; border-radius:12px; font-size:15px; min-height:120px; resize:vertical; }
        .send-btn{ background:#111827; color:#fff; border:none; padding:14px 25px; border-radius:12px; font-weight:bold; font-size:16px; cursor:pointer; margin-top:15px; }
        .error{ background:#f8d7da; color:#721c24; padding:15px; border-radius:10px; margin-bottom:20px; }
        .badge{ padding:6px 12px; border-radius:30px; font-size:12px; font-weight:bold; display:inline-block; }
        @media(max-width:992px){ .sidebar{ width:100%; height:auto; position:relative; } .main{ margin-left:0; } .msg{ max-width:100%; } }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>KVN Client</h2>
    <a href="<?php echo base_url('client/dashboard.php'); ?>">Dashboard</a>
    <a href="tickets.php" class="active">Support</a>
    <a href="<?php echo base_url('client/projects/index.php'); ?>">Projects</a>
    <a href="<?php echo base_url('logout.php'); ?>">Logout</a>
</div>
<div class="main">
    <a href="tickets.php" class="back-btn">← Back to Tickets</a>
    <div class="topbar">
        <div>
            <h1><?php echo htmlspecialchars((string)($ticket['subject'] ?? $ticket['title'] ?? '')); ?></h1>
            <p>Status: <span class="badge"><?php echo htmlspecialchars((string)($ticket['status'] ?? '')); ?></span></p>
        </div>
        <a href="<?php echo base_url('logout.php'); ?>" class="logout-btn">Logout</a>
    </div>
    <?php if (!empty($errorMessage)): ?><div class="error"><?php echo htmlspecialchars($errorMessage); ?></div><?php endif; ?>
    <div class="card">
        <h3>Conversation</h3>
        <?php if (count($messages) > 0): ?>
            <?php foreach ($messages as $m): ?>
                <div class="msg <?php echo ($m['sender_type'] ?? 'client') === 'client' ? 'client-msg' : 'admin-msg'; ?>">
                    <p><?php echo nl2br(htmlspecialchars((string)($m['message'] ?? $m['content'] ?? ''))); ?></p>
                    <small><?php echo htmlspecialchars((string)($m['created_at'] ?? '')); ?> - <?php echo ($m['sender_type'] ?? 'client') === 'client' ? 'You' : 'Support Team'; ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:#888;">No messages yet.</p>
        <?php endif; ?>
    </div>
    <?php if (($ticket['status'] ?? '') !== 'Closed'): ?>
    <div class="reply-box">
        <h3>Reply</h3>
        <form method="POST">
            <textarea name="message" placeholder="Type your message here..." required></textarea>
            <button type="submit" class="send-btn">Send Message</button>
        </form>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
