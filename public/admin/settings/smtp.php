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
| REFACTORED: All SQL delegated to SettingsRepository.
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';
require_once '../../../helpers/security.php';
require_once '../../../helpers/csrf.php';
require_once '../../../helpers/session.php';
require_once '../../../helpers/functions.php';
require_once '../../includes/repositories.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle = 'SMTP Settings | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| ADMIN SETTINGS SERVICE
|--------------------------------------------------------------------------
*/

$settingsService = new \App\Services\AdminSettingsService();

/*
|--------------------------------------------------------------------------
| FETCH CURRENT SETTINGS
|--------------------------------------------------------------------------
*/

$settings = $settingsService->getSmtpSettings();

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

    $result = $settingsService->saveSmtpSettings($_POST);

    if ($result['success']) {
        $_SESSION['success'] = $result['message'];

        // Update config constants for current request
        define('SMTP_HOST', (string) ($_POST['smtp_host'] ?? ''));
        define('SMTP_PORT', (int) ($_POST['smtp_port'] ?? 587));
        define('SMTP_ENCRYPTION', (string) ($_POST['smtp_encryption'] ?? 'tls'));
        define('SMTP_USERNAME', (string) ($_POST['smtp_username'] ?? ''));
        define('SMTP_FROM_EMAIL', (string) ($_POST['smtp_from_email'] ?? ''));
        define('SMTP_FROM_NAME', (string) ($_POST['smtp_from_name'] ?? ''));
    } else {
        $_SESSION['error'] = $result['message'];
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

