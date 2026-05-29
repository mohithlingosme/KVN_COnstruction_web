<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CLIENT DASHBOARD
|--------------------------------------------------------------------------
| File:
| /public/client/dashboard.php
|--------------------------------------------------------------------------
*/

require_once '../../config/app.php';
require_once ROOT_PATH . '/middleware/client.php';
require_once ROOT_PATH . '/helpers/csrf.php';
require_once ROOT_PATH . '/helpers/security.php';

/*
|--------------------------------------------------------------------------
| FETCH CLIENT DATA FROM SESSION
|--------------------------------------------------------------------------
*/

$clientId = (int) ($_SESSION['user_id'] ?? 0);
$clientName = $_SESSION['client']['name'] ?? 'Client';

/*
|--------------------------------------------------------------------------
| FETCH PROJECTS (PREPARED STATEMENTS ONLY)
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare('
    SELECT p.*, ps.name as status_name
    FROM projects p
    LEFT JOIN project_statuses ps ON p.status_id = ps.id
    WHERE p.client_id = :client_id
    ORDER BY p.id DESC
');
$stmt->execute([':client_id' => $clientId]);
$projects = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| FETCH PAYMENTS (PREPARED STATEMENTS ONLY)
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare('
    SELECT pp.*, p.project_name
    FROM project_payments pp
    INNER JOIN projects p ON pp.project_id = p.id
    WHERE p.client_id = :client_id
    ORDER BY pp.id DESC
');
$stmt->execute([':client_id' => $clientId]);
$payments = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| STATS CALCULATION
|--------------------------------------------------------------------------
*/

$totalProjects = count($projects);
$completedProjects = count(array_filter($projects, fn($p) => $p['status_name'] === 'Completed'));
$ongoingProjects = count(array_filter($projects, fn($p) => in_array($p['status_name'], ['Planning', 'Foundation', 'Structure', 'Finishing'])));
$totalPaid = array_reduce(
    array_filter($payments, fn($p) => $p['status'] === 'paid'),
    fn($sum, $p) => $sum + (float) $p['amount'],
    0
);

/*
|--------------------------------------------------------------------------
| SEND MESSAGE HANDLER
|--------------------------------------------------------------------------
*/

$success = '';
$error = '';

if (request_method() === 'POST' && isset($_POST['send_message'])) {

    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if ($subject === '' || $message === '') {
            $error = 'Please fill all fields.';
        } else {
            $stmt = $conn->prepare(
                'INSERT INTO client_messages (client_id, subject, message, created_at) VALUES (:client_id, :subject, :message, NOW())'
            );
            $stmt->execute([
                ':client_id' => $clientId,
                ':subject' => $subject,
                ':message' => $message
            ]);

            logSecurityEvent($clientId, 'client_message_sent', 'info', 'Support message sent');

            $success = 'Message sent successfully.';
        }
    }
}

