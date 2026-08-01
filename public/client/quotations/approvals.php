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

$quotations = $clientService->getQuotations($clientId);
$pendingQuotations = array_filter($quotations, fn($q) => ($q['status'] ?? '') === 'Pending');

/*
|--------------------------------------------------------------------------
| HANDLE APPROVAL / REJECTION
|--------------------------------------------------------------------------
*/

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quotationId = (int) ($_POST['quotation_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($quotationId > 0) {
        if ($action === 'approve') {
            if ($clientService->updateQuotationStatus($quotationId, $clientId, 'Approved')) {
                $successMessage = 'Quotation approved successfully.';
            } else {
                $errorMessage = 'Failed to approve quotation.';
            }
        } elseif ($action === 'reject') {
            if ($clientService->updateQuotationStatus($quotationId, $clientId, 'Rejected')) {
                $successMessage = 'Quotation rejected.';
            } else {
                $errorMessage = 'Failed to reject quotation.';
            }
        }
    }
    $quotations = $clientService->getQuotations($clientId);
    $pendingQuotations = array_filter($quotations, fn($q) => ($q['status'] ?? '') === 'Pending');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Approvals</title>
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
        .card{ background:#fff; border-radius:20px; padding:25px; margin-bottom:20px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        .card h3{ margin-bottom:8px; }
        .card p{ color:#555; margin-bottom:8px; }
        .badge{ padding:6px 12px; border-radius:30px; font-size:12px; font-weight:bold; display:inline-block; }
        .Pending{ background:#fff3cd; color:#856404; }
        .Approved{ background:#d4edda; color:#155724; }
        .Rejected{ background:#f8d7da; color:#721c24; }
        .btn{ border:none; padding:10px 20px; border-radius:10px; font-weight:bold; cursor:pointer; margin-right:10px; }
        .btn-approve{ background:#10b981; color:#fff; }
        .btn-reject{ background:#ef4444; color:#fff; }
        .back-btn{ display:inline-block; text-decoration:none; color:#333; font-weight:bold; margin-bottom:20px; }
        .success{ background:#d4edda; color:#155724; padding:15px; border-radius:10px; margin-bottom:20px; }
        .error{ background:#f8d7da; color:#721c24; padding:15px; border-radius:10px; margin-bottom:20px; }
        .empty{ text-align:center; padding:60px; color:#777; background:#fff; border-radius:20px; }
        @media(max-width:992px){ .sidebar{ width:100%; height:auto; position:relative; } .main{ margin-left:0; } }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>KVN Client</h2>
    <a href="<?php echo base_url('client/dashboard.php'); ?>">Dashboard</a>
    <a href="index.php">Quotations</a>
    <a href="approvals.php" class="active">Approvals</a>
    <a href="<?php echo base_url('logout.php'); ?>">Logout</a>
</div>
<div class="main">
    <a href="index.php" class="back-btn">← Back to Quotations</a>
    <div class="topbar">
        <h1>Quotation Approvals</h1>
        <a href="<?php echo base_url('logout.php'); ?>" class="logout-btn">Logout</a>
    </div>
    <?php if (!empty($successMessage)): ?><div class="success"><?php echo htmlspecialchars($successMessage); ?></div><?php endif; ?>
    <?php if (!empty($errorMessage)): ?><div class="error"><?php echo htmlspecialchars($errorMessage); ?></div><?php endif; ?>
    <?php if (count($pendingQuotations) > 0): ?>
        <?php foreach ($pendingQuotations as $q): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars((string)($q['title'] ?? 'Quotation #' . $q['id'])); ?></h3>
                <p><strong>Amount:</strong> $<?php echo number_format((float)($q['total_amount'] ?? $q['amount'] ?? 0), 2); ?></p>
                <p><strong>Status:</strong> <span class="badge <?php echo htmlspecialchars((string)($q['status'] ?? '')); ?>"><?php echo htmlspecialchars((string)($q['status'] ?? '')); ?></span></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars((string)($q['created_at'] ?? '')); ?></p>
                <div style="margin-top:15px;">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="quotation_id" value="<?php echo (int)$q['id']; ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-approve" onclick="return confirm('Approve this quotation?')">Approve</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="quotation_id" value="<?php echo (int)$q['id']; ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-reject" onclick="return confirm('Reject this quotation?')">Reject</button>
                    </form>
                </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty"><h2>No Pending Approvals</h2><p>All quotations have been reviewed.</p></div>
    <?php endif; ?>
</div>
</body>
</html>
