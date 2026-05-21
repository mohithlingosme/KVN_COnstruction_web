<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/includes/middleware/AuthMiddleware.php';
require_once __DIR__ . '/includes/db.php';

// Require authentication
AuthMiddleware::requireAuth();

$error = '';
$success = '';

/* ADD LEAD */

if (isset($_POST['add_lead'])) {
    try {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $project_type = trim($_POST['project_type'] ?? '');
        $budget = trim($_POST['budget'] ?? '');
        $status = trim($_POST['status'] ?? 'New Lead');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($name) || empty($phone)) {
            $error = 'Name and Phone are required.';
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO leads (name, phone, email, project_type, budget, status, notes) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            if (!$stmt) {
                $error = 'Database error: ' . $conn->error;
                error_log('Prepare failed: ' . $conn->error);
            } else {
                $stmt->bind_param(
                    'sssssss',
                    $name,
                    $phone,
                    $email,
                    $project_type,
                    $budget,
                    $status,
                    $notes
                );

                if ($stmt->execute()) {
                    $success = 'Lead added successfully.';
                    $stmt->close();
                    header("Refresh:2; url=leads.php");
                } else {
                    $error = 'Failed to add lead: ' . $stmt->error;
                    error_log('Execute failed: ' . $stmt->error);
                    $stmt->close();
                }
            }
        }
    } catch (Throwable $e) {
        $error = 'An error occurred: ' . $e->getMessage();
        error_log('Lead add error: ' . $e->getMessage());
    }
}

/* DELETE LEAD */

if (isset($_GET['delete'])) {
    try {
        $id = (int)$_GET['delete'];

        if ($id <= 0) {
            $error = 'Invalid lead ID.';
        } else {
            $stmt = $conn->prepare("DELETE FROM leads WHERE id = ?");

            if (!$stmt) {
                $error = 'Database error: ' . $conn->error;
                error_log('Prepare failed: ' . $conn->error);
            } else {
                $stmt->bind_param('i', $id);

                if ($stmt->execute()) {
                    $success = 'Lead deleted successfully.';
                    $stmt->close();
                    header("Refresh:2; url=leads.php");
                } else {
                    $error = 'Failed to delete lead: ' . $stmt->error;
                    error_log('Delete failed: ' . $stmt->error);
                    $stmt->close();
                }
            }
        }
    } catch (Throwable $e) {
        $error = 'An error occurred: ' . $e->getMessage();
        error_log('Lead delete error: ' . $e->getMessage());
    }
}

/* GET LEADS */

try {
    $result = $conn->query("SELECT * FROM leads ORDER BY id DESC");
    if (!$result) {
        $error = 'Failed to fetch leads: ' . $conn->error;
        error_log('Query failed: ' . $conn->error);
        $leads = [];
    } else {
        $leads = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Throwable $e) {
    $error = 'An error occurred: ' . $e->getMessage();
    error_log('Leads fetch error: ' . $e->getMessage());
    $leads = [];
}
?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads CRM | KVN Construction</title>
    <link rel="stylesheet" href="assets/css/admin.css">

</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->

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

    <!-- MAIN -->

    <main class="main-content">

        <h1>Leads CRM</h1>

        <?php if ($error): ?>
            <div style="background: #ffe5e5; color: #d10000; padding: 12px; border-radius: 10px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: #e5ffe5; color: #00b000; padding: 12px; border-radius: 10px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- ADD LEAD -->

        <div class="card">

            <form method="POST">

                <input type="text" name="name" placeholder="Client Name" required>
                <input type="text" name="phone" placeholder="Phone" required>
                <input type="email" name="email" placeholder="Email">
                <input type="text" name="project_type" placeholder="Project Type">
                <input type="text" name="budget" placeholder="Budget">

                <select name="status">
                    <option>New Lead</option>
                    <option>Follow-up</option>
                    <option>Converted</option>
                    <option>Closed</option>
                </select>

                <textarea name="notes" placeholder="Notes"></textarea>

                <button type="submit" name="add_lead">Add Lead</button>

            </form>

        </div>

        <!-- LEADS TABLE -->

        <div class="card">

            <table>

                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Project</th>
                    <th>Budget</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>

                <?php foreach ($leads as $row): ?>

                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['project_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['budget']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td>
                        <a href="?delete=<?php echo (int)$row['id']; ?>">Delete</a>
                    </td>
                </tr>

                <?php endforeach; ?>

            </table>

        </div>

    </main>

</div>

</body>

</html>