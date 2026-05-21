<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/includes/middleware/AuthMiddleware.php';
require_once __DIR__ . '/includes/db.php';

AuthMiddleware::requireAuth();

$error = '';

try {
    $result = $conn->query("SELECT * FROM appointments ORDER BY id DESC LIMIT 100");
    $appointments = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
} catch (Throwable $e) {
    $error = 'An error occurred: ' . $e->getMessage();
    error_log('Appointments error: ' . $e->getMessage());
    $appointments = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments | KVN Construction</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <h2>KVN Admin</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="leads.php">Leads</a></li>
            <li><a href="projects.php">Projects</a></li>
            <li><a href="clients.php">Clients</a></li>
            <li><a href="quotations.php">Quotations</a></li>
            <li><a href="appointments.php">Appointments</a></li>
            <li><a href="admin-packages.php">Packages</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>Appointments</h1>

        <?php if ($error): ?>
            <div style="background: #ffe5e5; color: #d10000; padding: 12px; border-radius: 10px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($appointments as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['client_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['appointment_date'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['status'] ?? 'N/A'); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </main>
</div>
</body>
</html>
