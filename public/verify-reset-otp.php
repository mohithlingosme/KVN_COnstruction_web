<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| VERIFY RESET OTP PAGE
|--------------------------------------------------------------------------
*/

require_once '../config/app.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/session.php';
require_once ROOT_PATH . '/helpers/csrf.php';
require_once ROOT_PATH . '/middleware/guest.php';

/*
|--------------------------------------------------------------------------
| NO CACHE HEADERS
|--------------------------------------------------------------------------
*/

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

/*
|--------------------------------------------------------------------------
| RESET SESSION VALIDATION
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['password_reset_user_id']) || empty($_SESSION['password_reset_email'])) {
    $_SESSION['error'] = 'Password reset session expired. Please request a new OTP.';
    redirect('forgot-password.php');
}

/*
|--------------------------------------------------------------------------
| OTP EXPIRY CHECK
|--------------------------------------------------------------------------
*/

$otpCreatedAt = $_SESSION['password_reset_created_at'] ?? 0;
$expirySeconds = (defined('OTP_EXPIRY_MINUTES') ? OTP_EXPIRY_MINUTES : 5) * 60;

if ((time() - $otpCreatedAt) > $expirySeconds) {
    unset($_SESSION['password_reset_user_id']);
    unset($_SESSION['password_reset_email']);
    unset($_SESSION['password_reset_created_at']);
    
    $_SESSION['error'] = 'OTP expired. Please request a new one.';
    redirect('forgot-password.php');
}

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle = 'Verify Reset OTP | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';

unset($_SESSION['error']);
unset($_SESSION['success']);

/*
|--------------------------------------------------------------------------
| SESSION DATA
|--------------------------------------------------------------------------
*/

$email = $_SESSION['password_reset_email'] ?? '';
$attempts = (int) ($_SESSION['password_reset_attempts'] ?? 0);
$maxAttempts = defined('OTP_MAX_ATTEMPTS') ? OTP_MAX_ATTEMPTS : 3;
$remainingAttempts = max(0, $maxAttempts - $attempts);

/*
|--------------------------------------------------------------------------
| RESEND TIMER
|--------------------------------------------------------------------------
*/

$resendCooldown = 60; // 60 seconds

include ROOT_PATH . '/app/views/layouts/header.php';

?>

<!-- ================================= -->
<!-- OTP SECTION -->
<!-- ================================= -->

