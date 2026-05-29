<?php

declare(strict_types=1);

require_once '../config/app.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/helpers/mail.php';
require_once ROOT_PATH . '/middleware/guest.php';

if (empty($_SESSION['password_reset_user_id']) || empty($_SESSION['password_reset_email'])) {
    $_SESSION['error'] = 'Reset session expired.';
    redirect('forgot-password.php');
}

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

if (request_method() === 'POST') {
    $otp = trim((string) ($_POST['otp'] ?? ''));
    $userId = (int) $_SESSION['password_reset_user_id'];
    $email = (string) $_SESSION['password_reset_email'];
    $user = User::findById($userId);

    if ($user && verifyStoredOtp($userId, $user['phone'] ?? null, $email, $otp, 'password_reset')) {
        $_SESSION['password_reset_verified'] = true;
        $_SESSION['success'] = 'OTP verified. You can now reset your password.';
        redirect('reset-password.php');
    }

    $_SESSION['error'] = 'Invalid or expired OTP.';
    redirect('verify-reset-otp.php');
}

$pageTitle = 'Verify Reset OTP | ' . APP_NAME;
include ROOT_PATH . '/app/views/layouts/header.php';
?>
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="auth-card">
                    <div class="auth-header text-center">
                        <h1>Verify Reset OTP</h1>
                        <p>Enter the OTP sent to <?php echo escape($_SESSION['password_reset_email']); ?>.</p>
                    </div>
                    <?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo escape($error); ?></div><?php endif; ?>
                    <?php if ($success !== ''): ?><div class="alert alert-success"><?php echo escape($success); ?></div><?php endif; ?>
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <div class="form-group mb-4">
                            <label class="form-label">OTP</label>
                            <input type="text" name="otp" class="form-control" maxlength="6" minlength="6" required>
                        </div>
                        <button type="submit" class="btn-main w-100">Verify OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
