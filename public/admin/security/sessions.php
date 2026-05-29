<?php

declare(strict_types=1);

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';

if (request_method() === 'POST' && isset($_POST['revoke_session_id'])) {
    $sessionId = (int) $_POST['revoke_session_id'];
    $stmt = $conn->prepare('SELECT session_token, user_id FROM user_sessions WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $sessionId]);
    $session = $stmt->fetch();

    if ($session) {
        revokeSessionByToken((string) $session['session_token'], 'admin_revoked');
        logAdminAction((int) currentUserId(), 'session_revoked', 'Admin revoked a session', 'user_session', $sessionId);
        $_SESSION['success'] = 'Session revoked successfully.';
    }

    redirect('admin/security/sessions.php');
}

$pageTitle = 'Session Monitor | ' . APP_NAME;
$search = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;
$whereSql = '';
$params = [];

if ($search !== '') {
    $whereSql = 'WHERE u.full_name LIKE :search OR u.email LIKE :search OR us.ip_address LIKE :search';
    $params[':search'] = '%' . $search . '%';
}

$countStmt = $conn->prepare("SELECT COUNT(*) FROM user_sessions us INNER JOIN users u ON u.id = us.user_id $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

$stmt = $conn->prepare(
    "SELECT us.*, u.full_name, u.email, u.role
     FROM user_sessions us
     INNER JOIN users u ON u.id = us.user_id
     $whereSql
     ORDER BY us.last_activity DESC
     LIMIT :limit OFFSET :offset"
);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$sessions = $stmt->fetchAll();
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
                    <h1>Active Sessions</h1>
                    <p>Monitor concurrent sessions, devices, and revoke access when needed.</p>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo escape($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <div class="section-card mb-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="q" class="form-control" value="<?php echo escape($search); ?>" placeholder="User, email, or IP">
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn-admin w-100">Filter</button>
                    </div>
                </form>
            </div>

            <div class="section-card">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <h4>Session Records</h4>
                    <span class="text-muted"><?php echo number_format($total); ?> total</span>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>IP</th>
                                <th>Device</th>
                                <th>Last Activity</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($sessions): ?>
                            <?php foreach ($sessions as $session): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo escape((string) $session['full_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo escape((string) $session['email']); ?></small>
                                    </td>
                                    <td><?php echo escape((string) $session['role']); ?></td>
                                    <td><?php echo escape((string) ($session['ip_address'] ?? '-')); ?></td>
                                    <td><?php echo escape((string) ($session['device_name'] ?? $session['user_agent'] ?? '-')); ?></td>
                                    <td><?php echo escape((string) ($session['last_activity'] ?? '-')); ?></td>
                                    <td>
                                        <span class="badge <?php echo ((int) $session['is_active'] === 1 && empty($session['revoked_at'])) ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo ((int) $session['is_active'] === 1 && empty($session['revoked_at'])) ? 'Active' : 'Revoked'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ((int) $session['is_active'] === 1 && empty($session['revoked_at'])): ?>
                                            <form method="POST">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="revoke_session_id" value="<?php echo (int) $session['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Revoke</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-4">No sessions found.</td></tr>
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
