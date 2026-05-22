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
| SECURITY HEADERS
|--------------------------------------------------------------------------
*/

securityHeaders();

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
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
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="<?php echo base_url('../assets/admin/css/admin.css'); ?>"
    >

</head>

<body class="admin-login-body">

<!-- ================================= -->
<!-- LOGIN WRAPPER -->
<!-- ================================= -->

<div class="admin-login-wrapper">

    <div class="container">

        <div class="row justify-content-center align-items-center min-vh-100">

            <div class="col-xl-5 col-lg-6 col-md-8">

                <div class="admin-login-card">

                    <!-- ============================== -->
                    <!-- BRAND -->
                    <!-- ============================== -->

                    <div class="admin-brand text-center">

                        <div class="admin-logo">

                            <i class="bi bi-buildings"></i>

                        </div>

                        <h1>

                            <?php echo APP_NAME; ?>

                        </h1>

                        <p>

                            Secure Admin Authentication Portal

                        </p>

                    </div>

                    <!-- ============================== -->
                    <!-- ALERTS -->
                    <!-- ============================== -->

                    <?php if($error): ?>

                        <div class="alert alert-danger alert-dismissible fade show">

                            <i class="bi bi-exclamation-triangle-fill"></i>

                            <?php echo escape($error); ?>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                            ></button>

                        </div>

                    <?php endif; ?>

                    <?php if($success): ?>

                        <div class="alert alert-success alert-dismissible fade show">

                            <i class="bi bi-check-circle-fill"></i>

                            <?php echo escape($success); ?>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                            ></button>

                        </div>

                    <?php endif; ?>

                    <!-- ============================== -->
                    <!-- LOGIN FORM -->
                    <!-- ============================== -->

                    <form
                        action="../auth/admin-login-handler.php"
                        method="POST"
                        autocomplete="off"
                        id="adminLoginForm"
                    >

                        <?php echo csrfField(); ?>

                        <!-- EMAIL -->

                        <div class="form-group mb-4">

                            <label class="form-label">

                                Admin Email

                            </label>

                            <div class="input-group auth-input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-envelope-fill"></i>

                                </span>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Enter admin email"
                                    required
                                    maxlength="150"
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
                                    minlength="8"
                                >

                                <button
                                    type="button"
                                    class="toggle-password"
                                    onclick="togglePassword()"
                                >

                                    <i
                                        class="bi bi-eye"
                                        id="passwordIcon"
                                    ></i>

                                </button>

                            </div>

                        </div>

                        <!-- REMEMBER -->

                        <div class="d-flex justify-content-between align-items-center mb-4">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="remember_me"
                                    name="remember_me"
                                >

                                <label
                                    class="form-check-label"
                                    for="remember_me"
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

                            <i class="bi bi-shield-lock-fill"></i>

                            Secure Login

                        </button>

                    </form>

                    <!-- ============================== -->
                    <!-- SECURITY FEATURES -->
                    <!-- ============================== -->

                    <div class="security-features">

                        <div class="security-item">

                            <i class="bi bi-shield-check"></i>

                            <span>

                                Brute Force Protection Enabled

                            </span>

                        </div>

                        <div class="security-item">

                            <i class="bi bi-fingerprint"></i>

                            <span>

                                Session Fingerprinting Active

                            </span>

                        </div>

                        <div class="security-item">

                            <i class="bi bi-lock-fill"></i>

                            <span>

                                CSRF & XSS Protection Enabled

                            </span>

                        </div>

                    </div>

                    <!-- ============================== -->
                    <!-- FOOTER -->
                    <!-- ============================== -->

                    <div class="admin-login-footer text-center">

                        <small>

                            Authorized personnel only.
                            All activities are monitored.

                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- ================================= -->
<!-- JS -->
<!-- ================================= -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/*
|--------------------------------------------------------------------------
| TOGGLE PASSWORD
|--------------------------------------------------------------------------
*/

function togglePassword()
{
    const password =
    document.getElementById('password');

    const icon =
    document.getElementById('passwordIcon');

    if(password.type === 'password'){

        password.type = 'text';

        icon.classList.remove('bi-eye');

        icon.classList.add('bi-eye-slash');

    }else{

        password.type = 'password';

        icon.classList.remove('bi-eye-slash');

        icon.classList.add('bi-eye');
    }
}

/*
|--------------------------------------------------------------------------
| DISABLE MULTIPLE SUBMITS
|--------------------------------------------------------------------------
*/

document
.getElementById('adminLoginForm')

.addEventListener('submit', function(){

    const button =
    this.querySelector('button[type="submit"]');

    button.disabled = true;

    button.innerHTML = `

        <span
            class="spinner-border spinner-border-sm"
        ></span>

        Authenticating...
    `;
});

</script>

</body>

</html>