$csrfToken = generateCsrfToken();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard | <?php echo APP_NAME; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f9; color: #222; }
        .sidebar { width: 260px; background: #111827; height: 100vh; position: fixed; left: 0; top: 0; padding: 30px 20px; overflow: auto; }
        .sidebar h2 { color: #f5b400; margin-bottom: 35px; }
        .sidebar a { display: block; color: #fff; text-decoration: none; padding: 14px 16px; margin-bottom: 10px; border-radius: 10px; transition: 0.3s; }
        .sidebar a:hover { background: #f5b400; }
        .main { margin-left: 260px; padding: 40px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .welcome h1 { margin-bottom: 8px; }
        .logout { background: #dc3545; color: #fff; padding: 12px 18px; border-radius: 10px; text-decoration: none; font-weight: bold; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .card { background: #fff; padding: 25px; border-radius: 18px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .card h3 { color: #666; margin-bottom: 10px; }
        .card h2 { color: #111; }
        .section { background: #fff; padding: 30px; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 35px; }
        .section h2 { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f5b400; color: #fff; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        tr:hover { background: #fafafa; }
        .badge { padding: 8px 14px; border-radius: 30px; font-size: 12px; font-weight: bold; display: inline-block; }
        .Planning { background: #d1ecf1; color: #0c5460; }
        .In\.Progress { background: #fff3cd; color: #856404; }
        .Completed { background: #d4edda; color: #155724; }
        .On\.Hold { background: #f8d7da; color: #721c24; }
        .Paid { background: #d4edda; color: #155724; }
        .Pending { background: #fff3cd; color: #856404; }
        .Failed { background: #f8d7da; color: #721c24; }
        .progress { width: 100%; height: 18px; background: #eee; border-radius: 30px; overflow: hidden; }
        .progress-fill { height: 100%; background: #28a745; color: #fff; text-align: center; font-size: 11px; line-height: 18px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input, textarea { width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 10px; font-size: 15px; }
        textarea { min-height: 120px; resize: vertical; }
        button { background: #f5b400; color: #fff; border: none; padding: 14px 20px; border-radius: 10px; font-size: 15px; font-weight: bold; cursor: pointer; }
        button:hover { opacity: 0.9; }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        @media(max-width: 992px) { .sidebar { width: 100%; height: auto; position: relative; } .main { margin-left: 0; } table { display: block; overflow-x: auto; } }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>KVN Client</h2>
        <a href="#">Dashboard</a>
        <a href="#projects">My Projects</a>
        <a href="#payments">Payments</a>
        <a href="#support">Support</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- MAIN -->
    <div class="main">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="welcome">
                <h1>Welcome, <?php echo escape($clientName); ?></h1>
                <p>Track your projects, payments and updates.</p>
            </div>
            <a href="logout.php" class="logout">Logout</a>
        </div>

        <!-- STATS -->
        <div class="cards">
            <div class="card">
                <h3>Total Projects</h3>
                <h2><?php echo $totalProjects; ?></h2>
            </div>
            <div class="card">
                <h3>Ongoing Projects</h3>
                <h2><?php echo $ongoingProjects; ?></h2>
            </div>
            <div class="card">
                <h3>Completed Projects</h3>
                <h2><?php echo $completedProjects; ?></h2>
            </div>
            <div class="card">
                <h3>Total Paid</h3>
                <h2>₹<?php echo number_format($totalPaid, 2); ?></h2>
            </div>
        </div>

        <!-- PROJECTS -->
        <div class="section" id="projects">
            <h2>My Projects</h2>
            <table>
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Location</th>
                        <th>Plot Size</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>Expected End</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($projects) > 0): ?>
                        <?php foreach ($projects as $row): ?>
                            <tr>
                                <td><?php echo escape($row['project_name']); ?></td>
                                <td><?php echo escape($row['location']); ?></td>
                                <td><?php echo escape($row['plot_size']); ?> sq.ft</td>
                                <td>
                                    <span class="badge <?php echo escape(str_replace(' ', '.', $row['status_name'])); ?>">
                                        <?php echo escape($row['status_name']); ?>
                                    </span>
                                </td>
                                <td><?php echo escape($row['start_date']); ?></td>
                                <td><?php echo escape($row['expected_end_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No projects found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAYMENTS -->
        <div class="section" id="payments">
            <h2>Payment History</h2>
            <table>
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Amount</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($payments) > 0): ?>
                        <?php foreach ($payments as $pay): ?>
                            <tr>
                                <td><?php echo escape($pay['project_name']); ?></td>
                                <td>₹<?php echo number_format((float) $pay['amount'], 2); ?></td>
                                <td><?php echo escape($pay['payment_mode']); ?></td>
                                <td>
                                    <span class="badge <?php echo escape($pay['status']); ?>">
                                        <?php echo escape($pay['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo escape($pay['paid_at'] ?? 'Pending'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No payments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- SUPPORT -->
        <div class="section" id="support">
            <h2>Contact Support</h2>
            <?php if ($success !== ''): ?>
                <div class="alert success"><?php echo escape($success); ?></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo escape($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo escape($csrfToken); ?>">
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" required></textarea>
                </div>
                <button type="submit" name="send_message">Send Message</button>
            </form>
        </div>
    </div>
</body>
</html>