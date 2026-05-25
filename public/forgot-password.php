<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| FORGOT PASSWORD PAGE
|--------------------------------------------------------------------------
| File:
| /public/forgot-password.php
|--------------------------------------------------------------------------
| FEATURES
|--------------------------------------------------------------------------
| - Secure Password Reset Request
| - CSRF Protection
| - Rate Limiting
| - Email Validation
| - OTP Generation
| - Reset Session Creation
| - Flash Messages
| - Security Logging
|--------------------------------------------------------------------------
*/

require_once '../config/app.php';

require_once ROOT_PATH . '/helpers/security.php';

require_once ROOT_PATH . '/helpers/session.php';

require_once ROOT_PATH . '/helpers/csrf.php';

require_once ROOT_PATH . '/helpers/rateLimiter.php';

require_once ROOT_PATH . '/helpers/mail.php';

require_once ROOT_PATH . '/app/models/User.php';

require_once ROOT_PATH . '/app/controllers/AuthController.php';

require_once ROOT_PATH . '/middleware/guest.php';

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle =
'Forgot Password | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

$error =
$_SESSION['error'] ?? '';

$success =
$_SESSION['success'] ?? '';

unset($_SESSION['error']);

unset($_SESSION['success']);

/*
|--------------------------------------------------------------------------
| FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | CSRF VALIDATION
    |--------------------------------------------------------------------------
    */

    if (!validateCsrf($_POST['csrf_token'] ?? '')) {

        $_SESSION['error'] =
        'Invalid security token.';

        redirect('forgot-password.php');
    }

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT
    |--------------------------------------------------------------------------
    */

    $rateKey =
    'forgot_password_' .
    ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

    if (!checkRateLimit($rateKey, 5, 3600)) {

        $_SESSION['error'] =
        'Too many reset attempts. Please try again later.';

        redirect('forgot-password.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $email =
    sanitize($_POST['email'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (empty($email)) {

        $_SESSION['error'] =
        'Email address is required.';

        redirect('forgot-password.php');
    }

    if (!isValidEmail($email)) {

        $_SESSION['error'] =
        'Please enter a valid email address.';

        redirect('forgot-password.php');
    }

    /*
    |--------------------------------------------------------------------------
    | USER CHECK
    |--------------------------------------------------------------------------
    */

    $user =
    User::findByEmail($email);

    /*
    |--------------------------------------------------------------------------
    | ALWAYS RETURN SUCCESS
    |--------------------------------------------------------------------------
    | Prevent email enumeration
    |--------------------------------------------------------------------------
    */

    if (!$user) {

        incrementRateLimit($rateKey);

        $_SESSION['success'] =
        'If the email exists, a reset OTP has been sent.';

        redirect('forgot-password.php');
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE RESET OTP
    |--------------------------------------------------------------------------
    */

    $otp =
    random_int(100000, 999999);

    $otpHash =
    password_hash(
        (string) $otp,
        PASSWORD_DEFAULT
    );

    $expiresAt =
    date(
        'Y-m-d H:i:s',
        strtotime('+5 minutes')
    );

    /*
    |--------------------------------------------------------------------------
    | SAVE RESET OTP
    |--------------------------------------------------------------------------
    */

    $saved =
    User::savePasswordResetOtp(

        (int) $user['id'],

        $otpHash,

        $expiresAt
    );

    if (!$saved) {

        $_SESSION['error'] =
        'Unable to process password reset request.';

        redirect('forgot-password.php');
    }

    /*
    |--------------------------------------------------------------------------
    | SEND EMAIL
    |--------------------------------------------------------------------------
    */

    sendPasswordResetEmail(

        $email,

        (string) $otp,

        $user['full_name'] ?? 'User'
    );

    /*
    |--------------------------------------------------------------------------
    | RESET SESSION
    |--------------------------------------------------------------------------
    */

    $_SESSION['password_reset_user_id'] =
    $user['id'];

    $_SESSION['password_reset_email'] =
    $email;

    $_SESSION['password_reset_created_at'] =
    time();

    /*
    |--------------------------------------------------------------------------
    | LOG EVENT
    |--------------------------------------------------------------------------
    */

    logSecurityEvent(

        'PASSWORD_RESET_REQUEST',

        [

            'user_id' =>
            $user['id'],

            'email' =>
            $email,

            'ip' =>
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT INCREMENT
    |--------------------------------------------------------------------------
    */

    incrementRateLimit($rateKey);

    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    $_SESSION['success'] =
    'Password reset OTP sent successfully.';

    redirect('verify-reset-otp.php');
}

include ROOT_PATH . '/app/views/layouts/header.php';

?>

<!-- ================================= -->
<!-- FORGOT PASSWORD SECTION -->
<!-- ================================= -->

<section class="auth-section">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-5 col-md-7">

                <div class="auth-card">

                    <!-- HEADER -->

                    <div class="auth-header text-center">

                        <div class="auth-icon">

                            <i class="bi bi-key-fill"></i>

                        </div>

                        <h1>

                            Forgot Password

                        </h1>

                        <p>

                            Enter your registered email
                            to receive a password reset OTP.

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

                    <!-- FORM -->

                    <form
                        method="POST"
                        autocomplete="off"
                    >

                        <?php echo csrfField(); ?>

                        <!-- EMAIL -->

                        <div class="form-group mb-4">

                            <label class="form-label">

                                Email Address

                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="Enter your email"
                                required
                            >

                        </div>

                        <!-- BUTTON -->

                        <button
                            type="submit"
                            class="btn-main w-100"
                        >

                            Send Reset OTP

                        </button>

                    </form>

                    <!-- LOGIN -->

                    <div class="text-center mt-4">

                        <a
                            href="login.php"
                            class="back-link"
                        >

                            <i class="bi bi-arrow-left"></i>

                            Back To Login

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- STYLES -->
<!-- ================================= -->

<style>

.auth-section{

    min-height:100vh;

    display:flex;

    align-items:center;

    background:#f5f7fa;

    padding:80px 0;
}

.auth-card{

    background:#fff;

    padding:45px;

    border-radius:24px;

    box-shadow:
    0 10px 40px rgba(0,0,0,0.08);
}

.auth-header{

    margin-bottom:35px;
}

.auth-icon{

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

.auth-icon i{

    font-size:34px;

    color:#111827;
}

.auth-header h1{

    font-size:34px;

    font-weight:700;

    margin-bottom:10px;
}

.auth-header p{

    color:#6b7280;
}

.form-control{

    height:55px;

    border-radius:14px;

    border:2px solid #e5e7eb;
}

.form-control:focus{

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

.back-link{

    text-decoration:none;

    font-weight:600;

    color:#111827;
}

.back-link:hover{

    color:#f5b400;
}

@media(max-width:768px){

    .auth-card{

        padding:30px 20px;
    }

    .auth-header h1{

        font-size:28px;
    }
}

</style>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>