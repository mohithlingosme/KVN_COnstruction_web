<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| VERIFY PHONE OTP PAGE
|--------------------------------------------------------------------------
| File:
| /public/verify-phone-otp.php
|--------------------------------------------------------------------------
*/

require_once '../config/app.php';

require_once '../helpers/security.php';

require_once '../helpers/session.php';

require_once '../helpers/csrf.php';

require_once '../helpers/otp.php';

require_once '../middleware/guest.php';

/*
|--------------------------------------------------------------------------
| OTP SESSION VALIDATION
|--------------------------------------------------------------------------
*/

if (!isOtpSessionValid()) {

    $_SESSION['error'] =
    'OTP session expired.';

    redirect('phone-login.php');
}

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle =
'Verify OTP | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

$error =
$_SESSION['error'] ?? null;

$success =
$_SESSION['success'] ?? null;

unset($_SESSION['error']);

unset($_SESSION['success']);

$phone =
$_SESSION['otp_phone'] ?? '';

include '../app/views/layouts/header.php';

?>

<!-- ================================= -->
<!-- OTP SECTION -->
<!-- ================================= -->

<section class="otp-section">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-5 col-md-7">

                <div class="otp-card">

                    <!-- ============================== -->
                    <!-- HEADER -->
                    <!-- ============================== -->

                    <div class="otp-header text-center">

                        <div class="otp-icon">

                            <i class="bi bi-shield-lock"></i>

                        </div>

                        <h1>

                            OTP Verification

                        </h1>

                        <p>

                            Enter the 6-digit OTP sent to

                            <strong>

                                +91
                                <?php echo escape($phone); ?>

                            </strong>

                        </p>

                    </div>

                    <!-- ============================== -->
                    <!-- ALERTS -->
                    <!-- ============================== -->

                    <?php if($error): ?>

                        <div class="alert alert-danger">

                            <?php echo escape($error); ?>

                        </div>

                    <?php endif; ?>

                    <?php if($success): ?>

                        <div class="alert alert-success">

                            <?php echo escape($success); ?>

                        </div>

                    <?php endif; ?>

                    <!-- ============================== -->
                    <!-- OTP FORM -->
                    <!-- ============================== -->

                    <form
                        action="auth/verify-phone-otp-handler.php"
                        method="POST"
                        id="otpForm"
                    >

                        <?php echo csrfField(); ?>

                        <div class="otp-input-wrapper">

                            <input
                                type="text"
                                name="otp"
                                id="otp"
                                class="otp-input"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                placeholder="------"
                                required
                                autofocus
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn-main w-100"
                        >

                            Verify OTP

                        </button>

                    </form>

                    <!-- ============================== -->
                    <!-- RESEND -->
                    <!-- ============================== -->

                    <div class="otp-footer text-center">

                        <p>

                            Didn't receive OTP?

                        </p>

                        <button
                            id="resendBtn"
                            class="btn-resend"
                            disabled
                        >

                            Resend OTP in
                            <span id="countdown">

                                30

                            </span>s

                        </button>

                    </div>

                    <!-- ============================== -->
                    <!-- BACK -->
                    <!-- ============================== -->

                    <div class="text-center mt-4">

                        <a
                            href="login.php"
                            class="back-link"
                        >

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
<!-- OTP STYLES -->
<!-- ================================= -->

<style>

.otp-section{

    min-height:100vh;

    display:flex;

    align-items:center;

    background:#f5f7fa;

    padding:80px 0;
}

.otp-card{

    background:#fff;

    padding:45px;

    border-radius:20px;

    box-shadow:
    0 10px 40px rgba(0,0,0,0.08);
}

.otp-header{

    margin-bottom:35px;
}

.otp-icon{

    width:80px;

    height:80px;

    margin:auto;

    border-radius:50%;

    background:#f5b400;

    display:flex;

    align-items:center;

    justify-content:center;

    margin-bottom:20px;
}

.otp-icon i{

    font-size:36px;

    color:#111827;
}

.otp-header h1{

    font-size:34px;

    font-weight:700;

    margin-bottom:10px;
}

.otp-header p{

    color:#6b7280;
}

.otp-input-wrapper{

    margin-bottom:30px;
}

.otp-input{

    width:100%;

    height:70px;

    border-radius:16px;

    border:2px solid #e5e7eb;

    text-align:center;

    font-size:32px;

    letter-spacing:12px;

    font-weight:700;

    outline:none;

    transition:0.3s;
}

.otp-input:focus{

    border-color:#f5b400;

    box-shadow:
    0 0 0 4px rgba(245,180,0,0.15);
}

.btn-main{

    height:55px;

    border:none;

    border-radius:14px;

    background:#f5b400;

    color:#111827;

    font-weight:700;

    transition:0.3s;
}

.btn-main:hover{

    background:#e0a300;
}

.otp-footer{

    margin-top:25px;
}

.btn-resend{

    border:none;

    background:none;

    color:#111827;

    font-weight:700;

    cursor:pointer;
}

.btn-resend:disabled{

    opacity:0.6;

    cursor:not-allowed;
}

.back-link{

    text-decoration:none;

    font-weight:600;

    color:#111827;
}

.back-link:hover{

    color:#f5b400;
}

@media(max-width:768px){

    .otp-card{

        padding:30px 20px;
    }

    .otp-header h1{

        font-size:28px;
    }

    .otp-input{

        height:60px;

        font-size:26px;

        letter-spacing:8px;
    }
}

</style>

<!-- ================================= -->
<!-- OTP SCRIPT -->
<!-- ================================= -->

<script>

let countdown = 30;

const resendBtn =
document.getElementById('resendBtn');

const countdownText =
document.getElementById('countdown');

/*
|--------------------------------------------------------------------------
| COUNTDOWN
|--------------------------------------------------------------------------
*/

const timer = setInterval(() => {

    countdown--;

    countdownText.innerText =
    countdown;

    if(countdown <= 0){

        clearInterval(timer);

        resendBtn.disabled = false;

        resendBtn.innerHTML =
        'Resend OTP';
    }

}, 1000);

/*
|--------------------------------------------------------------------------
| RESEND OTP
|--------------------------------------------------------------------------
*/

resendBtn.addEventListener('click', function(){

    if(this.disabled) return;

    window.location.href =
    'auth/resend-otp-handler.php';
});

/*
|--------------------------------------------------------------------------
| OTP INPUT VALIDATION
|--------------------------------------------------------------------------
*/

document.getElementById('otp')

.addEventListener('input', function(){

    this.value =
    this.value.replace(/[^0-9]/g, '');
});

</script>

<?php include '../app/views/layouts/footer.php'; ?>