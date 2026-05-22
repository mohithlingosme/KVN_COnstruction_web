<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| LOGIN PAGE
|--------------------------------------------------------------------------
| File:
| /public/login.php
|--------------------------------------------------------------------------
*/

require_once '../config/app.php';

require_once '../helpers/security.php';

require_once '../helpers/session.php';

require_once '../helpers/csrf.php';

require_once '../helpers/rateLimiter.php';

require_once '../middleware/guest.php';

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle =
'Login | ' . APP_NAME;

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

include '../app/views/layouts/header.php';

?>

<!-- ================================= -->
<!-- LOGIN SECTION -->
<!-- ================================= -->

<section class="auth-section">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-5 col-md-8">

                <div class="auth-card">

                    <!-- ============================== -->
                    <!-- HEADER -->
                    <!-- ============================== -->

                    <div class="auth-header text-center">

                        <h1>
                            Welcome Back
                        </h1>

                        <p>
                            Login to continue to your account.
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
                    <!-- LOGIN OPTIONS -->
                    <!-- ============================== -->

                    <div class="login-tabs mb-4">

                        <button
                            class="login-tab active"
                            data-target="#phoneLogin"
                        >
                            Phone Login
                        </button>

                        <button
                            class="login-tab"
                            data-target="#adminLogin"
                        >
                            Admin Login
                        </button>

                    </div>

                    <!-- ================================= -->
                    <!-- PHONE LOGIN -->
                    <!-- ================================= -->

                    <div
                        id="phoneLogin"
                        class="login-panel active"
                    >

                        <form
                            action="auth/phone-login-handler.php"
                            method="POST"
                        >

                            <?php echo csrfField(); ?>

                            <div class="form-group mb-4">

                                <label class="form-label">

                                    Phone Number

                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">

                                        +91

                                    </span>

                                    <input
                                        type="tel"
                                        name="phone"
                                        class="form-control"
                                        placeholder="Enter mobile number"
                                        maxlength="10"
                                        required
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

                    </div>

                    <!-- ================================= -->
                    <!-- ADMIN LOGIN -->
                    <!-- ================================= -->

                    <div
                        id="adminLogin"
                        class="login-panel"
                    >

                        <form
                            action="auth/admin-login-handler.php"
                            method="POST"
                        >

                            <?php echo csrfField(); ?>

                            <div class="form-group mb-3">

                                <label class="form-label">

                                    Email Address

                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Enter email"
                                    required
                                >

                            </div>

                            <div class="form-group mb-4">

                                <label class="form-label">

                                    Password

                                </label>

                                <div class="password-wrapper">

                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control"
                                        placeholder="Enter password"
                                        required
                                    >

                                    <button
                                        type="button"
                                        class="toggle-password"
                                        onclick="togglePassword()"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </button>

                                </div>

                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">

                                <div class="form-check">

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="remember"
                                    >

                                    <label
                                        class="form-check-label"
                                        for="remember"
                                    >

                                        Remember Me

                                    </label>

                                </div>

                                <a
                                    href="forgot-password.php"
                                    class="auth-link"
                                >

                                    Forgot Password?

                                </a>

                            </div>

                            <button
                                type="submit"
                                class="btn-main w-100"
                            >

                                Login

                            </button>

                        </form>

                    </div>

                    <!-- ================================= -->
                    <!-- FOOTER -->
                    <!-- ================================= -->

                    <div class="auth-footer text-center mt-4">

                        <p>

                            By continuing you agree to
                            our Terms & Privacy Policy.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- AUTH STYLES -->
<!-- ================================= -->

<style>

.auth-section{

    padding:100px 0;
    background:#f5f7fa;
    min-height:100vh;
    display:flex;
    align-items:center;
}

.auth-card{

    background:#fff;
    border-radius:20px;
    padding:40px;
    box-shadow:0 10px 40px rgba(0,0,0,0.08);
}

.auth-header h1{

    font-size:36px;
    font-weight:700;
    margin-bottom:10px;
}

.auth-header p{

    color:#6b7280;
}

.login-tabs{

    display:flex;
    gap:10px;
}

.login-tab{

    flex:1;
    border:none;
    background:#f3f4f6;
    padding:14px;
    border-radius:12px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

.login-tab.active{

    background:#111827;
    color:#fff;
}

.login-panel{

    display:none;
}

.login-panel.active{

    display:block;
}

.form-label{

    font-weight:600;
    margin-bottom:8px;
}

.form-control{

    height:52px;
    border-radius:12px;
    border:1px solid #d1d5db;
}

.input-group-text{

    border-radius:12px 0 0 12px;
}

.btn-main{

    background:#f5b400;
    color:#111827;
    border:none;
    height:52px;
    border-radius:12px;
    font-weight:700;
    transition:0.3s;
}

.btn-main:hover{

    background:#e5a800;
}

.password-wrapper{

    position:relative;
}

.toggle-password{

    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    border:none;
    background:none;
    cursor:pointer;
}

.auth-link{

    color:#111827;
    text-decoration:none;
    font-weight:600;
}

.auth-link:hover{

    color:#f5b400;
}

.auth-footer p{

    color:#9ca3af;
    font-size:14px;
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

<!-- ================================= -->
<!-- LOGIN SCRIPT -->
<!-- ================================= -->

<script>

document.querySelectorAll('.login-tab')

.forEach(tab => {

    tab.addEventListener('click', function(){

        document.querySelectorAll('.login-tab')

        .forEach(btn => {

            btn.classList.remove('active');

        });

        document.querySelectorAll('.login-panel')

        .forEach(panel => {

            panel.classList.remove('active');

        });

        this.classList.add('active');

        document.querySelector(

            this.dataset.target

        ).classList.add('active');

    });
});

function togglePassword()
{
    const password =

    document.getElementById('password');

    if(password.type === 'password'){

        password.type = 'text';

    }else{

        password.type = 'password';
    }
}

</script>

<?php include '../app/views/layouts/footer.php'; ?>