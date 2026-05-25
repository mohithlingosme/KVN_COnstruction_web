<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| RESET PASSWORD PAGE
|--------------------------------------------------------------------------
| File:
| /public/reset-password.php
|--------------------------------------------------------------------------
| FEATURES
|--------------------------------------------------------------------------
| - Secure Password Reset
| - CSRF Protection
| - OTP Session Validation
| - Strong Password Validation
| - Password Hashing
| - Session Invalidation
| - Security Logging
|--------------------------------------------------------------------------
*/

require_once '../config/app.php';

require_once ROOT_PATH . '/helpers/security.php';

require_once ROOT_PATH . '/helpers/session.php';

require_once ROOT_PATH . '/helpers/csrf.php';

require_once ROOT_PATH . '/helpers/rateLimiter.php';

require_once ROOT_PATH . '/app/models/User.php';

require_once ROOT_PATH . '/app/controllers/AuthController.php';

require_once ROOT_PATH . '/middleware/guest.php';

/*
|--------------------------------------------------------------------------
| VALID RESET SESSION
|--------------------------------------------------------------------------
*/

if (

    empty($_SESSION['password_reset_verified']) ||

    empty($_SESSION['password_reset_user_id'])

) {

    $_SESSION['error'] =
    'Reset session expired.';

    redirect('forgot-password.php');
}

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle =
'Reset Password | ' . APP_NAME;

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

        redirect('reset-password.php');
    }

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT
    |--------------------------------------------------------------------------
    */

    $rateKey =
    'reset_password_' .
    ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

    if (!checkRateLimit($rateKey, 5, 3600)) {

        $_SESSION['error'] =
        'Too many reset attempts. Please try later.';

        redirect('reset-password.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $password =
    trim($_POST['password'] ?? '');

    $confirmPassword =
    trim($_POST['confirm_password'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        empty($password) ||

        empty($confirmPassword)

    ) {

        $_SESSION['error'] =
        'All fields are required.';

        redirect('reset-password.php');
    }

    if ($password !== $confirmPassword) {

        $_SESSION['error'] =
        'Passwords do not match.';

        redirect('reset-password.php');
    }

    /*
    |--------------------------------------------------------------------------
    | PASSWORD STRENGTH
    |--------------------------------------------------------------------------
    */

    if (strlen($password) < 8) {

        $_SESSION['error'] =
        'Password must be at least 8 characters long.';

        redirect('reset-password.php');
    }

    if (

        !preg_match('/[A-Z]/', $password) ||

        !preg_match('/[a-z]/', $password) ||

        !preg_match('/[0-9]/', $password)

    ) {

        $_SESSION['error'] =
        'Password must contain uppercase, lowercase and number.';

        redirect('reset-password.php');
    }

    /*
    |--------------------------------------------------------------------------
    | USER ID
    |--------------------------------------------------------------------------
    */

    $userId =
    (int) $_SESSION['password_reset_user_id'];

    /*
    |--------------------------------------------------------------------------
    | HASH PASSWORD
    |--------------------------------------------------------------------------
    */

    $passwordHash =
    password_hash(

        $password,

        PASSWORD_DEFAULT
    );

    /*
    |--------------------------------------------------------------------------
    | UPDATE PASSWORD
    |--------------------------------------------------------------------------
    */

    $updated =
    User::updatePassword(

        $userId,

        $passwordHash
    );

    if (!$updated) {

        $_SESSION['error'] =
        'Unable to reset password.';

        redirect('reset-password.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INVALIDATE OLD SESSIONS
    |--------------------------------------------------------------------------
    */

    User::invalidateUserSessions($userId);

    /*
    |--------------------------------------------------------------------------
    | CLEAR RESET OTP
    |--------------------------------------------------------------------------
    */

    User::clearPasswordResetOtp($userId);

    /*
    |--------------------------------------------------------------------------
    | LOG SECURITY EVENT
    |--------------------------------------------------------------------------
    */

    logSecurityEvent(

        'PASSWORD_RESET_SUCCESS',

        [

            'user_id' =>
            $userId,

            'ip' =>
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | DESTROY RESET SESSION
    |--------------------------------------------------------------------------
    */

    unset($_SESSION['password_reset_user_id']);

    unset($_SESSION['password_reset_email']);

    unset($_SESSION['password_reset_verified']);

    unset($_SESSION['password_reset_created_at']);

    /*
    |--------------------------------------------------------------------------
    | REGENERATE SESSION
    |--------------------------------------------------------------------------
    */

    session_regenerate_id(true);

    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    incrementRateLimit($rateKey);

    $_SESSION['success'] =
    'Password reset successful. Please login.';

    redirect('login.php');
}

include ROOT_PATH . '/app/views/layouts/header.php';

?>

<!-- ================================= -->
<!-- RESET PASSWORD SECTION -->
<!-- ================================= -->

<section class="auth-section">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-5 col-md-7">

                <div class="auth-card">

                    <!-- HEADER -->

                    <div class="auth-header text-center">

                        <div class="auth-icon">

                            <i class="bi bi-shield-lock-fill"></i>

                        </div>

                        <h1>

                            Reset Password

                        </h1>

                        <p>

                            Create a strong new password
                            for your account.

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

                        <!-- PASSWORD -->

                        <div class="form-group mb-4">

                            <label class="form-label">

                                New Password

                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Enter new password"
                                required
                            >

                        </div>

                        <!-- CONFIRM -->

                        <div class="form-group mb-4">

                            <label class="form-label">

                                Confirm Password

                            </label>

                            <input
                                type="password"
                                name="confirm_password"
                                class="form-control"
                                placeholder="Confirm new password"
                                required
                            >

                        </div>

                        <!-- PASSWORD RULES -->

                        <div class="password-rules mb-4">

                            <small>

                                Password must contain:

                            </small>

                            <ul>

                                <li>
                                    Minimum 8 characters
                                </li>

                                <li>
                                    Uppercase letter
                                </li>

                                <li>
                                    Lowercase letter
                                </li>

                                <li>
                                    Number
                                </li>

                            </ul>

                        </div>

                        <!-- BUTTON -->

                        <button
                            type="submit"
                            class="btn-main w-100"
                        >

                            Reset Password

                        </button>

                    </form>

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

.password-rules{

    background:#f9fafb;

    padding:15px;

    border-radius:12px;

    border:1px solid #e5e7eb;
}

.password-rules ul{

    margin:10px 0 0;

    padding-left:18px;
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