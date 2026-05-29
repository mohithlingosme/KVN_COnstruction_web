<?php

declare(strict_types=1);

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';

$pageTitle = 'Security Logs | ' . APP_NAME;
$search = trim((string) ($_GET['q'] ?? ''));
$level = trim((string) ($_GET['level'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(sl.event_type LIKE :search OR sl.event_details LIKE :search OR sl.ip_address LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

if ($level !== '' && in_array($level, ['info', 'warning', 'critical'], true)) {
    $where[] = 'sl.event_level = :level';
    $params[':level'] = $level;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $conn->prepare(
    "SELECT COUNT(*)
     FROM security_logs sl
     LEFT JOIN users u ON u.id = sl.user_id
     $whereSql"
);
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

$stmt = $conn->prepare(
    "SELECT sl.*, u.full_name
     FROM security_logs sl
     LEFT JOIN users u ON u.id = sl.user_id
     $whereSql
     ORDER BY sl.created_at DESC
     LIMIT :limit OFFSET :offset"
);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo base_url('../assets/admin/css/admin.css'); ?>">
</head>
<body>
<div class="admin-layout">
    <?php include '../../../app/views/layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include '../../../app/views/layouts/navbar.php'; ?>
        <div class="admin-content">
            <div class="dashboard-header">
                <div>
                    <h1>Security Logs</h1>
                    <p>Review authentication, suspicious activity, and operational security events.</p>
                </div>
            </div>

            <div class="section-card mb-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="q" class="form-control" value="<?php echo escape($search); ?>" placeholder="Event, details, or IP">
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Level</label>
                        <select name="level" class="form-select">
                            <option value="">All levels</option>
                            <option value="info" <?php echo $level === 'info' ? 'selected' : ''; ?>>Info</option>
                            <option value="warning" <?php echo $level === 'warning' ? 'selected' : ''; ?>>Warning</option>
                            <option value="critical" <?php echo $level === 'critical' ? 'selected' : ''; ?>>Critical</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn-admin w-100">Filter</button>
                    </div>
                </form>
            </div>

            <div class="section-card">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <h4>Security Events</h4>
                    <span class="text-muted"><?php echo number_format($total); ?> total</span>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>User</th>
                                <th>Level</th>
                                <th>Event</th>
                                <th>Details</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($logs): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo escape((string) $log['created_at']); ?></td>
                                    <td><?php echo escape((string) ($log['full_name'] ?? 'Guest/System')); ?></td>
                                    <td>
                                        <span class="badge <?php echo $log['event_level'] === 'critical' ? 'bg-danger' : ($log['event_level'] === 'warning' ? 'bg-warning text-dark' : 'bg-secondary'); ?>">
                                            <?php echo escape((string) $log['event_level']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo escape((string) $log['event_type']); ?></td>
                                    <td><?php echo escape((string) ($log['event_details'] ?? '')); ?></td>
                                    <td><?php echo escape((string) ($log['ip_address'] ?? '-')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4">No security logs found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <nav class="mt-4">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&level=<?php echo urlencode($level); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>
</body>
</html>
