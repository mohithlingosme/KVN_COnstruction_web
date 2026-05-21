<?php

declare(strict_types=1);

require_once "includes/auth.php";
require_once "includes/db.php";

function safe_int($v): int {
    return (int)$v;
}

function count_rows(mysqli $conn, string $table): int {
    // Restrict to only the tables used by this dashboard to avoid accidental injection.
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
        return 0;
    }
}

$totalLeads = count_rows($conn, "leads");
$totalProjects = count_rows($conn, "projects");
$totalAppointments = count_rows($conn, "appointments");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | KVN Construction</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <h2>KVN Admin</h2>

        <ul>
            <li>Dashboard</li>
            <li>Leads</li>
            <li>Projects</li>
            <li>Clients</li>
            <li>Quotations</li>
            <li>Appointments</li>
            <li>Packages</li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>
            Welcome,
            <?php echo htmlspecialchars($_SESSION["admin_name"] ?? "Admin", ENT_QUOTES, "UTF-8"); ?>
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
