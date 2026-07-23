<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| SMTP SETTINGS MANAGEMENT
|--------------------------------------------------------------------------
| File: /public/admin/settings/smtp.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';
require_once '../../../helpers/security.php';
require_once '../../../helpers/csrf.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle = 'SMTP Settings | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FETCH CURRENT SETTINGS
|--------------------------------------------------------------------------
*/

$settings = [];
try {
    $query = "SELECT * FROM settings WHERE `group` = 'smtp' ORDER BY `key` ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    
    foreach ($rows as $row) {
        $settings[$row['key']] = $row['value'];
    }
} catch (Exception $e) {
    error_log('SMTP settings fetch error: ' . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid security token.';
        redirect('admin/settings/smtp.php');
    }

    $smtpHost = trim(sanitize($_POST['smtp_host'] ?? ''));
    $smtpPort = (int) ($_POST['smtp_port'] ?? 587);
    $smtpEncryption = trim(sanitize($_POST['smtp_encryption'] ?? 'tls'));
    $smtpUsername = trim(sanitize($_POST['smtp_username'] ?? ''));
    $smtpPassword = trim($_POST['smtp_password'] ?? '');
    $smtpFromEmail = trim(sanitize($_POST['smtp_from_email'] ?? ''));
    $smtpFromName = trim(sanitize($_POST['smtp_from_name'] ?? ''));

    try {
        $conn->beginTransaction();

        $settingsData = [
            'smtp_host' => $smtpHost,
            'smtp_port' => (string) $smtpPort,
            'smtp_encryption' => $smtpEncryption,
            'smtp_username' => $smtpUsername,
            'smtp_from_email' => $smtpFromEmail,
            'smtp_from_name' => $smtpFromName,
        ];

        // Only update password if provided
        if (!empty($smtpPassword)) {
            $settingsData['smtp_password'] = $smtpPassword;
        }

        foreach ($settingsData as $key => $value) {
            $checkQuery = "SELECT id FROM settings WHERE `group` = 'smtp' AND `key` = :key LIMIT 1";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->execute([':key' => $key]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                $updateQuery = "UPDATE settings SET `value` = :value, updated_at = NOW() WHERE `group` = 'smtp' AND `key` = :key";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([':value' => $value, ':key' => $key]);
            } else {
                $insertQuery = "INSERT INTO settings (`group`, `key`, `value`, created_at) VALUES ('smtp', :key, :value, NOW())";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->execute([':key' => $key, ':value' => $value]);
            }
        }

        $conn->commit();
        $_SESSION['success'] = 'SMTP settings saved successfully.';

        // Update config constants for current request
        define('SMTP_HOST', $smtpHost);
        define('SMTP_PORT', $smtpPort);
        define('SMTP_ENCRYPTION', $smtpEncryption);
        define('SMTP_USERNAME', $smtpUsername);
        define('SMTP_FROM_EMAIL', $smtpFromEmail);
        define('SMTP_FROM_NAME', $smtpFromName);

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = 'Failed to save settings: ' . $e->getMessage();
    }

    redirect('admin/settings/smtp.php');
}

/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/admin.css'); ?>">
</head>
<body>
<div class="admin-layout">
    <?php include '../../../app/views/layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include '../../../app/views/layouts/navbar.php'; ?>
        <div class="admin-content">

            <div class="dashboard-header">
                <div>
                    <h1>SMTP Settings</h1>
                    <p>Configure email server settings for sending emails.</p>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo escape($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo escape($success); ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-envelope"></i> SMTP Configuration</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <?php echo csrfField(); ?>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">SMTP Host *</label>
                                        <input type="text" name="smtp_host" class="form-control"
                                               value="<?php echo escape($settings['smtp_host'] ?? 'smtp.gmail.com'); ?>" required>
                                        <small class="text-muted">e.g., smtp.gmail.com, smtp.sendgrid.net</small>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Port *</label>
                                        <input type="number" name="smtp_port" class="form-control"
                                               value="<?php echo (int)($settings['smtp_port'] ?? 587); ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Encryption</label>
                                        <select name="smtp_encryption" class="form-select">
                                            <option value="tls" <?php echo ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                            <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                            <option value="" <?php echo empty($settings['smtp_encryption']) ? 'selected' : ''; ?>>None</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Username *</label>
                                        <input type="text" name="smtp_username" class="form-control"
                                               value="<?php echo escape($settings['smtp_username'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="smtp_password" class="form-control"
                                               placeholder="Leave blank to keep current">
                                        <small class="text-muted">Enter new password only if changing</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">From Email *</label>
                                        <input type="email" name="smtp_from_email" class="form-control"
                                               value="<?php echo escape($settings['smtp_from_email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">From Name *</label>
                                        <input type="text" name="smtp_from_name" class="form-control"
                                               value="<?php echo escape($settings['smtp_from_name'] ?? APP_NAME); ?>" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Settings
                                </button>

                                <button type="button" class="btn btn-success ms-2" onclick="runSmtpConnectionTest()">
                                    <i class="bi bi-send"></i> Test Connection
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> SMTP Info</h5>
                        </div>
                        <div class="card-body">
                            <h6>Common SMTP Providers</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Gmail</strong></td><td>smtp.gmail.com:587 (TLS)</td></tr>
                                <tr><td><strong>SendGrid</strong></td><td>smtp.sendgrid.net:587 (TLS)</td></tr>
                                <tr><td><strong>Mailgun</strong></td><td>smtp.mailgun.org:587 (TLS)</td></tr>
                                <tr><td><strong>Postmark</strong></td><td>smtp.postmarkapp.com:587 (TLS)</td></tr>
                                <tr><td><strong>Amazon SES</strong></td><td>email-smtp.us-east-1.amazonaws.com:587 (TLS)</td></tr>
                            </table>
                            <hr>
                            <h6>Security Note</h6>
                            <p class="small text-muted">SMTP credentials are stored encrypted in the database. Use app-specific passwords for Gmail.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function runSmtpConnectionTest() {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Testing...';

    fetch('test-smtp.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?php echo csrfToken(); ?>'
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send"></i> Test Connection';
    })
    .catch(() => {
        alert('Connection test failed.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send"></i> Test Connection';
    });
}
</script>
</body>
</html>