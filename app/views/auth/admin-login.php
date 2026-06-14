<?php

declare(strict_types=1);

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

require_once ROOT_PATH . '/config/app.php';
require_once HELPER_PATH . '/security.php';
require_once HELPER_PATH . '/session.php';
require_once HELPER_PATH . '/csrf.php';
require_once HELPER_PATH . '/rateLimiter.php';
require_once MIDDLEWARE_PATH . '/admin-guest.php';

/*
|--------------------------------------------------------------------------
| SECURITY HEADERS
|--------------------------------------------------------------------------
*/

securityHeaders();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

/*
|--------------------------------------------------------------------------
| REDIRECT AUTHENTICATED ADMINS
|--------------------------------------------------------------------------
*/

if (
    function_exists('isLoggedIn') &&
    function_exists('isAdmin') &&
    isLoggedIn() &&
    isAdmin()
) {
    header('Location: dashboard.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle = 'Admin Login | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;

unset($_SESSION['error'], $_SESSION['success']);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title><?php echo escape($pageTitle); ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        body{
            min-height:100vh;
            background:#f4f6f9;
        }

        .admin-login-wrapper{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:40px 20px;
        }

        .admin-card{
            width:100%;
            max-width:520px;
            background:#ffffff;
            border-radius:20px;
            padding:40px;
            box-shadow:0 15px 40px rgba(0,0,0,0.08);
        }

        .brand-logo{
            width:80px;
            height:80px;
            margin:auto;
            border-radius:50%;
            background:#111827;
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:32px;
        }

        .brand-title{
            margin-top:20px;
            font-weight:700;
        }

        .brand-subtitle{
            color:#6b7280;
            font-size:14px;
        }

        .form-label{
            font-weight:600;
        }

        .input-group-text{
            background:#f8fafc;
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
            z-index:10;
        }

        .btn-admin{
            height:50px;
            border:none;
            border-radius:12px;
            font-weight:600;
            background:#111827;
            color:#fff;
        }

        .btn-admin:hover{
            background:#000;
            color:#fff;
        }

        .security-box{
            margin-top:25px;
            padding-top:20px;
            border-top:1px solid #e5e7eb;
        }

        .security-item{
            display:flex;
            align-items:center;
            gap:10px;
            margin-bottom:10px;
            color:#6b7280;
            font-size:14px;
        }

        .footer-note{
            text-align:center;
            margin-top:20px;
            color:#9ca3af;
            font-size:13px;
        }

    </style>

</head>

<body>

<div class="admin-login-wrapper">

    <div class="admin-card">

        <div class="text-center mb-4">

            <div class="brand-logo">
                <i class="bi bi-shield-lock-fill"></i>
            </div>

            <h2 class="brand-title">
                <?php echo escape(APP_NAME); ?>
            </h2>

            <p class="brand-subtitle">
                Secure Administrator Access
            </p>

        </div>

        <?php if ($error): ?>

            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo escape($error); ?>
            </div>

        <?php endif; ?>

        <?php if ($success): ?>

            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo escape($success); ?>
            </div>

        <?php endif; ?>

        <form
            id="adminLoginForm"
            action="../auth/admin-login-handler.php"
            method="POST"
            autocomplete="off"
        >

            <?php echo csrfField(); ?>

            <div class="mb-3">

                <label class="form-label">
                    Admin Email
                </label>

                <div class="input-group">

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

            <div class="mb-4">

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

            <div
                class="d-flex justify-content-between align-items-center mb-4"
            >

                <div class="form-check">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="remember_me"
                        name="remember_me"
                        value="1"
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
                    class="text-decoration-none"
                >
                    Forgot Password?
                </a>

            </div>

            <button
                type="submit"
                class="btn btn-admin w-100"
            >
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Login
            </button>

        </form>

        <div class="security-box">

            <div class="security-item">
                <i class="bi bi-shield-check"></i>
                Brute Force Protection Enabled
            </div>

            <div class="security-item">
                <i class="bi bi-fingerprint"></i>
                Session Fingerprinting Active
            </div>

            <div class="security-item">
                <i class="bi bi-lock-fill"></i>
                CSRF Protection Enabled
            </div>

        </div>

        <div class="footer-note">
            Authorized Personnel Only.
            All login attempts are monitored.
        </div>

    </div>

</div>

<script>

function togglePassword()
{
    const password =
        document.getElementById('password');

    const icon =
        document.getElementById('passwordIcon');

    if (password.type === 'password')
    {
        password.type = 'text';

        icon.classList.remove('bi-eye');

        icon.classList.add('bi-eye-slash');
    }
    else
    {
        password.type = 'password';

        icon.classList.remove('bi-eye-slash');

        icon.classList.add('bi-eye');
    }
}

document
.getElementById('adminLoginForm')
.addEventListener('submit', function(){

    const button =
        this.querySelector('button[type="submit"]');

    button.disabled = true;

    button.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Authenticating...';

});

</script>

</body>
</html>
