<?php

declare(strict_types=1);

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';

$pageTitle = 'Audit Logs | ' . APP_NAME;
$search = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$whereSql = '';
$params = [];

if ($search !== '') {
    $whereSql = 'WHERE al.action_type LIKE :search OR al.description LIKE :search OR u.full_name LIKE :search';
    $params[':search'] = '%' . $search . '%';
}

$countStmt = $conn->prepare(
    "SELECT COUNT(*) FROM audit_logs al
     LEFT JOIN users u ON u.id = al.user_id
     $whereSql"
);
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

$sql = "
    SELECT
        al.*,
        u.full_name AS actor_name,
        u.email AS actor_email
    FROM audit_logs al
    LEFT JOIN users u ON u.id = al.user_id
    $whereSql
    ORDER BY al.created_at DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $conn->prepare($sql);

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
                    <h1>Audit Logs</h1>
                    <p>Review administrative actions across the platform.</p>
                </div>
            </div>

            <div class="section-card mb-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="q" class="form-control" value="<?php echo escape($search); ?>" placeholder="Action, description, or user">
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn-admin w-100">Filter</button>
                    </div>
                </form>
            </div>

            <div class="section-card">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <h4>Activity Records</h4>
                    <span class="text-muted"><?php echo number_format($total); ?> total</span>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>Actor</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Entity</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($logs): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo escape((string) $log['created_at']); ?></td>
                                    <td>
                                        <strong><?php echo escape((string) ($log['actor_name'] ?? 'System')); ?></strong><br>
                                        <small class="text-muted"><?php echo escape((string) ($log['actor_email'] ?? '')); ?></small>
                                    </td>
                                    <td><span class="badge bg-dark"><?php echo escape((string) $log['action_type']); ?></span></td>
                                    <td><?php echo escape((string) ($log['description'] ?? '')); ?></td>
                                    <td><?php echo escape((string) ($log['entity_type'] ?? '-')); ?><?php echo !empty($log['entity_id']) ? ' #' . (int) $log['entity_id'] : ''; ?></td>
                                    <td><?php echo escape((string) ($log['ip_address'] ?? '-')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No audit logs found.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <nav class="mt-4">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
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