<section class="otp-section">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-5 col-md-7">

                <div class="otp-card">

                    <!-- HEADER -->

                    <div class="otp-header text-center">

                        <div class="otp-icon">
                            <i class="bi bi-envelope-check-fill"></i>
                        </div>

                        <h1>Verify OTP</h1>

                        <p>
                            Enter the 6-digit OTP sent to
                            <strong><?php echo escape($email); ?></strong>
                        </p>

                    </div>

                    <!-- ALERTS -->

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo escape($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <?php echo escape($success); ?>
                        </div>
                    <?php endif; ?>

                    <!-- ATTEMPTS -->

                    <div class="attempt-box text-center">
                        Attempts Remaining:
                        <strong><?php echo (int)$remainingAttempts; ?></strong>
                    </div>

                    <!-- OTP FORM -->

                    <form
                        action="auth/verify-reset-otp-handler.php"
                        method="POST"
                        id="otpForm"
                        autocomplete="off"
                    >

                        <?php echo csrfField(); ?>

                        <!-- HONEYPOT -->
                        <input
                            type="text"
                            name="website"
                            class="d-none"
                            tabindex="-1"
                            autocomplete="off"
                        >

                        <!-- OTP -->
                        <div class="otp-input-wrapper">
                            <input
                                type="text"
                                name="otp"
                                id="otp"
                                class="otp-input"
                                maxlength="6"
                                minlength="6"
                                pattern="[0-9]{6}"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                placeholder="------"
                                required
                                autofocus
                            >
                        </div>

                        <!-- BUTTON -->
                        <button
                            type="submit"
                            class="btn-main w-100"
                            id="verifyBtn"
                        >
                            Verify OTP
                        </button>

                    </form>

                    <!-- RESEND -->
                    <div class="otp-footer text-center">

                        <p class="mb-2">Didn't receive OTP?</p>

                        <button
                            id="resendBtn"
                            class="btn-resend"
                            disabled
                        >
                            Resend OTP in
                            <span id="countdown"><?php echo (int)$resendCooldown; ?></span>s
                        </button>

                    </div>

                    <!-- BACK -->
                    <div class="text-center mt-4">
                        <a href="login.php" class="back-link">
                            <i class="bi bi-arrow-left"></i>
                            Back to Login
                        </a>
                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- PAGE STYLES -->
<!-- ================================= -->

<style>
.otp-section {
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: #f5f7fa;
    padding: 80px 0;
}
.otp-card {
    background: #fff;
    padding: 45px;
    border-radius: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}
.otp-header { margin-bottom: 35px; }
.otp-icon {
    width: 80px;
    height: 80px;
    margin: auto;
    border-radius: 50%;
    background: #f5b400;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}
.otp-icon i {
    font-size: 36px;
    color: #111827;
}
.otp-header h1 {
    font-size: 34px;
    font-weight: 700;
    margin-bottom: 10px;
}
.otp-header p { color: #6b7280; }
.attempt-box {
    margin-bottom: 20px;
    color: #6b7280;
    font-size: 15px;
}
.otp-input-wrapper { margin-bottom: 30px; }
.otp-input {
    width: 100%;
    height: 70px;
    border-radius: 16px;
    border: 2px solid #e5e7eb;
    text-align: center;
    font-size: 32px;
    letter-spacing: 12px;
    font-weight: 700;
    outline: none;
    transition: 0.3s;
}
.otp-input:focus {
    border-color: #f5b400;
    box-shadow: 0 0 0 4px rgba(245,180,0,0.15);
}
.btn-main {
    height: 55px;
    border: none;
    border-radius: 14px;
    background: #f5b400;
    color: #111827;
    font-weight: 700;
    transition: 0.3s;
}
.btn-main:hover { background: #e0a300; }
.otp-footer { margin-top: 25px; }
.btn-resend {
    border: none;
    background: none;
    color: #111827;
    font-weight: 700;
    cursor: pointer;
}
.btn-resend:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.back-link {
    text-decoration: none;
    font-weight: 600;
    color: #111827;
}
.back-link:hover { color: #f5b400; }
@media(max-width:768px){
    .otp-card { padding: 30px 20px; }
    .otp-header h1 { font-size: 28px; }
    .otp-input { height: 60px; font-size: 26px; letter-spacing: 8px; }
}
</style>

<!-- ================================= -->
<!-- OTP SCRIPT -->
<!-- ================================= -->

<script>
document.addEventListener('DOMContentLoaded', function(){
    const otpInput = document.getElementById('otp');
    const otpForm = document.getElementById('otpForm');
    const resendBtn = document.getElementById('resendBtn');
    const countdownText = document.getElementById('countdown');

    let countdown = <?php echo (int)$resendCooldown; ?>;
    
    // Check if there is a resend timestamp in sessionStorage to resume timer
    const lastResend = sessionStorage.getItem('resetOtpLastResend');
    if (lastResend) {
        const passedSeconds = Math.floor((Date.now() - parseInt(lastResend)) / 1000);
        if (passedSeconds < countdown) {
            countdown = countdown - passedSeconds;
        } else {
            countdown = 0;
        }
    }

    if (countdown > 0) {
        const timer = setInterval(() => {
            countdown--;
            countdownText.innerText = countdown;

            if(countdown <= 0){
                clearInterval(timer);
                resendBtn.disabled = false;
                resendBtn.innerHTML = 'Resend OTP';
            }
        }, 1000);
    } else {
        resendBtn.disabled = false;
        resendBtn.innerHTML = 'Resend OTP';
    }

    otpInput.addEventListener('input', function(){
        this.value = this.value.replace(/[^0-9]/g, '');
        if(this.value.length === 6){
            otpForm.submit();
        }
    });

    resendBtn.addEventListener('click', function(){
        if(this.disabled){
            return;
        }

        resendBtn.disabled = true;
        resendBtn.innerHTML = 'Sending...';

        fetch('auth/resend-reset-otp-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'csrf_token=<?php echo csrfToken(); ?>'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success){
                sessionStorage.setItem('resetOtpLastResend', Date.now());
                location.reload();
            } else {
                alert(data.error || 'Unable to resend OTP.');
                resendBtn.disabled = false;
                resendBtn.innerHTML = 'Resend OTP';
            }
        })
        .catch(() => {
            alert('Something went wrong.');
            resendBtn.disabled = false;
            resendBtn.innerHTML = 'Resend OTP';
        });
    });
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
