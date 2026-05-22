<?php

require_once '../config/app.php';

require_once '../helpers/csrf.php';

require_once '../helpers/otp.php';

require_once '../helpers/sms.php';

require_once '../helpers/security.php';

require_once '../helpers/session.php';

$pageTitle = 'Phone Login | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| REDIRECT IF ALREADY LOGGED IN
|--------------------------------------------------------------------------
*/

if (isLoggedIn()) {

    if (isAdmin()) {

        header(
            'Location: admin/dashboard.php'
        );

    } else {

        header(
            'Location: client/dashboard.php'
        );
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| FORM HANDLING
|--------------------------------------------------------------------------
*/

$error = '';

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | CSRF VALIDATION
    |--------------------------------------------------------------------------
    */

    validateCsrf();

    /*
    |--------------------------------------------------------------------------
    | SANITIZE PHONE
    |--------------------------------------------------------------------------
    */

    $phone = sanitizePhoneNumber(

        $_POST['phone'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | VALIDATE PHONE
    |--------------------------------------------------------------------------
    */

    if (!validateIndianPhone($phone)) {

        $error =
        'Enter valid phone number.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | OTP BLOCK CHECK
        |--------------------------------------------------------------------------
        */

        if (isOtpBlocked($phone)) {

            $error =
            'Too many failed attempts. Try later.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | CREATE OTP
            |--------------------------------------------------------------------------
            */

            $otpData =
            createPhoneOtp($phone);

            if ($otpData['success']) {

                /*
                |--------------------------------------------------------------------------
                | SEND SMS
                |--------------------------------------------------------------------------
                */

                $sms =
                sendOtpSms(

                    $phone,

                    $otpData['otp']
                );

                if ($sms['success']) {

                    /*
                    |--------------------------------------------------------------------------
                    | OTP SESSION
                    |--------------------------------------------------------------------------
                    */

                    createOtpSession($phone);

                    /*
                    |--------------------------------------------------------------------------
                    | REDIRECT TO VERIFY
                    |--------------------------------------------------------------------------
                    */

                    $_SESSION['otp_phone'] =
                    $phone;

                    header(

                        'Location: verify-phone-otp.php'
                    );

                    exit;

                } else {

                    $error =
                    $sms['message'];
                }

            } else {

                $error =
                $otpData['message'];
            }
        }
    }
}

include '../app/views/layouts/header.php';

?>

<!-- ===================================== -->
<!-- PHONE LOGIN -->
<!-- ===================================== -->

<section class="login-page">

    <div class="container">

        <div class="auth-wrapper">

            <div class="auth-left">

                <div class="auth-content">

                    <span class="auth-tag">

                        Secure OTP Login

                    </span>

                    <h1>

                        Welcome to
                        KVN Construction

                    </h1>

                    <p>

                        Login securely using your
                        mobile number and OTP.

                    </p>

                    <ul class="auth-features">

                        <li>

                            <i class="bi bi-check-circle-fill"></i>

                            Secure OTP Authentication

                        </li>

                        <li>

                            <i class="bi bi-check-circle-fill"></i>

                            Instant Client Access

                        </li>

                        <li>

                            <i class="bi bi-check-circle-fill"></i>

                            Construction Project Dashboard

                        </li>

                        <li>

                            <i class="bi bi-check-circle-fill"></i>

                            Estimate Tracking & CRM

                        </li>

                    </ul>

                </div>

            </div>

            <div class="auth-right">

                <div class="auth-card">

                    <div class="auth-header">

                        <h2>

                            Phone Login

                        </h2>

                        <p>

                            Enter your mobile number

                        </p>

                    </div>

                    <!-- ========================= -->
                    <!-- ALERTS -->
                    <!-- ========================= -->

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

                    <!-- ========================= -->
                    <!-- FORM -->
                    <!-- ========================= -->

                    <form
                        method="POST"
                        class="auth-form"
                    >

                        <?php echo csrfField(); ?>

                        <div class="form-group">

                            <label>

                                Mobile Number

                            </label>

                            <div class="phone-input">

                                <span>

                                    +91

                                </span>

                                <input
                                    type="text"
                                    name="phone"
                                    maxlength="10"
                                    minlength="10"
                                    pattern="[0-9]{10}"
                                    placeholder="9876543210"
                                    required
                                    autocomplete="off"
                                >

                            </div>

                        </div>

                        <button
                            type="submit"
                            class="btn-main w-100"
                        >

                            Send OTP

                        </button>

                    </form>

                    <!-- ========================= -->
                    <!-- FOOTER -->
                    <!-- ========================= -->

                    <div class="auth-footer">

                        <p>

                            Admin Login?

                            <a href="admin/login.php">

                                Login Here

                            </a>

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ===================================== -->
<!-- PAGE STYLES -->
<!-- ===================================== -->

<style>

.login-page{

    padding:100px 0;

    background:#f8fafc;

    min-height:100vh;

    display:flex;

    align-items:center;
}

.auth-wrapper{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:60px;

    align-items:center;
}

.auth-left{

    padding-right:40px;
}

.auth-tag{

    display:inline-block;

    background:#fff4cf;

    padding:10px 18px;

    border-radius:50px;

    margin-bottom:25px;

    font-weight:600;
}

.auth-content h1{

    font-size:52px;

    font-weight:800;

    margin-bottom:25px;

    line-height:1.2;
}

.auth-content p{

    font-size:18px;

    color:#666;

    line-height:1.8;

    margin-bottom:35px;
}

.auth-features{

    list-style:none;

    padding:0;
}

.auth-features li{

    margin-bottom:18px;

    display:flex;

    align-items:center;

    gap:12px;

    font-weight:500;
}

.auth-features i{

    color:#22c55e;
}

.auth-card{

    background:#fff;

    padding:50px;

    border-radius:30px;

    box-shadow:0 20px 50px rgba(0,0,0,0.08);
}

.auth-header{

    margin-bottom:35px;
}

.auth-header h2{

    font-size:36px;

    font-weight:800;

    margin-bottom:10px;
}

.auth-header p{

    color:#666;
}

.form-group{

    margin-bottom:25px;
}

.form-group label{

    display:block;

    margin-bottom:10px;

    font-weight:600;
}

.phone-input{

    display:flex;

    align-items:center;

    border:1px solid #d1d5db;

    border-radius:16px;

    overflow:hidden;
}

.phone-input span{

    padding:16px 20px;

    background:#f8fafc;

    font-weight:600;

    border-right:1px solid #e5e7eb;
}

.phone-input input{

    border:none;

    width:100%;

    padding:16px;

    outline:none;
}

.auth-footer{

    margin-top:25px;

    text-align:center;
}

.auth-footer a{

    color:#f5b400;

    font-weight:600;
}

@media(max-width:991px){

    .auth-wrapper{

        grid-template-columns:1fr;
    }

    .auth-left{

        display:none;
    }
}

</style>

<?php include '../app/views/layouts/footer.php'; ?>