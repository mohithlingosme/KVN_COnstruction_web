<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/includes/middleware/AuthMiddleware.php';
require_once __DIR__ . '/includes/db.php';

// Require authentication
AuthMiddleware::requireAuth();

function safe_int($v): int {
    return (int)$v;
}

function count_rows(mysqli $conn, string $table): int {
    $allowed = ["leads", "projects", "appointments"];
    if (!in_array($table, $allowed, true)) {
        return 0;
    }

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM {$table}");
        if ($stmt === false) {
            return 0;
        }

        $stmt->execute();

        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;

        $stmt->close();

        return $row ? safe_int($row["total"] ?? 0) : 0;
    } catch (Throwable $e) {
        error_log("Error counting rows in {$table}: " . $e->getMessage());
        return 0;
    }
}

try {
    $totalLeads = count_rows($conn, "leads");
    $totalProjects = count_rows($conn, "projects");
    $totalAppointments = count_rows($conn, "appointments");
} catch (Throwable $e) {
    $totalLeads = 0;
    $totalProjects = 0;
    $totalAppointments = 0;
    error_log("Dashboard error: " . $e->getMessage());
}

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | KVN Construction</title>
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
        <h1>
            Welcome, <?php echo $admin_name; ?>
        </h1>

        <div class="cards">
            <div class="card">
                <h3>Leads</h3>
                <h2><?php echo $totalLeads; ?></h2>
            </div>

            <div class="card">
                <h3>Projects</h3>
                <h2><?php echo $totalProjects; ?></h2>
            </div>

            <div class="card">
                <h3>Appointments</h3>
                <h2><?php echo $totalAppointments; ?></h2>
            </div>
        </div>
    </main>
</div>
</body>
</html>
