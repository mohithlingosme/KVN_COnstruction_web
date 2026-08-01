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

/*
|--------------------------------------------------------------------------
| HANDLE CREATE TICKET
|--------------------------------------------------------------------------
*/

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $priority = trim($_POST['priority'] ?? 'Normal');

    if (empty($subject) || empty($message)) {
        $errorMessage = 'Subject and message are required.';
    } else {
        $data = [
            'client_id' => $clientId,
            'subject' => $subject,
            'message' => $message,
            'priority' => $priority,
            'status' => 'Open',
        ];

        $ticketId = $clientService->createSupportTicket($data);
        if ($ticketId > 0) {
            $successMessage = 'Ticket created successfully.';
        } else {
            $errorMessage = 'Failed to create ticket.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Support Ticket</title>
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
        .card{ background:#fff; border-radius:20px; padding:35px; box-shadow:0 5px 20px rgba(0,0,0,0.08); max-width:700px; }
        .form-group{ margin-bottom:25px; }
        .form-group label{ display:block; margin-bottom:8px; font-weight:bold; }
        .form-group input, .form-group textarea, .form-group select{ width:100%; padding:14px; border:1px solid #ddd; border-radius:12px; font-size:15px; }
        .form-group textarea{ min-height:150px; resize:vertical; }
        .submit-btn{ background:#111827; color:#fff; border:none; padding:15px 25px; border-radius:12px; font-size:16px; font-weight:bold; cursor:pointer; }
        .success{ background:#d4edda; color:#155724; padding:15px; border-radius:10px; margin-bottom:20px; }
        .error{ background:#f8d7da; color:#721c24; padding:15px; border-radius:10px; margin-bottom:20px; }
        @media(max-width:992px){ .sidebar{ width:100%; height:auto; position:relative; } .main{ margin-left:0; } }
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
        <h1>Create Support Ticket</h1>
        <a href="<?php echo base_url('logout.php'); ?>" class="logout-btn">Logout</a>
    </div>
    <?php if (!empty($successMessage)): ?><div class="success"><?php echo htmlspecialchars($successMessage); ?></div><?php endif; ?>
    <?php if (!empty($errorMessage)): ?><div class="error"><?php echo htmlspecialchars($errorMessage); ?></div><?php endif; ?>
    <div class="card">
        <form method="POST">
            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject" required placeholder="Brief title of your issue">
            </div>
            <div class="form-group">
                <label>Priority</label>
                <select name="priority">
                    <option value="Low">Low</option>
                    <option value="Normal" selected>Normal</option>
                    <option value="High">High</option>
                    <option value="Urgent">Urgent</option>
                </select>
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" required placeholder="Describe your issue in detail..."></textarea>
            </div>
            <button type="submit" class="submit-btn">Submit Ticket</button>
        </form>
    </div>
</body>
</html>
