<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ADMIN LOGIN PAGE
|--------------------------------------------------------------------------
| File:
| /public/admin/login.php
|--------------------------------------------------------------------------
*/

require_once '../../config/app.php';

require_once '../../helpers/security.php';

require_once '../../helpers/session.php';

require_once '../../helpers/csrf.php';

require_once '../../helpers/rateLimiter.php';

require_once '../../middleware/guest.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
'Admin Login | ' . APP_NAME;

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

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>

        <?php echo escape($pageTitle); ?>

    </title>

    <!-- ================================= -->
    <!-- CSS -->
    <!-- ================================= -->

    <link
        rel="stylesheet"
        href="<?php echo base_url('../assets/css/style.css'); ?>"
    >

    <link
        rel="stylesheet"
        href="<?php echo base_url('../assets/admin/css/admin.css'); ?>"
    >

    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

</head>

<body class="admin-auth-body">

<!-- ================================= -->
<!-- ADMIN LOGIN -->
<!-- ================================= -->

<section class="admin-auth-section">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-5 col-md-7">

                <div class="admin-auth-card">

                    <!-- ============================== -->
                    <!-- LOGO -->
                    <!-- ============================== -->

                    <div class="admin-auth-logo text-center">

                        <h1>

                            <?php echo APP_NAME; ?>

                        </h1>

                        <p>

                            Secure Admin Access Portal

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
                    <!-- FORM -->
                    <!-- ============================== -->

                    <form
                        action="../auth/admin-login-handler.php"
                        method="POST"
                    >

                        <?php echo csrfField(); ?>

                        <!-- EMAIL -->

                        <div class="form-group mb-4">

                            <label class="form-label">

                                Admin Email

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-envelope"></i>

                                </span>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Enter admin email"
                                    required
                                    autocomplete="off"
                                >

                            </div>

                        </div>

                        <!-- PASSWORD -->

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

                        <!-- REMEMBER -->

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
                                href="../forgot-password.php"
                                class="forgot-link"
                            >

                                Forgot Password?

                            </a>

                        </div>

                        <!-- BUTTON -->

                        <button
                            type="submit"
                            class="btn-admin-login w-100"
                        >

                            <i class="bi bi-shield-lock"></i>

                            Secure Login

                        </button>

                    </form>

                    <!-- ============================== -->
                    <!-- SECURITY INFO -->
                    <!-- ============================== -->

                    <div class="admin-security-info">

                        <div class="security-item">

                            <i class="bi bi-shield-check"></i>

                            Encrypted Session Protection

                        </div>

                        <div class="security-item">

                            <i class="bi bi-lock"></i>

                            Brute Force Prevention Enabled

                        </div>

                        <div class="security-item">

                            <i class="bi bi-fingerprint"></i>

                            Session Fingerprinting Active

                        </div>

                    </div>

                    <!-- ============================== -->
                    <!-- FOOTER -->
                    <!-- ============================== -->

                    <div class="admin-auth-footer text-center">

                        <small>

                            Authorized access only.

                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- ADMIN AUTH STYLES -->
<!-- ================================= -->

<style>

.admin-auth-body{

    background:
    linear-gradient(

        135deg,

        #0f172a,

        #111827,

        #1e293b

    );

    min-height:100vh;

    font-family:Arial,sans-serif;
}

.admin-auth-section{

    min-height:100vh;

    display:flex;

    align-items:center;

    padding:60px 0;
}

.admin-auth-card{

    background:#fff;

    border-radius:20px;

    padding:45px;

    box-shadow:
    0 15px 60px rgba(0,0,0,0.25);
}

.admin-auth-logo h1{

    font-size:34px;

    font-weight:800;

    color:#111827;

    margin-bottom:10px;
}

.admin-auth-logo p{

    color:#6b7280;

    margin-bottom:35px;
}

.form-label{

    font-weight:600;

    margin-bottom:10px;
}

.form-control{

    height:55px;

    border-radius:12px;

    border:1px solid #d1d5db;
}

.input-group-text{

    border-radius:12px 0 0 12px;

    background:#f9fafb;
}

.password-wrapper{

    position:relative;
}

.toggle-password{

    position:absolute;

    top:50%;

    right:15px;

    transform:translateY(-50%);

    border:none;

    background:none;

    cursor:pointer;

    color:#6b7280;
}

.btn-admin-login{

    height:55px;

    border:none;

    border-radius:12px;

    background:#f5b400;

    color:#111827;

    font-weight:700;

    transition:0.3s;
}

.btn-admin-login:hover{

    background:#e0a300;
}

.forgot-link{

    text-decoration:none;

    font-weight:600;

    color:#111827;
}

.forgot-link:hover{

    color:#f5b400;
}

.admin-security-info{

    margin-top:35px;

    padding-top:25px;

    border-top:1px solid #e5e7eb;
}

.security-item{

    display:flex;

    align-items:center;

    gap:10px;

    margin-bottom:12px;

    font-size:14px;

    color:#4b5563;
}

.security-item i{

    color:#16a34a;
}

.admin-auth-footer{

    margin-top:25px;

    color:#9ca3af;
}

@media(max-width:768px){

    .admin-auth-card{

        padding:30px 20px;
    }

    .admin-auth-logo h1{

        font-size:28px;
    }
}

</style>

<!-- ================================= -->
<!-- JS -->
<!-- ================================= -->

<script>

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

</body>

</html>