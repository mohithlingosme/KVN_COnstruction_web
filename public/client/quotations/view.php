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

$quotationId = (int) ($_GET['id'] ?? 0);
$quotation = $clientService->getQuotationById($quotationId, $clientId);

if (!$quotation) {
    header('Location: index.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| HANDLE APPROVAL / REJECTION
|--------------------------------------------------------------------------
*/

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'approve') {
        if ($clientService->updateQuotationStatus($quotationId, $clientId, 'Approved')) {
            $successMessage = 'Quotation approved successfully.';
            $quotation = $clientService->getQuotationById($quotationId, $clientId);
        } else {
            $errorMessage = 'Failed to approve quotation.';
        }
    } elseif ($action === 'reject') {
        if ($clientService->updateQuotationStatus($quotationId, $clientId, 'Rejected')) {
            $successMessage = 'Quotation rejected.';
            $quotation = $clientService->getQuotationById($quotationId, $clientId);
        } else {
            $errorMessage = 'Failed to reject quotation.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Details</title>
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
        .card{ background:#fff; border-radius:20px; padding:35px; box-shadow:0 5px 20px rgba(0,0,0,0.08); max-width:800px; }
        .card h1{ margin-bottom:20px; }
        .info-row{ display:flex; justify-content:space-between; padding:15px 0; border-bottom:1px solid #eee; }
        .info-row strong{ color:#555; }
        .badge{ padding:6px 12px; border-radius:30px; font-size:12px; font-weight:bold; display:inline-block; }
        .Pending{ background:#fff3cd; color:#856404; }
        .Approved{ background:#d4edda; color:#155724; }
        .Rejected{ background:#f8d7da; color:#721c24; }
        .actions{ margin-top:30px; display:flex; gap:15px; }
        .approve-btn{ background:#10b981; color:#fff; border:none; padding:14px 28px; border-radius:12px; font-size:16px; font-weight:bold; cursor:pointer; }
        .reject-btn{ background:#ef4444; color:#fff; border:none; padding:14px 28px; border-radius:12px; font-size:16px; font-weight:bold; cursor:pointer; }
        .back-btn{ display:inline-block; text-decoration:none; color:#333; font-weight:bold; margin-bottom:20px; }
        .success{ background:#d4edda; color:#155724; padding:15px; border-radius:10px; margin-bottom:20px; }
        .error{ background:#f8d7da; color:#721c24; padding:15px; border-radius:10px; margin-bottom:20px; }
        @media(max-width:992px){ .sidebar{ width:100%; height:auto; position:relative; } .main{ margin-left:0; } }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>KVN Client</h2>
    <a href="<?php echo base_url('client/dashboard.php'); ?>">Dashboard</a>
    <a href="index.php" class="active">Quotations</a>
    <a href="<?php echo base_url('client/payments/index.php'); ?>">Payments</a>
    <a href="<?php echo base_url('logout.php'); ?>">Logout</a>
</div>
<div class="main">
    <a href="index.php" class="back-btn">← Back to Quotations</a>
    <div class="topbar">
        <h1>Quotation Details</h1>
        <a href="<?php echo base_url('logout.php'); ?>" class="logout-btn">Logout</a>
    </div>
    <?php if (!empty($successMessage)): ?><div class="success"><?php echo htmlspecialchars($successMessage); ?></div><?php endif; ?>
    <?php if (!empty($errorMessage)): ?><div class="error"><?php echo htmlspecialchars($errorMessage); ?></div><?php endif; ?>
    <div class="card">
        <h1><?php echo htmlspecialchars((string)($quotation['title'] ?? 'Quotation #' . $quotation['id'])); ?></h1>
        <div class="info-row"><strong>Status</strong><span class="badge <?php echo htmlspecialchars((string)($quotation['status'] ?? '')); ?>"><?php echo htmlspecialchars((string)($quotation['status'] ?? '')); ?></span></div>
        <div class="info-row"><strong>Total Amount</strong><span>$<?php echo number_format((float)($quotation['total_amount'] ?? $quotation['amount'] ?? 0), 2); ?></span></div>
        <div class="info-row"><strong>Created</strong><span><?php echo htmlspecialchars((string)($quotation['created_at'] ?? '')); ?></span></div>
        <div class="info-row"><strong>Valid Until</strong><span><?php echo htmlspecialchars((string)($quotation['valid_until'] ?? 'N/A')); ?></span></div>
        <hr style="margin:20px 0;">
        <p><?php echo nl2br(htmlspecialchars((string)($quotation['description'] ?? $quotation['notes'] ?? ''))); ?></p>
        <?php if (($quotation['status'] ?? '') === 'Pending'): ?>
        <div class="actions">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="approve-btn" onclick="return confirm('Approve this quotation?')">Approve</button>
            </form>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="reject-btn" onclick="return confirm('Reject this quotation?')">Reject</button>
            </form>
        </div>
        <?php endif; ?>
</div>
</div>
</body>
</html>
